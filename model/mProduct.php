<?php
include_once "mConnect.php";

class mProduct {
    private $conn;

    public function __construct() {
        $p = new Connect();
        $this->conn = $p->connect();
    }

    public function getSanPhamMoiNhat($limit = 100) {
        $sql = "SELECT sp.*, nd.username, nd.avatar, nd.phone, nd.address
                FROM products sp 
                JOIN users nd ON sp.user_id = nd.id 
                WHERE sp.sale_status = 'Đang bán' AND sp.status = 'Đã duyệt'
                ORDER BY sp.updated_date DESC, sp.created_date DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];

        while ($row = $result->fetch_assoc()) {
            // Nếu có nhiều ảnh, tách lấy ảnh đầu tiên
            if (!empty($row['image'])) {
                $dsAnh = array_map('trim', explode(',', $row['image']));
                $row['anh_dau'] = $dsAnh[0] ?? ''; // ảnh đầu tiên
            } else {
                $row['anh_dau'] = '';
            }
            $data[] = $row;
        }
        return $data;
    }

    public function tinhThoiGian($created_date) {
        $now = new DateTime();
        $created = new DateTime($created_date);
        $diff = $now->diff($created);
    
        if ($diff->days == 0 && $diff->h == 0 && $diff->i < 60) return $diff->i . " phút trước";
        if ($diff->days == 0 && $diff->h < 24) return $diff->h . " giờ trước";
        if ($diff->days == 1) return "Hôm qua";
        if ($diff->days <= 6) return $diff->days . " ngày trước";
        if ($diff->days <= 30) return "Tuần trước";
        return "Tháng trước";
    }


    public function getSanPhamById($id) {
        $sql = "SELECT * FROM products WHERE id = ? AND status = 'Đã duyệt'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
    
        // xử lý chuỗi ảnh thành mảng
        if ($data && isset($data['image'])) {
            $data['ds_anh'] = array_map('trim', explode(',', $data['image']));
        }
    
        return $data;
    }
    
    public function searchProducts($keyword) {
        $sql = "SELECT sp.*, nd.username, nd.avatar, nd.phone, nd.address
                FROM products sp 
                JOIN users nd ON sp.user_id = nd.id 
                WHERE sp.sale_status = 'Đang bán' AND sp.title LIKE ?";
        $stmt = $this->conn->prepare($sql);
        $likeKeyword = '%' . $keyword . '%';
        $stmt->bind_param("s", $likeKeyword);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $data = [];
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['image'])) {
                $dsAnh = array_map('trim', explode(',', $row['image']));
                $row['anh_dau'] = $dsAnh[0] ?? '';
            } else {
                $row['anh_dau'] = '';
            }
            $data[] = $row;
        }
        return $data;
    }
    
    // Lấy sản phẩm theo user_id
    public function getSanPhamByUserId($user_id) {
        $sql = "SELECT * FROM products WHERE user_id = ? AND status = 'Đã duyệt' ORDER BY created_date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];

        while ($row = $result->fetch_assoc()) {
            // Nếu có nhiều ảnh, tách lấy ảnh đầu tiên
            if (!empty($row['image'])) {
                $dsAnh = array_map('trim', explode(',', $row['image']));
                $row['anh_dau'] = $dsAnh[0] ?? ''; // ảnh đầu tiên
            } else {
                $row['anh_dau'] = '';
            }
            $data[] = $row;
        }
        return $data;
    }

    // Lấy sản phẩm của user
    public function getProductsByUserId($user_id) {
        $sql = "SELECT id, title, price, image, description, sale_status, status
                FROM products 
                WHERE user_id = ? AND status = 'Đã duyệt'
                ORDER BY created_date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];

        while ($row = $result->fetch_assoc()) {
            // Xử lý ảnh sản phẩm
            if (!empty($row['image'])) {
                $dsAnh = array_map('trim', explode(',', $row['image']));
                $row['anh_dau'] = $dsAnh[0] ?? ''; // ảnh đầu tiên
            } else {
                $row['anh_dau'] = '';
            }
            $data[] = $row;
        }
        
        return $data;
    }
    
    
}
?>
