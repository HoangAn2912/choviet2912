<?php
// ===== File: api/chat-first-message-from-file.php =====
header("Content-Type: application/json");

$from = isset($_GET['from']) ? intval($_GET['from']) : 0;
$to = isset($_GET['to']) ? intval($_GET['to']) : 0;

if ($from === 0 || $to === 0) {
    http_response_code(400);
    echo json_encode(["error" => "Thiếu tham số"]);
    exit;
}

$min = min($from, $to);
$max = max($from, $to);
$filePath = __DIR__ . "/../chat/chat_{$min}_{$max}.json";

if (!file_exists($filePath)) {
    http_response_code(404);
    echo json_encode(["error" => "Không tìm thấy file tin nhắn"]);
    exit;
}

$messages = json_decode(file_get_contents($filePath), true);
if (!is_array($messages) || count($messages) === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Không có tin nhắn"]);
    exit;
}

$first = $messages[0];

// ✅ Lấy product_id từ DB dòng đầu tiên
require_once("../model/mChat.php");
$model = new mChat();
$row = $model->getFirstMessage($first['from'], $first['to']);
$idSanPham = $row['product_id'] ?? 0;

// ✅ Trả kết quả đầy đủ
echo json_encode([
    "id_nguoi_gui" => $first['from'],
    "id_nguoi_nhan" => $first['to'],
    "product_id" => $idSanPham,
    "thoi_gian" => $first['timestamp']
]);
exit;