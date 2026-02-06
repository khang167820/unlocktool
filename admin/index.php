<?php
// admin/index.php - Trang chủ quản trị
session_start();
require '../config.php';
require '../helpers.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$stats = [
    'accounts' => $conn->query("SELECT COUNT(*) FROM accounts")->fetch_row()[0],
    'prices' => $conn->query("SELECT COUNT(*) FROM prices")->fetch_row()[0],
    'total' => $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0],
    'paid' => $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'paid'")->fetch_row()[0],
    'pending' => $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetch_row()[0],
];

// Phân trang lịch sử thuê
$per_page = 50;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $per_page;
$total_paid = $stats['paid'];
$total_pages = ceil($total_paid / $per_page);

$history = $conn->query("SELECT o.*, a.username FROM orders o LEFT JOIN accounts a ON o.account_id = a.id WHERE o.status = 'paid' ORDER BY o.paid_at DESC LIMIT $per_page OFFSET $offset");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang Quản Trị</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body { background: #f5f7fa; padding: 12px; font-size: 13px; }
        h2 { font-size: 18px; margin-bottom: 10px; }
        .stat-box { background: #fff; border-radius: 8px; padding: 12px; text-align: center; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
        .stat-box h3 { font-size: 22px; margin: 0; color: #007bff; }
        .stat-box p { margin: 4px 0 0; font-size: 11px; color: #666; }
        .table { font-size: 12px; margin-bottom: 0; }
        .table th, .table td { padding: 5px 8px; }
    </style>
</head>
<body>
<h2>🏠 Trang Quản Trị</h2>
<?php include 'includes/nav.php'; ?>

<div class="row mb-3">
    <div class="col-4 col-md-2 mb-2"><div class="stat-box"><h3><?php echo $stats['accounts']; ?></h3><p>Tài khoản</p></div></div>
    <div class="col-4 col-md-2 mb-2"><div class="stat-box"><h3><?php echo $stats['prices']; ?></h3><p>Gói thuê</p></div></div>
    <div class="col-4 col-md-2 mb-2"><div class="stat-box" style="background:#d4edda;"><h3 style="color:#28a745;"><?php echo $stats['paid']; ?></h3><p>Đã TT</p></div></div>
    <div class="col-4 col-md-2 mb-2"><div class="stat-box" style="background:#fff3cd;"><h3 style="color:#856404;"><?php echo $stats['pending']; ?></h3><p>Chờ TT</p></div></div>
    <div class="col-4 col-md-2 mb-2"><div class="stat-box"><h3><?php echo $stats['total']; ?></h3><p>Tổng đơn</p></div></div>
</div>

<h6 class="mb-2">📜 Lịch sử thuê tài khoản (Trang <?php echo $page; ?>/<?php echo $total_pages; ?> - Tổng <?php echo number_format($total_paid); ?> đơn)</h6>
<div class="table-responsive">
    <table class="table table-bordered table-sm bg-white">
        <thead class="thead-light"><tr><th>ID</th><th>Mã</th><th>Tài khoản</th><th>Gói</th><th>Giá</th><th>IP</th><th>Thời gian</th></tr></thead>
        <tbody>
        <?php while ($row = $history->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><small><?php echo htmlspecialchars($row['tracking_code'] ?? '-'); ?></small></td>
                <td><small><?php echo htmlspecialchars($row['username'] ?? '-'); ?></small></td>
                <td><?php echo displayPackageName($row['hours']); ?></td>
                <td><?php echo formatMoney($row['amount']); ?></td>
                <td><small><?php echo htmlspecialchars($row['ip_address'] ?? '-'); ?></small></td>
                <td><small><?php echo $row['paid_at'] ? date('H:i d/m', strtotime($row['paid_at'])) : '-'; ?></small></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Phân trang -->
<?php if ($total_pages > 1): ?>
<nav class="mt-3">
    <ul class="pagination pagination-sm justify-content-center flex-wrap">
        <?php if ($page > 1): ?>
            <li class="page-item"><a class="page-link" href="?page=1">« Đầu</a></li>
            <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>">‹ Trước</a></li>
        <?php endif; ?>
        
        <?php
        $start_page = max(1, $page - 3);
        $end_page = min($total_pages, $page + 3);
        for ($i = $start_page; $i <= $end_page; $i++):
        ?>
            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        
        <?php if ($page < $total_pages): ?>
            <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>">Sau ›</a></li>
            <li class="page-item"><a class="page-link" href="?page=<?php echo $total_pages; ?>">Cuối »</a></li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>

</body>
</html>
