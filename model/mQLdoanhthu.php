<?php
include_once("mConnect.php");
class qldoanhthu {
    // Get revenue data with pagination and filtering
    function getRevenueData($offset, $limit, $startDate = null, $endDate = null, $userId = null) {
        $con = new Connect();
        $p = $con->connect();
        
        // Simplified query - just get all products and calculate revenue
        $sql = "SELECT 
                sp.id as products_id, 
                sp.title, 
                sp.price, 
                nd.id as users_id,
                nd.username,
                sp.created_date,
                11000 as revenue_fee
                FROM products sp
                JOIN users nd ON sp.user_id = nd.id
                WHERE 1=1";
        
        // Add date filter if provided
        if ($startDate && $endDate) {
            $sql .= " AND (sp.created_date BETWEEN '$startDate' AND '$endDate 23:59:59')";
        }
        
        // Add user filter if provided
        if ($userId) {
            $sql .= " AND sp.user_id = '$userId'";
        }
        
        // Add order and limit
        $sql .= " ORDER BY sp.id LIMIT $offset, $limit";
        
        $rs = mysqli_query($p, $sql);
        
        $data = array();
        if ($rs) {
            while ($row = mysqli_fetch_array($rs)) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    // Count total revenue records for pagination
    function countTotalRevenue($startDate = null, $endDate = null, $userId = null) {
        $con = new Connect();
        $p = $con->connect();
        
        // Simplified count query
        $sql = "SELECT COUNT(*) as total FROM products sp WHERE 1=1";
        
        // Add date filter if provided
        if ($startDate && $endDate) {
            $sql .= " AND (sp.created_date BETWEEN '$startDate' AND '$endDate 23:59:59')";
        }
        
        // Add user filter if provided
        if ($userId) {
            $sql .= " AND sp.user_id = '$userId'";
        }
        
        $rs = mysqli_query($p, $sql);
        
        if ($rs) {
            $row = mysqli_fetch_assoc($rs);
            return $row['total'];
        }
        
        return 0;
    }
    
    // Get summary statistics
    function getRevenueSummary($startDate = null, $endDate = null) {
        $con = new Connect();
        $p = $con->connect();
        
        // Simplified summary query
        $sql = "SELECT 
                COUNT(*) as total_posts,
                COUNT(DISTINCT id_users) as total_users,
                COUNT(*) * 11000 as total_revenue
                FROM products
                WHERE 1=1";
        
        // Add date filter if provided
        if ($startDate && $endDate) {
            $sql .= " AND (created_date BETWEEN '$startDate' AND '$endDate 23:59:59')";
        }
        
        $rs = mysqli_query($p, $sql);
        
        $summary = array('total_posts' => 0, 'total_users' => 0, 'total_revenue' => 0);
        
        if ($rs) {
            $row = mysqli_fetch_assoc($rs);
            $summary['total_posts'] = $row['total_posts'] ?? 0;
            $summary['total_users'] = $row['total_users'] ?? 0;
            $summary['total_revenue'] = $row['total_revenue'] ?? 0;
        }
        
        return $summary;
    }
    
    // Get top users by revenue
    function getTopUsersByRevenue($limit = 5, $startDate = null, $endDate = null) {
        $con = new Connect();
        $p = $con->connect();
        
        // Simplified top users query
        $sql = "SELECT 
                nd.id,
                nd.username,
                COUNT(sp.id) as total_posts,
                COUNT(*) * 11000 as total_revenue
                FROM users nd
                JOIN products sp ON nd.id = sp.user_id
                WHERE 1=1";
        
        // Add date filter if provided
        if ($startDate && $endDate) {
            $sql .= " AND (sp.created_date BETWEEN '$startDate' AND '$endDate 23:59:59')";
        }
        
        $sql .= " GROUP BY nd.id
                ORDER BY total_revenue DESC
                LIMIT $limit";
        
        $rs = mysqli_query($p, $sql);
        
        $data = array();
        if ($rs) {
            while ($row = mysqli_fetch_array($rs)) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    // Get monthly revenue data for chart
    function getMonthlyRevenue($year = null) {
        $con = new Connect();
        $p = $con->connect();
        
        $currentYear = $year ? $year : date('Y');
        
        // Simplified monthly revenue query
        $sql = "SELECT 
                MONTH(created_date) as month,
                COUNT(*) * 11000 as monthly_revenue
                FROM products
                WHERE YEAR(created_date) = '$currentYear'
                GROUP BY MONTH(created_date)
                ORDER BY month";
        
        $rs = mysqli_query($p, $sql);
        
        $data = array();
        if ($rs) {
            while ($row = mysqli_fetch_array($rs)) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    // Export revenue data to CSV
    function exportRevenueData($startDate = null, $endDate = null, $userId = null) {
        $con = new Connect();
        $p = $con->connect();
        
        // Simplified export query
        $sql = "SELECT 
                sp.id as products_id, 
                sp.title, 
                sp.price, 
                nd.id as users_id,
                nd.username,
                nd.email,
                sp.created_date,
                11000 as revenue_fee
                FROM products sp
                JOIN users nd ON sp.user_id = nd.id
                WHERE 1=1";
        
        // Add date filter if provided
        if ($startDate && $endDate) {
            $sql .= " AND (sp.created_date BETWEEN '$startDate' AND '$endDate 23:59:59')";
        }
        
        // Add user filter if provided
        if ($userId) {
            $sql .= " AND sp.user_id = '$userId'";
        }
        
        $sql .= " ORDER BY sp.created_date DESC";
        
        $rs = mysqli_query($p, $sql);
        
        $data = array();
        if ($rs) {
            while ($row = mysqli_fetch_array($rs)) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    // Get all users for filter dropdown
    function getAllUsers() {
        $con = new Connect();
        $p = $con->connect();
        
        $sql = "SELECT id, username FROM users ORDER BY username";
        $rs = mysqli_query($p, $sql);
        
        $data = array();
        if ($rs) {
            while ($row = mysqli_fetch_array($rs)) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
}
?>