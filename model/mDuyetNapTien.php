<?php
include_once("mConnect.php");

class mDuyetNapTien {
    private $conn;

    public function __construct() {
        $p = new Connect();
        $this->conn = $p->connect();
    }

    // Get all transactions with optional filters and pagination
    public function getAllTransactions($status = null, $userId = null, $search = null, $page = 1, $perPage = 10) {
        $query = "
            SELECT lsck.*, nd.username, nd.email, tck.balance
            FROM transfer_history lsck
            LEFT JOIN users nd ON lsck.user_id = nd.id
            LEFT JOIN transfer_accounts tck ON lsck.user_id = tck.user_id
            WHERE 1=1
        ";

        $countQuery = "
            SELECT COUNT(*) as total
            FROM transfer_history lsck
            LEFT JOIN users nd ON lsck.user_id = nd.id
            LEFT JOIN transfer_accounts tck ON lsck.user_id = tck.user_id
            WHERE 1=1
        ";

        $types = "";
        $params = [];

        if ($status !== null && $status !== '') {
            $query .= " AND lsck.transfer_status = ?";
            $countQuery .= " AND lsck.transfer_status = ?";
            $types .= "s";
            $params[] = $status;
        }

        if ($userId !== null && $userId !== '') {
            $query .= " AND lsck.user_id = ?";
            $countQuery .= " AND lsck.user_id = ?";
            $types .= "i";
            $params[] = $userId;
        }

        if ($search !== null && $search !== '') {
            $query .= " AND (nd.username LIKE ? OR nd.email LIKE ? OR lsck.transfer_content LIKE ?)";
            $countQuery .= " AND (nd.username LIKE ? OR nd.email LIKE ? OR lsck.transfer_content LIKE ?)";
            $types .= "sss";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Get total count for pagination
        $countStmt = $this->conn->prepare($countQuery);
        if (!empty($params)) {
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalCount = $countResult->fetch_assoc()['total'];
        $totalPages = ceil($totalCount / $perPage);

        // Calculate pagination
        $offset = ($page - 1) * $perPage;
        $query .= " ORDER BY lsck.created_date DESC LIMIT ?, ?";
        $types .= "ii";
        $params[] = $offset;
        $params[] = $perPage;

        // Get paginated data
        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return [
            'data' => $data,
            'pagination' => [
                'total' => $totalCount,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => $totalPages
            ]
        ];
    }

    // Get transaction by ID
    public function getTransactionById($id) {
        $query = "
            SELECT lsck.*, nd.username, nd.email, tck.balance
            FROM transfer_history lsck
            LEFT JOIN users nd ON lsck.user_id = nd.id
            LEFT JOIN transfer_accounts tck ON lsck.user_id = tck.user_id
            WHERE lsck.history_id = ?
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    // Update transaction status
    public function updateTransactionStatus($id, $status) {
        try {
            $query = "UPDATE transfer_history SET transfer_status = ? WHERE history_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("si", $status, $id);
            
            if (!$stmt->execute()) {
                throw new Exception('Không thể cập nhật trạng thái: ' . $stmt->error);
            }
            
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Update status error: " . $e->getMessage());
            throw $e;
        }
    }

    // Update multiple transaction statuses
    public function updateMultipleTransactionStatus($ids, $status) {
        if (empty($ids)) {
            return false;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $query = "
            UPDATE transfer_history
            SET transfer_status = ?
            WHERE history_id IN ($placeholders) AND transfer_status = 'Đang chờ duyệt'
        ";

        $types = "s" . str_repeat("i", count($ids));
        $params = array_merge([$status], $ids);

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        return $stmt->execute();
    }

    // Update user balance
    public function updateUserBalance($userId, $amount) {
        try {
            // Get current balance
            $query = "SELECT balance FROM transfer_accounts WHERE user_id = ? FOR UPDATE";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $account = $result->fetch_assoc();

            if (!$account) {
                throw new Exception('Tài khoản không tồn tại');
            }

            $newBalance = $account['balance'] + $amount;

            // Update balance
            $query = "UPDATE transfer_accounts SET balance = ? WHERE user_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("di", $newBalance, $userId);
            
            if (!$stmt->execute()) {
                throw new Exception('Không thể cập nhật số dư: ' . $stmt->error);
            }

            return true;
        } catch (Exception $e) {
            error_log("Update balance error: " . $e->getMessage());
            throw $e;
        }
    }

    // Extract amount from transaction content
    public function extractAmountFromContent($content) {
        preg_match('/(\d+)$/', $content, $matches);
        if (isset($matches[1])) {
            return (int)$matches[1];
        }
        return 0;
    }

    // Get transaction statistics
    public function getTransactionStatistics() {
        $query = "
            SELECT 
                COUNT(*) as total_transactions,
                SUM(CASE WHEN transfer_status = 'Đang chờ duyệt' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN transfer_status = 'Đã duyệt' THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN transfer_status = 'Từ chối duyệt' THEN 1 ELSE 0 END) as rejected_count
            FROM transfer_history
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Get users for dropdown
    public function getAllUsers() {
        $query = "SELECT id, username FROM users ORDER BY username";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        return $users;
    }

    // Begin transaction
    public function beginTransaction() {
        return $this->conn->begin_transaction();
    }

    // Commit transaction
    public function commitTransaction() {
        return $this->conn->commit();
    }

    // Rollback transaction
    public function rollbackTransaction() {
        return $this->conn->rollback();
    }
}
?>
