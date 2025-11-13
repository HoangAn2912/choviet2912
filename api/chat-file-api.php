<?php
header("Content-Type: application/json");

require_once("../controller/cChat.php");

$from = isset($_GET['from']) ? intval($_GET['from']) : 0;
$to = isset($_GET['to']) ? intval($_GET['to']) : 0;

if ($from <= 0 || $to <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid parameters"]);
    exit;
}

$cChat = new cChat();
$messages = $cChat->getMessagesFromFile($from, $to);
echo json_encode($messages);
?>