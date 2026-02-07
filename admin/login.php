<?php
// admin/login.php - Trang đăng nhập quản trị

// Cấu hình session cookie trước khi start
ini_set('session.cookie_secure', '1');       // HTTPS only
ini_set('session.cookie_httponly', '1');      // Không cho JS truy cập
ini_set('session.cookie_samesite', 'Lax');   // Cho phép redirect cùng site
ini_set('session.use_strict_mode', '1');     // Bảo mật session ID

session_start();
require '../config.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $_POST['password'];
    $res = $conn->query("SELECT * FROM admin WHERE username='{$user}'");
    if ($res && $res->num_rows > 0) {
        $admin = $res->fetch_assoc();
        if (password_verify($pass, $admin['password'])) {
            // Regenerate session ID để tránh session fixation
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            
            // Đảm bảo session được lưu trước khi redirect
            session_write_close();
            
            header("Location: index.php");
            exit;
        } else {
            $error = "Mật khẩu không đúng.";
        }
    } else {
        $error = "Tên đăng nhập không tồn tại.";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Admin Login</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="p-4">
<div class="container" style="max-width: 400px;">
  <h2 class="mb-4">Đăng nhập Admin</h2>
  <?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
  <?php endif; ?>
  <form method="post" action="login.php">
    <div class="form-group">
      <label>Tên đăng nhập:</label>
      <input type="text" name="username" class="form-control" required>
    </div>
    <div class="form-group">
      <label>Mật khẩu:</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Đăng nhập</button>
  </form>
</div>
</body>
</html>
