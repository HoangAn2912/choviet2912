<?php
/**
 * Security Helper Class
 * Xử lý CSRF, XSS, Input Validation
 */

class Security {
    
    // =============================================
    // CSRF PROTECTION
    // =============================================
    
    /**
     * Tạo CSRF token mới
     */
    public static function generateCSRFToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();
        
        return $token;
    }
    
    /**
     * Lấy CSRF token hiện tại (hoặc tạo mới nếu chưa có)
     */
    public static function getCSRFToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Tạo token mới nếu chưa có hoặc đã hết hạn (1 giờ)
        if (!isset($_SESSION['csrf_token']) || 
            !isset($_SESSION['csrf_token_time']) || 
            (time() - $_SESSION['csrf_token_time']) > 3600) {
            return self::generateCSRFToken();
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCSRFToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Kiểm tra token có tồn tại không
        if (!isset($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        
        // Kiểm tra token có khớp không
        if (!hash_equals($_SESSION['csrf_token'], $token)) {
            return false;
        }
        
        // Kiểm tra token có hết hạn không (1 giờ)
        if (isset($_SESSION['csrf_token_time']) && 
            (time() - $_SESSION['csrf_token_time']) > 3600) {
            return false;
        }
        
        return true;
    }
    
    /**
     * HTML input hidden cho CSRF token
     */
    public static function csrfField() {
        $token = self::getCSRFToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
    
    /**
     * Meta tag cho AJAX requests
     */
    public static function csrfMetaTag() {
        $token = self::getCSRFToken();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
    
    // =============================================
    // XSS PROTECTION
    // =============================================
    
    /**
     * Sanitize string để tránh XSS
     */
    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        
        // Remove null bytes
        $data = str_replace(chr(0), '', $data);
        
        // Convert special characters to HTML entities
        $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        return $data;
    }
    
    /**
     * Clean input data
     */
    public static function cleanInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'cleanInput'], $data);
        }
        
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        
        return $data;
    }
    
    /**
     * Validate email
     */
    public static function validateEmail($email) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    /**
     * Validate URL
     */
    public static function validateURL($url) {
        return filter_var($url, FILTER_VALIDATE_URL);
    }
    
    /**
     * Validate integer
     */
    public static function validateInt($value, $min = null, $max = null) {
        $value = filter_var($value, FILTER_VALIDATE_INT);
        
        if ($value === false) {
            return false;
        }
        
        if ($min !== null && $value < $min) {
            return false;
        }
        
        if ($max !== null && $value > $max) {
            return false;
        }
        
        return $value;
    }
    
    /**
     * Validate float/decimal
     */
    public static function validateFloat($value, $min = null, $max = null) {
        $value = filter_var($value, FILTER_VALIDATE_FLOAT);
        
        if ($value === false) {
            return false;
        }
        
        if ($min !== null && $value < $min) {
            return false;
        }
        
        if ($max !== null && $value > $max) {
            return false;
        }
        
        return $value;
    }
    
    // =============================================
    // FILE UPLOAD SECURITY
    // =============================================
    
    /**
     * Validate uploaded file
     */
    public static function validateUpload($file, $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'], $maxSize = 5242880) {
        $errors = [];
        
        // Kiểm tra file có tồn tại không
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            $errors[] = 'Không có file được upload';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Kiểm tra upload error
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Lỗi upload file: ' . $file['error'];
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Kiểm tra kích thước file
        if ($file['size'] > $maxSize) {
            $errors[] = 'File quá lớn. Kích thước tối đa: ' . ($maxSize / 1024 / 1024) . 'MB';
        }
        
        // Kiểm tra MIME type thực tế
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            $errors[] = 'Loại file không được phép. Chỉ cho phép: ' . implode(', ', $allowedTypes);
        }
        
        // Kiểm tra extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = 'Extension không được phép';
        }
        
        // Kiểm tra file có phải là image thực sự không
        if (strpos($mimeType, 'image/') === 0) {
            $imageInfo = getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                $errors[] = 'File không phải là ảnh hợp lệ';
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'mime_type' => $mimeType,
            'extension' => $extension
        ];
    }
    
    /**
     * Generate safe filename
     */
    public static function generateSafeFilename($originalName, $prefix = '') {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        
        return $prefix . $timestamp . '_' . $random . '.' . $extension;
    }
    
    // =============================================
    // SESSION SECURITY
    // =============================================
    
    /**
     * Khởi tạo session an toàn
     */
    public static function initSecureSession() {
        if (session_status() === PHP_SESSION_NONE) {
            // Cấu hình session bảo mật
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_samesite', 'Strict');
            
            // Sử dụng HTTPS nếu có
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                ini_set('session.cookie_secure', 1);
            }
            
            session_start();
            
            // Regenerate session ID định kỳ
            if (!isset($_SESSION['created'])) {
                $_SESSION['created'] = time();
            } else if (time() - $_SESSION['created'] > 1800) {
                // Regenerate sau 30 phút
                session_regenerate_id(true);
                $_SESSION['created'] = time();
            }
        }
    }
    
    /**
     * Validate session
     */
    public static function validateSession() {
        self::initSecureSession();
        
        // Kiểm tra User Agent
        if (!isset($_SESSION['user_agent'])) {
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        } else if ($_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
            // Session hijacking detected
            session_destroy();
            return false;
        }
        
        // Kiểm tra IP address (optional, có thể bỏ nếu user dùng mobile data)
        // if (!isset($_SESSION['ip_address'])) {
        //     $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
        // } else if ($_SESSION['ip_address'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
        //     session_destroy();
        //     return false;
        // }
        
        return true;
    }
    
    // =============================================
    // PASSWORD SECURITY
    // =============================================
    
    /**
     * Hash password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }
    
    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Validate password strength
     */
    public static function validatePasswordStrength($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Mật khẩu phải có ít nhất 1 chữ hoa';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Mật khẩu phải có ít nhất 1 chữ thường';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Mật khẩu phải có ít nhất 1 số';
        }
        
        // if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        //     $errors[] = 'Mật khẩu phải có ít nhất 1 ký tự đặc biệt';
        // }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    // =============================================
    // SQL INJECTION PREVENTION
    // =============================================
    
    /**
     * Escape string cho SQL (backup method, nên dùng prepared statements)
     */
    public static function escapeSQL($value, $connection) {
        return mysqli_real_escape_string($connection, $value);
    }
    
    // =============================================
    // GENERAL SECURITY
    // =============================================
    
    /**
     * Check if request is AJAX
     */
    public static function isAjax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Get client IP address
     */
    public static function getClientIP() {
        $ip = '';
        
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ip = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }
}
?>
































