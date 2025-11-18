<?php
include_once("mConnect.php");
class qldoanhthu {
    // Get revenue data with pagination and filtering
    function getRevenueData($offset, $limit, $startDate = null, $endDate = null, $userId = null) {
        $con = new Connect();
        $p = $con->connect();
        
        // Lấy dữ liệu từ phí đăng tin (products)
        $sql_posts = "SELECT 
                sp.id as id, 
                sp.title, 
                sp.price, 
                nd.id as users_id,
                nd.username,
                sp.created_date as revenue_date,
                11000 as revenue_fee,
                'posting_fee' as revenue_type,
                'Phí đăng tin' as revenue_type_name
                FROM products sp
                JOIN users nd ON sp.user_id = nd.id
                WHERE 1=1";
        
        // Add date filter if provided
        if ($startDate && $endDate) {
            $sql_posts .= " AND (sp.created_date BETWEEN '$startDate' AND '$endDate 23:59:59')";
        }
        
        // Add user filter if provided
        if ($userId) {
            $sql_posts .= " AND sp.user_id = '$userId'";
        }
        
        // Lấy dữ liệu từ phí đăng ký gói live (livestream_payment_history)
        $sql_live = "SELECT 
                ph.id as id,
                CONCAT('Gói livestream: ', p.package_name) as title,
                0 as price,
                u.id as users_id,
                u.username,
                ph.payment_date as revenue_date,
                ph.amount as revenue_fee,
                'livestream_package' as revenue_type,
                'Phí đăng ký gói live' as revenue_type_name
                FROM livestream_payment_history ph
                JOIN users u ON ph.user_id = u.id
                JOIN livestream_packages p ON ph.package_id = p.id
                WHERE ph.payment_status = 'success'";
        
        // Add date filter if provided
        if ($startDate && $endDate) {
            $sql_live .= " AND (ph.payment_date BETWEEN '$startDate' AND '$endDate 23:59:59')";
        }
        
        // Add user filter if provided
        if ($userId) {
            $sql_live .= " AND ph.user_id = '$userId'";
        }
        
        // UNION và sắp xếp
        $sql = "($sql_posts) UNION ALL ($sql_live) ORDER BY revenue_date DESC LIMIT $offset, $limit";
        
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
        
        // Đếm phí đăng tin
        $sql_posts = "SELECT COUNT(*) as total FROM products sp WHERE 1=1";
        
        // Add date filter if provided
        if ($startDate && $endDate) {
            $sql_posts .= " AND (sp.created_date BETWEEN '$startDate' AND '$endDate 23:59:59')";
        }
        
        // Add user filter if provided
        if ($userId) {
            $sql_posts .= " AND sp.user_id = '$userId'";
        }
        
        // Đếm phí đăng ký gói live
        $sql_live = "SELECT COUNT(*) as total FROM livestream_payment_history ph WHERE ph.payment_status = 'success'";
        
        // Add date filter if provided
        if ($startDate && $endDate) {
            $sql_live .= " AND (ph.payment_date BETWEEN '$startDate' AND '$endDate 23:59:59')";
        }
        
        // Add user filter if provided
        if ($userId) {
            $sql_live .= " AND ph.user_id = '$userId'";
        }
        
        // Tính tổng
        $rs_posts = mysqli_query($p, $sql_posts);
        $rs_live = mysqli_query($p, $sql_live);
        
        $total_posts = 0;
        $total_live = 0;
        
        if ($rs_posts) {
            $row = mysqli_fetch_assoc($rs_posts);
            $total_posts = $row['total'] ?? 0;
        }
        
        if ($rs_live) {
            $row = mysqli_fetch_assoc($rs_live);
            $total_live = $row['total'] ?? 0;
        }
        
        return $total_posts + $total_live;
    }
    
    // Get summary statistics
    function getRevenueSummary($startDate = null, $endDate = null) {
        $con = new Connect();
        $p = $con->connect();
        
        // Tính doanh thu từ phí đăng tin (products)
        $sql_posts = "SELECT 
                COUNT(*) as total_posts,
                COUNT(DISTINCT user_id) as total_users,
                COUNT(*) * 11000 as post_revenue
                FROM products
                WHERE 1=1";
        
        // Add date filter if provided
        if ($startDate && $endDate) {
            $sql_posts .= " AND (created_date BETWEEN '$startDate' AND '$endDate 23:59:59')";
        }
        
        $rs_posts = mysqli_query($p, $sql_posts);
        $post_data = array('total_posts' => 0, 'total_users' => 0, 'post_revenue' => 0);
        if ($rs_posts) {
            $row = mysqli_fetch_assoc($rs_posts);
            $post_data['total_posts'] = $row['total_posts'] ?? 0;
            $post_data['total_users'] = $row['total_users'] ?? 0;
            $post_data['post_revenue'] = $row['post_revenue'] ?? 0;
        }
        
        // Tính doanh thu từ phí đăng ký gói live (livestream_payment_history)
        $sql_live = "SELECT 
                COUNT(*) as total_packages,
                COUNT(DISTINCT user_id) as total_live_users,
                COALESCE(SUM(amount), 0) as live_revenue
                FROM livestream_payment_history
                WHERE payment_status = 'success'";
        
        // Add date filter if provided
        if ($startDate && $endDate) {
            $sql_live .= " AND (payment_date BETWEEN '$startDate' AND '$endDate 23:59:59')";
        }
        
        $rs_live = mysqli_query($p, $sql_live);
        $live_data = array('total_packages' => 0, 'total_live_users' => 0, 'live_revenue' => 0);
        if ($rs_live) {
            $row = mysqli_fetch_assoc($rs_live);
            $live_data['total_packages'] = $row['total_packages'] ?? 0;
            $live_data['total_live_users'] = $row['total_live_users'] ?? 0;
            $live_data['live_revenue'] = $row['live_revenue'] ?? 0;
        }
        
        // Tính tổng số người dùng duy nhất (có thể vừa đăng tin vừa đăng ký gói live)
        $sql_total_users = "SELECT COUNT(DISTINCT user_id) as total_unique_users
                FROM (
                    SELECT user_id FROM products WHERE 1=1";
        
        if ($startDate && $endDate) {
            $sql_total_users .= " AND (created_date BETWEEN '$startDate' AND '$endDate 23:59:59')";
        }
        
        $sql_total_users .= " UNION
                    SELECT user_id FROM livestream_payment_history 
                    WHERE payment_status = 'success'";
        
        if ($startDate && $endDate) {
            $sql_total_users .= " AND (payment_date BETWEEN '$startDate' AND '$endDate 23:59:59')";
        }
        
        $sql_total_users .= ") as all_users";
        
        $rs_total_users = mysqli_query($p, $sql_total_users);
        $total_unique_users = 0;
        if ($rs_total_users) {
            $row = mysqli_fetch_assoc($rs_total_users);
            $total_unique_users = $row['total_unique_users'] ?? 0;
        }
        
        // Tổng hợp
        $summary = array(
            'total_posts' => $post_data['total_posts'],
            'total_users' => $post_data['total_users'],
            'total_packages' => $live_data['total_packages'],
            'total_live_users' => $live_data['total_live_users'],
            'total_unique_users' => $total_unique_users,
            'post_revenue' => $post_data['post_revenue'],
            'live_revenue' => $live_data['live_revenue'],
            'total_revenue' => $post_data['post_revenue'] + $live_data['live_revenue']
        );
        
        return $summary;
    }
    
    // Get top users by account balance
    function getTopUsersByRevenue($limit = 5, $startDate = null, $endDate = null) {
        $con = new Connect();
        $p = $con->connect();
        
        // Chỉ lấy người dùng thông thường (role_id = 2) và moderator (role_id = 3)
        $sql = "SELECT 
                u.id as user_id,
                u.username,
                COALESCE(ta.balance, 0) as total_revenue
                FROM users u
                LEFT JOIN transfer_accounts ta ON u.id = ta.user_id
                WHERE COALESCE(ta.balance, 0) > 0
                AND u.role_id IN (2, 3)
                ORDER BY ta.balance DESC
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
        
        // Tính doanh thu từ phí đăng tin theo tháng
        $sql_posts = "SELECT 
                MONTH(created_date) as month,
                COUNT(*) * 11000 as post_revenue
                FROM products
                WHERE YEAR(created_date) = '$currentYear'
                GROUP BY MONTH(created_date)";
        
        // Tính doanh thu từ phí đăng ký gói live theo tháng
        $sql_live = "SELECT 
                MONTH(payment_date) as month,
                COALESCE(SUM(amount), 0) as live_revenue
                FROM livestream_payment_history
                WHERE YEAR(payment_date) = '$currentYear' AND payment_status = 'success'
                GROUP BY MONTH(payment_date)";
        
        // Gộp dữ liệu từ cả 2 nguồn - sử dụng FULL OUTER JOIN mô phỏng
        $sql = "SELECT 
                COALESCE(p.month, l.month) as month,
                COALESCE(p.post_revenue, 0) + COALESCE(l.live_revenue, 0) as monthly_revenue
                FROM (
                    $sql_posts
                ) p
                LEFT JOIN (
                    $sql_live
                ) l ON p.month = l.month
                UNION
                SELECT 
                l.month as month,
                COALESCE(p.post_revenue, 0) + COALESCE(l.live_revenue, 0) as monthly_revenue
                FROM (
                    $sql_posts
                ) p
                RIGHT JOIN (
                    $sql_live
                ) l ON p.month = l.month
                WHERE p.month IS NULL";
        
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
                sp.id as id, 
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