<?php
/**
 * Cấu hình kết nối database
 * Sử dụng MySQLi để kết nối database
 */

class Database {
    private $host = 'localhost';
    private $username = 'choviet_user';
    private $password = 'Choviet@123456';
    private $database = 'choviet_db';
    private $connection;
    
    public function __construct() {
        // Load main config helper if not already loaded
        if (!function_exists('config')) {
            $helperPath = __DIR__ . '/../../../helpers/url_helper.php';
            if (file_exists($helperPath)) {
                require_once $helperPath;
            }
        }
        
        // Load config from environment
        if (function_exists('config')) {
            $this->host = config('db_host', 'localhost');
            $this->username = config('db_user', 'root');
            $this->password = config('db_pass', '');
            $this->database = config('db_name', 'choviet29');
        }
        
        $this->connect();
    }
    
    /**
     * Kết nối database
     */
    private function connect() {
        try {
            // Enable error reporting for mysqli
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            
            $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database);
            
            // Set charset to utf8mb4
            $this->connection->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            // Log error instead of dying if possible, or show generic error
            error_log("VietQR Database connection error: " . $e->getMessage());
            die("Lỗi kết nối database thanh toán. Vui lòng thử lại sau.");
        }
    }
    
    /**
     * Lấy connection
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Thực hiện query
     */
    public function query($sql) {
        return $this->connection->query($sql);
    }
    
    /**
     * Thực hiện prepared statement
     */
    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }
    
    /**
     * Lấy ID của record vừa insert
     */
    public function getLastInsertId() {
        return $this->connection->insert_id;
    }
    
    /**
     * Escape string để tránh SQL injection
     */
    public function escapeString($string) {
        return $this->connection->real_escape_string($string);
    }
    
    /**
     * Đóng kết nối
     */
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
    
    /**
     * Destructor - tự động đóng kết nối
     */
    public function __destruct() {
        $this->close();
    }
}

/**
 * Singleton pattern để đảm bảo chỉ có 1 connection
 */
class DatabaseManager {
    private static $instance = null;
    private $database;
    
    private function __construct() {
        $this->database = new Database();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new DatabaseManager();
        }
        return self::$instance;
    }
    
    public function getDatabase() {
        return $this->database;
    }
}
?>
