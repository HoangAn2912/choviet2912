<?php
/**
 * Configuration file for dynamic path management
 * This file helps manage paths dynamically based on the current folder
 */

// Lấy tên folder hiện tại từ đường dẫn
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

// Lấy base path động
function getDynamicBasePath() {
    $folderName = getCurrentFolderName();
    return $folderName ? '/' . $folderName : '';
}

// Lấy base URL động
function getDynamicBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $basePath = getDynamicBasePath();
    
    return $protocol . '://' . $host . $basePath;
}

// Cấu hình cho Node.js server
function getNodeServerConfig() {
    return [
        'hostname' => 'localhost',
        'port' => 8080,
        'basePath' => getDynamicBasePath()
    ];
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




