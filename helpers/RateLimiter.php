<?php
/**
 * Rate Limiter Class
 * Chống spam và DDoS attacks
 */

class RateLimiter {
    private $redis = null;
    private $useRedis = false;
    private $dataDir = '';
    
    public function __construct() {
        // Thử kết nối Redis nếu có
        if (extension_loaded('redis')) {
            try {
                $this->redis = new Redis();
                $this->redis->connect('127.0.0.1', 6379);
                $this->useRedis = true;
            } catch (Exception $e) {
                $this->useRedis = false;
            }
        }
        
        // Sử dụng file storage nếu không có Redis
        if (!$this->useRedis) {
            $this->dataDir = __DIR__ . '/../logs/rate_limit/';
            if (!file_exists($this->dataDir)) {
                mkdir($this->dataDir, 0755, true);
            }
        }
    }
    
    /**
     * Check rate limit
     * 
     * @param string $key Unique identifier (IP, user_id, etc)
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $decaySeconds Time window in seconds
     * @return array ['allowed' => bool, 'remaining' => int, 'reset_at' => timestamp]
     */
    public function check($key, $maxAttempts = 60, $decaySeconds = 60) {
        $identifier = $this->getIdentifier($key);
        
        if ($this->useRedis) {
            return $this->checkRedis($identifier, $maxAttempts, $decaySeconds);
        } else {
            return $this->checkFile($identifier, $maxAttempts, $decaySeconds);
        }
    }
    
    /**
     * Redis-based rate limiting
     */
    private function checkRedis($identifier, $maxAttempts, $decaySeconds) {
        $current = $this->redis->get($identifier);
        
        if ($current === false) {
            // First attempt
            $this->redis->setex($identifier, $decaySeconds, 1);
            return [
                'allowed' => true,
                'remaining' => $maxAttempts - 1,
                'reset_at' => time() + $decaySeconds
            ];
        }
        
        $current = intval($current);
        
        if ($current >= $maxAttempts) {
            $ttl = $this->redis->ttl($identifier);
            return [
                'allowed' => false,
                'remaining' => 0,
                'reset_at' => time() + $ttl,
                'retry_after' => $ttl
            ];
        }
        
        // Increment counter
        $this->redis->incr($identifier);
        $ttl = $this->redis->ttl($identifier);
        
        return [
            'allowed' => true,
            'remaining' => $maxAttempts - ($current + 1),
            'reset_at' => time() + $ttl
        ];
    }
    
    /**
     * File-based rate limiting (fallback)
     */
    private function checkFile($identifier, $maxAttempts, $decaySeconds) {
        $filename = $this->dataDir . md5($identifier) . '.json';
        
        // Đọc dữ liệu hiện tại
        $data = $this->readRateLimitFile($filename);
        
        $now = time();
        
        // Reset nếu đã hết thời gian
        if ($data && isset($data['reset_at']) && $now >= $data['reset_at']) {
            $data = null;
        }
        
        if (!$data) {
            // First attempt hoặc đã reset
            $data = [
                'attempts' => 1,
                'reset_at' => $now + $decaySeconds
            ];
            $this->writeRateLimitFile($filename, $data);
            
            return [
                'allowed' => true,
                'remaining' => $maxAttempts - 1,
                'reset_at' => $data['reset_at']
            ];
        }
        
        // Kiểm tra số lần thử
        if ($data['attempts'] >= $maxAttempts) {
            return [
                'allowed' => false,
                'remaining' => 0,
                'reset_at' => $data['reset_at'],
                'retry_after' => $data['reset_at'] - $now
            ];
        }
        
        // Tăng số lần thử
        $data['attempts']++;
        $this->writeRateLimitFile($filename, $data);
        
        return [
            'allowed' => true,
            'remaining' => $maxAttempts - $data['attempts'],
            'reset_at' => $data['reset_at']
        ];
    }
    
    /**
     * Clear rate limit for a key
     */
    public function clear($key) {
        $identifier = $this->getIdentifier($key);
        
        if ($this->useRedis) {
            $this->redis->del($identifier);
        } else {
            $filename = $this->dataDir . md5($identifier) . '.json';
            if (file_exists($filename)) {
                unlink($filename);
            }
        }
    }
    
    /**
     * Get unique identifier
     */
    private function getIdentifier($key) {
        return 'rate_limit:' . $key;
    }
    
    /**
     * Read rate limit file
     */
    private function readRateLimitFile($filename) {
        if (!file_exists($filename)) {
            return null;
        }
        
        $content = file_get_contents($filename);
        if ($content === false) {
            return null;
        }
        
        $data = json_decode($content, true);
        return $data ?: null;
    }
    
    /**
     * Write rate limit file
     */
    private function writeRateLimitFile($filename, $data) {
        file_put_contents($filename, json_encode($data));
    }
    
    /**
     * Clean up old rate limit files
     */
    public function cleanup() {
        if ($this->useRedis) {
            return; // Redis tự động xóa với TTL
        }
        
        $files = glob($this->dataDir . '*.json');
        $now = time();
        
        foreach ($files as $file) {
            $data = $this->readRateLimitFile($file);
            if ($data && isset($data['reset_at']) && $now >= $data['reset_at']) {
                unlink($file);
            }
        }
    }
    
    /**
     * Middleware để check rate limit cho API
     * 
     * @param string $endpoint API endpoint name
     * @param int $maxAttempts Maximum requests
     * @param int $decaySeconds Time window
     * @return bool
     */
    public static function middleware($endpoint = 'api', $maxAttempts = 60, $decaySeconds = 60) {
        $limiter = new self();
        
        // Sử dụng IP + User ID (nếu có) làm key
        $ip = Security::getClientIP();
        $userId = $_SESSION['user_id'] ?? 'guest';
        $key = $endpoint . ':' . $ip . ':' . $userId;
        
        $result = $limiter->check($key, $maxAttempts, $decaySeconds);
        
        // Set headers
        header('X-RateLimit-Limit: ' . $maxAttempts);
        header('X-RateLimit-Remaining: ' . $result['remaining']);
        header('X-RateLimit-Reset: ' . $result['reset_at']);
        
        if (!$result['allowed']) {
            header('Retry-After: ' . $result['retry_after']);
            header('HTTP/1.1 429 Too Many Requests');
            
            http_response_code(429);
            echo json_encode([
                'success' => false,
                'message' => 'Quá nhiều yêu cầu. Vui lòng thử lại sau ' . $result['retry_after'] . ' giây.',
                'retry_after' => $result['retry_after']
            ]);
            exit;
        }
        
        return true;
    }
    
    /**
     * Presets cho các loại rate limit khác nhau
     */
    public static function forLogin() {
        return self::middleware('login', 5, 300); // 5 lần / 5 phút
    }
    
    public static function forAPI() {
        return self::middleware('api', 60, 60); // 60 lần / 1 phút
    }
    
    public static function forUpload() {
        return self::middleware('upload', 10, 300); // 10 lần / 5 phút
    }
    
    public static function forChat() {
        return self::middleware('chat', 30, 60); // 30 tin nhắn / 1 phút
    }
}
?>



















