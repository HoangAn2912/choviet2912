<?php
require_once 'model/mConnect.php';
$conn = new Connect();
$db = $conn->connect();

$email = 'hoangan2711.npha@gmail.com';
$new_role = 2; // User role

$sql = "UPDATE users SET role_id = ? WHERE email = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("is", $new_role, $email);

if ($stmt->execute()) {
    echo "Successfully updated role_id to $new_role for $email";
} else {
    echo "Error updating role: " . $stmt->error;
}
?>
