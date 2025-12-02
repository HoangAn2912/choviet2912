<?php
require_once(__DIR__ . '/../model/mReview.php');

class cReview {
    public function getReviewsBySeller($sellerId) {
        $model = new mReview();
        return $model->getReviewsBySellerId($sellerId);
    }

    public function themDanhGia() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../index.php");
            exit;
        }

        $model = new mReview();

        $reviewer_id      = intval($_POST['reviewer_id'] ?? 0);
        $reviewed_user_id = intval($_POST['reviewed_user_id'] ?? 0);
        $product_id       = intval($_POST['product_id'] ?? 0);
        $rating           = intval($_POST['rating'] ?? 0);
        $comment          = trim($_POST['comment'] ?? '');

        if ($reviewer_id <= 0 || $reviewed_user_id <= 0 || $product_id <= 0 || $rating <= 0) {
            header("Location: ../index.php?tin-nhan&to={$reviewed_user_id}&toast=" . urlencode("❌ Thiếu thông tin đánh giá hợp lệ.") . "&type=error");
            exit;
        }

        $message = '';
        $ok = false;

        // Nếu có thông tin đơn hàng, dùng hệ thống review nâng cao
        $orderType = $_POST['order_type'] ?? '';
        $orderId   = intval($_POST['order_id'] ?? 0);
        if ($orderType && $orderId > 0) {
            $data = [
                'reviewer_id'      => $reviewer_id,
                'reviewed_user_id' => $reviewed_user_id,
                'product_id'       => $product_id,
                'rating'           => $rating,
                'comment'          => $comment,
                'order_type'       => $orderType,
                'order_id'         => $orderId,
            ];

            $result = $model->addReview($data);
            $ok = $result['success'];
            $message = $result['message'];
        } else {
            if ($model->daDanhGia($reviewer_id, $reviewed_user_id, $product_id)) {
                header("Location: ../index.php?tin-nhan&to={$reviewed_user_id}&toast=" . urlencode("⚠️ Bạn đã đánh giá người này cho sản phẩm này rồi!") . "&type=warning");
                exit;
            }

            $ok = $model->themDanhGia($reviewer_id, $reviewed_user_id, $product_id, $rating, $comment);
            $message = $ok ? 'Đánh giá thành công!' : 'Không thể lưu đánh giá.';
        }

        if ($ok) {
            // Đánh giá thành công: quay lại khung chat với thông tin sản phẩm đã đánh giá
            header("Location: ../index.php?tin-nhan&to={$reviewed_user_id}&product_id={$product_id}&reviewed=1&toast=" . urlencode("✅ " . $message) . "&type=success");
        } else {
            header("Location: ../index.php?tin-nhan&to={$reviewed_user_id}&product_id={$product_id}&toast=" . urlencode("❌ " . $message) . "&type=error");
        }
        exit;
    }
}
