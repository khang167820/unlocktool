<?php
// admin/logout.php - Đăng xuất quản trị
require 'includes/session_config.php';
session_start();
$_SESSION = [];
session_destroy();
header("Location: login.php");
exit;
?>
