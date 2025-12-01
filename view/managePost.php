
 <head>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* ========================================
       PAGE LAYOUT - Background & Wrapper
    ======================================== */
    
    /* Body background */
    body {
        margin: 0;
        padding: 0;
    }

    /* Page background - Lớp ngoài cùng (xám nhẹ) */
    .page-background {
        background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
        min-height: calc(80vh - 10px);
        padding: 0 2rem 2rem 2rem;
    }

    /* Content wrapper - Khối trắng bên trong */
    .content-wrapper {
        background: #ffffff;
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem;
        border-radius: 16px;
        box-shadow: 0 6px 30px rgba(0, 0, 0, 0.12);
    }
    
    /* Avatar styles */
    #profileUser img.rounded-circle {
      border-radius: 50% !important;
      width: 50px !important;
      height: 50px !important;
      object-fit: cover !important;
      display: block !important;
      flex-shrink: 0;
    }
    
    /* Bỏ padding trái phải của product-item */
    .product-item {
      padding-left: 0 !important;
      padding-right: 0 !important;
      position: relative;
      z-index: 1 !important; /* Thấp hơn dropdown */
      overflow: visible !important;
    }
    
    /* Bỏ background màu khi hover trên card */
    .product-card:hover {
      background-color: transparent !important;
    }
    
    .product-card {
      background-color: #fff !important;
      position: relative;
      z-index: 1 !important; /* Thấp hơn dropdown */
      overflow: visible !important;
    }
    
    /* Đảm bảo card không cắt dropdown */
    .product-item .row {
      overflow: visible !important;
      position: relative;
      z-index: 1 !important;
    }
    
    .btn-action {
      background-color: #FFBD1B;
      color: #000 !important;
      border: 1px solid #e0e0e0; /* Viền xám nhẹ */
      font-weight: bold;
      border-radius: 50px;
      transition: background-color 0.3s ease;
    }
    
    .btn-action:hover,
    .btn-action:focus,
    .btn-action:active {
      background-color: #f5f5f5 !important; 
      color: #000 !important; 
      border-color: #e0e0e0 !important; 
    }
    
    .btn-edit {
      background-color: #fff;
      color: #000 !important;
      border: 1px solid #FFBD1B; 
      font-weight: bold;
      border-radius: 50px;
      transition: background-color 0.3s ease;
    }
    
    .btn-edit:hover,
    .btn-edit:focus,
    .btn-edit:active {
      background-color: #f5f5f5 !important; 
      color: #000 !important; /* Giữ màu chữ đen */
      border-color: #e0e0e0 !important; /* Giữ viền xám nhẹ */
    }
    
    
    .btn-push {
      background: #28a745; 
      color: #ffffff !important;
      font-weight: bold;
      border: none;
      border-radius: 50px;
    }
    
    .btn-push:hover {
      background: #218838 !important;
      color: #ffffff !important;
    }
    
    /* Container bên trong không cần padding thêm */
    .content-wrapper .container-fluid,
    .content-wrapper .container {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    
    /* ========================================
       RESPONSIVE - Tablet & Mobile
    ======================================== */
    
    /* Tablet */
    @media (max-width: 991px) {
        .page-background {
            padding: 0 1.5rem 1.5rem 1.5rem;
        }
        
        .content-wrapper {
            padding: 1.5rem;
            border-radius: 14px !important;
        }
    }

    /* Mobile */
    @media (max-width: 768px) {
        .page-background {
            padding: 0 1rem 1rem 1rem;
        }
        
        .content-wrapper {
            padding: 1rem;
            border-radius: 12px !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        }
    }

    /* Mobile nhỏ */
    @media (max-width: 576px) {
        .page-background {
            padding: 0 0.5rem 0.5rem 0.5rem;
        }
        
        .content-wrapper {
            padding: 0.75rem;
            border-radius: 10px !important;
        }
    }
  </style>
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

<!-- Page Background Start -->
<div class="page-background">
    <!-- Content Wrapper Start -->
    <div class="content-wrapper">

<div class="container my-2">
  <div class="d-flex justify-content-between align-items-center mb-2" id="profileUser">
    <div class="d-flex align-items-center">
      <?php 
      $avatarPath = 'img/';
      $avatarFile = !empty($user['avatar']) ? $user['avatar'] : 'default-avatar.png';
      // Đảm bảo avatar không chứa đường dẫn đầy đủ
      if (strpos($avatarFile, 'img/') === 0) {
          $avatarFile = str_replace('img/', '', $avatarFile);
      }
      $avatarFullPath = $avatarPath . htmlspecialchars($avatarFile);
      ?>
      <img src="<?= $avatarFullPath ?>" 
           class="rounded-circle mr-3" 
           alt="Avatar" 
           width="50" 
           height="50" 
           style="object-fit: cover; border-radius: 50%; width: 50px; height: 50px; display: block;"
           onerror="this.onerror=null; this.src='img/default-avatar.png';">
      <div><div class="font-weight-bold"><?= htmlspecialchars($user['username'] ?? 'Người dùng') ?></div></div>
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
            // Xác định trạng thái hiển thị
            $displayStatus = '';
            if ($post['status'] === 'Chờ duyệt' || $post['status'] === 'Từ chối') {
                $displayStatus = $post['status'];
            } else {
                // Chỉ hiển thị các sản phẩm đã được duyệt
                if ($post['status'] === 'Đã duyệt') {
                    $displayStatus = $post['sale_status'] ?? 'Đang bán';
                } else {
                    // Bỏ qua các sản phẩm chưa được duyệt (trừ Chờ duyệt và Từ chối)
                    continue;
                }
            }
            
            // So sánh với status tab hiện tại
            if ($displayStatus === $statusTab):
                $hasProduct = true;
        ?>
          <div class="col-12 mb-3 product-item" data-status="<?= htmlspecialchars($statusTab) ?>" style="position: relative; z-index: auto;">
            <div class="card shadow-sm product-card" style="margin-bottom: -5px; position: relative; z-index: auto;">
              <div class="row no-gutters align-items-stretch" style="min-height: 220px; position: relative; z-index: auto;">
                <div class="col-md-3">
                  <?php 
                  $postImages = !empty($post['image']) ? explode(',', $post['image']) : [];
                  $firstImage = !empty($postImages) ? trim($postImages[0]) : 'default-product.jpg';
                  
                  if (strpos($firstImage, 'img/') === 0) {
                      $firstImage = str_replace('img/', '', $firstImage);
                  }
                  ?>
                  <img src="img/<?= htmlspecialchars($firstImage) ?>" 
                       class="card-img-top product-image" 
                       alt="<?= htmlspecialchars($post['title']) ?>"
                       style="width: 100%; height: 220px; object-fit: cover;"
                       onerror="this.onerror=null; this.src='img/default-product.jpg';">
                </div>
                <div class="col-md-9 d-flex flex-column justify-content-center">
                  <div class="card-body pb-2">
                    <h5 class="card-title mb-2"><?= htmlspecialchars($post['title']) ?></h5>
                    <p class="text-danger font-weight-bold mb-1"><?= number_format($post['price'], 0, ',', '.') ?> đ</p>
                    <p class="card-text small text-muted mb-1">
                      <i class="fas fa-map-marker-alt mr-1" style="color:rgb(49, 49, 49);"></i>
                      <?= htmlspecialchars($user['address'] ?? 'Chưa cập nhật') ?>
                    </p>
                    <p class="card-text small text-muted mb-1">
                      <i class="fas fa-clock mr-1" style="color: #6c757d;"></i>
                      Cập nhật: <?= htmlspecialchars($post['thoi_pricen_cu_the'] ?? $post['thoi_gian_cu_the'] ?? 'N/A') ?>
                    </p>
                    <p class="card-text small text-muted">
                      <i class="fas fa-info-circle mr-1" style="color: #007bff;"></i>Trạng thái:
                      <span class="badge badge-<?= getBadgeColor($displayStatus) ?>"><?= htmlspecialchars($displayStatus) ?></span>
                    </p>
                  </div>
                  <?php if ($displayStatus === 'Đang bán'): ?>
                    <div class="d-flex flex-wrap align-items-center gap-2 pd">
                      <button class="btn btn-action btn-sm" onclick="showModalDaBan(<?= $post['id'] ?>)">
                        <i class="fas fa-check-circle mr-1"></i> Đã bán
                      </button>
                      <button class="btn btn-action btn-sm" onclick="showModalAnSanPham(<?= $post['id'] ?>)">
                        <i class="fas fa-eye-slash mr-1"></i> Ẩn sản phẩm
                      </button>
                      <button class="btn btn-edit btn-sm" onclick="window.location.href='index.php?quan-ly-tin&sua=<?= $post['id'] ?>'">
                        <i class="fas fa-edit mr-1"></i> Sửa tin
                      </button>
                      <button class="btn btn-push btn-sm" onclick="showModalDayTin(<?= $post['id'] ?>)">
                        <i class="fas fa-bullhorn mr-1"></i> Đẩy tin
                      </button>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php 
            endif; 
        ?>
      <?php endforeach; ?>

      <?php if (!$hasProduct): ?>
        <div class="col-12 no-product text-center text-muted my-4" data-status="<?= htmlspecialchars($statusTab) ?>">
          <?= getNoProductText($statusTab) ?>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
</div>

<!-- Modal chọn lý do đã bán -->
<div class="modal" id="modalDaBan" tabindex="-1" style="display:none; z-index:1100;">
  <div class="modal-dialog">
    <div class="modal-content p-4 rounded" style="background: white; width: 600px; margin: 80px auto; box-shadow: 0 4px 10px rgba(0,0,0,0.2); position: relative; left: -45px;">
      <div class="modal-header" style="padding-top: 0; padding-bottom: 10px;">
        <h5 class="modal-title font-weight-bold" style="color: #333;">Chọn lý do đã bán</h5>
        <button type="button" class="close" onclick="closeModalDaBan()" style="font-size: 24px; color: #555;">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="mb-3" style="color: #666;">Vui lòng chọn lý do sản phẩm đã được bán:</p>
        <div class="list-group">
          <label class="list-group-item d-flex align-items-center" style="cursor: pointer; border: 1px solid #ddd; margin-bottom: 8px; border-radius: 8px;">
            <input type="radio" name="lyDoDaBan" value="Sản phẩm đã được bán tại Chợ Việt" class="mr-3" style="cursor: pointer;">
            <span>Sản phẩm đã được bán tại Chợ Việt</span>
          </label>
          <label class="list-group-item d-flex align-items-center" style="cursor: pointer; border: 1px solid #ddd; margin-bottom: 8px; border-radius: 8px;">
            <input type="radio" name="lyDoDaBan" value="Sản phẩm này tôi đã bán ở kênh khác" class="mr-3" style="cursor: pointer;">
            <span>Sản phẩm này tôi đã bán ở kênh khác</span>
          </label>
          <label class="list-group-item d-flex align-items-center" style="cursor: pointer; border: 1px solid #ddd; margin-bottom: 8px; border-radius: 8px;">
            <input type="radio" name="lyDoDaBan" value="Tôi không còn muốn bán sản phẩm này nữa" class="mr-3" style="cursor: pointer;">
            <span>Tôi không còn muốn bán sản phẩm này nữa</span>
          </label>
          <label class="list-group-item d-flex align-items-center" style="cursor: pointer; border: 1px solid #ddd; margin-bottom: 8px; border-radius: 8px;">
            <input type="radio" name="lyDoDaBan" value="Sản phẩm đã hết hàng hoặc không còn sẵn có" class="mr-3" style="cursor: pointer;">
            <span>Sản phẩm đã hết hàng hoặc không còn sẵn có</span>
          </label>
          <label class="list-group-item d-flex align-items-center" style="cursor: pointer; border: 1px solid #ddd; margin-bottom: 8px; border-radius: 8px;">
            <input type="radio" name="lyDoDaBan" value="Lý do khác" class="mr-3" style="cursor: pointer;">
            <span>Lý do khác</span>
          </label>
        </div>
      </div>
      <div class="modal-footer" style="border-top: 1px solid #ddd; padding-top: 15px;">
        <button type="button" class="btn btn-secondary" onclick="closeModalDaBan()">Hủy</button>
        <button type="button" id="btnXacNhanDaBan" class="btn btn-warning text-white font-weight-bold" onclick="xacNhanDaBan(event)">Xác nhận</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal chọn lý do ẩn sản phẩm -->
<div class="modal" id="modalAnSanPham" tabindex="-1" style="display:none; z-index:1100;">
  <div class="modal-dialog">
    <div class="modal-content p-4 rounded" style="background: white; width: 600px; margin: 80px auto; box-shadow: 0 4px 10px rgba(0,0,0,0.2); position: relative; left: -45px;">
      <div class="modal-header" style="padding-top: 0; padding-bottom: 10px;">
        <h5 class="modal-title font-weight-bold" style="color: #333;">Chọn lý do ẩn sản phẩm</h5>
        <button type="button" class="close" onclick="closeModalAnSanPham()" style="font-size: 24px; color: #555;">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="mb-3" style="color: #666;">Vui lòng chọn lý do bạn muốn ẩn sản phẩm này:</p>
        <div class="list-group">
          <label class="list-group-item d-flex align-items-center" style="cursor: pointer; border: 1px solid #ddd; margin-bottom: 8px; border-radius: 8px;">
            <input type="radio" name="lyDoAnSanPham" value="Tôi tạm thời không muốn bán sản phẩm này" class="mr-3" style="cursor: pointer;">
            <span>Tôi tạm thời không muốn bán sản phẩm này</span>
          </label>
          <label class="list-group-item d-flex align-items-center" style="cursor: pointer; border: 1px solid #ddd; margin-bottom: 8px; border-radius: 8px;">
            <input type="radio" name="lyDoAnSanPham" value="Sản phẩm cần được kiểm tra lại trước khi bán" class="mr-3" style="cursor: pointer;">
            <span>Sản phẩm cần được kiểm tra lại trước khi bán</span>
          </label>
          <label class="list-group-item d-flex align-items-center" style="cursor: pointer; border: 1px solid #ddd; margin-bottom: 8px; border-radius: 8px;">
            <input type="radio" name="lyDoAnSanPham" value="Tôi muốn chỉnh sửa thông tin sản phẩm" class="mr-3" style="cursor: pointer;">
            <span>Tôi muốn chỉnh sửa thông tin sản phẩm</span>
          </label>
          <label class="list-group-item d-flex align-items-center" style="cursor: pointer; border: 1px solid #ddd; margin-bottom: 8px; border-radius: 8px;">
            <input type="radio" name="lyDoAnSanPham" value="Sản phẩm không còn phù hợp để bán" class="mr-3" style="cursor: pointer;">
            <span>Sản phẩm không còn phù hợp để bán</span>
          </label>
          <label class="list-group-item d-flex align-items-center" style="cursor: pointer; border: 1px solid #ddd; margin-bottom: 8px; border-radius: 8px;">
            <input type="radio" name="lyDoAnSanPham" value="Lý do khác" class="mr-3" style="cursor: pointer;">
            <span>Lý do khác</span>
          </label>
        </div>
      </div>
      <div class="modal-footer" style="border-top: 1px solid #ddd; padding-top: 15px;">
        <button type="button" class="btn btn-secondary" onclick="closeModalAnSanPham()">Hủy</button>
        <button type="button" id="btnXacNhanAnSanPham" class="btn btn-warning text-white font-weight-bold" onclick="xacNhanAnSanPham(event)">Xác nhận</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal đẩy tin -->
<div class="modal" id="modalDayTin" tabindex="-1" style="display:none; z-index:1100;">
  <div class="modal-dialog">
    <div class="modal-content p-4 rounded" style="background: white; width: 500px; margin: 80px auto; box-shadow: 0 4px 10px rgba(0,0,0,0.2); position: relative; left: -45px;">
      <div class="modal-header" style="padding-top: 0; padding-bottom: 10px; border-bottom: 1px solid #ddd;">
        <h5 class="modal-title font-weight-bold" style="color: #333;">Xác nhận đẩy tin</h5>
        <button type="button" class="close" onclick="closeModalDayTin()" style="font-size: 24px; color: #555; background: none; border: none; cursor: pointer;">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="text-center mb-3">
          <i class="fas fa-bullhorn fa-3x text-warning mb-3"></i>
          <h5 class="font-weight-bold mb-3">Đẩy tin lên đầu danh sách</h5>
          <p class="text-muted mb-2">Bạn sẽ mất phí <strong class="text-danger">11.000đ</strong> để đẩy tin này lên đầu danh sách và tiếp cận nhiều người xem hơn.</p>
          <div class="alert alert-info mb-0" style="background-color: #e7f3ff; border: 1px solid #b3d9ff; color: #004085;">
            <i class="fas fa-info-circle mr-2"></i>
            <small>Sau khi đẩy tin, sản phẩm của bạn sẽ được chuyển về trạng thái "Chờ duyệt" và hiển thị ở đầu danh sách sau khi được duyệt.</small>
          </div>
        </div>
      </div>
      <div class="modal-footer" style="border-top: 1px solid #ddd; padding-top: 15px;">
        <button type="button" class="btn btn-secondary" onclick="closeModalDayTin()">Hủy</button>
        <button type="button" id="btnXacNhanDayTin" class="btn btn-warning text-white font-weight-bold" onclick="xacNhanDayTin(event)">Xác nhận đẩy tin</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal sửa tin -->

<div class="modal" id="suaTinModal" tabindex="-1" style="display:none; z-index:1100;">
  <div class="modal-dialog modal-lg">
    <div class="modal-content rounded" style="background: white; max-width: 900px; margin: 30px auto; box-shadow: 0 4px 10px rgba(0,0,0,0.2); position: relative; max-height: 90vh; display: flex; flex-direction: column;">
      <div class="modal-header" style="padding: 15px 20px; border-bottom: 1px solid #ddd; flex-shrink: 0;">
        <h4 class="modal-title font-weight-bold text-center w-100" id="modal-subtitle" style="margin: 0;">Sửa tin</h4>
        <button type="button" class="close" onclick="closeSuaTinModalNew()" style="position: absolute; top: 15px; right: 20px; font-size: 22px; color: #555; background: none; border: none; cursor: pointer;">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="modal-body" style="padding: 20px; overflow-y: auto; flex: 1;">
        <form id="form-sua-tin" action="index.php?action=suaTin&id=<?= $tin['id'] ?? '' ?>" method="post" enctype="multipart/form-data" data-userid="<?= $_SESSION['user_id'] ?? 0 ?>">
          <div class="row">
            <!-- Cột trái -->
            <div class="col-md-6">
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
                <textarea class="form-control" id="moTaSua" name="description" rows="4" placeholder="Mô tả chi tiết sản phẩm..." required><?= htmlspecialchars($tin['description'] ?? '') ?></textarea>
              </div>

              <!-- Loại sản phẩm -->
              <div class="form-group">
                <label class="font-weight-bold">
                  Loại sản phẩm <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($tenLoai) ?>" readonly>
              </div>
            </div>

            <!-- Cột phải -->
            <div class="col-md-6">
              <!-- Ảnh hiện tại -->
              <div class="form-group">
                <label class="font-weight-bold">Ảnh hiện tại</label>
                <div class="preview-anh-cu mb-2" id="previewAnhCu" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                  <?php if (!empty($tin['image'])): ?>
                    <?php 
                    $images = explode(',', $tin['image']);
                    foreach ($images as $index => $img): 
                      $img = trim($img);
                      if (empty($img)) continue;
                    ?>
                      <div class="d-inline-block position-relative mr-2 mb-2 anh-cu-item" style="position: relative; display: inline-block;" data-img-name="<?= htmlspecialchars($img) ?>">
                        <img src="img/<?= htmlspecialchars($img) ?>" width="80" height="80" style="object-fit: cover; border: 1px solid #ddd; border-radius: 4px;">
                        <button type="button" class="btn btn-sm btn-danger position-absolute" onclick="xoaAnhCu(this, '<?= htmlspecialchars($img) ?>'); return false;" style="top: -5px; right: -5px; width: 24px; height: 24px; padding: 0; border-radius: 50%; font-size: 12px; line-height: 1; z-index: 10; cursor: pointer;">
                          <i class="fas fa-times"></i>
                        </button>
                        <input type="hidden" name="images_to_keep[]" value="<?= htmlspecialchars($img) ?>" class="img-keep-input">
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
                <small class="form-text text-muted">Nhấn nút X để xóa ảnh. Tổng số ảnh (còn lại + mới) phải từ 2 đến 6 ảnh.</small>
              </div>

              <!-- Chọn ảnh mới -->
              <div class="form-group">
                <label for="hinhAnhSua" class="font-weight-bold">
                  Thêm ảnh mới (tùy chọn)
                </label>
                <input type="file" class="form-control-file" id="hinhAnhSua" name="image[]" accept=".jpg,.jpeg,.png" multiple>
                <small class="form-text text-muted mt-2">Chọn thêm ảnh mới (định dạng .jpg, .png). Tổng số ảnh phải từ 2 đến 6 ảnh.</small>
                <div id="previewAnhMoi" class="mt-2" style="max-height: 150px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;"></div>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer" style="padding: 15px 20px; border-top: 1px solid #ddd; flex-shrink: 0;">
        <button type="button" class="btn btn-secondary" onclick="closeSuaTinModalNew()">Hủy</button>
        <button type="submit" form="form-sua-tin" class="btn btn-warning font-weight-bold text-white" style="font-size: 16px;">Lưu thay đổi</button>
      </div>
    </div>
  </div>
</div>

    </div>
    <!-- Content Wrapper End -->
</div>
<!-- Page Background End -->

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
let currentDayTinId = null;

function showModalDayTin(productId) {
  currentDayTinId = productId;
  document.getElementById('modalOverlay').style.display = 'block';
  document.getElementById('modalDayTin').style.display = 'block';
}

function closeModalDayTin() {
  document.getElementById('modalOverlay').style.display = 'none';
  document.getElementById('modalDayTin').style.display = 'none';
  currentDayTinId = null;
}

function xacNhanDayTin(event) {
  if (!currentDayTinId) {
    alert('Lỗi: Không tìm thấy ID sản phẩm!');
    return;
  }
  
  // Disable button để tránh double submit
  const confirmBtn = event ? event.target : document.getElementById('btnXacNhanDayTin');
  if (confirmBtn) {
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Đang xử lý...';
  }
  
  // Chuyển hướng đến trang đẩy tin
  window.location.href = 'index.php?quan-ly-tin&daytin=' + currentDayTinId;
}

// Đóng modal đẩy tin khi click vào overlay
document.addEventListener('DOMContentLoaded', function() {
  const overlay = document.getElementById('modalOverlay');
  const modalDayTin = document.getElementById('modalDayTin');
  
  if (overlay && modalDayTin) {
    // Kiểm tra nếu đã có event listener cho modalDayTin chưa
    const existingListener = overlay.getAttribute('data-daytin-listener');
    if (!existingListener) {
      overlay.setAttribute('data-daytin-listener', 'true');
      // Sử dụng event delegation để xử lý tất cả modals
      overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
          if (modalDayTin && modalDayTin.style.display === 'block') {
            closeModalDayTin();
          }
        }
      });
    }
  }
});
</script>

<script>
let currentProductId = null;

function showModalDaBan(productId) {
  currentProductId = productId;
  document.getElementById('modalOverlay').style.display = 'block';
  document.getElementById('modalDaBan').style.display = 'block';
  // Reset radio buttons
  document.querySelectorAll('input[name="lyDoDaBan"]').forEach(radio => {
    radio.checked = false;
  });
}

function closeModalDaBan() {
  document.getElementById('modalOverlay').style.display = 'none';
  document.getElementById('modalDaBan').style.display = 'none';
  currentProductId = null;
}

// Functions cho modal Ẩn sản phẩm
function showModalAnSanPham(productId) {
  currentProductId = productId;
  document.getElementById('modalOverlay').style.display = 'block';
  document.getElementById('modalAnSanPham').style.display = 'block';
  // Reset radio buttons
  document.querySelectorAll('input[name="lyDoAnSanPham"]').forEach(radio => {
    radio.checked = false;
  });
}

function closeModalAnSanPham() {
  document.getElementById('modalOverlay').style.display = 'none';
  document.getElementById('modalAnSanPham').style.display = 'none';
  currentProductId = null;
}

// Đóng modal khi click vào overlay
document.addEventListener('DOMContentLoaded', function() {
  const overlay = document.getElementById('modalOverlay');
  const modalDaBan = document.getElementById('modalDaBan');
  const modalAnSanPham = document.getElementById('modalAnSanPham');
  
  if (overlay && modalDaBan) {
    overlay.addEventListener('click', function(e) {
      if (e.target === overlay && modalDaBan.style.display === 'block') {
        closeModalDaBan();
      }
    });
  }
  
  if (overlay && modalAnSanPham) {
    overlay.addEventListener('click', function(e) {
      if (e.target === overlay && modalAnSanPham.style.display === 'block') {
        closeModalAnSanPham();
      }
    });
  }
});

function xacNhanDaBan(event) {
  const selectedReason = document.querySelector('input[name="lyDoDaBan"]:checked');
  if (!selectedReason) {
    alert('Vui lòng chọn lý do đã bán!');
    return;
  }
  
  const note = selectedReason.value;
  if (!currentProductId) {
    alert('Lỗi: Không tìm thấy ID sản phẩm!');
    return;
  }
  
  // Disable button để tránh double submit
  const confirmBtn = event ? event.target : document.getElementById('btnXacNhanDaBan');
  if (confirmBtn) {
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Đang xử lý...';
  }
  
  fetch('index.php?action=capNhatTrangThai', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `id=${currentProductId}&loai=Đã bán&note=${encodeURIComponent(note)}`
  })
  .then(res => {
    if (!res.ok) {
      throw new Error('Network response was not ok');
    }
    return res.text(); // Đọc text trước để debug
  })
  .then(text => {
    console.log('Response:', text); // Debug
    try {
      const data = JSON.parse(text);
      if (data.status === 'success') {
        closeModalDaBan();
        window.location.href = "index.php?quan-ly-tin&toast=" + encodeURIComponent("Cập nhật thành công!") + "&type=success";
      } else {
        showToast(data.message || "Cập nhật không thành công!", "error");
        if (confirmBtn) {
          confirmBtn.disabled = false;
          confirmBtn.textContent = 'Xác nhận';
        }
      }
    } catch (e) {
      console.error('JSON parse error:', e, 'Response text:', text);
      showToast("Có lỗi xảy ra khi xử lý phản hồi từ server!", "error");
      if (confirmBtn) {
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Xác nhận';
      }
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showToast("Có lỗi xảy ra khi cập nhật: " + error.message, "error");
    if (confirmBtn) {
      confirmBtn.disabled = false;
      confirmBtn.textContent = 'Xác nhận';
    }
  });
}

function xacNhanAnSanPham(event) {
  const selectedReason = document.querySelector('input[name="lyDoAnSanPham"]:checked');
  if (!selectedReason) {
    alert('Vui lòng chọn lý do ẩn sản phẩm!');
    return;
  }
  
  const note = selectedReason.value;
  if (!currentProductId) {
    alert('Lỗi: Không tìm thấy ID sản phẩm!');
    return;
  }
  
  // Disable button để tránh double submit
  const confirmBtn = event ? event.target : document.getElementById('btnXacNhanAnSanPham');
  if (confirmBtn) {
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Đang xử lý...';
  }
  
  fetch('index.php?action=capNhatTrangThai', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `id=${currentProductId}&loai=Đã ẩn&note=${encodeURIComponent(note)}`
  })
  .then(res => {
    if (!res.ok) {
      throw new Error('Network response was not ok');
    }
    return res.text();
  })
  .then(text => {
    console.log('Response:', text);
    try {
      const data = JSON.parse(text);
      if (data.status === 'success') {
        closeModalAnSanPham();
        window.location.href = "index.php?quan-ly-tin&toast=" + encodeURIComponent("Cập nhật thành công!") + "&type=success";
      } else {
        showToast(data.message || "Cập nhật không thành công!", "error");
        if (confirmBtn) {
          confirmBtn.disabled = false;
          confirmBtn.textContent = 'Xác nhận';
        }
      }
    } catch (e) {
      console.error('JSON parse error:', e, 'Response text:', text);
      showToast("Có lỗi xảy ra khi xử lý phản hồi từ server!", "error");
      if (confirmBtn) {
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Xác nhận';
      }
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showToast("Có lỗi xảy ra khi cập nhật: " + error.message, "error");
    if (confirmBtn) {
      confirmBtn.disabled = false;
      confirmBtn.textContent = 'Xác nhận';
    }
  });
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

// Xóa ảnh cũ
function xoaAnhCu(button, imgName) {
  // Ngăn chặn event bubbling
  if (event) {
    event.preventDefault();
    event.stopPropagation();
  }
  
  if (!confirm('Bạn có chắc muốn xóa ảnh này?')) {
    return false;
  }
  
  // Tìm container chứa ảnh
  let container = button.parentElement;
  if (!container || !container.classList.contains('anh-cu-item')) {
    container = button.closest('.anh-cu-item');
  }
  
  if (!container) {
    console.error('Không tìm thấy container ảnh');
    return false;
  }
  
  // Tìm input hidden
  const keepInput = container.querySelector('.img-keep-input');
  
  // Xóa input hidden để không gửi lên server
  if (keepInput) {
    keepInput.remove();
  }
  
  // Ẩn container với animation
  container.style.transition = 'opacity 0.3s';
  container.style.opacity = '0';
  
  setTimeout(function() {
    container.style.display = 'none';
    // Kiểm tra số lượng ảnh còn lại
    kiemTraSoLuongAnh();
  }, 300);
  
  return false;
}

// Kiểm tra số lượng ảnh
function kiemTraSoLuongAnh() {
  const anhCuConLai = document.querySelectorAll('.img-keep-input').length;
  const anhMoiInput = document.getElementById('hinhAnhSua');
  const anhMoiCount = anhMoiInput ? anhMoiInput.files.length : 0;
  const tongAnh = anhCuConLai + anhMoiCount;
  
  // Hiển thị thông báo
  const previewAnhCu = document.getElementById('previewAnhCu');
  let thongBao = previewAnhCu.querySelector('.thong-bao-anh');
  if (!thongBao) {
    thongBao = document.createElement('small');
    thongBao.className = 'thong-bao-anh form-text text-muted mt-1';
    previewAnhCu.appendChild(thongBao);
  }
  
  if (tongAnh < 2) {
    thongBao.textContent = `Cảnh báo: Bạn cần ít nhất 2 ảnh. Hiện tại: ${tongAnh} ảnh (${anhCuConLai} ảnh cũ + ${anhMoiCount} ảnh mới)`;
    thongBao.className = 'thong-bao-anh form-text text-danger mt-1';
  } else if (tongAnh > 6) {
    thongBao.textContent = `Cảnh báo: Tối đa 6 ảnh. Hiện tại: ${tongAnh} ảnh (${anhCuConLai} ảnh cũ + ${anhMoiCount} ảnh mới)`;
    thongBao.className = 'thong-bao-anh form-text text-warning mt-1';
  } else {
    thongBao.textContent = `Tổng số ảnh: ${tongAnh} (${anhCuConLai} ảnh cũ + ${anhMoiCount} ảnh mới)`;
    thongBao.className = 'thong-bao-anh form-text text-success mt-1';
  }
}

// Preview ảnh mới
document.addEventListener('DOMContentLoaded', function() {
  const hinhAnhSua = document.getElementById('hinhAnhSua');
  const previewAnhMoi = document.getElementById('previewAnhMoi');
  
  if (hinhAnhSua && previewAnhMoi) {
    hinhAnhSua.addEventListener('change', function(e) {
      previewAnhMoi.innerHTML = '';
      const files = e.target.files;
      
      if (files.length > 0) {
        const previewTitle = document.createElement('p');
        previewTitle.className = 'font-weight-bold mb-2';
        previewTitle.textContent = 'Ảnh mới đã chọn:';
        previewAnhMoi.appendChild(previewTitle);
        
        Array.from(files).forEach((file, index) => {
          if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
              const imgContainer = document.createElement('div');
              imgContainer.className = 'd-inline-block mr-2 mb-2';
              imgContainer.style.cssText = 'position: relative; display: inline-block;';
              
              const img = document.createElement('img');
              img.src = e.target.result;
              img.width = 80;
              img.height = 80;
              img.style.cssText = 'object-fit: cover; border: 1px solid #28a745; border-radius: 4px;';
              
              imgContainer.appendChild(img);
              previewAnhMoi.appendChild(imgContainer);
            };
            reader.readAsDataURL(file);
          }
        });
      }
      
      kiemTraSoLuongAnh();
    });
  }
  
  // Kiểm tra số lượng ảnh khi load trang
  kiemTraSoLuongAnh();
  
  // Validate form trước khi submit
  const formSuaTin = document.getElementById('form-sua-tin');
  if (formSuaTin) {
    formSuaTin.addEventListener('submit', function(e) {
      const anhCuConLai = document.querySelectorAll('.img-keep-input').length;
      const anhMoiInput = document.getElementById('hinhAnhSua');
      const anhMoiCount = anhMoiInput ? anhMoiInput.files.length : 0;
      const tongAnh = anhCuConLai + anhMoiCount;
      
      if (tongAnh < 2) {
        e.preventDefault();
        alert('Vui lòng giữ lại ít nhất 2 ảnh hoặc thêm ảnh mới để có tổng cộng ít nhất 2 ảnh!');
        return false;
      }
      
      if (tongAnh > 6) {
        e.preventDefault();
        alert('Tổng số ảnh không được vượt quá 6 ảnh!');
        return false;
      }
    });
  }
});
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


