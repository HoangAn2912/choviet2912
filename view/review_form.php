<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<?php
require_once("controller/cUser.php");
require_once("model/mReview.php");

$reviewer_id = $_GET['from'] ?? 0;
$reviewed_user_id = $_GET['to'] ?? 0;
$product_id = $_GET['product_id'] ?? 0;

$mReview = new mReview();
$cUser = new cUser();

$daDanhGia = $mReview->daDanhGia($reviewer_id, $reviewed_user_id, $product_id);
$receiver = $cUser->getUserById($reviewed_user_id);
?>

<?php include("view/header.php"); ?>
<div class="container py-4">
  <h4 class="mb-4">Đánh giá người bán</h4>
  <?php if (!$daDanhGia): ?>
  <form action="api/review-api.php?act=themDanhGia" method="post">
    <input type="hidden" name="reviewer_id" value="<?= $reviewer_id ?>">
<input type="hidden" name="reviewed_user_id" value="<?= $reviewed_user_id ?>">
<input type="hidden" name="product_id" value="<?= $product_id ?>">

    <div class="mb-3">
      <label class="form-label">Người được đánh giá:</label>
      <div class="d-flex align-items-center">
        <img src="img/<?= $receiver['avatar'] ?>" alt="avatar" width="50" class="rounded-circle me-2">
        <strong><?= htmlspecialchars($receiver['username']) ?></strong>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Số sao</label>
      <select name="rating" class="form-control" required>
        <?php for ($i = 5; $i >= 1; $i--): ?>
          <option value="<?= $i ?>"><?= $i ?> sao</option>
        <?php endfor; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Bình luận</label>
      <textarea name="comment" class="form-control" rows="4" required></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
  </form>
  <?php else: ?>
    <div class="alert alert-info mt-3">Bạn đã đánh giá người này rồi.</div>
  <?php endif; ?>
</div>
<?php include("view/footer.php"); ?>