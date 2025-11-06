<?php
include_once "mConnect.php";

class mLivestream {
    private $conn;

    public function __construct() {
        $p = new Connect();
        $this->conn = $p->connect();
    }

    // =============================================
    // QUẢN LÝ LIVESTREAM
    // =============================================

    // Lấy danh sách livestream theo user_id
    public function getLivestreamsByUserId($user_id) {
        $sql = "SELECT l.*, u.username, u.avatar, u.phone,
                       COUNT(DISTINCT lv.user_id) as current_viewers,
                       COUNT(DISTINCT lp.id) as product_count
                FROM livestream l 
                LEFT JOIN users u ON l.user_id = u.id 
                LEFT JOIN livestream_viewers lv ON l.id = lv.livestream_id 
                LEFT JOIN livestream_products lp ON l.id = lp.livestream_id
                WHERE l.user_id = ?
                GROUP BY l.id 
                ORDER BY l.created_date DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $livestreams = [];
        while ($row = $result->fetch_assoc()) {
            $livestreams[] = $row;
        }
        return $livestreams;
    }

    // Lấy danh sách livestream
    public function getLivestreams($status = null, $limit = 20) {
        $sql = "SELECT l.*, u.username, u.avatar, 
                       COUNT(DISTINCT lv.user_id) as current_viewers,
                       COUNT(DISTINCT lp.id) as product_count
                FROM livestream l 
                LEFT JOIN users u ON l.user_id = u.id 
                LEFT JOIN livestream_viewers lv ON l.id = lv.livestream_id 
                LEFT JOIN livestream_products lp ON l.id = lp.livestream_id";
        
        $conditions = [];
        if ($status) {
            $conditions[] = "l.status = ?";
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " GROUP BY l.id ORDER BY l.created_date DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . intval($limit);
        }

        $stmt = $this->conn->prepare($sql);
        if ($status) {
            $stmt->bind_param("s", $status);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $livestreams = [];
        while ($row = $result->fetch_assoc()) {
            $livestreams[] = $row;
        }
        return $livestreams;
    }

    // Lấy thông tin livestream theo ID
    public function getLivestreamById($id) {
        $sql = "SELECT l.*, u.username, u.avatar, u.phone
                FROM livestream l 
                LEFT JOIN users u ON l.user_id = u.id 
                WHERE l.id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    // Tạo livestream mới
    public function createLivestream($data) {
        $sql = "INSERT INTO livestream (user_id, title, description, start_time, end_time, status, image) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issssss", 
            $data['user_id'], 
            $data['title'], 
            $data['description'], 
            $data['start_time'], 
            $data['end_time'], 
            $data['status'], 
            $data['image']
        );
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    // Cập nhật trạng thái livestream
    public function updateLivestreamStatus($id, $status) {
        $sql = "UPDATE livestream SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    // =============================================
    // QUẢN LÝ SẢN PHẨM TRONG LIVESTREAM
    // =============================================

    // Thêm sản phẩm vào livestream
    public function addProductToLivestream($livestream_id, $product_id, $special_price = null, $stock_quantity = 1) {
        // Kiểm tra xem sản phẩm đã có trong livestream chưa
        $check_sql = "SELECT id FROM livestream_products WHERE livestream_id = ? AND product_id = ?";
        $check_stmt = $this->conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $livestream_id, $product_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            return false; // Sản phẩm đã tồn tại
        }
        
        $sql = "INSERT INTO livestream_products (livestream_id, product_id, special_price, stock_quantity, is_pinned, created_date) 
                VALUES (?, ?, ?, ?, 0, NOW())";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iidi", $livestream_id, $product_id, $special_price, $stock_quantity);
        return $stmt->execute();
    }

    // Lấy danh sách sản phẩm trong livestream
    public function getLivestreamProducts($livestream_id) {
        $sql = "SELECT lp.*, p.title, p.price, p.image, p.description
                FROM livestream_products lp 
                LEFT JOIN products p ON lp.product_id = p.id 
                WHERE lp.livestream_id = ? 
                ORDER BY lp.id ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $livestream_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            // Xử lý ảnh sản phẩm giống như trong mProduct
            if (!empty($row['image'])) {
                $dsAnh = array_map('trim', explode(',', $row['image']));
                $row['anh_dau'] = $dsAnh[0] ?? ''; // ảnh đầu tiên
            } else {
                $row['anh_dau'] = '';
            }
            $products[] = $row;
        }
        return $products;
    }

    // Ghim sản phẩm
    public function pinProduct($livestream_id, $product_id) {
        // Bỏ ghim tất cả sản phẩm khác
        $sql1 = "UPDATE livestream_products SET is_pinned = 0, pinned_at = NULL WHERE livestream_id = ?";
        $stmt1 = $this->conn->prepare($sql1);
        $stmt1->bind_param("i", $livestream_id);
        $stmt1->execute();

        // Ghim sản phẩm mới
        $sql2 = "UPDATE livestream_products SET is_pinned = 1, pinned_at = NOW() 
                 WHERE livestream_id = ? AND product_id = ?";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->bind_param("ii", $livestream_id, $product_id);
        return $stmt2->execute();
    }

    // Lấy sản phẩm đang được ghim
    public function getPinnedProduct($livestream_id) {
        $sql = "SELECT lp.*, p.title, p.price, p.image, p.description
                FROM livestream_products lp 
                LEFT JOIN products p ON lp.product_id = p.id 
                WHERE lp.livestream_id = ? AND lp.is_pinned = 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $livestream_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row) {
            // Xử lý ảnh sản phẩm giống như trong mProduct
            if (!empty($row['image'])) {
                $dsAnh = array_map('trim', explode(',', $row['image']));
                $row['anh_dau'] = $dsAnh[0] ?? ''; // ảnh đầu tiên
            } else {
                $row['anh_dau'] = '';
            }
        }
        
        return $row;
    }

    // =============================================
    // QUẢN LÝ GIỎ HÀNG LIVESTREAM
    // =============================================

    // Thêm sản phẩm vào giỏ hàng
    public function addToCart($user_id, $livestream_id, $product_id, $quantity = 1) {
        // Lấy giá sản phẩm (ưu tiên giá đặc biệt)
        $price_sql = "SELECT COALESCE(lp.special_price, p.price) as price 
                      FROM livestream_products lp 
                      LEFT JOIN products p ON lp.product_id = p.id 
                      WHERE lp.livestream_id = ? AND lp.product_id = ?";
        
        $price_stmt = $this->conn->prepare($price_sql);
        $price_stmt->bind_param("ii", $livestream_id, $product_id);
        $price_stmt->execute();
        $price_result = $price_stmt->get_result();
        $price_data = $price_result->fetch_assoc();
        
        if (!$price_data) {
            return false;
        }
        
        $price = $price_data['price'];
        
        // Kiểm tra xem sản phẩm đã có trong giỏ chưa
        $check_sql = "SELECT id, quantity FROM livestream_cart_items 
                      WHERE user_id = ? AND livestream_id = ? AND product_id = ?";
        $check_stmt = $this->conn->prepare($check_sql);
        $check_stmt->bind_param("iii", $user_id, $livestream_id, $product_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($existing = $check_result->fetch_assoc()) {
            // Cập nhật số lượng
            $new_quantity = $existing['quantity'] + $quantity;
            $update_sql = "UPDATE livestream_cart_items SET quantity = ?, price = ? WHERE id = ?";
            $update_stmt = $this->conn->prepare($update_sql);
            $update_stmt->bind_param("idi", $new_quantity, $price, $existing['id']);
            return $update_stmt->execute();
        } else {
            // Thêm mới
            $insert_sql = "INSERT INTO livestream_cart_items (user_id, livestream_id, product_id, quantity, price) 
                           VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = $this->conn->prepare($insert_sql);
            $insert_stmt->bind_param("iiiid", $user_id, $livestream_id, $product_id, $quantity, $price);
            return $insert_stmt->execute();
        }
    }

    // Lấy giỏ hàng của user trong livestream
    public function getCart($user_id, $livestream_id) {
        $sql = "SELECT lci.*, p.title, p.image, p.description
                FROM livestream_cart_items lci 
                LEFT JOIN products p ON lci.product_id = p.id 
                WHERE lci.user_id = ? AND lci.livestream_id = ? 
                ORDER BY lci.added_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $livestream_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $cart = [];
        $total = 0;
        while ($row = $result->fetch_assoc()) {
            // Xử lý ảnh sản phẩm giống như trong mProduct
            if (!empty($row['image'])) {
                $dsAnh = array_map('trim', explode(',', $row['image']));
                $row['anh_dau'] = $dsAnh[0] ?? ''; // ảnh đầu tiên
            } else {
                $row['anh_dau'] = '';
            }
            
            $row['subtotal'] = $row['quantity'] * $row['price'];
            $total += $row['subtotal'];
            $cart[] = $row;
        }
        
        return [
            'items' => $cart,
            'total' => $total,
            'item_count' => count($cart)
        ];
    }

    // Cập nhật số lượng sản phẩm trong giỏ hàng
    public function updateCartItemQuantity($item_id, $quantity, $user_id, $livestream_id) {
        if ($quantity <= 0) {
            // Xóa item nếu số lượng <= 0
            $delete_sql = "DELETE FROM livestream_cart_items WHERE id = ? AND user_id = ? AND livestream_id = ?";
            $delete_stmt = $this->conn->prepare($delete_sql);
            $delete_stmt->bind_param("iii", $item_id, $user_id, $livestream_id);
            return $delete_stmt->execute();
        } else {
            // Cập nhật số lượng
            $update_sql = "UPDATE livestream_cart_items SET quantity = ? WHERE id = ? AND user_id = ? AND livestream_id = ?";
            $update_stmt = $this->conn->prepare($update_sql);
            $update_stmt->bind_param("iiii", $quantity, $item_id, $user_id, $livestream_id);
            return $update_stmt->execute();
        }
    }

    // Xóa sản phẩm khỏi giỏ hàng
    public function removeFromCart($user_id, $livestream_id, $product_id) {
        $sql = "DELETE FROM livestream_cart_items 
                WHERE user_id = ? AND livestream_id = ? AND product_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $user_id, $livestream_id, $product_id);
        return $stmt->execute();
    }

    // Cập nhật số lượng trong giỏ hàng
    public function updateCartQuantity($user_id, $livestream_id, $product_id, $quantity) {
        if ($quantity <= 0) {
            return $this->removeFromCart($user_id, $livestream_id, $product_id);
        }
        
        $sql = "UPDATE livestream_cart_items SET quantity = ? 
                WHERE user_id = ? AND livestream_id = ? AND product_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiii", $quantity, $user_id, $livestream_id, $product_id);
        return $stmt->execute();
    }

    // =============================================
    // QUẢN LÝ ĐƠN HÀNG
    // =============================================

    // Tạo đơn hàng từ giỏ hàng
    public function createOrder($user_id, $livestream_id, $cart_items, $payment_method = 'vnpay', $address_data = []) {
        $this->conn->begin_transaction();
        
        try {
            // Tạo mã đơn hàng
            $order_code = 'LIVE' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Tính tổng tiền
            $total_amount = 0;
            foreach ($cart_items as $item) {
                $total_amount += $item['quantity'] * $item['price'];
            }
            
            // Tạo đơn hàng
            $order_sql = "INSERT INTO livestream_orders (order_code, user_id, livestream_id, total_amount, payment_method, delivery_name, delivery_phone, delivery_province, delivery_district, delivery_ward, delivery_street, delivery_address, status) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
            $order_stmt = $this->conn->prepare($order_sql);
            
            // Tạo biến riêng để tránh lỗi bind_param
            $delivery_name = $address_data['full_name'] ?? '';
            $delivery_phone = $address_data['phone'] ?? '';
            $delivery_province = $address_data['province'] ?? '';
            $delivery_district = $address_data['district'] ?? '';
            $delivery_ward = $address_data['ward'] ?? '';
            $delivery_street = $address_data['street'] ?? '';
            $delivery_address = $address_data['address'] ?? '';
            
            $order_stmt->bind_param("siidssssssss", 
                $order_code, $user_id, $livestream_id, $total_amount, $payment_method,
                $delivery_name, $delivery_phone, $delivery_province, $delivery_district, $delivery_ward, $delivery_street, $delivery_address
            );
            $order_stmt->execute();
            
            $order_id = $this->conn->insert_id;
            
            // Tạo chi tiết đơn hàng
            foreach ($cart_items as $item) {
                $item_sql = "INSERT INTO livestream_order_items (order_id, product_id, quantity, price) 
                             VALUES (?, ?, ?, ?)";
                $item_stmt = $this->conn->prepare($item_sql);
                $item_stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                $item_stmt->execute();
            }
            
            // Xóa giỏ hàng
            $clear_sql = "DELETE FROM livestream_cart_items WHERE user_id = ? AND livestream_id = ?";
            $clear_stmt = $this->conn->prepare($clear_sql);
            $clear_stmt->bind_param("ii", $user_id, $livestream_id);
            $clear_stmt->execute();
            
            $this->conn->commit();
            
            // 📧 GỬI EMAIL THÔNG BÁO CHO SELLER
            $this->sendOrderNotificationEmail($order_id, $livestream_id, $order_code, $total_amount, $cart_items, $delivery_name, $delivery_phone);
            
            return $order_id;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error creating order: " . $e->getMessage());
            error_log("Order data - User ID: $user_id, Livestream ID: $livestream_id, Payment Method: $payment_method");
            error_log("Address data: " . json_encode($address_data));
            error_log("Cart items: " . json_encode($cart_items));
            return false;
        }
    }

    // Lấy thông tin đơn hàng
    public function getOrder($order_id) {
        $sql = "SELECT lo.*, u.username, u.phone, u.email, l.title as livestream_title
                FROM livestream_orders lo 
                LEFT JOIN users u ON lo.user_id = u.id 
                LEFT JOIN livestream l ON lo.livestream_id = l.id 
                WHERE lo.id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    // Cập nhật trạng thái đơn hàng
    public function updateOrderStatus($order_id, $status, $vnpay_txn_ref = null) {
        $sql = "UPDATE livestream_orders SET status = ?, vnpay_txn_ref = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $status, $vnpay_txn_ref, $order_id);
        return $stmt->execute();
    }

    // =============================================
    // QUẢN LÝ VIEWERS VÀ TƯƠNG TÁC
    // =============================================

    // Thêm viewer vào livestream
    public function addViewer($livestream_id, $user_id) {
        $sql = "INSERT INTO livestream_viewers (livestream_id, user_id) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE last_activity = NOW()";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $livestream_id, $user_id);
        return $stmt->execute();
    }

    // Lấy số lượng viewers thực tế dựa trên hoạt động gần đây
    public function getViewerCount($livestream_id) {
        // Đếm số lượng người xem dựa trên hoạt động trong 5 phút gần đây
        $sql = "SELECT COUNT(DISTINCT user_id) as count FROM (
                    SELECT user_id FROM livestream_messages 
                    WHERE livestream_id = ? AND created_time > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                    UNION
                    SELECT user_id FROM livestream_interactions 
                    WHERE livestream_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                    UNION
                    SELECT user_id FROM livestream_viewers 
                    WHERE livestream_id = ? AND last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                ) as active_viewers";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $livestream_id, $livestream_id, $livestream_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        $real_count = $data['count'] ?? 0;
        
        // Nếu không có hoạt động gần đây, sử dụng số từ bảng livestream
        if ($real_count == 0) {
            $sql = "SELECT viewer_count FROM livestream WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $livestream_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            $real_count = $data['viewer_count'] ?? 0;
        }
        
        return $real_count;
    }

    // Lấy số lượng viewers hiện tại (bao gồm cả guest)
    public function getCurrentViewerCount($livestream_id) {
        // Đếm tất cả viewers đang active (trong 5 phút gần đây)
        $sql = "SELECT COUNT(*) as count FROM livestream_viewers 
                WHERE livestream_id = ? AND last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $livestream_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        return $data['count'] ?? 0;
    }

    // Ghi nhận user join livestream (bao gồm cả guest)
    public function recordViewerJoin($livestream_id, $user_id, $session_id = null) {
        // Nếu là guest user, sử dụng session_id
        $identifier = $user_id > 0 ? $user_id : $session_id;
        $is_guest = $user_id <= 0;
        
        if (!$identifier) {
            return false;
        }
        
        // Kiểm tra xem user đã join chưa
        $check_sql = "SELECT id FROM livestream_viewers WHERE livestream_id = ? AND user_id = ?";
        $check_stmt = $this->conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $livestream_id, $identifier);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Cập nhật last_activity
            $update_sql = "UPDATE livestream_viewers SET last_activity = NOW() WHERE livestream_id = ? AND user_id = ?";
            $update_stmt = $this->conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $livestream_id, $identifier);
            return $update_stmt->execute();
        } else {
            // Thêm mới
            $insert_sql = "INSERT INTO livestream_viewers (livestream_id, user_id, joined_at, last_activity) 
                          VALUES (?, ?, NOW(), NOW())";
            $insert_stmt = $this->conn->prepare($insert_sql);
            $insert_stmt->bind_param("ii", $livestream_id, $identifier);
            return $insert_stmt->execute();
        }
    }

    // Hủy đơn hàng
    public function cancelOrder($order_id, $user_id) {
        // Kiểm tra đơn hàng có tồn tại và thuộc về user không
        $check_sql = "SELECT id, status, total_amount, payment_method FROM livestream_orders 
                      WHERE id = ? AND user_id = ?";
        $check_stmt = $this->conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $order_id, $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows == 0) {
            return false;
        }
        
        $order = $result->fetch_assoc();
        
        // Chỉ cho phép hủy khi đơn hàng ở trạng thái pending hoặc confirmed
        if (!in_array($order['status'], ['pending', 'confirmed'])) {
            return false;
        }
        
        // Bắt đầu transaction
        $this->conn->begin_transaction();
        error_log("Starting cancel order transaction for order_id: $order_id, user_id: $user_id");
        
        try {
            // Cập nhật trạng thái đơn hàng thành cancelled
            $update_sql = "UPDATE livestream_orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?";
            $update_stmt = $this->conn->prepare($update_sql);
            $update_stmt->bind_param("i", $order_id);
            $update_stmt->execute();
            error_log("Order status updated to cancelled for order_id: $order_id");
            
            // Nếu thanh toán bằng ví, hoàn tiền lại vào transfer_accounts
            if ($order['payment_method'] == 'wallet') {
                error_log("Processing wallet refund for order_id: $order_id, amount: " . $order['total_amount']);
                // Sử dụng PDO connection cho transfer_accounts
                try {
                    // Tạo PDO connection riêng cho transfer_accounts
                    $pdo = new PDO("mysql:host=localhost;dbname=choviet29", "root", "");
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    // Bắt đầu transaction cho transfer_accounts
                    $pdo->beginTransaction();
                    
                    // Kiểm tra xem tài khoản transfer_accounts có tồn tại không
                    $check_account_sql = "SELECT id FROM transfer_accounts WHERE user_id = ?";
                    $check_account_stmt = $pdo->prepare($check_account_sql);
                    $check_account_stmt->execute([$user_id]);
                    $account_exists = $check_account_stmt->rowCount() > 0;
                    
                    if ($account_exists) {
                        // Cập nhật số dư
                        $refund_sql = "UPDATE transfer_accounts SET balance = balance + ? WHERE user_id = ?";
                        $refund_stmt = $pdo->prepare($refund_sql);
                        $refund_stmt->execute([$order['total_amount'], $user_id]);
                    } else {
                        // Tạo tài khoản mới với số dư hoàn tiền
                        $create_account_sql = "INSERT INTO transfer_accounts (account_number, user_id, balance) VALUES (?, ?, ?)";
                        $create_account_stmt = $pdo->prepare($create_account_sql);
                        $account_number = 'REFUND_' . $user_id . '_' . time();
                        $create_account_stmt->execute([$account_number, $user_id, $order['total_amount']]);
                    }
                    
                    // Commit transaction cho transfer_accounts
                    $pdo->commit();
                    error_log("Transfer account refund successful for user_id: $user_id, amount: " . $order['total_amount']);
                    
                    // Đánh dấu đã hoàn tiền thành công
                    $refund_successful = true;
                    
                } catch (Exception $e) {
                    // Rollback transaction cho transfer_accounts nếu có lỗi
                    if (isset($pdo)) {
                        $pdo->rollback();
                    }
                    error_log("Transfer account error: " . $e->getMessage());
                    $refund_successful = false;
                    // Không throw exception, chỉ log lỗi
                }
            } else {
                $refund_successful = true; // Không cần hoàn tiền
            }
            
            // Commit transaction chính
            try {
                $this->conn->commit();
                error_log("Cancel order successful for order_id: $order_id, user_id: $user_id");
                return true;
            } catch (Exception $e) {
                error_log("Error committing main transaction: " . $e->getMessage());
                // Nếu đã hoàn tiền thành công, vẫn trả về true
                if (isset($refund_successful) && $refund_successful) {
                    error_log("Main transaction failed but refund was successful, returning true");
                    return true;
                }
                throw $e;
            }
            
        } catch (Exception $e) {
            // Rollback nếu có lỗi
            $this->conn->rollback();
            error_log("Cancel order error: " . $e->getMessage());
            error_log("Cancel order stack trace: " . $e->getTraceAsString());
            
            // Nếu đã hoàn tiền thành công, vẫn trả về true
            if (isset($refund_successful) && $refund_successful) {
                error_log("Exception occurred but refund was successful, returning true");
                return true;
            }
            
            return false;
        }
    }

    // Lấy thông tin đơn hàng
    public function getOrderInfo($order_id, $user_id) {
        $sql = "SELECT payment_method, total_amount FROM livestream_orders WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $order_id, $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Kiểm tra tài khoản transfer_accounts
    public function checkTransferAccount($user_id) {
        $sql = "SELECT balance FROM transfer_accounts WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Ghi nhận tương tác
    public function recordInteraction($livestream_id, $user_id, $action_type) {
        $sql = "INSERT INTO livestream_interactions (livestream_id, user_id, action_type) 
                VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iis", $livestream_id, $user_id, $action_type);
        return $stmt->execute();
    }

    // Lấy thống kê livestream
    public function getLivestreamStats($livestream_id) {
        $sql = "SELECT 
                    COUNT(DISTINCT lv.user_id) as total_viewers,
                    COUNT(DISTINCT lo.id) as total_orders,
                    COALESCE(SUM(lo.total_amount), 0) as total_revenue,
                    COUNT(DISTINCT CASE WHEN li.action_type = 'like' THEN li.user_id END) as total_likes
                FROM livestream l
                LEFT JOIN livestream_viewers lv ON l.id = lv.livestream_id
                LEFT JOIN livestream_orders lo ON l.id = lo.livestream_id AND lo.status != 'cancelled'
                LEFT JOIN livestream_interactions li ON l.id = li.livestream_id
                WHERE l.id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $livestream_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    // Lấy danh sách đơn hàng của user
    public function getUserOrders($user_id, $status_filter = null, $limit = 20, $offset = 0) {
        $sql = "SELECT DISTINCT
                    lo.*,
                    l.title as livestream_title,
                    l.image as livestream_thumbnail,
                    u.username as streamer_name
                FROM livestream_orders lo
                LEFT JOIN livestream l ON lo.livestream_id = l.id
                LEFT JOIN users u ON l.user_id = u.id
                WHERE lo.user_id = ?";
        
        $params = [$user_id];
        $param_types = "i";
        
        // Thêm filter theo status nếu có
        if ($status_filter) {
            $sql .= " AND lo.status = ?";
            $params[] = $status_filter;
            $param_types .= "s";
        }
        
        $sql .= " ORDER BY lo.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $param_types .= "ii";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($param_types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        
        return $orders;
    }

    // Lấy chi tiết đơn hàng với sản phẩm
    public function getOrderDetails($order_id) {
        $sql = "SELECT 
                    lo.*,
                    l.title as livestream_title,
                    l.image as livestream_thumbnail,
                    u.username as streamer_name
                FROM livestream_orders lo
                LEFT JOIN livestream l ON lo.livestream_id = l.id
                LEFT JOIN users u ON l.user_id = u.id
                WHERE lo.id = ?";
        
        $stmt = $this->conn->prepare($sql);
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
            $items_stmt->bind_param("i", $order_id);
            $items_stmt->execute();
            $items_result = $items_stmt->get_result();
            
            $items = [];
            while ($item = $items_result->fetch_assoc()) {
                // Xử lý ảnh sản phẩm
                if ($item['product_image']) {
                    $images = explode(',', $item['product_image']);
                    $item['anh_dau'] = 'img/' . trim($images[0]);
                } else {
                    $item['anh_dau'] = 'img/default-product.jpg';
                }
                $items[] = $item;
            }
            
            $order['items'] = $items;
        }
        
        return $order;
    }

    // Xử lý thanh toán bằng ví
    public function processWalletPayment($order_id, $user_id) {
        $this->conn->begin_transaction();
        
        try {
            // Lấy thông tin đơn hàng
            $order_sql = "SELECT total_amount FROM livestream_orders WHERE id = ? AND user_id = ?";
            $order_stmt = $this->conn->prepare($order_sql);
            $order_stmt->bind_param("ii", $order_id, $user_id);
            $order_stmt->execute();
            $order = $order_stmt->get_result()->fetch_assoc();
            
            if (!$order) {
                throw new Exception("Không tìm thấy đơn hàng");
            }
            
            // Lấy số dư hiện tại từ transfer_accounts (cùng cơ sở dữ liệu với nạp tiền)
            $balance_sql = "SELECT balance FROM transfer_accounts WHERE user_id = ?";
            $balance_stmt = $this->conn->prepare($balance_sql);
            $balance_stmt->bind_param("i", $user_id);
            $balance_stmt->execute();
            $account = $balance_stmt->get_result()->fetch_assoc();
            
            if (!$account || $account['balance'] < $order['total_amount']) {
                throw new Exception("Số dư không đủ");
            }
            
            // Trừ tiền từ ví
            $new_balance = $account['balance'] - $order['total_amount'];
            $update_balance_sql = "UPDATE transfer_accounts SET balance = ? WHERE user_id = ?";
            $update_balance_stmt = $this->conn->prepare($update_balance_sql);
            $update_balance_stmt->bind_param("di", $new_balance, $user_id);
            $update_balance_stmt->execute();
            
            // Cập nhật trạng thái đơn hàng
            $update_order_sql = "UPDATE livestream_orders SET status = 'confirmed' WHERE id = ?";
            $update_order_stmt = $this->conn->prepare($update_order_sql);
            $update_order_stmt->bind_param("i", $order_id);
            $update_order_stmt->execute();
            
            // Xóa giỏ hàng
            $clear_cart_sql = "DELETE FROM livestream_cart_items WHERE user_id = ? AND livestream_id = (SELECT livestream_id FROM livestream_orders WHERE id = ?)";
            $clear_cart_stmt = $this->conn->prepare($clear_cart_sql);
            $clear_cart_stmt->bind_param("ii", $user_id, $order_id);
            $clear_cart_stmt->execute();
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    // Xóa sản phẩm khỏi livestream
    public function removeProductFromLivestream($livestream_id, $product_id) {
        $sql = "DELETE FROM livestream_products WHERE livestream_id = ? AND product_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $livestream_id, $product_id);
        return $stmt->execute();
    }

    // Bỏ ghim sản phẩm
    public function unpinProduct($livestream_id, $product_id) {
        $sql = "UPDATE livestream_products SET is_pinned = 0, pinned_at = NULL WHERE livestream_id = ? AND product_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $livestream_id, $product_id);
        return $stmt->execute();
    }

    // =============================================
    // EMAIL NOTIFICATIONS
    // =============================================
    
    /**
     * Gửi email thông báo đơn hàng mới cho seller
     */
    private function sendOrderNotificationEmail($order_id, $livestream_id, $order_code, $total_amount, $cart_items, $customer_name, $customer_phone) {
        try {
            // Lấy thông tin seller từ livestream
            $livestream = $this->getLivestreamById($livestream_id);
            if (!$livestream) {
                return false;
            }
            
            $seller_id = $livestream['user_id'];
            
            // Lấy thông tin seller
            $seller_sql = "SELECT username, email FROM users WHERE id = ?";
            $seller_stmt = $this->conn->prepare($seller_sql);
            $seller_stmt->bind_param("i", $seller_id);
            $seller_stmt->execute();
            $seller_result = $seller_stmt->get_result();
            $seller = $seller_result->fetch_assoc();
            
            if (!$seller || !$seller['email']) {
                error_log("No seller email found for order $order_id");
                return false;
            }
            
            // Load email helper
            require_once __DIR__ . '/../helpers/EmailNotification.php';
            $emailer = new EmailNotification();
            
            // Prepare order data
            $orderData = [
                'order_code' => $order_code,
                'total_amount' => $total_amount,
                'customer_name' => $customer_name,
                'customer_phone' => $customer_phone,
                'items' => $cart_items
            ];
            
            // Send email (queued)
            $sent = $emailer->sendOrderNotification(
                $seller['email'],
                $seller['username'],
                $orderData
            );
            
            if ($sent) {
                error_log("Order notification email queued for seller: " . $seller['email']);
            }
            
            return $sent;
            
        } catch (Exception $e) {
            error_log("Error sending order notification email: " . $e->getMessage());
            return false;
        }
    }

}
?>



