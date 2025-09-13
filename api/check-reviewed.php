<?php
require_once("../model/mReview.php");
$mReview = new mReview();

$from = intval($_GET['from'] ?? 0);
$to = intval($_GET['to'] ?? 0);
$product_id = intval($_GET['product_id'] ?? 0);

$reviewed = $mReview->daDanhGia($from, $to, $product_id);
echo json_encode(['reviewed' => $reviewed]);
