<?php
include_once("model/mQLdoanhthu.php");
class cqldoanhthu {
    // Get revenue data with pagination and filtering
    function getRevenueData($offset, $limit, $startDate = null, $endDate = null, $userId = null) {
        $p = new qldoanhthu();
        $data = $p->getRevenueData($offset, $limit, $startDate, $endDate, $userId);
        return $data;
    }
    
    // Count total revenue records for pagination
    function countTotalRevenue($startDate = null, $endDate = null, $userId = null) {
        $p = new qldoanhthu();
        return $p->countTotalRevenue($startDate, $endDate, $userId);
    }
    
    // Get summary statistics
    function getRevenueSummary($startDate = null, $endDate = null) {
        $p = new qldoanhthu();
        return $p->getRevenueSummary($startDate, $endDate);
    }
    
    // Get top users by revenue
    function getTopUsersByRevenue($limit = 5, $startDate = null, $endDate = null) {
        $p = new qldoanhthu();
        return $p->getTopUsersByRevenue($limit, $startDate, $endDate);
    }
    
    // Get monthly revenue data for chart
    function getMonthlyRevenue($year = null) {
        $p = new qldoanhthu();
        return $p->getMonthlyRevenue($year);
    }
    
    // Export revenue data to CSV
    function exportRevenueData($startDate = null, $endDate = null, $userId = null) {
        $p = new qldoanhthu();
        return $p->exportRevenueData($startDate, $endDate, $userId);
    }
    
    // Get all users for filter dropdown
    function getAllUsers() {
        $p = new qldoanhthu();
        return $p->getAllUsers();
    }
}
?>