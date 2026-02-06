<?php
// create_order.php - Tạo đơn hàng và link thanh toán Pay2S
require 'config.php';
require 'helpers.php';

// ===== RATE LIMITING & SECURITY =====
$ip_address = getClientIP();

// 1. Danh sách IP bị block vĩnh viễn
$blocked_ips = [
    '42.119.229.100',
];

if (in_array($ip_address, $blocked_ips)) {
    http_response_code(403);
    die('Access denied.');
}

// 2. Verify reCAPTCHA v3
$recaptcha_secret = '6Lf1I1IsAAAAACehsrHGV1fCvq8pTsHSKlO7lRe0';
$recaptcha_token = $_POST['recaptcha_token'] ?? '';

if (empty($recaptcha_token)) {
    http_response_code(403);
    die('Xác thực thất bại. Vui lòng thử lại.');
}

$recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
$recaptcha_data = [
    'secret' => $recaptcha_secret,
    'response' => $recaptcha_token,
    'remoteip' => $ip_address
];

$recaptcha_options = [
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => http_build_query($recaptcha_data)
    ]
];

$recaptcha_context = stream_context_create($recaptcha_options);
$recaptcha_result = file_get_contents($recaptcha_url, false, $recaptcha_context);
$recaptcha_json = json_decode($recaptcha_result, true);

// Score 0.0 = bot, 1.0 = human. Threshold 0.5
if (!$recaptcha_json['success'] || $recaptcha_json['score'] < 0.5) {
    http_response_code(403);
    die('Phát hiện hành vi bất thường. Vui lòng thử lại.');
}

// 3. Rate Limiting với Database Lock (chống concurrent requests)
$conn->begin_transaction();

try {
    // Lock để chống nhiều request cùng lúc
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM orders WHERE ip_address = ? AND created_at >= ? FOR UPDATE");
    
    // 2a. Check: Max 3 đơn trong 1 phút
    $one_min_ago = date('Y-m-d H:i:s', time() - 60);
    $stmt->bind_param("ss", $ip_address, $one_min_ago);
    $stmt->execute();
    $count_1min = $stmt->get_result()->fetch_assoc()['cnt'];
    
    if ($count_1min >= 3) {
        $conn->rollback();
        http_response_code(429);
        die("Bạn chỉ được tạo tối đa 3 đơn hàng mỗi phút. Vui lòng đợi 1 phút.");
    }
    $stmt->close();
    
    // 2b. Check: 10 đơn pending trong 60 phút → chặn
    $block_time_ago = date('Y-m-d H:i:s', time() - 3600);
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM orders WHERE ip_address = ? AND status = 'pending' AND created_at >= ? FOR UPDATE");
    $stmt->bind_param("ss", $ip_address, $block_time_ago);
    $stmt->execute();
    $pending_count = $stmt->get_result()->fetch_assoc()['cnt'];
    $stmt->close();
    
    if ($pending_count >= 10) {
        $conn->rollback();
        http_response_code(429);
        die("Mày bị ngu loll à. Tạo đơn thì thanh toán đi. Đói ăn đi phá à, chờ 60p nữa nhé.");
    }
    
    // Commit lock - các request khác phải đợi
    $conn->commit();
    
} catch (Exception $e) {
    $conn->rollback();
    die("Lỗi hệ thống. Vui lòng thử lại.");
}

// 3. Kiểm tra Referer (chặn request từ bên ngoài)
$allowed_referers = ['unlocktool.us', 'www.unlocktool.us'];
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$referer_host = parse_url($referer, PHP_URL_HOST) ?? '';

if (!empty($referer) && !in_array($referer_host, $allowed_referers)) {
    http_response_code(403);
    die('Invalid request source.');
}

// ===== END SECURITY =====

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['price_id'])) {
    header("Location: index.php");
    exit;
}

$price_id = intval($_POST['price_id']);
$price = $conn->query("SELECT * FROM prices WHERE id=$price_id")->fetch_assoc();

if (!$price) {
    die("Gói thuê không hợp lệ.");
}

$hours = $price['hours'];
$amount = (int)$price['price'];

if ($amount < 1000 || $amount > 10000000000) {
    die("Số tiền thuê không hợp lệ (1,000 - 10,000,000,000 VND)");
}

// Tạo mã theo dõi UNIQUE và lưu đơn hàng
// $ip_address đã được khai báo ở phần security
$created_at = date('Y-m-d H:i:s');

// Tạo tracking_code không bị trùng
$max_attempts = 10;
$attempt = 0;
$tracking_code = '';

do {
    // Format: DH + ddmm + 6 số random
    $tracking_code = 'DH' . date('dm') . random_int(100000, 999999);
    $check = $conn->query("SELECT id FROM orders WHERE tracking_code = '{$tracking_code}' LIMIT 1");
    $attempt++;
} while ($check && $check->num_rows > 0 && $attempt < $max_attempts);

if ($attempt >= $max_attempts) {
    // Fallback: dùng timestamp + 3 số random
    $tracking_code = 'DH' . date('dm') . time() % 1000000;
}

$conn->query("INSERT INTO orders (tracking_code, hours, amount, status, created_at, ip_address) 
    VALUES ('{$tracking_code}', {$hours}, {$amount}, 'pending', '{$created_at}', '{$ip_address}')");
$order_id = $conn->insert_id;
$conn->query("UPDATE orders SET order_code = $order_id WHERE id = $order_id");

if ($order_id <= 0) {
    die("Lỗi tạo đơn hàng.");
}

// ===== Pay2S API =====
$accessKey = 'ce6205fe594f6e67011cb372dd5165ff8f3898cafa1ccf6b56b4c7f8cadbf5a8';
$secretKey = '3f334b33818fb769456aa2db199485ad2cedd8a542aeb8ad90e3a516ea93033b';
$partnerCode = 'PAY2S6K81PRUW2ZHNNX1';
$merchantName = "Unlocktool.US";

$returnUrl = $base_url . "order_status.php?token={$tracking_code}&orderCode={$order_id}";
$ipnUrl = $base_url . "webhook.php";
$requestId = time() . "";

$bankList = [['account_number' => '20867091', 'bank_id' => 'acb']];

// Tạo chữ ký
$rawHash = "accessKey={$accessKey}&amount={$amount}&bankAccounts=Array&ipnUrl={$ipnUrl}&orderId={$order_id}&orderInfo={$tracking_code}&partnerCode={$partnerCode}&redirectUrl={$returnUrl}&requestId={$requestId}&requestType=pay2s";
$signature = hash_hmac("sha256", $rawHash, $secretKey);

$payload = [
    'accessKey' => $accessKey,
    'partnerCode' => $partnerCode,
    'partnerName' => $merchantName,
    'requestId' => $requestId,
    'amount' => $amount,
    'orderId' => $order_id,
    'orderInfo' => $tracking_code,
    'orderType' => 'pay2s',
    'bankAccounts' => $bankList,
    'redirectUrl' => $returnUrl,
    'ipnUrl' => $ipnUrl,
    'requestType' => 'pay2s',
    'signature' => $signature,
];

// Gửi request
$ch = curl_init('https://payment.pay2s.vn/v1/gateway/api/create');
curl_setopt_array($ch, [
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT => 10,
]);
$result = json_decode(curl_exec($ch), true);
curl_close($ch);

if (isset($result['status']) && $result['status'] == false) {
    $conn->query("DELETE FROM orders WHERE id={$order_id}");
    die("Tạo link thanh toán thất bại: " . htmlspecialchars($result['message'] ?? 'Unknown error'));
}

header("Location: " . $result['payUrl']);
exit;
