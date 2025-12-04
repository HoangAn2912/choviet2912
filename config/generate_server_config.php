<?php
/**
 * ========================================
 * Script tạo server_config.js từ env_config.php
 * ========================================
 * 
 * Cách sử dụng:
 * php config/generate_server_config.php [local|production|staging]
 * 
 * Script này sẽ đọc config từ env_config.php và tạo file server_config.js
 * cho Node.js server sử dụng.
 */

// Lấy môi trường từ argument hoặc mặc định 'local'
$env = $argv[1] ?? 'local';
if (!defined('APP_ENV')) {
    define('APP_ENV', $env);
}

// Load config helper
require_once __DIR__ . '/../helpers/config_helper.php';

// Lấy config Node.js server
$nodeConfig = getNodeServerConfig();

// Tạo nội dung file JS
$jsContent = "// ========================================\n";
$jsContent .= "// Auto-generated from env_config.php\n";
$jsContent .= "// DO NOT EDIT MANUALLY - Chạy: php config/generate_server_config.php [env]\n";
$jsContent .= "// Generated at: " . date('Y-m-d H:i:s') . "\n";
$jsContent .= "// Environment: " . APP_ENV . "\n";
$jsContent .= "// ========================================\n\n";
$jsContent .= "module.exports = " . json_encode($nodeConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . ";\n";

// Ghi file
$outputPath = __DIR__ . '/server_config.js';
$result = file_put_contents($outputPath, $jsContent);

if ($result !== false) {
    echo "✅ Đã tạo server_config.js thành công!\n";
    echo "📁 File: $outputPath\n";
    echo "🌍 Environment: " . APP_ENV . "\n";
    echo "📋 Config:\n";
    echo json_encode($nodeConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
} else {
    echo "❌ Lỗi: Không thể ghi file server_config.js\n";
    echo "💡 Kiểm tra quyền ghi file trong thư mục config/\n";
    exit(1);
}