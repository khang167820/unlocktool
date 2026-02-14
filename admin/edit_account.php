<?php
// admin/edit_account.php - Sửa tài khoản
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

$account = null;
if (isset($_GET['id'])) $account = $conn->query("SELECT * FROM accounts WHERE id = " . intval($_GET['id']))->fetch_assoc();
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && !isset($_POST['action'])) $account = $conn->query("SELECT * FROM accounts WHERE id = " . intval($_POST['id']))->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['update'])) {
    $id = intval($_POST['update']);
    // Chỉ cho phép cập nhật mật khẩu, loại, ghi chú và ngày ghi chú (không đổi username)
    $note_content = $conn->real_escape_string($_POST['note'] ?? '');
    $note_date = $conn->real_escape_string($_POST['note_date'] ?? '');
    $conn->query("UPDATE accounts SET password='".$conn->real_escape_string($_POST['password'])."', type='".$conn->real_escape_string($_POST['type'])."', note='".$note_content."', renewal_date=".($note_date ? "'$note_date'" : "NULL")." WHERE id=$id");
    if ($_POST['action'] === 'ready') {
        // Kiểm tra ghi chú: không cho chuyển chờ thuê nếu có ghi chú
        $check = $conn->query("SELECT note FROM accounts WHERE id=$id")->fetch_assoc();
        if ($check && !empty($check['note'])) {
            header("Location: edit_account.php?id=$id&blocked=note&t=" . time()); exit;
        }
        $conn->query("UPDATE accounts SET is_available=1, password_changed=0 WHERE id=$id");
    }
    elseif ($_POST['action'] === 'pass_changed') $conn->query("UPDATE accounts SET password_changed=1 WHERE id=$id");
    header("Location: accounts.php?t=" . time()); exit;
}

if (isset($_GET['delete'])) { $conn->query("DELETE FROM accounts WHERE id = " . intval($_GET['delete'])); header("Location: accounts.php?t=" . time()); exit; }
if (isset($_GET['reset_time'])) { $conn->query("UPDATE orders SET expires_at = NOW() WHERE account_id = " . intval($_GET['reset_time']) . " AND status = 'paid'"); header("Location: accounts.php?t=" . time()); exit; }

if (!$account) die("Không tìm thấy tài khoản.");
$blocked_msg = isset($_GET['blocked']) ? 'Không thể chuyển "Chờ thuê" vì tài khoản có ghi chú!' : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa tài khoản</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f7fa; padding: 12px; font-size: 13px; }
        h2 { font-size: 18px; margin-bottom: 10px; }
        .form-control { height: 32px; padding: 4px 8px; font-size: 13px; }
        .form-group { margin-bottom: 10px; }
        .form-group label { font-size: 12px; margin-bottom: 2px; font-weight: 600; }
        .btn { padding: 6px 12px; font-size: 12px; }
        .edit-form { background: #fff; padding: 16px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); max-width: 600px; }
    </style>
</head>
<body>
<h2>✏️ Sửa tài khoản #<?php echo $account['id']; ?></h2>
<?php if ($blocked_msg): ?>
    <div class="alert alert-danger" style="max-width:600px;"><?php echo $blocked_msg; ?></div>
<?php endif; ?>
<?php include 'includes/nav.php'; ?>

<form method="post" class="edit-form">
    <input type="hidden" name="update" value="<?php echo $account['id']; ?>">
    <div class="form-row">
        <div class="form-group col-md-6">
            <label>Tên đăng nhập</label>
            <div class="input-group">
                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($account['username']); ?>" readonly>
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary copy-btn" type="button" data-copy="<?php echo htmlspecialchars($account['username']); ?>">Copy TK</button>
                </div>
            </div>
            <small class="text-muted">Không cho phép sửa tên đăng nhập.</small>
        </div>
        <div class="form-group col-md-6">
            <label>Mật khẩu</label>
            <div class="input-group">
                <input type="text" name="password" class="form-control" value="<?php echo htmlspecialchars($account['password']); ?>" required>
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary gen-pass-btn" type="button">Đề xuất</button>
                    <button class="btn btn-outline-secondary copy-btn" type="button" data-copy="<?php echo htmlspecialchars($account['password']); ?>">Copy MK</button>
                </div>
            </div>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label>Loại</label>
            <input type="text" name="type" class="form-control" value="<?php echo htmlspecialchars($account['type']); ?>">
        </div>
        <div class="form-group col-md-6">
            <label>Ghi chú (nội dung)</label>
            <input type="text" name="note" class="form-control" value="<?php echo htmlspecialchars($account['note'] ?? ''); ?>" placeholder="Nhập ghi chú">
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label>Ngày ghi chú (lịch)</label>
            <input type="date" name="note_date" class="form-control" value="<?php echo htmlspecialchars($account['renewal_date'] ?? ''); ?>">
        </div>
    </div>
    <div class="d-flex flex-wrap" style="gap:8px;">
        <button type="submit" name="action" value="ready" class="btn btn-info">✅ Chờ thuê</button>
        <button type="submit" name="action" value="pass_changed" class="btn <?php echo $account['password_changed'] ? 'btn-success' : 'btn-danger'; ?>">🔐 Đổi pass</button>
        <button type="submit" name="action" value="update" class="btn btn-primary">💾 Cập nhật</button>
        <a href="accounts.php" class="btn btn-secondary">← Quay lại</a>
        <a href="?delete=<?php echo $account['id']; ?>" class="btn btn-danger" onclick="return confirm('Xóa?')">🗑 Xóa</a>
        <a href="?reset_time=<?php echo $account['id']; ?>" class="btn btn-warning">⏰ Reset TG</a>
    </div>
</form>
<script>
document.querySelectorAll('.copy-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const text = this.dataset.copy || '';
        navigator.clipboard.writeText(text).then(() => {
            const old = this.textContent;
            this.textContent = '✓ Copied';
            setTimeout(() => this.textContent = old, 800);
        });
    });
});

// Đề xuất mật khẩu dạng Unlock123 (random 100-999)
document.querySelectorAll('.gen-pass-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const input = this.closest('.input-group').querySelector('input[name="password"]');
        if (!input) return;
        const rand = Math.floor(100 + Math.random() * 900); // 100-999
        const suggest = 'Unlock' + rand;
        input.value = suggest;
        // Cập nhật nút copy MK cạnh nó
        const copyBtn = this.parentElement.querySelector('.copy-btn');
        if (copyBtn) copyBtn.dataset.copy = suggest;
    });
});
</script>
</body>
</html>
