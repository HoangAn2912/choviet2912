<?php
include_once("view/header.php");
include_once("controller/cLivestream.php");

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?login');
    exit;
}

$cLivestream = new cLivestream();
$livestream_id = $_GET['id'] ?? null;

if (!$livestream_id) {
    header('Location: index.php?quan-ly-tin');
    exit;
}

// Lấy thông tin livestream
include_once("model/mLivestream.php");
$mLivestream = new mLivestream();
$livestream = $mLivestream->getLivestreamById($livestream_id);

// Debug: Hiển thị thông tin debug
if (!$livestream) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border-radius: 5px;'>";
    echo "<h3>❌ Lỗi: Không tìm thấy livestream ID=$livestream_id</h3>";
    echo "<p>Vui lòng kiểm tra lại ID livestream.</p>";
    echo "<a href='index.php?my-livestreams' class='btn btn-primary'>Quay lại danh sách livestream</a>";
    echo "</div>";
    include_once("view/footer.php");
    exit;
}

if ($livestream['user_id'] != $_SESSION['user_id']) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border-radius: 5px;'>";
    echo "<h3>❌ Lỗi: Không có quyền truy cập</h3>";
    echo "<p>Bạn không có quyền truy cập livestream này.</p>";
    echo "<a href='index.php?my-livestreams' class='btn btn-primary'>Quay lại danh sách livestream</a>";
    echo "</div>";
    include_once("view/footer.php");
    exit;
}

// Lấy dữ liệu bổ sung
$products = $mLivestream->getLivestreamProducts($livestream_id);
$pinned_product = $mLivestream->getPinnedProduct($livestream_id);
$stats = $mLivestream->getLivestreamStats($livestream_id);
?>

<style>
.page-background {
    background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
    min-height: calc(100vh - 180px);
    padding: 0 2rem 2rem 2rem;
}

.content-wrapper {
    background: #ffffff;
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
    border-radius: 16px;
    box-shadow: 0 6px 30px rgba(0, 0, 0, 0.12);
}

.content-wrapper .container,
.content-wrapper .container-fluid {
    padding-left: 0 !important;
    padding-right: 0 !important;
}

@media (max-width: 768px) {
    .page-background {
        padding: 0 1rem 1rem 1rem;
    }
    .content-wrapper {
        padding: 1.5rem;
        border-radius: 12px;
    }
}

.streamer-panel {
    min-height: 100%;
    padding: 20px 0;
}

.panel-header {
    background: white;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.live-status {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 14px;
}

.status-live {
    background: #ff0000;
    color: white;
}

.status-pending {
    background: #ffc107;
    color: #333;
}

.status-ended {
    background: #6c757d;
    color: white;
}

.control-panel {
    background: white;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.panel-tabs {
    display: flex;
    border-bottom: 2px solid #e9ecef;
    margin-bottom: 20px;
}

.tab-btn {
    padding: 12px 24px;
    border: none;
    background: none;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    font-weight: 500;
    color: #6c757d;
    transition: all 0.3s;
}

.tab-btn.active {
    color: #007bff;
    border-bottom-color: #007bff;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.product-card {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s;
    cursor: pointer;
    position: relative; /* Để số thứ tự sản phẩm (product-number) bám đúng vào từng card, không trôi lên trên */
}

.product-card:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.product-card.pinned {
    border-color: #ffc107;
    box-shadow: 0 0 0 2px #ffc107;
}

.product-number {
    position: absolute;
    top: 10px;
    left: 10px;
    background: #ffc107;
    color: #333;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
    z-index: 1;
}

.product-list-item {
    display: flex;
    align-items: center;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.2s;
}

.product-list-item:hover {
    background: #f8f9fa;
    border-color: #ffc107;
}

.product-list-item.selected {
    background: #fff9e6;
    border-color: #ffc107;
}

.product-list-item img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 6px;
    margin-right: 15px;
}

.product-list-item .product-info {
    flex: 1;
}

.product-list-item .product-info h6 {
    margin: 0 0 5px 0;
    font-size: 14px;
}

.product-list-item .product-info p {
    margin: 0;
    color: #666;
    font-size: 12px;
}

.product-card img {
    width: 100%;
    height: 150px;
    object-fit: cover;
}

.product-info {
    padding: 15px;
}

.product-info h6 {
    margin: 0 0 8px 0;
    font-size: 16px;
    font-weight: 600;
}

.product-price {
    color: #28a745;
    font-weight: bold;
    font-size: 18px;
    margin-bottom: 10px;
}

.product-actions {
    display: flex;
    gap: 10px;
}

.btn-pin {
    background: #ffc107;
    color: #333;
    border: none;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    cursor: pointer;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: all 0.3s ease;
}

.btn-pin:hover {
    background: #e0a800;
    transform: scale(1.1);
}

.btn-pin.pinned {
    background: #dc3545;
    color: white;
}

.btn-pin.pinned:hover {
    background: #c82333;
}

.btn-remove {
    background: #dc3545;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 5px;
    cursor: pointer;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.stat-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.stat-number {
    font-size: 32px;
    font-weight: bold;
    color: #007bff;
    margin-bottom: 8px;
}

.stat-label {
    color: #6c757d;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.live-controls {
    display: flex;
    gap: 15px;
    align-items: center;
    margin-bottom: 20px;
}

.btn-live {
    background: #dc3545;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    font-size: 16px;
}

.btn-live.ended {
    background: #6c757d;
}

.btn-add-product {
    background: #28a745;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
}

.chat-messages {
    max-height: 300px;
    overflow-y: auto;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
}

.chat-message {
    margin-bottom: 10px;
    padding: 8px 12px;
    background: white;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}

.chat-message .username {
    font-weight: bold;
    color: #007bff;
    margin-right: 8px;
}

.chat-message .content {
    color: #333;
}

.chat-message .timestamp {
    font-size: 12px;
    color: #6c757d;
    float: right;
}

.pinned-product-display {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}

.pinned-product {
    display: flex;
    align-items: center;
    gap: 15px;
}

.pinned-product img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}

.pinned-product-info h5 {
    margin: 0 0 5px 0;
    color: #856404;
}

.pinned-product-info .price {
    color: #28a745;
    font-weight: bold;
    font-size: 18px;
}

/* Livestream icons đỏ trong panel quản lý */
.panel-header i.fas.fa-video,
.live-controls i.fas.fa-play,
.live-controls i.fas.fa-stop,
.live-controls i.fas.fa-broadcast-tower {
    color: #dc3545 !important;
}

@media (max-width: 768px) {
    .product-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .live-controls {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>

<div class="page-background">
    <div class="content-wrapper">
<div class="container-fluid streamer-panel">
    <div class="row">
        <div class="col-12">
            <!-- Panel Header -->
            <div class="panel-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-2">
                            <i class="fas fa-video text-primary mr-2"></i>
                            Quản lý Livestream
                        </h2>
                        <h4 class="mb-0"><?= htmlspecialchars($livestream['title']) ?></h4>
                    </div>
                    <div>
                        <span class="live-status status-<?= $livestream['status'] ?>">
                            <?php
                            switch($livestream['status']) {
                                case 'dang_dien_ra':
                                    echo '<i class="fas fa-circle mr-1"></i>Đang live';
                                    break;
                                case 'chua_bat_dau':
                                    echo '<i class="fas fa-clock mr-1"></i>Chưa bắt đầu';
                                    break;
                                case 'da_ket_thuc':
                                    echo '<i class="fas fa-stop mr-1"></i>Đã kết thúc';
                                    break;
                            }
                            ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Live Controls -->
            <div class="control-panel">
                <div class="live-controls">
                    <?php if ($livestream['status'] == 'chua_bat_dau'): ?>
                        <button class="btn-live" onclick="startLivestream()">
                            <i class="fas fa-play mr-2"></i>Bắt đầu Live
                        </button>
                    <?php elseif ($livestream['status'] == 'dang_dien_ra' || $livestream['status'] == 'dang_live'): ?>
                        <button class="btn-live ended" onclick="endLivestream()">
                            <i class="fas fa-stop mr-2"></i>Kết thúc Live
                        </button>
                        <a href="index.php?broadcast&id=<?= $livestream_id ?>" class="btn-live" style="margin-left: 10px;">
                            <i class="fas fa-broadcast-tower mr-2"></i>Quay lại Live
                        </a>
                    <?php elseif ($livestream['status'] == 'da_ket_thuc'): ?>
                        <button class="btn-live" onclick="startLivestream()">
                            <i class="fas fa-play mr-2"></i>Bắt đầu Live lại
                        </button>
                    <?php endif; ?>
                    
                    <button class="btn-add-product" onclick="showAddProductModal()">
                        <i class="fas fa-plus mr-2"></i>Thêm sản phẩm
                    </button>
                    
                    <a href="index.php?livestream&id=<?= $livestream['id'] ?>" class="btn btn-outline-primary">
                        <i class="fas fa-external-link-alt mr-2"></i>Xem Live
                    </a>
                </div>

                <!-- Pinned Product Display -->
                <?php if ($pinned_product): ?>
                <div class="pinned-product-display">
                    <h5><i class="fas fa-thumbtack text-warning mr-2"></i>Sản phẩm đang ghim</h5>
                    <div class="pinned-product">
                        <?php 
                        // Sử dụng ảnh đầu tiên đã xử lý từ mLivestream (anh_dau)
                        $pinnedImage = $pinned_product['anh_dau'] ?? 'default-product.jpg';
                        if (!file_exists('img/' . $pinnedImage)) {
                            $pinnedImage = 'default-product.jpg';
                        }
                        ?>
                        <img src="img/<?= htmlspecialchars($pinnedImage) ?>" alt="Sản phẩm">
                        <div class="pinned-product-info">
                            <h5><?= htmlspecialchars($pinned_product['title']) ?></h5>
                            <div class="price">
                                <?= number_format($pinned_product['special_price'] ?: $pinned_product['price']) ?> đ
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Panel Tabs -->
            <div class="control-panel">
                <div class="panel-tabs">
                    <button class="tab-btn active" onclick="showTab('products')">
                        <i class="fas fa-box mr-2"></i>Sản phẩm
                    </button>
                    <button class="tab-btn" onclick="showTab('analytics')">
                        <i class="fas fa-chart-bar mr-2"></i>Thống kê
                    </button>
                    <button class="tab-btn" onclick="showTab('settings')">
                        <i class="fas fa-cog mr-2"></i>Cài đặt
                    </button>
                </div>

                <!-- Tab Content -->
                <div class="tab-content active" id="products-tab">
                    <h5 class="mb-3">Sản phẩm đang bán</h5>
                    <div class="product-grid" id="product-grid">
                        <?php 
                        // Sắp xếp sản phẩm: ghim lên đầu, sau đó theo thứ tự
                        $pinned_products = array_filter($products, function($p) { return $p['is_pinned']; });
                        $unpinned_products = array_filter($products, function($p) { return !$p['is_pinned']; });
                        $sorted_products = array_merge($pinned_products, $unpinned_products);
                        $index = 1;
                        ?>
                        <?php foreach ($sorted_products as $product): ?>
                        <div class="product-card <?= $product['is_pinned'] ? 'pinned' : '' ?>" 
                             data-product-id="<?= $product['product_id'] ?>">
                            <div class="product-number"><?= $index++ ?></div>
                            <?php 
                            // Sử dụng ảnh đầu tiên đã xử lý từ mLivestream (anh_dau)
                            $productImage = $product['anh_dau'] ?? 'default-product.jpg';
                            if (!file_exists('img/' . $productImage)) {
                                $productImage = 'default-product.jpg';
                            }
                            ?>
                            <img src="img/<?= htmlspecialchars($productImage) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
                            <div class="product-info">
                                <h6><?= htmlspecialchars($product['title']) ?></h6>
                                <div class="product-price">
                                    <?= number_format($product['special_price'] ?: $product['price']) ?> đ
                                </div>
                                <div class="product-actions">
                                    <button class="btn-remove" onclick="removeProduct(<?= $product['product_id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php if (!$product['is_pinned']): ?>
                                    <button class="btn-pin" onclick="pinProduct(<?= $product['product_id'] ?>)">
                                        <i class="fas fa-thumbtack"></i>
                                    </button>
                                    <?php else: ?>
                                    <button class="btn-pin pinned" onclick="unpinProduct(<?= $product['product_id'] ?>)">
                                        <i class="fas fa-thumbtack"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="tab-content" id="analytics-tab">
                    <h5 class="mb-3">Thống kê livestream</h5>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number" id="viewer-count"><?= $stats['total_viewers'] ?? 0 ?></div>
                            <div class="stat-label">Người xem</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number" id="order-count"><?= $stats['total_orders'] ?? 0 ?></div>
                            <div class="stat-label">Đơn hàng</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number" id="revenue"><?= number_format($stats['total_revenue'] ?? 0) ?></div>
                            <div class="stat-label">Doanh thu (VNĐ)</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number" id="likes-count"><?= $stats['total_likes'] ?? 0 ?></div>
                            <div class="stat-label">Lượt thích</div>
                        </div>
                    </div>
                </div>

                <div class="tab-content" id="settings-tab">
                    <h5 class="mb-3">Cài đặt livestream</h5>
                    <form id="livestream-settings">
                        <div class="form-group">
                            <label for="livestream-title">Tiêu đề</label>
                            <input type="text" class="form-control" id="livestream-title" 
                                   value="<?= htmlspecialchars($livestream['title']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="livestream-description">Mô tả</label>
                            <textarea class="form-control" id="livestream-description" rows="3"><?= htmlspecialchars($livestream['description']) ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Cập nhật</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm sản phẩm vào livestream</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Danh sách sản phẩm của bạn</h6>
                        <div class="product-list" id="available-products" style="max-height: 400px; overflow-y: auto;">
                            <!-- Products will be loaded here -->
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Thông tin sản phẩm</h6>
                        <form id="add-product-form">
                            <input type="hidden" id="livestream-id" value="<?= $livestream['id'] ?>">
                            <input type="hidden" id="selected-product-id">
                            
                            <div class="selected-product-info" id="selected-product-info" style="display: none;">
                                <div class="product-preview">
                                    <img id="preview-image" src="" alt="Preview" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                                    <div class="product-details">
                                        <h6 id="preview-title"></h6>
                                        <p id="preview-price" class="text-muted"></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="special-price">Giá đặc biệt (để trống nếu dùng giá gốc)</label>
                                <input type="number" class="form-control" id="special-price" placeholder="Nhập giá đặc biệt">
                            </div>
                            <div class="form-group">
                                <label for="stock-quantity">Số lượng còn lại</label>
                                <input type="number" class="form-control" id="stock-quantity" placeholder="Nhập số lượng">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="addProduct()" id="add-product-btn" disabled>Thêm sản phẩm</button>
            </div>
        </div>
    </div>
            </div>
        </div>
    </div>
</div>

<script>
const livestreamId = <?= $livestream['id'] ?>;
const userId = <?= $_SESSION['user_id'] ?>;

// WebSocket connection
let liveSocket = null;

document.addEventListener('DOMContentLoaded', function() {
    connectWebSocket();
    loadProducts();
    loadChatMessages();
    
    // Cập nhật thống kê mỗi 10 giây
    setInterval(updateStats, 10000);

    // Cập nhật tiêu đề & mô tả livestream trong tab Cài đặt
    const settingsForm = document.getElementById('livestream-settings');
    if (settingsForm) {
        settingsForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const titleInput = document.getElementById('livestream-title');
            const descInput = document.getElementById('livestream-description');

            const title = (titleInput?.value || '').trim();
            const description = (descInput?.value || '').trim();

            if (!title) {
                if (typeof showToast === 'function') {
                    showToast('Tiêu đề không được để trống', 'error');
                } else {
                    alert('Tiêu đề không được để trống');
                }
                return;
            }

            fetch('api/livestream-api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_livestream_info&livestream_id=${encodeURIComponent(livestreamId)}&title=${encodeURIComponent(title)}&description=${encodeURIComponent(description)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (typeof showToast === 'function') {
                        showToast(data.message || 'Cập nhật cài đặt livestream thành công', 'success');
                    }
                    // Cập nhật lại tiêu đề & mô tả hiển thị ở phần thông tin chính
                    const headerTitle = document.querySelector('.stream-info h2');
                    const headerDesc = document.querySelector('.stream-info p.text-muted');
                    if (headerTitle) headerTitle.textContent = title;
                    if (headerDesc) headerDesc.textContent = description;
                } else {
                    if (typeof showToast === 'function') {
                        showToast(data.message || 'Cập nhật thất bại', 'error');
                    } else {
                        alert(data.message || 'Cập nhật thất bại');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof showToast === 'function') {
                    showToast('Lỗi kết nối: ' + error.message, 'error');
                } else {
                    alert('Lỗi kết nối: ' + error.message);
                }
            });
        });
    }
});

function connectWebSocket() {
    const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
    const wsUrl = `${protocol}//${window.location.hostname}:3000/livestream`;
    
    liveSocket = new WebSocket(wsUrl);
    
    liveSocket.onopen = function() {
        console.log('Connected to livestream WebSocket');
        liveSocket.send(JSON.stringify({
            type: 'join_livestream',
            livestream_id: livestreamId,
            user_id: userId
        }));
    };
    
    liveSocket.onmessage = function(event) {
        const data = JSON.parse(event.data);
        handleWebSocketMessage(data);
    };
}

function handleWebSocketMessage(data) {
    switch(data.type) {
        case 'chat_message':
            addChatMessage(data);
            break;
        case 'product_pinned':
            updatePinnedProduct(data.product);
            break;
        case 'viewer_count':
            updateViewerCount(data.count);
            break;
        case 'order_placed':
            updateOrderCount();
            break;
    }
}

function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.add('active');
    event.target.classList.add('active');
}

function startLivestream() {
    if (confirm('Bắt đầu livestream? Bạn sẽ được chuyển đến trang phát sóng.')) {
        fetch('api/livestream-api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update_status&livestream_id=${livestreamId}&status=dang_dien_ra`
        })
        .then(response => response.json())
        .then(data => {
            console.log('API Response:', data);
            if (data.success) {
                // Chuyển hướng đến trang broadcast
                window.location.href = `index.php?broadcast&id=${livestreamId}`;
            } else {
                alert('Lỗi: ' + (data.message || 'Không thể cập nhật trạng thái'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Lỗi kết nối: ' + error.message);
        });
    }
}

function endLivestream() {
    if (confirm('Kết thúc livestream?')) {
        fetch('controller/cLivestream.php?action=toggle_status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `livestream_id=${livestreamId}&status=da_ket_thuc`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }
}

function pinProduct(productId) {
    fetch('controller/cLivestream.php?action=pin_product', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `livestream_id=${livestreamId}&product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    });
}

function removeProduct(productId) {
    if (confirm('Xóa sản phẩm khỏi livestream?')) {
        fetch('api/livestream-api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=remove_product&livestream_id=${livestreamId}&product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Lỗi kết nối: ' + error.message);
        });
    }
}

function showAddProductModal() {
    loadProducts();
    $('#addProductModal').modal('show');
}

function unpinProduct(productId) {
    if (confirm('Bỏ ghim sản phẩm này?')) {
        fetch('api/livestream-api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=unpin_product&livestream_id=${livestreamId}&product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Lỗi kết nối: ' + error.message);
        });
    }
}

function addProduct() {
    const productId = document.getElementById('selected-product-id').value;
    const specialPrice = document.getElementById('special-price').value;
    const stockQuantity = document.getElementById('stock-quantity').value;
    
    if (!productId) {
        alert('Vui lòng chọn sản phẩm');
        return;
    }
    
    fetch('api/livestream-api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add_product&livestream_id=${livestreamId}&product_id=${productId}&special_price=${specialPrice}&stock_quantity=${stockQuantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Có lỗi xảy ra');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Lỗi kết nối: ' + error.message);
    });
}

function loadProducts() {
    fetch('api/livestream-api.php?action=get_available_products')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const container = document.getElementById('available-products');
            container.innerHTML = '';
            
            data.products.forEach(product => {
                const productItem = document.createElement('div');
                productItem.className = 'product-list-item';
                productItem.onclick = () => selectProduct(product);
                
                productItem.innerHTML = `
                    <img src="img/${product.anh_dau || 'default-product.jpg'}" alt="${product.title}">
                    <div class="product-info">
                        <h6>${product.title}</h6>
                        <p>${new Intl.NumberFormat('vi-VN').format(product.price)} đ</p>
                    </div>
                `;
                
                container.appendChild(productItem);
            });
        }
    })
    .catch(error => {
        console.error('Error loading products:', error);
    });
}

function selectProduct(product) {
    // Remove previous selection
    document.querySelectorAll('.product-list-item').forEach(item => {
        item.classList.remove('selected');
    });
    
    // Add selection to clicked item
    event.currentTarget.classList.add('selected');
    
    // Update form
    document.getElementById('selected-product-id').value = product.id;
    document.getElementById('preview-image').src = `img/${product.anh_dau || 'default-product.jpg'}`;
    document.getElementById('preview-title').textContent = product.title;
    document.getElementById('preview-price').textContent = new Intl.NumberFormat('vi-VN').format(product.price) + ' đ';
    document.getElementById('selected-product-info').style.display = 'block';
    document.getElementById('add-product-btn').disabled = false;
}

function loadChatMessages() {
    fetch(`api/livestream-api.php?action=get_chat_messages&livestream_id=${livestreamId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const chatMessages = document.getElementById('chat-messages');
            chatMessages.innerHTML = '';
            
            data.messages.forEach(message => {
                addChatMessage(message);
            });
        }
    });
}

function addChatMessage(data) {
    const chatMessages = document.getElementById('chat-messages');
    const messageDiv = document.createElement('div');
    messageDiv.className = 'chat-message';
    messageDiv.innerHTML = `
        <span class="username">${data.username}:</span>
        <span class="content">${data.content}</span>
        <span class="timestamp">${new Date(data.created_time).toLocaleTimeString()}</span>
    `;
    
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function updateStats() {
    fetch(`api/livestream-api.php?action=get_livestream&id=${livestreamId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateViewerCount(data.stats.total_viewers);
            updateOrderCount(data.stats.total_orders);
            updateRevenue(data.stats.total_revenue);
        }
    });
}

function updateViewerCount(count) {
    document.getElementById('viewer-count').textContent = count;
}

function updateOrderCount(count) {
    if (count !== undefined) {
        document.getElementById('order-count').textContent = count;
    }
}

function updateRevenue(revenue) {
    if (revenue !== undefined) {
        document.getElementById('revenue').textContent = new Intl.NumberFormat('vi-VN').format(revenue);
    }
}
</script>

<?php include_once("view/footer.php"); ?>



