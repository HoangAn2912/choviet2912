<?php
include_once "mConnect.php";

class mCategory {
    private $conn;

    public function __construct() {
        $p = new Connect();
        $this->conn = $p->connect();
    }

    public function layDanhMuc() {
        $sql = "
            SELECT 
                cha.parent_category_id AS id_cha,
                cha.parent_category_name AS ten_cha,
                con.id AS id_con,
                con.category_name AS ten_con
            FROM parent_categories cha
            LEFT JOIN product_categories con ON cha.parent_category_id = con.parent_category_id
            ORDER BY cha.parent_category_id, con.id
        ";

        $result = $this->conn->query($sql);
        $data = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }
// hiển thị sản phẩm khi đang trạng thái đang bán và tìm được trên danh mục
    public function getProductsByCategoryId($id_loai) {
        $sql = "SELECT * FROM products WHERE category_id = ? AND sale_status = 'Đang bán'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_loai);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        return $products;
    }

    public function layDanhMucVaSoLuong() {
        $sql = "
            SELECT 
                con.id AS id_loai,
                con.category_name AS category_name,
                COUNT(sp.id) AS quantity
            FROM product_categories con
            LEFT JOIN products sp ON con.id = sp.category_id AND sp.sale_status = 'Đang bán'
            WHERE con.category_name NOT LIKE 'Khác'
            GROUP BY con.id, con.category_name
            ORDER BY con.category_name ASC
        ";
    
        $result = $this->conn->query($sql);
        $data = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function getUserById($id) {
        $sql = "SELECT id, username, avatar FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
}