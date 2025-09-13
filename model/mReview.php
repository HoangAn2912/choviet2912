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
}
?>
