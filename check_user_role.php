<?php
require_once 'model/mConnect.php';
$conn = new Connect();
$db = $conn->connect();

$email = 'hoangan2711.npha@gmail.com';
$sql = "SELECT id, username, email, role_id FROM users WHERE email = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

print_r($user);
?>
