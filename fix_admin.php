<?php
// fix_admin.php - Xóa .htaccess gây chặn admin
// Upload file này vào THƯ MỤC GỐC (public_html), KHÔNG phải admin/
// Truy cập: https://unlocktool.us/fix_admin.php

echo "<h2>Fix Admin Authentication</h2>";

$adminDir = __DIR__ . '/admin';
$htaccess = $adminDir . '/.htaccess';
$htpasswd = $adminDir . '/.htpasswd';

echo "<p><strong>Admin dir:</strong> $adminDir</p>";
echo "<p><strong>.htaccess exists:</strong> " . (file_exists($htaccess) ? '✅ YES - CẦN XÓA!' : '❌ No') . "</p>";
echo "<p><strong>.htpasswd exists:</strong> " . (file_exists($htpasswd) ? '✅ YES - CẦN XÓA!' : '❌ No') . "</p>";

// Nội dung .htaccess nếu có
if (file_exists($htaccess)) {
    echo "<pre style='background:#fee;padding:10px;'>" . htmlspecialchars(file_get_contents($htaccess)) . "</pre>";
}

// Xóa nếu có tham số ?fix=1
if (isset($_GET['fix']) && $_GET['fix'] == '1') {
    $deleted = [];
    if (file_exists($htaccess)) {
        unlink($htaccess);
        $deleted[] = '.htaccess';
    }
    if (file_exists($htpasswd)) {
        unlink($htpasswd);
        $deleted[] = '.htpasswd';
    }
    if (count($deleted) > 0) {
        echo "<p style='color:green;font-size:20px;'>✅ Đã xóa: " . implode(', ', $deleted) . "</p>";
        echo "<p>👉 <a href='/admin/login.php'>Test login ngay</a></p>";
    } else {
        echo "<p style='color:orange;'>⚠️ Không có file nào cần xóa.</p>";
    }
} else {
    echo "<p>👉 <a href='?fix=1' style='color:red;font-size:18px;'>BẤM ĐÂY ĐỂ XÓA .htaccess</a></p>";
}

// Kiểm tra session
echo "<h3>Session Test</h3>";
session_start();
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session status: " . session_status() . "</p>";
$_SESSION['test'] = 'ok';
echo "<p>Session write test: ✅</p>";

// Liệt kê tất cả file trong admin/ (kể cả ẩn)
echo "<h3>All files in admin/ (including hidden)</h3>";
echo "<ul>";
foreach (scandir($adminDir) as $f) {
    if ($f === '.' || $f === '..') continue;
    $full = $adminDir . '/' . $f;
    $size = is_file($full) ? filesize($full) : 'DIR';
    $hidden = (substr($f, 0, 1) === '.') ? ' 🔴 HIDDEN' : '';
    echo "<li><code>$f</code> ($size)$hidden</li>";
}
echo "</ul>";
?>
