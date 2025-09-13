<?php
require_once 'model/mChat.php';

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
        require_once("model/mChat.php");
        $chatModel = new mChat();
        $users = $chatModel->getConversationUsers($current_user_id);
    
        foreach ($users as &$user) {
            $fileName = $this->getChatFileName($current_user_id, $user['id']);
            $filePath = __DIR__ . "/../chat/" . $fileName;
    
            $last = $this->getLastMessageFromFile($filePath);
    
            $user['tin_cuoi'] = $last['content'];
            $user['created_time'] = $last['created_time'];
        }
    
        return $users;
    }
    
    // ✅ Tạo tên file chat
    private function getChatFileName($id1, $id2) {
        $min = min($id1, $id2);
        $max = max($id1, $id2);
        return "chat_{$min}_{$max}.json";
    }
    
    // ✅ Đọc dòng cuối từ file JSON
    private function getLastMessageFromFile($filePath) {
        if (!file_exists($filePath)) return ['content' => '', 'created_time' => ''];
    
        $messages = json_decode(file_get_contents($filePath), true);
        if (!is_array($messages) || count($messages) === 0) return ['content' => '', 'created_time' => ''];
    
        $last = end($messages);
        if (!isset($last['content']) && isset($last['noi_dung'])) {
            $last['content'] = $last['noi_dung'];
        }
        $timestamp = strtotime($last['timestamp']);
    
        return [
            'content' => $last['content'] ?? '',
            'created_time' => $this->formatThoiGian($timestamp)
        ];
    }
    
    // ✅ Format thời gian: <1 ngày => HH:MM; >=1 ngày => tương đối ngày/tháng/năm
    private function formatThoiGian($timestamp) {
        $now = time();
        $diff = $now - $timestamp;

        if ($diff < 86400) {
            return date('H:i', $timestamp);
        }
        if ($diff < 2 * 86400) {
            return '1 ngày trước';
        }
        if ($diff < 30 * 86400) {
            return floor($diff / 86400) . ' ngày trước';
        }
        if ($diff < 365 * 86400) {
            return floor($diff / (30 * 86400)) . ' tháng trước';
        }
        return floor($diff / (365 * 86400)) . ' năm trước';
    }
    

    public function getMessagesFromFile($from, $to) {
        return $this->model->readChatFile($from, $to);
    }
   
    public function saveFileName($from, $to, $fileName) {
        return $this->model->saveFileName($from, $to, $fileName);
    }

    public function demTinNhanChuaDoc($userId) {
        $model = new mChat();
        return $model->demTinNhanChuaDoc($userId);
    }

    public function getFirstMessage($from, $to) {
        $model = new mChat();
        return $model->getFirstMessage($from, $to);
    }
}
