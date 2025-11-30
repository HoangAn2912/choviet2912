<?php
include_once("model/mQLdonhang.php");

class cQLdonhang {
    private $model;
    
    public function __construct() {
        $this->model = new mQLdonhang();
    }
    
    // Lấy tất cả đơn hàng với filter
    public function getAllOrders($status = null, $livestream_id = null, $user_id = null, $start_date = null, $end_date = null, $limit = 20, $offset = 0) {
        return $this->model->getAllOrders($status, $livestream_id, $user_id, $start_date, $end_date, $limit, $offset);
    }
    
    // Đếm tổng số đơn hàng
    public function countOrders($status = null, $livestream_id = null, $user_id = null, $start_date = null, $end_date = null) {
        return $this->model->countOrders($status, $livestream_id, $user_id, $start_date, $end_date);
    }
    
    // Lấy thống kê
    public function getStats() {
        return $this->model->getStats();
    }
    
    // Cập nhật trạng thái đơn hàng
    public function updateOrderStatus($order_id, $status) {
        return $this->model->updateOrderStatus($order_id, $status);
    }
    
    // Lấy chi tiết đơn hàng
    public function getOrderDetails($order_id) {
        return $this->model->getOrderDetails($order_id);
    }
    
    // Lấy danh sách livestream
    public function getAllLivestreams() {
        return $this->model->getAllLivestreams();
    }
    
    // Lấy danh sách người dùng
    public function getAllUsers() {
        return $this->model->getAllUsers();
    }
}
?>










