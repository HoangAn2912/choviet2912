<?php
/**
 * ========================================
 * DATABASE CONNECTION WITH ENVIRONMENT SUPPORT
 * ========================================
 * Tự động lấy cấu hình database từ config/env_config.php
 */

// Load environment config helper
require_once __DIR__ . '/../helpers/url_helper.php';

class Connect {
    /**
     * Kết nối database với cấu hình từ môi trường
     * @return mysqli Database connection
     */
    public function connect() {
        // Thiết lập múi giờ PHP cho toàn ứng dụng
        if (function_exists('date_default_timezone_set')) {
            date_default_timezone_set('Asia/Ho_Chi_Minh');
        }

        // Lấy thông tin database từ config môi trường
        $host = config('db_host', 'localhost');
        $user = config('db_user', 'admin');
        $pass = config('db_pass', '123456');
        $dbname = config('db_name', 'choviet29');
        $charset = config('db_charset', 'utf8');
        $timezone = config('db_timezone', '+07:00');
        
        // Kết nối database
        $con = @mysqli_connect($host, $user, $pass, $dbname);
        
        if (!$con) {
            // Hiển thị lỗi khác nhau tùy môi trường
            if (isLocal()) {
                // Local: Hiển thị chi tiết để debug
                echo "<div style='background:#f8d7da;border:1px solid #f5c2c7;padding:20px;margin:20px;border-radius:5px;font-family:Arial;'>";
                echo "<h3 style='color:#842029;margin:0 0 10px 0;'>Lỗi kết nối Database (LOCAL)</h3>";
                echo "<p style='margin:5px 0;'><strong>Host:</strong> $host</p>";
                echo "<p style='margin:5px 0;'><strong>Database:</strong> $dbname</p>";
                echo "<p style='margin:5px 0;'><strong>User:</strong> $user</p>";
                echo "<p style='margin:5px 0;'><strong>Chi tiết lỗi:</strong> " . mysqli_connect_error() . "</p>";
                echo "<hr style='margin:15px 0;'>";
                echo "<p style='margin:5px 0;'><strong>Giải pháp:</strong></p>";
                echo "<ul style='margin:10px 0;padding-left:20px;'>";
                echo "<li>Kiểm tra XAMPP MySQL đã chạy chưa</li>";
                echo "<li>Kiểm tra thông tin trong <code>config/env_config.php</code></li>";
                echo "<li>Kiểm tra user và password trong phpMyAdmin</li>";
                echo "</ul>";
                echo "</div>";
            } else {
                // Production: Hiển thị thông báo chung (bảo mật)
                echo "<div style='background:#f8d7da;padding:20px;text-align:center;'>";
                echo "<h3>Không thể kết nối cơ sở dữ liệu</h3>";
                echo "<p>Vui lòng thử lại sau hoặc liên hệ quản trị viên.</p>";
                echo "</div>";
                
                // Log lỗi (nếu có thể)
                @error_log("Database connection failed: " . mysqli_connect_error());
            }
            exit();
        } else {
            // Thiết lập charset
            mysqli_query($con, "SET NAMES '$charset'");
            
            // Thiết lập múi giờ cho phiên MySQL để NOW()/TIMESTAMP đồng bộ
            @mysqli_query($con, "SET time_zone = '$timezone'");
            
            // Log kết nối thành công (chỉ ở local nếu debug bật)
            if (isLocal() && config('debug', false)) {
                // Có thể uncomment để debug
                // logMessage("Database connected successfully to $dbname", 'info');
            }
            
            return $con;
        }
    }
    
    /**
     * Lấy thông tin database hiện tại (debug only)
     * @return array
     */
    public function getDatabaseInfo() {
        if (!isLocal()) {
            return [];
        }
        
        return [
            'host' => config('db_host', 'N/A'),
            'database' => config('db_name', 'N/A'),
            'user' => config('db_user', 'N/A'),
            'charset' => config('db_charset', 'utf8'),
            'environment' => getCurrentEnvironment()
        ];
    }
}
?>

