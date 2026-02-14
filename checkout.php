<?php
// checkout.php - Hiển thị xác nhận đơn hàng trước khi thanh toán
session_start();
require 'config.php';
require 'helpers.php';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_POST['price_id'])) {
    echo "<h3>Không có gói thuê được chọn.</h3><a href='index.php'>Quay lại</a>";
    exit;
}

$price_id = intval($_POST['price_id']);
$res = $conn->query("SELECT * FROM prices WHERE id = {$price_id}");
if (!$res || $res->num_rows === 0) {
    echo "<h3>Gói thuê không hợp lệ.</h3><a href='index.php'>Quay lại</a>";
    exit;
}
$price = $res->fetch_assoc();

$page_title = 'Xác nhận đơn hàng';
$body_class = '';
require 'includes/header.php';
?>

<!-- reCAPTCHA v3 -->
<script src="https://www.google.com/recaptcha/api.js?render=6Lf1I1IsAAAAAJKiY4CNETg_LD6Nkj0nRIGgOJtl"></script>

<style>
.checkout-wrapper {
    min-height: 85vh; display: flex; align-items: center; justify-content: center;
    padding: 40px 20px; background: linear-gradient(180deg, #f0f4ff 0%, #fff 100%);
}
.checkout-card {
    background: white; border-radius: 20px; max-width: 520px; width: 100%;
    box-shadow: 0 4px 24px rgba(0,0,0,0.07); border: 1px solid #e8ecf2;
    overflow: hidden;
}
.checkout-header {
    background: linear-gradient(135deg, #1e2128, #2d3548); color: white;
    padding: 28px 30px; text-align: center;
}
.checkout-header h2 { font-size: 1.3rem; font-weight: 700; margin: 0 0 4px; }
.checkout-header p { font-size: 0.84rem; color: #94a3b8; margin: 0; }
.checkout-body { padding: 28px 30px; }
.checkout-detail {
    background: #f8fafc; border-radius: 12px; padding: 20px;
    margin-bottom: 20px; border: 1px solid #eef1f6;
}
.checkout-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 10px 0; border-bottom: 1px solid #e8ecf2;
}
.checkout-row:last-child { border-bottom: none; }
.checkout-row .label { font-size: 0.88rem; color: #64748b; display: flex; align-items: center; gap: 8px; }
.checkout-row .label i { font-size: 0.9rem; width: 18px; text-align: center; }
.checkout-row .value { font-size: 0.92rem; font-weight: 600; color: #1e2128; }
.checkout-price {
    font-size: 1.4rem; font-weight: 800; color: #16a34a;
    background: linear-gradient(135deg, #ecfdf5, #d1fae5); padding: 4px 14px;
    border-radius: 8px;
}
.checkout-note {
    display: flex; align-items: flex-start; gap: 10px;
    background: #f0f5ff; border: 1px solid #d6e3f8; border-radius: 10px;
    padding: 14px 16px; margin-bottom: 20px; font-size: 0.82rem; color: #475569; line-height: 1.5;
}
.checkout-note i { color: #3b82f6; margin-top: 2px; flex-shrink: 0; }
.checkout-btn-confirm {
    width: 100%; padding: 14px; border: none; border-radius: 12px;
    background: linear-gradient(135deg, #16a34a, #15803d); color: white;
    font-size: 0.95rem; font-weight: 700; cursor: pointer;
    transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px;
}
.checkout-btn-confirm:hover { transform: translateY(-1px); box-shadow: 0 4px 16px rgba(22,163,74,0.3); }
.checkout-btn-confirm:disabled { opacity: 0.6; cursor: not-allowed; transform: none; box-shadow: none; }
.checkout-btn-back {
    width: 100%; padding: 12px; border: 1px solid #e0e4ea; border-radius: 12px;
    background: white; color: #64748b; font-size: 0.88rem; font-weight: 600;
    cursor: pointer; transition: all 0.2s; text-align: center; display: block;
    text-decoration: none; margin-top: 10px;
}
.checkout-btn-back:hover { border-color: #94a3b8; color: #1e2128; background: #f8fafc; }
.checkout-trust {
    display: flex; justify-content: center; gap: 24px; margin-top: 20px;
    padding-top: 16px; border-top: 1px solid #eef1f6;
}
.checkout-trust-item { display: flex; align-items: center; gap: 6px; font-size: 0.75rem; color: #94a3b8; }
.checkout-trust-item i { font-size: 0.85rem; color: #16a34a; }
@media (max-width: 480px) {
    .checkout-wrapper { padding: 20px 12px; }
    .checkout-header { padding: 22px 20px; }
    .checkout-body { padding: 22px 20px; }
    .checkout-price { font-size: 1.15rem; }
    .checkout-trust { gap: 14px; }
}
</style>

<div class="checkout-wrapper">
    <div class="checkout-card">
        <div class="checkout-header">
            <h2>🛒 Xác nhận đơn hàng</h2>
            <p>Kiểm tra thông tin trước khi thanh toán</p>
        </div>
        <div class="checkout-body">
            <div class="checkout-detail">
                <div class="checkout-row">
                    <span class="label"><i class="fas fa-cube"></i> Sản phẩm</span>
                    <span class="value">UnlockTool</span>
                </div>
                <div class="checkout-row">
                    <span class="label"><i class="fas fa-clock"></i> Gói thuê</span>
                    <span class="value"><?php echo displayPackageName($price['hours']); ?></span>
                </div>
                <div class="checkout-row">
                    <span class="label"><i class="fas fa-university"></i> Thanh toán</span>
                    <span class="value">Chuyển khoản NH</span>
                </div>
                <div class="checkout-row">
                    <span class="label"><i class="fas fa-tag"></i> Thành tiền</span>
                    <span class="checkout-price"><?php echo formatMoney($price['price']); ?></span>
                </div>
            </div>

            <div class="checkout-note">
                <i class="fas fa-info-circle"></i>
                <span>Bạn sẽ được cấp tài khoản ngay sau khi thanh toán thành công. Hệ thống tự động xác nhận chuyển khoản.</span>
            </div>

            <form method="post" action="create_order.php" id="checkoutForm">
                <input type="hidden" name="price_id" value="<?php echo $price_id; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="recaptcha_token" id="recaptcha_token">
                <button type="submit" class="checkout-btn-confirm" id="submitBtn">
                    <i class="fas fa-lock"></i> Xác nhận và thanh toán
                </button>
                <a href="index.php" class="checkout-btn-back">← Quay lại trang chủ</a>
            </form>

            <div class="checkout-trust">
                <span class="checkout-trust-item"><i class="fas fa-shield-alt"></i> Bảo mật</span>
                <span class="checkout-trust-item"><i class="fas fa-bolt"></i> Tức thì</span>
                <span class="checkout-trust-item"><i class="fas fa-headset"></i> Hỗ trợ 24/7</span>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
    
    grecaptcha.ready(function() {
        grecaptcha.execute('6Lf1I1IsAAAAAJKiY4CNETg_LD6Nkj0nRIGgOJtl', {action: 'create_order'}).then(function(token) {
            document.getElementById('recaptcha_token').value = token;
            document.getElementById('checkoutForm').submit();
        });
    });
});
</script>

<?php require 'includes/footer.php'; ?>
