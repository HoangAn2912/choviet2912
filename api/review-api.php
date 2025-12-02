<?php
session_start();

require_once __DIR__ . '/../controller/cReview.php';

$action = $_GET['act'] ?? '';

switch ($action) {
    case 'themDanhGia':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../index.php');
            exit;
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: ../index.php?login');
            exit;
        }

        // Ép reviewer_id = user đang đăng nhập để tránh giả mạo
        $_POST['reviewer_id'] = $_SESSION['user_id'];

        $ctrl = new cReview();
        $ctrl->themDanhGia();
        break;

    default:
        http_response_code(400);
        echo 'Invalid action';
        break;
}
