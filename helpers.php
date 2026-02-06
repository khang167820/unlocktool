<?php
/**
 * Helpers - Các hàm dùng chung cho toàn bộ website
 */

/**
 * Che bớt chuỗi, chỉ hiển thị ký tự đầu
 */
function mask($str, $visible = 1) {
    $len = strlen($str);
    if ($len <= $visible) return str_repeat('*', $len);
    return substr($str, 0, $visible) . str_repeat('*', $len - $visible);
}

/**
 * Chuyển đổi số giờ thành tên gói thuê dễ đọc
 */
function displayPackageName($hours) {
    $hours = (int)$hours;
    return match($hours) {
        24  => '1 ngày',
        48  => '2 ngày',
        72  => '3 ngày',
        168 => '7 ngày',
        360 => '14 ngày',
        720 => '30 ngày',
        default => $hours . ' giờ'
    };
}

/**
 * Lấy IP thực của người dùng
 */
function getClientIP() {
    return $_SERVER['HTTP_CLIENT_IP'] 
        ?? $_SERVER['HTTP_X_FORWARDED_FOR'] 
        ?? $_SERVER['REMOTE_ADDR'];
}

/**
 * Format số tiền theo định dạng Việt Nam
 */
function formatMoney($amount) {
    return number_format($amount, 0, ',', '.') . ' VNĐ';
}

/**
 * Tính thời gian còn lại và format
 */
function getTimeRemaining($expires_at) {
    if (!$expires_at) return null;
    
    $expires_time = strtotime($expires_at);
    $now = time();
    
    if ($expires_time <= $now) {
        return ['expired' => true, 'text' => 'Đã hết hạn', 'timestamp' => $expires_time];
    }
    
    $remain = $expires_time - $now;
    $h = floor($remain / 3600);
    $m = floor(($remain % 3600) / 60);
    $s = $remain % 60;
    
    return [
        'expired' => false,
        'text' => "{$h}h {$m}m {$s}s",
        'timestamp' => $expires_time,
        'seconds' => $remain
    ];
}

