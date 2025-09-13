<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once("../model/mChat.php");
$mChat = new mChat();

// ----------------- POST: Lưu tin nhắn vào DB -----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['from'], $data['to'], $data['content'])) {
        http_response_code(400);
        echo json_encode(["status" => "missing_fields"]);
        exit;
    }

    $from = intval($data['from']);
    $to = intval($data['to']);
    $content = trim($data['content']);
    $idSanPham = isset($data['product_id']) ? intval($data['product_id']) : null;

    if ($content === "") {
        echo json_encode(["status" => "empty"]);
        exit;
    }

    $success = $mChat->sendMessage($from, $to, $content, $idSanPham);
    echo json_encode(["status" => $success ? "ok" : "db_error"]);
    exit;
}

// ----------------- GET: Lấy tin nhắn từ DB -----------------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['from'], $_GET['to'])) {
    $from = intval($_GET['from']);
    $to = intval($_GET['to']);

    $messages = $mChat->getMessages($from, $to);
    echo json_encode($messages);
    exit;
}

// ----------------- Trường hợp không hợp lệ -----------------
http_response_code(400);
echo json_encode(["status" => "error", "message" => "Invalid request"]);
