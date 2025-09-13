<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../model/mLoginLogout.php';
require_once __DIR__ . '/../helpers/logger.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Controller xử lý OTP qua email
 */
class OtpController {
    private $model;
    private $logger;
    private $emailConfig;
    
    public function __construct() {
        $this->model = new mLoginLogout();
        $this->logger = new Logger();
        
        // Tải cấu hình
        $this->emailConfig = require_once __DIR__ . '/../config/email_config.php';
    }
    
    /**
     * Xử lý yêu cầu AJAX
     */
    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        
        header('Content-Type: application/json');
        
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'send_otp':
                $this->handleSendOTP();
                break;
            case 'send_reset_otp':
                $this->handleSendResetOTP();
                break;
            case 'verify_otp':
                $this->handleVerifyOTP();
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
                break;
        }
    }
    
    /**
     * Xử lý gửi OTP
     */
    private function handleSendOTP() {
        $email = $_POST['contact'];
        
        try {
            // Kiểm tra email đã tồn tại
            if ($this->model->checkEmailExists($email)) {
                echo json_encode(['success' => false, 'message' => 'Email đã được sử dụng']);
                return;
            }
            
            // Tạo mã OTP
            $otp = $this->model->createOTP($email, 'email');
            
            if (!$otp) {
                echo json_encode(['success' => false, 'message' => 'Không thể tạo mã OTP']);
                return;
            }
            
            // Gửi OTP
            $result = $this->sendOTPByEmail($email, $otp);
            echo json_encode($result);
            
        } catch (Exception $e) {
            $this->logger->error("Lỗi gửi OTP", ['email' => $email, 'error' => $e->getMessage()]);
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi gửi OTP']);
        }
    }
    
    /**
     * Xử lý gửi OTP để đặt lại mật khẩu
     */
    private function handleSendResetOTP() {
        $email = $_POST['contact'];
        
        try {
            // Kiểm tra email có tồn tại không (phải có để đặt lại mật khẩu)
            if (!$this->model->checkEmailExists($email)) {
                echo json_encode(['success' => false, 'message' => 'Email không tồn tại trong hệ thống']);
                return;
            }
            
            // Tạo mã OTP
            $otp = $this->model->createOTP($email, 'email');
            
            if (!$otp) {
                echo json_encode(['success' => false, 'message' => 'Không thể tạo mã OTP']);
                return;
            }
            
            // Gửi OTP
            $result = $this->sendOTPByEmail($email, $otp, 'reset_password');
            echo json_encode($result);
            
        } catch (Exception $e) {
            $this->logger->error("Lỗi gửi OTP đặt lại mật khẩu", ['email' => $email, 'error' => $e->getMessage()]);
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi gửi OTP']);
        }
    }
    
    /**
     * Xử lý xác thực OTP
     */
    private function handleVerifyOTP() {
        $email = $_POST['contact'];
        $otp = $_POST['otp'];
        
        try {
            $result = $this->model->verifyOTP($email, 'email', $otp);
            echo json_encode($result);
        } catch (Exception $e) {
            $this->logger->error("Lỗi xác thực OTP", ['email' => $email, 'error' => $e->getMessage()]);
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi xác thực OTP']);
        }
    }
    
    /**
     * Gửi OTP qua email
     */
    private function sendOTPByEmail($email, $otp, $purpose = 'register') {
        try {
            $mail = $this->createMailer();
            $this->configureEmail($mail, $email, $otp, $purpose);
            
            $this->logger->info("Đang gửi email đến: $email", ['otp' => $otp, 'purpose' => $purpose]);
            
            $mail->send();
            
            $this->logger->info("Gửi email thành công đến: $email");
            return ['success' => true, 'message' => 'Mã OTP đã được gửi đến email của bạn'];
            
        } catch (Exception $e) {
            $this->logger->error("Lỗi gửi email", ['email' => $email, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Không thể gửi email: ' . $e->getMessage()];
        }
    }
    
    /**
     * Tạo đối tượng PHPMailer
     */
    private function createMailer() {
        $mail = new PHPMailer(true);
        
        // Cấu hình SMTP
        $mail->isSMTP();
        $mail->Host = $this->emailConfig['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $this->emailConfig['username'];
        $mail->Password = $this->emailConfig['password'];
        $mail->SMTPSecure = $this->emailConfig['encryption'];
        $mail->Port = $this->emailConfig['port'];
        $mail->CharSet = 'UTF-8';
        
        return $mail;
    }
    
    /**
     * Cấu hình email
     */
    private function configureEmail($mail, $email, $otp, $purpose = 'register') {
        // Người gửi và người nhận
        $mail->setFrom($this->emailConfig['from_email'], $this->emailConfig['from_name']);
        $mail->addAddress($email);
        
        // Nội dung email
        $mail->isHTML(true);
        
        if ($purpose === 'reset_password') {
            $mail->Subject = 'Mã xác thực đặt lại mật khẩu Chợ Việt';
            $mail->Body = $this->createResetPasswordEmailContent($otp);
        } else {
            $mail->Subject = 'Mã xác thực đăng ký tài khoản Chợ Việt';
            $mail->Body = $this->createEmailContent($otp);
        }
    }
    
    /**
     * Tạo nội dung email
     */
    private function createEmailContent($otp) {
        return '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                <h2 style="color: #fcb50e; text-align: center;">Chợ Việt - Xác thực tài khoản</h2>
                <p>Xin chào,</p>
                <p>Cảm ơn bạn đã đăng ký tài khoản tại Chợ Việt. Để hoàn tất quá trình đăng ký, vui lòng sử dụng mã xác thực sau:</p>
                <div style="background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 24px; letter-spacing: 5px; font-weight: bold; margin: 20px 0;">
                    ' . $otp . '
                </div>
                <p>Mã xác thực có hiệu lực trong vòng 10 phút.</p>
                <p>Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email này.</p>
                <p style="margin-top: 30px; text-align: center; font-size: 12px; color: #777;">
                    © 2025 Chợ Việt – Nền tảng mua bán đồ cũ C2C.
                </p>
            </div>
        ';
    }
    
    /**
     * Tạo nội dung email đặt lại mật khẩu
     */
    private function createResetPasswordEmailContent($otp) {
        return '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                <h2 style="color: #fcb50e; text-align: center;">Chợ Việt - Đặt lại mật khẩu</h2>
                <p>Xin chào,</p>
                <p>Bạn đã yêu cầu đặt lại mật khẩu cho tài khoản Chợ Việt. Để tiếp tục, vui lòng sử dụng mã xác thực sau:</p>
                <div style="background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 24px; letter-spacing: 5px; font-weight: bold; margin: 20px 0;">
                    ' . $otp . '
                </div>
                <p>Mã xác thực có hiệu lực trong vòng 10 phút.</p>
                <p>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này và đảm bảo tài khoản của bạn được bảo mật.</p>
                <p style="margin-top: 30px; text-align: center; font-size: 12px; color: #777;">
                    © 2025 Chợ Việt – Nền tảng mua bán đồ cũ C2C.
                </p>
            </div>
        ';
    }
}

// Khởi tạo controller và xử lý yêu cầu
$controller = new OtpController();
$controller->handleRequest();
?>
