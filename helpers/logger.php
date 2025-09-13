<?php
/**
 * Lớp Logger giúp ghi log chi tiết
 */
class Logger {
    private $logFile;
    
    /**
     * Khởi tạo Logger
     * 
     * @param string $logFile Đường dẫn đến file log
     */
    public function __construct($logFile = null) {
        $this->logFile = $logFile ?: __DIR__ . '/../logs/app.log';
        
        // Tạo thư mục logs nếu chưa tồn tại
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Ghi log thông tin
     * 
     * @param string $message Nội dung log
     * @param array $context Dữ liệu bổ sung
     */
    public function info($message, array $context = []) {
        $this->log('INFO', $message, $context);
    }
    
    /**
     * Ghi log lỗi
     * 
     * @param string $message Nội dung log
     * @param array $context Dữ liệu bổ sung
     */
    public function error($message, array $context = []) {
        $this->log('ERROR', $message, $context);
    }
    
    /**
     * Ghi log cảnh báo
     * 
     * @param string $message Nội dung log
     * @param array $context Dữ liệu bổ sung
     */
    public function warning($message, array $context = []) {
        $this->log('WARNING', $message, $context);
    }
    
    /**
     * Ghi log gỡ lỗi
     * 
     * @param string $message Nội dung log
     * @param array $context Dữ liệu bổ sung
     */
    public function debug($message, array $context = []) {
        $this->log('DEBUG', $message, $context);
    }
    
    /**
     * Ghi log
     * 
     * @param string $level Mức độ log
     * @param string $message Nội dung log
     * @param array $context Dữ liệu bổ sung
     */
    private function log($level, $message, array $context = []) {
        $date = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $logMessage = "[$date] [$level] $message$contextStr" . PHP_EOL;
        
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }
}
?>

