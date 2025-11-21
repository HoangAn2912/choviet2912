<?php
// Xử lý AJAX request TRƯỚC TIÊN - trước khi có bất kỳ output nào
if (isset($_GET['action']) && $_GET['action'] === 'get_details' && isset($_GET['order_id'])) {
    // Kiểm tra session cho AJAX request
    if (!isset($_SESSION['role']) || ($_SESSION['role'] != 1 && $_SESSION['role'] != 4 && $_SESSION['role'] != 5)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Không đủ thẩm quyền'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    include_once(__DIR__ . "/../../controller/cQLdonhang.php");
    $controller = new cQLdonhang();
    
    header('Content-Type: application/json');
    
    try {
        $order_id = intval($_GET['order_id']);
        
        if ($order_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID đơn hàng không hợp lệ'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $order = $controller->getOrderDetails($order_id);
        
        if ($order) {
            // Đảm bảo tất cả các trường cần thiết đều có giá trị
            $order['order_code'] = $order['order_code'] ?? '';
            $order['status'] = $order['status'] ?? 'pending';
            $order['total_amount'] = $order['total_amount'] ?? 0;
            $order['payment_method'] = $order['payment_method'] ?? '';
            $order['created_at'] = $order['created_at'] ?? date('Y-m-d H:i:s');
            $order['updated_at'] = $order['updated_at'] ?? $order['created_at'];
            $order['items'] = $order['items'] ?? [];
            
            echo json_encode(['success' => true, 'order' => $order], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng'], JSON_UNESCAPED_UNICODE);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// Kiểm tra quyền truy cập cho trang thông thường
if ($_SESSION['role'] != 1 && $_SESSION['role'] != 4 && $_SESSION['role'] != 5) {
    echo "<script>
        alert('Bạn không đủ thẩm quyền truy cập!');
    </script>";
    header("refresh: 0; url='/ad'");
    exit;
}

include_once(__DIR__ . "/../../controller/cQLdonhang.php");
$controller = new cQLdonhang();

// Xử lý cập nhật trạng thái
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = $_POST['order_id'] ?? '';
    $new_status = $_POST['new_status'] ?? '';
    
    if ($order_id && $new_status) {
        if ($controller->updateOrderStatus($order_id, $new_status)) {
            $message = "Cập nhật trạng thái đơn hàng thành công!";
        } else {
            $message = "Lỗi cập nhật trạng thái!";
        }
    }
}

// Lấy tham số filter
$status_filter = $_GET['status'] ?? 'all';
$livestream_filter = $_GET['livestream_id'] ?? '';
$user_filter = $_GET['user_id'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;
$currentPage = $page;

// Lấy dữ liệu
$stats = $controller->getStats();
$orders = $controller->getAllOrders(
    $status_filter !== 'all' ? $status_filter : null,
    $livestream_filter ?: null,
    $user_filter ?: null,
    $start_date ?: null,
    $end_date ?: null,
    $limit,
    $offset
);
$total_orders = $controller->countOrders(
    $status_filter !== 'all' ? $status_filter : null,
    $livestream_filter ?: null,
    $user_filter ?: null,
    $start_date ?: null,
    $end_date ?: null
);
$total_pages = ceil($total_orders / $limit);
$totalPages = $total_pages;
$totalItems = $total_orders;

// Function to generate pagination URL
function getPaginationUrl($page, $status, $livestream_id, $user_id, $start_date, $end_date) {
    $url = "qldonhang&page={$page}";
    if ($status && $status !== 'all') $url .= "&status={$status}";
    if ($livestream_id) $url .= "&livestream_id={$livestream_id}";
    if ($user_id) $url .= "&user_id={$user_id}";
    if ($start_date) $url .= "&start_date={$start_date}";
    if ($end_date) $url .= "&end_date={$end_date}";
    return $url;
}

$livestreams = $controller->getAllLivestreams();
$users = $controller->getAllUsers();

// Load helper cho đường dẫn ảnh
require_once __DIR__ . '/../../helpers/url_helper.php';

// Mapping trạng thái
$status_names = [
    'pending' => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'shipped' => 'Đang giao hàng',
    'delivered' => 'Đã giao hàng',
    'cancelled' => 'Đã hủy'
];

$status_colors = [
    'pending' => 'warning',
    'confirmed' => 'info',
    'shipped' => 'primary',
    'delivered' => 'success',
    'cancelled' => 'danger'
];
?>

<style>
    /* CSS riêng cho trang quản lý đơn hàng */
    /* CSS riêng cho trang quản lý đơn hàng - chỉ override nếu cần */
    .qldonhang-container {
        /* Đã được định nghĩa trong admin-common.css */
    }
    
    .stats-grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
        gap: 20px; 
        margin-bottom: 30px; 
    }
    
    .stat-card { 
        background: white; 
        padding: 20px; 
        border-radius: 10px; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
    }
    
    .stat-card h3 { 
        color: #666; 
        font-size: 0.9rem; 
        margin-bottom: 10px; 
        text-transform: uppercase; 
    }
    
    .stat-card .number { 
        font-size: 2rem; 
        font-weight: bold; 
        color: #333; 
    }
    
    .stat-card.success .number { color: #28a745; }
    .stat-card.warning .number { color: #ffc107; }
    .stat-card.danger .number { color: #dc3545; }
    .stat-card.primary .number { color: #007bff; }
    .stat-card.info .number { color: #17a2b8; }
    
    .filters { 
        background: white; 
        padding: 20px; 
        border-radius: 10px; 
        margin-bottom: 20px; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
    }
    
    .filters form { 
        display: flex; 
        gap: 15px; 
        align-items: flex-end; 
        flex-wrap: wrap; 
    }
    
    .filters .form-group {
        flex: 1;
        min-width: 150px;
    }
    
    .filters label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: #333;
    }
    
    .filters select, .filters input { 
        width: 100%;
        padding: 8px 12px; 
        border: 1px solid #ddd; 
        border-radius: 5px; 
    }
    
    .filters button { 
        background: #007bff; 
        color: white; 
        padding: 8px 16px; 
        border: none; 
        border-radius: 5px; 
        cursor: pointer; 
        white-space: nowrap;
    }
    
    .filters button:hover { background: #0056b3; }
    
    .orders-table { 
        background: white; 
        border-radius: 10px; 
        overflow: hidden; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
    }
    
    .table { 
        width: 100%; 
        border-collapse: collapse; 
    }
    
    .table th { 
        background: #f8f9fa; 
        padding: 15px; 
        text-align: left; 
        font-weight: 600; 
        border-bottom: 2px solid #dee2e6; 
    }
    
    .table td { 
        padding: 15px; 
        border-bottom: 1px solid #dee2e6; 
        vertical-align: middle;
    }
    
    .table tr:hover { background: #f8f9fa; }
    
    /* Truncate text dài cho cột Livestream - chỉ 1 dòng */
    .table td:nth-child(2) {
        max-width: 300px;
    }
    
    .livestream-title {
        display: inline-block;
        max-width: 150px; /* Khoảng 15 ký tự */
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
        line-height: 1.5;
    }
    
    .livestream-cell {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .status-badge { 
        padding: 6px 12px; 
        border-radius: 20px; 
        font-size: 0.85rem; 
        font-weight: 600; 
        display: inline-block;
    }
    
    .status-badge.warning { background: #fff3cd; color: #856404; }
    .status-badge.info { background: #d1ecf1; color: #0c5460; }
    .status-badge.primary { background: #cce5ff; color: #004085; }
    .status-badge.success { background: #d4edda; color: #155724; }
    .status-badge.danger { background: #f8d7da; color: #721c24; }
    
    .amount { font-weight: 600; color: #28a745; }
    
    .actions { display: flex; gap: 5px; flex-wrap: wrap; }
    
    .btn { 
        padding: 6px 12px; 
        border: none; 
        border-radius: 4px; 
        cursor: pointer; 
        font-size: 0.85rem; 
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-primary { background: #007bff; color: white; }
    .btn-success { background: #28a745; color: white; }
    .btn-danger { background: #dc3545; color: white; }
    .btn-info { background: #17a2b8; color: white; }
    .btn:hover { opacity: 0.8; }
    
    .pagination { 
        display: flex; 
        justify-content: center; 
        gap: 10px; 
        margin-top: 20px; 
    }
    
    .pagination a, .pagination span { 
        padding: 8px 12px; 
        border: 1px solid #ddd; 
        border-radius: 4px; 
        text-decoration: none; 
        color: #007bff; 
    }
    
    .pagination .current { background: #007bff; color: white; }
    
    .alert { 
        padding: 15px; 
        margin-bottom: 20px; 
        border-radius: 5px; 
    }
    
    .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    
    .modal { 
        display: none; 
        position: fixed; 
        top: 0; 
        left: 0; 
        width: 100%; 
        height: 100%; 
        background: rgba(0,0,0,0.5); 
        z-index: 1000; 
        overflow-y: auto;
    }
    
    .modal-content { 
        background: white; 
        margin: 3% auto; 
        padding: 30px; 
        width: 90%; 
        max-width: 900px; 
        border-radius: 10px; 
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        position: relative;
    }
    
    .modal-header { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        margin-bottom: 20px; 
        border-bottom: 2px solid #dee2e6;
        padding-bottom: 15px;
    }
    
    .modal-header h3 {
        margin: 0;
        color: #333;
    }
    
    .close { 
        font-size: 28px; 
        cursor: pointer; 
        color: #999;
        background: none;
        border: none;
    }
    
    .close:hover { color: #333; }
    
    .detail-section {
        margin-bottom: 25px;
    }
    
    .detail-section h4 {
        color: #007bff;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #dee2e6;
    }
    
    .detail-row {
        display: flex;
        margin-bottom: 10px;
    }
    
    .detail-label {
        font-weight: 600;
        width: 150px;
        color: #666;
    }
    
    .detail-value {
        flex: 1;
        color: #333;
    }
    
    .product-item {
        display: flex;
        align-items: center;
        padding: 15px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 10px;
        background: #f8f9fa;
        transition: all 0.3s ease;
    }
    
    .product-item:hover {
        background: #e9ecef;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .product-item img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 5px;
        margin-right: 15px;
        border: 2px solid #dee2e6;
        flex-shrink: 0;
    }
    
    .product-info {
        flex: 1;
    }
    
    .product-title {
        font-weight: 600;
        margin-bottom: 5px;
        color: #333;
        font-size: 1rem;
    }
    
    .product-price {
        color: #28a745;
        font-weight: 600;
        font-size: 1.05rem;
        margin-top: 5px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .loading-spinner {
        display: inline-block;
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @media (max-width: 768px) {
        .table { font-size: 0.8rem; }
        .filters form { flex-direction: column; align-items: stretch; }
        .stats-grid { grid-template-columns: 1fr; }
        .table th, .table td { padding: 8px; }
        .modal-content { width: 95%; padding: 20px; }
    }
</style>

<div class="qldonhang-container">
    <div class="admin-card">
        <h3 class="admin-card-title">Quản lý đơn hàng</h3>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo strpos($message, 'thành công') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
        <div class="stat-card info">
            <h3>Đã Xác Nhận</h3>
            <div class="number"><?php echo number_format($stats['confirmed_orders']); ?></div>
        </div>
        <div class="stat-card success">
            <h3>Đã Giao</h3>
            <div class="number"><?php echo number_format($stats['delivered_orders']); ?></div>
        </div>
        <div class="stat-card danger">
            <h3>Đã Hủy</h3>
            <div class="number"><?php echo number_format($stats['cancelled_orders']); ?></div>
        </div>
        <div class="stat-card primary">
            <h3>Tổng Doanh Thu</h3>
            <div class="number"><?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?> đ</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters">
        <form method="GET">
            <input type="hidden" name="qldonhang" value="">
            
            <div class="form-group">
                <label>Trạng thái</label>
                <select name="status" class="form-control">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Tất cả</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Chờ xác nhận</option>
                    <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Đã xác nhận</option>
                    <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Đang giao hàng</option>
                    <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Đã giao hàng</option>
                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Livestream</label>
                <select name="livestream_id" class="form-control">
                    <option value="">Tất cả livestream</option>
                    <?php foreach ($livestreams as $ls): ?>
                        <option value="<?php echo $ls['id']; ?>" <?php echo $livestream_filter == $ls['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ls['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Người mua</label>
                <select name="user_id" class="form-control">
                    <option value="">Tất cả người dùng</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>" <?php echo $user_filter == $user['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Từ ngày</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>">
            </div>
            
            <div class="form-group">
                <label>Đến ngày</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>">
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="mdi mdi-filter"></i> Lọc
                </button>
                <a href="?qldonhang" class="btn btn-secondary" style="margin-left: 10px;">
                    <i class="mdi mdi-refresh"></i> Đặt lại
                </a>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Mã Đơn</th>
                    <th>Livestream</th>
                    <th>Streamer</th>
                    <th>Người Mua</th>
                    <th>Số Tiền</th>
                    <th>Trạng Thái</th>
                    <th>Ngày Đặt</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="8" class="empty-message">
                            Không có đơn hàng nào
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><strong>#<?php echo htmlspecialchars($order['order_code']); ?></strong></td>
                            <td>
                                <div class="livestream-cell">
                                    <?php 
                                    // Xử lý ảnh livestream
                                    $livestreamImage = 'default-live.jpg';
                                    if (!empty($order['livestream_thumbnail'])) {
                                        $livestreamImage = $order['livestream_thumbnail'];
                                    }
                                    
                                    // Xử lý tiêu đề - giới hạn 15 ký tự
                                    $livestreamTitle = $order['livestream_title'] ?? 'N/A';
                                    $livestreamTitleFull = $livestreamTitle;
                                    if (mb_strlen($livestreamTitle) > 15) {
                                        $livestreamTitle = mb_substr($livestreamTitle, 0, 15) . '...';
                                    }
                                    
                                    // Đường dẫn ảnh từ thư mục img
                                    $imagePath = getBasePath() . '/img/' . htmlspecialchars($livestreamImage);
                                    ?>
                                    <img src="<?php echo $imagePath; ?>" 
                                         alt="<?php echo htmlspecialchars($livestreamTitleFull); ?>"
                                         style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px; flex-shrink: 0;"
                                         onerror="this.onerror=null; this.src='<?php echo getBasePath(); ?>/img/default-live.jpg';">
                                    <span class="livestream-title" title="<?php echo htmlspecialchars($livestreamTitleFull); ?>">
                                        <?php echo htmlspecialchars($livestreamTitle); ?>
                                    </span>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($order['seller_name'] ?? 'N/A'); ?></td>
                            <td>
                                <div><?php echo htmlspecialchars($order['buyer_name'] ?? 'N/A'); ?></div>
                                <small style="color: #666;"><?php echo htmlspecialchars($order['buyer_email'] ?? ''); ?></small>
                            </td>
                            <td class="amount"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> đ</td>
                            <td>
                                <span class="status-badge <?php echo $status_colors[$order['status']] ?? 'warning'; ?>">
                                    <?php echo $status_names[$order['status']] ?? $order['status']; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                            <td>
                                <div class="actions">
                                    <button class="btn btn-primary" onclick="showOrderDetails(<?php echo $order['id']; ?>)">
                                        Chi tiết
                                    </button>
                                    <?php if ($order['status'] !== 'delivered' && $order['status'] !== 'cancelled'): ?>
                                        <select class="btn btn-info" onchange="updateStatus(<?php echo $order['id']; ?>, this.value)" style="padding: 6px 8px;">
                                            <option value="">Cập nhật</option>
                                            <?php if ($order['status'] === 'pending'): ?>
                                                <option value="confirmed">Xác nhận</option>
                                                <option value="cancelled">Hủy</option>
                                            <?php elseif ($order['status'] === 'confirmed'): ?>
                                                <option value="shipped">Giao hàng</option>
                                                <option value="cancelled">Hủy</option>
                                            <?php elseif ($order['status'] === 'shipped'): ?>
                                                <option value="delivered">Hoàn thành</option>
                                            <?php endif; ?>
                                        </select>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination-info">
            Hiển thị <?php echo count($orders); ?> trên tổng số <?php echo $totalItems; ?> bản ghi
        </div>
        <div class="pagination">
            <?php if ($currentPage > 1): ?>
                <a href="?<?php echo getPaginationUrl($currentPage - 1, $status_filter, $livestream_filter, $user_filter, $start_date, $end_date); ?>">&lt;</a>
            <?php endif; ?>
            
            <?php
                // Determine range of page numbers to show (current page ± 1)
                $startPage = max(1, $currentPage - 1);
                $endPage = min($totalPages, $currentPage + 1);
                
                // Show first page if not in range
                if ($startPage > 1): ?>
                    <a href="?<?php echo getPaginationUrl(1, $status_filter, $livestream_filter, $user_filter, $start_date, $end_date); ?>">1</a>
                    <?php if ($startPage > 2): ?>
                        <span class="ellipsis">...</span>
                    <?php endif; ?>
                <?php endif;
                
                // Generate page links
                for ($i = $startPage; $i <= $endPage; $i++):
            ?>
                <?php if ($i == $currentPage): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?<?php echo getPaginationUrl($i, $status_filter, $livestream_filter, $user_filter, $start_date, $end_date); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php
                // Show last page if not in range
                if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                        <span class="ellipsis">...</span>
                    <?php endif; ?>
                    <a href="?<?php echo getPaginationUrl($totalPages, $status_filter, $livestream_filter, $user_filter, $start_date, $end_date); ?>"><?php echo $totalPages; ?></a>
                <?php endif; ?>
            
            <?php if ($currentPage < $totalPages): ?>
                <a href="?<?php echo getPaginationUrl($currentPage + 1, $status_filter, $livestream_filter, $user_filter, $start_date, $end_date); ?>">&gt;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    </div>
</div>

<!-- Modal Chi tiết đơn hàng -->
<div id="orderModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Chi tiết đơn hàng</h3>
            <button class="close" onclick="closeModal()">&times;</button>
        </div>
        <div id="orderDetails"></div>
    </div>
</div>

<script>
function showOrderDetails(orderId) {
    // Hiển thị loading
    document.getElementById('orderDetails').innerHTML = '<div style="text-align: center; padding: 40px;"><div class="loading-spinner"></div><p style="margin-top: 15px; color: #666;">Đang tải chi tiết đơn hàng...</p></div>';
    document.getElementById('orderModal').style.display = 'block';
    
    // Lấy URL hiện tại và tạo URL fetch
    // Đảm bảo giữ nguyên các tham số hiện tại nếu có
    const url = new URL(window.location.href);
    url.searchParams.set('qldonhang', '');
    url.searchParams.set('action', 'get_details');
    url.searchParams.set('order_id', orderId);
    
    fetch(url.toString())
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const order = data.order;
                // Xử lý ảnh livestream
                let livestreamImage = '<?php echo getBasePath(); ?>/img/default-live.jpg';
                if (order.livestream_thumbnail) {
                    if (order.livestream_thumbnail.startsWith('http') || order.livestream_thumbnail.startsWith('/')) {
                        livestreamImage = order.livestream_thumbnail;
                    } else {
                        livestreamImage = '<?php echo getBasePath(); ?>/img/' + order.livestream_thumbnail;
                    }
                }
                
                // Xử lý địa chỉ giao hàng
                let deliveryAddress = '';
                const addressParts = [];
                if (order.delivery_address) addressParts.push(order.delivery_address);
                if (order.delivery_street) addressParts.push(order.delivery_street);
                if (order.delivery_ward) addressParts.push(order.delivery_ward);
                if (order.delivery_district) addressParts.push(order.delivery_district);
                if (order.delivery_province) addressParts.push(order.delivery_province);
                deliveryAddress = addressParts.length > 0 ? addressParts.join(', ') : 'N/A';
                
                // Xử lý phương thức thanh toán
                let paymentMethod = order.payment_method || 'N/A';
                if (paymentMethod === 'vnpay') {
                    paymentMethod = 'VNPay';
                } else if (paymentMethod === 'cod') {
                    paymentMethod = 'Thanh toán khi nhận hàng (COD)';
                }
                
                let html = `
                    <div class="detail-section">
                        <h4>Thông tin đơn hàng</h4>
                        <div class="detail-row">
                            <span class="detail-label">Mã đơn hàng:</span>
                            <span class="detail-value"><strong style="color: #007bff; font-size: 1.1rem;">#${order.order_code}</strong></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Trạng thái:</span>
                            <span class="detail-value">
                                <span class="status-badge ${getStatusColor(order.status)}">${getStatusName(order.status)}</span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Tổng tiền:</span>
                            <span class="detail-value amount" style="font-size: 1.2rem;">${formatCurrency(order.total_amount)} đ</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Phương thức thanh toán:</span>
                            <span class="detail-value">${paymentMethod}</span>
                        </div>
                        ${order.vnpay_txn_ref ? `
                        <div class="detail-row">
                            <span class="detail-label">Mã giao dịch VNPay:</span>
                            <span class="detail-value"><code style="background: #f5f5f5; padding: 4px 8px; border-radius: 4px;">${order.vnpay_txn_ref}</code></span>
                        </div>
                        ` : ''}
                        <div class="detail-row">
                            <span class="detail-label">Ngày đặt:</span>
                            <span class="detail-value">${formatDate(order.created_at)}</span>
                        </div>
                        ${order.updated_at && order.updated_at !== order.created_at ? `
                        <div class="detail-row">
                            <span class="detail-label">Cập nhật lần cuối:</span>
                            <span class="detail-value">${formatDate(order.updated_at)}</span>
                        </div>
                        ` : ''}
                    </div>
                    
                    <div class="detail-section">
                        <h4>Thông tin livestream</h4>
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                            <img src="${livestreamImage}" 
                                 alt="${order.livestream_title || 'Livestream'}"
                                 style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; flex-shrink: 0;"
                                 onerror="this.onerror=null; this.src='<?php echo getBasePath(); ?>/img/default-live.jpg';">
                            <div style="flex: 1;">
                                <div style="font-weight: 600; margin-bottom: 5px; color: #333;">${order.livestream_title || 'N/A'}</div>
                                <div style="color: #666; font-size: 0.9rem;">Streamer: <strong>${order.seller_name || 'N/A'}</strong></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h4>Thông tin người mua</h4>
                        <div class="detail-row">
                            <span class="detail-label">Tên:</span>
                            <span class="detail-value">${order.buyer_name || 'N/A'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value">${order.buyer_email || 'N/A'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Số điện thoại:</span>
                            <span class="detail-value">${order.buyer_phone || 'N/A'}</span>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h4>Thông tin giao hàng</h4>
                        <div class="detail-row">
                            <span class="detail-label">Người nhận:</span>
                            <span class="detail-value">${order.delivery_name || 'N/A'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Số điện thoại:</span>
                            <span class="detail-value">${order.delivery_phone || 'N/A'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Địa chỉ:</span>
                            <span class="detail-value">${deliveryAddress}</span>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h4>Sản phẩm trong đơn hàng</h4>
                        ${renderProducts(order.items || [])}
                    </div>
                `;
                document.getElementById('orderDetails').innerHTML = html;
                document.getElementById('orderModal').style.display = 'block';
            } else {
                // Nếu không thành công, hiển thị thông báo lỗi từ server
                const errorMsg = data.message || 'Không thể tải chi tiết đơn hàng';
                document.getElementById('orderDetails').innerHTML = '<div style="text-align: center; padding: 40px; color: #dc3545;"><p>' + errorMsg + '</p></div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('orderDetails').innerHTML = '<div style="text-align: center; padding: 40px; color: #dc3545;"><p>Lỗi khi tải chi tiết đơn hàng: ' + error.message + '</p><p style="font-size: 0.9rem; margin-top: 10px;">Vui lòng thử lại sau hoặc liên hệ quản trị viên.</p></div>';
        });
}

function renderProducts(items) {
    if (!items || items.length === 0) {
        return '<p style="color: #666; padding: 20px; text-align: center;">Không có sản phẩm trong đơn hàng</p>';
    }
    
    let html = '';
    items.forEach(item => {
        // Xử lý đường dẫn ảnh sản phẩm
        let imagePath = '';
        if (item.product_image) {
            // Nếu đã có đường dẫn đầy đủ thì dùng, nếu không thì thêm base path
            if (item.product_image.startsWith('http') || item.product_image.startsWith('/')) {
                imagePath = item.product_image;
            } else {
                imagePath = '<?php echo getBasePath(); ?>/' + item.product_image;
            }
        } else {
            imagePath = '<?php echo getBasePath(); ?>/img/default-product.jpg';
        }
        
        const totalPrice = (parseFloat(item.price) || 0) * (parseInt(item.quantity) || 0);
        
        html += `
            <div class="product-item">
                <img src="${imagePath}" 
                     alt="${item.product_title || 'Sản phẩm'}"
                     onerror="this.onerror=null; this.src='<?php echo getBasePath(); ?>/img/default-product.jpg';">
                <div class="product-info">
                    <div class="product-title">${item.product_title || 'N/A'}</div>
                    <div style="color: #666; margin: 5px 0;">
                        Số lượng: <strong>${item.quantity || 0}</strong> | 
                        Đơn giá: <strong>${formatCurrency(item.price || 0)} đ</strong>
                    </div>
                    <div class="product-price">Thành tiền: ${formatCurrency(totalPrice)} đ</div>
                </div>
            </div>
        `;
    });
    return html;
}

function updateStatus(orderId, newStatus) {
    if (!newStatus) return;
    
    // Set values for modal
    document.getElementById('updateOrderId').value = orderId;
    document.getElementById('updateOrderStatus').value = newStatus;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('updateOrderStatusModal'));
    modal.show();
}

function confirmUpdateOrderStatus() {
    const orderId = document.getElementById('updateOrderId').value;
    const newStatus = document.getElementById('updateOrderStatus').value;
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="order_id" value="${orderId}">
        <input type="hidden" name="new_status" value="${newStatus}">
    `;
    document.body.appendChild(form);
    form.submit();
}

function closeModal() {
    document.getElementById('orderModal').style.display = 'none';
}

function getStatusName(status) {
    const names = {
        'pending': 'Chờ xác nhận',
        'confirmed': 'Đã xác nhận',
        'shipped': 'Đang giao hàng',
        'delivered': 'Đã giao hàng',
        'cancelled': 'Đã hủy'
    };
    return names[status] || status;
}

function getStatusColor(status) {
    const colors = {
        'pending': 'warning',
        'confirmed': 'info',
        'shipped': 'primary',
        'delivered': 'success',
        'cancelled': 'danger'
    };
    return colors[status] || 'warning';
}

function formatCurrency(amount) {
    return amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN');
}

// Đóng modal khi click bên ngoài
window.onclick = function(event) {
    const modal = document.getElementById('orderModal');
    if (event.target === modal) {
        closeModal();
    }
}
</script>

<!-- Update Order Status Modal -->
<div class="modal fade" id="updateOrderStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cập nhật trạng thái đơn hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn cập nhật trạng thái đơn hàng này?</p>
                <input type="hidden" id="updateOrderId">
                <input type="hidden" id="updateOrderStatus">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="confirmUpdateOrderStatus()">Xác nhận cập nhật</button>
            </div>
        </div>
    </div>
</div>

