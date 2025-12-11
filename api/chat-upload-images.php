<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// Khởi tạo session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("../helpers/Security.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$from = isset($_POST['from']) ? intval($_POST['from']) : 0;
$to = isset($_POST['to']) ? intval($_POST['to']) : 0;

if ($from <= 0 || $to <= 0 || $from != $_SESSION['user_id']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

if (!isset($_FILES['images']) || empty($_FILES['images']['name'][0])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No images uploaded']);
    exit;
}

$uploadedImages = [];
$targetDir = __DIR__ . "/../img/";

if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 5 * 1024 * 1024; // 5MB

$totalFiles = count($_FILES['images']['name']);

for ($i = 0; $i < $totalFiles; $i++) {
    if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) {
        continue;
    }
    
    $file = [
        'name' => $_FILES['images']['name'][$i],
        'type' => $_FILES['images']['type'][$i],
        'tmp_name' => $_FILES['images']['tmp_name'][$i],
        'error' => $_FILES['images']['error'][$i],
        'size' => $_FILES['images']['size'][$i]
    ];
    
    // Validate file
    $validation = Security::validateUpload($file, $allowedTypes, $maxSize);
    
    if (!$validation['valid']) {
        continue; // Bỏ qua file không hợp lệ
    }
    
    // Generate safe filename
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $newFileName = 'chat_' . time() . '_' . uniqid() . '_' . rand(1000, 9999) . '.' . $ext;
    $targetFile = $targetDir . $newFileName;
    
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        $uploadedImages[] = $newFileName;
    }
}

if (empty($uploadedImages)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No valid images uploaded']);
    exit;
}

echo json_encode([
    'success' => true,
    'images' => $uploadedImages,
    'count' => count($uploadedImages)
]);
?>

