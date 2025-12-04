<?php
include_once "mConnect.php";

class mPost {
    private $conn;

    public function __construct() {
        $db = new Connect();
        $this->conn = $db->connect();
    }

    public function insertSanPham($title, $price, $description, $image, $user_id, $category_id) {
        $ngayTao = date('Y-m-d H:i:s');
        $trangThai = 'Chờ duyệt';
        $trangThaiBan = 'Đang bán';

        // Map biến cho phần bind phía dưới
        $created_date = $ngayTao;
        $status = $trangThai;
        $sale_status = $trangThaiBan;

        // Phí đăng bài (nếu vượt ngưỡng)
        $phiDangBai = 11000;
    
        // Bước 1: Đếm số lượng bài đăng đã có
        $sqlCount = "SELECT COUNT(*) as quantity FROM products WHERE user_id = ?";
        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->bind_param("i", $user_id);
        $stmtCount->execute();
        $resultCount = $stmtCount->get_result();
        $rowCount = $resultCount->fetch_assoc();
        $soLuong = (int)$rowCount['quantity'];
        $stmtCount->close();
    
        // Bước 2: FIXED - 2 bài đầu miễn phí, từ bài thứ 3 trở đi tính phí
        // Nếu đã có >= 2 bài (tức đang đăng bài thứ 3 trở lên) => trừ phí
        if ($soLuong >= 2) {
            // Trừ số dư trong tài khoản
    
            // Kiểm tra số dư hiện tại
            $sqlCheck = "SELECT balance FROM transfer_accounts WHERE user_id = ?";
            $stmtCheck = $this->conn->prepare($sqlCheck);
            $stmtCheck->bind_param("i", $user_id);
            $stmtCheck->execute();
            $resultCheck = $stmtCheck->get_result();
            $rowCheck = $resultCheck->fetch_assoc();
            $soDuHienTai = (int)$rowCheck['balance'];
            $stmtCheck->close();
    
            if ($soDuHienTai < $phiDangBai) {
                return false; // Không đủ tiền
            }
    
            // Cập nhật số dư
            $sqlUpdate = "UPDATE transfer_accounts SET balance = balance - ? WHERE user_id = ?";
            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->bind_param("ii", $phiDangBai, $user_id);
            $stmtUpdate->execute();
            $stmtUpdate->close();
        }
    
        // Bước 3: Thêm sản phẩm mới
        $sqlInsert = "INSERT INTO products (title, price, description, image, created_date, status, sale_status, user_id, category_id) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtInsert = $this->conn->prepare($sqlInsert);
        $stmtInsert->bind_param("sdssssssi", $title, $price, $description, $image, $created_date, $status, $sale_status, $user_id, $category_id);
        $result = $stmtInsert->execute();
        $idSanPhamMoi = $stmtInsert->insert_id;
        $stmtInsert->close();
    
        // Bước 4: Ghi vào lịch sử phí nếu đã trừ tiền
        if ($soLuong >= 2 && $result) {
            $sqlLichSu = "INSERT INTO posting_fee_history (product_id, user_id, amount, created_date) 
                          VALUES (?, ?, ?, CURDATE())";
            $stmtLichSu = $this->conn->prepare($sqlLichSu);
            $stmtLichSu->bind_param("iid", $idSanPhamMoi, $user_id, $phiDangBai);
            $stmtLichSu->execute();
            $stmtLichSu->close();
        }
    
        return $result;
    }
    
    public function layTatCaTinDangTheoNguoiDung($userId) {
        $sql = "SELECT sp.*, tk.balance, nd.username, nd.avatar
                FROM products sp
                INNER JOIN users nd ON sp.user_id = nd.id 
                INNER JOIN transfer_accounts tk ON nd.id = tk.user_id 
                WHERE sp.user_id = ?
                ORDER BY sp.updated_date DESC";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $posts = [];
    
        while ($row = $result->fetch_assoc()) {
            $row['thoi_gian_cu_the'] = $this->tinhThoiGian($row['updated_date']);
            // Xử lý thời gian cập nhật - sửa typo
            $row['thoi_pricen_cu_the'] = $row['thoi_gian_cu_the'];
            $posts[] = $row;
        }
    
        return $posts;
    }
    
    public function tinhThoiGian($updated_date) {
        $now = new DateTime();
        $created = new DateTime($updated_date);
        $diff = $now->diff($created);
    
        if ($diff->days == 0 && $diff->h == 0 && $diff->i < 60) return $diff->i . " phút trước";
        if ($diff->days == 0 && $diff->h < 24) return $diff->h . " giờ trước";
        if ($diff->days == 1) return "Hôm qua";
        if ($diff->days <= 6) return $diff->days . " ngày trước";
        if ($diff->days <= 30) return "Tuần trước";
        return "Tháng trước";
    }

    public function demSoLuongTheoTrangThai($userId) {
        $sql = "SELECT 
                    SUM(CASE WHEN sp.sale_status = 'Đang bán' AND sp.status = 'Đã duyệt' THEN 1 ELSE 0 END) AS 'Đang bán',
                    SUM(CASE WHEN sp.sale_status = 'Đã bán' AND sp.status = 'Đã duyệt' THEN 1 ELSE 0 END) AS 'Đã bán',
                    SUM(CASE WHEN sp.sale_status = 'Đã ẩn' AND sp.status = 'Đã duyệt' THEN 1 ELSE 0 END) AS 'Đã ẩn',
                    SUM(CASE WHEN sp.status = 'Chờ duyệt' AND sp.sale_status = 'Đang bán' THEN 1 ELSE 0 END) AS 'Chờ duyệt',
                    SUM(CASE WHEN sp.status = 'Từ chối' AND sp.sale_status = 'Đang bán'THEN 1 ELSE 0 END) AS 'Từ chối'
                FROM products sp
                WHERE sp.user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function layThongTinNguoiDung($userId) {
        $sql = "SELECT nd.avatar, nd.username, tk.balance, nd.address
                FROM users nd
                LEFT JOIN transfer_accounts tk ON nd.id = tk.user_id
                WHERE nd.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        // Đảm bảo các giá trị mặc định nếu NULL
        if ($user) {
            $user['avatar'] = $user['avatar'] ?? 'default-avatar.png';
            $user['username'] = $user['username'] ?? 'Người dùng';
            $user['balance'] = $user['balance'] ?? 0;
            $user['address'] = $user['address'] ?? 'Chưa cập nhật';
        }
        
        return $user;
    }

    public function demSoLuongTin($userId) {
        $sql = "SELECT COUNT(*) as count FROM products WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return intval($res['count']);
    }
    
    public function updateTrangThaiBan($idTin, $trangThaiBanMoi, $note = null) {
        try {
            // Nếu có note, cập nhật cả note và sale_status
            if ($note !== null && !empty(trim($note))) {
                // Giới hạn độ dài note để tránh vượt quá varchar(255)
                $note = substr(trim($note), 0, 255);
                $sql = "UPDATE products SET sale_status = ?, note = ?, updated_date = NOW() WHERE id = ?";
                $stmt = $this->conn->prepare($sql);
                if (!$stmt) {
                    error_log("Prepare failed: " . $this->conn->error);
                    return false;
                }
                $stmt->bind_param("ssi", $trangThaiBanMoi, $note, $idTin);
            } else {
                // Nếu không có note, chỉ cập nhật sale_status (giữ nguyên note cũ)
        $sql = "UPDATE products SET sale_status = ?, updated_date = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
                if (!$stmt) {
                    error_log("Prepare failed: " . $this->conn->error);
                    return false;
                }
        $stmt->bind_param("si", $trangThaiBanMoi, $idTin);
            }
            
        $result = $stmt->execute();
            if (!$result) {
                error_log("Execute failed: " . $stmt->error);
                $stmt->close();
                return false;
            }
            
            // Kiểm tra affected_rows để đảm bảo có dòng nào được update
            $affectedRows = $stmt->affected_rows;
        $stmt->close();
            
            // Trả về true nếu execute thành công (kể cả khi affected_rows = 0, có thể do giá trị không thay đổi)
        return $result;
        } catch (Exception $e) {
            error_log("Error in updateTrangThaiBan: " . $e->getMessage());
            return false;
        }
    }

    public function laySanPhamTheoId($id) {
        $sql = "SELECT * FROM products WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }

    public function layTenLoaiSanPham($idLoai) {
        $sql = "SELECT category_name FROM product_categories WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idLoai);
        $stmt->execute();
        $stmt->bind_result($tenLoai);
        $stmt->fetch();
        $stmt->close();
        return $tenLoai;
    }
    
    public function capNhatSanPham($id, $title, $price, $description, $image, $category_id, $user_id) {
        $sql = "UPDATE products SET 
                        title = ?, 
                        price = ?, 
                        description = ?, 
                        image = ?, 
                        category_id = ?, 
                        status = 'Chờ duyệt', 
                        updated_date = NOW() 
                    WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        echo $tieuDe;
        echo $price;
        echo $moTa;
        echo $hinhAnh;      
        echo $idLoaiSanPham;
        echo $id;
        echo $idNguoiDung;
        $stmt->bind_param("sdssiii", $title, $price, $description, $image, $category_id, $id, $user_id);
        return $stmt->execute();
    }

    public function laySoDuNguoiDung($idNguoiDung) {
        $sql = "SELECT balance FROM transfer_accounts WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idNguoiDung);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return intval($res['balance'] ?? 0);
    }
    
    public function dayTin($idTin, $idNguoiDung) {
        // 1. Trừ tiền
        $stmt = $this->conn->prepare("UPDATE transfer_accounts SET balance = balance - 11000 WHERE user_id = ? AND balance >= 11000");
        $stmt->bind_param("i", $idNguoiDung);
        $stmt->execute();
        if ($stmt->affected_rows <= 0) return false;
        $stmt->close();
    
        // 2. Ghi lịch sử đẩy tin
        $stmt2 = $this->conn->prepare("INSERT INTO promotion_history (product_id, user_id, amount, promotion_time) VALUES (?, ?, 11000, NOW())");
        $stmt2->bind_param("ii", $idTin, $idNguoiDung);
        $stmt2->execute();
        $stmt2->close();

        // 3. Cập nhật trạng thái bài viết => Chờ duyệt
        $stmt3 = $this->conn->prepare("UPDATE products SET status = 'Chờ duyệt', updated_date = NOW() WHERE id = ? AND user_id = ?");
        $stmt3->bind_param("ii", $idTin, $idNguoiDung);
        $stmt3->execute();
        $stmt3->close();
    
        return true;
    }
    
    
      

}
?>
