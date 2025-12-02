<?php
/**
 * Cấu hình kết nối database
 * Sử dụng MySQLi để kết nối database
 */

class Database {
    private $host = '103.90.226.19';
    private $username = 'root';
    private $password = 'enaGzxJ6KJmLRBh5DN1J';
    private $database = '22';
    private $connection;
    
    public function __construct() {
        $this->connect();
    }
    
    /**
     * Kết nối database
     */
    private function connect() {
        try {
            $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database);
            
            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }
            
            // Set charset to utf8mb4
            $this->connection->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            die("Database connection error: " . $e->getMessage());
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
