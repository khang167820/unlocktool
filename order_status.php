<?php
// order_status.php - Hiển thị trạng thái đơn hàng
require 'config.php';
require 'helpers.php';

$token     = $_GET['token'] ?? '';
$orderCodeRaw = $_GET['orderCode'] ?? '';
$status    = isset($_GET['resultCode']) ? strtoupper($_GET['resultCode']) : '';
$client_ip = getClientIP();

if (empty($orderCodeRaw) && empty($token)) {
    echo "<h3>Thiếu mã đơn hàng.</h3>";
    exit;
}

// Tìm đơn hàng theo nhiều cách
$res = null;

// 1. Tìm bằng token (tracking_code từ URL callback)
if ($token !== '') {
    $safe = $conn->real_escape_string($token);
    $res = $conn->query("SELECT o.*, a.username, a.password, a.password_changed, a.is_available FROM orders o 
        LEFT JOIN accounts a ON o.account_id = a.id 
        WHERE o.tracking_code = '{$safe}' LIMIT 1");
}

// 2. Tìm trực tiếp bằng tracking_code (nếu khách nhập full mã như DH08012666381)
if ((!$res || $res->num_rows === 0) && $orderCodeRaw !== '') {
    $safeRaw = $conn->real_escape_string($orderCodeRaw);
    $res = $conn->query("SELECT o.*, a.username, a.password, a.password_changed, a.is_available FROM orders o 
        LEFT JOIN accounts a ON o.account_id = a.id 
        WHERE o.tracking_code = '{$safeRaw}' LIMIT 1");
}

// 3. Tìm bằng order_code (ID đơn hàng)
$orderCodeNum = intval(preg_replace('/[^0-9]/', '', $orderCodeRaw));
if ((!$res || $res->num_rows === 0) && $orderCodeNum > 0) {
    $res = $conn->query("SELECT o.*, a.username, a.password, a.password_changed, a.is_available FROM orders o 
        LEFT JOIN accounts a ON o.account_id = a.id 
        WHERE o.order_code = {$orderCodeNum} LIMIT 1");
}

// 4. Thử tìm đơn mới có format DH + số
if ((!$res || $res->num_rows === 0) && $orderCodeNum > 0) {
    // Lấy phần số giữ nguyên format (có số 0 đầu)
    preg_match('/\d+/', $orderCodeRaw, $numMatches);
    $numPart = $numMatches[0] ?? $orderCodeNum;
    $guess = "DH{$numPart}";
    $safeGuess = $conn->real_escape_string($guess);
    $res = $conn->query("SELECT o.*, a.username, a.password, a.password_changed, a.is_available FROM orders o 
        LEFT JOIN accounts a ON o.account_id = a.id 
        WHERE o.tracking_code = '{$safeGuess}' LIMIT 1");
}

// 5. Thử tìm đơn cũ có format RENT + số
if ((!$res || $res->num_rows === 0) && $orderCodeNum > 0) {
    preg_match('/\d+/', $orderCodeRaw, $numMatches);
    $numPart = $numMatches[0] ?? $orderCodeNum;
    $guess = "RENT{$numPart}";
    $safeGuess = $conn->real_escape_string($guess);
    $res = $conn->query("SELECT o.*, a.username, a.password, a.password_changed, a.is_available FROM orders o 
        LEFT JOIN accounts a ON o.account_id = a.id 
        WHERE o.tracking_code = '{$safeGuess}' LIMIT 1");
}

// 6. Tìm bằng LIKE nếu vẫn không tìm thấy
if ((!$res || $res->num_rows === 0) && $orderCodeRaw !== '') {
    preg_match('/\d+/', $orderCodeRaw, $numMatches);
    if (!empty($numMatches[0])) {
        $numPart = $conn->real_escape_string($numMatches[0]);
        $res = $conn->query("SELECT o.*, a.username, a.password, a.password_changed, a.is_available FROM orders o 
            LEFT JOIN accounts a ON o.account_id = a.id 
            WHERE o.tracking_code LIKE '%{$numPart}' LIMIT 1");
    }
}

if (!$res || $res->num_rows === 0) {
    echo "<h3>Đơn hàng không tồn tại.</h3>";
    exit;
}
$order = $res->fetch_assoc();

// Lịch sử thuê 30 ngày theo IP
$user_ip = getClientIP();
$time_limit = date('Y-m-d H:i:s', time() - 30 * 86400);
$stmt = $conn->prepare("
    SELECT o.*, a.username, a.password, a.type, a.password_changed, a.is_available
    FROM orders o
    JOIN accounts a ON o.account_id = a.id
    WHERE o.ip_address = ? AND o.created_at >= ? AND o.status = 'paid'
    ORDER BY o.created_at DESC
");
$stmt->bind_param("ss", $user_ip, $time_limit);
$stmt->execute();
$history_result = $stmt->get_result();

// Xác định trạng thái hiển thị
$show_account = false;
if ($status !== '') {
    if ($status == '00' || $status == 0) {
        $status_text = '<span style="color: #16c60c; font-weight: bold;">Đã thanh toán</span>';
        $show_account = true;
    } else {
        $status_text = '<span style="color: red; font-weight: bold;">Đã hủy</span>';
    }
} else {
    switch ($order['status']) {
        case 'paid':
            $status_text = '<span style="color: #16c60c; font-weight: bold;">Đã thanh toán</span>';
            $show_account = true;
            break;
        case 'cancelled':
            $status_text = '<span style="color: red; font-weight: bold;">Đã hủy</span>';
            break;
        default:
            $status_text = '<span style="color: gray; font-weight: bold;">Đang chờ thanh toán</span>';
    }
}

$page_title = 'Trạng thái đơn hàng';
$body_class = 'd-flex flex-column justify-content-center align-items-center min-vh-100';
$extra_css = '
    .info-label { color: #ff2d89; font-weight: bold; font-size: 1.08rem; margin-bottom: 0.6rem; }
    .hotline { color: #0076FF; font-weight: bold; font-size: 1.07rem; }
    .card h3 { font-size: 1.5rem; letter-spacing: 0.5px; }
    .card p strong, .card p { font-weight: bold !important; }
    .copy-btn { font-size: 0.75rem; padding: 2px 8px; margin-left: 5px; border-radius: 4px; }
    body { padding: 20px 10px !important; min-height: 100vh; }
    .history-btn { 
        background: linear-gradient(135deg, #f8c346, #f0a400); 
        border: none; 
        color: #1f1a0d; 
        font-weight: 700; 
        padding: 12px 24px; 
        border-radius: 12px; 
        box-shadow: 0 10px 24px rgba(240, 164, 0, 0.35); 
        transition: all 0.2s;
        font-size: 1rem;
    }
    .history-btn:hover { 
        transform: translateY(-2px); 
        box-shadow: 0 14px 30px rgba(240, 164, 0, 0.45); 
        color: #0d0c09; 
    }
    .history-collapse-wrapper {
        display: flex;
        justify-content: center;
        width: 100%;
        padding: 0 15px;
    }
    .history-card { 
        max-width: 900px; 
        width: 100%;
        border-radius: 18px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.08);
        border: 1px solid #f0e6d3;
        background: rgba(255,255,255,0.98);
    }
    .history-card .table { 
        font-size: 0.9rem; 
        margin-bottom: 0;
    }
    .history-card .table th { 
        background: linear-gradient(135deg, #f8c346, #e6a817);
        color: #4a3c1a; 
        font-weight: 700;
        padding: 14px 10px;
        white-space: nowrap;
        border: none;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .history-card .table td {
        padding: 12px 10px;
        vertical-align: middle;
        border-color: #f5ebe0;
        background: #fff;
    }
    .history-card .table tbody tr:hover td {
        background-color: #fffbf0;
    }
    .history-card .table tbody tr:nth-child(even) td {
        background-color: #fdfaf5;
    }
    .history-card .table tbody tr:nth-child(even):hover td {
        background-color: #fff8e8;
    }
    .history-title {
        background: linear-gradient(135deg, #f8c346, #e6a817);
        color: #4a3c1a;
        padding: 16px 20px;
        border-radius: 18px 18px 0 0;
        margin: 0;
        text-align: center;
        font-size: 1.15rem;
        font-weight: 700;
        border-bottom: 2px solid #e6a817;
    }
    .history-card .p-3 {
        padding: 1rem !important;
    }
    .history-card .badge-info {
        background: linear-gradient(135deg, #4d96ff, #3a7bd5);
        font-weight: 600;
        padding: 5px 10px;
    }
    .history-card .badge-success {
        background: linear-gradient(135deg, #28a745, #20853a);
        font-weight: 600;
        padding: 5px 10px;
    }
    .history-card .badge-secondary {
        background: linear-gradient(135deg, #888, #666);
        font-weight: 600;
        padding: 5px 10px;
    }
    .history-card code {
        font-family: inherit;
        font-size: 0.9em;
    }
    .history-card .btn-outline-primary {
        border-color: #f8c346;
        color: #b8860b;
    }
    .history-card .btn-outline-primary:hover {
        background: #f8c346;
        color: #4a3c1a;
    }
    .history-card .btn-outline-success {
        border-color: #28a745;
        color: #28a745;
    }
    .history-card .btn-outline-success:hover {
        background: #28a745;
        color: #fff;
    }
    @media (max-width: 768px) {
        .history-card .table { font-size: 0.78rem; }
        .history-card .table th, .history-card .table td { padding: 8px 5px; }
        .copy-btn { font-size: 0.65rem; padding: 1px 5px; }
        .history-title { font-size: 1rem; padding: 12px 15px; }
    }
';
require 'includes/header.php';
?>

<div class="card p-4" style="width: 100%; max-width: 500px;">
    <h3 class="text-center mb-4">Trạng thái đơn hàng</h3>
    <p><strong>Mã đơn hàng:</strong> <?php echo htmlspecialchars($order['tracking_code'] ?? $order['id']); ?></p>
    <p><strong>Gói thuê:</strong> <?php echo displayPackageName($order['hours']); ?></p>
    <p><strong>Giá tiền:</strong> <?php echo formatMoney($order['amount']); ?></p>
    <p><strong>Trạng thái:</strong> <?php echo $status_text; ?></p>
    
    <?php if ($show_account && $order['username']): ?>
        <?php 
        $time_info = getTimeRemaining($order['expires_at']); 
        $is_expired = $time_info && $time_info['expired'];
        ?>
        <script>
        // Xóa bộ đếm refresh khi đã có tài khoản
        localStorage.removeItem('refresh_count_<?php echo intval($order['id']); ?>');
        </script>
        
        <?php if (!$is_expired && ($order['password_changed'] ?? 0) == 0): ?>
            <!-- Còn hạn, chưa đổi pass và đang thuê: hiển thị đầy đủ -->
            <p class="info-label">
                Tài khoản: <?php echo htmlspecialchars($order['username']); ?>
                <button class="btn btn-outline-secondary btn-sm copy-btn" data-copy="<?php echo htmlspecialchars($order['username']); ?>">Sao chép</button>
            </p>
            <p class="info-label">
                Mật khẩu: <?php echo htmlspecialchars($order['password']); ?>
                <button class="btn btn-outline-secondary btn-sm copy-btn" data-copy="<?php echo htmlspecialchars($order['password']); ?>">Sao chép</button>
            </p>
            <?php if ($time_info): ?>
                <p><strong>Hết hạn lúc:</strong> <?php echo date('H:i d/m/Y', $time_info['timestamp']); ?></p>
                <p><strong>Còn lại:</strong> <span data-expire="<?php echo $time_info['timestamp']; ?>"><?php echo $time_info['text']; ?></span></p>
            <?php endif; ?>
        <?php else: ?>
            <!-- Đã hết hạn hoặc đã đổi pass: ẨN mật khẩu -->
            <p class="info-label">
                Tài khoản: <?php echo htmlspecialchars($order['username']); ?>
            </p>
            <p class="info-label">
                Mật khẩu: <span class="text-muted"><?php echo str_repeat('*', strlen($order['password'])); ?></span>
            </p>
            <?php if ($time_info): ?>
                <p><strong>Hết hạn lúc:</strong> <?php echo date('H:i d/m/Y', $time_info['timestamp']); ?></p>
                <?php if (!$is_expired): ?>
                    <p><strong>Còn lại:</strong> <span data-expire="<?php echo $time_info['timestamp']; ?>"><?php echo $time_info['text']; ?></span></p>
                <?php endif; ?>
            <?php endif; ?>
            <?php if ($is_expired): ?>
                <p class="text-danger">Tài khoản đã hết hạn. Vui lòng tạo đơn mới để tiếp tục sử dụng.</p>
            <?php elseif (($order['password_changed'] ?? 0) == 1): ?>
                <p class="text-danger">Mật khẩu đã được thay đổi. Vui lòng LH admin để tiếp tục sử dụng.</p>
            <?php elseif (false): // is_available check removed ?>
                <p class="text-danger">Tài khoản đang ở trạng thái chờ thuê. Vui lòng liên hệ admin để được hỗ trợ.</p>
            <?php endif; ?>
        <?php endif; ?>
        
    <?php elseif ($show_account && $order['status'] === 'paid' && empty($order['account_id'])): ?>
        <div class="alert alert-warning text-center mb-3" id="waitingBox">
            <div class="spinner-border spinner-border-sm text-warning mr-2" role="status"></div>
            <strong>Đang chờ cấp tài khoản...</strong>
            <br><small id="waitingText">Trang sẽ tự động cập nhật sau <span id="countdown">3</span> giây</small>
        </div>
        <p class="text-muted text-center"><small>Nếu sau 30 giây vẫn chưa có, vui lòng liên hệ hỗ trợ.</small></p>
        <script>
        (function() {
            var orderId = <?php echo intval($order['id']); ?>;
            var storageKey = 'refresh_count_' + orderId;
            var refreshCount = parseInt(localStorage.getItem(storageKey) || '0');
            var maxRefresh = 10; // Tối đa 10 lần (30 giây)
            var seconds = 3;
            
            // Đếm ngược
            var countdownEl = document.getElementById('countdown');
            var interval = setInterval(function() {
                seconds--;
                if (countdownEl) countdownEl.innerText = seconds;
                if (seconds <= 0) {
                    clearInterval(interval);
                    if (refreshCount < maxRefresh) {
                        localStorage.setItem(storageKey, refreshCount + 1);
                        location.reload();
                    } else {
                        // Hết số lần thử, hiển thị thông báo
                        document.getElementById('waitingBox').innerHTML = 
                            '<strong>⚠️ Chưa có tài khoản sẵn sàng</strong><br>' +
                            '<small>Vui lòng đợi admin cấp thêm hoặc liên hệ hỗ trợ.</small>';
                        localStorage.removeItem(storageKey);
                    }
                }
            }, 1000);
        })();
        </script>
    <?php elseif ($show_account && $order['status'] === 'paid'): ?>
        <p class="text-danger">Đơn đã thanh toán. Vui lòng kiểm tra mục Lịch sử thuê (30 ngày) hoặc liên hệ hỗ trợ.</p>
    <?php endif; ?>
    
    <p>
        <strong>Đối với tài khoản sai pass khách vui lòng mua tài khoản mới và gửi thông tin qua hotline:</strong>
        <span class="hotline">0777333763</span>
    </p>
    
    <!-- Nút lịch sử thuê 30 ngày -->
    <button class="history-btn d-flex align-items-center justify-content-center w-100 mt-3" type="button" data-toggle="collapse" data-target="#historyCollapse" aria-expanded="false" aria-controls="historyCollapse">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="mr-2" viewBox="0 0 16 16">
            <path d="M8.515 3.542a.5.5 0 0 1 0 .916 5.001 5.001 0 1 1-4.53 7.274.5.5 0 1 1-.93-.368A6 6 0 1 0 8 2v1a.5.5 0 0 1 .5.5c0 .145-.053.29-.14.42a.5.5 0 0 1-.845-.42z"/>
            <path d="M7.5 8V5.5a.5.5 0 0 1 1 0V8h2a.5.5 0 0 1 0 1H8a.5.5 0 0 1-.5-.5z"/>
        </svg>
        Lịch sử thuê (30 ngày)
    </button>
    
    <!-- Nút tải lại trang -->
    <button class="btn btn-outline-info mt-3 btn-block d-flex align-items-center justify-content-center" onclick="location.reload();">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="mr-2" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
            <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
        </svg>
        Tải lại trang
    </button>
    
    <a href="index.php" class="btn btn-primary mt-3 btn-block">Quay lại trang chủ</a>
</div>

<!-- Lịch sử thuê 30 ngày - Collapse -->
<div class="collapse mt-4" id="historyCollapse">
    <div class="history-collapse-wrapper">
        <div class="card history-card p-0">
            <div class="history-title">
                🕐 Lịch sử thuê tài khoản (30 ngày)
            </div>
            <div class="p-3">
                <?php if ($history_result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered text-center mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Loại</th>
                                    <th>Tài khoản</th>
                                    <th>Mật khẩu</th>
                                    <th>Gói thuê</th>
                                    <th>Thời gian thuê</th>
                                    <th>Còn lại</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $i = 1; while ($r = $history_result->fetch_assoc()): 
                                $h_time = getTimeRemaining($r['expires_at']);
                            ?>
                                <tr>
                                    <td><strong><?php echo $i++; ?></strong></td>
                                    <td><?php echo htmlspecialchars($r['type'] ?? 'Không rõ'); ?></td>
                                    <td>
                                        <span style="background:#fff8e8;padding:4px 8px;border-radius:6px;border:1px solid #f0d890;font-weight:600;color:#8b6914;"><?php echo htmlspecialchars($r['username']); ?></span>
                                        <button class="btn btn-outline-primary btn-sm copy-btn" data-copy="<?php echo htmlspecialchars($r['username']); ?>">📋</button>
                                    </td>
                                    <td>
                                        <?php if (($h_time && $h_time['expired']) || ($r['password_changed'] ?? 0) == 1 || ($r['is_available'] ?? 0) == 1): ?>
                                            <span class="text-muted" style="font-weight:500;"><?php echo str_repeat('*', strlen($r['password'])); ?></span>
                                        <?php else: ?>
                                            <span style="background:#e8f5e9;padding:4px 8px;border-radius:6px;border:1px solid #a5d6a7;font-weight:600;color:#2e7d32;"><?php echo htmlspecialchars($r['password']); ?></span>
                                            <button class="btn btn-outline-success btn-sm copy-btn" data-copy="<?php echo htmlspecialchars($r['password']); ?>">📋</button>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge badge-info"><?php echo displayPackageName($r['hours']); ?></span></td>
                                    <td style="white-space:nowrap;"><?php echo date('H:i d/m/Y', strtotime($r['created_at'])); ?></td>
                                    <td>
                                        <?php if ($h_time && !$h_time['expired']): ?>
                                            <span class="badge badge-success" data-expire="<?php echo $h_time['timestamp']; ?>"><?php echo $h_time['text']; ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Hết hạn</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center mb-0">
                        <strong>📭 Chưa có lịch sử</strong><br>
                        Bạn chưa thuê tài khoản nào trong 30 ngày qua.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Countdown timer (đếm ngược thời gian thuê còn lại)
function updateCountdowns() {
    document.querySelectorAll('[data-expire]').forEach(el => {
        const t = parseInt(el.dataset.expire) * 1000;
        const d = t - Date.now();
        if (d <= 0) {
            el.innerText = 'Đã hết hạn';
        } else {
            const h = Math.floor(d / 3600000);
            const m = Math.floor((d % 3600000) / 60000);
            const s = Math.floor((d % 60000) / 1000);
            el.innerText = `${h}h ${m}m ${s}s`;
        }
    });
}

setInterval(updateCountdowns, 1000);
window.onload = function() { updateCountdowns(); };

// Copy button
$(document).ready(function() {
    $(document).on('click', '.copy-btn', function() {
        const text = $(this).data('copy');
        navigator.clipboard.writeText(text).then(() => alert('Đã sao chép: ' + text));
    });
});
</script>

<?php require 'includes/footer.php'; ?>
