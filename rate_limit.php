<?php
// rate_limit.php
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$storageDir = __DIR__ . '/rate_limit_logs';
$time = time();
$window = 120; // 2 phút (120 giây)
$limit = 50; // 50 request
$banTime = 300; // 5 phút (300 giây)

// Tạo thư mục lưu log nếu chưa có
if (!is_dir($storageDir)) {
 mkdir($storageDir, 0755, true);
}

$logFile = $storageDir . '/' . md5($ip) . '.json';

// Đọc dữ liệu cũ
$data = [
 'requests' => [],
 'banned_until' => 0,
];

if (file_exists($logFile)) {
 $json = file_get_contents($logFile);
 $old = json_decode($json, true);
 if (is_array($old)) {
 $data = array_merge($data, $old);
 }
}

// Nếu đang bị cấm
if ($data['banned_until'] > $time) {
 header('HTTP/1.1 429 Too Many Requests');
 header('Retry-After: ' . ($data['banned_until'] - $time));
 exit('Bạn đã gửi quá nhiều yêu cầu, vui lòng thử lại sau vài phút.');
}

// Lọc request trong khoảng 2 phút gần nhất
$data['requests'] = array_filter(
 $data['requests'],
 function ($t) use ($time, $window) {
 return ($t > $time - $window);
 }
);

// Thêm request hiện tại
$data['requests'][] = $time;

// Nếu vượt limit => ban 5 phút
if (count($data['requests']) > $limit) {
 $data['banned_until'] = $time + $banTime;
}

// Lưu lại file
file_put_contents($logFile, json_encode($data));

if ($data['banned_until'] > $time) {
 header('HTTP/1.1 429 Too Many Requests');
 header('Retry-After: ' . ($data['banned_until'] - $time));
 exit('Bạn đã gửi quá nhiều yêu cầu, vui lòng thử lại sau vài phút.');
}