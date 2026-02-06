<?php
// webhook.php - Xử lý IPN từ Pay2S
require 'config.php';
require 'helpers.php';

header("content-type: application/json; charset=UTF-8");
http_response_code(200);

$rawData = file_get_contents('php://input');
file_put_contents(__DIR__ . '/webhook_log.txt', date('c') . " RAW: " . $rawData . PHP_EOL, FILE_APPEND);

$data = json_decode($rawData, true);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

$accessKey = "ce6205fe594f6e67011cb372dd5165ff8f3898cafa1ccf6b56b4c7f8cadbf5a8";
$secretKey = "3f334b33818fb769456aa2db199485ad2cedd8a542aeb8ad90e3a516ea93033b";

try {
    // Lấy dữ liệu từ webhook
    $amount = $data['amount'];
    $extraData = $data['extraData'] ?? '';
    $message = $data['message'];
    $orderId = $data['orderId'];
    $orderInfo = $data['orderInfo'];
    $orderType = $data['orderType'];
    $partnerCode = $data['partnerCode'];
    $payType = $data['payType'];
    $requestId = $data['requestId'];
    $responseTime = $data['responseTime'];
    $resultCode = $data['resultCode'];
    $transId = $data['transId'];
    $m2signature = $data['m2signature'];

    // Verify chữ ký
    $rawHash = "accessKey=$accessKey&amount=$amount&extraData=$extraData&message=$message&orderId=$orderId&orderInfo=$orderInfo&orderType=$orderType&partnerCode=$partnerCode&payType=$payType&requestId=$requestId&responseTime=$responseTime&resultCode=$resultCode&transId=$transId";
    $partnerSignature = hash_hmac("sha256", $rawHash, $secretKey);

    if ($m2signature !== $partnerSignature) {
        echo json_encode(['success' => false, 'message' => 'Invalid signature']);
        exit;
    }

    // Xử lý thanh toán thành công
    if (in_array($resultCode, ['0', 0, '00'])) {
        $orderId = intval($orderId);
        $orderInfoEsc = $conn->real_escape_string($orderInfo);

        // Bắt đầu transaction để tránh race condition
        $conn->begin_transaction();
        
        try {
            // Tìm đơn hàng và lock để tránh xử lý trùng
            $order_result = $conn->query("SELECT * FROM orders WHERE order_code={$orderId} AND status='pending' LIMIT 1 FOR UPDATE");
            $order = $order_result ? $order_result->fetch_assoc() : null;
            
            if (!$order) {
                $order_result = $conn->query("SELECT * FROM orders WHERE tracking_code='{$orderInfoEsc}' AND status='pending' LIMIT 1 FOR UPDATE");
                $order = $order_result ? $order_result->fetch_assoc() : null;
            }

            if ($order && $order['status'] === 'pending') {
                // Kiểm tra đơn này đã được xử lý chưa (double-check)
                $check_processed = $conn->query("SELECT id FROM orders WHERE id={$order['id']} AND status='paid' LIMIT 1");
                if ($check_processed && $check_processed->num_rows > 0) {
                    $conn->rollback();
                    file_put_contents(__DIR__ . '/webhook_log.txt', date('c') . " ALREADY_PAID: Order {$order['id']} was already processed" . PHP_EOL, FILE_APPEND);
                    echo json_encode(['success' => true, 'message' => 'Order already processed']);
                    exit;
                }
                
                // TỰ ĐỘNG GIẢI PHÓNG tài khoản đã hết hạn - ĐÃ TẮT
                // (Tính năng này đã được tắt theo yêu cầu - admin phải chuyển tài khoản thủ công)
                /*
                $conn->query("
                    UPDATE accounts a 
                    LEFT JOIN (
                        SELECT account_id, MAX(expires_at) AS max_expires
                        FROM orders WHERE status='paid'
                        GROUP BY account_id
                    ) o ON a.id = o.account_id
                    SET a.is_available = 1
                    WHERE a.is_available = 0 
                    AND (o.max_expires IS NULL OR o.max_expires <= NOW())
                    AND (a.note IS NULL OR a.note = '')
                ");
                $released_count = $conn->affected_rows;
                if ($released_count > 0) {
                    file_put_contents(__DIR__ . '/webhook_log.txt', date('c') . " AUTO_RELEASE: Released {$released_count} expired accounts" . PHP_EOL, FILE_APPEND);
                }
                */
                
                // Tìm tài khoản trống với LOCK để tránh gán trùng
                // Chỉ lấy tài khoản có is_available=1 và không có ghi chú
                // Nếu is_available=1 nghĩa là admin đã xác nhận sẵn sàng cho thuê
                $acc_query = "
                    SELECT a.id, a.username, a.password, a.note
                    FROM accounts a
                    WHERE a.is_available = 1
                    AND (a.note IS NULL OR a.note = '')
                    ORDER BY (
                        CASE 
                            WHEN (SELECT MAX(o3.expires_at) FROM orders o3 WHERE o3.account_id = a.id AND o3.status = 'paid') IS NULL THEN 0
                            ELSE 1
                        END
                    ) ASC,
                    (
                        SELECT MAX(o3.expires_at) FROM orders o3 
                        WHERE o3.account_id = a.id AND o3.status = 'paid'
                    ) ASC, 
                    a.id ASC
                    LIMIT 1
                    FOR UPDATE
                ";
                
                $acc_result = $conn->query($acc_query);
                $acc = $acc_result ? $acc_result->fetch_assoc() : null;
                
                // Log chi tiết để debug
                $debug_available = $conn->query("SELECT COUNT(*) as cnt FROM accounts WHERE is_available = 1")->fetch_assoc()['cnt'];
                $debug_available_no_note = $conn->query("SELECT COUNT(*) as cnt FROM accounts WHERE is_available = 1 AND (note IS NULL OR note = '')")->fetch_assoc()['cnt'];
                $debug_now = $conn->query("SELECT NOW() as now_time")->fetch_assoc()['now_time'];
                file_put_contents(__DIR__ . '/webhook_log.txt', date('c') . " DEBUG: MySQL_NOW={$debug_now}, Available_total={$debug_available}, Available_no_note={$debug_available_no_note}" . PHP_EOL, FILE_APPEND);
                file_put_contents(__DIR__ . '/webhook_log.txt', date('c') . " FIND_ACC: Order {$order['id']} - " . ($acc ? "Found ID={$acc['id']} ({$acc['username']})" : "NO ACCOUNT FOUND") . PHP_EOL, FILE_APPEND);

                $paid_at = date('Y-m-d H:i:s');
                $expires_at = date('Y-m-d H:i:s', time() + $order['hours'] * 3600);

                if ($acc) {
                    // Double-check tài khoản vẫn available trước khi gán
                    $recheck = $conn->query("SELECT is_available FROM accounts WHERE id={$acc['id']} LIMIT 1")->fetch_assoc();
                    
                    if ($recheck && $recheck['is_available'] == 1) {
                        // Sử dụng UPDATE có điều kiện để đảm bảo atomic
                        $update_acc = $conn->query("UPDATE accounts SET is_available=0 WHERE id={$acc['id']} AND is_available=1");
                        
                        if ($conn->affected_rows > 0) {
                            $conn->query("UPDATE orders SET status='paid', account_id={$acc['id']}, paid_at='{$paid_at}', expires_at='{$expires_at}' WHERE id={$order['id']}");
                            $conn->commit();
                            file_put_contents(__DIR__ . '/webhook_log.txt', date('c') . " ASSIGNED: Order {$order['id']} -> Account {$acc['id']} ({$acc['username']})" . PHP_EOL, FILE_APPEND);
                        } else {
                            // Tài khoản đã bị gán cho đơn khác trong khoảng thời gian rất ngắn
                            // Rollback và đánh dấu paid, admin sẽ gán sau
                            $conn->rollback();
                            $conn->query("UPDATE orders SET status='paid', paid_at='{$paid_at}' WHERE id={$order['id']}");
                            file_put_contents(__DIR__ . '/webhook_log.txt', date('c') . " RACE_CONDITION: Order {$order['id']} - Account {$acc['id']} was taken, order marked paid without account" . PHP_EOL, FILE_APPEND);
                        }
                    } else {
                        // Tài khoản đã bị gán cho đơn khác
                        $conn->rollback();
                        $conn->query("UPDATE orders SET status='paid', paid_at='{$paid_at}' WHERE id={$order['id']}");
                        file_put_contents(__DIR__ . '/webhook_log.txt', date('c') . " NO_LONGER_AVAILABLE: Order {$order['id']} - Account {$acc['id']} was taken, order marked paid without account" . PHP_EOL, FILE_APPEND);
                    }
                } else {
                    // Không có tài khoản, vẫn đánh dấu paid nhưng không gán account
                    $conn->query("UPDATE orders SET status='paid', paid_at='{$paid_at}' WHERE id={$order['id']}");
                    $conn->commit();
                    file_put_contents(__DIR__ . '/webhook_log.txt', date('c') . " NO_ACC: Order {$order['id']} paid but no account available" . PHP_EOL, FILE_APPEND);
                }
            } else {
                $conn->rollback();
                file_put_contents(__DIR__ . '/webhook_log.txt', date('c') . " SKIP: Order {$orderId} not found or not pending" . PHP_EOL, FILE_APPEND);
            }
        } catch (Exception $e) {
            $conn->rollback();
            file_put_contents(__DIR__ . '/webhook_log.txt', date('c') . " TRANSACTION_ERROR: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
        }
    }

    echo json_encode(['success' => true, 'message' => 'Received payment result']);

} catch (Exception $e) {
    file_put_contents(__DIR__ . '/webhook_log.txt', date('c') . " ERROR: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
