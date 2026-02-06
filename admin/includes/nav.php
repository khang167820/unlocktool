<?php
/**
 * Admin Navigation - Dùng chung cho tất cả trang admin
 */
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<style>
.admin-nav { margin-bottom: 12px; display: flex; flex-wrap: wrap; gap: 6px; }
.admin-nav a {
    padding: 6px 12px;
    border-radius: 6px;
    background: #f3f6fb;
    color: #1f2937;
    text-decoration: none;
    font-weight: 500;
    font-size: 13px;
    transition: all 0.15s;
}
.admin-nav a:hover { background: #e0e6ed; text-decoration: none; }
.admin-nav a.active { background: #007bff; color: #fff; font-weight: 600; }
.admin-nav a.logout { background: #dc3545; color: #fff; }
.admin-nav a.logout:hover { background: #c82333; }
</style>
<div class="admin-nav">
    <a href="accounts.php" class="<?php echo $currentPage === 'accounts.php' ? 'active' : ''; ?>">📋 Tài khoản</a>
    <a href="prices.php" class="<?php echo $currentPage === 'prices.php' ? 'active' : ''; ?>">💰 Gói thuê</a>
    <a href="orders.php" class="<?php echo $currentPage === 'orders.php' ? 'active' : ''; ?>">📦 Đơn hàng</a>
    <a href="assign_accounts.php" class="<?php echo $currentPage === 'assign_accounts.php' ? 'active' : ''; ?>">🔧 Gán acc</a>
    <a href="dashboard.php" class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">📊 Doanh thu</a>
    <a href="index.php" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">🏠 Trang chủ</a>
    <a href="logout.php" class="logout">🚪 Đăng xuất</a>
</div>
