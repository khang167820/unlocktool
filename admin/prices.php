<?php
// admin/prices.php - Quản lý gói thuê
require 'includes/session_config.php';
session_start();
require '../config.php';
require '../helpers.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$message = $error = "";
if (isset($_GET['delete_id'])) { $conn->query("DELETE FROM prices WHERE id=" . intval($_GET['delete_id'])); $message = "Đã xóa gói thuê"; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $hours = intval($_POST['hours']);
    $price = intval($_POST['price']);
    if ($hours <= 0 || $price <= 0) { $error = "Giá trị không hợp lệ"; }
    else {
        $id === 0 ? $conn->query("INSERT INTO prices (hours, price) VALUES ($hours, $price)") : $conn->query("UPDATE prices SET hours=$hours, price=$price WHERE id=$id");
        header("Location: prices.php"); exit;
    }
}

$prices = $conn->query("SELECT * FROM prices ORDER BY hours");
$edit = isset($_GET['edit_id']) ? $conn->query("SELECT * FROM prices WHERE id=" . intval($_GET['edit_id']))->fetch_assoc() : null;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Gói thuê</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body { background: #f5f7fa; padding: 12px; font-size: 13px; }
        h2 { font-size: 18px; margin-bottom: 10px; }
        .form-control { height: 32px; padding: 4px 8px; font-size: 13px; }
        .btn { padding: 4px 10px; font-size: 12px; }
        .btn-sm { padding: 2px 6px; font-size: 11px; }
        .table { font-size: 12px; }
        .table th, .table td { padding: 6px 10px; }
        .add-form { background: #fff; padding: 12px; border-radius: 8px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); max-width: 500px; }
        .alert { padding: 8px 12px; font-size: 13px; margin-bottom: 10px; }
    </style>
</head>
<body>
<h2>💰 Quản lý Gói thuê</h2>
<?php include 'includes/nav.php'; ?>

<?php if ($message): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

<form method="post" class="add-form">
    <div class="form-row align-items-end">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?php echo $edit['id']; ?>"><?php endif; ?>
        <div class="form-group col-5 mb-0">
            <label style="font-size:12px;">Thời lượng (giờ)</label>
            <input type="number" name="hours" class="form-control" value="<?php echo $edit['hours'] ?? ''; ?>" required>
        </div>
        <div class="form-group col-5 mb-0">
            <label style="font-size:12px;">Giá (VND)</label>
            <input type="number" name="price" class="form-control" value="<?php echo $edit['price'] ?? ''; ?>" required>
        </div>
        <div class="form-group col-2 mb-0">
            <button type="submit" class="btn btn-primary btn-block"><?php echo $edit ? 'Lưu' : 'Thêm'; ?></button>
        </div>
    </div>
    <?php if ($edit): ?><a href="prices.php" class="btn btn-secondary btn-sm mt-2">Hủy</a><?php endif; ?>
</form>

<table class="table table-bordered bg-white" style="max-width:500px;">
    <thead class="thead-light"><tr><th>ID</th><th>Thời gian</th><th>Giá</th><th width="100">Thao tác</th></tr></thead>
    <tbody>
    <?php while ($row = $prices->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo displayPackageName($row['hours']); ?></td>
            <td><?php echo formatMoney($row['price']); ?></td>
            <td>
                <a href="?edit_id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">Sửa</a>
                <a href="?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa?')">Xóa</a>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
</body>
</html>
