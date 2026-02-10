/* ===== Article Shared JS — UnlockTool.us ===== */

document.addEventListener('DOMContentLoaded', function () {

    // ===== Reading Progress Bar =====
    const progressBar = document.querySelector('.reading-progress');
    if (progressBar) {
        window.addEventListener('scroll', function () {
            const scrollTop = window.scrollY;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
            progressBar.style.width = progress + '%';
        });
    }

    // ===== Back to Top Button =====
    const backBtn = document.querySelector('.back-to-top');
    if (backBtn) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 400) {
                backBtn.classList.add('visible');
            } else {
                backBtn.classList.remove('visible');
            }
        });
        backBtn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ===== Scroll Reveal =====
    const reveals = document.querySelectorAll('.reveal');
    if (reveals.length > 0) {
        const revealObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

        reveals.forEach(function (el) { revealObserver.observe(el); });
    }

    // ===== Mobile Hamburger Menu =====
    const hamburger = document.querySelector('.nav-hamburger');
    const navLinks = document.querySelector('.nav-links');
    if (hamburger && navLinks) {
        hamburger.addEventListener('click', function () {
            navLinks.classList.toggle('active');
            this.innerHTML = navLinks.classList.contains('active')
                ? '<i class="fas fa-times"></i>'
                : '<i class="fas fa-bars"></i>';
        });
    }

    // ===== Auto-add reveal class to main content elements =====
    const contentWrapper = document.querySelector('.content-wrapper');
    if (contentWrapper) {
        const elements = contentWrapper.querySelectorAll('h2, .feature-grid, .tool-card, .platform-card, .compare-table, .price-table, .stat-row, .cta-section, .review-mini, .faq-item, .highlight-box, .img-article, .compare-box, .step-box, .related-posts');
        elements.forEach(function (el) {
            if (!el.classList.contains('reveal')) {
                el.classList.add('reveal');
            }
        });

        // Re-observe newly added reveals
        const newReveals = contentWrapper.querySelectorAll('.reveal:not(.revealed)');
        if (newReveals.length > 0) {
            const autoObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                        autoObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

            newReveals.forEach(function (el) { autoObserver.observe(el); });
        }
    }

    // ===== Smooth scroll for TOC links =====
    document.querySelectorAll('.toc a[href^="#"]').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const offset = 80;
                const y = target.getBoundingClientRect().top + window.scrollY - offset;
                window.scrollTo({ top: y, behavior: 'smooth' });
            }
        });
    });

});
