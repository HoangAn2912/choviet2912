<?php
require_once 'mConnect.php';

class mUser extends Connect {
    public function getUserById($id) {
        $conn = $this->connect();
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
