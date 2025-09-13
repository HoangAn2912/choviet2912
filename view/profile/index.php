<?php
require_once 'model/mLoginLogout.php';
require_once 'model/mProfile.php';
require_once 'controller/cProfile.php';
include_once("controller/cReview.php");
require_once __DIR__ . '/../../helpers/url_helper.php';
$baseUrl = getBaseUrl() . '/';
$model = new mProfile();
$cReview = new cReview();
$cProfile = new cProfile();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?login');
    exit;
}

if (isset($_GET['thongtin']) && is_numeric($_GET['thongtin']) && intval($_GET['thongtin']) > 0) {
    $idNguoiDung = intval($_GET['thongtin']);
} else {
    $idNguoiDung = $_SESSION['user_id'];
}

// Tạo URL thân thiện cho người dùng hiện tại
$friendlyUrl = $cProfile->getFriendlyUrl($idNguoiDung);

$user = $model->getUserById($idNguoiDung);
$ratingStats = $model->getRatingStats($idNguoiDung);
$totalReviews = $ratingStats['total_reviews'] ?? 0;
$averageRating = number_format($ratingStats['average_rating'] ?? 0, 1);
$reviews = $cReview->getReviewsBySeller($idNguoiDung);
$sanPhamDangHienThi = $cProfile->getSanPhamDangHienThi($idNguoiDung);
$sanPhamDaBan = $cProfile->getSanPhamDaBan($idNguoiDung);
$avatarPath = 'img/';
$anh = $user['avatar'] ?? '';
$avatarFile = 'default.jpg';
$countDangHienThi = $cProfile->countSanPhamDangHienThi($idNguoiDung);
$countDaBan = $cProfile->countSanPhamDaBan($idNguoiDung);
if (!empty($anh)) {
    $filePath = $avatarPath . $anh;
    if (file_exists($filePath)) {
        $avatarFile = $anh;
    }
}

?>
<head>
<link href="../css/profile.css" rel="stylesheet">
<style>
    .profile-product-list {
    max-height: 450px;   /* hoặc 5*90px nếu mỗi sản phẩm ~90px */
    overflow-y: auto;
    padding-right: 8px;  /* tránh che nội dung bởi thanh cuộn */
}
/* Thanh tab */
.profile-tabs {
    margin-bottom: 18px;
    display: flex;
    gap: 24px;
}
.profile-tabs .tab-link {
    padding: 0 0 10px 0;
    font-weight: 600;
    color: #333;
    border: none;
    background: none;
    border-bottom: 3px solid transparent;
    transition: border-color 0.2s, color 0.2s;
    position: relative;
}
.profile-tabs .tab-link.tab-active {
    color: #FFA800;
    border-bottom: 3px solid #FFA800;
}

</style>
</head>
<?php
include_once("view/header.php");
?>
<div class="container my-5">
    <div class="row">
        <!-- Cột trái: Thông tin user -->
        <div class="col-md-4">
            <div class="card profile-info text-center p-4">
                <?php
                    $avatarPath = 'img/';
                    $avatarFile = 'default.jpg';
                    if (!empty($user['avatar'])) {
                        $anh = basename($user['avatar']);
                        $absolutePath = $_SERVER['DOCUMENT_ROOT'] . getBasePath() . '/' . $avatarPath . $anh;
                        if (file_exists($absolutePath)) {
                            $avatarFile = $anh;
                        }
                    }
                ?>
                <img src="<?= $avatarPath . htmlspecialchars($avatarFile) ?>"
                     alt="Ảnh đại diện"
                     class="profile-avatar mb-3"
                     onerror="this.onerror=null;this.src='img/default.jpg';">

                <h5 class="mb-1"><?= htmlspecialchars($user['username']) ?></h5>
                <p class="text-muted small">
                    <?= ($idNguoiDung == $_SESSION['user_id']) ? "Chào mừng bạn đến với trang cá nhân của bạn." : "Đây là trang cá nhân của " . htmlspecialchars($user['username']) . "." ?>
                </p>
                   
                <div class="info-left text-left mt-3">
                <?php if ($totalReviews > 0): ?>
                    <p class="mt-2 mb-2">
                        <strong><?= $averageRating ?></strong>
                        <?php for ($i = 0; $i < floor($averageRating); $i++): ?>
                            <i class="fas fa-star text-warning"></i>
                        <?php endfor; ?>
                        <?php if ($averageRating - floor($averageRating) >= 0.5): ?>
                            <i class="fas fa-star-half-alt text-warning"></i>
                        <?php endif; ?>
                        (<?= $totalReviews ?> đánh giá)
                    </p>
                <?php else: ?>
                    <p class="text-muted">(Chưa có đánh giá)</p>
                <?php endif; ?>
                    <p><i class="fas fa-envelope" style="color: #3D464D;"></i> Email:  <?= htmlspecialchars($user['email']) ?></p>
                    <p><i class="fas fa-phone" style="color: #3D464D;"></i> SĐT: <?= htmlspecialchars($user['phone']) ?></p>
                    <?php if ($idNguoiDung == $_SESSION['user_id']): ?>
                        <p><i class="fas fa-credit-card" style="color: #3D464D;"></i> Mã tài khoản: <?= htmlspecialchars($user['id_ck']) ?></p>
                    <?php endif; ?>
                    <p><i class="fas fa-map-marker-alt" style="color: #3D464D;"></i>  Địa chỉ: <?= htmlspecialchars($user['address']) ?></p>
                    <p><i class="fas fa-calendar-alt" style="color: #3D464D;"></i> Ngày sinh: <?= date('d/m/Y', strtotime($user['birth_date'])) ?></p>
                                         <p><i class="fas fa-calendar-alt" style="color: #3D464D;"></i> Ngày tham gia: <?= date('d/m/Y', strtotime($user['created_date'])) ?></p>
                </div>


                <?php if ($idNguoiDung == $_SESSION['user_id']): ?>
                    <button class="btn btn-warning mt-3 w-100" onclick="document.getElementById('editProfileModal').style.display='block'">
                        <i class="fas fa-edit mr-1"></i> Chỉnh sửa thông tin
                    </button>
                    <div class="mt-2 text-center">
                        <small>URL trang cá nhân: <a href="<?= $friendlyUrl ?>"><?= getBaseUrl() ?>/<?= $friendlyUrl ?></a></small>
                    </div>
                <?php else: ?>
                    <a href="index.php?tin-nhan&to=<?= $idNguoiDung ?>" class="btn btn-warning mt-3 w-100 text-white">
                        <i class="fas fa-comment mr-1" style="color: #3D464D;"></i> Nhắn tin
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Cột phải: Tin đăng + Đánh giá -->
        <div class="col-md-8">
            <!-- Hiển thị đang bán và đã bán -->
            <div class="card profile-posts mb-4 " style="padding: 25px;">
                <div class="profile-tabs mb-3" style="padding-bottom: 10px;">
                    <a href="#" class="tab-link tab-active" data-target="#tab-danghienthi">
                        Đang hiển thị (<?= $countDangHienThi ?>)
                    </a>
                    <a href="#" class="tab-link" data-target="#tab-daban">
                        Đã bán (<?= $countDaBan ?>)
                    </a>
                </div>
                
                 <!-- Tab Đang hiển thị -->
                <div id="tab-danghienthi" class="tab-content profile-product-list">
                    <?php if (empty($sanPhamDangHienThi)): ?>
                        <div class="text-center text-muted">
                            <img src="img/no-posts.png" style="max-width: 200px;" alt="Không có tin">
                            <p>Không có sản phẩm đang hiển thị</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($sanPhamDangHienThi as $sp): ?>
                            <div class="d-flex mb-3 align-items-start">
                                <img src="img/<?= explode(',', $sp['image'])[0] ?>" style="width:80px; height:80px; object-fit:cover; border-radius:4px; margin-right: 15px;">
                                <div>
                                    <strong><?= htmlspecialchars($sp['title']) ?></strong><br>
                                    <span class="text-danger"><?= number_format($sp['price'], 0, ',', '.') ?> đ</span><br>
                                    <small class="text-muted">Cập nhật: <?= date('d/m/Y H:i', strtotime($sp['created_date'])) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                 <!-- Tab Đã bán -->
                <div id="tab-daban" class="tab-content profile-product-list" style="display:none">
                    <?php if (empty($sanPhamDaBan)): ?>
                        <div class="text-center text-muted">
                            <img src="img/no-posts.png" style="max-width: 200px;" alt="Không có tin">
                            <p>Không có sản phẩm đã bán</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($sanPhamDaBan as $sp): ?>
                            <div class="d-flex mb-3 align-items-start">
                                <img src="img/<?= explode(',', $sp['image'])[0] ?>" style="width:80px; height:80px; object-fit:cover; border-radius:4px; margin-right: 15px;">
                                <div>
                                    <strong><?= htmlspecialchars($sp['title']) ?></strong><br>
                                    <span class="text-danger"><?= number_format($sp['price'], 0, ',', '.') ?> đ</span><br>
                                    <small class="text-muted">Cập nhật: <?= date('d/m/Y H:i', strtotime($sp['created_date'])) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>

             <!-- Đánh giá -->
            <div class="card profile-posts profile-product-list" style="padding: 25px;">
                <h5 class="mb-3 profile-tabs">Đánh giá sản phẩm</h5>
                <?php if (empty($reviews)): ?>
                    <div class="text-center text-muted">
                        <img src="img/no-posts.png" style="max-width: 200px;" alt="Không có đánh giá">
                        <p>Bạn chưa có đánh giá nào</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="d-flex mb-3 align-items-start">
                            <?php
                                $firstImg = '';
                                if (!empty($review['image'])) {
                                    $arr = explode(',', $review['image']);
                                    $firstImg = trim($arr[0]);
                                }
                                ?>
                                <img src="img/<?= htmlspecialchars($firstImg) ?>" alt="" style="width:80px; height:80px; object-fit:cover; margin-right:15px; border-radius:4px;">
                            <div>
                                <div class="mb-1">
                                                                         <strong><?= htmlspecialchars($review['reviewer_name']) ?></strong>
                                     <small class="text-muted ml-2"><?= date('d/m/Y', strtotime($review['review_date'])) ?></small>
                                </div>
                                <div class="rating mb-1">
                                    <?php
                                    $full = floor($review['rating']);
$half = $review['rating'] - $full >= 0.5;
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $full) echo '<i class="fas fa-star text-warning"></i>';
                                        elseif ($half && $i === $full + 1) echo '<i class="fas fa-star-half-alt text-warning"></i>';
                                        else echo '<i class="far fa-star text-warning"></i>';
                                    }
                                    ?>
                                </div>
                                <div class="product-name mb-1">
                                    <strong><?= htmlspecialchars($review['title']) ?></strong> - <span class="text-danger"><?= number_format($review['price'], 0, ',', '.') ?> đ</span>
                                </div>
                                <div class="review-comment"><?= htmlspecialchars($review['description']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<?php
    include_once("view/footer.php");
?>
<?php
    include_once("js/profile.php");
?>
<!-- Modal Chỉnh Sửa Thông Tin -->
<div id="editProfileModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); overflow-y:auto; z-index:1050;">
  <div class="modal-content p-4 rounded" style="background:white; width:600px; margin:80px auto; position:relative;">
    <h4 class="font-weight-bold text-center mb-3">Chỉnh sửa thông tin cá nhân</h4>
    <button onclick="document.getElementById('editProfileModal').style.display='none'" class="btn btn-link p-0" style="position:absolute; top:10px; right:10px; font-size:22px;"><i class="fas fa-times"></i></button>

    <form method="POST" action="index.php?action=capNhatThongTin" enctype="multipart/form-data">
      <div class="form-group">
        <label>Email <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
      </div>

      <div class="form-group">
        <label>Số điện thoại</label>
        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>"
       pattern="[0-9]{10,11}" title="Vui lòng nhập số điện thoại hợp lệ (10–11 chữ số)">
      </div>

      <div class="form-group">
        <label>Địa chỉ</label>
        <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['address']) ?>">
      </div>

      <div class="form-group">
        <label>Ngày sinh</label>
        <input type="date" name="birth_date" class="form-control" max="<?= date('Y-m-d') ?>"
value="<?= htmlspecialchars($user['birth_date']) ?>">
      </div>

      <div class="form-group">
        <label>Ảnh đại diện mới (tuỳ chọn)</label>
        <input type="file" name="avatar" class="form-control-file" accept=".jpg,.jpeg,.png">
      </div>

      <button type="submit" class="btn btn-warning w-100 font-weight-bold text-white">Cập nhật</button>
    </form>
  </div>
</div>
