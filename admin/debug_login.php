<?php
// debug_login.php - Kiểm tra tại sao login bị loop
// Upload file này lên admin/ và truy cập: unlocktool.us/admin/debug_login.php
// XÓA FILE NÀY SAU KHI DEBUG XONG!

echo "<h2>🔍 Debug Admin Login</h2>";
echo "<pre>";

// 1. Kiểm tra session
echo "=== SESSION ===\n";
session_start();
echo "Session ID: " . session_id() . "\n";
echo "Session save path: " . session_save_path() . "\n";
echo "Session status: " . session_status() . " (1=disabled, 2=active)\n";
echo "Session data: ";
print_r($_SESSION);
echo "\n";

// 2. Kiểm tra database
echo "\n=== DATABASE ===\n";
try {
    require '../config.php';
    echo "DB connection: OK\n";
    
    // 3. Kiểm tra bảng admin
    $res = $conn->query("SELECT id, username, password FROM admin");
    if ($res && $res->num_rows > 0) {
        echo "Admin table: OK (" . $res->num_rows . " rows)\n";
        while ($row = $res->fetch_assoc()) {
            echo "  - ID: {$row['id']}, Username: {$row['username']}\n";
            echo "  - Password hash: " . substr($row['password'], 0, 20) . "...\n";
            
            // Kiểm tra password - thử với mật khẩu bạn nhập
            $test_passwords = ['Tkk123@', 'admin', '123456'];
            foreach ($test_passwords as $tp) {
                $result = password_verify($tp, $row['password']) ? '✅ MATCH' : '❌ NO';
                echo "  - Test '$tp': $result\n";
            }
        }
    } else {
        echo "⚠️ Admin table: EMPTY hoặc KHÔNG TỒN TẠI!\n";
        // Thử tạo bảng và admin
        echo "Thử kiểm tra bảng...\n";
        $tables = $conn->query("SHOW TABLES LIKE 'admin'");
        echo "Bảng 'admin' tồn tại: " . ($tables->num_rows > 0 ? 'CÓ' : 'KHÔNG') . "\n";
    }
} catch (Exception $e) {
    echo "❌ DB Error: " . $e->getMessage() . "\n";
}

// 4. Kiểm tra PHP config
echo "\n=== PHP CONFIG ===\n";
echo "PHP version: " . phpversion() . "\n";
echo "session.cookie_secure: " . ini_get('session.cookie_secure') . "\n";
echo "session.cookie_httponly: " . ini_get('session.cookie_httponly') . "\n";
echo "session.cookie_samesite: " . ini_get('session.cookie_samesite') . "\n";
echo "session.use_cookies: " . ini_get('session.use_cookies') . "\n";
echo "session.use_only_cookies: " . ini_get('session.use_only_cookies') . "\n";

// 5. Kiểm tra cookie
echo "\n=== COOKIES ===\n";
echo "Cookies received: ";
print_r($_COOKIE);

echo "</pre>";

echo "<hr>";
echo "<p><strong>Hướng dẫn:</strong> Copy toàn bộ kết quả trên và gửi cho tôi để debug.</p>";
?>
