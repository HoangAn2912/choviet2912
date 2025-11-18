<?php
/**
 * ========================================
 * URL HELPER WITH ENVIRONMENT SUPPORT
 * ========================================
 * Tự động lấy cấu hình từ config/env_config.php
 * Hỗ trợ nhiều môi trường: local, production, staging
 */

/**
 * Lấy cấu hình môi trường hiện tại (với cache)
 * @return array Cấu hình môi trường
 */
function getEnvironmentConfig() {
    static $config = null;
    
    // Cache config để tối ưu performance
    if ($config !== null) {
        return $config;
    }
    
    $configPath = __DIR__ . '/../config/env_config.php';
    
    // Kiểm tra file có tồn tại
    if (!file_exists($configPath)) {
        // Fallback: Sử dụng auto-detect nếu chưa có config
        $config = [
            'base_url' => '',
            'base_path' => '',
            'force_https' => false,
            'debug' => true
        ];
        return $config;
    }
    
    try {
        $allConfigs = require $configPath;
        $env = defined('APP_ENV') ? APP_ENV : 'local';
        
        if (!isset($allConfigs[$env])) {
            // Lỗi: Môi trường không tồn tại
            if (defined('APP_ENV') && APP_ENV === 'local') {
                die("❌ LỖI CONFIG: Môi trường '$env' không tồn tại trong config/env_config.php<br>" .
                    "👉 Các môi trường có sẵn: " . implode(', ', array_keys($allConfigs)));
            } else {
                // Production: Fallback sang local
                $config = $allConfigs['local'] ?? [];
                return $config;
            }
        }
        
        $config = $allConfigs[$env];
        
    } catch (Exception $e) {
        // Lỗi khi đọc config
        if (defined('APP_ENV') && APP_ENV === 'local') {
            die("❌ LỖI khi đọc config: " . $e->getMessage());
        } else {
            // Production: Sử dụng fallback
            $config = [
                'base_url' => '',
                'base_path' => '',
                'force_https' => false,
                'debug' => false
            ];
        }
    }
    
    return $config;
}

/**
 * Lấy giá trị config theo key
 * @param string $key Key cần lấy (vd: 'db_host', 'base_url')
 * @param mixed $default Giá trị mặc định nếu không tìm thấy
 * @return mixed Giá trị config
 */
function config($key, $default = null) {
    $config = getEnvironmentConfig();
    return isset($config[$key]) ? $config[$key] : $default;
}

/**
 * Lấy base URL động dựa trên môi trường
 * @return string Base URL (vd: http://localhost:8080)
 */
function getBaseUrl() {
    $baseUrl = config('base_url');
    $basePath = config('base_path', '');
    
    // Ưu tiên: Dùng config cố định nếu có
    if (!empty($baseUrl)) {
        return rtrim($baseUrl . $basePath, '/');
    }
    
    // Fallback: Tự động phát hiện (cho trường hợp chưa config)
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    
    // Force HTTPS nếu cấu hình yêu cầu
    if (config('force_https', false)) {
        $protocol = 'https';
    }
    
    $host = $_SERVER['HTTP_HOST'];  // Bao gồm port (vd: localhost:8080)
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $path = dirname($scriptName);
    
    // Sửa lỗi: Nếu đang trong thư mục controller, đi lên 1 cấp
    if (strpos($path, '/controller') !== false) {
        $path = dirname($path);
    }
    
    // Loại bỏ trailing slash nếu không phải root
    if ($path !== '/') {
        $path = rtrim($path, '/');
    }
    
    return $protocol . '://' . $host . $path;
}

/**
 * Lấy base path cho assets (CSS, JS, images)
 * @return string Base path (vd: '' hoặc '/subfolder')
 */
function getBasePath() {
    $basePath = config('base_path');
    
    // Ưu tiên: Dùng config cố định nếu có
    if ($basePath !== null) {
        return $basePath;
    }
    
    // Fallback: Tự động phát hiện
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $path = dirname($scriptName);
    
    // Sửa lỗi: Nếu đang trong thư mục controller, đi lên 1 cấp
    if (strpos($path, '/controller') !== false) {
        $path = dirname($path);
    }
    
    // Loại bỏ trailing slash nếu không phải root
    if ($path !== '/') {
        $path = rtrim($path, '/');
    }
    
    return $path;
}

/**
 * Tạo URL thân thiện cho trang profile
 * @param int $userId ID của người dùng
 * @return string URL thân thiện hoặc fallback URL
 */
function getProfileUrl($userId) {
    require_once __DIR__ . '/../model/mProfile.php';
    $model = new mProfile();
    $user = $model->getUserById($userId);
    
    if (!$user) {
        return 'index.php?thongtin=' . $userId;
    }
    
    return $user['username'];
}

/**
 * Tạo URL thân thiện cho trang profile với slug
 * @param int $userId ID của người dùng
 * @return string URL thân thiện với slug hoặc fallback URL
 */
function getProfileUrlWithSlug($userId) {
    require_once __DIR__ . '/../model/mProfile.php';
    $model = new mProfile();
    $user = $model->getUserById($userId);
    
    if (!$user) {
        return 'index.php?thongtin=' . $userId;
    }
    
    return $model->createSlug($user['username']);
}

/**
 * ========================================
 * ENVIRONMENT HELPER FUNCTIONS
 * ========================================
 */

/**
 * Kiểm tra môi trường hiện tại
 * @return string Tên môi trường (local/production/staging)
 */
function getCurrentEnvironment() {
    return defined('APP_ENV') ? APP_ENV : 'local';
}

/**
 * Kiểm tra có phải môi trường production không
 * @return bool
 */
function isProduction() {
    return getCurrentEnvironment() === 'production';
}

/**
 * Kiểm tra có phải môi trường local không
 * @return bool
 */
function isLocal() {
    return getCurrentEnvironment() === 'local';
}

/**
 * Kiểm tra có phải môi trường staging không
 * @return bool
 */
function isStaging() {
    return getCurrentEnvironment() === 'staging';
}

/**
 * ========================================
 * DEBUG HELPER FUNCTIONS
 * ========================================
 */

/**
 * Debug helper - chỉ hiển thị ở môi trường local
 * @param mixed $data Dữ liệu cần debug
 * @param string $label Nhãn mô tả
 */
function debug($data, $label = 'DEBUG') {
    if (isLocal() && config('debug', false)) {
        echo "<pre style='background:#f0f0f0;padding:10px;margin:10px 0;border-left:4px solid #f00;font-family:monospace;font-size:12px;'>";
        echo "<strong style='color:#f00;'>🐞 $label:</strong>\n";
        print_r($data);
        echo "</pre>";
    }
}

/**
 * Log message to file (nếu cần)
 * @param string $message Message cần log
 * @param string $level Level (info/error/warning)
 */
function logMessage($message, $level = 'info') {
    if (config('debug', false)) {
        $logPath = config('log_path', __DIR__ . '/../logs');
        $logFile = $logPath . '/app.log';
        
        if (is_writable(dirname($logFile))) {
            $timestamp = date('Y-m-d H:i:s');
            $logEntry = "[$timestamp] [$level] $message\n";
            @file_put_contents($logFile, $logEntry, FILE_APPEND);
        }
    }
}

/**
 * Hiển thị thông tin môi trường (debug only)
 */
function showEnvironmentInfo() {
    if (!isLocal()) {
        return;
    }
    
    $config = getEnvironmentConfig();
    echo "<div style='background:#fff3cd;border:1px solid #ffc107;padding:15px;margin:10px 0;border-radius:5px;'>";
    echo "<h4 style='margin:0 0 10px 0;color:#856404;'>⚙️ Thông tin môi trường</h4>";
    echo "<table style='width:100%;border-collapse:collapse;'>";
    echo "<tr><td style='padding:5px;border-bottom:1px solid #ddd;'><strong>Môi trường:</strong></td><td style='padding:5px;border-bottom:1px solid #ddd;'>" . getCurrentEnvironment() . "</td></tr>";
    echo "<tr><td style='padding:5px;border-bottom:1px solid #ddd;'><strong>Base URL:</strong></td><td style='padding:5px;border-bottom:1px solid #ddd;'>" . getBaseUrl() . "</td></tr>";
    echo "<tr><td style='padding:5px;border-bottom:1px solid #ddd;'><strong>Base Path:</strong></td><td style='padding:5px;border-bottom:1px solid #ddd;'>" . getBasePath() . "</td></tr>";
    echo "<tr><td style='padding:5px;border-bottom:1px solid #ddd;'><strong>Database:</strong></td><td style='padding:5px;border-bottom:1px solid #ddd;'>" . config('db_name', 'N/A') . "</td></tr>";
    echo "<tr><td style='padding:5px;border-bottom:1px solid #ddd;'><strong>Debug Mode:</strong></td><td style='padding:5px;border-bottom:1px solid #ddd;'>" . (config('debug', false) ? '✅ Bật' : '❌ Tắt') . "</td></tr>";
    echo "</table>";
    echo "</div>";
}
?>


