<?php
// admin/orders.php - Quản lý đơn hàng
require 'includes/session_config.php';
session_start();
require '../config.php';
require '../helpers.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$message = "";

// Xóa đơn
if (isset($_GET['delete_id'])) {
    $oid = intval($_GET['delete_id']);
    $ord = $conn->query("SELECT * FROM orders WHERE id={$oid}")->fetch_assoc();
    if ($ord && $ord['account_id']) $conn->query("UPDATE accounts SET is_available=1 WHERE id={$ord['account_id']}");
    $conn->query("DELETE FROM orders WHERE id={$oid}");
    $message = "Đã xóa đơn #{$oid}";
}

// Xóa tất cả pending
if (isset($_GET['delete_pending'])) {
    $conn->query("DELETE FROM orders WHERE status='pending'");
    $message = "Đã xóa tất cả đơn pending";
}

// GÁN TÀI KHOẢN THỦ CÔNG cho đơn thiếu acc
if (isset($_GET['assign_id'])) {
    $oid = intval($_GET['assign_id']);
    $ord = $conn->query("SELECT * FROM orders WHERE id={$oid} AND status='paid' AND account_id IS NULL")->fetch_assoc();
    
    if ($ord) {
        // Tìm tài khoản trống - chỉ cần is_available=1 và không có note
        $acc = $conn->query("
            SELECT a.id, a.username FROM accounts a
            WHERE a.is_available = 1
            AND (a.note IS NULL OR a.note = '')
            ORDER BY (
                CASE 
                    WHEN (SELECT MAX(expires_at) FROM orders WHERE account_id = a.id AND status = 'paid') IS NULL THEN 0
                    ELSE 1
                END
            ) ASC,
            (SELECT MAX(expires_at) FROM orders WHERE account_id = a.id AND status = 'paid') ASC, 
            a.id ASC
            LIMIT 1
        ")->fetch_assoc();
        
        if ($acc) {
            $expires_at = date('Y-m-d H:i:s', strtotime($ord['paid_at']) + $ord['hours'] * 3600);
            $conn->query("UPDATE accounts SET is_available=0 WHERE id={$acc['id']}");
            $conn->query("UPDATE orders SET account_id={$acc['id']}, expires_at='{$expires_at}' WHERE id={$oid}");
            $message = "✅ Đã gán tài khoản {$acc['username']} cho đơn #{$oid}";
        } else {
            $message = "❌ Không có tài khoản trống để gán!";
        }
    }
}

// GÁN TẤT CẢ đơn thiếu acc
if (isset($_GET['assign_all'])) {
    $missing = $conn->query("SELECT * FROM orders WHERE status='paid' AND account_id IS NULL ORDER BY paid_at ASC");
    $assigned = 0;
    while ($ord = $missing->fetch_assoc()) {
        $acc = $conn->query("
            SELECT a.id FROM accounts a
            WHERE a.is_available = 1 
            AND (a.note IS NULL OR a.note = '')
            ORDER BY (
                CASE 
                    WHEN (SELECT MAX(expires_at) FROM orders WHERE account_id = a.id AND status = 'paid') IS NULL THEN 0
                    ELSE 1
                END
            ) ASC,
            (SELECT MAX(expires_at) FROM orders WHERE account_id = a.id AND status = 'paid') ASC,
            a.id ASC
            LIMIT 1
        ")->fetch_assoc();
        if ($acc) {
            $expires_at = date('Y-m-d H:i:s', strtotime($ord['paid_at']) + $ord['hours'] * 3600);
            $conn->query("UPDATE accounts SET is_available=0 WHERE id={$acc['id']}");
            $conn->query("UPDATE orders SET account_id={$acc['id']}, expires_at='{$expires_at}' WHERE id={$ord['id']}");
            $assigned++;
        }
    }
    $message = $assigned > 0 ? "✅ Đã gán {$assigned} đơn!" : "Không có đơn nào cần gán hoặc hết tài khoản trống";
}

$orders = $conn->query("SELECT o.*, a.username AS account_name FROM orders o LEFT JOIN accounts a ON o.account_id = a.id ORDER BY o.id DESC");

// Đếm đơn thiếu tài khoản
$missing_count = $conn->query("SELECT COUNT(*) as cnt FROM orders WHERE status='paid' AND account_id IS NULL")->fetch_assoc()['cnt'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Đơn hàng</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body { background: #f5f7fa; padding: 12px; font-size: 13px; }
        h2 { font-size: 18px; margin-bottom: 10px; }
        .btn { padding: 4px 10px; font-size: 12px; }
        .btn-sm { padding: 2px 6px; font-size: 11px; }
        .table { font-size: 12px; margin-bottom: 0; }
        .table th, .table td { padding: 5px 8px; vertical-align: middle; }
        .badge { font-size: 10px; padding: 3px 6px; }
        .alert { padding: 8px 12px; font-size: 13px; margin-bottom: 10px; }
        .missing-acc { background: #fff3cd !important; }
    </style>
</head>
<body>
<h2>Quản lý Đơn hàng</h2>
<?php include 'includes/nav.php'; ?>

<?php if ($message): ?><div class="alert alert-info"><?php echo $message; ?></div><?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-2 flex-wrap" style="gap:8px;">
    <span class="text-muted" style="font-size:12px;">
        Tổng: <?php echo $orders->num_rows; ?> đơn
        <?php if ($missing_count > 0): ?>
            | <span class="text-danger font-weight-bold">⚠️ <?php echo $missing_count; ?> đơn thiếu tài khoản</span>
        <?php endif; ?>
    </span>
    <div>
        <?php if ($missing_count > 0): ?>
            <a href="?assign_all=1" class="btn btn-success btn-sm" onclick="return confirm('Gán tài khoản cho tất cả đơn thiếu?');">✅ Gán tất cả (<?php echo $missing_count; ?>)</a>
        <?php endif; ?>
        <a href="?delete_pending=1" class="btn btn-danger btn-sm" onclick="return confirm('Xóa tất cả pending?');">🗑 Xóa Pending</a>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-hover bg-white">
        <thead class="thead-light">
            <tr><th width="50">ID</th><th>Mã theo dõi</th><th>Gói</th><th>Số tiền</th><th width="70">Trạng thái</th><th>IP</th><th>Tài khoản</th><th width="100">Thao tác</th></tr>
        </thead>
        <tbody>
        <?php 
        $orders->data_seek(0); // Reset pointer
        while ($row = $orders->fetch_assoc()): 
            $is_missing = ($row['status'] === 'paid' && empty($row['account_id']));
        ?>
            <tr class="<?php echo $is_missing ? 'missing-acc' : ''; ?>">
                <td><?php echo $row['id']; ?></td>
                <td><small><?php echo htmlspecialchars($row['tracking_code']); ?></small></td>
                <td><?php echo displayPackageName($row['hours']); ?></td>
                <td><?php echo formatMoney($row['amount']); ?></td>
                <td>
                    <span class="badge badge-<?php echo $row['status'] === 'paid' ? 'success' : ($row['status'] === 'cancelled' ? 'danger' : 'warning'); ?>">
                        <?php echo $row['status']; ?>
                    </span>
                </td>
                <td><small><?php echo htmlspecialchars($row['ip_address'] ?? '-'); ?></small></td>
                <td>
                    <?php if ($is_missing): ?>
                        <span class="text-danger font-weight-bold">⚠️ Chưa có</span>
                    <?php else: ?>
                        <small><?php echo htmlspecialchars($row['account_name'] ?? '-'); ?></small>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($is_missing): ?>
                        <a href="?assign_id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm">Gán ACC</a>
                    <?php endif; ?>
                    <a href="?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa?');">Xóa</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
