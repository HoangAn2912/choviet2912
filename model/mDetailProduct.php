<?php
include_once("mConnect.php");

class mDetailProduct {
    private $conn;

    public function __construct() {
        $p = new Connect();
        $this->conn = $p->connect();
    }

    public function getDetailById($id) {
        $sql = "SELECT 
                        sp.*, 
                        nd.username, 
                        nd.avatar, 
                        nd.phone,
                        nd.address,
                        (
                            SELECT COUNT(*) 
                            FROM products 
                            WHERE user_id = nd.id AND sale_status = 'da_ban'
                        ) AS so_luong_da_ban,
                        (
                            SELECT ROUND(AVG(rating), 1) 
                            FROM reviews dg 
                            JOIN products sp2 ON dg.product_id = sp2.id 
                            WHERE sp2.user_id = nd.id
                        ) AS diem_danh_gia,
                        (
                            SELECT COUNT(*) 
                            FROM reviews dg 
                            JOIN products sp2 ON dg.product_id = sp2.id 
                            WHERE sp2.user_id = nd.id
                        ) AS so_nguoi_danh_gia
                    FROM products sp 
                    JOIN users nd ON sp.user_id = nd.id 
                    WHERE sp.id = ?
                    ";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        if ($data && isset($data['image'])) {
            $data['ds_anh'] = array_map('trim', explode(',', $data['image']));
        }

        $stmt->close();
        $this->conn->close();

        return $data;
    }
}
