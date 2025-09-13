<?php
require_once 'mConnect.php';

class mProfile extends Connect {
    public function getUserById($id) {
        $conn = $this->connect();
        $stmt = $conn->prepare("SELECT users.*, transfer_accounts.account_number
                                FROM users
                                LEFT JOIN transfer_accounts 
                                ON users.id = transfer_accounts.user_id
                                WHERE users.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getRatingStats($userId) {
        $conn = $this->connect(); // <-- thêm dòng này để lấy kết nối
        $stmt = $conn->prepare("SELECT COUNT(*) as total_reviews, AVG(rating) as average_rating FROM reviews WHERE reviewed_user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getSanPhamTheoTrangThai($userId, $trangThaiBan) {
        $conn = $this->connect();
        $sql = "SELECT id, title, price, image, updated_date 
                FROM products 
                WHERE user_id = ? 
                AND status = 'Đã duyệt' 
                AND sale_status = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $userId, $trangThaiBan);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }


    public function capNhatThongTin($id, $email, $phone, $address, $birth_date, $avatar = null) {
        // Kiểm tra tuổi
        $dob = new DateTime($birth_date);
        $today = new DateTime();
        $age = $today->diff($dob)->y;
        if ($age < 18) {
            return false;
        }

        $conn = $this->connect();
        if ($avatar) {
            $sql = "UPDATE users SET email=?, phone=?, address=?, birth_date=?, avatar=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $email, $phone, $address, $birth_date, $avatar, $id);
        } else {
            $sql = "UPDATE users SET email=?, phone=?, address=?, birth_date=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $email, $phone, $address, $birth_date, $id);
        }
        $stmt->execute();
        return true;
    }
    
    public function countSanPhamTheoTrangThai($userId, $trangThaiBan) {
        $conn = $this->connect();
        $sql = "SELECT COUNT(*) as total FROM products WHERE user_id = ? AND status = 'Đã duyệt' AND sale_status = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $userId, $trangThaiBan);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'] ?? 0;
    }
    
    public function getUserByUsername($username) {
        $conn = $this->connect();
        $cleanUsername = $this->createSlug($username);
        
        $stmt = $conn->prepare("SELECT id FROM users WHERE LOWER(REPLACE(REPLACE(REPLACE(username, ' ', ''), 'đ', 'd'), 'Đ', 'D')) = ?");
        $stmt->bind_param("s", $cleanUsername);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            return $user['id'];
        }
        return null;
    }
    
    // Hàm chuyển đổi tên đăng nhập thành slug URL
    public function createSlug($str) {
        $str = trim(mb_strtolower($str, 'UTF-8'));
        
        // Chuyển đổi các ký tự có dấu thành không dấu
        $unicode = array(
            'a'=>'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
            'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i'=>'í|ì|ỉ|ĩ|ị',
            'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y'=>'ý|ỳ|ỷ|ỹ|ỵ',
            'd'=>'đ'
        );
        
        foreach($unicode as $nonUnicode=>$uni) {
            $str = preg_replace("/($uni)/i", $nonUnicode, $str);
        }
        
        // Loại bỏ các ký tự đặc biệt và khoảng trắng
        $str = preg_replace('/[^a-z0-9]/', '', $str);
        
        return $str;
    }
}
?>
