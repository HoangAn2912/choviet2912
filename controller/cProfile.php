<?php
require_once 'model/mProfile.php';

class cProfile {
    private $model;

    public function __construct() {
        $this->model = new mProfile();
    }

    public function getSanPhamDangHienThi($userId) {
        return $this->model->getSanPhamTheoTrangThai($userId, 'Đang bán');
    }

    public function getSanPhamDaBan($userId) {
        return $this->model->getSanPhamTheoTrangThai($userId, 'Đã bán');
    }

    public function capNhatThongTin() {
    if (!isset($_SESSION['user_id'])) return;

    $id = $_SESSION['user_id'];
    $email = $_POST['email'];
            $phone = $_POST['phone'];
            $address = $_POST['address'];
            $birth_date = $_POST['birth_date'];
            $avatar = null;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $profileUrl = $ctrl->getFriendlyUrl($_SESSION['user_id']);
        header("Location: " . $profileUrl . "?toast=" . urlencode("❌ Email không hợp lệ") . "&type=error");
        exit;
    }
            if (!preg_match('/^[0-9]{10,11}$/', $phone)) {
        $profileUrl = $ctrl->getFriendlyUrl($_SESSION['user_id']);
        header("Location: " . $profileUrl . "?toast=" . urlencode("❌ Số điện thoại không hợp lệ! Phải có 10–11 chữ số") . "&type=error");
        exit;
    }
    // Kiểm tra tuổi
            $dob = new DateTime($birth_date);
    $today = new DateTime();
    $age = $today->diff($dob)->y;
    if ($age < 18) {
        $profileUrl = $ctrl->getFriendlyUrl($_SESSION['user_id']);
        header("Location: " . $profileUrl . "?toast=" . urlencode("❌ Ngày sinh không hợp lệ. Bạn phải đủ 18 tuổi trở lên!") . "&type=error");
        exit;
    }

    // Xử lý upload ảnh đại diện nếu có
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
                    $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
            $profileUrl = $ctrl->getFriendlyUrl($_SESSION['user_id']);
            header("Location: " . $profileUrl . "?toast=" . urlencode("❌ Ảnh phải có định dạng .jpg, .jpeg hoặc .png") . "&type=error");
            exit;
        }
        $targetDir = "img/";
        $fileName = time() . '_' . uniqid() . '.' . $ext;
        $targetFile = $targetDir . $fileName;
                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
                $avatar = $fileName;
        }
    }

            $this->model->capNhatThongTin($id, $email, $phone, $address, $birth_date, $avatar);
    
    // Chuyển hướng về URL thân thiện sau khi cập nhật
    $friendlyUrl = $this->getFriendlyUrl($id);
    header("Location: $friendlyUrl?toast=" . urlencode("✅ Bạn đã cập nhật thông tin thành công!") . "&type=success");
    exit;
}

    public function countSanPhamDangHienThi($userId) {
        return $this->model->countSanPhamTheoTrangThai($userId, 'Đang bán');
    }

    public function countSanPhamDaBan($userId) {
        return $this->model->countSanPhamTheoTrangThai($userId, 'Đã bán');
    }
    
    // Phương thức tạo URL thân thiện từ tên đăng nhập
    public function getFriendlyUrl($userId) {
        $user = $this->model->getUserById($userId);
        if (!$user) return 'index.php?thongtin=' . $userId;
        
        $username = $user['username'];
        return $this->model->createSlug($username);
    }
}
