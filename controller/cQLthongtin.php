<?php
include_once("model/mQLthongtin.php");
class cqlthongtin {
    function getalluser() {
        $p = new qlthongtin();
        $data = $p->selectAllUser();
        return $data;
    }
    
    // Get one user by ID
    function getoneuser($id) {
        $p = new qlthongtin();
        $data = $p->selectOneUser($id);
        return $data;
    }
    
    // Update user information
    function getupdateuser($id, $hoten, $email, $sdt, $dc, $anh, $role_id) {
        $p = new qlthongtin();
        $data = $p->updateUser($id, $hoten, $email, $sdt, $dc, $anh, $role_id);
        return $data;
    }
    
    // Update user with password
    function getupdateuser_with_password($id, $username, $email, $password, $phone, $address, $avatar, $role_id) {
        $p = new qlthongtin();
        $data = $p->updateUserWithPassword($id, $username, $email, $password, $phone, $address, $avatar, $role_id);
        return $data;
    }
    
    // Disable user
    function disableuser($id) {
        $p = new qlthongtin();
        $data = $p->disableUser($id);
        return $data;
    }
    
    // Restore user
    function restoreuser($id) {
        $p = new qlthongtin();
        $data = $p->restoreUser($id);
        return $data;
    }
    
    function getpaginatedusers($offset, $limit, $statusFilter = 'all', $search = '') {
        $p = new qlthongtin();
        $data = $p->selectPaginatedUsers($offset, $limit, $statusFilter, $search);
        return $data;
    }
    
    // Count total users with optional status filter and search
    function countUsers($statusFilter = 'all', $search = '') {
        $p = new qlthongtin();
        return $p->countTotalUsers($statusFilter, $search);
    }
    
    // Get user statistics
    function getUserStats() {
        $p = new qlthongtin();
        return $p->getUserStats();
    }

    // Add new user
    function adduser($username, $email, $password, $phone, $address, $avatar, $role_id) {
        $p = new qlthongtin();
        $result = $p->insertUserWithRole($username, $email, $password, $phone, $address, $avatar, $role_id);
        
        // Xử lý kết quả trả về (có thể là boolean hoặc array)
        if (is_array($result)) {
            return $result;
        }
        
        // Nếu trả về boolean (backward compatibility)
        if ($result === true) {
            return ['success' => true, 'message' => 'Thêm người dùng thành công.'];
        }
        
        return ['success' => false, 'error' => 'Không thể thêm người dùng.'];
    }
    public function getinsertuser($hoten, $email, $mk, $sdt, $dc, $anh) {
        $p = new qlthongtin();
        $result = $p->insertuser($hoten, $email, $mk, $sdt, $dc, $anh);
        
        // Xử lý kết quả trả về (có thể là boolean hoặc array)
        if (is_array($result)) {
            return $result;
        }
        
        // Nếu trả về boolean (backward compatibility)
        if ($result === true) {
            return ['success' => true, 'message' => 'Thêm người dùng thành công.'];
        }
        
        return ['success' => false, 'error' => 'Không thể thêm người dùng.'];
    }
}
?>