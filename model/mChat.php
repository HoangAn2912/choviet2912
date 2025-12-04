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
        
        // Gắn đúng giá trị
        $stmt->bind_param("iiii", $user1, $user2, $user2, $user1);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    

    public function getConversationUsers($currentUserId) {
        $conn = $this->connect();
        
        // Lấy danh sách từ DB
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
        $usersFromDB = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Scan file JSON trong thư mục chat để tìm conversations không có trong DB
        $chatDir = "/var/www/choviet.site/chat/";
        $existingUserIds = array_column($usersFromDB, 'id');
        $files = glob($chatDir . "chat_*_*.json");
        
        foreach ($files as $file) {
            $fileName = basename($file);
            if (preg_match('/chat_(\d+)_(\d+)\.json/', $fileName, $matches)) {
                $id1 = intval($matches[1]);
                $id2 = intval($matches[2]);
                
                // Xác định ID của người kia
                $otherUserId = ($id1 == $currentUserId) ? $id2 : (($id2 == $currentUserId) ? $id1 : null);
                
                if ($otherUserId && !in_array($otherUserId, $existingUserIds)) {
                    // Lấy thông tin user từ DB
                    $stmtUser = $conn->prepare("SELECT id, username, avatar FROM users WHERE id = ?");
                    $stmtUser->bind_param("i", $otherUserId);
                    $stmtUser->execute();
                    $result = $stmtUser->get_result();
                    
                    if ($user = $result->fetch_assoc()) {
                        // Đọc tin nhắn cuối từ file JSON
                        $messages = json_decode(file_get_contents($file), true);
                        if (is_array($messages) && count($messages) > 0) {
                            $lastMsg = end($messages);
                            $user['tin_cuoi'] = $lastMsg['content'] ?? '';
                            $user['created_time'] = ''; // Sẽ được format lại ở controller
                            $usersFromDB[] = $user;
                            $existingUserIds[] = $otherUserId;
                        }
                    }
                }
            }
        }
        
        return $usersFromDB;
    }
    
    public function sendMessage($from, $to, $content, $idSanPham = null) {
        $conn = $this->connect();
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, product_id, content, created_time) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("iiis", $from, $to, $idSanPham, $content);
        return $stmt->execute();
    }

    public function readChatFile($from, $to) {
        $ids = [$from, $to];
        sort($ids);
        $filePath = "/var/www/choviet.site/chat/chat_{$ids[0]}_{$ids[1]}.json";
    
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


    public function demTinNhanChuaDoc($idNguoiDung) {
        $conn = $this->connect();
        $stmt = $conn->prepare("SELECT COUNT(*) AS so_chua_doc FROM messages WHERE receiver_id = ? AND is_read = 0");
        $stmt->bind_param("i", $idNguoiDung);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return intval($row['so_chua_doc'] ?? 0);
    }

    /**
     * Lấy tin nhắn đầu tiên giữa 2 user từ file JSON (ưu tiên),
     * nếu không có file thì fallback về bảng messages trong DB.
     */
    public function getFirstMessage($from, $to) {
        // Ưu tiên đọc từ file JSON (chat_id1_id2.json)
        $messages = $this->readChatFile($from, $to);
        if (!empty($messages)) {
            // Mảng đã được lưu theo thời gian, nhưng đảm bảo sort tăng dần theo timestamp
            usort($messages, function ($a, $b) {
                return strtotime($a['timestamp'] ?? $a['created_time'] ?? 'now')
                     <=> strtotime($b['timestamp'] ?? $b['created_time'] ?? 'now');
            });
            return $messages[0];
        }

        // Fallback: đọc từ DB nếu vì lý do nào đó không có file JSON
        $conn = $this->connect();
        $sql = "SELECT * FROM messages 
                WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) 
                ORDER BY created_time ASC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $from, $to, $to, $from);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function markAsRead($messageId, $userId) {
        $conn = $this->connect();
        $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE id = ? AND receiver_id = ?");
        $stmt->bind_param("ii", $messageId, $userId);
        return $stmt->execute();
    }
    
    public function markConversationAsRead($user1, $user2, $userId) {
        $conn = $this->connect();
        $stmt = $conn->prepare("UPDATE messages SET is_read = 1 
            WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) 
            AND receiver_id = ? AND is_read = 0");
        $stmt->bind_param("iiiii", $user1, $user2, $user2, $user1, $userId);
        return $stmt->execute();
    }
}
