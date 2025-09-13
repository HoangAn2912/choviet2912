<?php
include_once 'model/mDuyetNapTien.php';

class cDuyetNapTien {
    private $model;
    
    public function __construct() {
        $this->model = new mDuyetNapTien();
    }
    
    // Get all transactions with optional filters and pagination
    public function getAllTransactions($status = null, $userId = null, $search = null, $page = 1, $perPage = 10) {
        return $this->model->getAllTransactions($status, $userId, $search, $page, $perPage);
    }
    
    // Get transaction by ID
    public function getTransactionById($id) {
        return $this->model->getTransactionById($id);
    }
    
    // Approve transaction
    public function approveTransaction($id, $customAmount = null) {
        try {
            // Get transaction details
            $transaction = $this->model->getTransactionById($id);
        
            if (!$transaction) {
                return [
                    'success' => false,
                    'message' => 'Giao dịch không tồn tại'
                ];
            }
        
            if ($transaction['transfer_status'] != 'Đang chờ duyệt') {
                return [
                    'success' => false,
                    'message' => 'Giao dịch này đã được xử lý trước đó'
                ];
            }
        
            // Extract amount from content or use custom amount
            $amount = $customAmount !== null ? $customAmount : $this->model->extractAmountFromContent($transaction['transfer_content']);
        
            if ($amount <= 0) {
                return [
                    'success' => false,
                    'message' => 'Số tiền không hợp lệ'
                ];
            }
        
            // Start transaction
            $this->model->beginTransaction();
        
            try {
                // Update transaction status to approved
                $statusUpdated = $this->model->updateTransactionStatus($id, 'Đã duyệt');
            
                if (!$statusUpdated) {
                    throw new Exception('Không thể cập nhật trạng thái giao dịch');
                }
            
                // Update user balance
                $balanceUpdated = $this->model->updateUserBalance($transaction['user_id'], $amount);
            
                if (!$balanceUpdated) {
                    throw new Exception('Không thể cập nhật số dư tài khoản');
                }
            
                $this->model->commitTransaction();
            
                return [
                    'success' => true,
                    'message' => "Giao dịch #$id đã được phê duyệt thành công! Số tiền " . number_format($amount, 0, ',', '.') . " VND đã được cộng vào tài khoản."
                ];
            
            } catch (Exception $e) {
                $this->model->rollbackTransaction();
                throw $e;
            }
        
        } catch (Exception $e) {
            error_log("Approve transaction error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ];
        }
    }
    
    // Reject transaction
    public function rejectTransaction($id) {
        // Get transaction details
        $transaction = $this->model->getTransactionById($id);
        
        if (!$transaction) {
            return [
                'success' => false,
                'message' => 'Giao dịch không tồn tại'
            ];
        }
        
        if ($transaction['transfer_status'] != 'Đang chờ duyệt') {
            return [
                'success' => false,
                'message' => 'Giao dịch này đã được xử lý trước đó'
            ];
        }
        
        // Update transaction status to rejected
        $statusUpdated = $this->model->updateTransactionStatus($id, 'Từ chối duyệt');
        
        if ($statusUpdated) {
            return [
                'success' => true,
                'message' => 'Giao dịch đã bị từ chối'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Không thể cập nhật trạng thái giao dịch'
            ];
        }
    }
    
    // Bulk approve transactions
    public function bulkApproveTransactions($ids) {
        if (empty($ids)) {
            return [
                'success' => false,
                'message' => 'Không có giao dịch nào được chọn'
            ];
        }
        
        $successCount = 0;
        $failCount = 0;
        
        foreach ($ids as $id) {
            $result = $this->approveTransaction($id);
            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
            }
        }
        
        return [
            'success' => $successCount > 0,
            'message' => "Đã phê duyệt $successCount giao dịch thành công" . ($failCount > 0 ? ", $failCount giao dịch thất bại" : "")
        ];
    }
    
    // Bulk reject transactions
    public function bulkRejectTransactions($ids) {
        if (empty($ids)) {
            return [
                'success' => false,
                'message' => 'Không có giao dịch nào được chọn'
            ];
        }
        
        // Update all selected transactions to rejected
        $statusUpdated = $this->model->updateMultipleTransactionStatus($ids, 'Từ chối duyệt');
        
        if ($statusUpdated) {
            return [
                'success' => true,
                'message' => 'Đã từ chối tất cả giao dịch đã chọn'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Không thể cập nhật trạng thái giao dịch'
            ];
        }
    }
    
    // Get transaction statistics
    public function getTransactionStatistics() {
        return $this->model->getTransactionStatistics();
    }
    
    // Get all users
    public function getAllUsers() {
        return $this->model->getAllUsers();
    }
    
    // Handle AJAX requests
    public function handleAjaxRequest() {
        // Set content type to JSON
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON data');
            }
            
            $action = $input['action'] ?? '';
            
            switch ($action) {
                case 'approve':
                    $id = $input['id'] ?? 0;
                    $amount = $input['amount'] ?? null;
                    $result = $this->approveTransaction($id, $amount);
                    break;
                    
                case 'reject':
                    $id = $input['id'] ?? 0;
                    $result = $this->rejectTransaction($id);
                    break;
                    
                case 'bulk_approve':
                    $ids = $input['ids'] ?? [];
                    $result = $this->bulkApproveTransactions($ids);
                    break;
                    
                case 'bulk_reject':
                    $ids = $input['ids'] ?? [];
                    $result = $this->bulkRejectTransactions($ids);
                    break;
                    
                default:
                    $result = ['success' => false, 'message' => 'Invalid action'];
            }
            
            echo json_encode($result);
            
        } catch (Exception $e) {
            error_log("AJAX Error: " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }
}
?>
