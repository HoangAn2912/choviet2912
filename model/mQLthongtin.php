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
        // Sử dụng mysqli_fetch_assoc để chỉ lấy associative keys (tên cột)
        while ($row = mysqli_fetch_assoc($rs)) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    // Update user information
    function updateUser($id, $hoten, $email, $sdt, $dc, $anh, $role_id) {
        $con = new Connect();
        $p = $con->connect();
        
        // Set charset UTF-8 để xử lý đúng ký tự đặc biệt
        mysqli_set_charset($p, "utf8mb4");
        
        // Đảm bảo role_id không NULL
        if (empty($role_id) || $role_id === null) {
            $role_id = 1; // Mặc định là admin nếu không có giá trị
        }
        
        // Sử dụng prepared statement để tránh SQL injection và xử lý encoding tốt hơn
        $id = intval($id);
        $role_id = intval($role_id);
        
        // Lấy số điện thoại hiện tại của user
        $currentPhoneStmt = $p->prepare("SELECT phone FROM users WHERE id = ?");
        $currentPhoneStmt->bind_param("i", $id);
        $currentPhoneStmt->execute();
        $currentPhoneResult = $currentPhoneStmt->get_result();
        $currentPhone = '';
        if ($currentPhoneRow = $currentPhoneResult->fetch_assoc()) {
            $currentPhone = $currentPhoneRow['phone'] ?? '';
        }
        $currentPhoneStmt->close();
        
        // Xử lý phone: Kiểm tra duplicate trước khi update
        $shouldUpdatePhone = false;
        $phoneToUpdate = null;
        
        if (!empty($sdt)) {
            // Nếu số điện thoại mới khác với số hiện tại
            if ($sdt !== $currentPhone) {
                // Kiểm tra xem số điện thoại này đã được user khác sử dụng chưa
                $checkPhoneStmt = $p->prepare("SELECT id FROM users WHERE phone = ? AND id != ?");
                $checkPhoneStmt->bind_param("si", $sdt, $id);
                $checkPhoneStmt->execute();
                $checkPhoneResult = $checkPhoneStmt->get_result();
                
                if ($checkPhoneResult->num_rows == 0) {
                    // Số điện thoại chưa được user khác sử dụng, có thể update
                    $shouldUpdatePhone = true;
                    $phoneToUpdate = $sdt;
                }
                // Nếu số điện thoại đã được user khác sử dụng, không update phone
                $checkPhoneStmt->close();
            }
            // Nếu số điện thoại mới giống với số hiện tại, không cần update
        }
        
        // Thực hiện update
        if ($shouldUpdatePhone) {
            // Update với phone mới
            $stmt = $p->prepare("UPDATE users SET username = ?, email = ?, phone = ?, address = ?, avatar = ?, role_id = ?, updated_date = NOW() WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("sssssii", $hoten, $email, $phoneToUpdate, $dc, $anh, $role_id, $id);
                $result = $stmt->execute();
                $stmt->close();
                return $result;
            }
        } else {
            // Update không có phone (giữ nguyên phone hiện tại hoặc phone rỗng)
            $stmt = $p->prepare("UPDATE users SET username = ?, email = ?, address = ?, avatar = ?, role_id = ?, updated_date = NOW() WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("ssssii", $hoten, $email, $dc, $anh, $role_id, $id);
                $result = $stmt->execute();
                $stmt->close();
                return $result;
            }
        }
        
        // Fallback nếu prepare statement thất bại
        $hoten = mysqli_real_escape_string($p, $hoten);
        $email = mysqli_real_escape_string($p, $email);
        $dc = mysqli_real_escape_string($p, $dc);
        $anh = mysqli_real_escape_string($p, $anh);
        
        if ($shouldUpdatePhone && $phoneToUpdate) {
            $phoneToUpdate = mysqli_real_escape_string($p, $phoneToUpdate);
            $sql = "UPDATE users SET 
                    username = '$hoten', 
                    email = '$email', 
                    phone = '$phoneToUpdate',
                    address = '$dc', 
                    avatar = '$anh',
                    role_id = '$role_id',
                    updated_date = NOW()
                    WHERE id = '$id'";
        } else {
            $sql = "UPDATE users SET 
                    username = '$hoten', 
                    email = '$email', 
                    address = '$dc', 
                    avatar = '$anh',
                    role_id = '$role_id',
                    updated_date = NOW()
                    WHERE id = '$id'";
        }
        return mysqli_query($p, $sql);
    }
    
    // Update user with password
    function updateUserWithPassword($id, $username, $email, $password, $phone, $address, $avatar, $role_id) {
        $con = new Connect();
        $p = $con->connect();
        
        // Set charset UTF-8 để xử lý đúng ký tự đặc biệt
        mysqli_set_charset($p, "utf8mb4");
        
        // Đảm bảo role_id không NULL
        if (empty($role_id) || $role_id === null) {
            $role_id = 1; // Mặc định là admin nếu không có giá trị
        }
        
        // Sử dụng prepared statement để tránh SQL injection và xử lý encoding tốt hơn
        $id = intval($id);
        $role_id = intval($role_id);
        
        // Lấy số điện thoại hiện tại của user
        $currentPhoneStmt = $p->prepare("SELECT phone FROM users WHERE id = ?");
        $currentPhoneStmt->bind_param("i", $id);
        $currentPhoneStmt->execute();
        $currentPhoneResult = $currentPhoneStmt->get_result();
        $currentPhone = '';
        if ($currentPhoneRow = $currentPhoneResult->fetch_assoc()) {
            $currentPhone = $currentPhoneRow['phone'] ?? '';
        }
        $currentPhoneStmt->close();
        
        // Xử lý phone: Kiểm tra duplicate trước khi update
        $shouldUpdatePhone = false;
        $phoneToUpdate = null;
        
        if (!empty($phone)) {
            // Nếu số điện thoại mới khác với số hiện tại
            if ($phone !== $currentPhone) {
                // Kiểm tra xem số điện thoại này đã được user khác sử dụng chưa
                $checkPhoneStmt = $p->prepare("SELECT id FROM users WHERE phone = ? AND id != ?");
                $checkPhoneStmt->bind_param("si", $phone, $id);
                $checkPhoneStmt->execute();
                $checkPhoneResult = $checkPhoneStmt->get_result();
                
                if ($checkPhoneResult->num_rows == 0) {
                    // Số điện thoại chưa được user khác sử dụng, có thể update
                    $shouldUpdatePhone = true;
                    $phoneToUpdate = $phone;
                }
                // Nếu số điện thoại đã được user khác sử dụng, không update phone
                $checkPhoneStmt->close();
            }
            // Nếu số điện thoại mới giống với số hiện tại, không cần update
        }
        
        // Thực hiện update
        if ($shouldUpdatePhone) {
            // Update với phone mới
            $stmt = $p->prepare("UPDATE users SET username = ?, email = ?, password = ?, phone = ?, address = ?, avatar = ?, role_id = ?, updated_date = NOW() WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("ssssssii", $username, $email, $password, $phoneToUpdate, $address, $avatar, $role_id, $id);
                $result = $stmt->execute();
                $stmt->close();
                return $result;
            }
        } else {
            // Update không có phone (giữ nguyên phone hiện tại hoặc phone rỗng)
            $stmt = $p->prepare("UPDATE users SET username = ?, email = ?, password = ?, address = ?, avatar = ?, role_id = ?, updated_date = NOW() WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("sssssii", $username, $email, $password, $address, $avatar, $role_id, $id);
                $result = $stmt->execute();
                $stmt->close();
                return $result;
            }
        }
        
        // Fallback nếu prepare statement thất bại
        $username = mysqli_real_escape_string($p, $username);
        $email = mysqli_real_escape_string($p, $email);
        $password = mysqli_real_escape_string($p, $password);
        $address = mysqli_real_escape_string($p, $address);
        $avatar = mysqli_real_escape_string($p, $avatar);
        
        if ($shouldUpdatePhone && $phoneToUpdate) {
            $phoneToUpdate = mysqli_real_escape_string($p, $phoneToUpdate);
            $sql = "UPDATE users SET 
                    username = '$username', 
                    email = '$email',
                    password = '$password', 
                    phone = '$phoneToUpdate',
                    address = '$address', 
                    avatar = '$avatar',
                    role_id = '$role_id',
                    updated_date = NOW()
                    WHERE id = '$id'";
        } else {
            $sql = "UPDATE users SET 
                    username = '$username', 
                    email = '$email',
                    password = '$password', 
                    address = '$address', 
                    avatar = '$avatar',
                    role_id = '$role_id',
                    updated_date = NOW()
                    WHERE id = '$id'";
        }
        return mysqli_query($p, $sql);
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