<?php
header("Content-Type: application/json");

$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
if ($userId <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "missing user_id"]);
    exit;
}

$chatDir = __DIR__ . "/../chat";
$file = $chatDir . "/unread_{$userId}.json";

if (!file_exists($file)) {
    echo json_encode([]);
    exit;
}

$data = json_decode(file_get_contents($file), true);
if (!is_array($data)) $data = [];
echo json_encode($data);
?>


