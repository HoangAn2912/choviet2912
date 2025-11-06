<?php
require_once(__DIR__ . '/../model/mSellerDashboard.php');
require_once(__DIR__ . '/../helpers/Security.php');

class cSellerDashboard {
    private $model;

    public function __construct() {
        $this->model = new mSellerDashboard();
    }

    /**
     * Hiển thị dashboard chính
     */
    public function showDashboard() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: loginlogout/login.php");
            exit;
        }

        $seller_id = $_SESSION['user_id'];
        $days = intval($_GET['days'] ?? 30);
        
        // Lấy dữ liệu
        $summary = $this->model->getRevenueSummary($seller_id, $days);
        $daily_revenue = $this->model->getDailyRevenue($seller_id, $days);
        $top_products = $this->model->getTopProducts($seller_id, 5);
        $recent_orders = $this->model->getLivestreamOrders($seller_id, null, 10);
        $review_stats = $this->model->getReviewStats($seller_id);
        
        return [
            'summary' => $summary,
            'daily_revenue' => $daily_revenue,
            'top_products' => $top_products,
            'recent_orders' => $recent_orders,
            'review_stats' => $review_stats,
            'days' => $days
        ];
    }

    /**
     * Hiển thị danh sách đơn hàng
     */
    public function showOrders() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: loginlogout/login.php");
            exit;
        }

        $seller_id = $_SESSION['user_id'];
        $status = $_GET['status'] ?? null;
        
        $orders = $this->model->getLivestreamOrders($seller_id, $status, 100);
        
        return [
            'orders' => $orders,
            'current_status' => $status
        ];
    }

    /**
     * API: Cập nhật trạng thái đơn hàng
     */
    public function updateOrderStatus() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        // Validate CSRF
        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            return;
        }

        $order_id = intval($_POST['order_id'] ?? 0);
        $new_status = Security::sanitize($_POST['status'] ?? '');
        $seller_id = $_SESSION['user_id'];

        // Validate status
        $allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'completed', 'cancelled'];
        if (!in_array($new_status, $allowed_statuses)) {
            echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ']);
            return;
        }

        $result = $this->model->updateOrderStatus($order_id, $seller_id, $new_status);
        echo json_encode($result);
    }

    /**
     * API: Lấy chi tiết đơn hàng
     */
    public function getOrderDetails() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        $order_id = intval($_GET['order_id'] ?? 0);
        $seller_id = $_SESSION['user_id'];

        $items = $this->model->getLivestreamOrderItems($order_id, $seller_id);
        echo json_encode(['success' => true, 'data' => $items]);
    }

    /**
     * API: Lấy dữ liệu biểu đồ
     */
    public function getChartData() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        $seller_id = $_SESSION['user_id'];
        $days = intval($_GET['days'] ?? 30);

        $data = $this->model->getDailyRevenue($seller_id, $days);
        echo json_encode(['success' => true, 'data' => $data]);
    }
}
?>











