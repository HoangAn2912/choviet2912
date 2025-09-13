<?php
require_once("../controller/cReview.php");

$cReview = new cReview();
$action = $_GET['act'] ?? '';

if ($action === 'themDanhGia') {
    $cReview->themDanhGia();
}
