<?php
require_once 'mConnect.php';

class mReview extends Connect {
    public function getReviewsBySellerId($sellerId) {
        $conn = $this->connect();
        $sql = "SELECT 
                    users.username AS reviewer_name,
                    products.title AS ten_san_pham,
                    products.price AS gia_ban,
                    products.image AS hinh_san_pham,
                    reviews.rating,
                    reviews.comment AS comment,
                    reviews.created_date AS review_date
                FROM reviews
                INNER JOIN users ON reviews.reviewer_id = users.id
                INNER JOIN products ON reviews.product_id = products.id
                WHERE reviews.reviewed_user_id = ?
                ORDER BY reviews.created_date DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $sellerId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $conn->close();
        return $result;
    }
    
    public function themDanhGia($idNguoiDanhGia, $idNguoiDuocDanhGia, $idSanPham, $soSao, $binhLuan) {
        $conn = $this->connect();
        $stmt = $conn->prepare("INSERT INTO reviews 
            (reviewer_id, reviewed_user_id, product_id, rating, comment, created_date)
            VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("iiiis", $idNguoiDanhGia, $idNguoiDuocDanhGia, $idSanPham, $soSao, $binhLuan);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
    


    public function daDanhGia($idNguoiDanhGia, $idNguoiDuocDanhGia, $idSanPham) {
        $conn = $this->connect();
        $stmt = $conn->prepare("SELECT COUNT(*) FROM reviews WHERE reviewer_id = ? AND reviewed_user_id = ? AND product_id = ?");
        $stmt->bind_param("iii", $idNguoiDanhGia, $idNguoiDuocDanhGia, $idSanPham);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        $conn->close();
        return $count > 0;
    }

    /**
     * ✅ PHIÊN BẢN MỚI: Thêm đánh giá có liên kết với đơn hàng
     */
    public function addReview($data) {
        $conn = $this->connect();
        
        try {
            // Kiểm tra quyền review (gọi stored procedure)
            $check_sql = "CALL check_review_permission(?, ?, ?, ?, ?, @can_review, @reason)";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("iiiis", 
                $data['reviewer_id'],
                $data['reviewed_user_id'],
                $data['product_id'],
                $data['order_type'],
                $data['order_id']
            );
            $check_stmt->execute();
            $check_stmt->close();
            
            // Lấy kết quả
            $result = $conn->query("SELECT @can_review AS can_review, @reason AS reason");
            $check_result = $result->fetch_assoc();
            
            if ($check_result['can_review'] != 1) {
                return [
                    'success' => false,
                    'message' => $check_result['reason']
                ];
            }
            
            // Thêm review
            $is_verified = ($data['order_type'] != 'direct') ? 1 : 0;
            
            $sql = "INSERT INTO reviews 
                   (reviewer_id, reviewed_user_id, product_id, rating, comment, 
                    livestream_order_id, c2c_order_id, order_type, is_verified_purchase, created_date)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($sql);
            
            $livestream_order_id = ($data['order_type'] == 'livestream') ? $data['order_id'] : null;
            $c2c_order_id = ($data['order_type'] == 'c2c') ? $data['order_id'] : null;
            
            $stmt->bind_param("iiiisiiis",
                $data['reviewer_id'],
                $data['reviewed_user_id'],
                $data['product_id'],
                $data['rating'],
                $data['comment'],
                $livestream_order_id,
                $c2c_order_id,
                $data['order_type'],
                $is_verified
            );
            
            $success = $stmt->execute();
            $stmt->close();
            $conn->close();
            
            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Đánh giá thành công!'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Lỗi khi thêm đánh giá'
                ];
            }
            
        } catch (Exception $e) {
            $conn->close();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Lấy thống kê đánh giá của seller
     */
    public function getSellerRatingStats($seller_id) {
        $conn = $this->connect();
        $sql = "SELECT * FROM v_seller_ratings WHERE seller_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $seller_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $result;
    }

    /**
     * Lấy các đơn hàng chưa đánh giá của buyer
     */
    public function getPendingReviewOrders($buyer_id) {
        $conn = $this->connect();
        
        // Lấy đơn hàng livestream chưa review
        $livestream_sql = "SELECT DISTINCT
                              lo.id AS order_id,
                              'livestream' AS order_type,
                              loi.product_id,
                              p.title AS product_name,
                              p.image AS product_image,
                              p.user_id AS seller_id,
                              u.username AS seller_name,
                              lo.created_date AS order_date
                          FROM livestream_orders lo
                          JOIN livestream_order_items loi ON lo.id = loi.order_id
                          JOIN products p ON loi.product_id = p.id
                          JOIN users u ON p.user_id = u.id
                          LEFT JOIN reviews r ON r.livestream_order_id = lo.id 
                                             AND r.product_id = loi.product_id
                                             AND r.reviewer_id = ?
                          WHERE lo.buyer_id = ? 
                            AND (lo.order_status = 'completed' OR lo.order_status = 'delivered')
                            AND r.id IS NULL";
        
        $livestream_stmt = $conn->prepare($livestream_sql);
        $livestream_stmt->bind_param("ii", $buyer_id, $buyer_id);
        $livestream_stmt->execute();
        $livestream_result = $livestream_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Lấy đơn hàng C2C chưa review
        $c2c_sql = "SELECT 
                       co.id AS order_id,
                       'c2c' AS order_type,
                       co.product_id,
                       p.title AS product_name,
                       p.image AS product_image,
                       co.seller_id,
                       u.username AS seller_name,
                       co.created_at AS order_date
                   FROM c2c_orders co
                   JOIN products p ON co.product_id = p.id
                   JOIN users u ON co.seller_id = u.id
                   LEFT JOIN reviews r ON r.c2c_order_id = co.id 
                                      AND r.product_id = co.product_id
                                      AND r.reviewer_id = ?
                   WHERE co.buyer_id = ? 
                     AND (co.order_status = 'completed' OR co.order_status = 'delivered')
                     AND r.id IS NULL";
        
        $c2c_stmt = $conn->prepare($c2c_sql);
        $c2c_stmt->bind_param("ii", $buyer_id, $buyer_id);
        $c2c_stmt->execute();
        $c2c_result = $c2c_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $conn->close();
        
        // Merge results
        return array_merge($livestream_result, $c2c_result);
    }

    /**
     * Lấy danh sách review với filter verified
     */
    public function getReviewsWithVerification($seller_id, $verified_only = false) {
        $conn = $this->connect();
        
        $where_clause = $verified_only ? "AND r.is_verified_purchase = 1" : "";
        
        $sql = "SELECT 
                    r.*,
                    u.username AS reviewer_name,
                    u.avatar AS reviewer_avatar,
                    p.title AS product_name,
                    p.image AS product_image
                FROM reviews r
                JOIN users u ON r.reviewer_id = u.id
                JOIN products p ON r.product_id = p.id
                WHERE r.reviewed_user_id = ? $where_clause
                ORDER BY r.created_date DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $seller_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $conn->close();
        
        return $result;
    }
}
?>
