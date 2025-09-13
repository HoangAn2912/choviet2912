
 <head>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
 </head>

<?php
include_once("controller/cPost.php");
include_once("model/mPost.php");

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Bạn cần đăng nhập để xem tin đăng của mình.'); window.location.href='index.php?login';</script>";
    exit;
}



$postCtrl = new cPost();
$userId = $_SESSION['user_id'] ?? 0;
$posts = $postCtrl->layDanhSachTinNguoiDung($userId);
$count = $postCtrl->demSoLuongTheoTrangThai($userId);
$user = $postCtrl->layThongTinNguoiDung($userId);
$tin = null;
if (isset($_GET['sua'])) {
    $tinId = intval($_GET['sua']);
    $tin = $postCtrl->laySanPhamTheoId($tinId);
    if (!$tin || $tin['user_id'] != $userId) {
        echo "<script>alert('Không tìm thấy bài viết hoặc bạn không có quyền chỉnh sửa.'); window.location.href='index.php?quan-ly-tin';</script>";
        exit;
    }
    echo "<script>document.addEventListener('DOMContentLoaded', function() {
  openSuaTinModal();
});</script>";
}
$tenLoai = '';
if ($tin && isset($tin['category_id'])) {
    $tenLoai = $postCtrl->layTenLoaiSanPham($tin['category_id']);
}

function getBadgeColor($status) {
  $map = [
    'Đang bán' => 'success',
    'Đã bán' => 'secondary',
    'Chờ duyệt' => 'warning',
    'Từ chối' => 'danger',
    'Đã ẩn' => 'dark',
  ];
  return $map[$status] ?? 'secondary';
}

function getNoProductText($status) {
  $map = [
    'Đang bán' => 'Chưa có sản phẩm đang bán.',
    'Đã bán' => 'Chưa có sản phẩm đã bán.',
    'Chờ duyệt' => 'Chưa có sản phẩm chờ duyệt.',
    'Từ chối' => 'Chưa có sản phẩm bị từ chối.',
    'Đã ẩn' => 'Chưa có sản phẩm đã ẩn.',
  ];
  return $map[$status] ?? 'Chưa có sản phẩm.';
}
?>
<div id="modalOverlay" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:1050;"></div>
<?php include_once("view/header.php"); ?>

<div class="container my-2">
  <div class="d-flex justify-content-between align-items-center mb-2" id="profileUser">
    <div class="d-flex align-items-center">
      <img src="img/<?= htmlspecialchars($user['avatar'] ?? 'default-avatar.png') ?>" class="rounded-circle mr-3" alt="Avatar" width="50" height="50" style="object-fit: cover;">
      <div><div class="font-weight-bold"><?= htmlspecialchars($user['username'] ?? 'Tên đăng nhập') ?></div></div>
    </div>
    <div class="d-flex align-items-center">
      <i class="fas fa-coins text-warning mr-2"></i>
      <span class="font-weight-bold text-dark"><?= number_format($user['balance'] ?? 0, 0, ',', '.') ?> đ</span>
      <button onclick="window.location.href='index.php?nap-tien'" class="btn btn-success btn-sm ml-3 rounded-circle" title="Nạp thêm" style="width: 30px; height: 30px; padding: 0;">+</button>
    </div>
  </div>

  <hr class="m-0" style="border-top: 2px solid #ddd;">

  <ul class="nav nav-tabs mb-4" id="tabTinDang">
    <?php $statusList = ['Đang bán', 'Đã bán', 'Chờ duyệt', 'Từ chối', 'Đã ẩn']; ?>
    <?php foreach ($statusList as $tab): ?>
      <li class="nav-item">
        <a class="nav-link<?= $tab === 'Đang bán' ? ' active' : '' ?>" data-status="<?= $tab ?>" href="#">
          <?= $tab ?> (<?= $count[$tab] ?? 0 ?>)
        </a>
      </li>
    <?php endforeach; ?>
  </ul>

  <div class="row" id="tinDangList">
    <?php foreach ($statusList as $statusTab): ?>
      <?php $hasProduct = false; ?>
      <?php foreach ($posts as $post): ?>
        <?php
            if ($post['status'] === 'Chờ duyệt' || $post['status'] === 'Từ chối') {
                $status = $post['status'];
            } else {
                $status = $post['sale_status'];
            }
            ?>
        <?php if ($status === $statusTab): ?>
          <?php $hasProduct = true; ?>
          <div class="col-12 mb-3 product-item" data-status="<?= $statusTab ?>">
            <div class="card shadow-sm product-card">
              <div class="row no-gutters align-items-stretch" style="min-height: 220px;">
                <div class="col-md-3">
                  <img src="img/<?= explode(',', $post['image'])[0] ?>" class="card-img-top product-image">
                </div>
                <div class="col-md-9 d-flex flex-column justify-content-center">
                  <div class="card-body pb-2">
                    <h5 class="card-title mb-2"><?= htmlspecialchars($post['title']) ?></h5>
                    <p class="text-danger font-weight-bold mb-1"><?= number_format($post['price']) ?> đ</p>
                    <p class="card-text small text-muted mb-1">
                      <i class="fas fa-map-marker-alt mr-1" style="color:rgb(49, 49, 49);"></i>
                      <?= htmlspecialchars($user['address'] ?? 'Chưa cập nhật') ?>
                    </p>
                    <p class="card-text small text-muted mb-1">
                      <i class="fas fa-clock mr-1" style="color: #6c757d;"></i>
                      Cập nhật: <?= $post['thoi_pricen_cu_the'] ?>
                    </p>
                    <p class="card-text small text-muted">
                      <i class="fas fa-info-circle mr-1" style="color: #007bff;"></i>Trạng thái:
                      <span class="badge badge-<?= getBadgeColor($status) ?>"><?= $status ?></span>
                    </p>
                  </div>
                  <?php if ($status === 'Đang bán'): ?>
                    <div class="d-flex flex-wrap align-items-center gap-2 pd">
                      <div class="btn-group">
                        <button type="button" class="btn btn-action btn-sm dropdown-toggle color-text" data-toggle="dropdown">
                          <i class="fas fa-sync-alt mr-1"></i> Cập nhật trạng thái
                        </button>
                        <div class="dropdown-menu">
                          <a class="dropdown-item d-flex align-items-center" href="#" onclick="xacNhanCapNhat(<?= $post['id'] ?>, 'Đã bán')">
                            <i class="fas fa-check-circle mr-2" style="color: #28a745;"></i> Đã bán sản phẩm
                          </a>
                          <a class="dropdown-item d-flex align-items-center" href="#" onclick="xacNhanCapNhat(<?= $post['id'] ?>, 'Đã ẩn')">
                            <i class="fas fa-eye-slash mr-2" style="color: #6c757d;"></i> Ẩn sản phẩm
                          </a>
                        </div>
                      </div>
                      <button class="btn btn-edit btn-sm" onclick="window.location.href='index.php?quan-ly-tin&sua=<?= $post['id'] ?>'">
                        <i class="fas fa-edit mr-1"></i> Sửa tin
                      </button>
                      <button class="btn btn-push btn-sm" onclick="xacNhanDayTin(<?= $post['id'] ?>)">
                        <i class="fas fa-bullhorn mr-1"></i> Đẩy tin
                      </button>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>

      <?php if (!$hasProduct): ?>
        <div class="col-12 no-product text-center text-muted my-4" data-status="<?= $statusTab ?>">
          <?= getNoProductText($statusTab) ?>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
</div>

<!-- Modal sửa tin -->

<div class="modal" id="suaTinModal" tabindex="-1" style="display:none; z-index:1100;">
  <div class="modal-dialog">
    <div class="modal-content p-4 rounded" style="background: white; width: 600px; margin: 80px auto; box-shadow: 0 4px 10px rgba(0,0,0,0.2); position: relative; left: -45px;">
      <div class="modal-header" style="padding-top: 0; padding-bottom: 10px;">
        <h4 class="modal-title font-weight-bold text-center w-100" id="modal-subtitle" style="margin-top: 0;">Sửa tin</h4>
        <button type="button" class="close" onclick="closeSuaTinModalNew()" style="position: absolute; top: 16px; right: 16px; font-size: 22px; color: #555;">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="modal-body">
        <form id="form-sua-tin" action="index.php?action=suaTin&id=<?= $tin['id'] ?? '' ?>" method="post" enctype="multipart/form-data" data-userid="<?= $_SESSION['user_id'] ?? 0 ?>">
          <!-- Tiêu đề tin đăng -->
          <div class="form-group">
            <label for="tieuDeSua" class="font-weight-bold">
              Tiêu đề bài đăng <span class="text-danger">*</span>
            </label>
                         <input type="text" class="form-control" id="tieuDeSua" name="title" placeholder="Nhập tên sản phẩm cần bán" value="<?= htmlspecialchars($tin['title'] ?? '') ?>" required>
          </div>

          <!-- Giá bán -->
          <div class="form-group">
            <label for="priceSua" class="font-weight-bold">
              Giá bán (đ) <span class="text-danger">*</span>
            </label>
                         <input type="number" class="form-control" id="priceSua" name="price" placeholder="Nhập số tiền cần bán" value="<?= htmlspecialchars($tin['price'] ?? '') ?>" required>
          </div>

          <!-- Mô tả chi tiết -->
          <div class="form-group">
            <label for="moTaSua" class="font-weight-bold">
              Mô tả chi tiết <span class="text-danger">*</span>
            </label>
                         <textarea class="form-control" id="moTaSua" name="description" rows="5" placeholder="Mô tả chi tiết sản phẩm..." required><?= htmlspecialchars($tin['description'] ?? '') ?></textarea>
          </div>

          <!-- Loại sản phẩm -->
          <div class="form-group">
            <label class="font-weight-bold">
              Loại sản phẩm <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($tenLoai) ?>" readonly>
          </div>

          <!-- Ảnh hiện tại -->
          <div class="form-group">
            <label class="font-weight-bold">Ảnh hiện tại</label>
            <div class="preview-anh-cu mb-2">
                             <?php if (!empty($tin['image'])): ?>
                 <?php foreach (explode(',', $tin['image']) as $img): ?>
                  <img src="img/<?= htmlspecialchars($img) ?>" width="80" style="margin: 5px; object-fit: cover;">
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>

          <!-- Chọn ảnh mới -->
          <div class="form-group">
            <label for="hinhAnhSua" class="font-weight-bold">
              Hình ảnh sản phẩm <span class="text-danger">*</span>
            </label>
                         <input type="file" class="form-control-file" id="hinhAnhSua" name="image[]" accept=".jpg,.jpeg,.png" multiple>
            <small class="form-text text-muted mt-2">Chọn từ 2 đến 6 hình ảnh (định dạng .jpg, .png).</small>
          </div>

          <!-- Nút Lưu thay đổi -->
          <button type="submit" class="btn btn-warning w-100 font-weight-bold text-white" style="font-size: 16px;">Lưu thay đổi</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include_once("view/footer.php"); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const tabs = document.querySelectorAll('#tabTinDang .nav-link');
  const allProducts = document.querySelectorAll('.product-item, .no-product');

  function showTab(status) {
    allProducts.forEach(el => {
      el.style.display = (el.dataset.status === status) ? 'block' : 'none';
    });
  }

  tabs.forEach(tab => {
    tab.addEventListener('click', function (e) {
      e.preventDefault();
      tabs.forEach(t => t.classList.remove('active'));
      this.classList.add('active');
      const selectedStatus = this.getAttribute('data-status');
      showTab(selectedStatus);
    });
  });

  // Hiển thị mặc định tab "Đang bán"
  showTab('Đang bán');
});
</script>

<script>
function xacNhanDayTin(id) {
    if (confirm("Bạn sẽ mất phí 11.000đ để đẩy tin này đến với nhiều người xem mới hơn.\nBạn có chắc chắn muốn tiếp tục?")) {
        window.location.href = 'index.php?quan-ly-tin&daytin=' + id;
    }
}
</script>

<script>
function xacNhanCapNhat(id, loai) {
  let message = '';
  if (loai === 'Đã bán') {
    message = "Bạn có chắc chắn muốn ẩn sản phẩm đã bán này không?";
  } else if (loai === 'Đã ẩn') {
    message = "Bạn có chắc chắn muốn ẩn sản phẩm này không?";
  }

  if (confirm(message)) {
    fetch('index.php?action=capNhatTrangThai', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `id=${id}&loai=${loai}`
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'success') {
      window.location.href = "index.php?quan-ly-tin&toast=" + encodeURIComponent("✅ Cập nhật thành công!") + "&type=success";
    } else {
      showToast(data.message || "❌ Cập nhật không thành công!", "error");
    }
  });
  }
}
</script>

<script>
function openSuaTinModal() {
  document.getElementById('modalOverlay').style.display = 'block';
  document.getElementById('suaTinModal').style.display = 'block';
  document.getElementById('form-sua-tin').style.display = 'block';
  document.getElementById('modal-subtitle').innerText = 'Sửa tin';
}

function closeSuaTinModalNew() {
  document.getElementById('modalOverlay').style.display = 'none';
  document.getElementById('suaTinModal').style.display = 'none';
}
</script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<!-- Toastify JS -->
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<!-- Hàm showToast -->
<script src="js/toast.js"></script>
<!-- Gọi toast nếu có -->
<?php include_once("toastify.php"); ?>
<!-- Bootstrap JS (v5) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script> -->


