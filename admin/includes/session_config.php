<?php
// admin/includes/session_config.php
// Cấu hình session chung cho tất cả trang admin
// Include file này TRƯỚC session_start()

ini_set('session.cookie_secure', '1');       // Chỉ gửi cookie qua HTTPS
ini_set('session.cookie_httponly', '1');      // Không cho JS truy cập cookie
ini_set('session.cookie_samesite', 'Lax');   // Cho phép redirect cùng site
ini_set('session.use_strict_mode', '1');     // Bảo mật session ID
