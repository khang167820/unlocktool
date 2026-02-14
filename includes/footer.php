<?php
/**
 * Footer chung - Zalo icon và scripts
 */
?>
<!-- Zalo Support -->
<a href="https://zalo.me/0777333763" target="_blank" class="zalo-link">
    <img src="https://stc-zaloprofile.zdn.vn/pc/v1/images/zalo_sharelogo.png" class="zalo-icon" alt="Zalo">
</a>
<div class="zalo-text">Liên hệ Zalo hoặc SDT: 0777333763 (Mai Quyên) để được hỗ trợ nhanh chóng (24/7)</div>

<style>
.zalo-icon {
    position: fixed;
    bottom: 25px;
    left: 25px;
    width: 55px;
    height: 55px;
    z-index: 999;
}
.zalo-text {
    position: fixed;
    bottom: 30px;
    left: 90px;
    background: rgba(255,255,255,0.9);
    padding: 8px 12px;
    border-radius: 6px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    z-index: 998;
    font-weight: bold;
    color: #dc3545;
}
@media (max-width: 768px) {
    .zalo-text { font-size: 0.85rem; left: 70px; }
    .zalo-icon { width: 45px; height: 45px; bottom: 20px; left: 15px; }
}
</style>

<script>
// Countdown timer cho thời gian còn lại
function updateCountdowns() {
    const els = document.querySelectorAll('[data-expire]');
    const now = Date.now();
    els.forEach(el => {
        const t = parseInt(el.dataset.expire) * 1000;
        const d = t - now;
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
setInterval(updateCountdowns, 1000);
window.addEventListener('load', updateCountdowns);

// Copy to clipboard helper
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Đã sao chép: ' + text);
    });
}

// Copy button handler (vanilla JS — no jQuery dependency)
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.copy-btn');
    if (!btn) return;
    var text = btn.getAttribute('data-clipboard-text') || btn.getAttribute('data-copy');
    if (text) {
        navigator.clipboard.writeText(text).then(function() {
            var oldText = btn.textContent;
            btn.textContent = 'Đã sao chép';
            setTimeout(function() { btn.textContent = oldText; }, 1200);
        });
    }
});
</script>
<?php if (!empty($extra_scripts)) echo $extra_scripts; ?>
<script src="https://code.jquery.com/jquery-3.5.1.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" defer></script>
</body>
</html>

