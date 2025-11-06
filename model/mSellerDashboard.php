<?php
require_once(__DIR__ . '/mConnect.php');

/**
 * Model cho Seller Dashboard và Thống kê doanh thu
 */
class mSellerDashboard {
    private $conn;

    public function __construct() {
        $p = new Connect();
        $this->conn = $p->connect();
    }

    /**
     * Lấy tổng quan doanh thu
     */
    public function getRevenueSummary($seller_id, $days = 30) {
        $sql = "SELECT 
                    -- Livestream revenue
                    COALESCE(SUM(CASE 
                        WHEN lo.order_status IN ('completed', 'delivered') 
                             AND lo.created_date >= DATE_SUB(NOW(), INTERVAL ? DAY)
                        THEN lo.total_amount 
                        ELSE 0 
                    END), 0) AS livestream_revenue,
                    
                    COUNT(DISTINCT CASE 
                        WHEN lo.order_status IN ('completed', 'delivered')
                             AND lo.created_date >= DATE_SUB(NOW(), INTERVAL ? DAY)
                        THEN lo.id 
                    END) AS livestream_orders_count,
                    
                    -- C2C revenue (from negotiated price)
                    COALESCE(SUM(CASE 
                        WHEN co.order_status IN ('completed', 'delivered')
                             AND co.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                        THEN co.negotiated_price * co.quantity
                        ELSE 0 
                    END), 0) AS c2c_revenue,
                    
                    COUNT(DISTINCT CASE 
                        WHEN co.order_status IN ('completed', 'delivered')
                             AND co.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                        THEN co.id 
                    END) AS c2c_orders_count,
                    
                    -- Pending orders
                    COUNT(DISTINCT CASE 
                        WHEN lo.order_status IN ('pending', 'processing')
                        THEN lo.id 
                    END) AS livestream_pending_count,
                    
                    COUNT(DISTINCT CASE 
                        WHEN co.order_status IN ('pending_payment', 'pending_shipment')
                        THEN co.id 
                    END) AS c2c_pending_count
                    
                FROM users u
                LEFT JOIN livestream_orders lo ON u.id = lo.seller_id
                LEFT JOIN c2c_orders co ON u.id = co.seller_id
                WHERE u.id = ?
                GROUP BY u.id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiii", $days, $days, $days, $days, $seller_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        // Calculate total
        $result['total_revenue'] = $result['livestream_revenue'] + $result['c2c_revenue'];
        $result['total_orders'] = $result['livestream_orders_count'] + $result['c2c_orders_count'];
        $result['total_pending'] = $result['livestream_pending_count'] + $result['c2c_pending_count'];
        
        return $result;
    }

    /**
     * Lấy doanh thu theo ngày (cho biểu đồ)
     */
    public function getDailyRevenue($seller_id, $days = 30) {
        $sql = "SELECT 
                    DATE(created_date) AS date,
                    SUM(total_amount) AS revenue,
                    COUNT(*) AS orders
                FROM livestream_orders
                WHERE seller_id = ? 
                  AND order_status IN ('completed', 'delivered')
                  AND created_date >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_date)
                
                UNION ALL
                
                SELECT 
                    DATE(created_at) AS date,
                    SUM(negotiated_price * quantity) AS revenue,
                    COUNT(*) AS orders
                FROM c2c_orders
                WHERE seller_id = ? 
                  AND order_status IN ('completed', 'delivered')
                  AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                
                ORDER BY date DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiii", $seller_id, $days, $seller_id, $days);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }

    /**
     * Top sản phẩm bán chạy
     */
    public function getTopProducts($seller_id, $limit = 10) {
        $sql = "SELECT 
                    p.id,
                    p.title,
                    p.price,
                    p.image,
                    COALESCE(SUM(loi.quantity), 0) AS total_sold_livestream,
                    COALESCE(SUM(CASE WHEN co.order_status IN ('completed', 'delivered') THEN co.quantity ELSE 0 END), 0) AS total_sold_c2c,
                    COALESCE(SUM(loi.quantity * loi.price), 0) + COALESCE(SUM(CASE WHEN co.order_status IN ('completed', 'delivered') THEN co.negotiated_price * co.quantity ELSE 0 END), 0) AS total_revenue
                FROM products p
                LEFT JOIN livestream_order_items loi ON p.id = loi.product_id
                LEFT JOIN livestream_orders lo ON loi.order_id = lo.id AND lo.order_status IN ('completed', 'delivered')
                LEFT JOIN c2c_orders co ON p.id = co.product_id
                WHERE p.user_id = ?
                GROUP BY p.id, p.title, p.price, p.image
                HAVING total_revenue > 0
                ORDER BY total_revenue DESC
                LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $seller_id, $limit);
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
            $row['total_sold'] = $row['total_sold_livestream'] + $row['total_sold_c2c'];
            $data[] = $row;
        }
        
        return $data;
    }

    /**
     * Lấy danh sách đơn hàng livestream
     */
    public function getLivestreamOrders($seller_id, $status = null, $limit = 50) {
        $where_clause = $status ? "AND lo.order_status = ?" : "";
        
        $sql = "SELECT 
                    lo.*,
                    u.username AS buyer_name,
                    u.email AS buyer_email,
                    u.phone AS buyer_phone,
                    COUNT(loi.id) AS items_count
                FROM livestream_orders lo
                JOIN users u ON lo.buyer_id = u.id
                LEFT JOIN livestream_order_items loi ON lo.id = loi.order_id
                WHERE lo.seller_id = ? $where_clause
                GROUP BY lo.id
                ORDER BY lo.created_date DESC
                LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        
        if ($status) {
            $stmt->bind_param("isi", $seller_id, $status, $limit);
        } else {
            $stmt->bind_param("ii", $seller_id, $limit);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }

    /**
     * Lấy chi tiết items của đơn hàng livestream
     */
    public function getLivestreamOrderItems($order_id, $seller_id) {
        // Verify ownership
        $check_sql = "SELECT seller_id FROM livestream_orders WHERE id = ?";
        $check_stmt = $this->conn->prepare($check_sql);
        $check_stmt->bind_param("i", $order_id);
        $check_stmt->execute();
        $order = $check_stmt->get_result()->fetch_assoc();
        
        if (!$order || $order['seller_id'] != $seller_id) {
            return [];
        }

        $sql = "SELECT 
                    loi.*,
                    p.title AS product_name,
                    p.image AS product_image
                FROM livestream_order_items loi
                JOIN products p ON loi.product_id = p.id
                WHERE loi.order_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            // Process image
            if (!empty($row['product_image'])) {
                $images = array_map('trim', explode(',', $row['product_image']));
                $row['first_image'] = $images[0] ?? '';
            } else {
                $row['first_image'] = '';
            }
            $data[] = $row;
        }
        
        return $data;
    }

    /**
     * Cập nhật trạng thái đơn hàng livestream
     */
    public function updateOrderStatus($order_id, $seller_id, $new_status) {
        // Verify ownership
        $check_sql = "SELECT seller_id FROM livestream_orders WHERE id = ?";
        $check_stmt = $this->conn->prepare($check_sql);
        $check_stmt->bind_param("i", $order_id);
        $check_stmt->execute();
        $order = $check_stmt->get_result()->fetch_assoc();
        
        if (!$order || $order['seller_id'] != $seller_id) {
            return ['success' => false, 'message' => 'Không có quyền'];
        }

        $sql = "UPDATE livestream_orders SET order_status = ?, updated_date = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $new_status, $order_id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Cập nhật thành công'];
        } else {
            return ['success' => false, 'message' => 'Lỗi cập nhật'];
        }
    }

    /**
     * Thống kê theo tháng (cho admin hoặc seller xem tổng quan dài hạn)
     */
    public function getMonthlyStats($seller_id, $months = 12) {
        $sql = "SELECT 
                    DATE_FORMAT(created_date, '%Y-%m') AS month,
                    SUM(total_amount) AS revenue,
                    COUNT(*) AS orders
                FROM livestream_orders
                WHERE seller_id = ? 
                  AND order_status IN ('completed', 'delivered')
                  AND created_date >= DATE_SUB(NOW(), INTERVAL ? MONTH)
                GROUP BY DATE_FORMAT(created_date, '%Y-%m')
                ORDER BY month DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $seller_id, $months);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }

    /**
     * Thống kê review ratings
     */
    public function getReviewStats($seller_id) {
        $sql = "SELECT * FROM v_seller_ratings WHERE seller_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $seller_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        return $result;
    }
}
?>











