<?php
// Include Security helper
include_once("helpers/Security.php");

// Khởi tạo session bảo mật
Security::initSecureSession();

// Validate session
Security::validateSession();

// Xử lý đăng xuất
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header('Location: index.php?login');
    exit;
}

include_once("view/admin/admin.php");
?>