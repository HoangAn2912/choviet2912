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
    
    function getpaginatedusers($offset, $limit, $statusFilter = 'all') {
        $p = new qlthongtin();
        $data = $p->selectPaginatedUsers($offset, $limit, $statusFilter);
        return $data;
    }
    
    // Count total users with optional status filter
    function countUsers($statusFilter = 'all') {
        $p = new qlthongtin();
        return $p->countTotalUsers($statusFilter);
    }

    // Add new user
    function adduser($username, $email, $password, $phone, $address, $avatar, $role_id) {
    $p = new qlthongtin();
    $data = $p->insertUser($username, $email, $password, $phone, $address, $avatar, $role_id);
        return $data;
    }
    public function getinsertuser($hoten, $email, $mk, $sdt, $dc, $anh) {
        $p = new qlthongtin();
        return $p->insertuser($hoten, $email, $mk, $sdt, $dc, $anh);
    }
}
?>