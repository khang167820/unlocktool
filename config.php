<?php
// config.php - Cấu hình cơ sở dữ liệu và PayOS
// Thông tin kết nối cơ sở dữ liệu MySQL
$db_host = 'localhost';
$db_name = 'u620980434_yenz';  // Đổi tên DB nếu cần
$db_user = 'u620980434_yenz';       // Đổi user nếu cần
$db_pass = 'T@i010196@';           // Đổi mật khẩu nếu cần

// Thông tin cấu hình cổng thanh toán PayOS (thay bằng thông tin của bạn nếu cần)
$payos_client_id = '4fe73e04-c39c-44b6-b02a-9f2f8c2e67c3';
$payos_api_key   = '4145c223-db04-4a72-8ea1-392e70f17f9a';
$payos_checksum_key = 'd4cc99da53923ccf71ddfadf6c2dca409d6f26d963f6f1b19a5540ef0ad34826';

// URL cơ bản của website (dùng để thiết lập returnUrl/cancelUrl cho PayOS và link webhook).
// Bao gồm giao thức (http/https) và kết thúc với dấu /
$base_url = 'https://www.unlocktool.us/';  // Thay bằng URL thực tế của trang


// Kết nối cơ sở dữ liệu với error handling tốt hơn
$conn = null;
$db_connected = false;
try {
    $conn = @new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        error_log("Database connection error: " . $conn->connect_error);
        $conn = null;
        $db_connected = false;
    } else {
        $db_connected = true;
        $conn->set_charset("utf8");
        
        // Đặt timezone cho MySQL giống PHP (Việt Nam UTC+7)
        $conn->query("SET time_zone = '+07:00'");
        
        // Tự động đóng kết nối khi script kết thúc
        register_shutdown_function(function() use (&$conn) {
            if ($conn && !$conn->connect_error) {
                $conn->close();
            }
        });
    }
} catch (Exception $e) {
    error_log("Database connection exception: " . $e->getMessage());
    $conn = null;
    $db_connected = false;
}

// Thiết lập múi giờ Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');
?>
