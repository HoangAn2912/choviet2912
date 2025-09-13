<?php
include_once("model/mQLgiaodich.php");

class cGiaodich {
    private $model;
    
    public function __construct() {
        $this->model = new mGiaodich();
    }
    
    // Get all transactions
    public function getAllTransactions() {
        return $this->model->getAllTransactions();
    }
    
    // Get paginated transactions with filters
    public function getPaginatedTransactions($offset, $limit, $statusFilter = 'all', $typeFilter = 'all', $searchTerm = '') {
        return $this->model->getPaginatedTransactions($offset, $limit, $statusFilter, $typeFilter, $searchTerm);
    }
    
    // Count transactions for pagination
    public function countTransactions($statusFilter = 'all', $typeFilter = 'all', $searchTerm = '') {
        return $this->model->countTransactions($statusFilter, $typeFilter, $searchTerm);
    }
    
    // Get transaction by ID
    public function getTransactionById($id) {
        return $this->model->getTransactionById($id);
    }
    
    // Add new transaction
    public function addTransaction($userId, $type, $amount, $status) {
        return $this->model->addTransaction($userId, $type, $amount, $status);
    }
    
    // Update transaction status
    public function updateTransactionStatus($id, $status) {
        return $this->model->updateTransactionStatus($id, $status);
    }
    
    // Process bulk status update
    public function bulkUpdateStatus($ids, $status) {
        return $this->model->bulkUpdateStatus($ids, $status);
    }
    
    // Get unique transaction types
    public function getTransactionTypes() {
        return $this->model->getTransactionTypes();
    }
    
    // Get transaction statistics
    public function getTransactionStats() {
        return $this->model->getTransactionStats();
    }
    
    // Get users for dropdown
    public function getUsers() {
        return $this->model->getUsers();
    }
    
    // Format currency
    public function formatCurrency($amount) {
        return number_format($amount, 0, ',', '.') . ' VNĐ';
    }
    
    // Get status badge class
    public function getStatusBadgeClass($status) {
        switch ($status) {
            case 'Hoàn thành':
                return 'bg-success text-white';
            case 'Đang xử lý':
                return 'bg-warning text-dark';
            case 'Hủy':
                return 'bg-danger text-white';
            default:
                return 'bg-secondary text-white';
        }
    }
    
    // Get transaction type badge class
    public function getTypeBadgeClass($type) {
        switch ($type) {
            case 'Nạp tiền':
                return 'bg-primary text-white';
            case 'Rút tiền':
                return 'bg-info text-white';
            case 'Thanh toán':
                return 'bg-dark text-white';
            default:
                return 'bg-secondary text-white';
        }
    }
}
?>