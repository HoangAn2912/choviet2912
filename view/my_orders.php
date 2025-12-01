<?php
// Session đã được start trong index.php, không cần start lại

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?login');
    exit;
}

include_once("model/mLivestream.php");
include_once("model/mUser.php");

$model = new mLivestream();
$mUser = new mUser();

// Lấy thông tin user
$user = $mUser->getUserById($_SESSION['user_id']);

// Lấy danh sách đơn hàng
$status_filter = $_GET['status'] ?? null;
$orders = $model->getUserOrders($_SESSION['user_id'], $status_filter);


include_once("header.php");
?>

<style>
        /* Page Background - Gradient nhẹ */
        .page-background {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            min-height: calc(100vh - 180px);
            padding: 0 2rem 2rem 2rem;
        }

        /* Content wrapper - Khối trắng bên trong */
        .content-wrapper {
            background: #ffffff;
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 6px 30px rgba(0, 0, 0, 0.12);
        }
        
        /* Bỏ padding của container bên trong content-wrapper */
        .content-wrapper .container,
        .content-wrapper .container-fluid {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

.orders-container {
    background: transparent;
    min-height: auto;
    padding: 0;
}

.orders-header {
    background: linear-gradient(135deg, #ffc107, #ff8f00);
    color: #333;
    padding: 20px 0;
    margin-bottom: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
}

.orders-header h1 {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0;
    color: #333;
}

.orders-header p {
    font-size: 1rem;
    margin: 5px 0 0 0;
    color: #555;
}

.order-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 1px solid #e0e0e0;
}

.order-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

.order-header {
    background: linear-gradient(135deg, #ffc107, #ff8f00);
    color: #333;
    padding: 12px 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.order-info h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
}

.order-info p {
    margin: 3px 0 0 0;
    color: #555;
    font-size: 0.85rem;
}

.order-status {
    display: flex;
    align-items: center;
    gap: 10px;
}

.status-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.cancel-order-btn {
    background: #dc3545;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.cancel-order-btn:hover {
    background: #c82333;
    color: white;
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
}

.current-filter {
    background: rgba(255, 255, 255, 0.1);
    padding: 8px 15px;
    border-radius: 20px;
    margin-top: 10px;
    display: inline-block;
    font-size: 0.9rem;
    color: #333;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    padding: 10px 15px;
    background: #f8f9fa;
    border-radius: 8px;
    transition: all 0.3s ease;
    user-select: none;
}

.timeline-header:hover {
    background: #e9ecef;
}

.timeline-arrow {
    transition: transform 0.3s ease;
    color: #6c757d;
}

.timeline-arrow.rotated {
    transform: rotate(180deg);
}

.timeline-content {
    padding: 15px;
    background: #fff;
    border-radius: 0 0 8px 8px;
    border: 1px solid #dee2e6;
    border-top: none;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.status-confirmed {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.status-shipping {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-delivered {
    background: #e2e3e5;
    color: #383d41;
    border: 1px solid #d6d8db;
}

.status-completed {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Style cho đơn hàng đã hủy - chỉ hiển thị thông tin cơ bản */
.order-card.cancelled-order {
    background: linear-gradient(135deg, #ff9a9e, #fecfef);
    border: 1px solid #ff6b6b;
    margin-bottom: 15px;
}

.order-card.cancelled-order .order-header {
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.order-card.cancelled-order .order-info h3 {
    color: #721c24;
    font-weight: 700;
    margin: 0 0 5px 0;
}

.order-card.cancelled-order .order-info p {
    color: #721c24;
    font-weight: 500;
    margin: 0 0 3px 0;
    font-size: 0.9rem;
}

.order-card.cancelled-order .status-badge {
    background: #ff6b6b;
    color: white;
    font-weight: 700;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.9rem;
}

/* Ẩn order-body cho đơn hàng đã hủy */
.order-card.cancelled-order .order-body {
    display: none !important;
}

/* Đảm bảo đơn hàng đã hủy chỉ hiển thị header */
.order-card.cancelled-order {
    min-height: auto;
    padding: 0;
}

.order-card.cancelled-order .order-header {
    border-radius: 8px;
    margin: 0;
}

/* Làm cho tất cả đơn hàng có giao diện compact hơn */
.order-card {
    border-radius: 8px;
}

.order-card .order-header {
    border-radius: 8px 8px 0 0;
}

.order-card .order-body {
    border-radius: 0 0 8px 8px;
}

.order-body {
    padding: 15px;
    background: white;
}

.order-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 15px;
}

.order-detail-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.order-detail-label {
    font-size: 0.85rem;
    color: #666;
    font-weight: 500;
}

.order-detail-value {
    font-size: 1rem;
    color: #333;
    font-weight: 500;
}

.order-items {
    margin-top: 15px;
}

.order-items h5 {
    color: #333;
    margin-bottom: 10px;
    font-weight: 600;
}

.order-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 8px;
}

.order-item-image {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    object-fit: cover;
}

.order-item-info {
    flex: 1;
}

.order-item-name {
    font-size: 1rem;
    color: #333;
    font-weight: 500;
    margin-bottom: 5px;
}

.order-item-details {
    font-size: 0.9rem;
    color: #666;
}

.order-item-price {
    font-size: 1.1rem;
    font-weight: 600;
    color: #ffc107;
}

.order-total {
    background: linear-gradient(135deg, #ffc107, #ff8f00);
    color: #333;
    padding: 12px 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0;
    border-radius: 0;
}

.empty-orders {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.empty-orders i {
    font-size: 4rem;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-orders h3 {
    margin-bottom: 10px;
    color: #333;
}

.empty-orders p {
    margin-bottom: 30px;
}

.btn-primary {
    background: linear-gradient(135deg, #ffc107, #ff8f00);
    border: none;
    color: white;
    padding: 12px 30px;
    border-radius: 25px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 193, 7, 0.4);
    color: white;
    text-decoration: none;
}

.status-timeline {
    margin-top: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.timeline-item {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
    position: relative;
}

.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 15px;
    top: 30px;
    width: 2px;
    height: 20px;
    background: #ddd;
}

.timeline-icon {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    color: white;
    z-index: 1;
    position: relative;
}

.timeline-icon.active {
    background: #28a745;
}

.timeline-icon.pending {
    background: #ffc107;
}

.timeline-icon.inactive {
    background: #ddd;
}

.timeline-content {
    flex: 1;
}

.timeline-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 2px;
}

.timeline-desc {
    font-size: 0.9rem;
    color: #666;
}

@media (max-width: 768px) {
    .page-background {
        padding: 0 1rem 1rem 1rem;
    }
    
    .content-wrapper {
        padding: 1.5rem;
        border-radius: 12px;
    }
    
    .order-details {
        grid-template-columns: 1fr;
    }
    
    .order-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .order-status {
        width: 100%;
        justify-content: flex-start;
    }
    
    .order-item {
        flex-direction: column;
        text-align: center;
    }
    
    .order-item-image {
        width: 80px;
        height: 80px;
    }
}
</style>

<!-- Page Background Start -->
<div class="page-background">
    <!-- Content Wrapper Start -->
    <div class="content-wrapper">

<div class="orders-container">
    <div class="orders-header">
        <div class="container" style="padding-left: 0; padding-right: 0;">
            <h1><i class="fas fa-shopping-bag mr-3"></i>Đơn hàng của tôi</h1>
            <p>Quản lý và theo dõi tất cả đơn hàng của bạn</p>
            <?php if ($status_filter): ?>
                <div class="current-filter">
                    <i class="fas fa-filter mr-2"></i>
                    Đang lọc theo: 
                    <strong>
                        <?php
                        $status_names = [
                            'pending' => 'Chờ xác nhận',
                            'confirmed' => 'Đã xác nhận', 
                            'shipping' => 'Đang giao hàng',
                            'delivered' => 'Đã giao hàng',
                            'cancelled' => 'Đã hủy'
                        ];
                        echo $status_names[$status_filter] ?? $status_filter;
                        ?>
                    </strong>
                    <a href="index.php?my-orders" class="btn btn-sm btn-outline-light ml-2">
                        <i class="fas fa-times"></i> Xóa lọc
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="container" style="padding-left: 0; padding-right: 0;">
        <?php if (empty($orders)): ?>
            <div class="empty-orders">
                <i class="fas fa-shopping-cart"></i>
                <h3>Chưa có đơn hàng nào</h3>
                <p>Bạn chưa có đơn hàng nào. Hãy khám phá các sản phẩm thú vị từ livestream!</p>
                <a href="index.php" class="btn-primary">
                    <i class="fas fa-home mr-2"></i>Về trang chủ
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card <?= $order['status'] == 'cancelled' ? 'cancelled-order' : '' ?>">
                    <div class="order-header">
                        <div class="order-info">
                            <h3>Đơn hàng #<?= $order['order_code'] ?></h3>
                            <p>Livestream: <?= htmlspecialchars($order['livestream_title'] ?? 'N/A') ?></p>
                            <p>Streamer: <?= htmlspecialchars($order['streamer_name'] ?? 'N/A') ?></p>
                        </div>
                        <div class="order-status">
                            <?php
                            $status = $order['status'];
                            $statusText = '';
                            $statusClass = '';
                            
                            switch ($status) {
                                case 'pending':
                                    $statusText = 'Chờ xác nhận';
                                    $statusClass = 'status-pending';
                                    break;
                                case 'confirmed':
                                    $statusText = 'Đã xác nhận';
                                    $statusClass = 'status-confirmed';
                                    break;
                                case 'shipping':
                                    $statusText = 'Đang giao hàng';
                                    $statusClass = 'status-shipping';
                                    break;
                                case 'delivered':
                                    $statusText = 'Đang vận chuyển';
                                    $statusClass = 'status-delivered';
                                    break;
                                case 'completed':
                                    $statusText = 'Đã giao thành công';
                                    $statusClass = 'status-completed';
                                    break;
                                case 'cancelled':
                                    $statusText = 'Đã hủy';
                                    $statusClass = 'status-cancelled';
                                    break;
                                default:
                                    $statusText = 'Không xác định';
                                    $statusClass = 'status-pending';
                            }
                            ?>
                            <span class="status-badge <?= $statusClass ?>">
                                <?= $statusText ?>
                            </span>
                            
                            <?php if ($status == 'pending' || $status == 'confirmed'): ?>
                                <button class="cancel-order-btn" onclick="cancelOrder(<?= $order['id'] ?>)">
                                    <i class="fas fa-times"></i> Hủy đơn
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($order['status'] != 'cancelled'): ?>
                    <div class="order-body">
                        <div class="order-details">
                            <div class="order-detail-item">
                                <span class="order-detail-label">Ngày đặt hàng</span>
                                <span class="order-detail-value"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
                            </div>
                            <div class="order-detail-item">
                                <span class="order-detail-label">Phương thức thanh toán</span>
                                <span class="order-detail-value">
                                    <?php
                                    switch ($order['payment_method']) {
                                        case 'vnpay':
                                            echo 'Thanh toán trực tuyến (VNPay)';
                                            break;
                                        case 'wallet':
                                            echo 'Thanh toán bằng ví';
                                            break;
                                        case 'cash':
                                            echo 'Giao trực tiếp';
                                            break;
                                        default:
                                            echo 'Không xác định';
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="order-detail-item">
                                <span class="order-detail-label">Người nhận</span>
                                <span class="order-detail-value"><?= htmlspecialchars($order['delivery_name'] ?? 'N/A') ?></span>
                            </div>
                            <div class="order-detail-item">
                                <span class="order-detail-label">Số điện thoại</span>
                                <span class="order-detail-value"><?= htmlspecialchars($order['delivery_phone'] ?? 'N/A') ?></span>
                            </div>
                            <div class="order-detail-item">
                                <span class="order-detail-label">Địa chỉ giao hàng</span>
                                <span class="order-detail-value">
                                    <?= htmlspecialchars($order['delivery_address'] ?? 'N/A') ?>
                                </span>
                            </div>
                        </div>

                        <div class="order-items">
                            <h5>- Sản phẩm trong đơn hàng</h5>
                            <?php
                            $orderDetails = $model->getOrderDetails($order['id']);
                            if (isset($orderDetails['items'])) {
                                foreach ($orderDetails['items'] as $item):
                            ?>
                                <div class="order-item">
                                    <?php 
                                    $itemImage = $item['anh_dau'] ?? 'img/default-product.jpg';
                                    // Đường dẫn ảnh từ gốc project
                                    if (strpos($itemImage, 'img/') !== 0) {
                                        $itemImage = 'img/' . basename($itemImage);
                                    }
                                    ?>
                                    <img src="<?= htmlspecialchars($itemImage) ?>" 
                                         alt="<?= htmlspecialchars($item['product_title']) ?>" 
                                         class="order-item-image">
                                    <div class="order-item-info">
                                        <div class="order-item-name"><?= htmlspecialchars($item['product_title']) ?></div>
                                        <div class="order-item-details">
                                            Số lượng: <?= $item['quantity'] ?> | Giá: <?= number_format($item['product_price']) ?> đ
                                        </div>
                                    </div>
                                    <div class="order-item-price">
                                        <?= number_format($item['product_price'] * $item['quantity']) ?> đ
                                    </div>
                                </div>
                            <?php 
                                endforeach; 
                            }
                            ?>
                        </div>

                        <div class="status-timeline">
                            <div class="timeline-header" onclick="toggleTimeline(<?= $order['id'] ?>)">
                                <h5><i class="fas fa-history mr-2"></i>Trạng thái đơn hàng</h5>
                                <i class="fas fa-chevron-down timeline-arrow" id="arrow-<?= $order['id'] ?>"></i>
                            </div>
                            <div class="timeline-content" id="timeline-<?= $order['id'] ?>" style="display: none;">
                            <div class="timeline-item">
                                <div class="timeline-icon <?= in_array($status, ['pending', 'confirmed', 'shipping', 'delivered', 'completed']) ? 'active' : 'inactive' ?>">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Chờ xác nhận</div>
                                    <div class="timeline-desc">Đơn hàng đang chờ xác nhận từ người bán</div>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-icon <?= in_array($status, ['confirmed', 'shipping', 'delivered', 'completed']) ? 'active' : 'inactive' ?>">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Đã xác nhận</div>
                                    <div class="timeline-desc">Đơn hàng đã được xác nhận</div>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-icon <?= in_array($status, ['shipping', 'delivered', 'completed']) ? 'active' : 'inactive' ?>">
                                    <i class="fas fa-truck"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Đang giao hàng</div>
                                    <div class="timeline-desc">Đơn hàng đang được giao</div>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-icon <?= in_array($status, ['delivered', 'completed']) ? 'active' : 'inactive' ?>">
                                    <i class="fas fa-shipping-fast"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Đang vận chuyển</div>
                                    <div class="timeline-desc">Đơn hàng đang được vận chuyển</div>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-icon <?= $status == 'completed' ? 'active' : 'inactive' ?>">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Đã giao thành công</div>
                                    <div class="timeline-desc">Đơn hàng đã được giao thành công</div>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>

                        <div class="order-total">
                            <span>Tổng cộng:</span>
                            <span><?= number_format($order['total_amount']) ?> đ</span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

    </div>
    <!-- Content Wrapper End -->
</div>
<!-- Page Background End -->

<script>
function cancelOrder(orderId) {
    if (confirm('Bạn có chắc chắn muốn hủy đơn hàng này? Hành động này không thể hoàn tác.')) {
        // Gửi request hủy đơn hàng
        fetch('api/livestream-api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=cancel_order&order_id=${orderId}`
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            return response.text();
        })
        .then(text => {
            console.log('Raw response:', text);
            try {
                const data = JSON.parse(text);
                console.log('Parsed data:', data);
                if (data.success) {
                    // Hiển thị thông báo thành công với thông tin hoàn tiền
                    alert(data.message || 'Đã hủy đơn hàng thành công!');
                    // Reload trang để cập nhật
                    location.reload();
                } else {
                    alert('Có lỗi xảy ra: ' + (data.message || 'Không thể hủy đơn hàng'));
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response text:', text);
                alert('Lỗi phản hồi từ server: ' + text.substring(0, 200));
            }
        })
        .catch(error => {
            console.error('Network error:', error);
            alert('Có lỗi xảy ra khi hủy đơn hàng: ' + error.message);
        });
    }
}

function toggleTimeline(orderId) {
    const timeline = document.getElementById('timeline-' + orderId);
    const arrow = document.getElementById('arrow-' + orderId);
    
    if (timeline.style.display === 'none') {
        timeline.style.display = 'block';
        arrow.classList.add('rotated');
    } else {
        timeline.style.display = 'none';
        arrow.classList.remove('rotated');
    }
}
</script>

<?php include_once("footer.php"); ?>

