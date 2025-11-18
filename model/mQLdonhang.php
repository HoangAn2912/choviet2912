<?php
include_once("mConnect.php");

class mQLdonhang {
    private $conn;
    
    public function __construct() {
        $con = new Connect();
        $this->conn = $con->connect();
    }
    
    // Lấy tất cả đơn hàng với filter
    public function getAllOrders($status = null, $livestream_id = null, $user_id = null, $start_date = null, $end_date = null, $limit = 20, $offset = 0) {
        $sql = "SELECT 
                    lo.*,
                    l.title as livestream_title,
                    l.image as livestream_thumbnail,
                    u.username as buyer_name,
                    u.email as buyer_email,
                    u.phone as buyer_phone,
                    seller.username as seller_name,
                    seller.id as seller_id
                FROM livestream_orders lo
                LEFT JOIN livestream l ON lo.livestream_id = l.id
                LEFT JOIN users u ON lo.user_id = u.id
                LEFT JOIN users seller ON l.user_id = seller.id
                WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if ($status && $status !== 'all') {
            $sql .= " AND lo.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        if ($livestream_id) {
            $sql .= " AND lo.livestream_id = ?";
            $params[] = $livestream_id;
            $types .= "i";
        }
        
        if ($user_id) {
            $sql .= " AND lo.user_id = ?";
            $params[] = $user_id;
            $types .= "i";
        }
        
        if ($start_date) {
            $sql .= " AND DATE(lo.created_at) >= ?";
            $params[] = $start_date;
            $types .= "s";
        }
        
        if ($end_date) {
            $sql .= " AND DATE(lo.created_at) <= ?";
            $params[] = $end_date;
            $types .= "s";
        }
        
        $sql .= " ORDER BY lo.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";
        
        $stmt = $this->conn->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        
        return $orders;
    }
    
    // Đếm tổng số đơn hàng
    public function countOrders($status = null, $livestream_id = null, $user_id = null, $start_date = null, $end_date = null) {
        $sql = "SELECT COUNT(*) as total 
                FROM livestream_orders lo 
                WHERE 1=1";
        $params = [];
        $types = "";
        
        if ($status && $status !== 'all') {
            $sql .= " AND lo.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        if ($livestream_id) {
            $sql .= " AND lo.livestream_id = ?";
            $params[] = $livestream_id;
            $types .= "i";
        }
        
        if ($user_id) {
            $sql .= " AND lo.user_id = ?";
            $params[] = $user_id;
            $types .= "i";
        }
        
        if ($start_date) {
            $sql .= " AND DATE(lo.created_at) >= ?";
            $params[] = $start_date;
            $types .= "s";
        }
        
        if ($end_date) {
            $sql .= " AND DATE(lo.created_at) <= ?";
            $params[] = $end_date;
            $types .= "s";
        }
        
        $stmt = $this->conn->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['total'] ?? 0;
    }
    
    // Lấy thống kê
    public function getStats() {
        $stats = [];
        
        // Tổng đơn hàng
        $result = $this->conn->query("SELECT COUNT(*) as total FROM livestream_orders");
        $stats['total_orders'] = $result->fetch_assoc()['total'];
        
        // Đơn hàng đã xác nhận
        $result = $this->conn->query("SELECT COUNT(*) as total FROM livestream_orders WHERE status = 'confirmed'");
        $stats['confirmed_orders'] = $result->fetch_assoc()['total'];
        
        // Đơn hàng đã giao
        $result = $this->conn->query("SELECT COUNT(*) as total FROM livestream_orders WHERE status = 'delivered'");
        $stats['delivered_orders'] = $result->fetch_assoc()['total'];
        
        // Đơn hàng đã hủy
        $result = $this->conn->query("SELECT COUNT(*) as total FROM livestream_orders WHERE status = 'cancelled'");
        $stats['cancelled_orders'] = $result->fetch_assoc()['total'];
        
        // Tổng doanh thu của hệ thống từ phí gói livestream (không phải từ đơn hàng)
        // Doanh thu = tổng tiền từ các gói livestream đã thanh toán thành công
        $result = $this->conn->query("SELECT COALESCE(SUM(amount), 0) as total 
                                      FROM livestream_payment_history 
                                      WHERE payment_status = 'success'");
        $stats['total_revenue'] = $result->fetch_assoc()['total'];
        
        return $stats;
    }
    
    // Cập nhật trạng thái đơn hàng
    public function updateOrderStatus($order_id, $status) {
        $sql = "UPDATE livestream_orders SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $status, $order_id);
        return $stmt->execute();
    }
    
    // Lấy chi tiết đơn hàng với sản phẩm
    public function getOrderDetails($order_id) {
        try {
            $sql = "SELECT 
                        lo.*,
                        l.title as livestream_title,
                        l.image as livestream_thumbnail,
                        u.username as buyer_name,
                        u.email as buyer_email,
                        u.phone as buyer_phone,
                        seller.username as seller_name,
                        seller.id as seller_id
                    FROM livestream_orders lo
                    LEFT JOIN livestream l ON lo.livestream_id = l.id
                    LEFT JOIN users u ON lo.user_id = u.id
                    LEFT JOIN users seller ON l.user_id = seller.id
                    WHERE lo.id = ?";
            
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->conn->error);
                return false;
            }
            
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $order = $result->fetch_assoc();
            
            if ($order) {
                // Lấy sản phẩm trong đơn hàng
                $items_sql = "SELECT 
                                loi.*,
                                p.title as product_title,
                                p.image as product_image,
                                p.price as product_price
                             FROM livestream_order_items loi
                             LEFT JOIN products p ON loi.product_id = p.id
                             WHERE loi.order_id = ?";
                
                $items_stmt = $this->conn->prepare($items_sql);
                if ($items_stmt) {
                    $items_stmt->bind_param("i", $order_id);
                    $items_stmt->execute();
                    $items_result = $items_stmt->get_result();
                    
                    $items = [];
                    while ($item = $items_result->fetch_assoc()) {
                        // Xử lý ảnh sản phẩm
                        if ($item['product_image']) {
                            $images = explode(',', $item['product_image']);
                            $item['product_image'] = 'img/' . trim($images[0]);
                        } else {
                            $item['product_image'] = 'img/default-product.jpg';
                        }
                        $items[] = $item;
                    }
                    
                    $order['items'] = $items;
                } else {
                    $order['items'] = [];
                }
            }
            
            return $order;
        } catch (Exception $e) {
            error_log("Error in getOrderDetails: " . $e->getMessage());
            return false;
        }
    }
    
    // Lấy danh sách livestream
    public function getAllLivestreams() {
        $result = $this->conn->query("SELECT id, title FROM livestream ORDER BY created_date DESC");
        $livestreams = [];
        while ($row = $result->fetch_assoc()) {
            $livestreams[] = $row;
        }
        return $livestreams;
    }
    
    // Lấy danh sách người dùng
    public function getAllUsers() {
        $result = $this->conn->query("SELECT id, username, email FROM users ORDER BY username");
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        return $users;
    }
}
?>

