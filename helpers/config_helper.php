<?php
/**
 * ========================================
 * CONFIG HELPER - Lấy config từ env_config.php
 * ========================================
 * File này cung cấp các hàm helper để lấy config từ env_config.php
 * Tất cả config đều được tập trung trong 1 file duy nhất
 */

/**
 * Lấy config theo key từ môi trường hiện tại
 * @param string $key Key cần lấy (vd: 'db_host', 'base_url')
 * @param mixed $default Giá trị mặc định nếu không tìm thấy
 * @return mixed Giá trị config
 */
function getConfig($key, $default = null) {
    static $config = null;
    
    if ($config === null) {
        $configPath = __DIR__ . '/../config/env_config.php';
        if (!file_exists($configPath)) {
            // Fallback nếu không có config file
            return $default;
        }
        
        try {
            $allConfigs = require $configPath;
            $env = defined('APP_ENV') ? APP_ENV : 'local';
            $config = $allConfigs[$env] ?? [];
        } catch (Exception $e) {
            // Lỗi khi đọc config
            if (defined('APP_ENV') && APP_ENV === 'local') {
                die("LỖI khi đọc config: " . $e->getMessage());
            }
            $config = [];
        }
    }
    
    return $config[$key] ?? $default;
}

/**
 * Lấy toàn bộ config của môi trường hiện tại
 * @return array
 */
function getAllConfig() {
    static $config = null;
    
    if ($config === null) {
        $configPath = __DIR__ . '/../config/env_config.php';
        if (!file_exists($configPath)) {
            return [];
        }
        
        try {
            $allConfigs = require $configPath;
            $env = defined('APP_ENV') ? APP_ENV : 'local';
            $config = $allConfigs[$env] ?? [];
        } catch (Exception $e) {
            $config = [];
        }
    }
    
    return $config;
}

/**
 * Lấy config email (trả về array tương thích với email_config.php)
 * @return array
 */
function getEmailConfig() {
    return [
        'host' => getConfig('email_host', 'smtp.gmail.com'),
        'username' => getConfig('email_username'),
        'password' => getConfig('email_password'),
        'port' => getConfig('email_port', 587),
        'encryption' => getConfig('email_encryption', 'tls'),
        'from_email' => getConfig('email_from'),
        'from_name' => getConfig('email_from_name', 'Chợ Việt')
    ];
}

/**
 * Lấy config VietQR Payment
 * @return array
 */
function getVietQRConfig() {
    return [
        'api_url' => getConfig('vietqr_api_url', 'https://img.vietqr.io/image/'),
        'bank_code' => getConfig('vietqr_bank_code', 'VCB'),
        'account_number' => getConfig('vietqr_account_number'),
        'account_name' => getConfig('vietqr_account_name'),
        'sieuthicode_api_url' => getConfig('sieuthicode_api_url'),
        'sieuthicode_token' => getConfig('sieuthicode_token'),
        'site_url' => getConfig('base_url'),
        'payment_amounts' => getConfig('payment_amounts', [20000, 50000, 100000, 200000, 500000, 1000000, 2000000, 5000000, 10000000])
    ];
}

/**
 * Lấy config Node.js server (trả về array để export sang JS)
 * @return array
 */
function getNodeServerConfig() {
    return [
        'hostname' => getConfig('node_host', 'localhost'),
        'port' => getConfig('node_port', 8080),
        'basePath' => getConfig('base_path', ''),
        'wsHost' => getConfig('ws_host', 'localhost'),
        'wsPort' => getConfig('ws_port', 3000),
        'wsSecret' => getConfig('ws_secret', ''),
        'chatPath' => getConfig('chat_path', '')
    ];
}
