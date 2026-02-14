<?php
/**
 * Header chung cho các trang frontend
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $page_title ?? 'Thuê tài khoản Unlocktool tự động'; ?></title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico?v=1">
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="//code.jquery.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        :root {
            --bg-image: url('https://unlocktool.us/3b75cb84-2ea8-4a5f-bbee-3f3d32cc64693782334b80e7292aba0fde71e1fd9267.jpg');
            --primary: #007bff;
            --success: #28a745;
            --danger: #dc3545;
            --overlay-bg: rgba(255, 255, 255, 0.92);
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-image) no-repeat center top fixed;
            background-size: cover;
            background-attachment: fixed;
            min-height: 100vh;
            background-color: #f0f0f0;
        }
        .overlay {
            background-color: var(--overlay-bg);
            padding: 30px;
            margin: 40px auto 80px;
            border-radius: 12px;
            max-width: 1200px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }
        .badge-success { background-color: var(--success); }
        .badge-danger { background-color: var(--danger); }
        .card {
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
            border-radius: 18px;
            background: #fff;
        }
        .copy-btn {
            font-size: 0.85rem;
            padding: 3px 10px;
            margin-left: 8px;
            border-radius: 6px;
        }
        @media (max-width: 768px) {
            .overlay { padding: 20px; margin: 0 10px; border-radius: 8px; }
        }
        <?php if (!empty($extra_css)) echo $extra_css; ?>
    </style>
    <?php if (!empty($extra_head)) echo $extra_head; ?>
</head>
<body class="<?php echo $body_class ?? 'p-4'; ?>">

