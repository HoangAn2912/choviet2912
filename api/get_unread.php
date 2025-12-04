<?php
// api/get_unread.php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Validate user_id
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($user_id <= 0) {
    echo json_encode([]);
    exit;
}

// Path to the unread file
$file_path = '/var/www/choviet.site/chat/unread_' . $user_id . '.json';

if (file_exists($file_path)) {
    // Read and output the file content
    readfile($file_path);
} else {
    // Return empty object if file doesn't exist
    echo json_encode([]);
}
?>
