<?php
require_once __DIR__ . '/../model/mChat.php';
require_once __DIR__ . '/../helpers/TimeHelper.php';

class cChat {
    private $model;

    public function __construct() {
        $this->model = new mChat();
    }

    public function saveMessage($from, $to, $content) {
        return $this->model->saveMessage($from, $to, $content);
    }

    public function getMessages($from, $to) {
        return $this->model->getMessages($from, $to);
    }

    public function getConversationUsers($current_user_id) {
        $users = $this->model->getConversationUsers($current_user_id);
    
        foreach ($users as &$user) {
            $fileName = $this->getChatFileName($current_user_id, $user['id']);
            $filePath = __DIR__ . "/../chat/" . $fileName;
    
            $last = $this->getLastMessageFromFile($filePath);
    
            $user['tin_cuoi']      = $last['content'];
            $user['created_time']  = $last['created_time'];
            $user['last_ts']       = $last['ts']; // dùng để sắp xếp mới nhất lên đầu
        }
        unset($user);

        // Sắp xếp theo thời gian tin nhắn mới nhất giảm dần
        usort($users, function($a, $b) {
            return ($b['last_ts'] ?? 0) <=> ($a['last_ts'] ?? 0);
        });

        // Bỏ trường phụ trợ
        foreach ($users as &$user) {
            unset($user['last_ts']);
        }
        unset($user);
    
        return $users;
    }
    
    // Tạo tên file chat
    private function getChatFileName($id1, $id2) {
        $min = min($id1, $id2);
        $max = max($id1, $id2);
        return "chat_{$min}_{$max}.json";
    }
    
    // Đọc dòng cuối từ file JSON
    private function getLastMessageFromFile($filePath) {
        if (!file_exists($filePath)) return ['content' => '', 'created_time' => '', 'ts' => 0];
    
        $messages = json_decode(file_get_contents($filePath), true);
        if (!is_array($messages) || count($messages) === 0) return ['content' => '', 'created_time' => '', 'ts' => 0];
    
        $last = end($messages);
        if (!isset($last['content']) && isset($last['noi_dung'])) {
            $last['content'] = $last['noi_dung'];
        }
        // Lấy timestamp ưu tiên theo trường timestamp, fallback created_time
        $timestamp = 0;
        if (!empty($last['timestamp'])) {
            $timestamp = strtotime($last['timestamp']);
        } elseif (!empty($last['created_time'])) {
            $timestamp = strtotime($last['created_time']);
        }
        
        // Xử lý tin nhắn sản phẩm - extract tên sản phẩm từ HTML
        $content = $last['content'] ?? '';
        if (strpos($content, 'product-card-message') !== false) {
            // Extract tên sản phẩm từ HTML
            if (preg_match('/<h6[^>]*>([^<]+)<\/h6>/', $content, $matches)) {
                $content = 'Sản phẩm: ' . trim($matches[1]);
            } else {
                $content = 'Đã gửi sản phẩm';
            }
        }
    
        return [
            'content' => $content,
            'created_time' => $this->formatThoiGian($timestamp),
            'ts' => $timestamp
        ];
    }
    
    // Format thời gian: <1 ngày => HH:MM; >=1 ngày => tương đối ngày/tháng/năm
    private function formatThoiGian($timestamp) {
        return TimeHelper::formatRelativeTime($timestamp);
    }
    

    public function getMessagesFromFile($from, $to) {
        return $this->model->readChatFile($from, $to);
    }
   
    public function saveFileName($from, $to, $fileName) {
        return $this->model->saveFileName($from, $to, $fileName);
    }

    public function demTinNhanChuaDoc($userId) {
        return $this->model->demTinNhanChuaDoc($userId);
    }

    public function getFirstMessage($from, $to) {
        return $this->model->getFirstMessage($from, $to);
    }
    
    public function sendMessage($from, $to, $content, $idSanPham = null) {
        return $this->model->sendMessage($from, $to, $content, $idSanPham);
    }
    
    public function markAsRead($messageId, $userId) {
        return $this->model->markAsRead($messageId, $userId);
    }
    
    public function markConversationAsRead($user1, $user2, $userId) {
        return $this->model->markConversationAsRead($user1, $user2, $userId);
    }
}
