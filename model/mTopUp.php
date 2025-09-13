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
        $sql = "SELECT * FROM transfer_history WHERE user_id = ? ORDER BY created_date DESC";
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