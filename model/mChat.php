<?php
require_once 'mConnect.php';

class mChat extends Connect {
    public function saveMessage($from, $to, $content) {
        $conn = $this->connect();
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $from, $to, $content);
        return $stmt->execute();
    }    

    public function getMessages($user1, $user2) {
        $conn = $this->connect();
        
        // Đảm bảo chỉ lấy các tin nhắn từ 2 người, không bị hoán vị lặp
        $stmt = $conn->prepare("
            SELECT * FROM messages 
            WHERE (sender_id = ? AND receiver_id = ?) 
               OR (sender_id = ? AND receiver_id = ?)
            ORDER BY created_time ASC
        ");
        
        // 🛠 Gắn đúng giá trị
        $stmt->bind_param("iiii", $user1, $user2, $user2, $user1);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    

    public function getConversationUsers($currentUserId) {
        $conn = $this->connect();
        $stmt = $conn->prepare("SELECT u.id, u.username, u.avatar,
                (SELECT content FROM messages 
                 WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id)
                 ORDER BY created_time DESC LIMIT 1) as tin_cuoi,
                (SELECT DATE_FORMAT(created_time, '%H:%i %d/%m') FROM messages 
                 WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id)
                 ORDER BY created_time DESC LIMIT 1) as created_time
            FROM users u
            WHERE u.id != ?
              AND EXISTS (
                  SELECT 1 FROM messages t 
                  WHERE (t.sender_id = ? AND t.receiver_id = u.id) 
                     OR (t.sender_id = u.id AND t.receiver_id = ?)
              )
            ORDER BY created_time DESC
        ");
        
        $stmt->bind_param("iiiiiii", 
            $currentUserId, $currentUserId, 
            $currentUserId, $currentUserId, 
            $currentUserId, $currentUserId, $currentUserId
        );
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function sendMessage($from, $to, $content, $idSanPham = null) {
        $conn = $this->connect();
        $min = min($from, $to);
        $max = max($from, $to);
        $fileName = "chat_" . $min . "_" . $max . ".json";
    
        // ⚠️ Kiểm tra nếu chưa có dòng nào thì mới insert tên file vào DB
        $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM messages WHERE 
            (sender_id = ? AND receiver_id = ?) OR 
            (sender_id = ? AND receiver_id = ?)");
        $stmtCheck->bind_param("iiii", $from, $to, $to, $from);
        $stmtCheck->execute();
        $stmtCheck->bind_result($count);
        $stmtCheck->fetch();
        $stmtCheck->close();
    
        if ($count == 0) {
            // ✅ Chỉ lưu 1 dòng duy nhất để ghi nhớ tên file
            $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, product_id, content, created_time) 
                                    VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("iiis", $from, $to, $idSanPham, $fileName);
            return $stmt->execute();
        }
    
        return true; // Nếu đã có rồi thì không cần lưu thêm nữa
    }

    public function readChatFile($from, $to) {
        $ids = [$from, $to];
        sort($ids);
        $filePath = __DIR__ . "/../../chat/chat_{$ids[0]}_{$ids[1]}.json";
    
        if (!file_exists($filePath)) return [];
    
        $messages = json_decode(file_get_contents($filePath), true);
        if (!is_array($messages)) return [];
        // Chuẩn hóa: chuyển noi_dung -> content để tương thích mới
        foreach ($messages as &$msg) {
            if (!isset($msg['content']) && isset($msg['noi_dung'])) {
                $msg['content'] = $msg['noi_dung'];
                unset($msg['noi_dung']);
            }
        }
        unset($msg);
        return $messages;
    }
    
    public function getChatFileName($user1, $user2) {
        $conn = $this->connect();
        $stmt = $conn->prepare("SELECT content FROM messages 
            WHERE ((sender_id = ? AND receiver_id = ?) 
                OR (sender_id = ? AND receiver_id = ?))
                ORDER BY created_time ASC LIMIT 1");
        $stmt->bind_param("iiii", $user1, $user2, $user2, $user1);
        $stmt->execute();
        $stmt->bind_result($fileName);
        $stmt->fetch();
        $stmt->close();
        return $fileName;
    }

    public function saveFileName($from, $to, $fileName) {
        $conn = $this->connect();
    
        // Kiểm tra xem đã tồn tại đoạn chat chưa
        $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM messages WHERE 
            (sender_id = ? AND receiver_id = ?) 
            OR (sender_id = ? AND receiver_id = ?)");
        $stmtCheck->bind_param("iiii", $from, $to, $to, $from);
        $stmtCheck->execute();
        $stmtCheck->bind_result($count);
        $stmtCheck->fetch();
        $stmtCheck->close();
    
        if ($count == 0) {
            $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content, created_time) 
                                    VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("iis", $from, $to, $fileName);
            return $stmt->execute();
        }
    
        return true; // Đã tồn tại thì không cần lưu thêm
    }

    public function getLastMessageFromFile($user1_id, $user2_id) {
        $file1 = "chat/chat_{$user1_id}_{$user2_id}.json";
        $file2 = "chat/chat_{$user2_id}_{$user1_id}.json";
        $file = file_exists($file1) ? $file1 : (file_exists($file2) ? $file2 : null);
    
        if (!$file) return ['content' => '', 'created_time' => ''];
    
        $messages = json_decode(file_get_contents($file), true);
        if (!$messages || count($messages) === 0) return ['content' => '', 'created_time' => ''];
    
        $last = end($messages);
        $timestamp = strtotime($last['timestamp']);
        return [
            'content' => $last['content'],
            'created_time' => $this->tinhThoiGian($timestamp)
        ];
    }
    
    private function tinhThoiGian($timestamp) {
        $now = time();
        $diff = $now - $timestamp;
        if ($diff < 86400) return date('H:i', $timestamp);
        elseif ($diff < 2 * 86400) return '1 ngày trước';
        elseif ($diff < 30 * 86400) return floor($diff / 86400) . ' ngày trước';
        elseif ($diff < 365 * 86400) return floor($diff / (30 * 86400)) . ' tháng trước';
        else return floor($diff / (365 * 86400)) . ' năm trước';
    }

    public function demTinNhanChuaDoc($idNguoiDung) {
        $conn = (new mConnect())->connect();
        $sql = "SELECT COUNT(*) AS so_chua_doc FROM messages WHERE receiver_id = ? AND is_read = 0";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$idNguoiDung]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return intval($row['so_chua_doc'] ?? 0);
    }

    public function getFirstMessage($from, $to) {
        $conn = $this->connect();
        $sql = "SELECT * FROM messages 
                WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) 
                ORDER BY created_time ASC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $from, $to, $to, $from);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    
    
    
}
