<?php
include_once("mConnect.php");

class mGiaodich {
    private $conn;
    
    public function __construct() {
        $this->conn = new Connect();
        $this->conn = $this->conn->connect();
    }
    
    // Get all transactions
    public function getAllTransactions() {
        $query = "SELECT gd.*, nd.username 
                 FROM transfer_history gd 
                 LEFT JOIN users nd ON gd.user_id = nd.id 
                 ORDER BY gd.created_date DESC";
        $result = $this->conn->query($query);
        $data = array();
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    // Get paginated transactions with filters
    public function getPaginatedTransactions($offset, $limit, $statusFilter = 'all', $typeFilter = 'all', $searchTerm = '') {
        $query = "SELECT gd.*, nd.username 
                 FROM transfer_history gd 
                 LEFT JOIN users nd ON gd.user_id = nd.id 
                 WHERE 1=1";
        
        // Apply status filter
        if ($statusFilter != 'all') {
            $query .= " AND gd.transfer_status = '" . $this->conn->real_escape_string($statusFilter) . "'";
        }
        
        // Apply type filter
        if ($typeFilter != 'all') {
            $query .= " AND gd.transfer_type = '" . $this->conn->real_escape_string($typeFilter) . "'";
        }
        
        // Apply search filter
        if (!empty($searchTerm)) {
            $searchTerm = $this->conn->real_escape_string($searchTerm);
            $query .= " AND (gd.id LIKE '%$searchTerm%' OR nd.username LIKE '%$searchTerm%')";
        }
        
        $query .= " ORDER BY gd.created_date DESC LIMIT $offset, $limit";
        
        $result = $this->conn->query($query);
        $data = array();
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    // Count transactions for pagination
    public function countTransactions($statusFilter = 'all', $typeFilter = 'all', $searchTerm = '') {
        $query = "SELECT COUNT(*) as total 
                 FROM transfer_history gd 
                 LEFT JOIN users nd ON gd.user_id = nd.id 
                 WHERE 1=1";
        
        // Apply status filter
        if ($statusFilter != 'all') {
            $query .= " AND gd.transfer_status = '" . $this->conn->real_escape_string($statusFilter) . "'";
        }
        
        // Apply type filter
        if ($typeFilter != 'all') {
            $query .= " AND gd.transfer_type = '" . $this->conn->real_escape_string($typeFilter) . "'";
        }
        
        // Apply search filter
        if (!empty($searchTerm)) {
            $searchTerm = $this->conn->real_escape_string($searchTerm);
            $query .= " AND (gd.id LIKE '%$searchTerm%' OR nd.username LIKE '%$searchTerm%')";
        }
        
        $result = $this->conn->query($query);
        $row = $result->fetch_assoc();
        
        return $row['total'];
    }
    
    // Get transaction by ID
    public function getTransactionById($id) {
        $id = $this->conn->real_escape_string($id);
        $query = "SELECT gd.*, nd.username 
                 FROM transfer_history gd 
                 LEFT JOIN users nd ON gd.user_id = nd.id 
                 WHERE gd.id = '$id'";
        
        $result = $this->conn->query($query);
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    // Add new transaction
    public function addTransaction($userId, $type, $amount, $status) {
        $userId = $this->conn->real_escape_string($userId);
        $type = $this->conn->real_escape_string($type);
        $amount = $this->conn->real_escape_string($amount);
        $status = $this->conn->real_escape_string($status);
        $date = date('Y-m-d H:i:s');
        
        $query = "INSERT INTO transfer_history (id_users, transfer_type, amount, status, created_date) 
                 VALUES ('$userId', '$type', '$amount', '$status', '$date')";
        
        if ($this->conn->query($query)) {
            return $this->conn->insert_id;
        }
        
        return false;
    }
    
    // Update transaction status
    public function updateTransactionStatus($id, $status) {
        $id = $this->conn->real_escape_string($id);
        $status = $this->conn->real_escape_string($status);
        
        $query = "UPDATE transfer_history SET status = '$status' WHERE id = '$id'";
        
        return $this->conn->query($query);
    }
    
    // Process bulk status update
    public function bulkUpdateStatus($ids, $status) {
        if (empty($ids)) return false;
        
        $idList = array();
        foreach ($ids as $id) {
            $idList[] = $this->conn->real_escape_string($id);
        }
        
        $idString = implode("','", $idList);
        $status = $this->conn->real_escape_string($status);
        
        $query = "UPDATE transfer_history SET status = '$status' WHERE id IN ('$idString')";
        
        return $this->conn->query($query);
    }
    
    // Get unique transaction types
    public function getTransactionTypes() {
        $query = "SELECT DISTINCT transfer_type FROM transfer_history ORDER BY transfer_type";
        $result = $this->conn->query($query);
        $types = array();
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $types[] = $row['transfer_type'];
            }
        }
        
        return $types;
    }
    
    // Get transaction statistics
    public function getTransactionStats() {
        $query = "SELECT 
                    COUNT(*) as total_transactions,
                    SUM(CASE WHEN status = 'Hoàn thành' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'Đang xử lý' THEN 1 ELSE 0 END) as processing,
                    SUM(CASE WHEN status = 'Hủy' THEN 1 ELSE 0 END) as cancelled,
                    SUM(CASE WHEN transfer_type = 'Nạp tiền' THEN amount ELSE 0 END) as total_deposits,
                    SUM(CASE WHEN transfer_type = 'Rút tiền' THEN amount ELSE 0 END) as total_withdrawals
                 FROM transfer_history";
        
        $result = $this->conn->query($query);
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return array(
            'total_transactions' => 0,
            'completed' => 0,
            'processing' => 0,
            'cancelled' => 0,
            'total_deposits' => 0,
            'total_withdrawals' => 0
        );
    }
    
    // Get users for dropdown
    public function getUsers() {
        $query = "SELECT id, username FROM users WHERE role_id = 2 AND is_active = 1 ORDER BY username";
        $result = $this->conn->query($query);
        $users = array();
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        
        return $users;
    }
}
?>