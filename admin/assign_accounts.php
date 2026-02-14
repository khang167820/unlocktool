<?php
// assign_accounts.php - Gán tài khoản cho các đơn đã thanh toán nhưng chưa có account
require 'includes/session_config.php';
require '../config.php';
require '../helpers.php';

session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$message = '';
$assigned_count = 0;

// Xử lý gán tự động
if (isset($_POST['auto_assign'])) {
    $conn->begin_transaction();
    
    try {
        // Lấy danh sách đơn paid nhưng chưa có account
        $pending_orders = $conn->query("
            SELECT * FROM orders 
            WHERE status = 'paid' AND (account_id IS NULL OR account_id = 0)
            ORDER BY paid_at ASC
            FOR UPDATE
        ");
        
        while ($order = $pending_orders->fetch_assoc()) {
            // Tìm tài khoản available - chỉ cần is_available=1 và không có note
            $acc_result = $conn->query("
                SELECT a.id, a.username
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
            ");
            
            $acc = $acc_result ? $acc_result->fetch_assoc() : null;
            
            if ($acc) {
                $expires_at = date('Y-m-d H:i:s', strtotime($order['paid_at']) + $order['hours'] * 3600);
                
                $update_acc = $conn->query("UPDATE accounts SET is_available=0 WHERE id={$acc['id']} AND is_available=1");
                
                if ($conn->affected_rows > 0) {
                    $conn->query("UPDATE orders SET account_id={$acc['id']}, expires_at='{$expires_at}' WHERE id={$order['id']}");
                    $assigned_count++;
                }
            }
        }
        
        $conn->commit();
        $message = "Đã gán thành công {$assigned_count} đơn hàng!";
        
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Lỗi: " . $e->getMessage();
    }
}

// Lấy danh sách đơn chưa có account
$unassigned = $conn->query("
    SELECT o.*, 
           TIMESTAMPDIFF(MINUTE, o.paid_at, NOW()) as wait_minutes
    FROM orders o
    WHERE o.status = 'paid' AND (o.account_id IS NULL OR o.account_id = 0)
    ORDER BY o.paid_at ASC
");

// Đếm tài khoản available
$available_count = $conn->query("SELECT COUNT(*) as cnt FROM accounts WHERE is_available = 1")->fetch_assoc()['cnt'];

require 'includes/nav.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gán tài khoản cho đơn hàng</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; padding: 20px; background: #f5f5f5; }
        .card { margin-bottom: 20px; }
        .table th { background: #343a40; color: white; }
        .badge-warning { font-size: 1rem; }
    </style>
</head>
<body>
<div class="container-fluid">
    <h2 class="mb-4">🔧 Gán tài khoản cho đơn hàng</h2>
    
    <?php if ($message): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5>Đơn chưa có tài khoản</h5>
                    <h2><?php echo $unassigned->num_rows; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5>Tài khoản sẵn sàng</h5>
                    <h2><?php echo $available_count; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <form method="post">
                        <button type="submit" name="auto_assign" class="btn btn-primary btn-lg btn-block" 
                                <?php echo ($unassigned->num_rows == 0 || $available_count == 0) ? 'disabled' : ''; ?>>
                            🚀 Gán tự động tất cả
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($unassigned->num_rows > 0): ?>
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <strong>Danh sách đơn chưa có tài khoản</strong>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Mã đơn</th>
                                <th>Gói thuê</th>
                                <th>Số tiền</th>
                                <th>Thanh toán lúc</th>
                                <th>Chờ</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = $unassigned->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><code><?php echo htmlspecialchars($row['tracking_code']); ?></code></td>
                                <td><?php echo $row['hours']; ?> giờ</td>
                                <td><?php echo number_format($row['amount']); ?> VND</td>
                                <td><?php echo $row['paid_at']; ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $row['wait_minutes'] > 30 ? 'danger' : 'warning'; ?>">
                                        <?php echo $row['wait_minutes']; ?> phút
                                    </span>
                                </td>
                                <td><?php echo $row['ip_address']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-success">
            ✅ Tất cả đơn hàng đã được gán tài khoản!
        </div>
    <?php endif; ?>
</div>
</body>
</html>
