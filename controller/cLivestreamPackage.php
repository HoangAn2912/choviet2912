<?php
/**
 * Controller quản lý gói Livestream
 */

require_once __DIR__ . '/../model/mLivestreamPackage.php';
require_once __DIR__ . '/../helpers/Security.php';

class cLivestreamPackage {
    
    /**
     * Hiển thị trang danh sách gói livestream
     */
    public function showPackages() {
        $model = new mLivestreamPackage();
        
        // Lấy danh sách gói
        $packages = $model->getAllPackages();
        
        // Kiểm tra user đã đăng nhập chưa
        $user_id = $_SESSION['user_id'] ?? 0;
        $activeRegistration = null;
        
        if ($user_id > 0) {
            // Lấy gói đang active (nếu có)
            $activeRegistration = $model->getActiveRegistration($user_id);
        }
        
        // Truyền data sang view
        include_once __DIR__ . '/../view/livestream_packages.php';
    }
    
    /**
     * Xử lý mua gói bằng ví
     */
    public function purchaseByWallet() {
        // Validate CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Security::validateCSRFToken($csrfToken)) {
            header("Location: index.php?livestream-packages&toast=" . urlencode("[Lỗi] CSRF token không hợp lệ!") . "&type=error");
            exit;
        }
        
        $user_id = $_SESSION['user_id'] ?? 0;
        
        if ($user_id == 0) {
            header("Location: index.php?livestream-packages&toast=" . urlencode("[Lỗi] Vui lòng đăng nhập!") . "&type=error");
            exit;
        }
        
        $package_id = intval($_POST['package_id'] ?? 0);
        
        if ($package_id == 0) {
            header("Location: index.php?livestream-packages&toast=" . urlencode("[Lỗi] Gói không hợp lệ!") . "&type=error");
            exit;
        }
        
        $model = new mLivestreamPackage();
        $result = $model->payByWallet($user_id, $package_id);
        
        if ($result['success']) {
            header("Location: index.php?livestream-packages&toast=" . urlencode("[Thành công] " . $result['message']) . "&type=success");
        } else {
            header("Location: index.php?livestream-packages&toast=" . urlencode("[Lỗi] " . $result['message']) . "&type=error");
        }
        exit;
    }
    
    /**
     * Tạo thanh toán VNPay cho gói livestream
     */
    public function purchaseByVNPay() {
        // Validate CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Security::validateCSRFToken($csrfToken)) {
            header("Location: index.php?livestream-packages&toast=" . urlencode("[Lỗi] CSRF token không hợp lệ!") . "&type=error");
            exit;
        }
        
        $user_id = $_SESSION['user_id'] ?? 0;
        
        if ($user_id == 0) {
            header("Location: index.php?livestream-packages&toast=" . urlencode("[Lỗi] Vui lòng đăng nhập!") . "&type=error");
            exit;
        }
        
        $package_id = intval($_POST['package_id'] ?? 0);
        
        if ($package_id == 0) {
            header("Location: index.php?livestream-packages&toast=" . urlencode("[Lỗi] Gói không hợp lệ!") . "&type=error");
            exit;
        }
        
        // Lấy thông tin gói
        $model = new mLivestreamPackage();
        $package = $model->getPackageById($package_id);
        
        if (!$package) {
            header("Location: index.php?livestream-packages&toast=" . urlencode("[Lỗi] Gói không tồn tại!") . "&type=error");
            exit;
        }
        
        // Tạo mã giao dịch
        $vnp_TxnRef = 'LSPKG_' . $user_id . '_' . time();
        $vnp_Amount = $package['price'] * 100; // VNPay yêu cầu nhân 100
        $vnp_OrderInfo = "Thanh toan goi livestream: " . $package['package_name'];
        
        // Lưu thông tin vào session để xử lý callback
        $_SESSION['pending_livestream_package'] = [
            'package_id' => $package_id,
            'txn_ref' => $vnp_TxnRef,
            'amount' => $package['price']
        ];
        
        // Redirect đến VNPay (sử dụng code VNPay hiện có)
        require_once __DIR__ . '/vnpay/vnpay_create_payment.php';
        exit;
    }
    
    /**
     * Xử lý callback từ VNPay
     */
    public function handleVNPayReturn() {
        $user_id = $_SESSION['user_id'] ?? 0;
        $pendingPackage = $_SESSION['pending_livestream_package'] ?? null;
        
        if (!$pendingPackage) {
            header("Location: index.php?livestream-packages&toast=" . urlencode("[Lỗi] Phiên thanh toán không hợp lệ!") . "&type=error");
            exit;
        }
        
        // Kiểm tra kết quả từ VNPay
        $vnp_ResponseCode = $_GET['vnp_ResponseCode'] ?? '';
        
        if ($vnp_ResponseCode == '00') {
            // Thanh toán thành công
            $model = new mLivestreamPackage();
            $result = $model->registerPackage(
                $user_id, 
                $pendingPackage['package_id'], 
                'vnpay',
                $pendingPackage['txn_ref']
            );
            
            // Xóa session
            unset($_SESSION['pending_livestream_package']);
            
            if ($result['success']) {
                header("Location: index.php?livestream-packages&toast=" . urlencode("[Thành công] " . $result['message']) . "&type=success");
            } else {
                header("Location: index.php?livestream-packages&toast=" . urlencode("[Lỗi] " . $result['message']) . "&type=error");
            }
        } else {
            // Thanh toán thất bại
            unset($_SESSION['pending_livestream_package']);
            header("Location: index.php?livestream-packages&toast=" . urlencode("[Lỗi] Thanh toán thất bại!") . "&type=error");
        }
        exit;
    }
    
    /**
     * Hiển thị lịch sử mua gói
     */
    public function showHistory() {
        $user_id = $_SESSION['user_id'] ?? 0;
        
        if ($user_id == 0) {
            header("Location: loginlogout/login.php");
            exit;
        }
        
        $model = new mLivestreamPackage();
        $registrations = $model->getRegistrationHistory($user_id);
        $payments = $model->getPaymentHistory($user_id);
        
        include_once __DIR__ . '/../view/livestream_package_history.php';
    }
    
    /**
     * Hủy gói livestream
     */
    public function cancelPackage() {
        $user_id = $_SESSION['user_id'] ?? 0;
        
        if ($user_id == 0) {
            header("Location: index.php?livestream-packages&toast=" . urlencode("[Lỗi] Vui lòng đăng nhập!") . "&type=error");
            exit;
        }
        
        $registration_id = intval($_POST['registration_id'] ?? 0);
        
        $model = new mLivestreamPackage();
        $success = $model->cancelRegistration($registration_id, $user_id);
        
        if ($success) {
            header("Location: index.php?livestream-package-history&toast=" . urlencode("[Thành công] Đã hủy gói thành công!") . "&type=success");
        } else {
            header("Location: index.php?livestream-package-history&toast=" . urlencode("[Lỗi] Hủy gói thất bại!") . "&type=error");
        }
        exit;
    }
}
?>











