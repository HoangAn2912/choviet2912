<?php
include_once("mConnect.php");
class qlthongtin{
    // Get all users with status information
    function selectAllUser() {
        $con = new Connect();
        $p = $con->connect();
        
        $sql = "SELECT * FROM users order by id asc";
        $rs = mysqli_query($p, $sql);
        
        $data = array();
        while ($row = mysqli_fetch_array($rs)) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    // Get one user by ID
    function selectOneUser($id) {
        $con = new Connect();
        $p = $con->connect();
        
        $sql = "SELECT * FROM users WHERE id = '$id'";
        $rs = mysqli_query($p, $sql);
        
        $data = array();
        while ($row = mysqli_fetch_array($rs)) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    // Update user information
    function updateUser($id, $hoten, $email, $sdt, $dc, $anh, $role_id) {
        $con = new Connect();
        $p = $con->connect();
        
        $sql = "UPDATE users SET 
                username = '$hoten', 
                email = '$email', 
                phone = '$sdt', 
                address = '$dc', 
                avatar = '$anh',
                role_id = '$role_id',
                updated_date = NOW()
                WHERE id = '$id'";
                
        $rs = mysqli_query($p, $sql);
        
        return $rs;
    }
    
    // Update user with password
    function updateUserWithPassword($id, $username, $email, $password, $phone, $address, $avatar, $role_id) {
        $con = new Connect();
        $p = $con->connect();
        
        $sql = "UPDATE users SET 
                username = '$hoten', 
                email = '$email',
                password = '$password', 
                phone = '$sdt', 
                address = '$dc', 
                avatar = '$anh',
                role_id = '$role_id',
                updated_date = NOW()
                WHERE id = '$id'";
                
        $rs = mysqli_query($p, $sql);
        
        return $rs;
    }
    
    // Disable user (set is_active = 0)
    function disableUser($id) {
        $con = new Connect();
        $p = $con->connect();
        
        $sql = "UPDATE users SET 
                is_active = 0,
                updated_date = NOW()
                WHERE id = '$id'";
                
        $rs = mysqli_query($p, $sql);
        
        return $rs;
    }
    
    // Restore user (set is_active = 1)
    function restoreUser($id) {
        $con = new Connect();
        $p = $con->connect();
        
        $sql = "UPDATE users SET 
                is_active = 1,
                updated_date = NOW()
                WHERE id = '$id'";
                
        $rs = mysqli_query($p, $sql);
        
        return $rs;
    }
    
	// Get paginated users with optional status filter
    function selectPaginatedUsers($offset, $limit, $statusFilter = 'all') {
        $con = new Connect();
        $p = $con->connect();
        
        $whereClause = "WHERE role_id = 2";
        
        if ($statusFilter === 'active') {
            $whereClause .= " AND is_active = 1";
        } else if ($statusFilter === 'disabled') {
            $whereClause .= " AND is_active = 0";
        }
        
        $sql = "SELECT * FROM users {$whereClause} ORDER BY id LIMIT {$offset}, {$limit}";
        $rs = mysqli_query($p, $sql);
        
        $data = array();
        while ($row = mysqli_fetch_array($rs)) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    // Count total users with optional status filter
    function countTotalUsers($statusFilter = 'all') {
        $con = new Connect();
        $p = $con->connect();
        
        $whereClause = "WHERE role_id = 2";
        
        if ($statusFilter === 'active') {
            $whereClause .= " AND is_active = 1";
        } else if ($statusFilter === 'disabled') {
            $whereClause .= " AND is_active = 0";
        }
        
        $sql = "SELECT COUNT(*) as total FROM users {$whereClause}";
        $rs = mysqli_query($p, $sql);
        $row = mysqli_fetch_assoc($rs);
        
        return $row['total'];
    }

	public function insertuser($hoten, $email, $mk, $sdt, $dc, $anh) {
		$p = new Connect();
		$con = $p->connect();
		$sql = "INSERT INTO users 
				(username, email, password, phone, address, role_id, avatar, created_date, updated_date, is_active) 
				VALUES (?, ?, ?, ?, ?, 2, ?, NOW(), NULL, 1)"	;
		$stmt = $con->prepare($sql);
		if (!$stmt) {
			return false;
		}
		$stmt->bind_param("ssssss", $hoten, $email, $mk, $sdt, $dc, $anh);
		$kq = $stmt->execute();
		$stmt->close();
		$con->close();
		return $kq;
	}
}
?>