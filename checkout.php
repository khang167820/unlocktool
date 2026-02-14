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

<div class="d-flex justify-content-center align-items-center" style="min-height: 90vh;">
<div class="overlay" style="max-width: 600px; margin: 20px;">
    <h2 class="mb-4 text-center">Xác nhận thuê tài khoản</h2>
    <div class="card">
        <div class="card-body">
            <p><strong>Gói thuê:</strong> <?php echo displayPackageName($price['hours']); ?></p>
            <p><strong>Giá tiền:</strong> <?php echo formatMoney($price['price']); ?></p>
            <p class="text-muted">Bạn sẽ được cấp tài khoản sau khi thanh toán thành công.<br>Vui lòng kiểm tra kỹ thông tin trước khi tiếp tục.</p>

            <form method="post" action="create_order.php" id="checkoutForm">
                <input type="hidden" name="price_id" value="<?php echo $price_id; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="recaptcha_token" id="recaptcha_token">
                <button type="submit" class="btn btn-success btn-block" id="submitBtn">✅ Xác nhận và thanh toán</button>
                <a href="index.php" class="btn btn-outline-secondary btn-block mt-2">⬅ Quay lại</a>
            </form>
        </div>
    </div>
</div>
</div>

<script>
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '⏳ Đang xử lý...';
    
    grecaptcha.ready(function() {
        grecaptcha.execute('6Lf1I1IsAAAAAJKiY4CNETg_LD6Nkj0nRIGgOJtl', {action: 'create_order'}).then(function(token) {
            document.getElementById('recaptcha_token').value = token;
            document.getElementById('checkoutForm').submit();
        });
    });
});
</script>

<?php require 'includes/footer.php'; ?>
