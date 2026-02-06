<?php
// admin/logout.php - Đăng xuất quản trị
session_start();
$_SESSION = [];
session_destroy();
header("Location: login.php");
exit;
?>
