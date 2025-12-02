<?php
/**
 * Class quản lý thanh toán
 * Xử lý tạo giao dịch, generate QR code, cập nhật balance
 */

require_once __DIR__ . '/../config/config.php';

class PaymentManager {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseManager::getInstance()->getDatabase();
    }
    
    /**
     * Tạo giao dịch mới
     */
    public function createTransaction($userId, $accountId, $amount, $notes = '') {
        try {
            // Tạo transaction ID unique
            $transactionId = 'TXN' . time() . rand(1000, 9999);
            
            // Prepare statement để insert transaction
            $stmt = $this->db->prepare("
                INSERT INTO transactions 
                (transaction_id, user_id, account_id, amount, notes, status) 
                VALUES (?, ?, ?, ?, ?, 'pending')
            ");
            
            $stmt->bind_param("siids", $transactionId, $userId, $accountId, $amount, $notes);
            
            if ($stmt->execute()) {
                $transactionDbId = $this->db->getLastInsertId();
                $stmt->close();
                
                return [
                    'success' => true,
                    'transaction_id' => $transactionId,
                    'db_id' => $transactionDbId,
                    'amount' => $amount
                ];
            } else {
                $stmt->close();
                return ['success' => false, 'error' => 'Không thể tạo giao dịch'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Lấy thông tin tài khoản theo user_id
     */
    public function getAccountByUserId($userId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM transfer_accounts WHERE user_id = ? LIMIT 1");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $account = $result->fetch_assoc();
            $stmt->close();
            
            return $account;
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Cập nhật balance sau khi nhận được callback
     */
    public function updateBalance($transactionId, $amount, $callbackData = null) {
        try {
            $this->db->getConnection()->begin_transaction();
            
            // Lấy thông tin transaction
            $stmt = $this->db->prepare("
                SELECT t.*, ta.id as account_id 
                FROM transactions t 
                JOIN transfer_accounts ta ON t.account_id = ta.id 
                WHERE t.transaction_id = ? AND t.status = 'pending'
            ");
            $stmt->bind_param("s", $transactionId);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $transaction = $result->fetch_assoc();
            $stmt->close();
            
            if (!$transaction) {
                throw new Exception("Transaction không tồn tại hoặc đã được xử lý");
            }
            
            // Cập nhật balance
            $stmt = $this->db->prepare("
                UPDATE transfer_accounts 
                SET balance = balance + ? 
                WHERE id = ?
            ");
            $stmt->bind_param("di", $amount, $transaction['account_id']);
            $stmt->execute();
            $stmt->close();
            
            // Cập nhật trạng thái transaction
            $callbackJson = $callbackData ? json_encode($callbackData) : null;
            $stmt = $this->db->prepare("
                UPDATE transactions 
                SET status = 'completed', callback_data = ?, updated_at = NOW() 
                WHERE transaction_id = ?
            ");
            $stmt->bind_param("ss", $callbackJson, $transactionId);
            $stmt->execute();
            $stmt->close();
            
            $this->db->getConnection()->commit();
            
            return ['success' => true, 'message' => 'Cập nhật balance thành công'];
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Lấy lịch sử giao dịch
     */
    public function getTransactionHistory($userId, $limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT t.*, ta.account_number 
                FROM transactions t 
                JOIN transfer_accounts ta ON t.account_id = ta.id 
                WHERE t.user_id = ? 
                ORDER BY t.created_at DESC 
                LIMIT ?
            ");
            $stmt->bind_param("ii", $userId, $limit);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $transactions = [];
            
            while ($row = $result->fetch_assoc()) {
                $transactions[] = $row;
            }
            
            $stmt->close();
            return $transactions;
            
        } catch (Exception $e) {
            return [];
        }
    }
}
?>
