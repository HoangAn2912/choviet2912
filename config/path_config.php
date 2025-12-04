<<<<<<< HEAD
<?php
/**
 * ========================================
 * PATH CONFIG - Lấy từ env_config.php
 * ========================================
 * File này tự động lấy config paths từ env_config.php
 * Không cần chỉnh sửa file này, chỉnh env_config.php là đủ
 */

// Load config helper
require_once __DIR__ . '/../helpers/config_helper.php';

// Lấy tên folder hiện tại từ đường dẫn (fallback nếu không có config)
function getCurrentFolderName() {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $pathParts = explode('/', trim($scriptName, '/'));
    
    // Nếu đang ở root thì trả về empty string
    if (count($pathParts) <= 1) {
        return '';
    }
    
    // Trả về folder đầu tiên (không phải file)
    return $pathParts[0];
}

// Lấy base path động (ưu tiên config từ env_config.php)
function getDynamicBasePath() {
    $basePath = getConfig('base_path', '');
    
    // Nếu có config thì dùng config
    if ($basePath !== '') {
        return $basePath;
    }
    
    // Fallback: Tự động phát hiện
    $folderName = getCurrentFolderName();
    return $folderName ? '/' . $folderName : '';
}

// Lấy base URL động (ưu tiên config từ env_config.php)
function getDynamicBaseUrl() {
    $baseUrl = getConfig('base_url', '');
    
    // Nếu có config thì dùng config
    if (!empty($baseUrl)) {
        $basePath = getDynamicBasePath();
        return rtrim($baseUrl . $basePath, '/');
    }
    
    // Fallback: Tự động phát hiện
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    
    // Force HTTPS nếu config yêu cầu
    if (getConfig('force_https', false)) {
        $protocol = 'https';
    }
    
    $host = $_SERVER['HTTP_HOST'];
    $basePath = getDynamicBasePath();
    
    return $protocol . '://' . $host . $basePath;
}

// Cấu hình cho Node.js server (lấy từ env_config.php)
function getNodeServerConfig() {
    // Lấy config từ helper (helper đã được load ở đầu file)
    $nodeConfig = [
        'hostname' => getConfig('node_host', 'localhost'),
        'port' => getConfig('node_port', 8080),
        'basePath' => getDynamicBasePath(),
        'wsHost' => getConfig('ws_host', 'localhost'),
        'wsPort' => getConfig('ws_port', 3000),
        'wsSecret' => getConfig('ws_secret', ''),
        'chatPath' => getConfig('chat_path', '')
    ];
    
    return $nodeConfig;
}

// Tạo JavaScript config cho frontend
function generateJsConfig() {
    $config = [
        'basePath' => getDynamicBasePath(),
        'baseUrl' => getDynamicBaseUrl(),
        'apiPath' => getDynamicBasePath() . '/api'
    ];
    
    return 'window.APP_CONFIG = ' . json_encode($config) . ';';
}
?>




=======
<?php
/**
 * ========================================
 * PATH CONFIG - Lấy từ env_config.php
 * ========================================
 * File này tự động lấy config paths từ env_config.php
 * Không cần chỉnh sửa file này, chỉnh env_config.php là đủ
 */

// Load config helper
require_once __DIR__ . '/../helpers/config_helper.php';

// Lấy tên folder hiện tại từ đường dẫn (fallback nếu không có config)
function getCurrentFolderName() {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $pathParts = explode('/', trim($scriptName, '/'));
    
    // Nếu đang ở root thì trả về empty string
    if (count($pathParts) <= 1) {
        return '';
    }
    
    // Trả về folder đầu tiên (không phải file)
    return $pathParts[0];
}

// Lấy base path động (ưu tiên config từ env_config.php)
function getDynamicBasePath() {
    $basePath = getConfig('base_path', '');
    
    // Nếu có config thì dùng config
    if ($basePath !== '') {
        return $basePath;
    }
    
    // Fallback: Tự động phát hiện
    $folderName = getCurrentFolderName();
    return $folderName ? '/' . $folderName : '';
}

// Lấy base URL động (ưu tiên config từ env_config.php)
function getDynamicBaseUrl() {
    $baseUrl = getConfig('base_url', '');
    
    // Nếu có config thì dùng config
    if (!empty($baseUrl)) {
        $basePath = getDynamicBasePath();
        return rtrim($baseUrl . $basePath, '/');
    }
    
    // Fallback: Tự động phát hiện
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    
    // Force HTTPS nếu config yêu cầu
    if (getConfig('force_https', false)) {
        $protocol = 'https';
    }
    
    $host = $_SERVER['HTTP_HOST'];
    $basePath = getDynamicBasePath();
    
    return $protocol . '://' . $host . $basePath;
}

// Cấu hình cho Node.js server (lấy từ env_config.php)
function getNodeServerConfig() {
    // Lấy config từ helper (helper đã được load ở đầu file)
    $nodeConfig = [
        'hostname' => getConfig('node_host', 'localhost'),
        'port' => getConfig('node_port', 8080),
        'basePath' => getDynamicBasePath(),
        'wsHost' => getConfig('ws_host', 'localhost'),
        'wsPort' => getConfig('ws_port', 3000),
        'wsSecret' => getConfig('ws_secret', ''),
        'chatPath' => getConfig('chat_path', ''),
        // SSL config cho WebSocket server
        'sslDomain' => getConfig('ssl_domain', ''),
        'sslKeyPath' => getConfig('ssl_key_path', ''),
        'sslCertPath' => getConfig('ssl_cert_path', '')
    ];
    
    return $nodeConfig;
}

// Tạo JavaScript config cho frontend
function generateJsConfig() {
    $config = [
        'basePath' => getDynamicBasePath(),
        'baseUrl' => getDynamicBaseUrl(),
        'apiPath' => getDynamicBasePath() . '/api',
        'wsPort' => getConfig('ws_port', 3000)
    ];
    
    return 'window.APP_CONFIG = ' . json_encode($config) . ';';
}
?>




>>>>>>> 65997a0 (up len web)
