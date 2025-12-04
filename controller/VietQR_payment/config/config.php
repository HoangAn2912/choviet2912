<?php
/**
<<<<<<< HEAD
 * ========================================
 * VIETQR CONFIG - Lấy từ env_config.php
 * ========================================
 * File này tự động lấy config VietQR từ env_config.php
 * Không cần chỉnh sửa file này, chỉnh env_config.php là đủ
=======
 * File cấu hình chính của hệ thống
 * Lấy config từ env_config.php
>>>>>>> 65997a0 (up len web)
 */

// Load config helper
require_once __DIR__ . '/../../../helpers/config_helper.php';

// Cấu hình timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

<<<<<<< HEAD
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
=======
// Cấu hình hiển thị lỗi dựa trên môi trường
if (defined('APP_ENV') && APP_ENV === 'local') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Lấy config VietQR từ env_config.php
$vietqrConfig = getVietQRConfig();

// Cấu hình VietQR
define('VIETQR_API_URL', $vietqrConfig['api_url']);
define('VIETQR_BANK_CODE', $vietqrConfig['bank_code']);
define('VIETQR_ACCOUNT_NUMBER', $vietqrConfig['account_number']);
define('VIETQR_ACCOUNT_NAME', $vietqrConfig['account_name']);

define('SIEUTHICODE_API_URL', $vietqrConfig['sieuthicode_api_url']);
define('SIEUTHICODE_TOKEN', $vietqrConfig['sieuthicode_token']);

// Cấu hình website - lấy từ env_config
define('SITE_URL', rtrim(getConfig('base_url', 'https://choviet.site'), '/') . '/');

// Development mode từ config
define('DEVELOPMENT_MODE', getConfig('development_mode', false));

// Các mức tiền có thể chọn (VND)
>>>>>>> 65997a0 (up len web)
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
