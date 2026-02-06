<?php
$page_title = "X�a Mi Account Xiaomi b?ng UnlockTool 2026 - Hu?ng d?n chi ti?t";
$page_description = "Hu?ng d?n x�a Mi Account Xiaomi b?ng UnlockTool 2026. Bypass t�i kho?n Xiaomi tr�n Redmi, POCO, Mi kh�ng c?n m?t kh?u, EDL Mode v� Fastboot.";
$page_keywords = "x�a mi account, bypass mi account, unlocktool xiaomi, x�a t�i kho?n xiaomi, mi account remove";
$canonical_url = "https://www.unlocktool.us/articles/4-xoa-mi-account-xiaomi.php";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <!-- Performance: Preconnect -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="dns-prefetch" href="https://www.googletagmanager.com">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0066cc">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $page_description; ?>">
    <meta name="keywords" content="<?php echo $page_keywords; ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo $canonical_url; ?>">\n    <link rel="alternate" hreflang="vi" href="<?php echo $canonical_url; ?>">
    <link rel="alternate" hreflang="x-default" href="<?php echo $canonical_url; ?>">">
    
    <meta property="og:type" content="article">
    <meta property="og:title" content="<?php echo $page_title; ?>">
    <meta property="og:description" content="<?php echo $page_description; ?>
    <meta property="og:image" content="https://www.unlocktool.us/images/unlocktool-guide.jpg">">
    
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "C�ch x�a Mi Account Xiaomi b?ng UnlockTool",
        "description": "Hu?ng d?n bypass t�i kho?n Xiaomi (Mi Account) kh�ng c?n m?t kh?u",
        "totalTime": "PT15M",
        "step": [
            {"@type": "HowToStep", "name": "T?i UnlockTool v� Driver", "text": "T?i UnlockTool v� Qualcomm/MTK Driver"},
            {"@type": "HowToStep", "name": "�ua m�y v�o EDL Mode", "text": "Gi? Volume Up + Down khi c?m c�p d? v�o EDL"},
            {"@type": "HowToStep", "name": "Ch?n Xiaomi > Mi Account Remove", "text": "Trong UnlockTool ch?n tab Xiaomi v� Mi Account Remove"},
            {"@type": "HowToStep", "name": "Ho�n t?t", "text": "Ch? qu� tr�nh ho�n t?t, m�y s? t? kh?i d?ng l?i"}
        ]
    }
    </script>
    
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {"@type": "Question", "name": "Mi Account l� g�?", "acceptedAnswer": {"@type": "Answer", "text": "Mi Account l� t�i kho?n Xiaomi d�ng d? d?ng b? d? li?u, Find Device v� b?o v? di?n tho?i. Khi b?t, n� s? kh�a m�y n?u ai d� reset m� kh�ng c� m?t kh?u."}},
            {"@type": "Question", "name": "X�a Mi Account c� m?t MIUI kh�ng?", "acceptedAnswer": {"@type": "Answer", "text": "Kh�ng, vi?c x�a Mi Account ch? g? b? kh�a t�i kho?n, h? di?u h�nh MIUI v?n gi? nguy�n."}},
            {"@type": "Question", "name": "EDL Mode l� g�?", "acceptedAnswer": {"@type": "Answer", "text": "EDL (Emergency Download Mode) l� ch? d? d?c bi?t tr�n di?n tho?i Qualcomm cho ph�p can thi?p s�u v�o h? th?ng, flash ROM v� x�a kh�a."}}
        ]
    }
    </script>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
    <style>
        body { font-family: 'Segoe UI', sans-serif; line-height: 1.8; background: #f8f9fa; }
        .article-header { background: linear-gradient(135deg, #ff6900 0%, #ffc107 100%); color: white; padding: 60px 0; }
        .article-header h1 { font-size: 2.2rem; font-weight: 700; }
        .article-content { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); margin: -30px auto 30px; max-width: 900px; }
        .article-content h2 { color: #ff6900; font-size: 1.6rem; font-weight: 700; margin-top: 35px; border-bottom: 3px solid #ff6900; padding-bottom: 10px; }
        .article-content h3 { color: #2c3e50; font-size: 1.25rem; font-weight: 600; margin-top: 25px; }
        .step-card { background: linear-gradient(135deg, #fff9e6 0%, #fff3cd 100%); border-left: 4px solid #ff6900; padding: 20px; margin: 15px 0; border-radius: 0 8px 8px 0; }
        .step-number { display: inline-block; width: 30px; height: 30px; background: #ff6900; color: white; border-radius: 50%; text-align: center; line-height: 30px; font-weight: 700; margin-right: 10px; }
        .info-box { background: #e3f2fd; border-left: 5px solid #2196f3; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .warning-box { background: #ffebee; border-left: 5px solid #f44336; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .success-box { background: #e8f5e9; border-left: 5px solid #4caf50; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .download-btn { display: inline-block; background: linear-gradient(135deg, #ff6900 0%, #ffc107 100%); color: white; padding: 15px 35px; border-radius: 50px; font-weight: 700; text-decoration: none; margin: 10px 5px; }
        .download-btn:hover { transform: translateY(-3px); color: white; text-decoration: none; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; border: 1px solid #ddd; }
        th { background: #ff6900; color: white; }
        .model-badge { display: inline-block; background: #fff3e0; color: #e65100; padding: 5px 12px; border-radius: 15px; margin: 3px; font-size: 0.85rem; border: 1px solid #ffcc80; }
        .faq-item { background: #f8f9fa; border-radius: 8px; margin: 10px 0; }
        .faq-question { padding: 15px 20px; font-weight: 600; cursor: pointer; background: white; border: 1px solid #e0e0e0; border-radius: 8px; }
        .faq-answer { padding: 15px 20px; display: none; }
    </style>

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "UnlockTool.us",
        "url": "https://www.unlocktool.us",
        "logo": "https://www.unlocktool.us/images/logo.png",
        "sameAs": [
            "https://www.facebook.com/unlocktool.us",
            "https://thuetaikhoan.net"
        ],
        "contactPoint": {
            "@type": "ContactPoint",
            "contactType": "customer service",
            "availableLanguage": ["Vietnamese", "English"]
        }
    }
    </script>

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebPage",
        "speakable": {
            "@type": "SpeakableSpecification",
            "cssSelector": ["h1", ".hero-section p.lead", ".toc", ".info-box", ".tip-box"]
        }
    }
    </script>

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "UnlockTool.us",
        "url": "https://www.unlocktool.us",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "https://www.unlocktool.us/search?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Product",
        "name": "D?ch v? m? kh�a di?n tho?i",
        "brand": {"@type": "Brand", "name": "UnlockTool.us"},
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "4.8",
            "bestRating": "5",
            "worstRating": "1",
            "ratingCount": "120"
        }
    }
    </script>
</head>
<body>

<header class="article-header">
    <div class="container text-center">
        <h1><i class="fas fa-mobile-alt"></i> X�a Mi Account Xiaomi b?ng UnlockTool 2026</h1>
        <p>Bypass t�i kho?n Xiaomi tr�n Redmi, POCO, Mi kh�ng c?n m?t kh?u</p>
    </div>
</header>

<div class="container">
    <article class="article-content">
        
        <div class="info-box">
            <h4><i class="fas fa-info-circle"></i> Mi Account l� g�?</h4>
            <p class="mb-0"><strong>Mi Account</strong> (T�i kho?n Xiaomi) l� h? th?ng t�i kho?n c?a Xiaomi, tuong t? nhu Samsung Account hay Apple ID. Khi b?t t�nh nang "Find Device", di?n tho?i s? b? kh�a Mi Account sau khi reset n?u kh�ng c� m?t kh?u g?c.</p>
        </div>
        
        <h2>1. C�c d�ng Xiaomi du?c h? tr?</h2>
        
        <h3>Xiaomi Mi Series</h3>
        <p>
            <span class="model-badge">Mi 8/8 Lite/8 SE</span>
            <span class="model-badge">Mi 9/9T/9 SE</span>
            <span class="model-badge">Mi 10/10T/10 Lite</span>
            <span class="model-badge">Mi 11/11T/11 Lite</span>
            <span class="model-badge">Mi 12/12T/12 Lite</span>
            <span class="model-badge">Mi 13/13T/13 Lite</span>
            <span class="model-badge">Mi 14</span>
        </p>
        
        <h3>Redmi Series</h3>
        <p>
            <span class="model-badge">Redmi Note 7-13</span>
            <span class="model-badge">Redmi 9-13</span>
            <span class="model-badge">Redmi K20-K70</span>
            <span class="model-badge">Redmi A1/A2</span>
        </p>
        
        <h3>POCO Series</h3>
        <p>
            <span class="model-badge">POCO F1-F6</span>
            <span class="model-badge">POCO X3-X6</span>
            <span class="model-badge">POCO M3-M6</span>
            <span class="model-badge">POCO C Series</span>
        </p>
        
        <h2>2. Phuong ph�p x�a Mi Account</h2>
        
        <h3>Phuong ph�p 1: EDL Mode (Chip Qualcomm)</h3>
        
        <p>��y l� phuong ph�p ph? bi?n nh?t cho c�c m�y Xiaomi d�ng chip Qualcomm:</p>
        
        <div class="step-card">
            <span class="step-number">1</span>
            <strong>C�i d?t Qualcomm Driver</strong>
            <p class="mb-0 mt-2">T?i v� c�i Qualcomm Driver t? <a href="https://file.unlocktool.net/page/all-driver" target="_blank">Full Driver UnlockTool</a></p>
        </div>
        
        <div class="step-card">
            <span class="step-number">2</span>
            <strong>�ua m�y v�o EDL Mode</strong>
            <p class="mb-0 mt-2">
                <strong>C�ch 1:</strong> T?t m�y ? Gi? Volume Up + Volume Down ? C?m c�p USB<br>
                <strong>C�ch 2:</strong> V�o Fastboot ? G� l?nh: <code>fastboot oem edl</code><br>
                <strong>C�ch 3:</strong> S? d?ng test point (c?n m? m�y)
            </p>
        </div>
        
        <div class="step-card">
            <span class="step-number">3</span>
            <strong>M? UnlockTool v� ch?n Xiaomi</strong>
            <p class="mb-0 mt-2">Ch?y UnlockTool ? �ang nh?p ? Click tab <strong>"Xiaomi"</strong> ? Ch?n <strong>"Qualcomm"</strong></p>
        </div>
        
        <div class="step-card">
            <span class="step-number">4</span>
            <strong>Ch?n Mi Account Remove</strong>
            <p class="mb-0 mt-2">Click <strong>"Mi Account Remove"</strong> ho?c <strong>"Remove Mi Cloud"</strong>. Ch? qu� tr�nh t? d?ng ch?y.</p>
        </div>
        
        <div class="step-card">
            <span class="step-number">5</span>
            <strong>Ho�n t?t</strong>
            <p class="mb-0 mt-2">Sau 3-10 ph�t, m�y s? t? kh?i d?ng l?i. Mi Account d� du?c x�a th�nh c�ng!</p>
        </div>
        
        <h3>Phuong ph�p 2: MTK Mode (Chip MediaTek)</h3>
        
        <p>Cho c�c m�y Xiaomi/Redmi d�ng chip MediaTek (Helio):</p>
        
        <ol>
            <li>C�i d?t MTK Driver</li>
            <li>T?t m�y ho�n to�n</li>
            <li>Trong UnlockTool, ch?n Xiaomi ? MTK</li>
            <li>Gi? Volume Up/Down v� c?m c�p USB</li>
            <li>Ch?n Mi Account Remove</li>
            <li>Ch? ho�n t?t</li>
        </ol>
        
        <h3>Phuong ph�p 3: Fastboot (�� Unlock Bootloader)</h3>
        
        <p>N?u m�y d� unlock bootloader t? tru?c:</p>
        
        <ol>
            <li>V�o Fastboot Mode: T?t m�y ? Gi? Volume Down + Power</li>
            <li>K?t n?i v?i m�y t�nh</li>
            <li>Trong UnlockTool ch?n Xiaomi ? Fastboot</li>
            <li>Ch?n Mi Account Remove</li>
            <li>Ch? ho�n t?t</li>
        </ol>
        
        <div class="warning-box">
            <h4><i class="fas fa-exclamation-triangle"></i> Luu � quan tr?ng</h4>
            <ul class="mb-0">
                <li>Phuong ph�p EDL c� th? kh�ng ho?t d?ng tr�n m?t s? model m?i do Xiaomi d� kh�a EDL</li>
                <li>M?t s? m�y c?n Auth (m� x�c th?c) d? v�o EDL - UnlockTool c� th? h? tr?</li>
                <li>Backup d? li?u quan tr?ng tru?c khi th?c hi?n</li>
            </ul>
        </div>
        
        <h2>3. B?ng phuong ph�p theo Model</h2>
        
        <table>
            <tr>
                <th>Model</th>
                <th>Chip</th>
                <th>Phuong ph�p</th>
                <th>C?n Auth?</th>
            </tr>
            <tr>
                <td>Redmi Note 7-9</td>
                <td>Qualcomm</td>
                <td>EDL</td>
                <td>Kh�ng</td>
            </tr>
            <tr>
                <td>Redmi Note 10-11</td>
                <td>Qualcomm</td>
                <td>EDL</td>
                <td>C� th?</td>
            </tr>
            <tr>
                <td>Redmi Note 12-13</td>
                <td>MediaTek/Qualcomm</td>
                <td>MTK/EDL</td>
                <td>C� th?</td>
            </tr>
            <tr>
                <td>POCO F1-F3</td>
                <td>Qualcomm</td>
                <td>EDL</td>
                <td>Kh�ng</td>
            </tr>
            <tr>
                <td>POCO X3-X5</td>
                <td>Qualcomm</td>
                <td>EDL</td>
                <td>C� th?</td>
            </tr>
            <tr>
                <td>Redmi 9A/9C/10A</td>
                <td>MediaTek</td>
                <td>MTK</td>
                <td>Kh�ng</td>
            </tr>
        </table>
        
        <h2>4. X? l� l?i thu?ng g?p</h2>
        
        <h3>4.1. Kh�ng v�o du?c EDL Mode</h3>
        <ul>
            <li>Th? gi? n�t l�u hon (10-15 gi�y)</li>
            <li>S? d?ng test point (c?n kinh nghi?m)</li>
            <li>M?t s? model m?i d� b? Xiaomi kh�a EDL</li>
        </ul>
        
        <h3>4.2. L?i "Need Auth"</h3>
        <ul>
            <li>Model n�y c?n m� Auth t? Xiaomi server</li>
            <li>UnlockTool c� th? h? tr? Auth cho m?t s? model</li>
            <li>Li�n h? h? tr? d? du?c tu v?n</li>
        </ul>
        
        <h3>4.3. M�y treo sau khi x�a Mi Account</h3>
        <ul>
            <li>V�o Recovery Mode v� Wipe Data</li>
            <li>Flash l?i ROM MIUI m?i</li>
        </ul>
        
        <h2>5. C�u h?i thu?ng g?p (FAQ)</h2>
        
        <div class="faq-item">
            <div class="faq-question">X�a Mi Account c� m?t d? li?u kh�ng?</div>
            <div class="faq-answer">Phuong ph�p EDL thu?ng kh�ng m?t d? li?u. Tuy nhi�n, n?u m�y dang ? tr?ng th�i kh�a, d? li?u c� th? d� b? m� h�a v� kh�ng th? truy c?p.</div>
        </div>
        
        <div class="faq-item">
            <div class="faq-question">X�a Mi Account kh�c g� x�a FRP?</div>
            <div class="faq-answer">Mi Account l� t�i kho?n c?a Xiaomi, c�n FRP (Google Account) l� c?a Google. Tr�n Xiaomi c� th? c� c? 2 lo?i kh�a v� c?n x�a c? 2.</div>
        </div>
        
        <div class="faq-item">
            <div class="faq-question">Sau khi x�a Mi Account c� dang nh?p l?i du?c kh�ng?</div>
            <div class="faq-answer">C�, b?n c� th? dang nh?p Mi Account m?i ho?c b? qua bu?c dang nh?p khi thi?t l?p m�y.</div>
        </div>
        
        <div class="faq-item">
            <div class="faq-question">UnlockTool c� x�a du?c Mi Account tr�n MIUI 14/15 kh�ng?</div>
            <div class="faq-answer">C�, UnlockTool li�n t?c du?c c?p nh?t d? h? tr? c�c phi�n b?n MIUI m?i. Tuy nhi�n m?t s? model m?i c� th? c?n Auth.</div>
        </div>
        
        <h2>6. Link t?i v� t�i nguy�n</h2>
        
        <div class="success-box">
            <h4><i class="fas fa-download"></i> Link t?i</h4>
            <ul class="mb-0">
                <li><strong>UnlockTool:</strong> <a href="https://file.unlocktool.net/page/index.php" target="_blank">file.unlocktool.net/page/index.php</a></li>
                <li><strong>Full Driver:</strong> <a href="https://file.unlocktool.net/page/all-driver" target="_blank">file.unlocktool.net/page/all-driver</a></li>
                <li><strong>Tool Android:</strong> <a href="https://file.unlocktool.net/page/tool---android---pc/tool-android---pc" target="_blank">file.unlocktool.net/page/tool-android-pc</a></li>
            </ul>
        </div>
        
        <h2>7. K?t lu?n</h2>
        
        <p>X�a Mi Account Xiaomi b?ng <strong>UnlockTool</strong> l� gi?i ph�p hi?u qu? cho nh?ng ai qu�n m?t kh?u t�i kho?n Xiaomi. Phuong ph�p EDL cho m�y Qualcomm v� MTK cho m�y MediaTek d?u c� t? l? th�nh c�ng cao.</p>
        
        <p>N?u b?n c?n t�i kho?n UnlockTool v?i gi� r?, h�y thu� t?i <a href="https://www.unlocktool.us/">UnlockTool.us</a> - ch? t? 8.000d/6 ti?ng!</p>
        
        <div class="text-center my-4">
            <a href="https://file.unlocktool.net/page/index.php" target="_blank" class="download-btn">
                <i class="fas fa-download"></i> T?i UnlockTool
            </a>
            <a href="https://www.unlocktool.us/" class="download-btn" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <i class="fas fa-key"></i> Thu� T�i Kho?n
            </a>
        </div>
        
    </article>
</div>


<!-- Social Share & Related Posts -->
<div class="container mb-5">
    <div class="content-wrapper" style="max-width:900px;margin:0 auto;background:#fff;border-radius:20px;box-shadow:0 10px 40px rgba(0,0,0,0.1);padding:40px;">
        
        <!-- Social Share -->
        <div class="social-share mb-4">
            <h5 class="mb-3"><i class="fas fa-share-alt me-2"></i>Chia s? b�i vi?t:</h5>
            <div class="d-flex flex-wrap gap-2">
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($canonical_url); ?>" target="_blank" class="btn btn-primary" style="background:#1877f2;border:none;"><i class="fab fa-facebook-f me-1"></i> Facebook</a>
                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($canonical_url); ?>&text=<?php echo urlencode($page_title); ?>" target="_blank" class="btn" style="background:#1da1f2;border:none;color:#fff;"><i class="fab fa-twitter me-1"></i> Twitter</a>
                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode($canonical_url); ?>&title=<?php echo urlencode($page_title); ?>" target="_blank" class="btn" style="background:#0077b5;border:none;color:#fff;"><i class="fab fa-linkedin-in me-1"></i> LinkedIn</a>
                <a href="https://t.me/share/url?url=<?php echo urlencode($canonical_url); ?>&text=<?php echo urlencode($page_title); ?>" target="_blank" class="btn" style="background:#0088cc;border:none;color:#fff;"><i class="fab fa-telegram-plane me-1"></i> Telegram</a>
                <a href="mailto:?subject=<?php echo urlencode($page_title); ?>&body=<?php echo urlencode($canonical_url); ?>" class="btn btn-secondary"><i class="fas fa-envelope me-1"></i> Email</a>
            </div>
        </div>
        
        <hr class="my-4">
        
        <!-- Related Posts -->
        <div class="related-posts">
            <h5 class="mb-3"><i class="fas fa-newspaper me-2"></i>B�i vi?t li�n quan:</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title"><a href="51-samsung-frp.php" class="text-decoration-none">Bypass FRP Samsung</a></h6>
                            <small class="text-muted">Hu?ng d?n chi ti?t</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title"><a href="73-iphone-ipad-bypass.php" class="text-decoration-none">Bypass iCloud iPhone</a></h6>
                            <small class="text-muted">T? iPhone 5s - X</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title"><a href="131-tai-unlocktool.php" class="text-decoration-none">T?i UnlockTool</a></h6>
                            <small class="text-muted">Download m?i nh?t</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Author Box -->
        <hr class="my-4">
        <div class="author-box d-flex gap-3 align-items-center p-3" style="background:#f8f9fa;border-radius:15px;">
            <div class="author-avatar" style="width:80px;height:80px;background:linear-gradient(135deg,#667eea,#764ba2);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:2rem;font-weight:bold;">UT</div>
            <div>
                <h6 class="mb-1">UnlockTool.us Team</h6>
                <p class="mb-0 text-muted" style="font-size:0.9rem;">�?i ngu k? thu?t vi�n chuy�n nghi?p v?i 10+ nam kinh nghi?m trong linh v?c m? kh�a di?n tho?i. H? tr? 24/7 t?i <a href="https://thuetaikhoan.net">Thuetaikhoan.net</a></p>
            </div>
        </div>
        
    </div>
</div>
<footer class="bg-dark text-white py-4 mt-5">
    <div class="container text-center">
        <p class="mb-0">� 2026 UnlockTool.us - D?ch v? cho thu� t�i kho?n UnlockTool uy t�n</p>
    </div>
</footer>

<script>
document.querySelectorAll('.faq-question').forEach(q => {
    q.addEventListener('click', () => {
        const a = q.nextElementSibling;
        a.style.display = a.style.display === 'block' ? 'none' : 'block';
    });
});
</script>
</body>
</html>
