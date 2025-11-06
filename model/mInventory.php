<?php
require_once(__DIR__ . '/mConnect.php');

/**
 * Model quản lý tồn kho sản phẩm livestream
 */
class mInventory {
    private $conn;

    public function __construct() {
        $p = new Connect();
        $this->conn = $p->connect();
    }

    /**
     * Lấy báo cáo tồn kho của seller
     */
    public function getInventoryBySeller($seller_id) {
        $sql = "SELECT * FROM v_inventory_report WHERE seller_id = ? ORDER BY stock_status DESC, product_name ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $seller_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }

    /**
     * Lấy tất cả sản phẩm livestream của seller (để quản lý)
     */
    public function getLivestreamProductsBySeller($seller_id) {
        $sql = "SELECT id, title, price, stock_quantity, low_stock_alert, track_inventory, image
                FROM products 
                WHERE user_id = ? AND is_livestream_product = 1
                ORDER BY created_date DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $seller_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            // Process image
            if (!empty($row['image'])) {
                $images = array_map('trim', explode(',', $row['image']));
                $row['first_image'] = $images[0] ?? '';
            } else {
                $row['first_image'] = '';
            }
            $data[] = $row;
        }
        
        return $data;
    }

    /**
     * Cập nhật cấu hình tồn kho cho sản phẩm
     */
    public function updateInventorySettings($product_id, $seller_id, $data) {
        // Kiểm tra quyền sở hữu
        $check_sql = "SELECT user_id FROM products WHERE id = ?";
        $check_stmt = $this->conn->prepare($check_sql);
        $check_stmt->bind_param("i", $product_id);
        $check_stmt->execute();
        $product = $check_stmt->get_result()->fetch_assoc();
        
        if (!$product || $product['user_id'] != $seller_id) {
            return ['success' => false, 'message' => 'Không có quyền'];
        }

        $sql = "UPDATE products 
                SET is_livestream_product = ?,
                    track_inventory = ?,
                    stock_quantity = ?,
                    low_stock_alert = ?
                WHERE id = ? AND user_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "iiiiii",
            $data['is_livestream_product'],
            $data['track_inventory'],
            $data['stock_quantity'],
            $data['low_stock_alert'],
            $product_id,
            $seller_id
        );
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Cập nhật thành công'];
        } else {
            return ['success' => false, 'message' => 'Lỗi cập nhật: ' . $stmt->error];
        }
    }

    /**
     * Điều chỉnh tồn kho thủ công (restock/adjustment)
     */
    public function adjustStock($product_id, $seller_id, $quantity_change, $change_type, $note) {
        try {
            $this->conn->begin_transaction();

            // Kiểm tra quyền sở hữu
            $check_sql = "SELECT user_id, stock_quantity, track_inventory FROM products WHERE id = ?";
            $check_stmt = $this->conn->prepare($check_sql);
            $check_stmt->bind_param("i", $product_id);
            $check_stmt->execute();
            $product = $check_stmt->get_result()->fetch_assoc();
            
            if (!$product || $product['user_id'] != $seller_id) {
                throw new Exception('Không có quyền');
            }

            if ($product['track_inventory'] != 1) {
                throw new Exception('Sản phẩm không có quản lý tồn kho');
            }

            // Gọi stored procedure
            $proc_sql = "CALL update_product_stock(?, ?, ?, ?, ?, NULL)";
            $proc_stmt = $this->conn->prepare($proc_sql);
            $proc_stmt->bind_param("iissi", $product_id, $quantity_change, $change_type, $note, $seller_id);
            $proc_stmt->execute();

            $this->conn->commit();
            return ['success' => true, 'message' => 'Cập nhật tồn kho thành công'];

        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Lấy lịch sử biến động tồn kho
     */
    public function getInventoryHistory($product_id, $seller_id, $limit = 50) {
        // Kiểm tra quyền
        $check_sql = "SELECT user_id FROM products WHERE id = ?";
        $check_stmt = $this->conn->prepare($check_sql);
        $check_stmt->bind_param("i", $product_id);
        $check_stmt->execute();
        $product = $check_stmt->get_result()->fetch_assoc();
        
        if (!$product || $product['user_id'] != $seller_id) {
            return [];
        }

        $sql = "SELECT h.*, u.username AS created_by_name
                FROM inventory_history h
                LEFT JOIN users u ON h.created_by = u.id
                WHERE h.product_id = ?
                ORDER BY h.created_at DESC
                LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $product_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }

    /**
     * Kiểm tra sản phẩm còn hàng không
     */
    public function checkStockAvailability($product_id, $quantity_needed) {
        $sql = "SELECT stock_quantity, track_inventory FROM products WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        
        if (!$product) {
            return ['available' => false, 'message' => 'Sản phẩm không tồn tại'];
        }

        // Nếu không track inventory, luôn có hàng
        if ($product['track_inventory'] != 1) {
            return ['available' => true, 'message' => 'OK'];
        }

        // Nếu track inventory, kiểm tra số lượng
        if ($product['stock_quantity'] === null || $product['stock_quantity'] >= $quantity_needed) {
            return ['available' => true, 'message' => 'OK'];
        } else {
            return [
                'available' => false, 
                'message' => 'Chỉ còn ' . $product['stock_quantity'] . ' sản phẩm'
            ];
        }
    }

    /**
     * Lấy thống kê tồn kho tổng quan
     */
    public function getInventoryStats($seller_id) {
        $sql = "SELECT 
                    COUNT(*) AS total_products,
                    SUM(CASE WHEN stock_quantity = 0 THEN 1 ELSE 0 END) AS out_of_stock,
                    SUM(CASE WHEN stock_quantity <= low_stock_alert AND stock_quantity > 0 THEN 1 ELSE 0 END) AS low_stock,
                    SUM(CASE WHEN stock_quantity > low_stock_alert THEN 1 ELSE 0 END) AS in_stock,
                    SUM(stock_quantity) AS total_stock_quantity
                FROM products
                WHERE user_id = ? AND is_livestream_product = 1 AND track_inventory = 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $seller_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Lấy danh sách sản phẩm sắp hết hàng
     */
    public function getLowStockProducts($seller_id) {
        $sql = "SELECT id, title, price, stock_quantity, low_stock_alert, image
                FROM products
                WHERE user_id = ? 
                  AND is_livestream_product = 1 
                  AND track_inventory = 1
                  AND stock_quantity <= low_stock_alert 
                  AND stock_quantity > 0
                ORDER BY stock_quantity ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $seller_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            // Process image
            if (!empty($row['image'])) {
                $images = array_map('trim', explode(',', $row['image']));
                $row['first_image'] = $images[0] ?? '';
            } else {
                $row['first_image'] = '';
            }
            $data[] = $row;
        }
        
        return $data;
    }
}
?>











