<?php
// admin/accounts.php - Quản lý tài khoản
require 'includes/session_config.php';
session_start();
require '../config.php';
require '../helpers.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Chống cache LiteSpeed/Hostinger
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

// Xử lý thêm tài khoản
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);
    $type = $conn->real_escape_string($_POST['type']);
    $note = $conn->real_escape_string($_POST['note'] ?? '');
    $renewal_date = '';
    $renewal_raw = $_POST['renewal_date'] ?? '';
    if ($renewal_raw !== '') {
        $dt = DateTime::createFromFormat('d/m/Y', $renewal_raw) ?: DateTime::createFromFormat('Y-m-d', $renewal_raw);
        $renewal_date = $dt ? $dt->format('Y-m-d') : '';
    }
    $renewal_value = $renewal_date ? "'{$conn->real_escape_string($renewal_date)}'" : "NULL";
    // Nếu có ghi chú thì đặt is_available = 0 (không cho vào hàng chờ thuê)
    $is_available = empty($note) ? 1 : 0;
    $conn->query("INSERT INTO accounts (username, password, type, is_available, note, renewal_date, password_changed) VALUES ('$username', '$password', '$type', $is_available, '$note', $renewal_value, 0)");
    header("Location: accounts.php?success=1&t=" . time());
    exit;
}

// Lưu trạng thái hàng loạt
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'bulk_save') {
    $readyIds = array_map('intval', array_keys($_POST['ready'] ?? []));
    if ($readyIds) {
        // Chặn đưa vào hàng chờ thuê nếu có ghi chú
        $conn->query("UPDATE accounts SET is_available = 1, password_changed = 0 WHERE id IN (" . implode(',', $readyIds) . ") AND (note IS NULL OR note = '')");
    }
}

// Khóa tất cả tài khoản có ghi chú đang ở trạng thái "Chờ thuê"
if (isset($_GET['lock_noted'])) {
    $result = $conn->query("UPDATE accounts SET is_available = 0 WHERE note IS NOT NULL AND note != '' AND is_available = 1");
    $affected = $conn->affected_rows;
    header("Location: accounts.php?locked=$affected&t=" . time());
    exit;
}

// Reset tất cả tài khoản "Chờ thuê" về password_changed = 0
if (isset($_GET['reset_pass_all'])) {
    $result = $conn->query("UPDATE accounts SET password_changed = 0 WHERE is_available = 1");
    $affected = $conn->affected_rows;
    header("Location: accounts.php?reset_pass=$affected&t=" . time());
    exit;
}

// Toggle actions
if (isset($_GET['toggle_id'])) { 
    $id = intval($_GET['toggle_id']);
    $acc = $conn->query("SELECT note, is_available FROM accounts WHERE id = $id")->fetch_assoc();
    // Nếu đang thuê và có ghi chú => không cho chuyển sang chờ thuê
    if ($acc && $acc['is_available'] == 0 && !empty($acc['note'])) {
        header("Location: accounts.php?blocked=note&t=" . time());
        exit;
    }
    // Khi chuyển sang Chờ thuê (is_available từ 0 -> 1), reset password_changed = 0
    if ($acc && $acc['is_available'] == 0) {
        $conn->query("UPDATE accounts SET is_available = 1, password_changed = 0 WHERE id = $id");
    } else {
        $conn->query("UPDATE accounts SET is_available = 0 WHERE id = $id");
    }
    header("Location: accounts.php?t=" . time()); 
    exit; 
}
if (isset($_GET['toggle_pass'])) { $conn->query("UPDATE accounts SET password_changed = 1 - password_changed WHERE id = " . intval($_GET['toggle_pass'])); header("Location: accounts.php?t=" . time()); exit; }
if (isset($_GET['mark_ready'])) { 
    $id = intval($_GET['mark_ready']);
    $acc = $conn->query("SELECT note, is_available FROM accounts WHERE id = $id")->fetch_assoc();
    if ($acc && $acc['is_available'] == 0 && !empty($acc['note'])) {
        header("Location: accounts.php?blocked=note&t=" . time());
        exit;
    }
    $conn->query("UPDATE accounts SET is_available = 1, password_changed = 0 WHERE id = $id"); 
    header("Location: accounts.php?t=" . time()); 
    exit; 
}

// SẮP XẾP THEO ƯU TIÊN:
// 1. Đang thuê + hết hạn (không có ghi chú)
// 2. Đang thuê + còn hạn (không có ghi chú)
// 3. Đang thuê + hết hạn + có ghi chú
// 4. Đang thuê + còn hạn + có ghi chú
// 5. Chờ thuê + có ghi chú
// 6. Chờ thuê bình thường
$accounts = $conn->query("
    SELECT a.*, 
           (SELECT MAX(expires_at) FROM orders WHERE account_id = a.id AND status = 'paid') AS expires_at 
    FROM accounts a 
    ORDER BY 
        -- Phân nhóm ưu tiên
        CASE 
            -- 1. Đang thuê + hết hạn (không có ghi chú)
            WHEN a.is_available = 0 AND (a.note IS NULL OR a.note = '') AND (SELECT MAX(expires_at) FROM orders WHERE account_id = a.id AND status = 'paid') <= NOW() THEN 0
            -- 2. Đang thuê + còn hạn (không có ghi chú)
            WHEN a.is_available = 0 AND (a.note IS NULL OR a.note = '') AND (SELECT MAX(expires_at) FROM orders WHERE account_id = a.id AND status = 'paid') > NOW() THEN 1
            -- 3. Đang thuê + hết hạn + có ghi chú
            WHEN a.is_available = 0 AND a.note IS NOT NULL AND a.note != '' AND (SELECT MAX(expires_at) FROM orders WHERE account_id = a.id AND status = 'paid') <= NOW() THEN 2
            -- 4. Đang thuê + còn hạn + có ghi chú
            WHEN a.is_available = 0 AND a.note IS NOT NULL AND a.note != '' AND (SELECT MAX(expires_at) FROM orders WHERE account_id = a.id AND status = 'paid') > NOW() THEN 3
            -- 5. Đang thuê + có ghi chú (không có order)
            WHEN a.is_available = 0 AND a.note IS NOT NULL AND a.note != '' THEN 4
            -- 6. Đang thuê bình thường (không có order)
            WHEN a.is_available = 0 THEN 5
            -- 7. Chờ thuê + có ghi chú
            WHEN a.is_available = 1 AND a.note IS NOT NULL AND a.note != '' THEN 6
            -- 8. Chờ thuê bình thường
            ELSE 7
        END ASC,
        -- Trong cùng nhóm: sắp xếp theo thời gian hết hạn (sớm nhất lên trước)
        (SELECT MAX(expires_at) FROM orders WHERE account_id = a.id AND status = 'paid') ASC,
        -- Cuối cùng theo ID
        a.id ASC
");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý tài khoản</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<style>
        body { background: #f5f7fa; padding: 12px; font-size: 13px; }
        h2 { font-size: 18px; margin-bottom: 10px; }
        .page-container { max-width: 1280px; margin: 0 auto; }
        .form-control { height: 32px; padding: 4px 8px; font-size: 13px; }
        .form-group { margin-bottom: 8px; }
        .form-group label { font-size: 12px; margin-bottom: 2px; font-weight: 600; }
        .btn { padding: 4px 10px; font-size: 12px; }
        .btn-sm { padding: 2px 6px; font-size: 11px; }
        .table { font-size: 12px; margin-bottom: 8px; }
        .table th, .table td { padding: 6px 8px; vertical-align: middle; }
        .badge { font-size: 11px; padding: 3px 8px; }
        .ready-badge { padding: 4px 10px; border-radius: 10px; font-weight: 600; color: #fff; background: #dc3545; font-size: 11px; cursor: pointer; }
        .ready-input:checked + .ready-badge { background: #28a745; }
        .add-form { background: #fff; padding: 12px; border-radius: 8px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        .copy-btn { cursor: pointer; font-size: 10px; padding: 1px 5px; }
        small { font-size: 10px; }
        .row-expired { background: #ffe0e0 !important; } /* Đỏ nhạt - đã hết hạn */
        .row-warning { background: #fff3cd !important; } /* Vàng - sắp hết */
        .row-note { background: #e7f3ff !important; }    /* Xanh nhạt - có ghi chú */
    </style>
</head>
<body>
<div class="page-container">
    <h2>Quản lý tài khoản</h2>
    <?php include 'includes/nav.php'; ?>
    
    <?php if (isset($_GET['locked'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            ✅ Đã khóa <strong><?php echo intval($_GET['locked']); ?></strong> tài khoản có ghi chú!
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['reset_pass'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            ✅ Đã reset <strong><?php echo intval($_GET['reset_pass']); ?></strong> tài khoản "Chờ thuê" về Đổi pass = Đỏ!
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['blocked']) && $_GET['blocked'] === 'note'): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            ⚠️ Không thể chuyển tài khoản có ghi chú sang trạng thái "Chờ thuê"!
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Form thêm -->
    <form method="post" class="add-form">
        <input type="hidden" name="action" value="add">
        <div class="form-row align-items-end">
            <div class="form-group col-md-2">
                <label>Tên đăng nhập</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="form-group col-md-2">
                <label>Mật khẩu</label>
                <input type="text" name="password" class="form-control" required>
            </div>
            <div class="form-group col-md-2">
                <label>Loại</label>
                <input type="text" name="type" class="form-control" value="Unlocktool">
            </div>
            <div class="form-group col-md-2">
                <label>Ngày gia hạn</label>
                <input type="date" name="renewal_date" class="form-control">
            </div>
            <div class="form-group col-md-3">
                <label>Ghi chú</label>
                <input type="text" name="note" class="form-control">
            </div>
            <div class="form-group col-md-1">
                <button type="submit" class="btn btn-primary btn-block">Thêm</button>
            </div>
        </div>
    </form>

    <!-- Danh sách -->
    <form method="post">
        <input type="hidden" name="action" value="bulk_save">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted" style="font-size:12px;">Tổng: <?php echo $accounts->num_rows; ?> tài khoản</span>
            <div>
                <a href="?reset_pass_all" class="btn btn-danger btn-sm" onclick="return confirm('Reset tất cả tài khoản Chờ thuê về Đổi pass = Đỏ?');">🔄 Reset Pass Chờ thuê</a>
                <a href="?lock_noted" class="btn btn-secondary btn-sm" onclick="return confirm('Khóa tất cả tài khoản có ghi chú đang ở trạng thái Chờ thuê?');">🔒 Khóa TK có ghi chú</a>
                <button type="submit" class="btn btn-success btn-sm">💾 Lưu trạng thái</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover bg-white mb-0">
                <thead class="thead-light">
                    <tr>
                        <th width="40">ID</th>
                        <th style="min-width:220px;">Tài khoản / Mật khẩu</th>
                        <th width="140">Trạng thái & Thời gian</th>
                        <th>Ghi chú</th>
                        <th width="200">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $accounts->fetch_assoc()): 
                    $time_info = getTimeRemaining($row['expires_at']);
                    $is_renting = !$row['is_available'];
                    $is_expired = $is_renting && $time_info && $time_info['expired'];
                    $is_warning = $is_renting && $time_info && !$time_info['expired'] && $time_info['seconds'] < 3600; // < 1 giờ
                    $has_note = !empty($row['note']);
                    
                    $row_class = '';
                    if ($is_expired) $row_class = 'row-expired';
                    elseif ($is_warning) $row_class = 'row-warning';
                    elseif ($has_note && $row['is_available']) $row_class = 'row-note';
                ?>
                    <tr class="<?php echo $row_class; ?>">
                        <td><?php echo $row['id']; ?></td>
                        <td>
                            <div>
                                <strong>TK:</strong>
                                <?php echo htmlspecialchars($row['username']); ?>
                            </div>
                            <div class="mt-1">
                                <strong>MK:</strong>
                                <?php echo htmlspecialchars($row['password']); ?>
                            </div>
                            <div class="mt-1 d-flex align-items-center" style="gap:6px;">
                                <button type="button" class="btn btn-outline-secondary btn-sm copy-all-btn"
                                    data-copy="<?php echo htmlspecialchars($row['username'] . "\n" . $row['password']); ?>">
                                    Copy
                                </button>
                                <a href="edit_account.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Sửa</a>
                            </div>
                        </td>
                        <td>
                            <?php if ($row['is_available']): ?>
                                <span class="badge badge-success">Chờ thuê</span>
                                <?php if ($time_info && $time_info['expired']): ?>
                                    <br><small class="text-success" data-waiting="<?php echo $time_info['timestamp']; ?>">⏳ ...</small>
                                <?php else: ?>
                                    <br><small class="text-success">🆕 Mới</small>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if ($has_note): ?>
                                    <span class="badge badge-secondary">Đang thuê</span>
                                    <br><small class="text-danger">Có ghi chú - không cho vào chờ thuê</small>
                                <?php else: ?>
                                    <label class="mb-0"><input type="checkbox" class="ready-input d-none" name="ready[<?php echo $row['id']; ?>]"><span class="ready-badge">Đang thuê</span></label>
                                <?php endif; ?>
                                <?php if ($time_info && $time_info['expired']): ?>
                                    <br><small class="text-danger font-weight-bold">⚠️ HẾT HẠN</small>
                                <?php elseif ($time_info && !$time_info['expired']): ?>
                                    <br><small class="text-muted"><?php echo floor($time_info['seconds']/3600).'h '.floor(($time_info['seconds']%3600)/60).'m'; ?></small>
                                <?php else: ?>
                                    <br><small class="text-muted">-</small>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td><small><?php echo htmlspecialchars($row['note'] ?? ''); ?></small></td>
                        <td>
                            <a href="?toggle_id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">Chuyển TT</a>
                            <a href="?toggle_pass=<?php echo $row['id']; ?>" class="btn btn-sm <?php echo $row['password_changed'] ? 'btn-success' : 'btn-danger'; ?>">Đổi pass</a>
                            <br><small class="text-muted">Ngày kích hoạt: <?php echo $row['renewal_date'] ? date('d/m/Y', strtotime($row['renewal_date'])) : '-'; ?></small>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </form>
</div>
<script>
// Copy TK + MK
document.querySelectorAll('.copy-all-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        navigator.clipboard.writeText(this.dataset.copy).then(() => {
            const old = this.textContent; this.textContent = '✓'; setTimeout(() => this.textContent = old, 800);
        });
    });
});

// Đếm thời gian chờ thuê
function updateWaitingTime() {
    document.querySelectorAll('[data-waiting]').forEach(el => {
        const expiredAt = parseInt(el.dataset.waiting) * 1000;
        const waited = Date.now() - expiredAt;
        if (waited > 0) {
            const h = Math.floor(waited / 3600000);
            const m = Math.floor((waited % 3600000) / 60000);
            el.innerText = h > 0 ? `⏳ ${h}h ${m}m` : `⏳ ${m}m`;
        }
    });
}
setInterval(updateWaitingTime, 1000);
updateWaitingTime();
</script>
</body>
</html>
