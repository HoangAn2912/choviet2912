<?php
/**
 * File cấu hình chính của hệ thống
 */

// Cấu hình timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Cấu hình hiển thị lỗi (chỉ dùng khi development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cấu hình VietQR
define('VIETQR_API_URL', 'https://img.vietqr.io/image/');
define('VIETQR_BANK_CODE', 'VCB'); // Vietcombank
define('VIETQR_ACCOUNT_NUMBER', '1026479899');
define('VIETQR_ACCOUNT_NAME', 'TRAN THAI BAO'); // ⚠️ THAY ĐỔI TÊN THẬT

define('SIEUTHICODE_API_URL', 'https://api.sieuthicode.net/historyapivcb/');
define('SIEUTHICODE_TOKEN', '22530f3629989e71d8d3cdecad7bc9f6');

// Cấu hình website
define('SITE_URL', 'https://choviet29.page.gd/'); // ⚠️ THAY ĐỔI DOMAIN THẬT


define('DEVELOPMENT_MODE', true);

// Các mức tiền có thể chọn (VND)
define('PAYMENT_AMOUNTS', [
    20000,
    50000, 
    100000,
    200000,
    500000,
    1000000,
    2000000,
    5000000,
    10000000
]);


// Include database
require_once __DIR__ . '/database.php';
?>
