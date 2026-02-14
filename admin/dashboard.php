<?php
// admin/dashboard.php - Thống kê doanh thu
require 'includes/session_config.php';
session_start();
require '../config.php';
require '../helpers.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$filter = $_GET['filter'] ?? 'today';
$from = $_GET['from'] ?? null;
$to = $_GET['to'] ?? null;

$filters = [
    'today' => ['Hôm nay', date('Y-m-d'), date('Y-m-d')],
    'yesterday' => ['Hôm qua', date('Y-m-d', strtotime('-1 day')), date('Y-m-d', strtotime('-1 day'))],
    'this_week' => ['Tuần này', date('Y-m-d', strtotime('monday this week')), date('Y-m-d')],
    'last_week' => ['Tuần trước', date('Y-m-d', strtotime('monday last week')), date('Y-m-d', strtotime('sunday last week'))],
    'this_month' => ['Tháng này', date('Y-m-01'), date('Y-m-d')],
    'last_month' => ['Tháng trước', date('Y-m-01', strtotime('-1 month')), date('Y-m-t', strtotime('-1 month'))],
    'this_year' => ['Năm nay', date('Y-01-01'), date('Y-m-d')],
];

if ($filter === 'custom' && $from && $to) { $date_start = $from; $date_end = $to; $label = "Tùy chọn"; }
elseif (isset($filters[$filter])) { [$label, $date_start, $date_end] = $filters[$filter]; }
else { $date_start = $date_end = date('Y-m-d'); $label = "Hôm nay"; }

$data = $conn->query("SELECT COUNT(*) AS cnt, COALESCE(SUM(amount),0) AS total FROM orders WHERE DATE(created_at) BETWEEN '$date_start' AND '$date_end' AND status='paid'")->fetch_assoc();
$diff = (strtotime($date_end) - strtotime($date_start)) / 86400 + 1;
$prev_end = date('Y-m-d', strtotime($date_start . ' -1 day'));
$prev_start = date('Y-m-d', strtotime($prev_end . " -" . ($diff - 1) . " days"));
$prev = $conn->query("SELECT COUNT(*) AS cnt, COALESCE(SUM(amount),0) AS total FROM orders WHERE DATE(created_at) BETWEEN '$prev_start' AND '$prev_end' AND status='paid'")->fetch_assoc();

function pct($n, $p) { return $p == 0 ? ($n == 0 ? 0 : 100) : round((($n - $p) / max(1, $p)) * 100); }
$pct_rev = pct($data['total'], $prev['total']);
$pct_ord = pct($data['cnt'], $prev['cnt']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Doanh thu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f7fa; padding: 12px; font-size: 13px; }
        h2 { font-size: 18px; margin-bottom: 10px; }
        .btn { padding: 4px 10px; font-size: 12px; }
        .filter-btn { padding: 5px 10px; margin: 2px; border-radius: 6px; border: 1px solid #ddd; background: #fff; font-size: 12px; cursor: pointer; }
        .filter-btn.active { background: #007bff; color: #fff; border-color: #007bff; }
        .filter-btn:hover { background: #e9ecef; }
        .filter-btn.active:hover { background: #0069d9; }
        .stat-card { background: #fff; border-radius: 10px; padding: 16px; box-shadow: 0 2px 6px rgba(0,0,0,0.06); text-align: center; }
        .stat-card h3 { font-size: 24px; margin: 8px 0 4px; }
        .stat-card .pct { font-size: 12px; }
        input[type="date"] { padding: 4px 8px; font-size: 12px; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>
<h2>📊 Doanh thu (<?php echo $label; ?>)</h2>
<?php include 'includes/nav.php'; ?>

<form method="get" class="mb-3">
    <?php foreach ($filters as $k => [$lbl, $s, $e]): ?>
        <button type="submit" name="filter" value="<?php echo $k; ?>" class="filter-btn <?php echo $filter === $k ? 'active' : ''; ?>"><?php echo $lbl; ?></button>
    <?php endforeach; ?>
    <input type="date" name="from" value="<?php echo htmlspecialchars($from ?? ''); ?>">
    <input type="date" name="to" value="<?php echo htmlspecialchars($to ?? ''); ?>">
    <button type="submit" name="filter" value="custom" class="filter-btn <?php echo $filter === 'custom' ? 'active' : ''; ?>">Tùy chọn</button>
</form>

<div class="row">
    <div class="col-md-4 mb-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#e8f5e9,#c8e6c9);">
            <div>💰 Tổng doanh thu</div>
            <h3 style="color:#2e7d32;"><?php echo number_format($data['total']); ?> VNĐ</h3>
            <span class="pct <?php echo $pct_rev < 0 ? 'text-danger' : 'text-success'; ?>"><?php echo $pct_rev; ?>% so với kỳ trước</span>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#e3f2fd,#bbdefb);">
            <div>📦 Số đơn hàng</div>
            <h3 style="color:#1565c0;"><?php echo $data['cnt']; ?> đơn</h3>
            <span class="pct <?php echo $pct_ord < 0 ? 'text-danger' : 'text-success'; ?>"><?php echo $pct_ord; ?>% so với kỳ trước</span>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="stat-card">
            <div>📅 Kỳ trước</div>
            <h3 style="font-size:16px;"><?php echo date('d/m', strtotime($prev_start)); ?> - <?php echo date('d/m/Y', strtotime($prev_end)); ?></h3>
            <span class="pct text-muted"><?php echo number_format($prev['total']); ?> VNĐ / <?php echo $prev['cnt']; ?> đơn</span>
        </div>
    </div>
</div>
</body>
</html>
