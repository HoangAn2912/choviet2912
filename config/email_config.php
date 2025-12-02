<?php
/**
 * ========================================
 * EMAIL CONFIG - Lấy từ env_config.php
 * ========================================
 * File này tự động lấy config email từ env_config.php
 * Không cần chỉnh sửa file này, chỉnh env_config.php là đủ
 */

// Load config helper (helper sẽ tự động load env_config.php và define APP_ENV)
require_once __DIR__ . '/../helpers/config_helper.php';

// Trả về config email từ env_config.php
$emailConfig = getEmailConfig();

// Kiểm tra và đảm bảo có đủ thông tin
if (empty($emailConfig) || empty($emailConfig['username']) || empty($emailConfig['password'])) {
    // Log warning nếu thiếu config (chỉ ở local để debug)
    if (defined('APP_ENV') && APP_ENV === 'local') {
        error_log("EMAIL CONFIG WARNING: Thiếu thông tin email trong env_config.php. Kiểm tra lại config email_username và email_password trong môi trường '" . APP_ENV . "'.");
    }
    
    // Trả về config rỗng để tránh lỗi (PHPMailer sẽ báo lỗi riêng)
    if (empty($emailConfig)) {
        $emailConfig = [
            'host' => 'smtp.gmail.com',
            'username' => '',
            'password' => '',
            'port' => 587,
            'encryption' => 'tls',
            'from_email' => '',
            'from_name' => 'Chợ Việt'
        ];
    }
}

return $emailConfig;
