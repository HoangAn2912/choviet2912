<?php
header("Content-Type: application/json");
require_once("../controller/cUser.php");

if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    $cUser = new cUser();
    $user = $cUser->getUserById($user_id);

    if ($user) {
        echo json_encode($user);
        exit;
    }
}
http_response_code(404);
echo json_encode(["error" => "User not found"]);
