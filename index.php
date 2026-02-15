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

// Query danh sách tài khoản (chỉ khi có kết nối DB)
$result = null;
$prices_result = null;
$history_result = null;

if ($db_connected && $conn) {
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
                WHEN a.is_available = 1 AND o.expires_at IS NOT NULL THEN 0
                WHEN a.is_available = 1 AND o.expires_at IS NULL THEN 1
                ELSE 2
            END,
            o.expires_at ASC,
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
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google-site-verification" content="sopE0oKWWzmSq9xXTXegYWh6bE4pdp_FbESZZhTtnmQ" />
    
    <!-- SEO Meta Tags -->
    <title>Thuê UnlockTool Giá Rẻ Từ 8K - Tự Động 24/7 | Uy Tín Giá Rẻ 2026</title>
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
        },
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "+84777333763",
            "contactType": "customer service",
            "availableLanguage": "Vietnamese",
            "areaServed": "VN"
        },
        "sameAs": [
            "https://zalo.me/0777333763"
        ]
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
    
    <!-- Structured Data - FAQPage (Google Rich Snippets) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "UnlockTool là gì?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "UnlockTool là phần mềm chuyên dụng dùng để mở khóa điện thoại, xóa FRP (Google Account), bypass iCloud, xóa Mi Account, flash firmware... Hỗ trợ đa dòng máy Samsung, Xiaomi, Oppo, Vivo, Realme, iPhone."
                }
            },
            {
                "@type": "Question",
                "name": "Thuê UnlockTool bao lâu nhận được tài khoản?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Hệ thống tự động 24/7, sau khi thanh toán thành công bạn sẽ nhận tài khoản ngay lập tức (thường trong vòng 30 giây đến 1 phút)."
                }
            },
            {
                "@type": "Question",
                "name": "Có thể đổi máy tính khi đang thuê UnlockTool không?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Không. UnlockTool quy định đổi PC sau mỗi 6 giờ. Nếu bạn đăng nhập máy thứ 2 khi chưa đủ thời gian, hệ thống sẽ báo lỗi liên kết máy cũ."
                }
            },
            {
                "@type": "Question",
                "name": "Thời gian thuê UnlockTool tính như thế nào?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Thời gian tính theo real-time từ lúc bạn nhận tài khoản. Đồng hồ đếm ngược liên tục, kể cả khi bạn không sử dụng."
                }
            },
            {
                "@type": "Question",
                "name": "Thanh toán bằng hình thức nào?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Hỗ trợ chuyển khoản ngân hàng (Vietcombank, MB Bank, Techcombank...) và ví điện tử. Hệ thống xác nhận tự động, nhận tài khoản ngay sau khi thanh toán."
                }
            },
            {
                "@type": "Question",
                "name": "Cần chuẩn bị gì trước khi thuê UnlockTool?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Bạn cần tải sẵn phần mềm UnlockTool từ trang chủ, cài đặt Driver đầy đủ cho dòng máy cần làm để tiết kiệm thời gian thuê."
                }
            },
            {
                "@type": "Question",
                "name": "UnlockTool hỗ trợ những dòng máy nào?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "UnlockTool hỗ trợ hầu hết các dòng Samsung, Xiaomi, Oppo, Vivo, Realme, Huawei, LG, Motorola, và iPhone (bypass iCloud, MDM). Phần mềm được cập nhật liên tục để hỗ trợ model mới nhất."
                }
            },
            {
                "@type": "Question",
                "name": "Có hỗ trợ cài driver không?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Có. Trên giao diện UnlockTool có mục Config → Install Driver. Bạn cũng có thể liên hệ Zalo để được admin gửi bộ Driver chuẩn."
                }
            },
            {
                "@type": "Question",
                "name": "Giá đại lý có không?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Có. Nếu bạn là cửa hàng hoặc cần thuê số lượng lớn thường xuyên, hãy liên hệ trực tiếp Zalo để nhận giá đại lý tốt nhất."
                }
            },
            {
                "@type": "Question",
                "name": "Quên thông tin tài khoản thì sao?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Bạn có thể tra cứu lại đơn hàng bằng mã đơn hoặc nội dung chuyển khoản ngay trên website. Ngoài ra, bạn có thể liên hệ Zalo để được hỗ trợ tra cứu lại thông tin."
                }
            }
        ]
    }
    </script>
    
    
    <!-- Structured Data - SoftwareApplication with Reviews (Google Rich Snippets eligible) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "UnlockTool.us - Thuê Tài Khoản UnlockTool",
        "applicationCategory": "UtilitiesApplication",
        "operatingSystem": "Windows",
        "url": "https://www.unlocktool.us",
        "description": "Dịch vụ cho thuê tài khoản UnlockTool premium theo giờ, ngày với giá rẻ nhất. Hệ thống tự động 24/7.",
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
            "reviewCount": "6500",
            "bestRating": "5",
            "worstRating": "1"
        },
        "review": [
            {
                "@type": "Review",
                "author": {"@type": "Person", "name": "Minh Tuấn"},
                "datePublished": "2026-02-05",
                "reviewBody": "Đã thuê UnlockTool ở đây hơn 10 lần. Thanh toán xong là nhận tài khoản liền. Xóa FRP Samsung A54, A34 đều thành công. Giá 8K/6h quá rẻ.",
                "name": "Dịch vụ nhanh, ổn định",
                "reviewRating": {"@type": "Rating", "ratingValue": "5", "bestRating": "5"}
            },
            {
                "@type": "Review",
                "author": {"@type": "Person", "name": "Thanh Hải"},
                "datePublished": "2026-01-22",
                "reviewBody": "Hệ thống tự động 24/7 tiện lợi. Thuê lúc 2h sáng, chuyển tiền xong 30 giây là có tài khoản luôn.",
                "name": "Tự động 24/7, rất tiện",
                "reviewRating": {"@type": "Rating", "ratingValue": "5", "bestRating": "5"}
            },
            {
                "@type": "Review",
                "author": {"@type": "Person", "name": "Thành Long"},
                "datePublished": "2025-12-05",
                "reviewBody": "Đã thử 3-4 trang khác nhau, chốt ở đây vì giá rẻ nhất, hệ thống tự động, hỗ trợ nhanh.",
                "name": "Trang uy tín nhất",
                "reviewRating": {"@type": "Rating", "ratingValue": "5", "bestRating": "5"}
            },
            {
                "@type": "Review",
                "author": {"@type": "Person", "name": "Ngọc Trâm"},
                "datePublished": "2026-01-05",
                "reviewBody": "Là thợ sửa điện thoại, dùng trang này từ khi mới mở. Thuê hàng tuần, chưa bao giờ gặp vấn đề gì.",
                "name": "Đáng tin cậy",
                "reviewRating": {"@type": "Rating", "ratingValue": "5", "bestRating": "5"}
            },
            {
                "@type": "Review",
                "author": {"@type": "Person", "name": "Công Minh"},
                "datePublished": "2025-11-20",
                "reviewBody": "Nhờ UnlockTool.us mà nhận thêm dịch vụ xóa khóa cho khách. Thuê 8K, thu khách 100-200K. Trang chạy ổn định.",
                "name": "Tiết kiệm chi phí",
                "reviewRating": {"@type": "Rating", "ratingValue": "5", "bestRating": "5"}
            }
        ]
    }
    </script>
    
    <link rel="icon" type="image/x-icon" href="/favicon.ico?v=1">
    
    <!-- Preconnect (max 2 for PageSpeed) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    
    <!-- Google Fonts: Inter (async load) -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"></noscript>
    
    <!-- Bootstrap CSS (async load to avoid render-blocking) -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"></noscript>
    
    <!-- Font Awesome (async load) -->
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    
    <!-- JS moved to bottom of body to eliminate render-blocking -->
    <style>
        /* Font-display optional: prevents CLS from font swap reflow */
        @font-face { font-family: 'Font Awesome 6 Free'; font-display: optional; }
        @font-face { font-family: 'Font Awesome 6 Brands'; font-display: optional; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-display: optional; }
        :root {
            --primary: #007bff;
            --success: #28a745;
            --danger: #dc3545;
        }
        *, *::before, *::after { box-sizing: border-box; }
        html, body {
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }
        body {
            background-color: #f0f2f5;
            min-height: 100vh;
            padding-top: 80px;
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
            max-width: 100%;
            margin: 0 auto;
            padding: 12px 30px;
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
            border-radius: 10px;
            overflow: hidden;
            flex-shrink: 0;
            aspect-ratio: 1;
        }
        .logo-icon img {
            width: 44px;
            height: 44px;
            object-fit: cover;
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
                margin: 0;
            }
            .main-header {
                position: relative !important;
                min-height: auto;
                padding: 0;
                width: 100%;
                margin: 0;
                border-radius: 0;
                left: 0;
                right: 0;
                border-left: none;
                border-right: none;
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
            .logo-icon img {
                width: 28px;
                height: 28px;
            }
            .header-container {
                padding: 8px 12px;
            }
            .overlay {
                margin-top: 10px !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                max-width: 100% !important;
            }
        }
        
        @media (min-width: 993px) {
            .mobile-menu {
                display: none !important;
            }
        }
        .overlay {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 30px;
            margin: 20px auto 0;
            border-radius: 0;
            max-width: 82%;
            box-shadow: none;
        }
        table { box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .table-responsive { max-height: 800px; overflow-y: auto; border-radius: 8px; }
        .table-responsive thead th { position: sticky; top: 0; z-index: 10; }
        thead th { background-color: #1a73e8; color: #ffffff; }
        @media (max-width: 768px) {
            .overlay { max-width: 100%; padding: 10px 6px; margin: 10px 0 0 0; }
            .table th, .table td { font-size: 0.7rem; padding: 6px 4px; }
            .badge-success, .badge-danger { font-size: 0.65rem; padding: 4px 8px; }
        }
        .badge-success { background-color: #1b813a; color: #ffffff; font-weight: 700; padding: 6px 16px; font-size: 0.875rem; display: inline-block; line-height: 1.5; border-radius: 20px; }
        .badge-danger { background-color: var(--danger); padding: 6px 16px; font-size: 0.875rem; border-radius: 20px; }
        
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
                transform: scale(1.05);
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
        .btn-primary { background-color: #1a73e8; border-color: #1567d3; color: #ffffff; border-radius: 20px; padding: 6px 16px; }
        .btn-primary:hover { background-color: #1567d3; border-color: #1259b8; color: #ffffff; }
        
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
    /* Blinking animation for rent button — composited only */
.btn-blink {
    animation: pulse-btn 2s ease-in-out infinite;
    font-weight: bold;
    will-change: transform;
    contain: layout style;
}
@keyframes pulse-btn {
    0%, 100% { 
        transform: scale(1);
    }
    50% { 
        transform: scale(1.05);
    }
}
/* ========== HERO BANNER ========== */
.hero-banner {
    background: #1a1d23;
    padding: 55px 20px 45px;
    text-align: center;
    width: 100vw;
    margin-left: calc(-50vw + 50%);
    position: relative;
    overflow: hidden;
    contain: layout style paint;
}
.hero-banner::after {
    content: '';
    position: absolute;
    top: -30%;
    left: -10%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(26,115,232,0.15) 0%, transparent 70%);
    border-radius: 50%;
    animation: heroOrb1 8s ease-in-out infinite alternate;
    pointer-events: none;
}
@keyframes heroOrb1 {
    0% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(60vw, 20px) scale(1.2); }
    100% { transform: translate(30vw, -10px) scale(0.9); }
}
.hero-particles {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    pointer-events: none;
    overflow: hidden;
}
.hero-particles span {
    position: absolute;
    width: 3px;
    height: 3px;
    background: rgba(255,255,255,0.15);
    border-radius: 50%;
    animation: heroPFloat linear infinite;
}
.hero-particles span:nth-child(1) { left: 10%; top: 20%; animation-duration: 6s; animation-delay: 0s; }
.hero-particles span:nth-child(2) { left: 25%; top: 60%; animation-duration: 8s; animation-delay: 1s; width: 2px; height: 2px; }
.hero-particles span:nth-child(3) { left: 45%; top: 30%; animation-duration: 7s; animation-delay: 2s; }
.hero-particles span:nth-child(4) { left: 65%; top: 70%; animation-duration: 9s; animation-delay: 0.5s; width: 4px; height: 4px; }
.hero-particles span:nth-child(5) { left: 80%; top: 15%; animation-duration: 6.5s; animation-delay: 1.5s; }
.hero-particles span:nth-child(6) { left: 90%; top: 50%; animation-duration: 7.5s; animation-delay: 3s; width: 2px; height: 2px; }
.hero-particles span:nth-child(7) { left: 35%; top: 80%; animation-duration: 8.5s; animation-delay: 2.5s; }
.hero-particles span:nth-child(8) { left: 55%; top: 10%; animation-duration: 5.5s; animation-delay: 0.8s; width: 4px; height: 4px; background: rgba(255,107,53,0.2); }
@keyframes heroPFloat {
    0% { transform: translateY(0) scale(1); opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { transform: translateY(-80px) scale(0.5); opacity: 0; }
}
.hero-banner::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: linear-gradient(90deg, #1a73e8, #ff6b35, #ffd700, #1a73e8);
    background-size: 300% 100%;
    animation: heroLine 4s linear infinite;
}
@keyframes heroLine {
    0% { background-position: 0% 0%; }
    100% { background-position: 300% 0%; }
}
/* heroFadeUp: removed opacity 0→1 to prevent CLS, keep subtle slide only */
@keyframes heroFadeUp {
    from { transform: translateY(8px); }
    to { transform: translateY(0); }
}
.hero-content { max-width: 700px; margin: 0 auto; position: relative; z-index: 1; }
.hero-title {
    font-size: 2rem;
    font-weight: 800;
    color: #ffffff;
    line-height: 1.3;
    margin-bottom: 14px;
    animation: heroFadeUp 0.4s ease-out both;
    will-change: transform;
}
.hero-highlight {
    color: #ff6b35;
    -webkit-text-fill-color: #ff6b35;
    background: none;
    -webkit-background-clip: unset;
    background-clip: unset;
}
.hero-subtitle {
    font-size: 0.95rem;
    color: rgba(255,255,255,0.6);
    max-width: 560px;
    margin: 0 auto 24px;
    line-height: 1.6;
    animation: heroFadeUp 0.4s ease-out 0.1s both;
    will-change: transform;
}
/* Hero Search */
.hero-search {
    display: flex;
    max-width: 520px;
    margin: 0 auto 30px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,0.3);
    animation: heroFadeUp 0.4s ease-out 0.15s both;
    will-change: transform;
    height: 47px;
}
.hero-search-input {
    flex: 1;
    padding: 13px 18px;
    border: none;
    font-size: 0.88rem;
    outline: none;
    color: #333;
    background: #fff;
}
.hero-search-input::placeholder { color: #aaa; }
.hero-search-btn {
    background: #1a73e8;
    color: white;
    border: none;
    padding: 13px 22px;
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s;
    white-space: nowrap;
}
.hero-search-btn:hover { background: #1567d3; }

.hero-stats {
    display: flex;
    justify-content: center;
    gap: 50px;
    animation: heroFadeUp 0.4s ease-out 0.2s both;
    will-change: transform;
}
.hero-stat { text-align: center; }
.hero-stat-number { font-size: 1.7rem; font-weight: 900; color: #ffffff; }
.hero-stat-label { font-size: 0.72rem; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px; }

@media (max-width: 768px) {
    .hero-banner { padding: 20px 14px 18px; }
    .hero-title { font-size: 1.15rem; margin-bottom: 6px; }
    .hero-subtitle { font-size: 0.72rem; margin-bottom: 12px; }
    .hero-search { max-width: 100%; margin-bottom: 16px; border-radius: 6px; }
    .hero-search-input { padding: 10px 12px; font-size: 0.75rem; }
    .hero-search-btn { padding: 10px 14px; font-size: 0.75rem; }
    .hero-stats { gap: 20px; }
    .hero-stat-number { font-size: 1rem; }
    .hero-stat-label { font-size: 0.55rem; }
}

/* ========== SERVICE STRIP ========== */
.service-strip {
    background: #f0f2f5;
    padding: 30px 20px 20px;
}
.service-strip-inner {
    max-width: 1100px;
    margin: 0 auto;
    display: flex;
    justify-content: center;
    flex-wrap: nowrap;
    gap: 16px;
}
.service-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 18px 16px 14px;
    border-radius: 16px;
    text-decoration: none;
    transition: all 0.3s ease;
    min-width: 0;
    flex: 1;
    background: #ffffff;
    border: 1px solid #e8ecf0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.service-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    text-decoration: none;
    border-color: #c0d4f0;
}
.service-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    aspect-ratio: 1;
}
.service-icon img {
    width: 68px;
    height: 68px;
    object-fit: contain;
}
.service-card span {
    font-size: 0.85rem;
    font-weight: 700;
    color: #1f2a44;
    text-align: center;
    line-height: 1.2;
}
.service-card:hover span { color: #1a73e8; }
@media (max-width: 768px) {
    .service-strip { padding: 12px 10px 8px; position: relative; }
    .service-strip::after { display: none; }
    .service-strip-inner {
        gap: 6px;
        flex-wrap: wrap;
        justify-content: center;
        overflow-x: visible;
    }
    .service-card { padding: 8px 6px 6px; min-width: 0; flex: 0 0 calc(25% - 6px); border-radius: 10px; }
    .service-icon { width: 36px; height: 36px; }
    .service-icon img { width: 30px; height: 30px; }
    .service-card span { font-size: 0.55rem; }
}

/* ========== NEW SEO SECTIONS ========== */

/* Section Headings */
.seo-section { max-width: 100%; margin: 0 auto 0; padding: 60px 20px; background: #ffffff; }
.seo-section:nth-child(even) { background: #f5f7fb; }
.seo-section-inner { max-width: 1200px; margin: 0 auto; }
.seo-section-title {
    text-align: center; font-size: 1.7rem; font-weight: 800; color: #1a1d23; margin-bottom: 10px;
    position: relative; display: inline-block; width: 100%;
}
.seo-section-title::after {
    content: ''; display: block; width: 50px; height: 3px; margin: 12px auto 0;
    background: linear-gradient(90deg, #1a73e8, #ff6b35); border-radius: 3px;
}
.seo-section-subtitle { text-align: center; font-size: 0.95rem; color: #777; margin-bottom: 36px; max-width: 600px; margin-left: auto; margin-right: auto; line-height: 1.6; }

/* Guide + Payment Two-Column */
.guide-payment-wrapper {
    display: grid; grid-template-columns: 1fr 1fr; gap: 28px;
    max-width: 1200px; margin: 0 auto; align-items: start;
}
.guide-col, .payment-col {
    background: white; border-radius: 20px; padding: 32px 30px;
    box-shadow: 0 1px 8px rgba(0,0,0,0.04); border: 1px solid #e8ecf2;
}
.guide-col-title { font-size: 1.3rem; font-weight: 700; color: #1e2128; margin-bottom: 4px; }
.guide-col-subtitle { font-size: 0.85rem; color: #94a3b8; margin-bottom: 20px; line-height: 1.5; }
.guide-promo {
    background: linear-gradient(135deg, #fff8e1, #fff3cd); border: 1px solid #ffd54f; border-radius: 10px;
    padding: 12px 18px; font-size: 0.85rem; color: #6d4c00; margin-bottom: 24px; display: flex; align-items: center; gap: 8px;
}
.guide-promo i { color: #e65100; font-size: 0.9rem; }
.guide-promo a { color: #1a73e8; font-weight: 700; text-decoration: none; }
.guide-promo a:hover { text-decoration: underline; }

/* Timeline */
.timeline { position: relative; padding-left: 42px; }
.timeline::before {
    content: ''; position: absolute; left: 17px; top: 16px; bottom: 16px;
    width: 3px; background: linear-gradient(180deg, #34a853 0%, #a8d94e 35%, #fbbc04 65%, #ea8600 100%);
    border-radius: 3px;
}
.timeline-item { position: relative; margin-bottom: 28px; }
.timeline-item:last-child { margin-bottom: 0; }
.timeline-dot {
    width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center;
    justify-content: center; font-size: 0.8rem; color: white; flex-shrink: 0;
    position: absolute; left: -42px; top: 0; z-index: 1;
    border: 4px solid white; box-shadow: 0 1px 4px rgba(0,0,0,0.12);
}
.timeline-dot.blue { background: #4285f4; }
.timeline-dot.orange { background: #ea8600; }
.timeline-dot.green { background: #34a853; }
.timeline-dot.red { background: #ea4335; }
.timeline-dot.purple { background: #a142f4; }
.timeline-content h4 { font-size: 0.92rem; font-weight: 700; color: #1e2128; margin: 0 0 4px; line-height: 1.4; }
.timeline-content p { font-size: 0.84rem; color: #64748b; line-height: 1.6; margin: 0; }

/* Payment Tabs */
.payment-tabs { display: flex; gap: 10px; margin-bottom: 24px; flex-wrap: wrap; }
.payment-tab {
    padding: 8px 20px; border-radius: 20px; border: 1px solid #dde3ec;
    background: white; font-size: 0.84rem; font-weight: 600; color: #64748b;
    cursor: pointer; transition: all 0.2s;
}
.payment-tab.active { background: #3b82f6; color: white; border-color: #3b82f6; }
.payment-tab:hover:not(.active) { border-color: #93b4f5; color: #3b82f6; background: #f0f4ff; }
.payment-panel { display: none; }
.payment-panel.active { display: block; }

/* Payment Brand */
.payment-brand { display: flex; flex-direction: column; align-items: center; gap: 8px; margin-bottom: 24px; padding: 16px 0; }
.payment-brand-icon {
    width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center;
    justify-content: center; font-size: 1.6rem; font-weight: 800; color: white;
}
.payment-brand-icon.momo { background: #d63384; }
.payment-brand-icon.vnpay { background: #0066b2; }
.payment-brand-icon.bank { background: #3b82f6; font-size: 1.8rem; }
.payment-brand-icon.crypto { background: #f7931a; }
.payment-brand-name { font-size: 1.05rem; font-weight: 700; color: #1e2128; }
.payment-brand-desc { font-size: 0.8rem; color: #94a3b8; }

/* Payment Info */
.payment-info-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 12px 0; border-bottom: 1px solid #f1f3f7; font-size: 0.88rem;
}
.payment-info-row:last-of-type { border-bottom: none; }
.payment-info-row span { color: #94a3b8; font-weight: 400; }
.payment-info-row strong { color: #1e2128; font-weight: 600; }
.payment-note {
    background: #f0f5ff; border: 1px solid #d6e3f8; border-radius: 10px;
    padding: 14px 18px; font-size: 0.82rem; color: #475569; margin-top: 20px; line-height: 1.6;
    display: flex; align-items: flex-start; gap: 8px;
}
.payment-note i { color: #3b82f6; margin-top: 2px; flex-shrink: 0; }

@media (max-width: 768px) {
    .guide-payment-wrapper { grid-template-columns: 1fr; gap: 16px; }
    .guide-col, .payment-col { padding: 22px 18px; border-radius: 14px; }
    .guide-col-title { font-size: 1.1rem; }
    .timeline { padding-left: 38px; }
    .timeline-dot { left: -38px; width: 32px; height: 32px; font-size: 0.7rem; }
    .timeline-content h4 { font-size: 0.85rem; }
    .timeline-content p { font-size: 0.78rem; }
    .payment-tabs { gap: 6px; }
    .payment-tab { padding: 6px 14px; font-size: 0.78rem; }
}

/* Why Choose Us */
.why-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; max-width: 1200px; margin: 0 auto; }
.why-card {
    background: white; border-radius: 16px; padding: 30px 22px; text-align: center;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04); border: 1px solid #eef0f4;
    transition: all 0.3s ease; position: relative; overflow: hidden;
}
.why-card::before {
    content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 3px;
    background: linear-gradient(90deg, #1a73e8, #ff6b35); transform: scaleX(0);
    transform-origin: left; transition: transform 0.3s ease;
}
.why-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
.why-card:hover::before { transform: scaleX(1); }
.why-icon {
    width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center;
    justify-content: center; margin: 0 auto 16px; font-size: 1.5rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.why-card:hover .why-icon { transform: scale(1.1); }
.why-icon.speed { background: linear-gradient(135deg, #e8f5e9, #c8e6c9); color: #2e7d32; }
.why-icon.price { background: linear-gradient(135deg, #fff3e0, #ffe0b2); color: #e65100; }
.why-icon.secure { background: linear-gradient(135deg, #e3f2fd, #bbdefb); color: #1565c0; }
.why-icon.auto { background: linear-gradient(135deg, #fce4ec, #f8bbd0); color: #c62828; }
.why-icon.support { background: linear-gradient(135deg, #f3e5f5, #e1bee7); color: #6a1b9a; }
.why-icon.update { background: linear-gradient(135deg, #e0f7fa, #b2ebf2); color: #00695c; }
.why-title { font-size: 0.98rem; font-weight: 700; color: #1a1d23; margin-bottom: 8px; }
.why-desc { font-size: 0.84rem; color: #777; line-height: 1.6; }
@media (max-width: 768px) { .why-grid { grid-template-columns: 1fr 1fr; gap: 12px; } .why-card { padding: 20px 14px; } .why-icon { width: 46px; height: 46px; font-size: 1.2rem; } .why-title { font-size: 0.82rem; } .why-desc { font-size: 0.72rem; } }
@media (min-width: 769px) and (max-width: 992px) { .why-grid { grid-template-columns: repeat(2, 1fr); } }

/* Testimonials */
.testimonials-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; max-width: 1200px; margin: 0 auto; }
.testimonial-card {
    background: white; border-radius: 16px; padding: 26px; position: relative;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04); border: 1px solid #eef0f4;
    transition: all 0.3s ease;
}
.testimonial-card::before {
    content: '\201C'; position: absolute; top: 16px; right: 20px;
    font-size: 3rem; color: rgba(26,115,232,0.1); font-family: Georgia, serif; line-height: 1;
}
.testimonial-card:hover { transform: translateY(-4px); box-shadow: 0 8px 28px rgba(0,0,0,0.1); }
.testimonial-stars { color: #f59e0b; font-size: 0.85rem; margin-bottom: 12px; letter-spacing: 2px; }
.testimonial-text { font-size: 0.88rem; color: #555; line-height: 1.7; font-style: italic; margin-bottom: 18px; }
.testimonial-author { display: flex; align-items: center; gap: 12px; }
.testimonial-avatar {
    width: 42px; height: 42px; border-radius: 50%;
    background: linear-gradient(135deg, #1a73e8, #0d47a1); color: white;
    display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem;
}
.testimonial-name { font-weight: 700; color: #1a1d23; font-size: 0.92rem; }
.testimonial-role { font-size: 0.78rem; color: #999; }
@media (max-width: 768px) { .testimonials-grid { grid-template-columns: 1fr; } }
@media (min-width: 769px) and (max-width: 992px) { .testimonials-grid { grid-template-columns: repeat(2, 1fr); } }

/* Price Comparison */
.price-compare-table {
    width: 100%; max-width: 1200px; margin: 0 auto; border-collapse: separate;
    border-spacing: 0; border-radius: 16px; overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}
.price-compare-table thead th {
    background: linear-gradient(135deg, #1a1d23, #2d3142); color: white;
    padding: 16px; font-size: 0.95rem; font-weight: 700; text-align: center;
}
.price-compare-table tbody td { padding: 15px 16px; text-align: center; border-bottom: 1px solid #f0f0f0; font-size: 0.92rem; transition: background 0.2s; }
.price-compare-table tbody tr:last-child td { border-bottom: none; }
.price-compare-table tbody tr:hover { background: #f0f4ff; }
.price-highlight { background: linear-gradient(135deg, #e8f5e9, #f1f8e9) !important; }
.price-tag { display: inline-block; background: linear-gradient(135deg, #28a745, #1e7e34); color: white; padding: 5px 16px; border-radius: 20px; font-weight: 700; font-size: 0.88rem; }
.price-tag-red { background: linear-gradient(135deg, #dc3545, #c82333); color: white; padding: 5px 16px; border-radius: 20px; font-weight: 700; font-size: 0.88rem; display: inline-block; }
.price-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; margin: 0 -20px; padding: 0 20px; }
@media (max-width: 768px) {
    .price-compare-table { font-size: 0.78rem; min-width: 500px; }
    .price-compare-table thead th { padding: 10px 8px; font-size: 0.75rem; }
    .price-compare-table tbody td { padding: 10px 8px; font-size: 0.78rem; }
    .price-tag, .price-tag-red { padding: 3px 10px; font-size: 0.72rem; }
}

/* FAQ Section */
.faq-container { max-width: 800px; margin: 0 auto; }
.faq-item {
    background: white; border-radius: 12px; margin-bottom: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.04); border: 1px solid #eef0f4;
    overflow: hidden; transition: border-color 0.3s, box-shadow 0.3s;
}
.faq-item:hover { border-color: #d0d8e8; }
.faq-item.active { border-color: #1a73e8; box-shadow: 0 4px 16px rgba(26,115,232,0.1); }
.faq-question {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 22px; cursor: pointer; font-weight: 700; font-size: 0.93rem;
    color: #1a1d23; background: white; transition: background 0.2s, color 0.2s; gap: 12px;
}
.faq-question:hover { background: #f8f9ff; }
.faq-item.active .faq-question { color: #1a73e8; }
.faq-question i { font-size: 0.8rem; color: #1a73e8; transition: transform 0.3s; flex-shrink: 0; }
.faq-item.active .faq-question i { transform: rotate(180deg); }
.faq-answer { padding: 0 22px; max-height: 0; overflow: hidden; transition: max-height 0.4s ease, padding 0.3s ease; font-size: 0.88rem; color: #666; line-height: 1.7; }
.faq-item.active .faq-answer { max-height: 300px; padding: 0 22px 20px; }

/* Recent Blog Posts */
.blog-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; max-width: 1200px; margin: 0 auto; }
.blog-card {
    background: white; border-radius: 16px; overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,0.05); border: 1px solid #eef0f4;
    transition: all 0.3s ease; text-decoration: none; color: inherit; display: block;
}
.blog-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.12); text-decoration: none; color: inherit; }
.blog-card-icon {
    height: 100px; background: linear-gradient(135deg, #1a1d23, #2d3142);
    display: flex; align-items: center; justify-content: center;
    font-size: 2.5rem; color: rgba(255,255,255,0.7);
    position: relative; overflow: hidden;
}
.blog-card-icon::after {
    content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.05), transparent);
    transition: left 0.6s ease;
}
.blog-card:hover .blog-card-icon::after { left: 100%; }
.blog-card-body { padding: 18px; }
.blog-card-title { font-size: 0.92rem; font-weight: 700; color: #1a1d23; margin-bottom: 8px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.blog-card-meta { font-size: 0.78rem; color: #999; }
@media (max-width: 768px) { .blog-grid { grid-template-columns: 1fr; } }
@media (min-width: 769px) and (max-width: 992px) { .blog-grid { grid-template-columns: repeat(2, 1fr); } }

/* Footer */
.site-footer { background: #1a1d23; color: #ccc; padding: 55px 20px 20px; margin-top: 0; }
.footer-container { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(4, 1fr); gap: 30px; }
.footer-col h4 { color: white; font-size: 1rem; font-weight: 700; margin-bottom: 16px; position: relative; padding-bottom: 10px; }
.footer-col h4::after { content: ''; position: absolute; bottom: 0; left: 0; width: 36px; height: 3px; background: linear-gradient(90deg, #1a73e8, #ff6b35); border-radius: 2px; }
.footer-col p { font-size: 0.85rem; line-height: 1.7; color: #999; }
.footer-col ul { list-style: none; padding: 0; margin: 0; }
.footer-col ul li { margin-bottom: 10px; }
.footer-col ul li a { color: #999; text-decoration: none; font-size: 0.85rem; transition: color 0.2s, padding-left 0.2s; display: inline-flex; align-items: center; gap: 6px; }
.footer-col ul li a:hover { color: #ff6b35; padding-left: 4px; text-decoration: none; }
.footer-bottom { max-width: 1200px; margin: 30px auto 0; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.08); text-align: center; font-size: 0.8rem; color: #666; }
@media (max-width: 768px) { .footer-container { grid-template-columns: 1fr 1fr; } }
@media (max-width: 480px) { .footer-container { grid-template-columns: 1fr; } }

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
                    <img src="images/logo.jpg" alt="UnlockTool.us Logo" width="44" height="44">
                </div>
                <div class="logo-text">
                    <h1 class="logo-title">UNLOCKTOOL.US</h1>
                    <p class="logo-subtitle">Thuê tài khoản tự động 24/7</p>
                </div>
            </a>
            
            
            <div class="header-right-actions">
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

<!-- ========== HERO BANNER ========== -->
<section class="hero-banner" id="hero">
    <div class="hero-particles"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></div>
    <div class="hero-content">
        <h1 class="hero-title">Thuê Tài Khoản <span class="hero-highlight">UnlockTool</span> Tự Động 24/7</h1>
        <p class="hero-subtitle">Hệ thống cho thuê tài khoản UnlockTool, Griffin, Samsung Tool và hơn 10+ công cụ GSM khác. Nhận tài khoản ngay sau khi thanh toán!</p>
        <div class="hero-search">
            <input type="text" id="heroSearchInput" placeholder="Tìm kiếm: Mua sơ đồ, Unlocktool, tool FRP, Credits, Bypass A12..." class="hero-search-input">
            <button id="heroSearchBtn" class="hero-search-btn"><i class="fas fa-search"></i> Tìm kiếm</button>
        </div>
        <div class="hero-stats">
            <div class="hero-stat">
                <div class="hero-stat-number">6,500+</div>
                <div class="hero-stat-label">Đơn hàng</div>
            </div>
            <div class="hero-stat">
                <div class="hero-stat-number">99%</div>
                <div class="hero-stat-label">Hài lòng</div>
            </div>
            <div class="hero-stat">
                <div class="hero-stat-number">10+</div>
                <div class="hero-stat-label">Dịch vụ</div>
            </div>
        </div>
    </div>
</section>

<!-- ========== SERVICE STRIP ========== -->
<section class="service-strip">
    <div class="service-strip-inner">
        <a class="service-card" href="https://thuetaikhoan.net/thue-unlocktool.php" target="_blank">
            <div class="service-icon"><img src="images/services/unlocktool.webp" alt="Unlocktool" loading="lazy" width="68" height="68"></div>
            <span>Unlocktool</span>
        </a>
        <a class="service-card" href="https://thuetaikhoan.net/thue-griffin.php" target="_blank">
            <div class="service-icon"><img src="images/services/griffin.webp" alt="Griffin-Unlocker" loading="lazy" width="68" height="68"></div>
            <span>Griffin</span>
        </a>
        <a class="service-card" href="https://thuetaikhoan.net/thue-amt.php" target="_blank">
            <div class="service-icon"><img src="images/services/amt.webp" alt="Android Multitool" loading="lazy" width="68" height="68"></div>
            <span>Android Multitool</span>
        </a>
        <a class="service-card" href="https://thuetaikhoan.net/thue-tsm.php" target="_blank">
            <div class="service-icon"><img src="images/services/tsm.webp" alt="TSM Tool" loading="lazy" width="68" height="68"></div>
            <span>TSM Tool</span>
        </a>
        <a class="service-card" href="https://thuetaikhoan.net/thue-vietmap.php" target="_blank">
            <div class="service-icon"><img src="images/services/vietmap.webp" alt="Vietmap Live" loading="lazy" width="68" height="68"></div>
            <span>Vietmap Live</span>
        </a>
        <a class="service-card" href="https://thuetaikhoan.net/thue-kg-killer.php" target="_blank">
            <div class="service-icon"><img src="images/services/kg-killer.webp" alt="KG Killer" loading="lazy" width="68" height="68"></div>
            <span>KG Killer</span>
        </a>
        <a class="service-card" href="https://thuetaikhoan.net/thue-dft.php" target="_blank">
            <div class="service-icon"><img src="images/services/dft-pro.webp" alt="DFT Pro Tool" loading="lazy" width="68" height="68"></div>
            <span>DFT Pro</span>
        </a>
        <a class="service-card" href="https://thuetaikhoan.net/thue-samsung-tool.php" target="_blank">
            <div class="service-icon"><img src="images/services/samsung-tool.webp" alt="Samsung Tool" loading="lazy" width="68" height="68"></div>
            <span>Samsung Tool</span>
        </a>
    </div>
</section>

<div class="overlay" id="account-table">

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
            <?php if ($result): ?>
            <?php while ($row = $result->fetch_assoc()): 
                $time_info = getTimeRemaining($row['expires_at']);
                $is_expired = $time_info ? $time_info['expired'] : true;
            ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['type'] ?? 'Không rõ'); ?></td>
                    <td>
                        <?php if ($row['is_available']): ?>
                            <button type="button" class="btn btn-primary btn-sm btn-blink" data-toggle="modal" data-target="#rentModal" data-account-id="<?php echo $row['id']; ?>">Thuê ngay</button>
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
            <?php else: ?>
                <tr><td colspan="7" class="text-center text-muted py-3">Đang tải dữ liệu...</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ========== SECTION: Hướng Dẫn + Thanh Toán ========== -->
<section class="seo-section" id="huong-dan">
    <div class="guide-payment-wrapper">
        <!-- LEFT: Timeline Steps -->
        <div class="guide-col">
            <h2 class="guide-col-title">Hướng dẫn thuê tài khoản</h2>
            <p class="guide-col-subtitle">Quy trình thuê tài khoản hoàn toàn tự động 24/7</p>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-dot blue"><i class="fas fa-list"></i></div>
                    <div class="timeline-content">
                        <h4>Bước 1: Chọn tài khoản UnlockTool</h4>
                        <p>Chọn tài khoản UnlockTool còn trống trong bảng phía trên, nhấn nút "Thuê ngay" để bắt đầu.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot orange"><i class="fas fa-shopping-cart"></i></div>
                    <div class="timeline-content">
                        <h4>Bước 2: Chọn gói thuê</h4>
                        <p>Chọn gói thuê phù hợp với nhu cầu của bạn, có thể là theo giờ, ngày. Tùy chọn voucher (nếu có).</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot green"><i class="fas fa-check-circle"></i></div>
                    <div class="timeline-content">
                        <h4>Bước 3: Xác nhận đơn thuê</h4>
                        <p>Xác nhận thuê và kiểm tra thông tin thanh toán.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot red"><i class="fas fa-credit-card"></i></div>
                    <div class="timeline-content">
                        <h4>Bước 4: Thanh toán</h4>
                        <p>Sau khi xác nhận, nhấn "Đến trang thanh toán" để đến trang thanh toán. Quét mã QR hoặc chuyển khoản theo thông tin hiển thị. Hệ thống sẽ tự động nhận diện giao dịch và cập nhật trạng thái đơn hàng.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot purple"><i class="fas fa-unlock-alt"></i></div>
                    <div class="timeline-content">
                        <h4>Bước 5: Nhận tài khoản</h4>
                        <p>Tại trang thanh toán, hệ thống sẽ tự động chuyển tới trang hiển thị thông tin tài khoản. Đăng ký tài khoản để kiểm tra được thông tin đơn cùng như nhận nhiều đặc quyền khuyến mãi.</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- RIGHT: Payment Methods -->
        <div class="payment-col">
            <h2 class="guide-col-title">Phương thức thanh toán</h2>
            <p class="guide-col-subtitle">Chuyển khoản ngân hàng — tự động xác nhận</p>
            <div class="payment-brand">
                <div class="payment-brand-icon bank">🏦</div>
                <div>
                    <div class="payment-brand-name">Ngân hàng</div>
                    <div class="payment-brand-desc">Chuyển khoản nội địa</div>
                </div>
            </div>
            <div class="payment-info-row"><span>Loại:</span><strong>Chuyển khoản ngân hàng</strong></div>
            <div class="payment-info-row"><span>Phương thức:</span><strong>Quét mã QR / CK thủ công</strong></div>
            <div class="payment-info-row"><span>Thời gian xử lý:</span><strong>Tức thì (QR) / 1-5 phút</strong></div>
            <div class="payment-note"><i class="fas fa-info-circle"></i> Hỗ trợ tất cả ngân hàng Việt Nam. Nội dung CK tự động, hệ thống nhận diện ngay.</div>
        </div>
    </div>
</section>

<!-- ========== SECTION: Tại Sao Chọn Chúng Tôi ========== -->
<section class="seo-section" id="tai-sao-chon">
    <h2 class="seo-section-title">Tại Sao Chọn UnlockTool.us?</h2>
    <p class="seo-section-subtitle">6 lý do hàng nghìn thợ sửa điện thoại tin dùng dịch vụ của chúng tôi</p>
    <div class="why-grid">
        <div class="why-card">
            <div class="why-icon speed"><i class="fas fa-bolt"></i></div>
            <div class="why-title">Nhận Tài Khoản Siêu Nhanh</div>
            <div class="why-desc">Hệ thống tự động 100%. Thanh toán xong 30 giây là có tài khoản, không cần chờ đợi admin duyệt.</div>
        </div>
        <div class="why-card">
            <div class="why-icon price"><i class="fas fa-tags"></i></div>
            <div class="why-title">Giá Thuê Cạnh Tranh Nhất</div>
            <div class="why-desc">Chỉ từ 8.000đ/6 giờ — giá rẻ nhất thị trường. Linh hoạt thuê theo giờ, ngày, tuần phù hợp mọi nhu cầu.</div>
        </div>
        <div class="why-card">
            <div class="why-icon secure"><i class="fas fa-shield-alt"></i></div>
            <div class="why-title">Tài Khoản Bản Quyền Riêng</div>
            <div class="why-desc">Tài khoản chính hãng, không dùng chung. Đảm bảo hoạt động ổn định xuyên suốt quá trình sử dụng.</div>
        </div>
        <div class="why-card">
            <div class="why-icon auto"><i class="fas fa-clock"></i></div>
            <div class="why-title">Hệ Thống Tự Động 24/7</div>
            <div class="why-desc">Thuê bất cứ lúc nào, kể cả 2h sáng. Hệ thống hoạt động hoàn toàn tự động, không phụ thuộc admin.</div>
        </div>
        <div class="why-card">
            <div class="why-icon support"><i class="fas fa-headset"></i></div>
            <div class="why-title">Hỗ Trợ Kỹ Thuật Tận Tình</div>
            <div class="why-desc">Đội ngũ kỹ thuật hỗ trợ qua Zalo, sẵn sàng giúp bạn xử lý các vấn đề kỹ thuật khi sử dụng tool.</div>
        </div>
        <div class="why-card">
            <div class="why-icon update"><i class="fas fa-sync-alt"></i></div>
            <div class="why-title">Cập Nhật Phiên Bản Mới Nhất</div>
            <div class="why-desc">Luôn sử dụng UnlockTool phiên bản mới nhất từ hãng. Hỗ trợ model Samsung, Xiaomi, Oppo... mới nhất.</div>
        </div>
    </div>
</section>

<!-- ========== SECTION: Khách Hàng Đánh Giá ========== -->
<section class="seo-section" id="danh-gia">
    <h2 class="seo-section-title">Khách Hàng Nói Gì Về UnlockTool.us?</h2>
    <p class="seo-section-subtitle">Hơn 6.500 đánh giá tích cực từ cộng đồng thợ sửa điện thoại</p>
    <div class="testimonials-grid">
        <div class="testimonial-card">
            <div class="testimonial-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
            <div class="testimonial-text">"Đã thuê UnlockTool ở đây hơn 10 lần. Thanh toán xong là nhận tài khoản liền. Xóa FRP Samsung A54, A34 đều thành công. Giá 8K/6h quá rẻ."</div>
            <div class="testimonial-author">
                <div class="testimonial-avatar">MT</div>
                <div><div class="testimonial-name">Minh Tuấn</div><div class="testimonial-role">Kỹ thuật viên phần mềm</div></div>
            </div>
        </div>
        <div class="testimonial-card">
            <div class="testimonial-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
            <div class="testimonial-text">"Hệ thống tự động 24/7 tiện lợi. Thuê lúc 2h sáng, chuyển tiền xong 30 giây là có tài khoản luôn. Rất hài lòng!"</div>
            <div class="testimonial-author">
                <div class="testimonial-avatar">TH</div>
                <div><div class="testimonial-name">Thanh Hải</div><div class="testimonial-role">Cửa hàng điện thoại (TP.HCM)</div></div>
            </div>
        </div>
        <div class="testimonial-card">
            <div class="testimonial-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
            <div class="testimonial-text">"Đã thử 3-4 trang khác nhau, chốt ở đây vì giá rẻ nhất, hệ thống tự động, hỗ trợ nhanh. Giới thiệu cho anh em trong hội thợ."</div>
            <div class="testimonial-author">
                <div class="testimonial-avatar">TL</div>
                <div><div class="testimonial-name">Thành Long</div><div class="testimonial-role">Cửa hàng điện thoại (Hà Nội)</div></div>
            </div>
        </div>
        <div class="testimonial-card">
            <div class="testimonial-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
            <div class="testimonial-text">"Là thợ sửa điện thoại, dùng trang này từ khi mới mở. Thuê hàng tuần, chưa bao giờ gặp vấn đề gì. Tài khoản luôn ổn định."</div>
            <div class="testimonial-author">
                <div class="testimonial-avatar">NT</div>
                <div><div class="testimonial-name">Ngọc Trâm</div><div class="testimonial-role">Kỹ thuật viên (Đà Nẵng)</div></div>
            </div>
        </div>
        <div class="testimonial-card">
            <div class="testimonial-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
            <div class="testimonial-text">"Nhờ UnlockTool.us mà nhận thêm dịch vụ xóa khóa cho khách. Thuê 8K, thu khách 100-200K. Trang chạy ổn định, rất đáng."</div>
            <div class="testimonial-author">
                <div class="testimonial-avatar">CM</div>
                <div><div class="testimonial-name">Công Minh</div><div class="testimonial-role">Chủ cửa hàng (Cần Thơ)</div></div>
            </div>
        </div>
        <div class="testimonial-card">
            <div class="testimonial-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
            <div class="testimonial-text">"Mình làm mobile ở quê, ít gặp máy khó nên mua tool năm rất phí. Từ ngày biết dịch vụ này tiết kiệm được bao nhiêu. Giá quá ổn!"</div>
            <div class="testimonial-author">
                <div class="testimonial-avatar">HM</div>
                <div><div class="testimonial-name">Hoàng Mobile</div><div class="testimonial-role">Cửa hàng điện thoại (Nghệ An)</div></div>
            </div>
        </div>
    </div>
</section>

<!-- ========== SECTION: So Sánh Giá Thuê vs Mua ========== -->
<section class="seo-section" id="so-sanh-gia">
    <h2 class="seo-section-title">Thuê UnlockTool Tiết Kiệm Hơn Mua</h2>
    <p class="seo-section-subtitle">So sánh chi phí giữa thuê tài khoản và mua license chính hãng</p>
    <div class="price-table-wrap">
        <table class="price-compare-table">
            <thead>
                <tr>
                    <th>Hạng mục</th>
                    <th>Thuê tại UnlockTool.us</th>
                    <th>Mua License chính hãng</th>
                </tr>
            </thead>
            <tbody>
                <tr class="price-highlight">
                    <td><strong>💰 Chi phí</strong></td>
                    <td><span class="price-tag">Từ 8.000đ (6 giờ)</span></td>
                    <td><span class="price-tag-red">~1.150.000đ/năm ($46 USD)</span></td>
                </tr>
                <tr>
                    <td><strong>⏱️ Thời gian sử dụng</strong></td>
                    <td>Linh hoạt: 6h, 12h, 24h, 3 ngày, 7 ngày</td>
                    <td>Cố định: 1 tháng / 1 năm</td>
                </tr>
                <tr>
                    <td><strong>🔧 Tính năng</strong></td>
                    <td>Full tính năng (giống mua)</td>
                    <td>Full tính năng</td>
                </tr>
                <tr>
                    <td><strong>📦 Dongle/Box</strong></td>
                    <td>Không cần (chỉ cần tải phần mềm)</td>
                    <td>Không cần</td>
                </tr>
                <tr>
                    <td><strong>🔄 Cập nhật</strong></td>
                    <td>Luôn version mới nhất</td>
                    <td>Luôn version mới nhất</td>
                </tr>
                <tr class="price-highlight">
                    <td><strong>📊 Phù hợp cho</strong></td>
                    <td><strong>Thợ làm ít, không cần tool cả tháng</strong></td>
                    <td><strong>Cửa hàng lớn, dùng hàng ngày</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<!-- ========== SECTION: FAQ ========== -->
<section class="seo-section" id="faq">
    <h2 class="seo-section-title">Câu Hỏi Thường Gặp</h2>
    <p class="seo-section-subtitle">Giải đáp các thắc mắc phổ biến khi thuê tài khoản UnlockTool</p>
    <div class="faq-container">
        <div class="faq-item">
            <div class="faq-question" onclick="this.parentElement.classList.toggle('active')">
                <span>UnlockTool là gì?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">UnlockTool là phần mềm chuyên dụng dùng để mở khóa điện thoại, xóa FRP (Google Account), bypass iCloud, xóa Mi Account, flash firmware... Hỗ trợ đa dòng máy Samsung, Xiaomi, Oppo, Vivo, Realme, iPhone. Đây là công cụ được hàng nghìn thợ sửa điện thoại tin dùng.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question" onclick="this.parentElement.classList.toggle('active')">
                <span>Thuê UnlockTool bao lâu nhận được tài khoản?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">Hệ thống tự động 24/7, sau khi thanh toán thành công bạn sẽ nhận tài khoản ngay lập tức (thường trong vòng 30 giây đến 1 phút). Không cần chờ admin duyệt.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question" onclick="this.parentElement.classList.toggle('active')">
                <span>Có thể đổi máy tính khi đang thuê không?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">Không. UnlockTool quy định đổi PC sau mỗi 6 giờ. Nếu bạn đăng nhập máy thứ 2 khi chưa đủ thời gian, hệ thống sẽ báo lỗi liên kết máy cũ. Bạn buộc phải tiếp tục sử dụng trên máy tính cũ.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question" onclick="this.parentElement.classList.toggle('active')">
                <span>Thời gian thuê tính như thế nào?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">Thời gian tính theo real-time từ lúc bạn nhận tài khoản. Đồng hồ đếm ngược liên tục 24/7. Nếu mất điện hoặc mạng, thời gian vẫn tiếp tục đếm. Tuy nhiên bạn có thể đăng nhập lại ngay khi có mạng.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question" onclick="this.parentElement.classList.toggle('active')">
                <span>Thanh toán bằng hình thức nào?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">Hỗ trợ chuyển khoản tất cả ngân hàng (Vietcombank, MB Bank, Techcombank, BIDV...) và ví điện tử. Hệ thống xác nhận tự động, nhận tài khoản ngay sau khi thanh toán thành công.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question" onclick="this.parentElement.classList.toggle('active')">
                <span>Cần chuẩn bị gì trước khi thuê?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">Bạn cần tải sẵn phần mềm UnlockTool từ trang chủ, cài đặt Driver đầy đủ cho dòng máy cần làm (Samsung, Xiaomi, Qualcomm...) để tiết kiệm thời gian thuê. Nếu cần hỗ trợ cài driver, liên hệ Zalo.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question" onclick="this.parentElement.classList.toggle('active')">
                <span>UnlockTool hỗ trợ những dòng máy nào?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">UnlockTool hỗ trợ hầu hết các dòng: Samsung (Galaxy A, S, M, F series), Xiaomi/Redmi/POCO, Oppo, Vivo, Realme, Huawei, LG, Motorola, và iPhone (bypass iCloud, MDM, Hello Screen). Phần mềm cập nhật liên tục để hỗ trợ model mới nhất kể cả Android 16.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question" onclick="this.parentElement.classList.toggle('active')">
                <span>Có hỗ trợ cài Driver không?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">Có. Trên giao diện UnlockTool có mục Config → Install Driver, bạn cài trực tiếp từ đó. Ngoài ra, bạn có thể inbox Zalo để admin gửi bộ Driver chuẩn cho dòng máy bạn cần.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question" onclick="this.parentElement.classList.toggle('active')">
                <span>Giá đại lý có không?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">Có. Nếu bạn là cửa hàng cần thuê thường xuyên hoặc thuê số lượng nhiều, hãy liên hệ trực tiếp Zalo để có giá đại lý tốt nhất. Chúng tôi hỗ trợ đặc biệt cho khách hàng lâu dài.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question" onclick="this.parentElement.classList.toggle('active')">
                <span>Quên thông tin tài khoản thì phải làm sao?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">Bạn có thể tra cứu lại đơn hàng bằng cách nhập mã đơn hoặc nội dung chuyển khoản ngay trên website (ô "Tìm đơn" ở header). Ngoài ra, hãy liên hệ Zalo để được admin hỗ trợ tra cứu lại thông tin tài khoản.</div>
        </div>
    </div>
</section>

<!-- ========== SECTION: Bài Viết Gần Đây ========== -->
<section class="seo-section" id="bai-viet">
    <h2 class="seo-section-title">Bài Viết Hữu Ích</h2>
    <p class="seo-section-subtitle">Hướng dẫn, thủ thuật và kiến thức chuyên sâu về UnlockTool & GSM</p>
    <div class="blog-grid">
        <a href="/articles/3-xoa-frp-samsung.php" class="blog-card">
            <div class="blog-card-icon" style="background: linear-gradient(135deg, #1565c0, #0d47a1);"><i class="fas fa-mobile-alt"></i></div>
            <div class="blog-card-body">
                <div class="blog-card-title">Hướng Dẫn Xóa FRP Samsung Bằng UnlockTool (Tất Cả Dòng)</div>
                <div class="blog-card-meta"><i class="fas fa-clock"></i> Cập nhật 2026</div>
            </div>
        </a>
        <a href="/articles/5-bypass-icloud-iphone.php" class="blog-card">
            <div class="blog-card-icon" style="background: linear-gradient(135deg, #424242, #212121);"><i class="fab fa-apple"></i></div>
            <div class="blog-card-body">
                <div class="blog-card-title">Bypass iCloud iPhone Bằng UnlockTool — Hướng Dẫn Chi Tiết</div>
                <div class="blog-card-meta"><i class="fas fa-clock"></i> Cập nhật 2026</div>
            </div>
        </a>
        <a href="/articles/19-cach-su-dung-unlocktool.php" class="blog-card">
            <div class="blog-card-icon" style="background: linear-gradient(135deg, #2e7d32, #1b5e20);"><i class="fas fa-book-open"></i></div>
            <div class="blog-card-body">
                <div class="blog-card-title">Hướng Dẫn Sử Dụng UnlockTool Từ A-Z Cho Người Mới</div>
                <div class="blog-card-meta"><i class="fas fa-clock"></i> Cập nhật 2026</div>
            </div>
        </a>
        <a href="/articles/42-xoa-mi-account-xiaomi.php" class="blog-card">
            <div class="blog-card-icon" style="background: linear-gradient(135deg, #ff6f00, #e65100);"><i class="fas fa-unlock"></i></div>
            <div class="blog-card-body">
                <div class="blog-card-title">Xóa Mi Account Xiaomi — Hướng Dẫn Bypass Mi Cloud</div>
                <div class="blog-card-meta"><i class="fas fa-clock"></i> Cập nhật 2026</div>
            </div>
        </a>
        <a href="/articles/14-thue-unlocktool-gia-re.php" class="blog-card">
            <div class="blog-card-icon" style="background: linear-gradient(135deg, #6a1b9a, #4a148c);"><i class="fas fa-tag"></i></div>
            <div class="blog-card-body">
                <div class="blog-card-title">Thuê UnlockTool Giá Rẻ — Bảng Giá & Đánh Giá Chi Tiết 2026</div>
                <div class="blog-card-meta"><i class="fas fa-clock"></i> Cập nhật 2026</div>
            </div>
        </a>
        <a href="/articles/45-vietmap-live-pro.php" class="blog-card">
            <div class="blog-card-icon" style="background: linear-gradient(135deg, #00695c, #004d40);"><i class="fas fa-map-marked-alt"></i></div>
            <div class="blog-card-body">
                <div class="blog-card-title">Vietmap Live Pro — Thuê Bản Quyền Giá Rẻ Nhất</div>
                <div class="blog-card-meta"><i class="fas fa-clock"></i> Cập nhật 2026</div>
            </div>
        </a>
    </div>
    <div class="text-center mt-4">
        <a href="/articles/" style="display:inline-flex;align-items:center;gap:8px;padding:12px 28px;background:linear-gradient(135deg,#1a73e8,#0d47a1);color:white;border-radius:30px;font-weight:700;text-decoration:none;box-shadow:0 4px 15px rgba(26,115,232,0.3);transition:all 0.3s;">Xem tất cả bài viết <i class="fas fa-arrow-right"></i></a>
    </div>
</section>

<!-- Modal: Lịch sử thuê -->
<div class="modal fade" id="historyModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Lịch sử thuê tài khoản (30 ngày gần nhất)</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <?php if ($history_result && $history_result->num_rows > 0): ?>
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
                    <?php if ($prices_result): ?>
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
                    <?php endif; ?>
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

<!-- ========== FOOTER ========== -->
<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-col">
            <h4>UnlockTool.us</h4>
            <p>Dịch vụ cho thuê tài khoản UnlockTool uy tín, giá rẻ nhất Việt Nam. Hệ thống tự động 24/7, nhận tài khoản ngay sau thanh toán.</p>
            <p style="margin-top:12px;"><strong style="color:white;">Hotline:</strong> 0777 333 763</p>
        </div>
        <div class="footer-col">
            <h4>Hướng Dẫn</h4>
            <ul>
                <li><a href="/articles/19-cach-su-dung-unlocktool.php"><i class="fas fa-angle-right"></i> Cách sử dụng UnlockTool</a></li>
                <li><a href="/articles/2-tai-unlocktool.php"><i class="fas fa-angle-right"></i> Tải UnlockTool</a></li>
                <li><a href="/articles/6-cai-driver-unlocktool.php"><i class="fas fa-angle-right"></i> Cài Driver UnlockTool</a></li>
                <li><a href="/articles/78-huong-dan-thanh-toan.php"><i class="fas fa-angle-right"></i> Hướng dẫn thanh toán</a></li>
                <li><a href="/articles/77-cau-hoi-thuong-gap.php"><i class="fas fa-angle-right"></i> Câu hỏi thường gặp</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Dịch Vụ</h4>
            <ul>
                <li><a href="/articles/14-thue-unlocktool-gia-re.php"><i class="fas fa-angle-right"></i> Thuê UnlockTool giá rẻ</a></li>
                <li><a href="/articles/3-xoa-frp-samsung.php"><i class="fas fa-angle-right"></i> Xóa FRP Samsung</a></li>
                <li><a href="/articles/5-bypass-icloud-iphone.php"><i class="fas fa-angle-right"></i> Bypass iCloud iPhone</a></li>
                <li><a href="/articles/42-xoa-mi-account-xiaomi.php"><i class="fas fa-angle-right"></i> Xóa Mi Account Xiaomi</a></li>
                <li><a href="/articles/45-vietmap-live-pro.php"><i class="fas fa-angle-right"></i> Thuê Vietmap Live Pro</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Liên Hệ</h4>
            <ul>
                <li><a href="https://zalo.me/0777333763" target="_blank"><i class="fas fa-comments"></i> Zalo: Mai Quyên</a></li>
                <li><a href="https://zalo.me/0799161640" target="_blank"><i class="fas fa-comments"></i> Zalo: Thanhtaj</a></li>
                <li><a href="tel:0799161640"><i class="fas fa-phone-alt"></i> 0799 161 640</a></li>
                <li><a href="tel:0777333763"><i class="fas fa-phone-alt"></i> 0777 333 763</a></li>
                <li><a href="https://zalo.me/g/qncjky686" target="_blank"><i class="fas fa-users"></i> Group Hỗ Trợ Zalo</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© 2024 - 2026 UnlockTool.us — Dịch vụ cho thuê tài khoản UnlockTool uy tín tại Việt Nam</p>
    </div>
</footer>

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

    // Hero search → redirect to thuetaikhoan.net
    function doHeroSearch() {
        var q = document.getElementById('heroSearchInput');
        if (q && q.value.trim()) {
            window.open('https://thuetaikhoan.net/ord-services?q=' + encodeURIComponent(q.value.trim()), '_blank');
        }
    }
    $(document).on('click', '#heroSearchBtn', doHeroSearch);
    $(document).on('keypress', '#heroSearchInput', function(e) {
        if (e.which === 13) doHeroSearch();
    });

    // Payment tabs
    $(document).on('click', '.payment-tab', function() {
        var tab = $(this).data('tab');
        $('.payment-tab').removeClass('active');
        $(this).addClass('active');
        $('.payment-panel').removeClass('active');
        $('#panel-' + tab).addClass('active');
    });
});
</script>
</body>
</html>
