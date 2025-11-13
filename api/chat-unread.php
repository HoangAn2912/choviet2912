<?php
header("Content-Type: application/json");

require_once("../controller/cChat.php");

$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
if ($userId <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "missing user_id"]);
    exit;
}

$cChat = new cChat();
$count = $cChat->demTinNhanChuaDoc($userId);
echo json_encode(["unread_count" => $count]);
?>


