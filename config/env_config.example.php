<?php
/**
 * ========================================
 * FILE MẪU CẤU HÌNH MÔI TRƯỜNG
 * ========================================
 * 
 * HƯỚNG DẪN SỬ DỤNG:
 * 1. Copy file này thành "env_config.php"
 * 2. Điền thông tin database, email, VietQR, URL của bạn
 * 3. Đổi APP_ENV thành 'production' khi deploy
 * 
 * LƯU Ý: File env_config.php chứa thông tin nhạy cảm
 * Không commit file đó lên Git!
 * ========================================
 */

// THAY ĐỔI DÒNG NÀY: 'local' hoặc 'production' hoặc 'staging'
define('APP_ENV', 'local');

// Tự động bật/tắt error reporting
if (APP_ENV === 'local') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

return [
    // ========================================
    // CẤU HÌNH LOCAL (XAMPP)
    // ========================================
    'local' => [
        // URL & Path
        'base_url' => 'http://localhost:8080',  // Thay đổi nếu cần
        'base_path' => '',
        'force_https' => false,
        
        // Database - ĐIỀN THÔNG TIN CỦA BẠN
        'db_host' => 'localhost',
        'db_user' => 'YOUR_DB_USERNAME',  // ĐỔI
        'db_pass' => 'YOUR_DB_PASSWORD',  // ĐỔI
        'db_name' => 'YOUR_DB_NAME',      // ĐỔI
        'db_charset' => 'utf8',
        'db_timezone' => '+07:00',
        
        // Paths (Windows - XAMPP)
        'project_root' => 'D:\\xampp\\htdocs',  // ĐỔI nếu cần
        'chat_path' => 'D:\\xampp\\htdocs\\chat',
        'upload_path' => 'D:\\xampp\\htdocs\\img',
        'log_path' => 'D:\\xampp\\htdocs\\logs',
        
        // Node.js
        'node_host' => 'localhost',
        'node_port' => 8080,
        'ws_host' => 'localhost',
        'ws_port' => 3000,
        'ws_secret' => '',
        
        // Email SMTP - ĐIỀN THÔNG TIN CỦA BẠN
        'email_host' => 'smtp.gmail.com',
        'email_username' => 'YOUR_EMAIL@gmail.com',  // ĐỔI
        'email_password' => 'YOUR_APP_PASSWORD',  // ĐỔI (App Password từ Google)
        'email_port' => 587,
        'email_encryption' => 'tls',
        'email_from' => 'YOUR_EMAIL@gmail.com',  // ĐỔI
        'email_from_name' => 'Chợ Việt',
        
        // VietQR Payment - ĐIỀN THÔNG TIN CỦA BẠN
        'vietqr_api_url' => 'https://img.vietqr.io/image/',
        'vietqr_bank_code' => 'VCB',
        'vietqr_account_number' => 'YOUR_ACCOUNT_NUMBER',  // ĐỔI
        'vietqr_account_name' => 'YOUR_ACCOUNT_NAME',  // ĐỔI
        'sieuthicode_api_url' => 'https://api.sieuthicode.net/historyapivcb/',
        'sieuthicode_token' => 'YOUR_SIEUTHICODE_TOKEN',  // ĐỔI
        'payment_amounts' => [20000, 50000, 100000, 200000, 500000, 1000000, 2000000, 5000000, 10000000],
        'development_mode' => true,
        
        // Debug
        'debug' => true,
        'cache_enabled' => false,
        'log_queries' => true,
    ],
    
    // ========================================
    // CẤU HÌNH PRODUCTION (HOSTING)
    // ========================================
    'production' => [
        // URL & Path - ĐIỀN DOMAIN CỦA BẠN
        'base_url' => 'https://yourdomain.com',  // ĐỔI
        'base_path' => '',  // Nếu trong subfolder: '/subfolder'
        'force_https' => true,
        
        // Database - LẤY TỪ CPANEL/HOSTING
        'db_host' => 'localhost',
        'db_user' => 'HOSTING_DB_USER',     // ĐỔI
        'db_pass' => 'HOSTING_DB_PASSWORD', // ĐỔI
        'db_name' => 'HOSTING_DB_NAME',     // ĐỔI
        'db_charset' => 'utf8',
        'db_timezone' => '+07:00',
        
        // Paths (Linux) - LẤY TỪ HOSTING
        'project_root' => '/home/username/public_html',  // ĐỔI
        'chat_path' => '/home/username/public_html/chat',
        'upload_path' => '/home/username/public_html/img',
        'log_path' => '/home/username/public_html/logs',
        
        // Node.js
        'node_host' => 'yourdomain.com',  // ĐỔI
        'node_port' => 8080,
        'ws_host' => 'yourdomain.com',    // ĐỔI
        'ws_port' => 3000,
        'ws_secret' => 'YOUR_WEBSOCKET_SECRET',  // ĐỔI (nên có)
        
        // Email SMTP - ĐIỀN THÔNG TIN CỦA BẠN
        'email_host' => 'smtp.gmail.com',
        'email_username' => 'YOUR_EMAIL@gmail.com',  // ĐỔI
        'email_password' => 'YOUR_APP_PASSWORD',  // ĐỔI
        'email_port' => 587,
        'email_encryption' => 'tls',
        'email_from' => 'YOUR_EMAIL@gmail.com',  // ĐỔI
        'email_from_name' => 'Chợ Việt',
        
        // VietQR Payment - ĐIỀN THÔNG TIN CỦA BẠN
        'vietqr_api_url' => 'https://img.vietqr.io/image/',
        'vietqr_bank_code' => 'VCB',
        'vietqr_account_number' => 'YOUR_ACCOUNT_NUMBER',  // ĐỔI
        'vietqr_account_name' => 'YOUR_ACCOUNT_NAME',  // ĐỔI
        'sieuthicode_api_url' => 'https://api.sieuthicode.net/historyapivcb/',
        'sieuthicode_token' => 'YOUR_SIEUTHICODE_TOKEN',  // ĐỔI
        'payment_amounts' => [20000, 50000, 100000, 200000, 500000, 1000000, 2000000, 5000000, 10000000],
        'development_mode' => false,
        
        // Debug (TẮT trên production)
        'debug' => false,
        'cache_enabled' => true,
        'log_queries' => false,
    ],
    
    // ========================================
    // 🧪 CẤU HÌNH STAGING (Tùy chọn - cho test)
    // ========================================
    'staging' => [
        'base_url' => 'https://test.yourdomain.com',
        'base_path' => '',
        'force_https' => true,
        'db_host' => 'localhost',
        'db_user' => 'test_user',
        'db_pass' => 'test_pass',
        'db_name' => 'test_database',
        'db_charset' => 'utf8',
        'db_timezone' => '+07:00',
        'project_root' => '/home/username/staging',
        'chat_path' => '/home/username/staging/chat',
        'upload_path' => '/home/username/staging/img',
        'log_path' => '/home/username/staging/logs',
        'node_host' => 'test.yourdomain.com',
        'node_port' => 8080,
        'ws_host' => 'test.yourdomain.com',
        'ws_port' => 3000,
        'ws_secret' => '',
        'email_host' => 'smtp.gmail.com',
        'email_username' => 'test_email@gmail.com',
        'email_password' => 'test_password',
        'email_port' => 587,
        'email_encryption' => 'tls',
        'email_from' => 'test_email@gmail.com',
        'email_from_name' => 'Chợ Việt',
        'vietqr_api_url' => 'https://img.vietqr.io/image/',
        'vietqr_bank_code' => 'VCB',
        'vietqr_account_number' => '1026479899',
        'vietqr_account_name' => 'TRAN THAI BAO',
        'sieuthicode_api_url' => 'https://api.sieuthicode.net/historyapivcb/',
        'sieuthicode_token' => 'test_token',
        'payment_amounts' => [20000, 50000, 100000, 200000, 500000, 1000000, 2000000, 5000000, 10000000],
        'development_mode' => true,
        'debug' => true,
        'cache_enabled' => false,
        'log_queries' => true,
    ],
];
?>
