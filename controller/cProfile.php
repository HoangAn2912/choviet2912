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
        
        // Lấy thông tin user hiện tại để so sánh
        $currentUser = $this->model->getUserById($id);
        if (!$currentUser) {
            $profileUrl = $this->getFriendlyUrl($_SESSION['user_id']);
            header("Location: " . $profileUrl . "?toast=" . urlencode("[Lỗi] Không tìm thấy thông tin người dùng") . "&type=error");
            exit;
        }
        
        $email = trim($_POST['email'] ?? $currentUser['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $birth_date = trim($_POST['birth_date'] ?? '');
        $avatar = null;
        
        // Kiểm tra xem có thay đổi gì không
        $hasChanges = false;
        
        // So sánh phone (normalize để so sánh)
        $currentPhone = trim($currentUser['phone'] ?? '');
        $phone = trim($phone);
        if ($phone !== $currentPhone) {
            $hasChanges = true;
        }
        
        // So sánh address (normalize để so sánh)
        $currentAddress = trim($currentUser['address'] ?? '');
        $address = trim($address);
        if ($address !== $currentAddress) {
            $hasChanges = true;
        }
        
        // So sánh birth_date (normalize để so sánh)
        $currentBirthDate = trim($currentUser['birth_date'] ?? '');
        $birth_date_trimmed = trim($birth_date);
        // Format birth_date để so sánh (chỉ lấy phần date, bỏ time nếu có)
        $currentBirthDateFormatted = '';
        $birthDateFormatted = '';
        if ($currentBirthDate) {
            $currentBirthDateFormatted = date('Y-m-d', strtotime($currentBirthDate));
        }
        if ($birth_date_trimmed) {
            $birthDateFormatted = date('Y-m-d', strtotime($birth_date_trimmed));
        }
        if ($birthDateFormatted !== $currentBirthDateFormatted) {
            $hasChanges = true;
        }
        
        // Kiểm tra có upload avatar mới không
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            $hasChanges = true;
        }
        
        // Nếu không có thay đổi gì, báo thông tin đã được lưu
        if (!$hasChanges) {
            $profileUrl = $this->getFriendlyUrl($_SESSION['user_id']);
            header("Location: " . $profileUrl . "?toast=" . urlencode("Thông tin của bạn đã được lưu") . "&type=info");
            exit;
        }

        // Validate phone (chỉ validate nếu có nhập)
        // Số điện thoại phải bắt đầu bằng 0 và có đủ 10 hoặc 11 số
        if (!empty($phone) && !preg_match('/^0[0-9]{9,10}$/', $phone)) {
            $profileUrl = $this->getFriendlyUrl($_SESSION['user_id']);
            header("Location: " . $profileUrl . "?toast=" . urlencode("[Lỗi] Số điện thoại không hợp lệ! Phải bắt đầu bằng số 0 và có đủ 10 hoặc 11 chữ số") . "&type=error");
            exit;
        }

        // Validate birth_date (chỉ validate nếu có nhập)
        if (!empty($birth_date)) {
            try {
                $dob = new DateTime($birth_date);
                $today = new DateTime();
                $age = $today->diff($dob)->y;
                
                if ($age < 18) {
                    $profileUrl = $this->getFriendlyUrl($_SESSION['user_id']);
                    header("Location: " . $profileUrl . "?toast=" . urlencode("[Lỗi] Ngày sinh không hợp lệ. Bạn phải đủ 18 tuổi trở lên!") . "&type=error");
                    exit;
                }
                
                // Kiểm tra ngày sinh không được trong tương lai
                if ($dob > $today) {
                    $profileUrl = $this->getFriendlyUrl($_SESSION['user_id']);
                    header("Location: " . $profileUrl . "?toast=" . urlencode("[Lỗi] Ngày sinh không được trong tương lai") . "&type=error");
                    exit;
                }
            } catch (Exception $e) {
                $profileUrl = $this->getFriendlyUrl($_SESSION['user_id']);
                header("Location: " . $profileUrl . "?toast=" . urlencode("[Lỗi] Ngày sinh không hợp lệ") . "&type=error");
                exit;
            }
        }

        // Xử lý upload ảnh đại diện nếu có
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $profileUrl = $this->getFriendlyUrl($_SESSION['user_id']);
                header("Location: " . $profileUrl . "?toast=" . urlencode("[Lỗi] Ảnh phải có định dạng .jpg, .jpeg, .png, .gif hoặc .webp") . "&type=error");
                exit;
            }
            
            // Kiểm tra kích thước file (tối đa 5MB)
            if ($_FILES['avatar']['size'] > 5 * 1024 * 1024) {
                $profileUrl = $this->getFriendlyUrl($_SESSION['user_id']);
                header("Location: " . $profileUrl . "?toast=" . urlencode("[Lỗi] Kích thước ảnh không được vượt quá 5MB") . "&type=error");
                exit;
            }
            
            $targetDir = "img/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            
            $fileName = time() . '_' . uniqid() . '.' . $ext;
            $targetFile = $targetDir . $fileName;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
                $avatar = $fileName;
            } else {
                $profileUrl = $this->getFriendlyUrl($_SESSION['user_id']);
                header("Location: " . $profileUrl . "?toast=" . urlencode("[Lỗi] Không thể upload ảnh đại diện") . "&type=error");
                exit;
            }
        }

        // Cập nhật thông tin (cho phép phone, address, birth_date là null/empty)
        $result = $this->model->capNhatThongTin($id, $email, $phone ?: null, $address ?: null, $birth_date ?: null, $avatar);
        
        if (!$result) {
            $profileUrl = $this->getFriendlyUrl($_SESSION['user_id']);
            header("Location: " . $profileUrl . "?toast=" . urlencode("[Lỗi] Có lỗi xảy ra khi cập nhật thông tin") . "&type=error");
            exit;
        }
    
        // Chuyển hướng về URL thân thiện sau khi cập nhật
        $friendlyUrl = $this->getFriendlyUrl($id);
        header("Location: $friendlyUrl?toast=" . urlencode("[Thành công] Bạn đã cập nhật thông tin thành công!") . "&type=success");
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
