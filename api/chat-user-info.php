<?php
header("Content-Type: application/json");
require_once("../model/mUser.php");
$mUser = new mUser();

if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    $user = $mUser->getUserById($user_id); // viết sẵn hàm này rồi

    if ($user) {
        echo json_encode($user);
        exit;
    }
}
http_response_code(404);
echo json_encode(["error" => "User not found"]);
