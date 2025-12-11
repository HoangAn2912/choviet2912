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
    echo "<h3>Lỗi: Không tìm thấy livestream ID=$livestream_id</h3>";
    echo "<p>Vui lòng kiểm tra lại ID livestream.</p>";
    echo "<a href='index.php?my-livestreams' class='btn btn-primary'>Quay lại danh sách livestream</a>";
    echo "</div>";
    include_once("view/footer.php");
    exit;
}

if ($livestream['user_id'] != $_SESSION['user_id']) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border-radius: 5px;'>";
    echo "<h3>Lỗi: Không có quyền truy cập</h3>";
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

// Lấy danh sách đơn hàng từ livestream này
include_once("model/mQLdonhang.php");
$mQLdonhang = new mQLdonhang();
$livestream_orders = $mQLdonhang->getAllOrders(null, $livestream_id, null, null, null, 100, 0);
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

/* Badge styles cho đơn hàng */
.badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.badge-warning {
    background: #ffc107;
    color: #333;
}

.badge-info {
    background: #17a2b8;
    color: white;
}

.badge-primary {
    background: #007bff;
    color: white;
}

.badge-success {
    background: #28a745;
    color: white;
}

.badge-danger {
    background: #dc3545;
    color: white;
}

.badge-secondary {
    background: #6c757d;
    color: white;
}

/* Table styles cho đơn hàng */
.table-responsive {
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.table-responsive::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #ffe139ff 0%, #ffaa0cff 100%);
    border-radius: 10px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #ffd700 0%, #ff9500 100%);
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
                    
                    <!-- Danh sách đơn hàng -->
                    <div class="mt-4">
                        <h5 class="mb-3">
                            <i class="fas fa-shopping-cart mr-2"></i>
                            Đơn hàng khách đã chốt (<?= count($livestream_orders) ?>)
                        </h5>
                        
                        <?php if (empty($livestream_orders)): ?>
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle mr-2"></i>
                                Chưa có đơn hàng nào từ livestream này
                            </div>
                        <?php else: ?>
                            <div class="table-responsive" style="max-height: <?= count($livestream_orders) > 10 ? '600px' : 'auto' ?>; overflow-y: <?= count($livestream_orders) > 10 ? 'auto' : 'visible' ?>;">
                                <table class="table table-hover" style="background: white; border-radius: 10px; overflow: hidden;">
                                    <thead style="background: linear-gradient(135deg, #ffe139ff 0%, #ffaa0cff 100%); color: #000;">
                                        <tr>
                                            <th style="padding: 12px; font-weight: 600; color: #000;">Mã đơn</th>
                                            <th style="padding: 12px; font-weight: 600; color: #000;">Người mua</th>
                                            <th style="padding: 12px; font-weight: 600; color: #000;">SĐT</th>
                                            <th style="padding: 12px; font-weight: 600; color: #000;">Email</th>
                                            <th style="padding: 12px; font-weight: 600; color: #000;">Số lượng SP</th>
                                            <th style="padding: 12px; font-weight: 600; color: #000;">Tổng tiền</th>
                                            <th style="padding: 12px; font-weight: 600; color: #000;">Trạng thái</th>
                                            <th style="padding: 12px; font-weight: 600; color: #000;">Ngày đặt</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($livestream_orders as $order): 
                                            // Xác định màu và text cho trạng thái
                                            $statusClass = '';
                                            $statusText = '';
                                            switch($order['status']) {
                                                case 'pending':
                                                    $statusClass = 'warning';
                                                    $statusText = 'Chờ xác nhận';
                                                    break;
                                                case 'confirmed':
                                                    $statusClass = 'info';
                                                    $statusText = 'Đã xác nhận';
                                                    break;
                                                case 'shipping':
                                                    $statusClass = 'primary';
                                                    $statusText = 'Đang giao';
                                                    break;
                                                case 'delivered':
                                                    $statusClass = 'success';
                                                    $statusText = 'Đã giao';
                                                    break;
                                                case 'cancelled':
                                                    $statusClass = 'danger';
                                                    $statusText = 'Đã hủy';
                                                    break;
                                                default:
                                                    $statusClass = 'secondary';
                                                    $statusText = $order['status'];
                                            }
                                        ?>
                                        <tr style="border-bottom: 1px solid #e9ecef; cursor: pointer;" onclick="showOrderDetail(<?= htmlspecialchars(json_encode($order)) ?>)">
                                            <td style="padding: 12px;">
                                                <strong style="color: #2196F3;">#<?= htmlspecialchars($order['order_code']) ?></strong>
                                            </td>
                                            <td style="padding: 12px;">
                                                <div>
                                                    <strong><?= htmlspecialchars($order['buyer_name'] ?? 'N/A') ?></strong>
                                                </div>
                                            </td>
                                            <td style="padding: 12px;">
                                                <?= htmlspecialchars($order['buyer_phone'] ?? $order['delivery_phone'] ?? 'N/A') ?>
                                            </td>
                                            <td style="padding: 12px;">
                                                <small><?= htmlspecialchars($order['buyer_email'] ?? 'N/A') ?></small>
                                            </td>
                                            <td style="padding: 12px; text-align: center;">
                                                <strong style="color: #007bff;">
                                                    <?= $order['total_quantity'] ?? 0 ?>
                                                </strong>
                                            </td>
                                            <td style="padding: 12px;">
                                                <strong style="color: #28a745;">
                                                    <?= number_format($order['total_amount'], 0, ',', '.') ?> đ
                                                </strong>
                                            </td>
                                            <td style="padding: 12px;">
                                                <span class="badge badge-<?= $statusClass ?>" style="padding: 6px 12px; border-radius: 20px; font-size: 0.85rem;">
                                                    <?= $statusText ?>
                                                </span>
                                            </td>
                                            <td style="padding: 12px; color: #666; font-size: 0.9rem;">
                                                <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
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
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm sản phẩm vào livestream</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Đóng" onclick="hideAddProductModal()">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6>Danh sách sản phẩm của bạn</h6>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllProducts()">Chọn tất cả</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllProducts()">Bỏ chọn</button>
                            </div>
                        </div>
                        <div class="product-list" id="available-products" style="max-height: 400px; overflow-y: auto;">
                            <!-- Products will be loaded here -->
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Sản phẩm đã chọn (<span id="selected-count">0</span>)</h6>
                        <div id="selected-products-list" style="max-height: 400px; overflow-y: auto;">
                            <div class="text-center text-muted py-3">Chưa chọn sản phẩm nào</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="hideAddProductModal()">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="addProduct()" id="add-product-btn" disabled>Thêm/Cập nhật sản phẩm</button>
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

const addProductModalControl = {
    backdropEl: null,
    manuallyOpen: false,
    scrollBarPadding: ''
};

function canUseBootstrapModal() {
    return typeof window.jQuery !== 'undefined'
        && typeof window.jQuery.fn !== 'undefined'
        && typeof window.jQuery.fn.modal === 'function';
}

function showAddProductModal() {
    const modal = document.getElementById('addProductModal');
    if (!modal) {
        return;
    }

    // Reset danh sách sản phẩm đã chọn khi mở modal
    selectedProducts = {};
    updateSelectedProductsList();
    updateSelectedCount();
    updateAddButton();
    
    loadProducts();

    if (canUseBootstrapModal()) {
        window.jQuery('#addProductModal').modal('show');
        return;
    }

    modal.classList.add('show');
    modal.style.display = 'block';
    modal.setAttribute('aria-hidden', 'false');
    modal.setAttribute('aria-modal', 'true');
    addProductModalControl.manuallyOpen = true;
    if (typeof window.innerWidth === 'number') {
        const scrollBarWidth = window.innerWidth - document.documentElement.clientWidth;
        if (scrollBarWidth > 0) {
            addProductModalControl.scrollBarPadding = document.body.style.paddingRight;
            document.body.style.paddingRight = scrollBarWidth + 'px';
        }
    }
    document.body.classList.add('modal-open');

    if (!addProductModalControl.backdropEl) {
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.addEventListener('click', hideAddProductModal);
        addProductModalControl.backdropEl = backdrop;
    }

    document.body.appendChild(addProductModalControl.backdropEl);
}

function hideAddProductModal() {
    const modal = document.getElementById('addProductModal');
    if (!modal) {
        return;
    }

    if (canUseBootstrapModal()) {
        window.jQuery('#addProductModal').modal('hide');
        return;
    }

    if (!addProductModalControl.manuallyOpen) {
        return;
    }

    modal.classList.remove('show');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    modal.removeAttribute('aria-modal');
    addProductModalControl.manuallyOpen = false;
    document.body.classList.remove('modal-open');
    if (addProductModalControl.scrollBarPadding !== '') {
        document.body.style.paddingRight = addProductModalControl.scrollBarPadding;
        addProductModalControl.scrollBarPadding = '';
    } else {
        document.body.style.paddingRight = '';
    }

    if (addProductModalControl.backdropEl && addProductModalControl.backdropEl.parentNode) {
        addProductModalControl.backdropEl.parentNode.removeChild(addProductModalControl.backdropEl);
    }
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && addProductModalControl.manuallyOpen) {
        hideAddProductModal();
    }
});

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

// Thêm/Cập nhật nhiều sản phẩm cùng lúc
function addProduct() {
    const selectedIds = Object.keys(selectedProducts);
    
    if (selectedIds.length === 0) {
        alert('Vui lòng chọn ít nhất một sản phẩm');
        return;
    }
    
    // Validate tất cả sản phẩm đã chọn
    for (const productId of selectedIds) {
        const selectedData = selectedProducts[productId];
        const stockQuantity = selectedData.stockQuantity;
        const specialPrice = selectedData.specialPrice;
        
        // Validate số lượng >= 0
        if (stockQuantity !== '' && stockQuantity !== null && (isNaN(stockQuantity) || parseInt(stockQuantity) < 0)) {
            alert(`Sản phẩm "${selectedData.product.title}": Số lượng phải là số lớn hơn hoặc bằng 0`);
            return;
        }
        
        // Validate giá > 0 nếu có nhập
        if (specialPrice !== '' && specialPrice !== null && (isNaN(specialPrice) || parseFloat(specialPrice) <= 0)) {
            alert(`Sản phẩm "${selectedData.product.title}": Giá đặc biệt phải là số lớn hơn 0`);
            return;
        }
    }
    
    // Lấy CSRF token
    let csrfToken = '';
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (csrfMeta) {
        csrfToken = csrfMeta.getAttribute('content');
    }
    
    // Chuẩn bị dữ liệu để gửi
    const productsData = selectedIds.map(productId => {
        const selectedData = selectedProducts[productId];
        return {
            product_id: productId,
            special_price: selectedData.specialPrice || '',
            stock_quantity: selectedData.stockQuantity || ''
        };
    });
    
    // Gửi request batch update
    fetch('api/livestream-api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'batch_update_products',
            livestream_id: livestreamId,
            products: productsData,
            csrf_token: csrfToken
        })
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                console.error('Response is not JSON:', text);
                throw new Error('Server trả về dữ liệu không hợp lệ. Vui lòng thử lại.');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Xóa danh sách đã chọn
            selectedProducts = {};
            updateSelectedProductsList();
            updateSelectedCount();
            updateAddButton();
            
            // Reload danh sách sản phẩm
            loadProducts();
            
            // Reload trang để cập nhật danh sách sản phẩm trong livestream
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

// Lưu danh sách sản phẩm đã chọn
let selectedProducts = {}; // {productId: {product, specialPrice, stockQuantity}}
// Lưu tất cả sản phẩm để có thể truy cập khi toggle
let allProductsData = [];

function loadProducts() {
    fetch(`api/livestream-api.php?action=get_available_products&livestream_id=${livestreamId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const container = document.getElementById('available-products');
            container.innerHTML = '';
            
            if (data.products.length === 0) {
                container.innerHTML = '<div class="text-center text-muted">Chưa có sản phẩm nào</div>';
                return;
            }
            
            // Lưu tất cả sản phẩm để có thể truy cập sau
            allProductsData = data.products;
            
            data.products.forEach(product => {
                const productItem = document.createElement('div');
                productItem.className = 'product-list-item';
                productItem.setAttribute('data-product-id', product.id);
                
                // Kiểm tra xem sản phẩm đã được chọn chưa
                const isSelected = selectedProducts[product.id] !== undefined;
                
                // Hiển thị badge nếu sản phẩm đã có trong livestream
                const badgeHtml = product.is_in_livestream 
                    ? '<span class="badge badge-warning" style="position: absolute; top: 5px; right: 5px; background: #ffd700; color: #000; padding: 2px 6px; border-radius: 3px; font-size: 10px;">Đã có</span>'
                    : '';
                
                // Hiển thị giá: ưu tiên giá đặc biệt trong livestream, sau đó giá gốc
                const displayPrice = product.is_in_livestream && product.livestream_special_price 
                    ? product.livestream_special_price 
                    : product.price;
                
                productItem.innerHTML = `
                    <div style="display: flex; align-items: center; width: 100%;">
                        <input type="checkbox" class="product-checkbox" data-product-id="${product.id}" 
                               ${isSelected ? 'checked' : ''} 
                               onchange="toggleProductSelection(${product.id}, this.checked)" 
                               onclick="event.stopPropagation();" 
                               style="margin-right: 10px; width: 18px; height: 18px; cursor: pointer;">
                        <div style="position: relative; flex: 1;">
                            ${badgeHtml}
                            <img src="img/${product.anh_dau || 'default-product.jpg'}" alt="${product.title}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px; margin-right: 15px;">
                        </div>
                        <div class="product-info" style="flex: 1;">
                            <h6>${product.title}</h6>
                            <p>${new Intl.NumberFormat('vi-VN').format(displayPrice)} đ</p>
                            ${product.is_in_livestream && product.livestream_stock_quantity !== null 
                                ? `<small class="text-muted">Còn lại: ${product.livestream_stock_quantity}</small>` 
                                : ''}
                        </div>
                    </div>
                `;
                
                if (isSelected) {
                    productItem.classList.add('selected');
                }
                
                container.appendChild(productItem);
            });
        }
    })
    .catch(error => {
        console.error('Error loading products:', error);
    });
}

// Toggle chọn/bỏ chọn sản phẩm
function toggleProductSelection(productId, isChecked) {
    const product = allProductsData.find(p => p.id == productId);
    if (!product) return;
    
    if (isChecked) {
        // Thêm sản phẩm vào danh sách đã chọn
        selectedProducts[productId] = {
            product: product,
            specialPrice: product.is_in_livestream ? (product.livestream_special_price || '') : '',
            stockQuantity: product.is_in_livestream ? (product.livestream_stock_quantity || '') : ''
        };
    } else {
        // Xóa sản phẩm khỏi danh sách đã chọn
        delete selectedProducts[productId];
    }
    
    // Cập nhật UI
    updateSelectedProductsList();
    updateSelectedCount();
    updateAddButton();
}

// Hiển thị danh sách sản phẩm đã chọn
function updateSelectedProductsList() {
    const container = document.getElementById('selected-products-list');
    const selectedIds = Object.keys(selectedProducts);
    
    if (selectedIds.length === 0) {
        container.innerHTML = '<div class="text-center text-muted py-3">Chưa chọn sản phẩm nào</div>';
        return;
    }
    
    container.innerHTML = '';
    
    selectedIds.forEach(productId => {
        const selectedData = selectedProducts[productId];
        const product = selectedData.product;
        const isInLivestream = product.is_in_livestream;
        
        const itemDiv = document.createElement('div');
        itemDiv.className = 'selected-product-item';
        itemDiv.style.cssText = 'border: 1px solid #ddd; border-radius: 8px; padding: 12px; margin-bottom: 10px; background: #f9f9f9;';
        
        itemDiv.innerHTML = `
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="d-flex align-items-center" style="flex: 1;">
                    <img src="img/${product.anh_dau || 'default-product.jpg'}" alt="${product.title}" 
                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px; margin-right: 10px;">
                    <div style="flex: 1;">
                        <h6 style="margin: 0; font-size: 14px;">${product.title}</h6>
                        <small class="text-muted">Giá gốc: ${new Intl.NumberFormat('vi-VN').format(product.price)} đ</small>
                        ${isInLivestream ? '<br><span class="badge badge-warning" style="font-size: 10px;">Đã có trong livestream</span>' : ''}
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="toggleProductSelection(${productId}, false)" style="margin-left: 10px;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="row">
                <div class="col-6">
                    <label style="font-size: 12px; color: #000; margin-bottom: 4px;">Giá đặc biệt</label>
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control form-control-sm product-special-price" 
                               data-product-id="${productId}" 
                               value="${selectedData.specialPrice}" 
                               placeholder="Để trống = giá gốc"
                               onchange="updateSelectedProductPrice(${productId}, this.value)">
                        ${isInLivestream && selectedData.specialPrice ? 
                            `<div class="input-group-append">
                                <button type="button" class="btn btn-sm" 
                                        style="background-color: #e0e0e0; color: #000; border: 1px solid #000; padding: 4px 8px;"
                                        onmouseover="this.style.backgroundColor='#b0b0b0'" 
                                        onmouseout="this.style.backgroundColor='#e0e0e0'"
                                        onclick="resetProductPrice(${productId})">
                                    <i class="fas fa-undo" style="font-size: 10px;"></i>
                                </button>
                            </div>` : ''}
                    </div>
                </div>
                <div class="col-6">
                    <label style="font-size: 12px; color: #000; margin-bottom: 4px;">Số lượng</label>
                    <input type="number" class="form-control form-control-sm product-stock-quantity" 
                           data-product-id="${productId}" 
                           value="${selectedData.stockQuantity}" 
                           placeholder="Nhập số lượng"
                           onchange="updateSelectedProductQuantity(${productId}, this.value)">
                </div>
            </div>
        `;
        
        container.appendChild(itemDiv);
    });
}

// Cập nhật giá của sản phẩm đã chọn
function updateSelectedProductPrice(productId, price) {
    if (selectedProducts[productId]) {
        selectedProducts[productId].specialPrice = price;
    }
}

// Cập nhật số lượng của sản phẩm đã chọn
function updateSelectedProductQuantity(productId, quantity) {
    if (selectedProducts[productId]) {
        selectedProducts[productId].stockQuantity = quantity;
    }
}

// Reset giá về giá gốc cho một sản phẩm
function resetProductPrice(productId) {
    if (selectedProducts[productId]) {
        selectedProducts[productId].specialPrice = '';
        updateSelectedProductsList();
    }
}

// Cập nhật số lượng sản phẩm đã chọn
function updateSelectedCount() {
    const count = Object.keys(selectedProducts).length;
    document.getElementById('selected-count').textContent = count;
}

// Cập nhật trạng thái nút thêm/cập nhật
function updateAddButton() {
    const btn = document.getElementById('add-product-btn');
    const count = Object.keys(selectedProducts).length;
    
    if (count > 0) {
        btn.disabled = false;
        const hasExistingProducts = Object.values(selectedProducts).some(data => data.product.is_in_livestream);
        if (hasExistingProducts) {
            btn.textContent = `Cập nhật ${count} sản phẩm`;
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-warning');
        } else {
            btn.textContent = `Thêm ${count} sản phẩm`;
            btn.classList.remove('btn-warning');
            btn.classList.add('btn-primary');
        }
    } else {
        btn.disabled = true;
        btn.textContent = 'Thêm/Cập nhật sản phẩm';
    }
}

// Chọn tất cả sản phẩm
function selectAllProducts() {
    allProductsData.forEach(product => {
        if (!selectedProducts[product.id]) {
            toggleProductSelection(product.id, true);
        }
    });
    // Cập nhật checkbox
    document.querySelectorAll('.product-checkbox').forEach(cb => {
        cb.checked = true;
    });
}

// Bỏ chọn tất cả sản phẩm
function deselectAllProducts() {
    selectedProducts = {};
    updateSelectedProductsList();
    updateSelectedCount();
    updateAddButton();
    // Cập nhật checkbox
    document.querySelectorAll('.product-checkbox').forEach(cb => {
        cb.checked = false;
    });
    // Cập nhật class selected
    document.querySelectorAll('.product-list-item').forEach(item => {
        item.classList.remove('selected');
    });
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

<!-- Modal Chi tiết đơn hàng -->
<div id="orderDetailModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); overflow-y:auto; z-index:1050;">
    <div class="modal-content p-4 rounded" style="background:white; width:700px; margin:50px auto; position:relative; max-width:90%;">
        <button onclick="closeOrderDetail()" class="btn btn-link p-0" style="position:absolute; top:10px; right:10px; font-size:22px; color:#333;">
            <i class="fas fa-times"></i>
        </button>
        
        <h4 class="font-weight-bold mb-3">
            <i class="fas fa-shopping-cart mr-2"></i>
            Chi tiết đơn hàng
        </h4>
        
        <div id="orderDetailContent">
            <!-- Nội dung sẽ được điền bằng JavaScript -->
        </div>
    </div>
</div>

<script>
let currentOrderDetail = null;

function showOrderDetail(order) {
    currentOrderDetail = order;
    const modal = document.getElementById('orderDetailModal');
    const content = document.getElementById('orderDetailContent');
    
    // Xác định trạng thái
    let statusClass = '';
    let statusText = '';
    switch(order.status) {
        case 'pending':
            statusClass = 'warning';
            statusText = 'Chờ xác nhận';
            break;
        case 'confirmed':
            statusClass = 'info';
            statusText = 'Đã xác nhận';
            break;
        case 'shipping':
            statusClass = 'primary';
            statusText = 'Đang giao';
            break;
        case 'delivered':
            statusClass = 'success';
            statusText = 'Đã giao';
            break;
        case 'cancelled':
            statusClass = 'danger';
            statusText = 'Đã hủy';
            break;
        default:
            statusClass = 'secondary';
            statusText = order.status;
    }
    
    // Format địa chỉ
    const addressParts = [];
    if (order.delivery_street) addressParts.push(order.delivery_street);
    if (order.delivery_ward) addressParts.push(order.delivery_ward);
    if (order.delivery_district) addressParts.push(order.delivery_district);
    if (order.delivery_province) addressParts.push(order.delivery_province);
    const fullAddress = addressParts.length > 0 ? addressParts.join(', ') : (order.delivery_address || 'N/A');
    
    content.innerHTML = `
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-1">Mã đơn: <strong style="color: #2196F3;">#${order.order_code}</strong></h5>
                    <span class="badge badge-${statusClass}" style="padding: 6px 12px; border-radius: 20px; font-size: 0.9rem;">
                        ${statusText}
                    </span>
                </div>
                <div class="text-right">
                    <strong style="color: #28a745; font-size: 1.2rem;">
                        ${new Intl.NumberFormat('vi-VN').format(order.total_amount)} đ
                    </strong>
                </div>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="card p-3" style="background: #f8f9fa; border-radius: 8px;">
                    <h6 class="font-weight-bold mb-2">
                        <i class="fas fa-user mr-2" style="color: #007bff;"></i>
                        Người mua
                    </h6>
                    <p class="mb-1"><strong>Tên:</strong> ${order.buyer_name || 'N/A'}</p>
                    <p class="mb-1"><strong>SĐT:</strong> ${order.buyer_phone || order.delivery_phone || 'N/A'}</p>
                    <p class="mb-0"><strong>Email:</strong> ${order.buyer_email || 'N/A'}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-3" style="background: #f8f9fa; border-radius: 8px;">
                    <h6 class="font-weight-bold mb-2">
                        <i class="fas fa-store mr-2" style="color: #ffc107;"></i>
                        Người bán
                    </h6>
                    <p class="mb-1"><strong>Tên:</strong> ${order.seller_name || 'N/A'}</p>
                    <p class="mb-0"><strong>ID:</strong> ${order.seller_id || 'N/A'}</p>
                </div>
            </div>
        </div>
        
        <div class="card p-3 mb-3" style="background: #f8f9fa; border-radius: 8px;">
            <h6 class="font-weight-bold mb-2">
                <i class="fas fa-video mr-2" style="color: #dc3545;"></i>
                Phiên Livestream
            </h6>
            <p class="mb-0"><strong>${order.livestream_title || 'N/A'}</strong></p>
        </div>
        
        <div class="card p-3 mb-3" style="background: #f8f9fa; border-radius: 8px;">
            <h6 class="font-weight-bold mb-2">
                <i class="fas fa-map-marker-alt mr-2" style="color: #28a745;"></i>
                Địa chỉ giao hàng
            </h6>
            <p class="mb-1"><strong>Người nhận:</strong> ${order.delivery_name || 'N/A'}</p>
            <p class="mb-1"><strong>SĐT:</strong> ${order.delivery_phone || 'N/A'}</p>
            <p class="mb-0"><strong>Địa chỉ:</strong> ${fullAddress}</p>
        </div>
        
        <div class="card p-3 mb-3" style="background: #f8f9fa; border-radius: 8px;">
            <h6 class="font-weight-bold mb-2">
                <i class="fas fa-info-circle mr-2" style="color: #17a2b8;"></i>
                Thông tin đơn hàng
            </h6>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Số lượng sản phẩm:</strong> ${order.total_quantity || 0}</p>
                    <p class="mb-1"><strong>Phương thức thanh toán:</strong> ${order.payment_method || 'N/A'}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Ngày đặt:</strong> ${new Date(order.created_at).toLocaleString('vi-VN')}</p>
                    <p class="mb-0"><strong>Ngày cập nhật:</strong> ${new Date(order.updated_at).toLocaleString('vi-VN')}</p>
                </div>
            </div>
        </div>
    `;
    
    modal.style.display = 'block';
}

function closeOrderDetail() {
    document.getElementById('orderDetailModal').style.display = 'none';
    currentOrderDetail = null;
}

// Đóng modal khi click bên ngoài
window.onclick = function(event) {
    const modal = document.getElementById('orderDetailModal');
    if (event.target == modal) {
        closeOrderDetail();
    }
}
</script>

<?php include_once("view/footer.php"); ?>



