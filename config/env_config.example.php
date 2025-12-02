<?php
/**
 * ========================================
 * FILE MẪU CẤU HÌNH MÔI TRƯỜNG
 * ========================================
 * 
 * HƯỚNG DẪN SỬ DỤNG:
 * 1. Copy file này thành "env_config.php"
 * 2. Điền thông tin database và URL của bạn
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
    // CẤU HÌNH LOCAL
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
        
        // Debug
        'debug' => true,
        'cache_enabled' => false,
        'log_queries' => true,
    ],
    
    // ========================================
    // CẤU HÌNH PRODUCTION
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
        'ws_secret' => '',  // Nên thêm secret
        
        // Debug (TẮT trên production)
        'debug' => false,
        'cache_enabled' => true,
        'log_queries' => false,
    ],
];
?>









