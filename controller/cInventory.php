<?php
require_once(__DIR__ . '/../model/mInventory.php');
require_once(__DIR__ . '/../helpers/Security.php');

class cInventory {
    private $model;

    public function __construct() {
        $this->model = new mInventory();
    }

    /**
     * Hiển thị trang quản lý tồn kho
     */
    public function showInventoryDashboard() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: loginlogout/login.php");
            exit;
        }

        $seller_id = $_SESSION['user_id'];
        
        // Lấy dữ liệu
        $products = $this->model->getLivestreamProductsBySeller($seller_id);
        $stats = $this->model->getInventoryStats($seller_id);
        $low_stock = $this->model->getLowStockProducts($seller_id);
        
        return [
            'products' => $products,
            'stats' => $stats,
            'low_stock' => $low_stock
        ];
    }

    /**
     * API: Cập nhật cấu hình tồn kho
     */
    public function updateSettings() {
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

        $product_id = intval($_POST['product_id'] ?? 0);
        $seller_id = $_SESSION['user_id'];

        $data = [
            'is_livestream_product' => intval($_POST['is_livestream_product'] ?? 0),
            'track_inventory' => intval($_POST['track_inventory'] ?? 0),
            'stock_quantity' => intval($_POST['stock_quantity'] ?? 0),
            'low_stock_alert' => intval($_POST['low_stock_alert'] ?? 5)
        ];

        $result = $this->model->updateInventorySettings($product_id, $seller_id, $data);
        echo json_encode($result);
    }

    /**
     * API: Điều chỉnh tồn kho
     */
    public function adjustStock() {
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

        $product_id = intval($_POST['product_id'] ?? 0);
        $seller_id = $_SESSION['user_id'];
        $quantity_change = intval($_POST['quantity_change'] ?? 0);
        $change_type = Security::sanitize($_POST['change_type'] ?? 'adjustment');
        $note = Security::sanitize($_POST['note'] ?? '');

        if ($quantity_change == 0) {
            echo json_encode(['success' => false, 'message' => 'Số lượng không hợp lệ']);
            return;
        }

        // Validate change_type
        $allowed_types = ['restock', 'adjustment'];
        if (!in_array($change_type, $allowed_types)) {
            echo json_encode(['success' => false, 'message' => 'Loại điều chỉnh không hợp lệ']);
            return;
        }

        $result = $this->model->adjustStock($product_id, $seller_id, $quantity_change, $change_type, $note);
        echo json_encode($result);
    }

    /**
     * API: Lấy lịch sử tồn kho
     */
    public function getHistory() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        $product_id = intval($_GET['product_id'] ?? 0);
        $seller_id = $_SESSION['user_id'];

        $history = $this->model->getInventoryHistory($product_id, $seller_id);
        echo json_encode(['success' => true, 'data' => $history]);
    }

    /**
     * API: Kiểm tra tồn kho
     */
    public function checkStock() {
        header('Content-Type: application/json');
        
        $product_id = intval($_GET['product_id'] ?? 0);
        $quantity = intval($_GET['quantity'] ?? 1);

        $result = $this->model->checkStockAvailability($product_id, $quantity);
        echo json_encode($result);
    }
}
?>











