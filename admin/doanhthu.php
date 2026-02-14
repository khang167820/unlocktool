<?php
require 'includes/session_config.php';
require '../config.php';
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Lấy dữ liệu thống kê
$revenueStats = $conn->query("
    SELECT a.username, SUM(o.amount) as total_paid 
    FROM orders o 
    LEFT JOIN accounts a ON o.account_id = a.id 
    WHERE o.status = 'paid' 
      AND MONTH(o.paid_at) = MONTH(CURRENT_DATE()) 
      AND YEAR(o.paid_at) = YEAR(CURRENT_DATE()) 
    GROUP BY o.account_id 
    ORDER BY total_paid DESC
");

if (!$revenueStats) {
    die("Lỗi truy vấn: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Thống kê Doanh thu theo Tài khoản</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="p-4">
<div class="container">
  <h2 class="mb-4">📊 Thống kê doanh thu theo tài khoản (Tháng <?php echo date('m/Y'); ?>)</h2>
  <table class="table table-bordered table-sm">
    <thead class="thead-light">
      <tr>
        <th>Tài khoản</th>
        <th>Tổng tiền thanh toán (VND)</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($revenueStats->num_rows > 0): ?>
        <?php while ($row = $revenueStats->fetch_assoc()): ?>
        <tr>
          <td><?php echo htmlspecialchars($row['username'] ?? '(Chưa gán tài khoản)'); ?></td>
          <td><?php echo number_format($row['total_paid'], 0, ',', '.'); ?></td>
        </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="2">Không có dữ liệu thanh toán nào trong tháng này.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
