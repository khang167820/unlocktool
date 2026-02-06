<?php
require '../config.php';
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Thống kê số liệu
$toolCount = $conn->query("SELECT COUNT(*) FROM accounts WHERE type = 'Unlocktool'")->fetch_row()[0];
$priceCount = $conn->query("SELECT COUNT(*) FROM prices")->fetch_row()[0];
$totalOrders = $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];
$paidOrders = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'paid'")->fetch_row()[0];
$pendingOrders = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetch_row()[0];
$cancelledOrders = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'cancelled'")->fetch_row()[0];

// Tính doanh thu trong tháng hiện tại
$monthlyRevenue = $conn->query("SELECT SUM(amount) FROM orders WHERE status = 'paid' AND MONTH(paid_at) = MONTH(CURRENT_DATE()) AND YEAR(paid_at) = YEAR(CURRENT_DATE())")->fetch_row()[0] ?? 0;

// Lấy lịch sử 7 ngày gần nhất
$history = $conn->query("SELECT o.*, a.username FROM orders o LEFT JOIN accounts a ON o.account_id = a.id WHERE o.paid_at >= NOW() - INTERVAL 7 DAY ORDER BY o.paid_at DESC");
?><!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Trang Quản Trị</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <style>
    body { padding: 2rem; background: #f7f7f7; }
    .nav-link { margin-right: 1rem; }
    .history-table { font-size: 0.9rem; }
  </style>
</head>
<body>
  <h1 class="mb-4">Trang Quản Trị</h1>
  <nav class="mb-3">
    <a href="accounts.php" class="nav-link d-inline">Tài khoản</a>
    <a href="prices.php" class="nav-link d-inline">Gói thuê</a>
    <a href="orders.php" class="nav-link d-inline">Đơn hàng</a>
    <a href="logout.php" class="nav-link d-inline text-danger">Đăng xuất</a>
  </nav>
  <p><strong>Tổng số tài khoản unlock tool:</strong> <?php echo $toolCount; ?></p>
  <p><strong>Tổng số gói thuê:</strong> <?php echo $priceCount; ?></p>
  <p><strong>Tổng số đơn hàng:</strong> <?php echo $totalOrders; ?> (Đã thanh toán: <?php echo $paidOrders; ?>, Đang chờ: <?php echo $pendingOrders; ?>, Đã hủy: <?php echo $cancelledOrders; ?>)</p>
  <p><strong>Tổng doanh thu tháng <?php echo date('m/Y'); ?>:</strong> <?php echo number_format($monthlyRevenue, 0, ',', '.'); ?> VND</p>

  <h4 class="mt-5">Lịch sử thuê 7 ngày gần nhất</h4>
  <table class="table table-bordered table-sm history-table mt-3">
    <thead class="thead-light">
      <tr>
        <th>Mã đơn</th>
        <th>Tài khoản</th>
        <th>Gói thuê</th>
        <th>Giá</th>
        <th>IP thuê</th>
        <th>Thanh toán lúc</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $history->fetch_assoc()): ?>
      <tr>
        <td><?php echo $row['id']; ?></td>
        <td><?php echo htmlspecialchars($row['username']); ?></td>
        <td><?php echo $row['hours'] . ' giờ'; ?></td>
        <td><?php echo number_format($row['amount'], 0, ',', '.') . ' VND'; ?></td>
        <td><?php echo htmlspecialchars($row['ip_address'] ?? '-'); ?></td>
        <td><?php echo $row['paid_at'] ? date('H:i d/m/Y', strtotime($row['paid_at'])) : '-'; ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</body>
</html>
