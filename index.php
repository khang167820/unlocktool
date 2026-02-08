<?php
require __DIR__ . '/rate_limit.php';
require 'config.php';
require 'helpers.php';

// Tắt cache hoàn toàn để trang chủ luôn cập nhật trạng thái TK mới nhất
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

// Tự động cập nhật trạng thái: Nếu hết hạn thuê thì chuyển về Chờ thuê
// ĐÃ TẮT - Admin phải chuyển tài khoản thủ công để tránh lỗi password_changed
/*
$conn->query("
    UPDATE accounts a 
    LEFT JOIN (
        SELECT account_id, MAX(expires_at) AS max_expires
        FROM orders WHERE status='paid'
        GROUP BY account_id
    ) o ON a.id = o.account_id
    SET a.is_available = 1
    WHERE a.is_available = 0 
    AND (o.max_expires IS NULL OR o.max_expires <= NOW())
    AND (a.note IS NULL OR a.note = '')
");
*/

// Query danh sách tài khoản - ƯU TIÊN acc chờ thuê LÂU NHẤT
$result = $conn->query("
    SELECT a.*, o.expires_at
    FROM accounts a
    LEFT JOIN (
        SELECT account_id, MAX(expires_at) AS expires_at
        FROM orders WHERE status='paid'
        GROUP BY account_id
    ) o ON a.id = o.account_id
    ORDER BY 
        a.is_available DESC,
        CASE 
            WHEN a.is_available = 1 AND o.expires_at IS NOT NULL THEN 0  -- Chờ thuê có lịch sử: ưu tiên cao
            WHEN a.is_available = 1 AND o.expires_at IS NULL THEN 1     -- Chờ thuê mới: sau đó
            ELSE 2                                                        -- Đang thuê: cuối
        END,
        o.expires_at ASC,  -- Hết hạn lâu nhất = chờ lâu nhất = lên đầu
        a.id ASC
");

$prices_result = $conn->query("SELECT id, hours, price FROM prices ORDER BY hours ASC");

// Lịch sử thuê 30 ngày theo IP
$user_ip = getClientIP();
$time_limit = date('Y-m-d H:i:s', time() - 30 * 86400);
$stmt = $conn->prepare("
    SELECT o.*, a.username, a.password, a.type, a.password_changed, a.is_available
    FROM orders o
    JOIN accounts a ON o.account_id = a.id
    WHERE o.ip_address = ? AND o.created_at >= ? AND o.status = 'paid'
    ORDER BY o.created_at DESC
");
$stmt->bind_param("ss", $user_ip, $time_limit);
$stmt->execute();
$history_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google-site-verification" content="sopE0oKWWzmSq9xXTXegYWh6bE4pdp_FbESZZhTtnmQ" />
    
    <!-- SEO Meta Tags -->
    <title>Thuê Tài Khoản UnlockTool Giá Rẻ 2026 - Tự Động 24/7 | UnlockTool.us</title>
    <meta name="description" content="Thuê tài khoản UnlockTool giá rẻ từ 8.000đ. Hệ thống tự động 24/7, nhận tài khoản ngay sau thanh toán. FRP Samsung, Mi Account Xiaomi, iCloud Bypass iPhone.">
    <meta name="keywords" content="thuê unlocktool, unlocktool giá rẻ, thuê tài khoản unlocktool, unlocktool, frp samsung, mi account xiaomi, icloud bypass">
    <meta name="author" content="UnlockTool.us">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://www.unlocktool.us/">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://www.unlocktool.us/">
    <meta property="og:title" content="Thuê Tài Khoản UnlockTool Giá Rẻ 2026 - Tự Động 24/7">
    <meta property="og:description" content="Thuê tài khoản UnlockTool giá rẻ từ 8.000đ. Hệ thống tự động 24/7, nhận tài khoản ngay sau thanh toán.">
    <meta property="og:image" content="https://www.unlocktool.us/images/unlocktool-banner.jpg">
    <meta property="og:locale" content="vi_VN">
    <meta property="og:site_name" content="UnlockTool.us">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Thuê Tài Khoản UnlockTool Giá Rẻ 2026">
    <meta name="twitter:description" content="Thuê tài khoản UnlockTool giá rẻ từ 8.000đ. Hệ thống tự động 24/7.">
    <meta name="twitter:image" content="https://www.unlocktool.us/images/unlocktool-banner.jpg">
    
    <!-- Structured Data - Organization -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "UnlockTool.us",
        "url": "https://www.unlocktool.us",
        "logo": "https://www.unlocktool.us/images/logo.png",
        "description": "Dịch vụ cho thuê tài khoản UnlockTool uy tín, giá rẻ tại Việt Nam",
        "address": {
            "@type": "PostalAddress",
            "addressCountry": "VN"
        }
    }
    </script>
    
    <!-- Structured Data - WebSite with SearchAction -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "UnlockTool.us",
        "url": "https://www.unlocktool.us",
        "description": "Thuê tài khoản UnlockTool giá rẻ, tự động 24/7"
    }
    </script>
    
    <!-- Structured Data - Service -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Thuê Tài Khoản UnlockTool",
        "provider": {
            "@type": "Organization",
            "name": "UnlockTool.us"
        },
        "description": "Dịch vụ cho thuê tài khoản UnlockTool premium theo giờ, ngày với giá rẻ nhất",
        "areaServed": "VN",
        "offers": {
            "@type": "AggregateOffer",
            "lowPrice": "8000",
            "highPrice": "180000",
            "priceCurrency": "VND",
            "offerCount": "6"
        },
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "4.9",
            "reviewCount": "6500"
        }
    }
    </script>
    
    <link rel="icon" type="image/x-icon" href="/favicon.ico?v=1">
    
    <!-- Preconnect CDN origins -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://code.jquery.com" crossorigin>
    
    <!-- CSS (critical) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    
    <!-- Font Awesome: preload + font-display swap -->
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    
    <!-- JS moved to bottom of body to eliminate render-blocking -->
    <style>
        /* Font-display swap for web fonts */
        @font-face { font-family: 'Font Awesome 6 Free'; font-display: swap; }
        @font-face { font-family: 'Font Awesome 6 Brands'; font-display: swap; }
        :root {
            --primary: #007bff;
            --success: #28a745;
            --danger: #dc3545;
        }
        body {
            background: url('https://unlocktool.us/3b75cb84-2ea8-4a5f-bbee-3f3d32cc64693782334b80e7292aba0fde71e1fd9267.webp') no-repeat center top fixed;
            background-size: cover;
            min-height: 100vh;
            background-color: #f0f0f0;
            padding-top: 80px;
        }
        /* JPG fallback for browsers without WebP */
        @supports not (background-image: url('data:image/webp;base64,UklGRh4AAABXRUJQVlA4TBEAAAAvAAAAAAfQ//73v/+BiTjU7QIA')) {
            body {
                background-image: url('https://unlocktool.us/3b75cb84-2ea8-4a5f-bbee-3f3d32cc64693782334b80e7292aba0fde71e1fd9267.jpg');
            }
        }
        
        /* Header Styles */
        .main-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(240, 248, 255, 0.98) 100%);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-bottom: 2px solid rgba(0, 123, 255, 0.2);
        }
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 12px 20px;
        }
        .header-row-1 {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }
        .logo-section {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #333;
            flex-shrink: 0;
        }
        .logo-section:hover {
            text-decoration: none;
            color: var(--primary);
        }
        .logo-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--primary), #0056b3);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            box-shadow: 0 3px 10px rgba(0, 123, 255, 0.3);
        }
        .logo-text {
            display: flex;
            flex-direction: column;
        }
        .logo-title {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--primary);
            margin: 0;
            line-height: 1.2;
        }
        .logo-subtitle {
            font-size: 0.7rem;
            color: #666;
            margin: 0;
            font-weight: 600;
        }
        
        /* Header Center - History & Search */
        .header-center-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            justify-content: center;
        }
        .header-history-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            background: linear-gradient(135deg, #f8c346, #f0a400);
            color: #1f1a0d;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            box-shadow: 0 3px 10px rgba(240, 164, 0, 0.3);
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .header-history-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(240, 164, 0, 0.4);
        }
        .header-search-form {
            display: flex;
            align-items: center;
        }
        .header-search-input {
            width: 150px;
            padding: 10px 12px;
            border: 2px solid #e0e5ec;
            border-right: none;
            border-radius: 10px 0 0 10px;
            font-size: 0.85rem;
            font-weight: 500;
            outline: none;
            transition: all 0.2s ease;
        }
        .header-search-input:focus {
            border-color: var(--primary);
        }
        .header-search-input::placeholder {
            color: #aaa;
            font-size: 0.8rem;
        }
        .header-search-btn {
            padding: 10px 16px;
            background: linear-gradient(135deg, var(--primary), #0056b3);
            color: white;
            border: none;
            border-radius: 0 10px 10px 0;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .header-search-btn:hover {
            background: linear-gradient(135deg, #0056b3, #004494);
        }
        
        /* Header Right - Support, Phone, Zalo */
        .header-right-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }
        .header-support-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 14px;
            background: linear-gradient(135deg, #28a745, #1e7e34);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            box-shadow: 0 3px 10px rgba(40, 167, 69, 0.3);
            transition: all 0.2s ease;
        }
        .header-support-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
            color: white;
            text-decoration: none;
        }
        .hotline-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 14px;
            background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.85rem;
            text-decoration: none;
            box-shadow: 0 3px 10px rgba(238, 90, 111, 0.3);
            transition: all 0.2s ease;
        }
        .hotline-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(238, 90, 111, 0.4);
            color: white;
            text-decoration: none;
        }
        .zalo-header-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            background: linear-gradient(135deg, #0068ff, #0052cc);
            border: none;
            border-radius: 10px;
            text-decoration: none;
            box-shadow: 0 3px 10px rgba(0, 104, 255, 0.3);
            transition: all 0.2s ease;
        }
        .zalo-header-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(0, 104, 255, 0.4);
        }
        .zalo-header-btn img {
            width: 26px;
            height: 26px;
        }
        
        /* Contact Dropdown */
        .contact-dropdown {
            position: relative;
            display: inline-block;
        }
        .contact-dropdown-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            background: linear-gradient(135deg, #28a745, #1e7e34);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            box-shadow: 0 3px 10px rgba(40, 167, 69, 0.3);
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .contact-dropdown-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }
        .contact-dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: calc(100% + 8px);
            background: white;
            min-width: 280px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            border-radius: 12px;
            z-index: 1001;
            overflow: hidden;
            border: 1px solid #e0e5ec;
        }
        .contact-dropdown-content.show {
            display: block;
        }
        .contact-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            border-bottom: 1px solid #f0f0f0;
            text-decoration: none;
            color: #333;
            transition: background-color 0.2s ease;
        }
        .contact-item:last-child {
            border-bottom: none;
        }
        .contact-item:hover {
            background-color: #f8f9fa;
            text-decoration: none;
            color: #333;
        }
        .contact-item-info {
            flex: 1;
        }
        .contact-item-title {
            font-weight: 700;
            font-size: 0.95rem;
            color: #1f2a44;
            margin-bottom: 4px;
        }
        .contact-item-desc {
            font-size: 0.8rem;
            color: #666;
        }
        .contact-item-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            border: none;
            transition: all 0.2s ease;
            flex-shrink: 0;
            pointer-events: none;
        }
        .contact-item-btn.zalo {
            background: linear-gradient(135deg, #0068ff, #0052cc);
            color: white;
        }
        .contact-item-btn.zalo:hover {
            background: linear-gradient(135deg, #0052cc, #003d99);
            transform: scale(1.05);
        }
        .contact-item-btn.phone {
            background: linear-gradient(135deg, #28a745, #1e7e34);
            color: white;
        }
        .contact-item-btn.phone:hover {
            background: linear-gradient(135deg, #1e7e34, #155724);
            transform: scale(1.05);
        }
        .contact-item-btn.group {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
        }
        .contact-item-btn.group:hover {
            background: linear-gradient(135deg, #138496, #117a8b);
            transform: scale(1.05);
        }
        
        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            background: linear-gradient(135deg, var(--primary), #0056b3);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-size: 1.2rem;
            cursor: pointer;
            min-width: 60px;
            box-shadow: 0 3px 10px rgba(0, 123, 255, 0.3);
            transition: all 0.2s ease;
        }
        .mobile-menu-toggle:hover,
        .mobile-menu-toggle:active {
            background: linear-gradient(135deg, #0056b3, #004494);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
        }
        .mobile-menu {
            display: none;
            flex-direction: column;
            gap: 10px;
            padding-top: 15px;
            margin-top: 15px;
            border-top: 1px solid #e0e5ec;
        }
        .mobile-menu.show {
            display: flex;
        }
        .mobile-history-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px;
            background: linear-gradient(135deg, #f8c346, #f0a400);
            color: #1f1a0d;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
        }
        .mobile-search-form {
            display: flex;
            gap: 0;
        }
        .mobile-search-form input {
            flex: 1;
            padding: 12px;
            border: 2px solid #e0e5ec;
            border-right: none;
            border-radius: 10px 0 0 10px;
            font-size: 0.9rem;
            outline: none;
        }
        .mobile-search-form button {
            padding: 12px 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 0 10px 10px 0;
            font-weight: 600;
        }
        .mobile-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            background: #f8f9fa;
            border-radius: 10px;
            color: #333;
            text-decoration: none;
            font-weight: 600;
        }
        .mobile-link:hover {
            background: #e9ecef;
            text-decoration: none;
            color: var(--primary);
        }
        
        @media (max-width: 992px) {
            .header-center-actions {
                display: none;
            }
            .header-right-actions {
                display: none;
            }
            .mobile-menu-toggle {
                display: block;
            }
        }
        
        @media (max-width: 768px) {
            body {
                padding-top: 0 !important;
            }
            .main-header {
                position: relative !important;
                min-height: auto;
                padding: 0;
            }
            .header-row-1 {
                min-height: 40px;
            }
            .logo-title {
                font-size: 0.9rem;
            }
            .logo-subtitle {
                display: none;
            }
            .logo-icon {
                width: 28px;
                height: 28px;
                font-size: 14px;
            }
            .header-container {
                padding: 8px 12px;
            }
            .overlay {
                margin-top: 10px !important;
            }
        }
        
        @media (min-width: 993px) {
            .mobile-menu {
                display: none !important;
            }
        }
        .overlay {
            background-color: rgba(255, 255, 255, 0.92);
            padding: 30px;
            margin: 40px auto 80px;
            border-radius: 12px;
            max-width: 1200px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }
        table { box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        thead th { background-color: #1976D2; color: #ffffff; }
        .badge-success { background-color: #28a745; color: #003d00; font-weight: 700; padding: 6px 12px; font-size: 0.875rem; display: inline-block; line-height: 1.5; border-radius: 4px; }
        .badge-danger { background-color: var(--danger); }
        
        /* Floating Contact Button */
        .floating-contact-btn {
            position: fixed;
            bottom: 25px;
            left: 25px;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00a8ff, #0097e6, #0068ff);
            border: 4px solid white;
            box-shadow: 0 8px 30px rgba(0, 168, 255, 0.5),
                        0 0 20px rgba(0, 168, 255, 0.3),
                        inset 0 2px 10px rgba(255, 255, 255, 0.3);
            z-index: 999;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            color: white;
            font-size: 28px;
            animation: pulse-glow 2s ease-in-out infinite;
            will-change: transform;
        }
        @keyframes pulse-glow {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.08);
            }
        }
        .floating-contact-btn:hover {
            transform: scale(1.15);
            background: linear-gradient(135deg, #00b8ff, #00a8ff, #0088ff);
            box-shadow: 0 10px 40px rgba(0, 168, 255, 0.7),
                        0 0 30px rgba(0, 168, 255, 0.5),
                        0 0 60px rgba(0, 168, 255, 0.4),
                        inset 0 2px 10px rgba(255, 255, 255, 0.4);
            animation: none;
        }
        .floating-contact-btn.active {
            transform: rotate(45deg) scale(1.1);
            background: linear-gradient(135deg, #ff4757, #ff3838, #ee5a6f);
            border-color: white;
            box-shadow: 0 8px 40px rgba(255, 71, 87, 0.6),
                        0 0 30px rgba(255, 71, 87, 0.4),
                        0 0 60px rgba(255, 71, 87, 0.3),
                        inset 0 2px 10px rgba(255, 255, 255, 0.3);
            animation: none;
        }
        .floating-contact-btn.active:hover {
            transform: rotate(45deg) scale(1.15);
            box-shadow: 0 10px 50px rgba(255, 71, 87, 0.7),
                        0 0 40px rgba(255, 71, 87, 0.5),
                        0 0 70px rgba(255, 71, 87, 0.4),
                        inset 0 2px 10px rgba(255, 255, 255, 0.4);
        }
        
        /* Floating Contact Dropdown */
        .floating-contact-dropdown {
            position: fixed;
            bottom: 105px;
            left: 25px;
            min-width: 320px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25),
                        0 0 20px rgba(0, 168, 255, 0.1);
            z-index: 998;
            opacity: 0;
            transform: translateY(20px) scale(0.9);
            pointer-events: none;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            border: 2px solid rgba(0, 168, 255, 0.2);
            overflow: hidden;
        }
        .floating-contact-dropdown.show {
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: all;
        }
        .floating-contact-dropdown-header {
            background: linear-gradient(135deg, #00a8ff, #0097e6, #0068ff);
            color: white;
            padding: 18px 20px;
            font-weight: 700;
            font-size: 1.05rem;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 10px rgba(0, 168, 255, 0.3);
        }
        .floating-contact-dropdown-header i {
            font-size: 1.2rem;
        }
        .floating-contact-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid #f0f0f0;
            text-decoration: none;
            color: #333;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .floating-contact-item:last-child {
            border-bottom: none;
        }
        .floating-contact-item:hover {
            background-color: #f8f9fa;
            text-decoration: none;
            color: #333;
            padding-left: 24px;
        }
        .floating-contact-item-info {
            flex: 1;
        }
        .floating-contact-item-title {
            font-weight: 700;
            font-size: 1rem;
            color: #1f2a44;
            margin-bottom: 4px;
        }
        .floating-contact-item-desc {
            font-size: 0.85rem;
            color: #666;
        }
        .floating-contact-item-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            border-radius: 10px;
            transition: all 0.2s ease;
            flex-shrink: 0;
            font-size: 20px;
        }
        .floating-contact-item:hover .floating-contact-item-icon {
            transform: scale(1.1);
        }
        .floating-contact-item-icon.zalo {
            background: linear-gradient(135deg, #0068ff, #0052cc);
            color: white;
        }
        .floating-contact-item-icon.phone {
            background: linear-gradient(135deg, #28a745, #1e7e34);
            color: white;
        }
        .floating-contact-item-icon.group {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
        }
        
        .floating-contact-label {
            position: fixed;
            bottom: 42px;
            left: 105px;
            background: rgba(0, 168, 255, 0.95);
            color: white;
            padding: 10px 16px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 0.9rem;
            white-space: nowrap;
            box-shadow: 0 4px 20px rgba(0, 168, 255, 0.5),
                        0 0 15px rgba(0, 168, 255, 0.3);
            z-index: 997;
            opacity: 0;
            transform: translateX(-15px);
            pointer-events: none;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            border: 2px solid white;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .floating-contact-wrapper:hover .floating-contact-label {
            opacity: 1;
            transform: translateX(0);
        }
        
        @media (max-width: 768px) {
            .floating-contact-dropdown {
                min-width: calc(100vw - 50px);
                max-width: 350px;
                left: 25px;
                right: 25px;
            }
            .floating-contact-label {
                display: none;
            }
        }
        
        /* Modal Styles */
        .modal-content { border-radius: 10px; }
        .modal-header { background-color: var(--primary); color: white; }
        .package-option { padding: 12px; border: 1px solid #ccc; border-radius: 6px; margin-bottom: 12px; cursor: pointer; transition: background-color 0.2s; font-size: 1.1rem; font-weight: 600; color: #333; display: flex; align-items: center; justify-content: space-between; }
        .package-option:hover { background-color: #f8f9fa; }
        .package-option.selected { background-color: #e9ecef; border-color: var(--primary); color: var(--primary); }
        .hot-label { font-size: 1rem; color: #ff4500; margin-left: 10px; animation: blink 1.5s infinite; will-change: opacity; }
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }
        
        /* UI Components */
        .pill-link { display: inline-flex; align-items: center; justify-content: center; padding: 10px 18px; border-radius: 999px; text-decoration: none; font-weight: 700; border: 1px solid; min-width: 160px; box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12); transition: all 0.2s; }
        .pill-link:hover { text-decoration: none; transform: translateY(-1px); box-shadow: 0 10px 22px rgba(0, 0, 0, 0.18); }
        .btn-primary { background-color: #1976D2; border-color: #1565C0; color: #ffffff; }
        .btn-primary:hover { background-color: #1565C0; border-color: #0D47A1; color: #ffffff; }
        
        /* Griffin-Unlocker - Purple */
        .pill-link.griffin { background: linear-gradient(145deg, #e8d5ff, #d4b3ff); color: #5a1a8a; border-color: #b894e6; box-shadow: 0 6px 16px rgba(138, 43, 226, 0.2); }
        .pill-link.griffin:hover { background: linear-gradient(145deg, #d4b3ff, #c19de6); color: #4a1570; box-shadow: 0 10px 22px rgba(138, 43, 226, 0.3); }
        
        /* Unlocktool - Blue */
        .pill-link.unlocktool { background: linear-gradient(145deg, #d6e9ff, #b8daff); color: #0d47a1; border-color: #90caf9; box-shadow: 0 6px 16px rgba(33, 150, 243, 0.2); }
        .pill-link.unlocktool:hover { background: linear-gradient(145deg, #b8daff, #9acaff); color: #0a3d7a; box-shadow: 0 10px 22px rgba(33, 150, 243, 0.3); }
        
        /* Vietmap - Green */
        .pill-link.vietmap { background: linear-gradient(145deg, #c8e6c9, #a5d6a7); color: #1b5e20; border-color: #81c784; box-shadow: 0 6px 16px rgba(76, 175, 80, 0.2); }
        .pill-link.vietmap:hover { background: linear-gradient(145deg, #a5d6a7, #8bc34a); color: #0d4f14; box-shadow: 0 10px 22px rgba(76, 175, 80, 0.3); }
        
        /* TSM Tool - Orange */
        .pill-link.tsm { background: linear-gradient(145deg, #ffe0b2, #ffcc80); color: #e65100; border-color: #ffb74d; box-shadow: 0 6px 16px rgba(255, 152, 0, 0.2); }
        .pill-link.tsm:hover { background: linear-gradient(145deg, #ffcc80, #ffb74d); color: #bf360c; box-shadow: 0 10px 22px rgba(255, 152, 0, 0.3); }
        
        /* Android Multitool (AMT) - Red/Pink */
        .pill-link.amt { background: linear-gradient(145deg, #f8bbd0, #f48fb1); color: #880e4f; border-color: #ec407a; box-shadow: 0 6px 16px rgba(233, 30, 99, 0.2); }
        .pill-link.amt:hover { background: linear-gradient(145deg, #f48fb1, #f06292); color: #6a0d3f; box-shadow: 0 10px 22px rgba(233, 30, 99, 0.3); }
        
        /* KG Killer - Teal */
        .pill-link.kg { background: linear-gradient(145deg, #b2ebf2, #80deea); color: #004d40; border-color: #4dd0e1; box-shadow: 0 6px 16px rgba(0, 150, 136, 0.2); }
        .pill-link.kg:hover { background: linear-gradient(145deg, #80deea, #4dd0e1); color: #00332c; box-shadow: 0 10px 22px rgba(0, 150, 136, 0.3); }
        
        /* DFT Pro - Navy/Indigo */
        .pill-link.dft { background: linear-gradient(145deg, #7986cb, #5c6bc0); color: #1a237e; border-color: #3f51b5; box-shadow: 0 6px 16px rgba(63, 81, 181, 0.2); }
        .pill-link.dft:hover { background: linear-gradient(145deg, #5c6bc0, #3f51b5); color: #0d1b5e; box-shadow: 0 10px 22px rgba(63, 81, 181, 0.3); }
        
        /* Samsung Tool - Royal Blue */
        .pill-link.samsung { background: linear-gradient(145deg, #1565c0, #0d47a1); color: #ffffff; border-color: #1976d2; box-shadow: 0 6px 16px rgba(21, 101, 192, 0.3); }
        .pill-link.samsung:hover { background: linear-gradient(145deg, #0d47a1, #0a3d8f); color: #ffffff; box-shadow: 0 10px 22px rgba(21, 101, 192, 0.4); }
        
        /* Thuê Dịch Vụ Khác - Gold/Amber */
        .pill-link.services { background: linear-gradient(145deg, #ffc107, #ff9800); color: #1a1a1a; border-color: #ffb300; box-shadow: 0 6px 16px rgba(255, 193, 7, 0.3); }
        .pill-link.services:hover { background: linear-gradient(145deg, #ffb300, #ff8f00); color: #1a1a1a; box-shadow: 0 10px 22px rgba(255, 193, 7, 0.4); }
        .cta-pill { border-radius: 999px; padding: 10px 22px; font-weight: 700; }
        .cta-support { display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.95); border: 1px solid #e5e7ef; box-shadow: 0 6px 18px rgba(0,0,0,0.08); padding: 12px 16px; border-radius: 12px; font-weight: 600; color: #1f2a44; }
        .cta-support a { font-weight: 700; color: var(--primary); }
        
        .history-btn { background: linear-gradient(135deg, #f8c346, #f0a400); border: none; color: #1f1a0d; font-weight: 700; padding: 10px 18px; border-radius: 12px; box-shadow: 0 10px 24px rgba(240, 164, 0, 0.35); transition: all 0.2s; height: 48px; }
        .history-btn:hover { transform: translateY(-1px); box-shadow: 0 14px 30px rgba(240, 164, 0, 0.45); color: #0d0c09; }
        
        .action-box { background: rgba(255,255,255,0.96); border: 1px solid #e6e8f0; border-radius: 12px; box-shadow: 0 8px 18px rgba(0,0,0,0.06); max-width: 440px; width: 100%; padding: 10px 14px; display: flex; align-items: center; justify-content: center; }
        .inline-actions { gap: 16px; flex-wrap: wrap; align-items: stretch; }
        
        .copy-btn { font-size: 0.85rem; padding: 3px 10px; margin-left: 8px; border-radius: 6px; }
        
        @media (max-width: 768px) {
            h2 { font-size: 1.2rem; text-align: center; font-weight: 600; color: #333; }
            .table-responsive { font-size: 0.85rem; }
            .overlay { padding: 15px 10px; margin: 0 8px; border-radius: 8px; }
            .overlay .text-center.mb-3 { padding-top: 5px; }
            .overlay .text-center.mb-3 .badge { margin-bottom: 8px; }
            .overlay .text-center.mb-3 p { font-size: 0.85rem !important; margin-top: 8px !important; margin-bottom: 10px !important; }
            .overlay .badge { font-size: 0.75rem; padding: 5px 10px; }
            .overlay .d-flex.flex-wrap { gap: 6px !important; }
            table th, table td { white-space: nowrap; padding: 0.3rem; font-size: 0.8rem; }
            .pill-link { 
                width: auto; 
                min-width: auto; 
                font-size: 0.78rem; 
                padding: 8px 12px;
                border-radius: 20px;
            }
            .cta-support, .history-btn { width: 100%; justify-content: center; }
        }
    /* Blinking animation for rent button */
.btn-blink {
    animation: blink-animation 1.5s ease-in-out infinite;
    font-weight: bold;
    will-change: opacity, transform;
}
@keyframes blink-animation {
    0%, 100% { 
        opacity: 1;
        transform: scale(1);
    }
    50% { 
        opacity: 0.7;
        transform: scale(1.02);
    }
}
</style>
</head>
<body class="p-4">

<!-- Main Header -->
<header class="main-header" id="mainHeader">
    <div class="header-container">
        <!-- Row 1: Logo + Actions -->
        <div class="header-row-1">
            <a href="index.php" class="logo-section">
                <div class="logo-icon">
                    <i class="fas fa-unlock-alt"></i>
                </div>
                <div class="logo-text">
                    <h1 class="logo-title">UNLOCKTOOL.US</h1>
                    <p class="logo-subtitle">Thuê tài khoản tự động 24/7</p>
                </div>
            </a>
            
            <div class="header-center-actions">
                <button class="header-history-btn" type="button" data-toggle="modal" data-target="#historyModal">
                    <i class="fas fa-clock-rotate-left"></i>
                    <span>Lịch sử thuê</span>
                </button>
                
                <div class="header-search-form">
                    <input type="text" id="headerTransferContent" class="header-search-input" placeholder="Nội dung CK hoặc mã đơn">
                    <button type="button" id="headerCheckBtn" class="header-search-btn">
                        <i class="fas fa-search"></i> Tìm đơn
                    </button>
                </div>
            </div>
            
            <div class="header-right-actions">
                <div class="contact-dropdown">
                    <button class="contact-dropdown-btn" id="contactDropdownBtn">
                    <i class="fas fa-headset"></i>
                        <span>Liên hệ</span>
                        <i class="fas fa-chevron-down" style="font-size: 0.7rem;"></i>
                    </button>
                    <div class="contact-dropdown-content" id="contactDropdown">
                        <a href="https://zalo.me/0777333763" target="_blank" class="contact-item">
                            <div class="contact-item-info">
                                <div class="contact-item-title">Zalo Mai Quyên</div>
                                <div class="contact-item-desc">Cấp tài khoản</div>
                            </div>
                            <div class="contact-item-btn zalo">
                                <i class="fas fa-comments"></i>
                            </div>
                        </a>
                        <a href="https://zalo.me/0799161640" target="_blank" class="contact-item">
                            <div class="contact-item-info">
                                <div class="contact-item-title">Zalo Thanhtaj</div>
                                <div class="contact-item-desc">Hỗ trợ mở khóa</div>
                            </div>
                            <div class="contact-item-btn zalo">
                                <i class="fas fa-comments"></i>
                            </div>
                        </a>
                        <a href="tel:0799161640" class="contact-item">
                            <div class="contact-item-info">
                                <div class="contact-item-title">Gọi Mai Quyên</div>
                                <div class="contact-item-desc">0799 161640</div>
                            </div>
                            <div class="contact-item-btn phone">
                    <i class="fas fa-phone-alt"></i>
                            </div>
                        </a>
                        <a href="tel:0777333763" class="contact-item">
                            <div class="contact-item-info">
                                <div class="contact-item-title">Gọi Khang</div>
                                <div class="contact-item-desc">0777 333 763</div>
                            </div>
                            <div class="contact-item-btn phone">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                </a>
                        <a href="https://zalo.me/g/qncjky686" target="_blank" class="contact-item">
                            <div class="contact-item-info">
                                <div class="contact-item-title">Group Hỗ Trợ</div>
                                <div class="contact-item-desc">Tham gia ngay</div>
                            </div>
                            <div class="contact-item-btn group">
                                <i class="fas fa-users"></i>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            
            <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Mở menu di động">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
        <!-- Mobile Menu -->
        <div class="mobile-menu" id="mobileMenu">
            <button class="mobile-history-btn" type="button" data-toggle="modal" data-target="#historyModal">
                <i class="fas fa-clock-rotate-left"></i> Lịch sử thuê
            </button>
            <div class="mobile-search-form">
                <input type="text" id="mobileTransferContent" placeholder="Nội dung CK hoặc mã đơn">
                <button type="button" id="mobileCheckBtn"><i class="fas fa-search"></i> Tìm đơn</button>
            </div>
            <a href="https://zalo.me/0777333763" target="_blank" class="mobile-link"><i class="fas fa-comments"></i> Zalo Mai Quyên - Cấp tài khoản</a>
            <a href="https://zalo.me/0799161640" target="_blank" class="mobile-link"><i class="fas fa-comments"></i> Zalo Thanhtaj - Hỗ trợ mở khóa</a>
            <a href="tel:0799161640" class="mobile-link"><i class="fas fa-phone-alt"></i> Gọi Mai Quyên: 0799 161640</a>
            <a href="tel:0777333763" class="mobile-link"><i class="fas fa-phone-alt"></i> Gọi Khang: 0777 333 763</a>
            <a href="https://zalo.me/g/qncjky686" target="_blank" class="mobile-link"><i class="fas fa-users"></i> Group Hỗ Trợ</a>
        </div>
    </div>
</header>

<script>
// Mobile menu toggle
document.getElementById('mobileMenuToggle')?.addEventListener('click', function() {
    const mobileMenu = document.getElementById('mobileMenu');
    mobileMenu.classList.toggle('show');
    this.innerHTML = mobileMenu.classList.contains('show') 
        ? '<i class="fas fa-times"></i>' 
        : '<i class="fas fa-bars"></i>';
});

// Close mobile menu when clicking outside
document.addEventListener('click', function(e) {
    const mobileMenu = document.getElementById('mobileMenu');
    const toggle = document.getElementById('mobileMenuToggle');
    if (mobileMenu && toggle && !mobileMenu.contains(e.target) && !toggle.contains(e.target)) {
        mobileMenu.classList.remove('show');
        toggle.innerHTML = '<i class="fas fa-bars"></i>';
    }
});

// Contact dropdown toggle
document.getElementById('contactDropdownBtn')?.addEventListener('click', function(e) {
    e.stopPropagation();
    const dropdown = document.getElementById('contactDropdown');
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
});

// Close contact dropdown when clicking outside
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('contactDropdown');
    const dropdownBtn = document.getElementById('contactDropdownBtn');
    if (dropdown && dropdownBtn && !dropdown.contains(e.target) && !dropdownBtn.contains(e.target)) {
        dropdown.classList.remove('show');
    }
});

// Close dropdown when clicking on contact items
document.querySelectorAll('.contact-item').forEach(item => {
    item.addEventListener('click', function() {
        const dropdown = document.getElementById('contactDropdown');
        if (dropdown) {
            setTimeout(() => {
                dropdown.classList.remove('show');
            }, 100);
        }
    });
});

</script>

<!-- Floating Contact Button -->
<div class="floating-contact-wrapper">
    <div class="floating-contact-label">
        <i class="fas fa-headset"></i> Liên hệ hỗ trợ
    </div>
    <button class="floating-contact-btn" id="floatingContactBtn" aria-label="Liên hệ hỗ trợ">
        <i class="fas fa-headset"></i>
    </button>
    <div class="floating-contact-dropdown" id="floatingContactDropdown">
        <div class="floating-contact-dropdown-header">
            <i class="fas fa-headset"></i>
            <span>Liên hệ hỗ trợ</span>
        </div>
        <a href="https://zalo.me/0777333763" target="_blank" class="floating-contact-item">
            <div class="floating-contact-item-info">
                <div class="floating-contact-item-title">Zalo Mai Quyên</div>
                <div class="floating-contact-item-desc">Cấp tài khoản</div>
            </div>
            <div class="floating-contact-item-icon zalo">
                <i class="fas fa-comments"></i>
            </div>
        </a>
        <a href="https://zalo.me/0799161640" target="_blank" class="floating-contact-item">
            <div class="floating-contact-item-info">
                <div class="floating-contact-item-title">Zalo Thanhtaj</div>
                <div class="floating-contact-item-desc">Hỗ trợ mở khóa</div>
            </div>
            <div class="floating-contact-item-icon zalo">
                <i class="fas fa-comments"></i>
            </div>
        </a>
        <a href="tel:0799161640" class="floating-contact-item">
            <div class="floating-contact-item-info">
                <div class="floating-contact-item-title">Gọi Mai Quyên</div>
                <div class="floating-contact-item-desc">0799 161640</div>
            </div>
            <div class="floating-contact-item-icon phone">
                <i class="fas fa-phone-alt"></i>
            </div>
        </a>
        <a href="tel:0777333763" class="floating-contact-item">
            <div class="floating-contact-item-info">
                <div class="floating-contact-item-title">Gọi Khang</div>
                <div class="floating-contact-item-desc">0777 333 763</div>
            </div>
            <div class="floating-contact-item-icon phone">
                <i class="fas fa-phone-alt"></i>
            </div>
        </a>
        <a href="https://zalo.me/g/qncjky686" target="_blank" class="floating-contact-item">
            <div class="floating-contact-item-info">
                <div class="floating-contact-item-title">Group Hỗ Trợ</div>
                <div class="floating-contact-item-desc">Tham gia ngay</div>
            </div>
            <div class="floating-contact-item-icon group">
                <i class="fas fa-users"></i>
            </div>
        </a>
    </div>
</div>

<main role="main">
<div class="overlay">
    <!-- Header Section -->
    <div class="text-center mb-3">
        <span class="badge badge-dark px-3 py-2">Hệ thống tự động 24/7</span>
        <p class="mt-3 mb-3" style="font-size:1.05rem;color:#1f2a44;">
            Dịch vụ cho thuê tài khoản hoàn toàn tự động, thanh toán tiện lợi, an toàn, nhận tài khoản ngay lập tức!
        </p>
        <div class="d-flex flex-wrap justify-content-center mt-2" style="gap:10px;">
            <a class="pill-link griffin" target="_blank" href="https://thuetaikhoan.net/thue-griffin.php">Griffin-Unlocker</a>
            <a class="pill-link amt" target="_blank" href="https://thuetaikhoan.net/thue-amt.php">Android Multitool</a>
            <a class="pill-link unlocktool" target="_blank" href="https://thuetaikhoan.net/thue-unlocktool.php">Unlocktool</a>
            <a class="pill-link tsm" target="_blank" href="https://thuetaikhoan.net/thue-tsm.php">TSM Tool</a>
            <a class="pill-link vietmap" target="_blank" href="https://thuetaikhoan.net/thue-vietmap.php">Vietmap Live (PRO)</a>
            <a class="pill-link kg" target="_blank" href="https://thuetaikhoan.net/thue-kg-killer.php">KG Killer</a>
            <a class="pill-link dft" target="_blank" href="https://thuetaikhoan.net/thue-dft.php">DFT Pro Tool</a>
            <a class="pill-link samsung" target="_blank" href="https://thuetaikhoan.net/thue-samsung-tool.php">Samsung Tool</a>
            <a class="pill-link services" target="_blank" href="https://thuetaikhoan.net/ord-services.php">Thuê Dịch Vụ Khác</a>
        </div>
    </div>

    <!-- Accounts Table -->
    <div class="table-responsive">
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Loại</th>
                    <th scope="col">Thuê</th>
                    <th scope="col">Trạng thái</th>
                    <th scope="col">Tài khoản</th>
                    <th scope="col">Mật khẩu</th>
                    <th scope="col">Thời gian còn lại</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): 
                $time_info = getTimeRemaining($row['expires_at']);
                $is_expired = $time_info ? $time_info['expired'] : true;
            ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['type'] ?? 'Không rõ'); ?></td>
                    <td>
                        <?php if ($row['is_available']): ?>
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#rentModal" data-account-id="<?php echo $row['id']; ?>">Thuê ngay</button>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-sm" disabled>Đang thuê</button>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge badge-<?php echo $row['is_available'] ? 'success' : 'danger'; ?>">
                            <?php echo $row['is_available'] ? 'Chờ thuê' : 'Đang thuê'; ?>
                        </span>
                    </td>
                    <td><?php echo mask($row['username']); ?></td>
                    <td><?php echo $is_expired ? str_repeat('*', strlen($row['password'])) : mask($row['password']); ?></td>
                    <td>
                        <?php if ($row['is_available']): ?>
                            <!-- Ẩn thời gian cho tài khoản Chờ thuê -->
                            <span class="text-muted">—</span>
                        <?php else: ?>
                            <?php if ($time_info && !$time_info['expired']): ?>
                                <span data-expire="<?php echo $time_info['timestamp']; ?>"><?php echo $time_info['text']; ?></span>
                            <?php elseif ($time_info && $time_info['expired']): ?>
                                <span class="text-success" data-waiting="<?php echo $time_info['timestamp']; ?>">⏳ Đang tính...</span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Lịch sử thuê -->
<div class="modal fade" id="historyModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Lịch sử thuê tài khoản (30 ngày gần nhất)</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <?php if ($history_result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead><tr><th>#</th><th>Loại</th><th>Tài khoản</th><th>Mật khẩu</th><th>Gói thuê</th><th>Thời gian thuê</th><th>Còn lại</th></tr></thead>
                            <tbody>
                            <?php $i = 1; while ($r = $history_result->fetch_assoc()): 
                                $h_time = getTimeRemaining($r['expires_at']);
                            ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($r['type']); ?></td>
                                    <td>
                                        <span><?php echo htmlspecialchars($r['username']); ?></span>
                                        <button class="btn btn-outline-secondary btn-sm copy-btn" data-copy="<?php echo htmlspecialchars($r['username']); ?>">Sao chép</button>
                                    </td>
                                    <td>
                                        <?php if (($h_time && $h_time['expired']) || ($r['password_changed'] ?? 0) == 1 || ($r['is_available'] ?? 0) == 1): ?>
                                            <?php echo str_repeat('*', strlen($r['password'])); ?>
                                        <?php else: ?>
                                            <span><?php echo htmlspecialchars($r['password']); ?></span>
                                            <button class="btn btn-outline-secondary btn-sm copy-btn" data-copy="<?php echo htmlspecialchars($r['password']); ?>">Sao chép</button>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo displayPackageName($r['hours']); ?></td>
                                    <td><?php echo $r['created_at']; ?></td>
                                    <td><?php echo $h_time ? $h_time['text'] : '-'; ?></td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center">Bạn chưa thuê tài khoản nào trong 30 ngày qua.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Chọn gói thuê -->
<div class="modal fade" id="rentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chọn gói thuê</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form method="post" action="checkout.php" id="rentForm">
                    <input type="hidden" name="account_id" id="account_id">
                    <input type="hidden" name="price_id" id="selected_price_id">
                    <?php while ($price_row = $prices_result->fetch_assoc()): ?>
                        <div class="package-option" data-price-id="<?php echo $price_row['id']; ?>">
                            <span>
                                <strong><?php echo displayPackageName($price_row['hours']); ?></strong> - <?php echo formatMoney($price_row['price']); ?>
                            </span>
                            <?php if ($price_row['hours'] == 6 || $price_row['hours'] == 24): ?>
                                <span class="hot-label">🔥 HOT</span>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                <button type="submit" class="btn btn-primary" form="rentForm" id="rentButton">Tiến hành thanh toán</button>
            </div>
        </div>
    </div>
</div>
</main>

<!-- JS loaded at bottom to avoid render-blocking -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Countdown timer (đếm ngược thời gian thuê còn lại)
function updateCountdowns() {
    document.querySelectorAll('[data-expire]').forEach(el => {
        const t = parseInt(el.dataset.expire) * 1000;
        const d = t - Date.now();
        if (d <= 0) {
            el.innerText = 'Đã hết hạn';
        } else {
            const h = Math.floor(d / 3600000);
            const m = Math.floor((d % 3600000) / 60000);
            const s = Math.floor((d % 60000) / 1000);
            el.innerText = `${h}h ${m}m ${s}s`;
        }
    });
}

// Waiting timer (đếm thời gian chờ thuê)
function updateWaitingTime() {
    document.querySelectorAll('[data-waiting]').forEach(el => {
        const expiredAt = parseInt(el.dataset.waiting) * 1000;
        const waited = Date.now() - expiredAt;
        if (waited > 0) {
            const h = Math.floor(waited / 3600000);
            const m = Math.floor((waited % 3600000) / 60000);
            const s = Math.floor((waited % 60000) / 1000);
            if (h > 0) {
                el.innerText = `⏳ ${h}h ${m}m ${s}s`;
            } else if (m > 0) {
                el.innerText = `⏳ ${m}m ${s}s`;
            } else {
                el.innerText = `⏳ ${s}s`;
            }
        }
    });
}

setInterval(updateCountdowns, 1000);
setInterval(updateWaitingTime, 1000);
window.onload = function() { updateCountdowns(); updateWaitingTime(); };

// Tạo 60 tài khoản ảo đang thuê với đếm ngược thời gian thực và sắp xếp đan xen với tài khoản thật
function createVirtualRentingAccounts() {
    const tbody = document.querySelector('table tbody');
    if (!tbody) return;
    
    const STORAGE_KEY = 'virtual_accounts_timers';
    
    // Đọc timer đã lưu từ localStorage
    let savedTimers = {};
    try {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved) {
            savedTimers = JSON.parse(saved);
        }
    } catch (e) {
        console.error('Error reading saved timers:', e);
    }
    
    // Mảng để lưu timer mới
    const newTimers = {};
    
    // Mảng để lưu tất cả tài khoản ảo trước khi sắp xếp
    const virtualAccounts = [];
    
    for (let i = 0; i < 60; i++) {
        // Tất cả tài khoản ảo đều bắt đầu bằng "M" + 9 dấu *
        const maskedUsername = 'M' + '*'.repeat(9);
        const maskedPassword = 'U' + '*'.repeat(7);
        
        // Tạo ID ảo từ 65 trở lên (200-259 cho 60 tài khoản)
        const virtualId = 200 + i;
        const timerKey = 'virtual_' + virtualId;
        
        let expireTimestamp;
        
        // Kiểm tra xem có timer đã lưu chưa
        if (savedTimers[timerKey] && savedTimers[timerKey] > Math.floor(Date.now() / 1000)) {
            // Sử dụng timer đã lưu nếu còn hạn
            expireTimestamp = savedTimers[timerKey];
        } else {
            // Tạo thời gian cố định mới (từ 1 giờ đến 650 giờ)
            // Sử dụng seed dựa trên ID để đảm bảo thời gian cố định cho mỗi ID
            const seed = virtualId * 12345; // Seed cố định cho mỗi ID
            const randomHours = (seed % 649) + 1;
            const randomMinutes = ((seed * 7) % 60);
            const randomSeconds = ((seed * 13) % 60);
            
            // Tính timestamp hết hạn (từ bây giờ + thời gian cố định)
            expireTimestamp = Math.floor(Date.now() / 1000) + (randomHours * 3600) + (randomMinutes * 60) + randomSeconds;
        }
        
        // Lưu timer mới
        newTimers[timerKey] = expireTimestamp;
        
        // Tính thời gian hiện tại để hiển thị
        const now = Math.floor(Date.now() / 1000);
        const remaining = expireTimestamp - now;
        const displayHours = Math.floor(remaining / 3600);
        const displayMinutes = Math.floor((remaining % 3600) / 60);
        const displaySeconds = remaining % 60;
        
        // Tạo hàng ảo
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${virtualId}</td>
            <td>Unlocktool</td>
            <td><button class="btn btn-secondary btn-sm" disabled>Đang thuê</button></td>
            <td><span class="badge badge-danger">Đang thuê</span></td>
            <td>${maskedUsername}</td>
            <td>${maskedPassword}</td>
            <td><span data-expire="${expireTimestamp}">${displayHours}h ${displayMinutes}m ${displaySeconds}s</span></td>
        `;
        
        // Lưu thông tin tài khoản vào mảng
        virtualAccounts.push({
            element: tr,
            expireTimestamp: expireTimestamp
        });
    }
    
    // Thu thập tất cả các hàng hiện có (tài khoản thật)
    const allRows = Array.from(tbody.querySelectorAll('tr'));
    const waitingAccounts = []; // Tài khoản chờ thuê
    const expiredAccounts = []; // Tài khoản đã hết hạn (màu xanh lá ⏳)
    const rentingAccounts = []; // Tài khoản đang thuê có timer
    
    // Thêm tài khoản thật vào mảng
    allRows.forEach(tr => {
        const expireSpan = tr.querySelector('[data-expire]');
        const waitingSpan = tr.querySelector('[data-waiting]');
        const badge = tr.querySelector('.badge');
        const isWaiting = badge && badge.textContent.trim() === 'Chờ thuê';
        
        if (isWaiting) {
            // Tài khoản chờ thuê - lên đầu
            waitingAccounts.push({
                element: tr,
                expireTimestamp: 0 // Đặt 0 để xếp đầu
            });
        } else if (waitingSpan) {
            // Tài khoản đã hết hạn (màu xanh lá ⏳ Đang tính...)
            const waitingTimestamp = parseInt(waitingSpan.getAttribute('data-waiting'));
            expiredAccounts.push({
                element: tr,
                expireTimestamp: waitingTimestamp
            });
        } else if (expireSpan) {
            // Tài khoản đang thuê có timer
            const expireTimestamp = parseInt(expireSpan.getAttribute('data-expire'));
            rentingAccounts.push({
                element: tr,
                expireTimestamp: expireTimestamp
            });
        } else {
            // Tài khoản đang thuê không có timer
            rentingAccounts.push({
                element: tr,
                expireTimestamp: Number.MAX_SAFE_INTEGER // Xếp cuối
            });
        }
    });
    
    // Thêm tài khoản ảo vào mảng đang thuê
    rentingAccounts.push(...virtualAccounts);
    
    // Sắp xếp tài khoản đã hết hạn theo thời gian chờ (từ ít đến nhiều)
    expiredAccounts.sort((a, b) => a.expireTimestamp - b.expireTimestamp);
    
    // Sắp xếp tài khoản đang thuê theo thời gian còn lại từ ít đến nhiều
    rentingAccounts.sort((a, b) => a.expireTimestamp - b.expireTimestamp);
    
    // Gộp lại: chờ thuê lên đầu, sau đó đã hết hạn (màu xanh lá), cuối cùng đang thuê
    const allAccounts = [...waitingAccounts, ...expiredAccounts, ...rentingAccounts];
    
    // Xóa tất cả hàng hiện có
    tbody.innerHTML = '';
    
    // Thêm lại tất cả hàng theo thứ tự đã sắp xếp
    allAccounts.forEach(account => {
        tbody.appendChild(account.element);
    });
    
    // Lưu timer vào localStorage
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(newTimers));
    } catch (e) {
        console.error('Error saving timers:', e);
    }
}

// Gọi hàm sau khi trang load xong
$(document).ready(function() {
    createVirtualRentingAccounts();
    // Cập nhật lại countdown cho các tài khoản ảo
    setTimeout(updateCountdowns, 100);
});

$(document).ready(function() {
    // Package selection
    $('.package-option').click(function() {
        $('.package-option').removeClass('selected');
        $(this).addClass('selected');
        $('#selected_price_id').val($(this).data('price-id'));
    });
    
    $('#rentButton').click(function() {
        if ($('#selected_price_id').val() === '') {
            alert('Vui lòng chọn một gói thuê!');
            return false;
        }
    });

    // Transfer content check - hỗ trợ cả mã đầy đủ (DH...) và chỉ số
    function handleCheckTransfer() {
        const raw = $('#transferContent').val().trim();
        if (!raw) { alert('Vui lòng nhập mã đơn hàng.'); return; }
        // Tìm mã có format DH... hoặc RENT... hoặc chỉ số
        const fullMatch = raw.match(/(?:DH|RENT)?\d+/gi);
        if (!fullMatch) { alert('Không tìm thấy mã đơn hàng. Vui lòng kiểm tra lại.'); return; }
        // Lấy mã cuối cùng (thường là mã đơn hàng trong nội dung CK)
        window.location.href = 'order_status.php?orderCode=' + encodeURIComponent(fullMatch[fullMatch.length - 1]);
    }
    $('#checkTransferBtn').click(handleCheckTransfer);
    $('#transferContent').on('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); handleCheckTransfer(); } });

    // Header search - tìm bằng mã đơn hàng
    function handleHeaderSearch() {
        const raw = $('#headerTransferContent').val().trim();
        if (!raw) { alert('Vui lòng nhập mã đơn hàng.'); return; }
        const fullMatch = raw.match(/(?:DH|RENT)?\d+/gi);
        if (!fullMatch) { alert('Không tìm thấy mã đơn hàng. Vui lòng kiểm tra lại.'); return; }
        window.location.href = 'order_status.php?orderCode=' + encodeURIComponent(fullMatch[fullMatch.length - 1]);
    }
    $('#headerCheckBtn').click(handleHeaderSearch);
    $('#headerTransferContent').on('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); handleHeaderSearch(); } });

    // Mobile search - tìm bằng mã đơn hàng
    function handleMobileSearch() {
        const raw = $('#mobileTransferContent').val().trim();
        if (!raw) { alert('Vui lòng nhập mã đơn hàng.'); return; }
        const fullMatch = raw.match(/(?:DH|RENT)?\d+/gi);
        if (!fullMatch) { alert('Không tìm thấy mã đơn hàng. Vui lòng kiểm tra lại.'); return; }
        window.location.href = 'order_status.php?orderCode=' + encodeURIComponent(fullMatch[fullMatch.length - 1]);
    }
    $('#mobileCheckBtn').click(handleMobileSearch);
    $('#mobileTransferContent').on('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); handleMobileSearch(); } });

    // Quick rent button
    $('#ctaRentNow').click(function() {
        const btn = document.querySelector('button[data-target="#rentModal"][data-account-id]');
        if (btn) { btn.click(); } else { alert('Hiện chưa có tài khoản sẵn sàng.'); }
    });

    // Modal account ID
    $('#rentModal').on('show.bs.modal', function(e) {
        $('#account_id').val($(e.relatedTarget).data('account-id'));
    });

    // Copy button
    $(document).on('click', '.copy-btn', function() {
        const text = $(this).data('copy');
        navigator.clipboard.writeText(text).then(() => alert('Đã sao chép: ' + text));
    });

    // Floating Contact Button Toggle
    $('#floatingContactBtn').on('click', function(e) {
        e.stopPropagation();
        const dropdown = $('#floatingContactDropdown');
        const btn = $(this);
        const icon = btn.find('i');
        
        if (dropdown.length) {
            const isOpen = dropdown.hasClass('show');
            
            if (isOpen) {
                dropdown.removeClass('show');
                btn.removeClass('active');
                icon.removeClass('fa-times').addClass('fa-headset');
            } else {
                dropdown.addClass('show');
                btn.addClass('active');
                icon.removeClass('fa-headset').addClass('fa-times');
            }
        }
    });

    // Close floating contact dropdown when clicking outside
    $(document).on('click', function(e) {
        const dropdown = $('#floatingContactDropdown');
        const btn = $('#floatingContactBtn');
        
        if (dropdown.length && btn.length) {
            const wrapper = btn.closest('.floating-contact-wrapper');
            if (wrapper.length && !wrapper[0].contains(e.target)) {
                dropdown.removeClass('show');
                btn.removeClass('active');
                const icon = btn.find('i');
                icon.removeClass('fa-times').addClass('fa-headset');
            }
        }
    });

    // Close floating dropdown when clicking on contact items
    $(document).on('click', '.floating-contact-item', function() {
        const dropdown = $('#floatingContactDropdown');
        const btn = $('#floatingContactBtn');
        if (dropdown.length && btn.length) {
            setTimeout(function() {
                dropdown.removeClass('show');
                btn.removeClass('active');
                const icon = btn.find('i');
                icon.removeClass('fa-times').addClass('fa-headset');
            }, 100);
        }
    });
});
</script>
</body>
</html>
