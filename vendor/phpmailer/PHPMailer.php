<?php
namespace PHPMailer\PHPMailer;

/**
 * Lớp PHPMailer đơn giản để giả lập chức năng gửi email
 * Đây là phiên bản đơn giản hóa để demo, không thực sự gửi email
 */
class PHPMailer {
    public $ErrorInfo = '';
    public $Host = 'smtp.gmail.com';
    public $SMTPAuth = true;
    public $Username = '';
    public $Password = '';
    public $SMTPSecure = 'tls';
    public $Port = 587;
    public $CharSet = 'UTF-8';
    public $From = '';
    public $FromName = '';
    public $Subject = '';
    public $Body = '';
    public $isHTML = false;
    
    const ENCRYPTION_STARTTLS = 'tls';
    
    public function isSMTP() {
        // Thiết lập chế độ SMTP
        return true;
    }
    
    public function setFrom($email, $name = '') {
        $this->From = $email;
        $this->FromName = $name;
        return true;
    }
    
    public function addAddress($email, $name = '') {
        // Thêm người nhận
        return true;
    }
    
    public function send() {
        // Giả lập gửi email thành công
        // Trong môi trường thực tế, bạn sẽ cần cài đặt PHPMailer đầy đủ
        
        // Ghi log email để kiểm tra
        $log = "To: " . $this->From . "\n";
        $log .= "Subject: " . $this->Subject . "\n";
        $log .= "Body: " . substr($this->Body, 0, 100) . "...\n\n";
        
        file_put_contents(__DIR__ . '/../../log.txt', $log, FILE_APPEND);
        
        return true;
    }
}
?>

