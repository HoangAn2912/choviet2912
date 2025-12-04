<?php
/**
 * File cấu hình chính của hệ thống
 * Lấy config từ env_config.php
 */

// Load config helper (chỉ 1 lần)
require_once __DIR__ . '/../../../helpers/config_helper.php';

// Cấu hình timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Lấy config VietQR từ env_config.php
$vietqrConfig = getVietQRConfig();

// Cấu hình VietQR - chỉ define nếu chưa tồn tại để tránh warning "already defined"
if (!defined('VIETQR_API_URL')) {
    define('VIETQR_API_URL', $vietqrConfig['api_url']);
}
if (!defined('VIETQR_BANK_CODE')) {
    define('VIETQR_BANK_CODE', $vietqrConfig['bank_code']);
}
if (!defined('VIETQR_ACCOUNT_NUMBER')) {
    define('VIETQR_ACCOUNT_NUMBER', $vietqrConfig['account_number']);
}
if (!defined('VIETQR_ACCOUNT_NAME')) {
    define('VIETQR_ACCOUNT_NAME', $vietqrConfig['account_name']);
}
if (!defined('SIEUTHICODE_API_URL')) {
    define('SIEUTHICODE_API_URL', $vietqrConfig['sieuthicode_api_url']);
}
if (!defined('SIEUTHICODE_TOKEN')) {
    define('SIEUTHICODE_TOKEN', $vietqrConfig['sieuthicode_token']);
}

// Cấu hình website - lấy từ env_config
if (!defined('SITE_URL')) {
    define('SITE_URL', rtrim(getConfig('base_url', 'https://choviet.site'), '/') . '/');
}

// Development mode từ config
if (!defined('DEVELOPMENT_MODE')) {
    define('DEVELOPMENT_MODE', getConfig('development_mode', false));
}

// Các mức tiền có thể chọn (VND)
if (!defined('PAYMENT_AMOUNTS')) {
    define('PAYMENT_AMOUNTS', $vietqrConfig['payment_amounts']);
}

// Cấu hình hiển thị lỗi (theo môi trường)
if (getConfig('debug', false)) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Include database
require_once __DIR__ . '/database.php';
