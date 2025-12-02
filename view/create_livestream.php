<?php
include_once("view/header.php");

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?login');
    exit;
}

require_once("model/mLivestreamPackage.php");
require_once("model/mUser.php");
$packageModel = new mLivestreamPackage();
$userModel = new mUser();
$userInfo = $userModel->getUserById($_SESSION['user_id']);
$userDisplayName = $userInfo['username'] ?? ($_SESSION['username'] ?? 'Người dùng');
$userAvatar = 'img/default-avatar.jpg';
if (!empty($userInfo['avatar'])) {
    $avatarCandidate = 'img/' . basename($userInfo['avatar']);
    if (file_exists($avatarCandidate)) {
        $userAvatar = $avatarCandidate;
    }
}
$livestreamPermission = $packageModel->checkLivestreamPermission($_SESSION['user_id']);
$canLivestream = $livestreamPermission['has_permission'];
$activeRegistration = $livestreamPermission['registration'] ?? null;

if (!$canLivestream || !$activeRegistration) {
    ?>
    <div class="container py-5 my-5">
        <div class="alert alert-warning shadow-sm text-center" role="alert" style="max-width: 640px; margin: 0 auto; border-radius: 16px;">
            <h4 class="alert-heading mb-3"><i class="fas fa-exclamation-triangle mr-2"></i>Không thể tạo livestream</h4>
            <p class="mb-3">
                <?= htmlspecialchars($livestreamPermission['message'] ?? 'Gói livestream của bạn đã hết hạn. Vui lòng gia hạn để tiếp tục livestream.') ?>
            </p>
            <a href="index.php?page=livestream-packages" class="btn btn-warning font-weight-bold px-4">
                Gia hạn gói livestream
            </a>
        </div>
    </div>
    <?php
    include_once("view/footer.php");
    exit;
}

$registrationStart = new DateTime($activeRegistration['registration_date']);
$registrationEnd = new DateTime($activeRegistration['expiry_date']);
$now = new DateTime();
$defaultStart = $now < $registrationStart ? $registrationStart : $now;
if ($defaultStart > $registrationEnd) {
    $defaultStart = clone $registrationStart;
}

$registrationStartIso = $registrationStart->format('Y-m-d\TH:i');
$registrationEndIso = $registrationEnd->format('Y-m-d\TH:i');
$defaultStartIso = $defaultStart->format('Y-m-d\TH:i');
?>

<style>
.create-livestream-container {
    background: #f8f9fa;
    min-height: 100vh;
    padding: 40px 0;
}

.form-container {
    background: white;
    border-radius: 15px;
    padding: 40px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    max-width: 800px;
    margin: 0 auto;
}

.form-title {
    text-align: center;
    margin-bottom: 40px;
    color: #333;
}

.form-title h2 {
    font-weight: 700;
    margin-bottom: 10px;
}

.form-title p {
    color: #666;
    font-size: 16px;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    display: block;
}

.form-control {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 12px 15px;
    font-size: 16px;
    transition: all 0.3s;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

/* File input styling */
input[type="file"].form-control {
    padding: 0;
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    height: auto;
    min-height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}

input[type="file"].form-control:hover {
    border-color: #007bff;
    background: #e3f2fd;
}

input[type="file"].form-control::-webkit-file-upload-button {
    background: #007bff;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    margin-right: 15px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
}

input[type="file"].form-control::-webkit-file-upload-button:hover {
    background: #0056b3;
    transform: translateY(-1px);
}

input[type="file"].form-control::file-selector-button {
    background: #007bff;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    margin-right: 15px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
}

input[type="file"].form-control::file-selector-button:hover {
    background: #0056b3;
    transform: translateY(-1px);
}

.btn-create {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    padding: 15px 40px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 18px;
    width: 100%;
    transition: all 0.3s;
}

.btn-create:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

/* Livestream icons đỏ */
.create-livestream-container i.fas.fa-video,
.create-livestream-container i.fas.fa-broadcast-tower,
.create-livestream-container i.fas.fa-play-circle,
.create-livestream-container i.fas.fa-circle-play {
    color: #dc3545 !important;
}

.product-selection {
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    min-height: 200px;
}

.product-item {
    display: flex;
    align-items: center;
    padding: 10px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.3s;
}

.product-item:hover {
    background: #f8f9fa;
    border-color: #007bff;
}

.product-item.selected {
    background: #e3f2fd;
    border-color: #2196f3;
}

.product-item img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    margin-right: 15px;
}

.product-info h6 {
    margin: 0 0 5px 0;
    font-weight: 600;
}

.product-info .price {
    color: #28a745;
    font-weight: bold;
}

.checkbox-wrapper {
    margin-left: auto;
}

.step-indicator {
    display: flex;
    justify-content: center;
    margin-bottom: 40px;
}

.step {
    display: flex;
    align-items: center;
    margin: 0 20px;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 10px;
}

.step.active .step-number {
    background: #007bff;
    color: white;
}

.step.completed .step-number {
    background: #28a745;
    color: white;
}

.step-line {
    width: 50px;
    height: 2px;
    background: #e9ecef;
    margin: 0 10px;
}

.step.completed + .step .step-line {
    background: #28a745;
}
</style>

<div class="container-fluid create-livestream-container" style="padding: 0px;">
    <div class="row">
        <div class="col-12">
            <div class="form-container">
                <!-- Step Indicator -->
                <div class="step-indicator">
                    <div class="step active">
                        <div class="step-number">1</div>
                        <span>Thông tin cơ bản</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <span>Chọn sản phẩm</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <span>Hoàn tất</span>
                    </div>
                </div>

                <!-- Form Title -->
                <div class="form-title">
                    <h2><i class="fas fa-video text-primary mr-2"></i>Tạo Livestream Mới</h2>
                    <p>Chia sẻ sản phẩm của bạn với khách hàng qua livestream</p>
                </div>

                <div class="alert alert-info" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-clock fa-lg mr-3 text-primary"></i>
                        <div class="text-left">
                            <strong>Gói livestream của bạn có hiệu lực:</strong><br>
                            <span>Từ <strong><?= date('d/m/Y H:i', strtotime($activeRegistration['registration_date'])) ?></strong> đến <strong><?= date('d/m/Y H:i', strtotime($activeRegistration['expiry_date'])) ?></strong></span>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form id="create-livestream-form">
                    <!-- Step 1: Basic Info -->
                    <div class="step-content" id="step-1">
                        <div class="form-group">
                            <label for="livestream-title">Tiêu đề livestream *</label>
                            <input type="text" class="form-control" id="livestream-title" 
                                   placeholder="Ví dụ: Bán điện thoại iPhone giá rẻ" required>
                        </div>

                        <div class="form-group">
                            <label for="livestream-description">Mô tả chi tiết</label>
                            <textarea class="form-control" id="livestream-description" rows="4" 
                                      placeholder="Mô tả về livestream, sản phẩm sẽ bán..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start-time">Thời gian bắt đầu</label>
                                    <input type="datetime-local"
                                           class="form-control"
                                           id="start-time"
                                           value="<?= $defaultStartIso ?>"
                                           min="<?= $registrationStartIso ?>"
                                           max="<?= $registrationEndIso ?>">
                                    <small class="text-muted">Trong khoảng hiệu lực gói livestream.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end-time">Thời gian kết thúc (tùy chọn)</label>
                                    <input type="datetime-local"
                                           class="form-control"
                                           id="end-time"
                                           min="<?= $registrationStartIso ?>"
                                           max="<?= $registrationEndIso ?>">
                                    <small class="text-muted">Nếu bỏ trống, mặc định livestream sẽ diễn ra trong khung giờ đã đăng ký.</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="livestream-image">Hình ảnh đại diện</label>
                            <input type="file" class="form-control" id="livestream-image" accept="image/*" onchange="previewImage(this)">
                            <small class="form-text text-muted">Hình ảnh sẽ hiển thị trong danh sách livestream</small>
                            <div id="image-preview" class="mt-2" style="display: none;">
                                <img id="preview-img" src="" alt="Preview" style="max-width: 200px; max-height: 150px; border-radius: 8px;">
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Product Selection -->
                    <div class="step-content" id="step-2" style="display: none;">
                        <h5 class="mb-3">Chọn sản phẩm sẽ bán trong livestream</h5>
                        <div class="product-selection" id="product-selection">
                            <div class="text-center text-muted">
                                <i class="fas fa-box fa-3x mb-3"></i>
                                <p>Đang tải danh sách sản phẩm...</p>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary" onclick="loadMoreProducts()">
                            <i class="fas fa-plus mr-2"></i>Thêm sản phẩm khác
                        </button>
                    </div>

                    <!-- Step 3: Preview -->
                    <div class="step-content" id="step-3" style="display: none;">
                        <h5 class="mb-3">Xem trước livestream</h5>
                        <div class="preview-card" style="border: 1px solid #e9ecef; border-radius: 10px; padding: 20px;">
                            <div class="d-flex align-items-center mb-3">
                                <img src="<?= htmlspecialchars($userAvatar) ?>" class="rounded-circle mr-3" width="50" height="50" style="object-fit: cover; border-radius: 50%;">
                                <div>
                                    <h6 class="mb-0"><?= htmlspecialchars($userDisplayName) ?></h6>
                                    <small class="text-muted">Sắp live</small>
                                </div>
                            </div>
                            <h4 id="preview-title">Tiêu đề livestream</h4>
                            <p id="preview-description" class="text-muted">Mô tả livestream</p>
                            <div class="selected-products" id="preview-products">
                                <!-- Selected products will be shown here -->
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary" id="prev-btn" style="display: none;" onclick="prevStep()">
                            <i class="fas fa-arrow-left mr-2"></i>Quay lại
                        </button>
                        <div class="ml-auto">
                            <button type="button" class="btn btn-outline-primary mr-2" id="next-btn" onclick="nextStep()">
                                Tiếp theo <i class="fas fa-arrow-right ml-2"></i>
                            </button>
                            <button type="submit" class="btn btn-create" id="create-btn" style="display: none;">
                                <i class="fas fa-video mr-2"></i>Tạo Livestream
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const LIVESTREAM_ALLOWED_START = '<?= $registrationStartIso ?>';
const LIVESTREAM_ALLOWED_END = '<?= $registrationEndIso ?>';
let currentStep = 1;
let selectedProducts = [];

document.addEventListener('DOMContentLoaded', function() {
    loadProducts();
    updateStepDisplay();
    initTimeRangeControls();
});

function initTimeRangeControls() {
    const startInput = document.getElementById('start-time');
    const endInput = document.getElementById('end-time');
    if (!startInput || !endInput) return;

    startInput.min = LIVESTREAM_ALLOWED_START;
    startInput.max = LIVESTREAM_ALLOWED_END;
    endInput.min = startInput.value || LIVESTREAM_ALLOWED_START;
    endInput.max = LIVESTREAM_ALLOWED_END;

    startInput.addEventListener('change', () => {
        if (!startInput.value) {
            startInput.value = LIVESTREAM_ALLOWED_START;
        }
        if (!isWithinAllowedRange(startInput.value)) {
            alert('Thời gian bắt đầu phải nằm trong thời hạn gói livestream.');
            startInput.value = LIVESTREAM_ALLOWED_START;
        }
        endInput.min = startInput.value;
        if (endInput.value && new Date(endInput.value) <= new Date(startInput.value)) {
            endInput.value = '';
        }
    });

    endInput.addEventListener('change', () => {
        if (endInput.value && !isWithinAllowedRange(endInput.value)) {
            alert('Thời gian kết thúc phải nằm trong thời hạn gói livestream.');
            endInput.value = '';
        } else if (endInput.value && new Date(endInput.value) <= new Date(startInput.value)) {
            alert('Thời gian kết thúc phải sau thời gian bắt đầu.');
            endInput.value = '';
        }
    });
}

function isWithinAllowedRange(value) {
    if (!value) return false;
    const startBoundary = new Date(LIVESTREAM_ALLOWED_START);
    const endBoundary = new Date(LIVESTREAM_ALLOWED_END);
    const compareDate = new Date(value);
    return compareDate >= startBoundary && compareDate <= endBoundary;
}

function nextStep() {
    if (validateCurrentStep()) {
        currentStep++;
        updateStepDisplay();
    }
}

function prevStep() {
    currentStep--;
    updateStepDisplay();
}

function updateStepDisplay() {
    // Hide all steps
    document.querySelectorAll('.step-content').forEach(step => {
        step.style.display = 'none';
    });
    
    // Show current step
    document.getElementById('step-' + currentStep).style.display = 'block';
    
    // Update step indicators
    document.querySelectorAll('.step').forEach((step, index) => {
        step.classList.remove('active', 'completed');
        if (index + 1 < currentStep) {
            step.classList.add('completed');
        } else if (index + 1 === currentStep) {
            step.classList.add('active');
        }
    });
    
    // Update buttons
    document.getElementById('prev-btn').style.display = currentStep > 1 ? 'block' : 'none';
    document.getElementById('next-btn').style.display = currentStep < 3 ? 'block' : 'none';
    document.getElementById('create-btn').style.display = currentStep === 3 ? 'block' : 'none';
    
    // Update preview if on step 3
    if (currentStep === 3) {
        updatePreview();
    }
}

function validateCurrentStep() {
    if (currentStep === 1) {
        const title = document.getElementById('livestream-title').value.trim();
        if (!title) {
            alert('Vui lòng nhập tiêu đề livestream');
            return false;
        }

        const startVal = document.getElementById('start-time').value;
        if (!startVal) {
            alert('Vui lòng chọn thời gian bắt đầu livestream');
            return false;
        }
        if (!isWithinAllowedRange(startVal)) {
            alert('Thời gian bắt đầu phải nằm trong thời hạn gói livestream.');
            return false;
        }

        const endVal = document.getElementById('end-time').value;
        if (endVal) {
            if (!isWithinAllowedRange(endVal)) {
                alert('Thời gian kết thúc phải nằm trong thời hạn gói livestream.');
                return false;
            }
            if (new Date(endVal) <= new Date(startVal)) {
                alert('Thời gian kết thúc phải sau thời gian bắt đầu.');
                return false;
            }
        }
    }
    return true;
}

function loadProducts() {
    // Load user's products
    fetch('api/livestream-api.php?action=get_user_products')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayProducts(data.products);
        }
    })
    .catch(error => {
        console.error('Error loading products:', error);
        document.getElementById('product-selection').innerHTML = 
            '<div class="text-center text-muted"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p>Không thể tải sản phẩm</p></div>';
    });
}

function displayProducts(products) {
    const container = document.getElementById('product-selection');
    
    if (products.length === 0) {
        container.innerHTML = 
            '<div class="text-center text-muted"><i class="fas fa-box fa-3x mb-3"></i><p>Bạn chưa có sản phẩm nào. <a href="?dangTin">Tạo sản phẩm mới</a></p></div>';
        return;
    }
    
    container.innerHTML = products.map(product => `
        <div class="product-item" data-product-id="${product.id}">
            <img src="img/${product.image || 'default-product.jpg'}" alt="${product.title}">
            <div class="product-info">
                <h6>${product.title}</h6>
                <div class="price">${formatMoney(product.price)} đ</div>
            </div>
            <div class="checkbox-wrapper">
                <input type="checkbox" onchange="toggleProduct(${product.id}, this.checked)">
            </div>
        </div>
    `).join('');
}

function toggleProduct(productId, selected) {
    const productItem = document.querySelector(`[data-product-id="${productId}"]`);
    
    if (selected) {
        selectedProducts.push(productId);
        productItem.classList.add('selected');
    } else {
        selectedProducts = selectedProducts.filter(id => id !== productId);
        productItem.classList.remove('selected');
    }
}

function updatePreview() {
    const title = document.getElementById('livestream-title').value;
    const description = document.getElementById('livestream-description').value;
    
    document.getElementById('preview-title').textContent = title || 'Tiêu đề livestream';
    document.getElementById('preview-description').textContent = description || 'Mô tả livestream';
    
    // Update selected products preview
    const previewProducts = document.getElementById('preview-products');
    if (selectedProducts.length > 0) {
        previewProducts.innerHTML = `
            <div class="mt-3">
                <h6>Sản phẩm sẽ bán (${selectedProducts.length}):</h6>
                <div class="d-flex flex-wrap gap-2">
                    ${selectedProducts.map(id => `<span class="badge badge-primary">Sản phẩm #${id}</span>`).join('')}
                </div>
            </div>
        `;
    } else {
        previewProducts.innerHTML = '<p class="text-muted">Chưa chọn sản phẩm nào</p>';
    }
}

function loadMoreProducts() {
    // Implementation for loading more products
    alert('Tính năng tải thêm sản phẩm đang được phát triển');
}

function formatMoney(amount) {
    return new Intl.NumberFormat('vi-VN').format(amount);
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('image-preview').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Form submission
document.getElementById('create-livestream-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Tạo FormData để xử lý file upload
    const formData = new FormData();
    formData.append('title', document.getElementById('livestream-title').value);
    formData.append('description', document.getElementById('livestream-description').value);
    formData.append('start_time', document.getElementById('start-time').value);
    formData.append('end_time', document.getElementById('end-time').value);
    formData.append('products', JSON.stringify(selectedProducts));
    
    // Xử lý upload ảnh
    const imageFile = document.getElementById('livestream-image').files[0];
    if (imageFile) {
        formData.append('image', imageFile);
    }
    
    fetch('api/create-livestream.php', {
        method: 'POST',
        body: formData  // Không cần Content-Type header khi dùng FormData
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        return response.text(); // Đọc response dưới dạng text trước
    })
    .then(text => {
        console.log('Raw response:', text);
        try {
            const data = JSON.parse(text);
            if (data.success) {
                const targetUrl = data.redirect_url || `index.php?streamer&id=${data.livestream_id}`;
                window.location.href = `${targetUrl}&toast=${encodeURIComponent('Tạo livestream thành công!')}&type=success`;
            } else {
                alert('Có lỗi xảy ra: ' + data.message);
            }
        } catch (e) {
            console.error('JSON parse error:', e);
            console.error('Response text:', text);
            alert('Lỗi phản hồi từ server: ' + text.substring(0, 100));
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('Có lỗi xảy ra khi tạo livestream: ' + error.message);
    });
});
</script>

<?php include_once("view/footer.php"); ?>
