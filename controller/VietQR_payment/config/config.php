<?php
/**
 * ========================================
 * VIETQR CONFIG - Lấy từ env_config.php
 * ========================================
 * File này tự động lấy config VietQR từ env_config.php
 * Không cần chỉnh sửa file này, chỉnh env_config.php là đủ
 */

// Cấu hình timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Load config helper
require_once __DIR__ . '/../../../helpers/config_helper.php';

// Lấy config VietQR từ env_config.php
$vietqrConfig = getVietQRConfig();

// Define constants để tương thích với code cũ
define('VIETQR_API_URL', $vietqrConfig['api_url']);
define('VIETQR_BANK_CODE', $vietqrConfig['bank_code']);
define('VIETQR_ACCOUNT_NUMBER', $vietqrConfig['account_number']);
define('VIETQR_ACCOUNT_NAME', $vietqrConfig['account_name']);
define('SIEUTHICODE_API_URL', $vietqrConfig['sieuthicode_api_url']);
define('SIEUTHICODE_TOKEN', $vietqrConfig['sieuthicode_token']);
define('SITE_URL', rtrim($vietqrConfig['site_url'], '/') . '/');
define('DEVELOPMENT_MODE', getConfig('development_mode', false));
define('PAYMENT_AMOUNTS', $vietqrConfig['payment_amounts']);

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
?>
