<?php
include_once "mConnect.php";
class mTopUp {
    private $conn;
    public function __construct() {
        $db = new Connect();
        $this->conn = $db->connect();
    }
    public function insertChuyenKhoan($userId, $transfer_content, $transfer_image, $transfer_status) {
        $sql = "INSERT INTO transfer_history (user_id, transfer_content, transfer_image, transfer_status, created_date) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isss", $userId, $transfer_content, $transfer_image, $transfer_status);
        $stmt->execute();
        $stmt->close();
    }
    public function getLichSuChuyenKhoan($userId) {
        // Lấy lịch sử từ bảng transactions, chỉ lấy các giao dịch deposit (nạp tiền)
        $sql = "SELECT t.*, ta.account_number 
                FROM transactions t 
                LEFT JOIN transfer_accounts ta ON t.account_id = ta.id 
                WHERE t.user_id = ? AND t.transaction_type = 'deposit'
                ORDER BY t.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) $data[] = $row;
        $stmt->close();
        return $data;
    }
}
?>