<?php
require_once(__DIR__ . '/../model/mReview.php');

class cReview {
    public function getReviewsBySeller($sellerId) {
        $model = new mReview();
        return $model->getReviewsBySellerId($sellerId);
    }

    public function themDanhGia() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $model = new mReview();
        $reviewer_id = $_POST['reviewer_id'];
$reviewed_user_id = $_POST['reviewed_user_id'];
$product_id = $_POST['product_id'];
$rating = $_POST['rating'];
$comment = trim($_POST['comment']);

        // Kiểm tra đã đánh giá chưa
        if ($model->daDanhGia($reviewer_id, $reviewed_user_id, $product_id)) {
           header("Location: ../index.php?tin-nhan&to=$reviewed_user_id&msg=" . urlencode("Bạn đã đánh giá người này cho sản phẩm này rồi!"));
            exit;
        }

        $ok = $model->themDanhGia($reviewer_id, $reviewed_user_id, $product_id, $rating, $comment);
        header("Location: ../index.php?tin-nhan&to=$reviewed_user_id&danhgia=" . ($ok ? "success" : "fail"));
        exit;
        }
    }
    
}
