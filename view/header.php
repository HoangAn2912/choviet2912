<?php
error_reporting(0);
include_once "controller/cCategory.php";

$cCategory = new cCategory();
$data = $cCategory->index();

$userHeader = null;
if (isset($_SESSION['user_id'])) {
    $userHeader = $cCategory->getUserById($_SESSION['user_id']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Chợ Việt - Nơi trao đổi hàng hóa</title>
    <link rel="icon" href="img/choviet-favicon.ico" type="icon">

    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Free HTML Templates" name="keywords">
    <meta content="Free HTML Templates" name="description">

    <!-- Favicon -->
    <!-- <link href="img/favicon.ico" rel="icon"> -->

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">  

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/profile.css" rel="stylesheet">
    <link href="css/managePost.css" rel="stylesheet">
    <link rel="stylesheet" href="css/chat.css">
    <!-- Toastify CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <!-- jQuery (nếu dùng Bootstrap 4) -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Toastify JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

</head>

<body>
    <!-- Topbar Start -->
    <div class="container-fluid">
        <div class="row align-items-center bg-light py-3 px-xl-5 d-none d-lg-flex">
            <div class="col-lg-4">
                <a href="index.php" class="text-decoration-none">
                    <span class="h1 text-uppercase text-white px-2" style="background-color: #3D464D;">Chợ</span>
                    <span class="h1 text-uppercase text-dark bg-primary px-2 ml-n1">Việt</span>
                </a>
            </div>
            <div class="col-lg-4 col-6 text-left">
                
            </div>
            <div class="col-lg-4 col-6 text-right">
                <p class="m-0">Hotline hỗ trợ:</p>
                <h5 class="m-0">+84-934-8383-66</h5>
            </div>
        </div>
    </div>
    <!-- Topbar End -->


    <!-- Navbar Start -->
    <div class="container-fluid bg-dark mb-30">
        <div class="row px-xl-5">
            <div class="col-lg-3 d-none d-lg-block">
                <a class="btn d-flex align-items-center justify-content-between bg-primary w-100" data-toggle="collapse" href="#navbar-vertical" style="height: 65px; padding: 0 30px; border-radius: 0;">
                    <h6 class="text-dark m-0"><i class="fa fa-bars mr-2"></i>Danh mục</h6>
                    <i class="fa fa-angle-down text-dark"></i>
                </a>
                <nav class="collapse position-absolute navbar navbar-vertical navbar-light align-items-start p-0 bg-light" id="navbar-vertical" style="width: calc(100% - 30px); z-index: 999;">
                <div class="navbar-nav w-100">
                    <?php if (!empty($data)) : ?>
                        <?php foreach ($data as $parent) : ?>
                            <?php if (!empty($parent['con'])) : ?>
                                <!-- Có danh mục con -->
                                <div class="nav-item dropdown dropright">
                                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                                        <?= htmlspecialchars($parent['ten_cha']) ?>
                                        <i class="fa fa-angle-right float-right mt-1"></i>
                                    </a>
                                    <div class="dropdown-menu position-absolute rounded-0 border-0 m-0">
                                        <?php foreach ($parent['con'] as $child) : ?>
                                            <a href="index.php?category=<?= $child['id_con'] ?>" class="dropdown-item">
                                                <?= htmlspecialchars($child['ten_con']) ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else : ?>
                                <!-- Không có danh mục con -->
                                <a href="category.php?id=<?= $parent['id_cha'] ?>" class="nav-item nav-link">
                                    <?= htmlspecialchars($parent['ten_cha']) ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                </nav>
            </div>
            <div class="col-lg-9">
                <nav class="navbar navbar-expand-lg bg-dark navbar-dark py-3 py-lg-0 px-0">
                    <a href="" class="text-decoration-none d-block d-lg-none">
                        <span class="h1 text-uppercase text-dark bg-light px-2">Chợ</span>
                        <span class="h1 text-uppercase text-light bg-primary px-2 ml-n1">Việt</span>
                    </a>
                    <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
                        <!-- Thanh tìm kiếm và nút đăng tin -->
                        <div class="d-flex align-items-center" style="flex-grow: 1; gap: 15px;">
                        <form action="index.php" method="get" class="flex-grow-1">
                            <input type="hidden" name="search" value="1">
                            <div class="input-group w-100">
                                <input type="text" name="keyword" class="form-control" placeholder="Tìm kiếm sản phẩm..." required>
                                <div class="input-group-append">
                                    <button class="input-group-text bg-transparent text-white" type="submit" style="background-color: #3D464D !important;">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>


                            <!-- Nút Đăng Tin -->
                            <a href="javascript:void(0);" class="btn btn-warning btn-dang-tin text-white d-flex align-items-center px-3 py-2" style="font-weight: 600;">
                                <i class="fas fa-edit mr-2"></i> ĐĂNG TIN
                            </a>

                        </div>

                        <!-- Menu navbar -->
                        <div class="navbar-nav d-flex align-items-center ml-4" style="gap: 20px;">
                            <a href="index.php?tin-nhan" class="nav-item nav-link d-flex align-items-center position-relative">
                                <i class="fas fa-envelope mr-2"></i> Tin nhắn
                                <?php if ($hasUnread): ?>
                                    <span class="position-absolute" style="top: 2px; right: -10px; width: 10px; height: 10px; background: red; border-radius: 50%;"></span>
                                <?php endif; ?>
                            </a>

                            <a href="?quan-ly-tin" class="nav-item nav-link d-flex align-items-center">
                                <i class="fas fa-tasks mr-2"></i> Quản lý tin
                            </a>

                            <a href="?nap-tien" class="nav-item nav-link d-flex align-items-center">
                                <i class="fas fa-coins mr-2"></i> Nạp tiền
                            </a>

                            <?php if (isset($_SESSION['user_id'])): ?>
                            <!-- Dropdown Đơn hàng -->
                            <div class="dropdown">
                                <a href="#" class="nav-item nav-link d-flex align-items-center dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-shopping-bag mr-2"></i> Đơn hàng
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="?my-orders">
                                        <i class="fas fa-list mr-2"></i>Tất cả đơn hàng
                                    </a>
                                    <a class="dropdown-item" href="?my-orders&status=pending">
                                        <i class="fas fa-clock mr-2"></i>Chờ xác nhận
                                    </a>
                                    <a class="dropdown-item" href="?my-orders&status=confirmed">
                                        <i class="fas fa-check mr-2"></i>Đã xác nhận
                                    </a>
                                    <a class="dropdown-item" href="?my-orders&status=shipping">
                                        <i class="fas fa-truck mr-2"></i>Đang giao hàng
                                    </a>
                                    <a class="dropdown-item" href="?my-orders&status=delivered">
                                        <i class="fas fa-check-circle mr-2"></i>Đã giao hàng
                                    </a>
                                    <a class="dropdown-item" href="?my-orders&status=cancelled">
                                        <i class="fas fa-times mr-2"></i>Đã hủy
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Dropdown Live Stream -->
                            <div class="dropdown">
                                <a href="#" class="nav-item nav-link d-flex align-items-center dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-video mr-2"></i> Live Stream
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="?livestream">
                                        <i class="fas fa-list mr-2"></i>Tất cả Live Stream
                                    </a>
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <a class="dropdown-item" href="?create-livestream">
                                            <i class="fas fa-plus mr-2"></i>Tạo Livestream
                                        </a>
                                        <a class="dropdown-item" href="?my-livestreams">
                                            <i class="fas fa-user mr-2"></i>Livestream của tôi
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                        </div>

                        <!-- Avatar tài khoản -->
                        <div class="navbar-nav d-flex align-items-center ml-4" style="gap: 20px;">
                            <div class="btn-group">
                                <button type="button" class="btn px-0 dropdown-toggle d-flex align-items-center"
                                        style="gap: 4px; line-height: 1; font-size: 18px; font-weight: 400; color: white; background: none; border: none;"
                                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <?php if ($userHeader): ?>
                                    <?php
                                    $avatarPath = 'img/';
                                    $avatarFile = 'default-avatar.jpg';
                                    if (!empty($userHeader['avatar']) && file_exists($avatarPath . $userHeader['avatar'])) {
    $avatarFile = $userHeader['avatar'];
}
                                    ?>
                                    <img src="<?= $avatarPath . htmlspecialchars($avatarFile) ?>" class="rounded-circle mr-2" style="width: 32px; height: 32px; object-fit: cover;">
                                    <span style="color: white; font-weight: 400;">
                                        <?= strlen($userHeader['username']) > 10 ? substr(htmlspecialchars($userHeader['username']), 0, 10) . '...' : htmlspecialchars($userHeader['username']) ?>
                                    </span>
                                <?php else: ?>

                                    <i class="fas fa-user text-primary mr-1"></i> 
                                    <span style="color: white;">Tài khoản</span>
                                <?php endif; ?>

                                </button>
                                <div class="dropdown-menu dropdown-menu-right"> 
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <a class="dropdown-item" href="<?= urlencode($userHeader['username'] ?? '') ?>">Quản lý thông tin</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="?action=logout">Đăng xuất</a>
                                    <?php else: ?>
                                        <a class="dropdown-item" href="?login">Đăng nhập</a>
                                        <a class="dropdown-item" href="?signup">Đăng ký</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>
    <!-- Navbar End -->

<!-- Modal Đăng Tin -->
<div id="dangTinModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); overflow-y: auto; z-index: 1050;">
  <div class="modal-content p-4 rounded" style="background: white; width: 600px; margin: 80px auto; box-shadow: 0 4px 10px rgba(0,0,0,0.2); position: relative;">

    <!-- Nút Close (Góc phải) -->
    <button id="closeBtn" class="btn btn-link p-0" style="position: absolute; top: 10px; right: 16px; font-size: 22px; color: #555;">
      <i class="fas fa-times"></i>
    </button>

    <!-- Header -->
    <div class="d-flex align-items-center justify-content-center mb-3 position-relative" style="border-bottom: 1px solid #ddd; padding-bottom: 10px;">
      <button id="backBtn" class="btn btn-link p-0" style="position: absolute; left: 0; font-size: 20px; color: #333; display: none;">
        <i class="fas fa-arrow-left"></i>
      </button>
      <h4 class="font-weight-bold m-0 text-center" style="color: #333;">Đăng tin</h4>
    </div>

    <!-- Content -->
    <div class="mb-3">
      <h5 id="modal-subtitle" class="font-weight-bold mb-3" style="color: #555;">Chọn danh mục</h5>
      <ul id="danh-muc-cha-list" class="list-group">
        <?php
        include_once "controller/cCategory.php";
        $m = new cCategory();
        $danhmuc = $m->index();

        $icons = [
          'Xe cộ' => 'fa-car',
          'Đồ điện tử' => 'fa-bolt',
          'Thời trang' => 'fa-tshirt',
          'Nội thất' => 'fa-couch',
          'Giải trí' => 'fa-gamepad',
          'Khác' => 'fa-ellipsis-h',
          'Nhạc cụ' => 'fa-music',
          'Thể thao' => 'fa-basketball-ball',
          'Bất động sản' => 'fa-home',
          'Việc làm' => 'fa-briefcase',
          'Dịch vụ' => 'fa-concierge-bell'
        ];

        foreach ($danhmuc as $id_cha => $parent):
          $iconClass = isset($icons[$parent['ten_cha']]) ? $icons[$parent['ten_cha']] : 'fa-folder';
        ?>
          <li class="list-group-item list-group-item-action d-flex align-items-center justify-content-between" data-id="<?= $id_cha ?>" style="cursor: pointer;">
            <div class="d-flex align-items-center" style="gap: 10px;">
              <i class="fas <?= $iconClass ?>" style="color: #3D464D;"></i>
              <span style="font-weight: 500; color: #333;">
                <?= htmlspecialchars($parent['ten_cha']) ?>
              </span>
            </div>
            <i class="fas fa-chevron-right" style="color: #888;"></i>
          </li>
        <?php endforeach; ?>
      </ul>

        <!-- Form Đăng Tin -->
        <div id="form-dang-tin" style="display: none;">
        <form id="submitForm" action="index.php?action=dangTin" method="POST" enctype="multipart/form-data" data-userid="<?= $_SESSION['user_id'] ?? 0 ?>"
        >
                        <input type="hidden" id="idLoaiSanPham" name="category_id" required>

            <!-- Tiêu đề tin đăng -->
            <div class="form-group">
            <label for="tieuDe" class="font-weight-bold">
                Tiêu đề bài đăng <span class="text-danger">*</span>
            </label>
                            <input type="text" class="form-control" id="tieuDe" name="title" placeholder="Nhập tên sản phẩm cần bán" required>
            </div>

            <!-- Giá bán -->
            <div class="form-group">
            <label for="giaBan" class="font-weight-bold">
                Giá bán (đ) <span class="text-danger">*</span>
            </label>
                            <input type="number" class="form-control" id="giaBan" name="price" placeholder="Nhập số tiền cần bán" required>
            </div>

            <!-- Mô tả chi tiết -->
            <div class="form-group">
            <label for="moTa" class="font-weight-bold">
                Mô tả chi tiết <span class="text-danger">*</span>
            </label>
                            <textarea class="form-control" id="moTa" name="description" rows="5" placeholder="Mô tả chi tiết sản phẩm..." required></textarea>
            </div>

            <!-- Hình ảnh sản phẩm -->
            <div class="form-group">
            <label for="hinhAnh" class="font-weight-bold">
                Hình ảnh sản phẩm <span class="text-danger">*</span>
            </label>
                            <input type="file" class="form-control-file" id="hinhAnh" name="image[]" accept=".jpg,.jpeg,.png" multiple required>
            <small class="form-text text-muted mt-2">Chọn từ 2 đến 6 hình ảnh (định dạng .jpg, .png).</small>
            </div>

            <!-- Nút Đăng tin -->
            <button type="submit" class="btn btn-warning w-100 font-weight-bold text-white" style="font-size: 16px;">Đăng tin</button>

        </form>
        </div>
    </div>

  </div>
</div>

<?php include_once("js/dangtin.php"); ?>

<!-- File toast dùng chung -->
<script src="js/toast.js"></script>

<!-- Gọi nếu có -->
<?php include_once("toastify.php"); ?>


