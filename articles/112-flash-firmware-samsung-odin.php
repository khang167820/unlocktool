<?php
$page_title = "Flash Firmware Samsung Bằng Odin 2026 - Hướng Dẫn Chi Tiết | UnlockTool";
$page_description = "Hướng dẫn flash firmware Samsung bằng Odin mới nhất 2026. Cứu máy brick, downgrade, nâng cấp ROM. Tải Odin và firmware Samsung - Thuê UnlockTool 8.000đ/6h";
$page_keywords = "flash samsung odin, odin samsung, flash firmware samsung, samsung firmware download, odin download, flash rom samsung";
$canonical_url = "https://www.unlocktool.us/articles/112-flash-firmware-samsung-odin.php";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <!-- Performance: Preconnect -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="dns-prefetch" href="https://www.googletagmanager.com">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0066cc">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $page_description; ?>">
    <meta name="keywords" content="<?php echo $page_keywords; ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo $canonical_url; ?>
    <link rel="alternate" hreflang="vi" href="<?php echo $canonical_url; ?>">
    <link rel="alternate" hreflang="x-default" href="<?php echo $canonical_url; ?>">
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?php echo $canonical_url; ?>">
    <meta property="og:title" content="<?php echo $page_title; ?>">
    <meta property="og:description" content="<?php echo $page_description; ?>">
    <meta property="og:image" content="https://www.unlocktool.us/images/og-unlocktool.jpg">
    <meta property="og:site_name" content="UnlockTool.us">
    <meta property="og:locale" content="vi_VN">
    <meta property="article:author" content="UnlockTool.us">
    <meta property="article:publisher" content="https://www.facebook.com/unlocktool.us">
    <meta property="article:modified_time" content="2026-02-06T00:00:00+07:00">
    <meta property="article:section" content="Hướng dẫn kỹ thuật">
    <meta property="article:tag" content="FRP Bypass">
    <meta property="article:tag" content="UnlockTool">
    <meta property="article:tag" content="Mở khóa điện thoại">
    
    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo $canonical_url; ?>">
    <meta name="twitter:title" content="<?php echo $page_title; ?>">
    <meta name="twitter:description" content="<?php echo $page_description; ?>">
    <meta name="twitter:image" content="https://www.unlocktool.us/images/og-unlocktool.jpg">
    
    
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "Trang chủ", "item": "https://www.unlocktool.us/"},
            {"@type": "ListItem", "position": 2, "name": "Bài viết", "item": "https://www.unlocktool.us/articles/"},
            {"@type": "ListItem", "position": 3, "name": "<?php echo $page_title; ?>", "item": "<?php echo $canonical_url; ?>"}
        ]
    }
    </script>
    <script type="application/ld+json">
    {"@context":"https://schema.org","@type":"HowTo","name":"Flash Firmware Samsung Bằng Odin","description":"Hướng dẫn flash firmware Samsung sử dụng Odin3","step":[
        {"@type":"HowToStep","name":"Tải Odin và Firmware","text":"Tải Odin3 mới nhất và firmware phù hợp với model Samsung"},
        {"@type":"HowToStep","name":"Vào Download Mode","text":"Tắt máy, nhấn giữ Vol Down + Power khi cắm cáp"},
        {"@type":"HowToStep","name":"Mở Odin","text":"Chạy Odin3.exe, kiểm tra cổng COM nhận diện máy"},
        {"@type":"HowToStep","name":"Load firmware","text":"Bấm BL, AP, CP, CSC để load các file firmware tương ứng"},
        {"@type":"HowToStep","name":"Flash","text":"Click Start và chờ PASS"}
    ]}
    </script>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body{font-family:'Segoe UI',sans-serif;line-height:1.8;color:#333}
        .hero-section{background:linear-gradient(135deg,#1428a0 0%,#0d1a6b 100%);color:#fff;padding:80px 0}
        .hero-section h1{font-size:2.5rem;font-weight:700}
        .toc-box{background:#f8f9fa;border-radius:15px;padding:25px;margin:30px 0;border-left:5px solid #1428a0}
        .info-box{background:#e3f2fd;border-left:5px solid #2196f3;padding:20px;margin:25px 0;border-radius:0 10px 10px 0}
        .warning-box{background:#fff3e0;border-left:5px solid #ff9800;padding:20px;margin:25px 0;border-radius:0 10px 10px 0}
        .success-box{background:#e8f5e9;border-left:5px solid #4caf50;padding:20px;margin:25px 0;border-radius:0 10px 10px 0}
        .danger-box{background:#ffebee;border-left:5px solid #f44336;padding:20px;margin:25px 0;border-radius:0 10px 10px 0}
        .feature-card{background:white;border-radius:15px;padding:25px;margin:15px 0;box-shadow:0 3px 15px rgba(0,0,0,0.1)}
        .price-highlight{background:linear-gradient(135deg,#1428a0 0%,#0d1a6b 100%);color:#fff;padding:40px;border-radius:20px;text-align:center;margin:40px 0}
        .price-highlight .price{font-size:3.5rem;font-weight:700}
        .table-custom{border-radius:15px;overflow:hidden;box-shadow:0 3px 15px rgba(0,0,0,0.1)}
        .table-custom thead{background:linear-gradient(135deg,#1428a0 0%,#0d1a6b 100%);color:#fff}
        h2{color:#1428a0;margin-top:40px;padding-bottom:10px;border-bottom:2px solid #1428a0}
        h3{color:#0d1a6b;margin-top:30px}
        .step-box{background:#fff;border-radius:15px;padding:25px;margin:20px 0;box-shadow:0 3px 15px rgba(0,0,0,0.1);border-left:5px solid #1428a0;position:relative;padding-left:70px}
        .step-number{position:absolute;left:15px;top:50%;transform:translateY(-50%);width:40px;height:40px;background:#1428a0;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:1.2rem}
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
</head>
<body>

<nav aria-label="Breadcrumb" class="py-2 bg-light">
    <div class="container">
        <ol class="breadcrumb mb-0" style="font-size:0.9rem;">
            <li class="breadcrumb-item"><a href="https://www.unlocktool.us/"><i class="fas fa-home"></i> Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="https://www.unlocktool.us/articles/">Bài viết</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo $page_title; ?></li>
        </ol>
    </div>
</nav>
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1><i class="fas fa-download me-3"></i><?php echo $page_title; ?></h1>
                <p class="lead mb-4">Hướng dẫn chi tiết flash firmware Samsung bằng Odin3. Cứu máy brick, downgrade, nâng cấp ROM an toàn!</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="https://www.unlocktool.us/" target="_blank" class="btn btn-light btn-lg"><i class="fas fa-shopping-cart me-2"></i>Thuê UnlockTool 8K/6H</a>
                    <a href="https://thuetaikhoan.net" target="_blank" class="btn btn-outline-light btn-lg"><i class="fas fa-headset me-2"></i>Hỗ Trợ Kỹ Thuật</a>
                </div>
            </div>
            <div class="col-lg-4 text-center mt-4 mt-lg-0">
                <i class="fas fa-mobile-alt fa-6x"></i>
                <p class="mt-2 fw-bold">Odin Flash Tool</p>
            </div>
        </div>
    </div>
</section>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8">
            
            <div class="toc-box">
                <h4><i class="fas fa-list me-2"></i>Mục Lục</h4>
                <ul class="list-unstyled">
                    <li><a href="#odin-la-gi">1. Odin là gì?</a></li>
                    <li><a href="#khi-nao-flash">2. Khi nào cần flash firmware?</a></li>
                    <li><a href="#chuan-bi">3. Chuẩn bị</a></li>
                    <li><a href="#giao-dien-odin">4. Giao diện Odin</a></li>
                    <li><a href="#huong-dan">5. Hướng dẫn flash chi tiết</a></li>
                    <li><a href="#loi-thuong-gap">6. Lỗi thường gặp</a></li>
                    <li><a href="#faq">7. FAQ</a></li>
                </ul>
            </div>

            <section id="odin-la-gi">
                <h2><i class="fas fa-info-circle me-2"></i>1. Odin Là Gì?</h2>
                
                <p><strong>Odin</strong> là công cụ flash firmware chính thức của Samsung dành cho kỹ thuật viên. Tên "Odin" lấy từ vị thần tối cao trong thần thoại Bắc Âu.</p>

                <h3>Tính năng của Odin:</h3>
                <ul>
                    <li><strong>Flash firmware:</strong> Cài lại hệ điều hành Samsung</li>
                    <li><strong>Cứu máy brick:</strong> Phục hồi máy bị lỗi phần mềm</li>
                    <li><strong>Downgrade:</strong> Hạ cấp firmware xuống phiên bản thấp hơn</li>
                    <li><strong>Upgrade:</strong> Nâng cấp firmware thủ công</li>
                    <li><strong>Flash Recovery/Kernel:</strong> Cài custom recovery, kernel</li>
                </ul>

                <h3>Các phiên bản Odin:</h3>
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Phiên bản</th>
                            <th>Mô tả</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Odin3 v3.14.4</td>
                            <td>Phiên bản phổ biến, ổn định</td>
                        </tr>
                        <tr>
                            <td>Odin3 v3.15.1</td>
                            <td>Phiên bản mới hơn, hỗ trợ model mới</td>
                        </tr>
                        <tr>
                            <td>Odin4</td>
                            <td>Phiên bản mới nhất (chưa phổ biến)</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section id="khi-nao-flash">
                <h2><i class="fas fa-question-circle me-2"></i>2. Khi Nào Cần Flash Firmware?</h2>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="feature-card h-100">
                            <h5><i class="fas fa-bug text-danger me-2"></i>Máy bị brick</h5>
                            <p class="small mb-0">Máy không khởi động được, treo logo, bootloop</p>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="feature-card h-100">
                            <h5><i class="fas fa-sync text-primary me-2"></i>Muốn cài ROM gốc</h5>
                            <p class="small mb-0">Trở về firmware chính hãng sau khi dùng custom ROM</p>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="feature-card h-100">
                            <h5><i class="fas fa-arrow-down text-warning me-2"></i>Downgrade</h5>
                            <p class="small mb-0">Hạ cấp xuống phiên bản firmware cũ hơn</p>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="feature-card h-100">
                            <h5><i class="fas fa-unlock text-success me-2"></i>Bypass/Unlock</h5>
                            <p class="small mb-0">Flash firmware để hỗ trợ bypass FRP, unlock...</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="chuan-bi">
                <h2><i class="fas fa-tools me-2"></i>3. Chuẩn Bị</h2>
                
                <h3>Phần mềm cần tải:</h3>
                <ul>
                    <li><strong>Odin3:</strong> <a href="https://file.unlocktool.net/page/tool---android---pc/tool-android---pc" target="_blank">Tải tại đây</a></li>
                    <li><strong>Samsung USB Driver:</strong> <a href="https://file.unlocktool.net/page/all-driver" target="_blank">Tải tại đây</a></li>
                    <li><strong>Firmware Samsung:</strong> Tải từ SamFirm, Frija, hoặc các nguồn uy tín</li>
                </ul>

                <h3>Tải firmware Samsung:</h3>
                <p>Bạn cần tải đúng firmware cho model và khu vực (CSC) của máy. Các nguồn tải phổ biến:</p>
                <ul>
                    <li><strong>SamFirm:</strong> Tool tải firmware từ server Samsung</li>
                    <li><strong>Frija:</strong> Tool tương tự SamFirm</li>
                    <li><strong>SamMobile:</strong> Website tải firmware (cần đăng ký)</li>
                </ul>

                <div class="warning-box">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Quan trọng!</h5>
                    <p class="mb-0">Tải đúng firmware cho model máy. Xem model trong Settings → About Phone → Model Number. Ví dụ: SM-A125F, SM-G991B...</p>
                </div>
            </section>

            <section id="giao-dien-odin">
                <h2><i class="fas fa-desktop me-2"></i>4. Giao Diện Odin</h2>
                
                <p>Odin có giao diện đơn giản với các thành phần chính:</p>

                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Nút</th>
                            <th>Mô tả</th>
                            <th>File tương ứng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>BL</strong></td>
                            <td>Bootloader</td>
                            <td>BL_xxxxx.tar.md5</td>
                        </tr>
                        <tr>
                            <td><strong>AP</strong></td>
                            <td>Android Partition (system)</td>
                            <td>AP_xxxxx.tar.md5</td>
                        </tr>
                        <tr>
                            <td><strong>CP</strong></td>
                            <td>Modem/Baseband</td>
                            <td>CP_xxxxx.tar.md5</td>
                        </tr>
                        <tr>
                            <td><strong>CSC</strong></td>
                            <td>Consumer Software Customization</td>
                            <td>CSC_xxxxx.tar.md5 hoặc HOME_CSC_xxxxx.tar.md5</td>
                        </tr>
                    </tbody>
                </table>

                <div class="info-box">
                    <h5><i class="fas fa-info-circle me-2"></i>CSC vs HOME_CSC</h5>
                    <ul class="mb-0">
                        <li><strong>CSC_xxx:</strong> Factory reset, xóa toàn bộ dữ liệu</li>
                        <li><strong>HOME_CSC_xxx:</strong> Giữ lại dữ liệu (nếu có thể)</li>
                    </ul>
                </div>
            </section>

            <section id="huong-dan">
                <h2><i class="fas fa-play-circle me-2"></i>5. Hướng Dẫn Flash Chi Tiết</h2>
                
                <div class="step-box">
                    <span class="step-number">1</span>
                    <h5>Giải nén firmware</h5>
                    <p class="mb-0">Giải nén file firmware đã tải. Bạn sẽ thấy các file: BL, AP, CP, CSC (hoặc HOME_CSC)</p>
                </div>

                <div class="step-box">
                    <span class="step-number">2</span>
                    <h5>Cài driver Samsung</h5>
                    <p class="mb-0">Cài Samsung USB Driver và khởi động lại máy tính</p>
                </div>

                <div class="step-box">
                    <span class="step-number">3</span>
                    <h5>Đưa Samsung vào Download Mode</h5>
                    <p class="mb-0">Tắt máy → Nhấn giữ Vol Down + Power khi cắm cáp USB → Nhấn Vol Up để xác nhận</p>
                </div>

                <div class="step-box">
                    <span class="step-number">4</span>
                    <h5>Mở Odin</h5>
                    <p class="mb-0">Chạy Odin3.exe với quyền Administrator. Kiểm tra ô "ID:COM" có hiện số port (máy được nhận)</p>
                </div>

                <div class="step-box">
                    <span class="step-number">5</span>
                    <h5>Load firmware vào Odin</h5>
                    <p class="mb-0">Click BL → chọn file BL, Click AP → chọn file AP, Click CP → chọn file CP, Click CSC → chọn file HOME_CSC (hoặc CSC)</p>
                </div>

                <div class="step-box">
                    <span class="step-number">6</span>
                    <h5>Kiểm tra Options</h5>
                    <p class="mb-0">Đảm bảo chỉ tick "Auto Reboot" và "F. Reset Time". KHÔNG tick "Re-Partition" trừ khi biết mình đang làm gì</p>
                </div>

                <div class="step-box">
                    <span class="step-number">7</span>
                    <h5>Click Start</h5>
                    <p class="mb-0">Click Start và chờ. Quá trình mất 5-15 phút tùy firmware. Khi hoàn tất sẽ hiện "PASS!" màu xanh</p>
                </div>

                <div class="success-box">
                    <h5><i class="fas fa-check-circle me-2"></i>Hoàn tất!</h5>
                    <p class="mb-0">Máy sẽ tự khởi động lại. Lần đầu khởi động có thể mất 5-10 phút, đừng hoảng!</p>
                </div>
            </section>

            <section id="loi-thuong-gap">
                <h2><i class="fas fa-bug me-2"></i>6. Lỗi Thường Gặp</h2>
                
                <div class="accordion" id="errorAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#err1">
                                FAIL! (hiện đỏ)
                            </button>
                        </h2>
                        <div id="err1" class="accordion-collapse collapse show">
                            <div class="accordion-body">
                                <ul>
                                    <li>Thử đổi cổng USB (ưu tiên USB 2.0)</li>
                                    <li>Thử cáp USB khác</li>
                                    <li>Kiểm tra firmware đúng model chưa</li>
                                    <li>Thử phiên bản Odin khác</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#err2">
                                Stuck ở "SetupConnection"
                            </button>
                        </h2>
                        <div id="err2" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <ul>
                                    <li>Cài lại driver Samsung</li>
                                    <li>Đổi cổng USB</li>
                                    <li>Đổi cáp USB</li>
                                    <li>Thử vào Download Mode lại</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#err3">
                                "Complete(Write) operation failed"
                            </button>
                        </h2>
                        <div id="err3" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <ul>
                                    <li>File firmware bị lỗi → tải lại</li>
                                    <li>Sai model → tải đúng firmware</li>
                                    <li>Bootloader bị khóa → cần mở khóa trước</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="faq">
                <h2><i class="fas fa-question-circle me-2"></i>7. FAQ</h2>
                
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Flash firmware có mất bảo hành không?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show">
                            <div class="accordion-body">
                                Flash firmware gốc (stock) thường không ảnh hưởng bảo hành. Tuy nhiên, flash custom ROM hoặc root sẽ mất bảo hành.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Flash có mất dữ liệu không?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                Nếu dùng <strong>HOME_CSC</strong>: có thể giữ dữ liệu. Nếu dùng <strong>CSC</strong>: sẽ factory reset, mất dữ liệu. Nên backup trước khi flash!
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="price-highlight">
                <h4><i class="fas fa-shopping-cart me-2"></i>Cần hỗ trợ flash Samsung?</h4>
                <p>Thuê UnlockTool để được hỗ trợ toàn diện</p>
                <p class="price">8.000đ</p>
                <p class="h4">/ 6 tiếng</p>
                <a href="https://www.unlocktool.us/" target="_blank" class="btn btn-light btn-lg mt-3"><i class="fas fa-shopping-cart me-2"></i>Thuê Ngay - UnlockTool.us</a>
            </div>

        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="sticky-top" style="top:20px">
                <div class="price-highlight mb-4">
                    <h5><i class="fas fa-unlock me-2"></i>Thuê UnlockTool</h5>
                    <p class="price" style="font-size:2.5rem">8.000đ</p>
                    <p>/ 6 tiếng</p>
                    <a href="https://www.unlocktool.us/" target="_blank" class="btn btn-light w-100 mt-3">Thuê Ngay</a>
                </div>

                <div class="card mb-4">
                    <div class="card-header text-white" style="background:#1428a0">
                        <i class="fas fa-download me-2"></i>Tải về
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><a href="https://file.unlocktool.net/page/tool---android---pc/tool-android---pc" target="_blank">Odin3</a></li>
                        <li class="list-group-item"><a href="https://file.unlocktool.net/page/all-driver" target="_blank">Samsung Driver</a></li>
                    </ul>
                </div>

                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <i class="fas fa-link me-2"></i>Bài viết liên quan
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><a href="111-sua-imei-samsung-2026.php">Sửa IMEI Samsung</a></li>
                        <li class="list-group-item"><a href="82-frp-samsung-a12.php">FRP Samsung A12</a></li>
                        <li class="list-group-item"><a href="98-xoa-knox-samsung.php">Xóa Knox Samsung</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Social Share & Related Posts -->
<div class="container mb-5">
    <div class="content-wrapper" style="max-width:900px;margin:0 auto;background:#fff;border-radius:20px;box-shadow:0 10px 40px rgba(0,0,0,0.1);padding:40px;">
        
        <!-- Social Share -->
        <div class="social-share mb-4">
            <h5 class="mb-3"><i class="fas fa-share-alt me-2"></i>Chia sẻ bài viết:</h5>
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
            <h5 class="mb-3"><i class="fas fa-newspaper me-2"></i>Bài viết liên quan:</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title"><a href="51-samsung-frp.php" class="text-decoration-none">Bypass FRP Samsung</a></h6>
                            <small class="text-muted">Hướng dẫn chi tiết</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title"><a href="73-iphone-ipad-bypass.php" class="text-decoration-none">Bypass iCloud iPhone</a></h6>
                            <small class="text-muted">Từ iPhone 5s - X</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title"><a href="131-tai-unlocktool.php" class="text-decoration-none">Tải UnlockTool</a></h6>
                            <small class="text-muted">Download mới nhất</small>
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
                <p class="mb-0 text-muted" style="font-size:0.9rem;">Đội ngũ kỹ thuật viên chuyên nghiệp với 10+ năm kinh nghiệm trong lĩnh vực mở khóa điện thoại. Hỗ trợ 24/7 tại <a href="https://thuetaikhoan.net">Thuetaikhoan.net</a></p>
            </div>
        </div>
        
    </div>
</div>
<footer class="bg-dark text-white py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5>UnlockTool.us</h5>
                <p class="small">Cho thuê tài khoản tool mở khóa điện thoại uy tín nhất Việt Nam.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="small mb-1">Dịch vụ: <a href="https://thuetaikhoan.net" class="text-white">Thuetaikhoan.net</a></p>
                <p class="small">&copy; 2026 UnlockTool.us - All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
