<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once("../controller/cLivestream.php");
include_once("../model/mLivestream.php");

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?login');
    exit;
}

$livestream_id = $_GET['livestream_id'] ?? null;
if (!$livestream_id) {
    header('Location: ../index.php');
    exit;
}

$model = new mLivestream();
$cart = $model->getCart($_SESSION['user_id'], $livestream_id);
$livestream = $model->getLivestreamById($livestream_id);

// Lấy thông tin user
include_once("../model/mUser.php");
$mUser = new mUser();
$user = $mUser->getUserById($_SESSION['user_id']);

// Kiểm tra thông tin user
if (!$user) {
    die("Lỗi: Không tìm thấy thông tin user với ID: " . $_SESSION['user_id']);
}

// Lấy số dư từ transfer_accounts (cùng cơ sở dữ liệu với nạp tiền)
include_once("../controller/vnpay/connection.php");
try {
    $stmt = $pdo->prepare("SELECT balance FROM transfer_accounts WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    $user['balance'] = $account ? $account['balance'] : 0;
} catch(PDOException $e) {
    $user['balance'] = 0;
}

if (empty($cart['items'])) {
    header('Location: ../index.php');
    exit;
}

include_once("header.php");
?>

<!-- API địa chỉ Việt Nam -->

<style>
.checkout-container {
    background: #f8f9fa;
    min-height: 100vh;
    padding: 20px 0;
}

.container {
    max-width: 1600px;
}

.checkout-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    overflow: hidden;
    min-height: calc(100vh - 200px);
    padding-bottom: 100px;
    max-width: 1400px;
    margin: 0 auto;
}

.checkout-header {
    background: linear-gradient(135deg, #ffc107 0%, #ff8f00 100%);
    color: #333;
    padding: 30px;
    text-align: center;
}

.checkout-header h2 {
    margin: 0;
    font-size: 28px;
    font-weight: 600;
}

.checkout-header p {
    margin: 10px 0 0 0;
    opacity: 0.9;
    font-size: 16px;
}

.payment-methods {
    padding: 35px;
}

.payment-method {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.payment-method:hover {
    border-color: #ffc107;
    box-shadow: 0 4px 15px rgba(255,193,7,0.2);
}

.payment-method.selected {
    border-color: #ffc107;
    background: #fffbf0;
}

.payment-method input[type="radio"] {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}

.payment-method-content {
    display: flex;
    align-items: center;
    gap: 20px;
}

.payment-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.payment-icon.cash {
    background: linear-gradient(135deg, #28a745, #20c997);
}

.payment-icon.wallet {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
}

.payment-icon.vnpay {
    background: linear-gradient(135deg, #ffc107, #ff8f00);
}

.payment-info h5 {
    margin: 0 0 8px 0;
    font-size: 18px;
    font-weight: 600;
    color: #333;
}

.payment-info p {
    margin: 0;
    color: #666;
    font-size: 14px;
    line-height: 1.4;
}

.order-summary {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 35px;
    margin-top: -35px;
    margin-right: 35px;
    margin-left: 35px;

}

.order-summary h4 {
    margin: 0 0 20px 0;
    color: #333;
    font-size: 20px;
    font-weight: 600;
}

.order-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #e9ecef;
}

.order-item:last-child {
    border-bottom: none;
}

.order-item-image {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    object-fit: cover;
    border: 1px solid #e9ecef;
}

.order-item-info {
    flex: 1;
}

.order-item-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.order-item-details {
    color: #666;
    font-size: 14px;
}

.order-item-price {
    font-weight: 600;
    color: #007bff;
    font-size: 16px;
}

.order-total {
    margin-top: 20px;
    text-align: right;
    padding: 10px 0;
}

.order-total h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: #333;
}

.checkout-btn {
    width: 100%;
    padding: 15px;
    font-size: 18px;
    font-weight: 600;
    border-radius: 8px;
    border: none;
    margin-top: 20px;
    transition: all 0.3s ease;
}

/* Nút thanh toán dài ra tới bên trái */
.payment-button-container {
    padding-left: 0 !important;
    padding-right: 0 !important;
    margin-right: 20px;
    margin-left: 20px;
}

.checkout-btn:disabled {
    background: #6c757d;
    cursor: not-allowed;
}

.checkout-btn.primary {
    background: #ffc107;
    color: #333;
    font-weight: 600;
}

.checkout-btn.primary:hover:not(:disabled) {
    background: #ff8f00;
    transform: translateY(-2px);
}

.user-info {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 25px;
    margin-bottom: 35px;
}

.user-info h5 {
    margin: 0 0 15px 0;
    color: #333;
    font-size: 18px;
}

.user-detail {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #e9ecef;
}

.user-detail:last-child {
    border-bottom: none;
}

.user-detail-label {
    font-weight: 600;
    color: #555;
}

.user-detail-value {
    color: #333;
}

.address-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 25px;
    margin-bottom: 35px;
}

.address-section h5 {
    margin: 0 0 15px 0;
    color: #333;
    font-size: 18px;
}

.address-input-group {
    margin-bottom: 15px;
}

.address-input-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #555;
}

.address-input-group input,
.address-input-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

.address-input-group input:focus,
.address-input-group textarea:focus {
    outline: none;
    border-color: #ffc107;
    box-shadow: 0 0 0 2px rgba(255,193,7,0.2);
}

.address-input-group select {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    background: white;
    transition: border-color 0.3s ease;
    min-height: 45px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px;
    padding-right: 40px;
}

.address-input-group select:focus {
    outline: none;
    border-color: #ffc107;
    box-shadow: 0 0 0 2px rgba(255,193,7,0.2);
}

.address-input-group select:disabled {
    background: #f8f9fa;
    color: #6c757d;
    cursor: not-allowed;
}

.address-input-group select option {
    padding: 8px 12px;
    white-space: normal;
    word-wrap: break-word;
    min-height: 35px;
    line-height: 1.4;
    font-size: 14px;
}

/* Đảm bảo dropdown có đủ không gian */
.address-input-group {
    position: relative;
    margin-bottom: 20px;
}

.address-input-group select:focus {
    z-index: 10;
    position: relative;
}

/* Đảm bảo dropdown không bị cắt */
.address-input-group select {
    max-width: 100%;
    word-wrap: break-word;
}

/* Responsive cho dropdown */
@media (max-width: 991px) {
    .address-input-group select {
        font-size: 16px;
        padding: 15px 12px;
        min-height: 50px;
    }
}

@media (max-width: 576px) {
    .address-input-group select {
        font-size: 16px;
        padding: 15px 10px;
        min-height: 50px;
    }
    
    .address-input-group select option {
        font-size: 16px;
        padding: 12px 8px;
    }
}

.loading {
    position: relative;
}

.loading::after {
    content: '';
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    border: 2px solid #ffc107;
    border-top: 2px solid transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: translateY(-50%) rotate(0deg); }
    100% { transform: translateY(-50%) rotate(360deg); }
}

@media (max-width: 768px) {
    .checkout-container {
        padding: 10px;
    }
    
    .checkout-header {
        padding: 20px;
    }
    
    .checkout-header h2 {
        font-size: 24px;
    }
    
    .payment-methods {
        padding: 20px;
    }
    
    .payment-method-content {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .order-item {
        flex-direction: column;
        text-align: center;
    }
    
    .address-input-group select {
        font-size: 16px;
        padding: 15px 12px;
        min-height: 50px;
    }
    
    .address-input-group select option {
        font-size: 16px;
        padding: 12px;
        min-height: 40px;
    }
    
    /* Responsive cho layout 2 cột */
    .row .col-lg-6 {
        margin-bottom: 30px;
    }
    
    .user-info, .address-section {
        margin-bottom: 20px;
    }
}

/* Cân đối chiều cao 2 cột */
.row .col-lg-6 {
    display: flex;
    flex-direction: column;
}

.row .col-lg-6:first-child {
    justify-content: flex-start;
}

.row .col-lg-6:last-child {
    justify-content: space-between;
}

/* Đảm bảo cột phải có chiều cao tối thiểu */
.row .col-lg-6:last-child {
    min-height: 600px;
}
</style>

<div class="checkout-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="checkout-card">
                    <!-- Header -->
                    <div class="checkout-header">
                        <h2><i class="fas fa-credit-card mr-3"></i>Thanh toán đơn hàng</h2>
                        <p>Chọn phương thức thanh toán phù hợp với bạn</p>
                    </div>

                    <!-- Nội dung chính chia 2 cột -->
                    <div class="row">
                        <!-- Cột trái: Thông tin người mua và địa chỉ -->
                        <div class="col-lg-6">
                            <!-- Thông tin user -->
                            <div class="user-info">
                                <h5><i class="fas fa-user mr-2"></i>Thông tin người mua</h5>
                                <div class="user-detail">
                                    <span class="user-detail-label">Họ tên:</span>
                                    <span class="user-detail-value"><?= htmlspecialchars($user['username'] ?? 'N/A') ?></span>
                                </div>
                                <div class="user-detail">
                                    <span class="user-detail-label">Email:</span>
                                    <span class="user-detail-value"><?= htmlspecialchars($user['email'] ?? 'N/A') ?></span>
                                </div>
                                <div class="user-detail">
                                    <span class="user-detail-label">Số dư tài khoản:</span>
                                    <span class="user-detail-value" style="color: #28a745; font-weight: 600;">
                                        <?= number_format($user['balance'] ?? 0) ?> đ
                                    </span>
                                </div>
                            </div>

                            <!-- Thông tin địa chỉ giao hàng -->
                            <div class="address-section">
                                <h5><i class="fas fa-map-marker-alt mr-2"></i>Địa chỉ giao hàng</h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="address-input-group">
                                    <label for="full_name">Họ và tên người nhận *</label>
                                    <input type="text" id="full_name" name="full_name" 
                                           value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="address-input-group">
                                    <label for="phone">Số điện thoại *</label>
                                    <input type="tel" id="phone" name="phone" 
                                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="address-input-group">
                            <label for="address">Địa chỉ chi tiết *</label>
                            <textarea id="address" name="address" rows="3" 
                                      placeholder="Nhập địa chỉ chi tiết (số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố)" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-lg-4 col-md-6 col-12">
                                <div class="address-input-group">
                                    <label for="province">Tỉnh/Thành phố *</label>
                                    <select id="province" name="province" class="form-control" required onchange="loadDistricts()">
                                        <option value="">Chọn tỉnh/thành phố</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-12">
                                <div class="address-input-group">
                                    <label for="district">Quận/Huyện *</label>
                                    <select id="district" name="district" class="form-control" required onchange="loadWards()">
                                        <option value="">Chọn quận/huyện</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-12 col-12">
                                <div class="address-input-group">
                                    <label for="ward">Phường/Xã *</label>
                                    <select id="ward" name="ward" class="form-control" required>
                                        <option value="">Chọn phường/xã</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="address-input-group">
                            <label for="street">Số nhà, tên đường *</label>
                            <input type="text" id="street" name="street" 
                                   placeholder="Ví dụ: 123 Nguyễn Văn A" required>
                        </div>
                        
                            </div>
                        </div>

                        <!-- Cột phải: Phương thức thanh toán và đơn hàng -->
                        <div class="col-lg-6">
                            <!-- Phương thức thanh toán -->
                            <div class="payment-methods">
                        <h4 class="mb-4"><i class="fas fa-credit-card mr-2"></i>Chọn phương thức thanh toán</h4>
                        
                        <!-- 1. Giao trực tiếp -->
                        <div class="payment-method" onclick="selectPaymentMethod('cash')">
                            <input type="radio" name="payment_method" value="cash" id="cash">
                            <div class="payment-method-content">
                                <div class="payment-icon cash">
                                    <i class="fas fa-hand-holding-usd"></i>
                                </div>
                                <div class="payment-info">
                                    <h5>Giao trực tiếp</h5>
                                    <p>Thanh toán khi nhận hàng. Bạn sẽ thanh toán trực tiếp cho người bán khi giao hàng.</p>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Thanh toán bằng tài khoản -->
                        <div class="payment-method" onclick="selectPaymentMethod('wallet')">
                            <input type="radio" name="payment_method" value="wallet" id="wallet">
                            <div class="payment-method-content">
                                <div class="payment-icon wallet">
                                    <i class="fas fa-wallet"></i>
                                </div>
                                <div class="payment-info">
                                    <h5>Thanh toán bằng tài khoản</h5>
                                    <p>Sử dụng số dư trong tài khoản của bạn để thanh toán. Số dư hiện tại: <strong><?= number_format($user['balance'] ?? 0) ?> đ</strong></p>
                                </div>
                            </div>
                        </div>

                        <!-- 3. VNPay -->
                        <div class="payment-method" onclick="selectPaymentMethod('vnpay')">
                            <input type="radio" name="payment_method" value="vnpay" id="vnpay">
                            <div class="payment-method-content">
                                <div class="payment-icon vnpay">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <div class="payment-info">
                                    <h5>Thanh toán trực tuyến VNPay</h5>
                                    <p>Thanh toán an toàn qua VNPay với thẻ ATM, Internet Banking, hoặc ví điện tử.</p>
                                </div>
                            </div>
                        </div>
                            </div>

                            <!-- Tóm tắt đơn hàng -->
                            <div class="order-summary">
                                <h4><i class="fas fa-shopping-cart mr-2"></i>Tóm tắt đơn hàng</h4>
                                
                                <?php foreach ($cart['items'] as $item): ?>
                                <div class="order-item">
                                    <?php 
                                    $itemImage = $item['anh_dau'] ?? $item['image'] ?? 'default-product.jpg';
                                    if (!file_exists('../img/' . $itemImage)) {
                                        $itemImage = 'default-product.jpg';
                                    }
                                    ?>
                                    <img src="../img/<?= htmlspecialchars($itemImage) ?>" 
                                         alt="<?= htmlspecialchars($item['title']) ?>" 
                                         class="order-item-image">
                                    <div class="order-item-info">
                                        <div class="order-item-name"><?= htmlspecialchars($item['title']) ?></div>
                                        <div class="order-item-details">
                                            <?= number_format($item['price']) ?> đ x <?= $item['quantity'] ?>
                                        </div>
                                    </div>
                                    <div class="order-item-price">
                                        <?= number_format($item['price'] * $item['quantity']) ?> đ
                                    </div>
                                </div>
                                <?php endforeach; ?>

                                <div class="order-total">
                                    <h3>Tổng cộng: <?= number_format($cart['total']) ?> đ</h3>
                                </div>
                            </div>

                            <!-- Nút thanh toán -->
                            <div class=" payment-button-container">
                                <button class="checkout-btn primary" id="checkoutBtn" onclick="processCheckout()" disabled>
                                    <i class="fas fa-credit-card mr-2"></i>Tiến hành thanh toán
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let selectedPaymentMethod = null;

function selectPaymentMethod(method) {
    // Bỏ chọn tất cả
    document.querySelectorAll('.payment-method').forEach(el => {
        el.classList.remove('selected');
    });
    
    // Chọn phương thức được click
    const selectedElement = event.currentTarget;
    selectedElement.classList.add('selected');
    selectedElement.querySelector('input[type="radio"]').checked = true;
    
    selectedPaymentMethod = method;
    document.getElementById('checkoutBtn').disabled = false;
}

function processCheckout() {
    if (!selectedPaymentMethod) {
        showToast('Vui lòng chọn phương thức thanh toán', 'error');
        return;
    }
    
    // Kiểm tra thông tin địa chỉ
    const fullName = document.getElementById('full_name').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const province = document.getElementById('province').value;
    const district = document.getElementById('district').value;
    const ward = document.getElementById('ward').value;
    const street = document.getElementById('street').value.trim();
    
    if (!fullName) {
        showToast('Vui lòng nhập họ tên người nhận', 'error');
        return;
    }
    
    if (!phone) {
        showToast('Vui lòng nhập số điện thoại', 'error');
        return;
    }
    
    if (!province) {
        showToast('Vui lòng chọn tỉnh/thành phố', 'error');
        return;
    }
    
    if (!district) {
        showToast('Vui lòng chọn quận/huyện', 'error');
        return;
    }
    
    if (!ward) {
        showToast('Vui lòng chọn phường/xã', 'error');
        return;
    }
    
    if (!street) {
        showToast('Vui lòng nhập số nhà, tên đường', 'error');
        return;
    }
    
    // Kiểm tra định dạng số điện thoại
    const phoneRegex = /^[0-9]{10,11}$/;
    if (!phoneRegex.test(phone)) {
        showToast('Số điện thoại không hợp lệ', 'error');
        return;
    }

    const checkoutBtn = document.getElementById('checkoutBtn');
    checkoutBtn.disabled = true;
    checkoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Đang xử lý...';

    const formData = new FormData();
    formData.append('action', 'checkout');
    formData.append('livestream_id', '<?= $livestream_id ?>');
    formData.append('payment_method', selectedPaymentMethod);
    
    // Thêm thông tin địa chỉ
    formData.append('full_name', document.getElementById('full_name').value);
    formData.append('phone', document.getElementById('phone').value);
    formData.append('province', document.getElementById('province').value);
    formData.append('district', document.getElementById('district').value);
    formData.append('ward', document.getElementById('ward').value);
    formData.append('street', document.getElementById('street').value);
    formData.append('address', document.getElementById('address').value);

    fetch('../api/livestream-api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Gửi thông báo đơn hàng mới qua WebSocket
            const livestreamId = <?= $livestream_id ?? 0 ?>;
            if (livestreamId) {
                sendOrderCreatedNotification(livestreamId, data);
            }
            
            if (selectedPaymentMethod === 'vnpay' && data.payment_url) {
                // Chuyển đến VNPay
                window.location.href = data.payment_url;
            } else {
                // Thanh toán thành công - chuyển đến trang quản lý đơn hàng
                showToast(data.message || 'Đặt hàng thành công!', 'success');
                setTimeout(() => {
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                    } else {
                        window.location.href = '../index.php?my-orders';
                    }
                }, 1500);
            }
        } else {
            showToast('Lỗi: ' + (data.message || 'Không thể xử lý đơn hàng'), 'error');
            checkoutBtn.disabled = false;
            checkoutBtn.innerHTML = '<i class="fas fa-credit-card mr-2"></i>Tiến hành thanh toán';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Có lỗi xảy ra khi xử lý đơn hàng', 'error');
        checkoutBtn.disabled = false;
        checkoutBtn.innerHTML = '<i class="fas fa-credit-card mr-2"></i>Tiến hành thanh toán';
    });
}

// Hàm gửi thông báo đơn hàng mới qua WebSocket
function sendOrderCreatedNotification(livestreamId, orderData) {
    // Tạo WebSocket connection tạm thời để gửi message
    try {
        const ws = new WebSocket('ws://localhost:3000');
        let messageSent = false;
        
        ws.onopen = function() {
            console.log('📦 WebSocket connected, joining livestream room');
            // Join livestream room trước
            ws.send(JSON.stringify({
                type: 'join_livestream',
                livestream_id: livestreamId,
                user_id: <?= isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0 ?>,
                user_type: 'viewer'
            }));
            
            // Gửi message order_created sau khi join (delay nhỏ)
            setTimeout(() => {
                if (!messageSent && ws.readyState === WebSocket.OPEN) {
                    console.log('📦 Sending order_created message');
                    ws.send(JSON.stringify({
                        type: 'order_created',
                        livestream_id: livestreamId,
                        order_id: orderData.order_id || null,
                        order_code: orderData.order_code || '',
                        total_amount: orderData.total_amount || 0
                    }));
                    messageSent = true;
                    
                    // Đóng connection sau khi gửi
                    setTimeout(() => {
                        ws.close();
                    }, 500);
                }
            }, 200);
        };
        
        ws.onerror = function(error) {
            console.warn('⚠️ WebSocket error (non-critical):', error);
            // Không ảnh hưởng đến flow chính
        };
    } catch (error) {
        console.warn('⚠️ Failed to send order_created via WebSocket (non-critical):', error);
        // Không ảnh hưởng đến flow chính
    }
}

// Kiểm tra số dư khi chọn thanh toán bằng ví
document.getElementById('wallet').addEventListener('change', function() {
    const userBalance = <?= $user['balance'] ?? 0 ?>;
    const totalAmount = <?= $cart['total'] ?>;
    
    
    if (this.checked && userBalance < totalAmount) {
        showToast('Số dư tài khoản không đủ để thanh toán', 'warning');
    }
});

// API địa chỉ Việt Nam
let provinces = [];
let districts = [];
let wards = [];

// Load danh sách tỉnh/thành phố khi trang load
document.addEventListener('DOMContentLoaded', function() {
    loadProvinces();
});

async function loadProvinces() {
    try {
        const response = await fetch('https://provinces.open-api.vn/api/');
        provinces = await response.json();
        
        const provinceSelect = document.getElementById('province');
        provinceSelect.innerHTML = '<option value="">Chọn tỉnh/thành phố</option>';
        
        provinces.forEach(province => {
            const option = document.createElement('option');
            option.value = province.code;
            option.textContent = province.name;
            provinceSelect.appendChild(option);
        });
    } catch (error) {
        console.error('Lỗi khi tải danh sách tỉnh/thành phố:', error);
        showToast('Không thể tải danh sách tỉnh/thành phố', 'error');
    }
}

async function loadDistricts() {
    const provinceSelect = document.getElementById('province');
    const districtSelect = document.getElementById('district');
    const wardSelect = document.getElementById('ward');
    
    if (!provinceSelect.value) {
        districtSelect.innerHTML = '<option value="">Chọn quận/huyện</option>';
        wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';
        return;
    }
    
    districtSelect.disabled = true;
    districtSelect.classList.add('loading');
    wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';
    
    try {
        const response = await fetch(`https://provinces.open-api.vn/api/p/${provinceSelect.value}?depth=2`);
        const data = await response.json();
        
        districtSelect.innerHTML = '<option value="">Chọn quận/huyện</option>';
        data.districts.forEach(district => {
            const option = document.createElement('option');
            option.value = district.code;
            option.textContent = district.name;
            districtSelect.appendChild(option);
        });
        
        districtSelect.disabled = false;
        districtSelect.classList.remove('loading');
        updateAddress();
    } catch (error) {
        console.error('Lỗi khi tải danh sách quận/huyện:', error);
        showToast('Không thể tải danh sách quận/huyện', 'error');
        districtSelect.disabled = false;
        districtSelect.classList.remove('loading');
    }
}

async function loadWards() {
    const districtSelect = document.getElementById('district');
    const wardSelect = document.getElementById('ward');
    
    if (!districtSelect.value) {
        wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';
        return;
    }
    
    wardSelect.disabled = true;
    wardSelect.classList.add('loading');
    
    try {
        const response = await fetch(`https://provinces.open-api.vn/api/d/${districtSelect.value}?depth=2`);
        const data = await response.json();
        
        wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';
        data.wards.forEach(ward => {
            const option = document.createElement('option');
            option.value = ward.code;
            option.textContent = ward.name;
            wardSelect.appendChild(option);
        });
        
        wardSelect.disabled = false;
        wardSelect.classList.remove('loading');
        updateAddress();
    } catch (error) {
        console.error('Lỗi khi tải danh sách phường/xã:', error);
        showToast('Không thể tải danh sách phường/xã', 'error');
        wardSelect.disabled = false;
        wardSelect.classList.remove('loading');
    }
}

function updateAddress() {
    const provinceSelect = document.getElementById('province');
    const districtSelect = document.getElementById('district');
    const wardSelect = document.getElementById('ward');
    const streetInput = document.getElementById('street');
    const addressTextarea = document.getElementById('address');
    
    let address = '';
    
    if (streetInput.value.trim()) {
        address += streetInput.value.trim();
    }
    
    if (wardSelect.value && wardSelect.selectedOptions[0]) {
        if (address) address += ', ';
        address += wardSelect.selectedOptions[0].textContent;
    }
    
    if (districtSelect.value && districtSelect.selectedOptions[0]) {
        if (address) address += ', ';
        address += districtSelect.selectedOptions[0].textContent;
    }
    
    if (provinceSelect.value && provinceSelect.selectedOptions[0]) {
        if (address) address += ', ';
        address += provinceSelect.selectedOptions[0].textContent;
    }
    
    addressTextarea.value = address;
}

// Lắng nghe thay đổi trong các trường địa chỉ
document.getElementById('street').addEventListener('input', updateAddress);
document.getElementById('ward').addEventListener('change', updateAddress);
</script>

<?php include_once("footer.php"); ?>

