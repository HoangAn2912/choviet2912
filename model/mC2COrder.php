<?php
/**
 * Model quản lý đơn hàng C2C (Consumer to Consumer)
 * Chức năng:
 * - Tạo đơn hàng
 * - Quản lý trạng thái
 * - Thương lượng giá
 * - Ký quỹ an toàn
 * - Tin nhắn trong đơn hàng
 */

require_once __DIR__ . '/mConnect.php';

class mC2COrder {
    private $conn;

    public function __construct() {
        $db = new Connect();
        $this->conn = $db->connect();
    }

    // =============================================
    // TẠO ĐƠN HÀNG
    // =============================================

    /**
     * Tạo đơn hàng C2C mới
     * 
     * @param array $data - Thông tin đơn hàng
     * @return array ['success' => bool, 'order_id' => int, 'order_code' => string, 'message' => string]
     */
    public function createOrder($data) {
        try {
            $this->conn->begin_transaction();

            // 1. Validate sản phẩm
            $product = $this->getProductInfo($data['product_id']);
            if (!$product) {
                throw new Exception("Sản phẩm không tồn tại");
            }

            if ($product['user_id'] == $data['buyer_id']) {
                throw new Exception("Không thể mua sản phẩm của chính mình");
            }

            // 2. Tạo mã đơn hàng
            $order_code = 'C2C' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // 3. Tính tổng tiền
            $quantity = $data['quantity'] ?? 1;
            $price = $data['price'] ?? $product['price'];
            $total_amount = $price * $quantity;

            // 4. Tạo đơn hàng
            $sql = "INSERT INTO c2c_orders (
                        order_code, buyer_id, seller_id, product_id, 
                        quantity, price, total_amount, status,
                        delivery_method, delivery_name, delivery_phone, 
                        delivery_address, delivery_province, delivery_district, delivery_ward,
                        payment_method, buyer_note
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            
            $status = 'pending';
            $delivery_method = $data['delivery_method'] ?? 'ship';
            $payment_method = $data['payment_method'] ?? 'cash';
            
            $stmt->bind_param("siiiddsssssssssss",
                $order_code,
                $data['buyer_id'],
                $product['user_id'], // seller_id
                $data['product_id'],
                $quantity,
                $price,
                $total_amount,
                $status,
                $delivery_method,
                $data['delivery_name'],
                $data['delivery_phone'],
                $data['delivery_address'],
                $data['delivery_province'],
                $data['delivery_district'],
                $data['delivery_ward'],
                $payment_method,
                $data['buyer_note']
            );
            
            $stmt->execute();
            $order_id = $this->conn->insert_id;

            // 5. Lưu lịch sử trạng thái
            $this->addStatusHistory($order_id, null, 'pending', $data['buyer_id'], 'Đơn hàng được tạo');

            // 6. Tạo tin nhắn hệ thống đầu tiên
            $this->addOrderMessage($order_id, $data['buyer_id'], 
                "Đơn hàng #{$order_code} đã được tạo. Người bán sẽ xác nhận trong thời gian sớm nhất.",
                'system'
            );

            $this->conn->commit();

            return [
                'success' => true,
                'order_id' => $order_id,
                'order_code' => $order_code,
                'message' => 'Tạo đơn hàng thành công'
            ];

        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error creating C2C order: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // =============================================
    // QUẢN LÝ TRẠNG THÁI
    // =============================================

    /**
     * Cập nhật trạng thái đơn hàng
     */
    public function updateOrderStatus($order_id, $new_status, $user_id, $note = null) {
        try {
            $this->conn->begin_transaction();

            // Lấy trạng thái hiện tại
            $order = $this->getOrderById($order_id);
            if (!$order) {
                throw new Exception("Đơn hàng không tồn tại");
            }

            $old_status = $order['status'];

            // Validate quyền thay đổi
            if ($user_id != $order['buyer_id'] && $user_id != $order['seller_id']) {
                throw new Exception("Bạn không có quyền thay đổi đơn hàng này");
            }

            // Cập nhật trạng thái
            $sql = "UPDATE c2c_orders SET status = ?, updated_at = NOW()";
            $params = [$new_status];
            $types = "s";

            // Cập nhật thời gian tương ứng
            if ($new_status == 'confirmed') {
                $sql .= ", confirmed_at = NOW()";
            } elseif ($new_status == 'completed') {
                $sql .= ", completed_at = NOW()";
            }

            $sql .= " WHERE id = ?";
            $params[] = $order_id;
            $types .= "i";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();

            // Lưu lịch sử
            $this->addStatusHistory($order_id, $old_status, $new_status, $user_id, $note);

            // Tin nhắn hệ thống
            $status_messages = [
                'negotiating' => 'Đơn hàng đang được thương lượng',
                'confirmed' => 'Người bán đã xác nhận đơn hàng',
                'shipping' => 'Đơn hàng đang được giao',
                'completed' => 'Đơn hàng đã hoàn tất',
                'cancelled' => 'Đơn hàng đã bị hủy',
                'disputed' => 'Đơn hàng đang trong tranh chấp'
            ];

            if (isset($status_messages[$new_status])) {
                $this->addOrderMessage($order_id, $user_id, $status_messages[$new_status], 'system');
            }

            $this->conn->commit();

            return ['success' => true, 'message' => 'Cập nhật trạng thái thành công'];

        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error updating order status: " . $e->getMessage());
            
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Lưu lịch sử thay đổi trạng thái
     */
    private function addStatusHistory($order_id, $old_status, $new_status, $changed_by, $note = null) {
        $sql = "INSERT INTO c2c_order_status_history (order_id, old_status, new_status, changed_by, note)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issis", $order_id, $old_status, $new_status, $changed_by, $note);
        return $stmt->execute();
    }

    // =============================================
    // THƯƠNG LƯỢNG GIÁ
    // =============================================

    /**
     * Gửi đề nghị giá
     */
    public function sendPriceOffer($order_id, $sender_id, $offer_price, $message) {
        return $this->addOrderMessage($order_id, $sender_id, $message, 'offer', $offer_price);
    }

    /**
     * Chấp nhận giá đề nghị
     */
    public function acceptOffer($order_id, $user_id, $offer_price) {
        try {
            $this->conn->begin_transaction();

            // Cập nhật giá trong đơn hàng
            $sql = "UPDATE c2c_orders SET price = ?, total_amount = price * quantity, updated_at = NOW()
                    WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("di", $offer_price, $order_id);
            $stmt->execute();

            // Thêm tin nhắn accept
            $this->addOrderMessage($order_id, $user_id, 
                "Đã chấp nhận giá " . number_format($offer_price) . "đ", 
                'accept', $offer_price
            );

            // Chuyển sang trạng thái confirmed
            $this->updateOrderStatus($order_id, 'confirmed', $user_id, 'Chấp nhận giá đề nghị');

            $this->conn->commit();

            return ['success' => true, 'message' => 'Đã chấp nhận giá đề nghị'];

        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // =============================================
    // TIN NHẮN TRONG ĐƠN HÀNG
    // =============================================

    /**
     * Thêm tin nhắn vào đơn hàng
     */
    public function addOrderMessage($order_id, $sender_id, $message, $type = 'text', $offer_price = null) {
        $sql = "INSERT INTO c2c_order_messages (order_id, sender_id, message, message_type, offer_price)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iissd", $order_id, $sender_id, $message, $type, $offer_price);
        return $stmt->execute();
    }

    /**
     * Lấy tin nhắn của đơn hàng
     */
    public function getOrderMessages($order_id) {
        $sql = "SELECT m.*, u.username, u.avatar
                FROM c2c_order_messages m
                LEFT JOIN users u ON m.sender_id = u.id
                WHERE m.order_id = ?
                ORDER BY m.created_at ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        
        return $messages;
    }

    /**
     * Đánh dấu tin nhắn đã đọc
     */
    public function markMessagesAsRead($order_id, $user_id) {
        $sql = "UPDATE c2c_order_messages 
                SET is_read = 1 
                WHERE order_id = ? AND sender_id != ? AND is_read = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $order_id, $user_id);
        return $stmt->execute();
    }

    // =============================================
    // KÝ QUỸ (ESCROW)
    // =============================================

    /**
     * Giữ tiền ký quỹ
     */
    public function holdEscrow($order_id, $amount, $user_id) {
        try {
            $this->conn->begin_transaction();

            // 1. Kiểm tra số dư
            $balance_sql = "SELECT balance FROM transfer_accounts WHERE user_id = ?";
            $balance_stmt = $this->conn->prepare($balance_sql);
            $balance_stmt->bind_param("i", $user_id);
            $balance_stmt->execute();
            $account = $balance_stmt->get_result()->fetch_assoc();

            if (!$account || $account['balance'] < $amount) {
                throw new Exception("Số dư không đủ");
            }

            // 2. Trừ tiền
            $update_sql = "UPDATE transfer_accounts SET balance = balance - ? WHERE user_id = ?";
            $update_stmt = $this->conn->prepare($update_sql);
            $update_stmt->bind_param("di", $amount, $user_id);
            $update_stmt->execute();

            // 3. Tạo escrow transaction
            $escrow_sql = "INSERT INTO escrow_transactions (order_id, amount, status) VALUES (?, ?, 'holding')";
            $escrow_stmt = $this->conn->prepare($escrow_sql);
            $escrow_stmt->bind_param("id", $order_id, $amount);
            $escrow_stmt->execute();

            // 4. Cập nhật đơn hàng
            $order_sql = "UPDATE c2c_orders SET escrow_held = 1, payment_status = 'paid' WHERE id = ?";
            $order_stmt = $this->conn->prepare($order_sql);
            $order_stmt->bind_param("i", $order_id);
            $order_stmt->execute();

            $this->conn->commit();

            return ['success' => true, 'message' => 'Đã giữ tiền ký quỹ'];

        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Chuyển tiền cho seller khi hoàn tất
     */
    public function releaseEscrowToSeller($order_id, $seller_id) {
        try {
            $this->conn->begin_transaction();

            // Lấy thông tin escrow
            $escrow_sql = "SELECT * FROM escrow_transactions WHERE order_id = ? AND status = 'holding'";
            $escrow_stmt = $this->conn->prepare($escrow_sql);
            $escrow_stmt->bind_param("i", $order_id);
            $escrow_stmt->execute();
            $escrow = $escrow_stmt->get_result()->fetch_assoc();

            if (!$escrow) {
                throw new Exception("Không tìm thấy tiền ký quỹ");
            }

            // Cộng tiền cho seller
            $add_sql = "UPDATE transfer_accounts SET balance = balance + ? WHERE user_id = ?";
            $add_stmt = $this->conn->prepare($add_sql);
            $add_stmt->bind_param("di", $escrow['amount'], $seller_id);
            $add_stmt->execute();

            // Cập nhật escrow
            $update_escrow = "UPDATE escrow_transactions 
                             SET status = 'released_to_seller', released_at = NOW() 
                             WHERE id = ?";
            $update_stmt = $this->conn->prepare($update_escrow);
            $update_stmt->bind_param("i", $escrow['id']);
            $update_stmt->execute();

            $this->conn->commit();

            return ['success' => true, 'message' => 'Đã chuyển tiền cho người bán'];

        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // =============================================
    // QUERY ĐƠN HÀNG
    // =============================================

    /**
     * Lấy đơn hàng theo ID
     */
    public function getOrderById($order_id) {
        $sql = "SELECT * FROM v_c2c_orders_full WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Lấy đơn hàng của buyer
     */
    public function getBuyerOrders($buyer_id, $status = null) {
        if ($status) {
            $sql = "SELECT * FROM v_c2c_orders_full WHERE buyer_id = ? AND status = ? ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("is", $buyer_id, $status);
        } else {
            $sql = "SELECT * FROM v_c2c_orders_full WHERE buyer_id = ? ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $buyer_id);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        
        return $orders;
    }

    /**
     * Lấy đơn hàng của seller
     */
    public function getSellerOrders($seller_id, $status = null) {
        if ($status) {
            $sql = "SELECT * FROM v_c2c_orders_full WHERE seller_id = ? AND status = ? ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("is", $seller_id, $status);
        } else {
            $sql = "SELECT * FROM v_c2c_orders_full WHERE seller_id = ? ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $seller_id);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        
        return $orders;
    }

    /**
     * Đếm đơn hàng theo trạng thái
     */
    public function countOrders($user_id, $role = 'buyer') {
        $field = $role == 'buyer' ? 'buyer_id' : 'seller_id';
        
        $sql = "SELECT 
                    status,
                    COUNT(*) as count
                FROM c2c_orders
                WHERE $field = ?
                GROUP BY status";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $counts = [];
        while ($row = $result->fetch_assoc()) {
            $counts[$row['status']] = $row['count'];
        }
        
        return $counts;
    }

    // =============================================
    // HELPER METHODS
    // =============================================

    /**
     * Lấy thông tin sản phẩm
     */
    private function getProductInfo($product_id) {
        $sql = "SELECT * FROM products WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Kiểm tra quyền truy cập đơn hàng
     */
    public function canAccessOrder($order_id, $user_id) {
        $order = $this->getOrderById($order_id);
        if (!$order) return false;
        
        return ($order['buyer_id'] == $user_id || $order['seller_id'] == $user_id);
    }
}
?>











