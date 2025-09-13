<?php
// Kết nối database - Thay đổi thông tin kết nối theo database của bạn
$servername = "localhost";
$username = "admin";
$password = "123456";
$dbname = "choviet29";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}
?>
