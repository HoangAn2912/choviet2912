<?php
/**
 * Email Notification Helper
 * Send emails với PHPMailer và queue support
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class EmailNotification {
    private $mailer;
    private $config;
    private $queueEnabled = true;
    private $queueDir;
    
    public function __construct($useMailtrap = false) {
        // Load config
        $configFile = $useMailtrap ? 
            __DIR__ . '/../config/email_config_mailtrap.php' : 
            __DIR__ . '/../config/email_config.php';
        
        if (file_exists($configFile)) {
            $this->config = include($configFile);
        } else {
            throw new Exception("Email config file not found: $configFile");
        }
        
        // Setup PHPMailer
        $this->mailer = new PHPMailer(true);
        $this->setupMailer();
        
        // Setup queue directory
        $this->queueDir = __DIR__ . '/../logs/email_queue/';
        if (!file_exists($this->queueDir)) {
            mkdir($this->queueDir, 0755, true);
        }
    }
    
    /**
     * Setup PHPMailer with config
     */
    private function setupMailer() {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['username'];
            $this->mailer->Password = $this->config['password'];
            $this->mailer->SMTPSecure = $this->config['encryption'];
            $this->mailer->Port = $this->config['port'];
            $this->mailer->CharSet = 'UTF-8';
            
            // From
            $this->mailer->setFrom($this->config['from_email'], $this->config['from_name']);
            
            // Debug (disable in production)
            $this->mailer->SMTPDebug = 0; // 0 = off, 2 = debug
            
        } catch (Exception $e) {
            error_log("Email setup error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Send email (với queue support)
     */
    public function send($to, $subject, $body, $isHTML = true, $attachments = []) {
        if ($this->queueEnabled) {
            return $this->addToQueue($to, $subject, $body, $isHTML, $attachments);
        } else {
            return $this->sendNow($to, $subject, $body, $isHTML, $attachments);
        }
    }
    
    /**
     * Send email immediately
     */
    public function sendNow($to, $subject, $body, $isHTML = true, $attachments = []) {
        try {
            // Reset recipients
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            // Recipients
            if (is_array($to)) {
                foreach ($to as $email => $name) {
                    if (is_numeric($email)) {
                        $this->mailer->addAddress($name);
                    } else {
                        $this->mailer->addAddress($email, $name);
                    }
                }
            } else {
                $this->mailer->addAddress($to);
            }
            
            // Content
            $this->mailer->isHTML($isHTML);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            
            if ($isHTML) {
                $this->mailer->AltBody = strip_tags($body);
            }
            
            // Attachments
            foreach ($attachments as $file) {
                if (file_exists($file)) {
                    $this->mailer->addAttachment($file);
                }
            }
            
            // Send
            $result = $this->mailer->send();
            
            if ($result) {
                $this->logEmail($to, $subject, 'sent');
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->logEmail($to, $subject, 'failed', $e->getMessage());
            error_log("Email send error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Add email to queue
     */
    private function addToQueue($to, $subject, $body, $isHTML, $attachments) {
        $queueData = [
            'to' => $to,
            'subject' => $subject,
            'body' => $body,
            'isHTML' => $isHTML,
            'attachments' => $attachments,
            'created_at' => time(),
            'attempts' => 0
        ];
        
        $filename = $this->queueDir . 'email_' . time() . '_' . uniqid() . '.json';
        
        if (file_put_contents($filename, json_encode($queueData, JSON_PRETTY_PRINT))) {
            $this->logEmail($to, $subject, 'queued');
            return true;
        }
        
        return false;
    }
    
    /**
     * Process email queue
     */
    public function processQueue($limit = 10) {
        $files = glob($this->queueDir . 'email_*.json');
        $processed = 0;
        
        foreach ($files as $file) {
            if ($processed >= $limit) {
                break;
            }
            
            $data = json_decode(file_get_contents($file), true);
            
            if (!$data) {
                unlink($file);
                continue;
            }
            
            // Check max attempts (3)
            if ($data['attempts'] >= 3) {
                $this->logEmail($data['to'], $data['subject'], 'failed', 'Max attempts reached');
                unlink($file);
                continue;
            }
            
            // Try to send
            $sent = $this->sendNow(
                $data['to'],
                $data['subject'],
                $data['body'],
                $data['isHTML'],
                $data['attachments']
            );
            
            if ($sent) {
                unlink($file);
                $processed++;
            } else {
                // Increment attempts
                $data['attempts']++;
                file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
            }
        }
        
        return $processed;
    }
    
    /**
     * Log email activity
     */
    private function logEmail($to, $subject, $status, $error = '') {
        $logFile = __DIR__ . '/../logs/email.log';
        $toStr = is_array($to) ? implode(', ', array_keys($to)) : $to;
        $timestamp = date('Y-m-d H:i:s');
        
        $message = "[$timestamp] [$status] To: $toStr | Subject: $subject";
        if ($error) {
            $message .= " | Error: $error";
        }
        
        file_put_contents($logFile, $message . "\n", FILE_APPEND);
    }
    
    /**
     * Disable queue (send immediately)
     */
    public function disableQueue() {
        $this->queueEnabled = false;
    }
    
    /**
     * Enable queue
     */
    public function enableQueue() {
        $this->queueEnabled = true;
    }
    
    // =============================================
    // NOTIFICATION TEMPLATES
    // =============================================
    
    /**
     * Send order notification to seller
     */
    public function sendOrderNotification($sellerEmail, $sellerName, $orderData) {
        $subject = "🛍️ Bạn có đơn hàng mới #" . $orderData['order_code'];
        
        $body = $this->getTemplate('order_notification', [
            'seller_name' => $sellerName,
            'order_code' => $orderData['order_code'],
            'total_amount' => number_format($orderData['total_amount']),
            'customer_name' => $orderData['customer_name'] ?? 'Khách hàng',
            'customer_phone' => $orderData['customer_phone'] ?? '',
            'items' => $orderData['items'] ?? [],
            'order_url' => $this->joinUrl($this->getBaseUrl(), 'index.php?my-orders')
        ]);
        
        return $this->send($sellerEmail, $subject, $body, true);
    }
    
    /**
     * Send post approved notification
     */
    public function sendPostApprovedNotification($userEmail, $userName, $postData) {
        $subject = "✅ Tin đăng của bạn đã được duyệt";
        
        $body = $this->getTemplate('post_approved', [
            'user_name' => $userName,
            'post_title' => $postData['title'],
            'post_url' => $this->joinUrl($this->getBaseUrl(), 'index.php?detail&id=' . $postData['id'])
        ]);
        
        return $this->send($userEmail, $subject, $body, true);
    }
    
    /**
     * Send post rejected notification
     */
    public function sendPostRejectedNotification($userEmail, $userName, $postData, $reason = '') {
        $subject = "❌ Tin đăng của bạn bị từ chối";
        
        $body = $this->getTemplate('post_rejected', [
            'user_name' => $userName,
            'post_title' => $postData['title'],
            'reason' => $reason ?: 'Vi phạm quy định đăng tin',
            'support_url' => $this->joinUrl($this->getBaseUrl(), 'index.php?contact')
        ]);
        
        return $this->send($userEmail, $subject, $body, true);
    }
    
    /**
     * Send welcome email
     */
    public function sendWelcomeEmail($userEmail, $userName) {
        $subject = "🎉 Chào mừng bạn đến với Chợ Việt!";
        
        $body = $this->getTemplate('welcome', [
            'user_name' => $userName,
            'home_url' => $this->getBaseUrl(),
            'profile_url' => $this->joinUrl($this->getBaseUrl(), 'index.php?thongtin')
        ]);
        
        return $this->send($userEmail, $subject, $body, true);
    }
    
    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($userEmail, $userName, $resetToken) {
        $subject = "🔑 Đặt lại mật khẩu - Chợ Việt";
        
        $resetUrl = $this->joinUrl($this->getBaseUrl(), 'index.php?action=reset_password&token=' . $resetToken);
        
        $body = $this->getTemplate('password_reset', [
            'user_name' => $userName,
            'reset_url' => $resetUrl,
            'expires' => '1 giờ'
        ]);
        
        return $this->send($userEmail, $subject, $body, true);
    }
    
    /**
     * Send livestream start notification
     */
    public function sendLivestreamStartNotification($userEmail, $userName, $livestreamData) {
        $subject = "🎥 Livestream đã bắt đầu: " . $livestreamData['title'];
        
        $body = $this->getTemplate('livestream_start', [
            'user_name' => $userName,
            'livestream_title' => $livestreamData['title'],
            'streamer_name' => $livestreamData['streamer_name'],
            'livestream_url' => $this->joinUrl($this->getBaseUrl(), 'index.php?watch&id=' . $livestreamData['id'])
        ]);
        
        return $this->send($userEmail, $subject, $body, true);
    }
    
    /**
     * Send order status update
     */
    public function sendOrderStatusUpdate($userEmail, $userName, $orderData, $newStatus) {
        $statusText = [
            'confirmed' => 'đã được xác nhận',
            'shipping' => 'đang được giao',
            'completed' => 'đã hoàn thành',
            'cancelled' => 'đã bị hủy'
        ];
        
        $subject = "📦 Đơn hàng #" . $orderData['order_code'] . " " . ($statusText[$newStatus] ?? 'đã cập nhật');
        
        $body = $this->getTemplate('order_status', [
            'user_name' => $userName,
            'order_code' => $orderData['order_code'],
            'status' => $statusText[$newStatus] ?? $newStatus,
            'order_url' => $this->getBaseUrl() . '/index.php?my-orders'
        ]);
        
        return $this->send($userEmail, $subject, $body, true);
    }
    
    /**
     * Get email template
     */
    private function getTemplate($templateName, $data = []) {
        $templateFile = __DIR__ . '/../view/email_templates/' . $templateName . '.php';
        
        if (!file_exists($templateFile)) {
            // Fallback to simple template
            return $this->getSimpleTemplate($templateName, $data);
        }
        
        // Extract data to variables
        extract($data);
        
        // Start output buffering
        ob_start();
        include $templateFile;
        $content = ob_get_clean();
        
        return $content;
    }
    
    /**
     * Simple template fallback
     */
    private function getSimpleTemplate($type, $data) {
        $baseTemplate = $this->getBaseTemplate();
        
        $content = '';
        
        switch ($type) {
            case 'order_notification':
                $content = "
                    <h2>Bạn có đơn hàng mới!</h2>
                    <p>Xin chào {$data['seller_name']},</p>
                    <p>Bạn có một đơn hàng mới với mã: <strong>{$data['order_code']}</strong></p>
                    <p>Tổng giá trị: <strong>{$data['total_amount']} đ</strong></p>
                    <p><a href='{$data['order_url']}' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Xem chi tiết đơn hàng</a></p>
                ";
                break;
                
            case 'post_approved':
                $content = "
                    <h2>Tin đăng đã được duyệt!</h2>
                    <p>Xin chào {$data['user_name']},</p>
                    <p>Tin đăng \"<strong>{$data['post_title']}</strong>\" của bạn đã được duyệt và hiển thị trên website.</p>
                    <p><a href='{$data['post_url']}' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Xem tin đăng</a></p>
                ";
                break;
                
            case 'post_rejected':
                $content = "
                    <h2>Tin đăng bị từ chối</h2>
                    <p>Xin chào {$data['user_name']},</p>
                    <p>Rất tiếc, tin đăng \"<strong>{$data['post_title']}</strong>\" của bạn không được duyệt.</p>
                    <p><strong>Lý do:</strong> {$data['reason']}</p>
                    <p>Vui lòng kiểm tra và đăng lại tin theo đúng quy định.</p>
                ";
                break;
                
            default:
                $content = "<p>Notification content</p>";
        }
        
        return str_replace('{{CONTENT}}', $content, $baseTemplate);
    }
    
    /**
     * Base HTML template
     */
    private function getBaseTemplate() {
        return '
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        a {
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛒 Chợ Việt</h1>
        </div>
        <div class="content">
            {{CONTENT}}
        </div>
        <div class="footer">
            <p>&copy; ' . date('Y') . ' Chợ Việt. All rights reserved.</p>
            <p>Email này được gửi tự động, vui lòng không reply.</p>
        </div>
    </div>
</body>
</html>
        ';
    }
    
    /**
     * Get base URL (không có trailing slash)
     */
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseUrl = $protocol . '://' . $host;
        
        // Lấy path từ SCRIPT_NAME để support subfolder
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $path = dirname($scriptName);
        if ($path !== '/' && $path !== '.') {
            $path = rtrim($path, '/');
            $baseUrl .= $path;
        }
        
        return rtrim($baseUrl, '/'); // Đảm bảo không có trailing slash
    }
    
    /**
     * Helper: Join URL paths đúng cách (tránh double slash)
     */
    private function joinUrl($base, $path) {
        $base = rtrim($base, '/');
        $path = ltrim($path, '/');
        return $base . '/' . $path;
    }
}
?>









