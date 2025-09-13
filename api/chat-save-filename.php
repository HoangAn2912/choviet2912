<?php
require_once("../controller/cChat.php");
$cChat = new cChat();

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['from'], $data['to'], $data['file_name'])) {
    $from = intval($data['from']);
    $to = intval($data['to']);
    $file = basename(trim($data['file_name'])); // tránh ../

    $cChat->saveFileName($from, $to, $file);
    echo json_encode(['status' => 'ok']);
    exit;
}
http_response_code(400);
echo json_encode(['status' => 'error']);

