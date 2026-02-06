<?php
// change_admin_password.php
require '../config.php';

$username = 'admin'; // hoặc thay tên admin khác nếu có
$newPassword = 'Tkk123@'; // ✅ Đổi thành mật khẩu mới mong muốn

$hashed = password_hash($newPassword, PASSWORD_DEFAULT);
$conn->query("UPDATE admin SET password = '{$hashed}' WHERE username = '{$username}'");

echo "✅ Mật khẩu admin đã được cập nhật.";
?>
