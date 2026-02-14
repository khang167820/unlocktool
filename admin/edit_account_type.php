<?php
// admin/edit_account_type.php - Quản lý loại tài khoản
require '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['type'])) {
    $id = intval($_POST['id']);
    $type = $conn->real_escape_string($_POST['type']);
    $conn->query("UPDATE accounts SET type='$type' WHERE id=$id");
    header("Location: edit_account_type.php?success=1");
    exit;
}

$result = $conn->query("SELECT id, username, type FROM accounts ORDER BY id");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý loại tài khoản</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="p-4">
<div class="container">
    <h2 class="mb-4">Quản lý loại tài khoản</h2>
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Cập nhật thành công!</div>
    <?php endif; ?>
    <table class="table table-bordered table-sm">
        <thead class="thead-light">
            <tr>
                <th>ID</th>
                <th>Tên đăng nhập</th>
                <th>Loại tài khoản</th>
                <th>Cập nhật</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <form method="post">
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td>
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <input type="text" name="type" class="form-control form-control-sm" value="<?php echo htmlspecialchars($row['type']); ?>">
                    </td>
                    <td>
                        <button type="submit" class="btn btn-sm btn-primary">Lưu</button>
                    </td>
                </form>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
