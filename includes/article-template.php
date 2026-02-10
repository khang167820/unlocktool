<?php
// ===== Article Template Components =====
// Include this file in all articles for shared navbar, progress bar, and footer

function render_article_head_assets() {
    echo '
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/article.css">';
}

function render_article_navbar() {
    echo '
    <div class="reading-progress"></div>
    <nav class="article-navbar">
        <div class="nav-inner">
            <a href="/" class="nav-logo">
                <span>🔓 UnlockTool.us</span>
            </a>
            <div class="nav-links">
                <a href="/">Trang chủ</a>
                <a href="/articles/">Bài viết</a>
                <a href="/huong-dan.php">Hướng dẫn</a>
                <a href="https://thuetaikhoan.net" target="_blank" rel="nofollow">Thuetaikhoan.net</a>
                <a href="/" class="nav-cta">Thuê Ngay</a>
            </div>
            <button class="nav-hamburger" aria-label="Menu"><i class="fas fa-bars"></i></button>
        </div>
    </nav>';
}

function render_article_footer() {
    echo '
    <footer class="article-footer">
        <div class="container">
            <p class="copyright">© 2024-2026 UnlockTool.us — Dịch vụ cho thuê UnlockTool uy tín #1 Việt Nam</p>
            <p class="contact">Liên hệ: Zalo 0777333763 | Email: support@unlocktool.us</p>
        </div>
    </footer>
    <button class="back-to-top" aria-label="Lên đầu trang"><i class="fas fa-arrow-up"></i></button>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/article.js"></script>';
}
?>
