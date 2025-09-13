<?php
require_once __DIR__ . '/../model/mLoginLogout.php';

/**
 * Controller xử lý đăng nhập, đăng ký và đăng xuất
 */
class LoginLogoutController {
    private $model;
    private $baseUrl;
    
    public function __construct() {
        $this->model = new mLoginLogout();
        require_once __DIR__ . '/../helpers/url_helper.php';
        $this->baseUrl = getBaseUrl() . '/';
    }
    
    /**
     * Xử lý yêu cầu POST
     */
    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        
        if (isset($_POST['login'])) {
            $this->handleLogin();
        } elseif (isset($_POST['register'])) {
            $this->handleRegister();
        } elseif (isset($_POST['reset_password'])) {
            $this->handleResetPassword();
        } elseif (isset($_POST['check_username'])) {
            $this->handleCheckUsername();
        } elseif (isset($_POST['check_password'])) {
            $this->handleCheckPassword();
        }
    }
    
    /**
     * Xử lý đăng nhập
     */
    private function handleLogin() {
        // Cho phép nhập email hoặc tên đăng nhập qua cùng một input
        $identifier = $_POST['email'];
        $password = md5($_POST['password']); // Mã hoá MD5
        
        // Ưu tiên phương thức linh hoạt: email hoặc tên đăng nhập
        $user = $this->model->checkLoginByIdentifier($identifier, $password);
        
        if ($user) {
            $this->createUserSession($user);
            $this->redirectBasedOnRole($user['role_id']);
        } else {
            // Trả về JSON thay vì chuyển hướng
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '❌ Email/Tên đăng nhập hoặc mật khẩu không đúng']);
            exit;
        }
    }
    
    /**
     * Xử lý đăng ký
     */
    private function handleRegister() {
        try {
            // Lấy dữ liệu từ form
            $formData = $this->getFormData();
            
            // Kiểm tra dữ liệu đầu vào
            $validationResult = $this->validateFormData($formData);
            if (!$validationResult['valid']) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false, 
                    'message' => $validationResult['message']
                ]);
                exit;
            }
            
            // Xác thực OTP
            $otpVerification = $this->verifyOTP($formData);
            if (!$otpVerification['success']) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false, 
                    'message' => '❌ ' . $otpVerification['message']
                ]);
                exit;
            }
            
            // Thực hiện đăng ký
            $this->performRegistration($formData);
            
        } catch (Exception $e) {
            error_log("Lỗi đăng ký: " . $e->getMessage());
            // Trả về JSON response thay vì redirect
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => '❌ Lỗi đăng ký: ' . $e->getMessage()
            ]);
            exit;
        }
    }
    
    /**
     * Lấy dữ liệu từ form đăng ký
     */
    private function getFormData() {
        return [
            'username' => trim($_POST['username']),
            'password' => $_POST['password'],
            'repassword' => $_POST['repassword'],
            'otp' => isset($_POST['otp']) ? trim($_POST['otp']) : '',
            'email' => trim($_POST['email'] ?? '')
        ];
    }
    
    /**
     * Kiểm tra dữ liệu đầu vào
     */
    private function validateFormData($data) {
        // Kiểm tra tên đăng nhập
        $usernameValidation = $this->model->validateUsername($data['username']);
        if (!$usernameValidation['valid']) {
            return ['valid' => false, 'message' => '❌ ' . $usernameValidation['message']];
        }
        
        // Kiểm tra mật khẩu
        $passwordValidation = $this->model->validatePassword($data['password']);
        if (!$passwordValidation['valid']) {
            return ['valid' => false, 'message' => '❌ ' . $passwordValidation['message']];
        }
        
        // Kiểm tra mật khẩu khớp
        if ($data['password'] !== $data['repassword']) {
            return ['valid' => false, 'message' => '❌ Mật khẩu không khớp'];
        }
        
        // Kiểm tra OTP
        if (empty($data['otp'])) {
            return ['valid' => false, 'message' => '❌ Vui lòng nhập mã OTP'];
        }
        
        // Kiểm tra email
        if (empty($data['email'])) {
            return ['valid' => false, 'message' => '❌ Vui lòng nhập email'];
        }
        
        // Kiểm tra định dạng email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => '❌ Email không hợp lệ'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Xác thực OTP
     */
    private function verifyOTP($data) {
        $email = $data['email'];
        $otp = $data['otp'];
        
        // Ghi log xác thực OTP
        error_log("Xác thực OTP cho email: $email - OTP: $otp");
        
        $result = $this->model->verifyOTP($email, 'email', $otp);
        
        // Ghi log kết quả
        error_log("Kết quả xác thực OTP: " . ($result['success'] ? 'Thành công' : 'Thất bại') . " - " . $result['message']);
        
        return $result;
    }
    
    /**
     * Thực hiện đăng ký
     */
    private function performRegistration($data) {
        $email = $data['email'];
        
        // Kiểm tra email đã tồn tại -> trả JSON, không redirect
        if ($this->model->checkEmailExists($email)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => '❌ Email đã tồn tại'
            ]);
            exit;
        }
        
        // Ghi log thông tin đăng ký
        error_log("Đăng ký tài khoản: username=" . $data['username'] . ", email=$email");
        
        // Thực hiện đăng ký
        $password_md5 = md5($data['password']);
        $ok = $this->model->registerUser($data['username'], $email, '', $password_md5, 1);
        
        if ($ok) {
            // Trả về JSON response thay vì redirect
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => '✅ Đăng ký thành công! Đang chuyển hướng đến trang đăng nhập...'
            ]);
            exit;
        } else {
            // Trả về JSON response thay vì redirect
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => '❌ Đăng ký thất bại!'
            ]);
            exit;
        }
    }
    

    
    /**
     * Tạo session cho người dùng
     */
    private function createUserSession($user) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['username'];
        $_SESSION['avatar'] = !empty($user['avatar']) ? $user['avatar'] : 'default-avatar.jpg';
        $_SESSION['role'] = $user['role_id'];
    }
    
    /**
     * Chuyển hướng dựa trên vai trò
     */
    private function redirectBasedOnRole($role) {
        if ($role == 1) {
            header("Location: " . $this->baseUrl . "ad");
        } elseif ($role == 2) {
            header("Location: " . $this->baseUrl . "index.php");
        } else {
            $this->redirectWithError('login', '❌ Email không hợp lệ!');
        }
        exit;
    }
    
    /**
     * Chuyển hướng với thông báo lỗi
     */
    private function redirectWithError($page, $message) {
        $url = ($page === 'login') ? '../index.php?login' : '../loginlogout/signup.php';
        header("Location: $url?toast=" . urlencode($message) . "&type=error");
        exit;
    }
    
    /**
     * Chuyển hướng với thông báo thành công
     */
    private function redirectWithSuccess($page, $message) {
        $url = ($page === 'login') ? '/index.php?login' : '../loginlogout/signup.php';
        header("Location: $url?toast=" . urlencode($message) . "&type=success");
        exit;
    }
    
    /**
     * Xử lý đặt lại mật khẩu
     */
    private function handleResetPassword() {
        try {
            $email = $_POST['email'];
            $otp = $_POST['otp'];
            $newPassword = $_POST['new_password'];
            
            // Ghi log
            error_log("Đặt lại mật khẩu: email=$email, otp=$otp");
            
            // Xác thực OTP
            $otpVerification = $this->model->verifyOTP($email, 'email', $otp);
            
            if (!$otpVerification['success']) {
                echo json_encode(['success' => false, 'message' => '❌ ' . $otpVerification['message']]);
                return;
            }
            
            // Cập nhật mật khẩu mới
            $password_md5 = md5($newPassword);
            $ok = $this->model->updatePassword($email, $password_md5);
            
            if ($ok) {
                echo json_encode(['success' => true, 'message' => '✅ Đặt lại mật khẩu thành công!']);
            } else {
                echo json_encode(['success' => false, 'message' => '❌ Không thể cập nhật mật khẩu!']);
            }
            
        } catch (Exception $e) {
            error_log("Lỗi đặt lại mật khẩu: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => '❌ Lỗi đặt lại mật khẩu: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Xử lý kiểm tra tên đăng nhập real-time
     */
    private function handleCheckUsername() {
        try {
            $username = trim($_POST['username']);
            
            if (empty($username)) {
                echo json_encode(['success' => false, 'message' => 'Tên đăng nhập không được để trống']);
                return;
            }
            
            $validation = $this->model->validateUsername($username);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $validation['valid'],
                'message' => $validation['message'],
                'available' => $validation['valid']
            ]);
            
        } catch (Exception $e) {
            error_log("Lỗi kiểm tra tên đăng nhập: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => '❌ Lỗi kiểm tra tên đăng nhập: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Xử lý kiểm tra mật khẩu real-time
     */
    private function handleCheckPassword() {
        try {
            $password = $_POST['password'];
            
            if (empty($password)) {
                echo json_encode(['success' => false, 'message' => 'Mật khẩu không được để trống']);
                return;
            }
            
            $validation = $this->model->validatePassword($password);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $validation['valid'],
                'message' => $validation['message'],
                'valid' => $validation['valid']
            ]);
            
        } catch (Exception $e) {
            error_log("Lỗi kiểm tra mật khẩu: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => '❌ Lỗi kiểm tra mật khẩu: ' . $e->getMessage()
            ]);
        }
    }
}

// Khởi tạo controller và xử lý yêu cầu
$controller = new LoginLogoutController();
$controller->handleRequest();
?>