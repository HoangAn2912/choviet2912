<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'model/mConnect.php';
$conn = new Connect();
$db = $conn->connect();

if ($db) {
    echo "Connection successful!";
} else {
    echo "Connection failed!";
}
?>
