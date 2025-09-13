<?php
ob_start(); // Bắt đầu bộ đệm để tránh lỗi headers
error_reporting(0);
if ($_SESSION['role'] != 1) {
    echo "<script>
        alert('Bạn không đủ thẩm quyền truy cập!');
        
    </script>";
    require_once '../helpers/url_helper.php';
    header("refresh: 0; url='" . getBaseUrl() . "/index.php?login'");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Admin</title>
  <!-- plugins:css -->
  <?php require_once '../helpers/url_helper.php'; ?>
  <link rel="stylesheet" href="<?= getBasePath() ?>/admin/src/assets/vendors/mdi/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="<?= getBasePath() ?>/admin/src/assets/vendors/css/vendor.bundle.base.css">
  <!-- endinject -->
  <!-- plugin css for this page -->
  <link rel="stylesheet" href="<?= getBasePath() ?>/admin/src/assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <!-- End plugin css for this page -->
  <!-- inject:css -->
  <link rel="stylesheet" href="<?= getBasePath() ?>/admin/src/assets/css/style.css">
  <!-- endinject -->
  <link rel="shortcut icon" href="<?= getBasePath() ?>/admin/src/assets/images/favicon.ico" />
  
</head>
<body>
  <div class="container-scroller">
    <!-- partial:partials/_navbar.html -->  
    <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
      <div class="navbar-brand-wrapper d-flex justify-content-center">
        <div class="navbar-brand-inner-wrapper d-flex justify-content-between align-items-center w-100">
          <b>Admin</b>
        </div>
      </div>
      <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
        <ul class="navbar-nav me-lg-4 w-100">
          <li class="nav-item nav-search d-none d-lg-block w-100">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text" id="search">
                  <i class="mdi mdi-magnify"></i>
                </span>
              </div>
              <input type="text" class="form-control" placeholder="Search now" aria-label="search"
                aria-describedby="search">
            </div>
          </li>
        </ul>
        <ul class="navbar-nav navbar-nav-right">
          
          <li class="nav-item nav-profile dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" id="profileDropdown">
              <img src="<?= getBasePath() ?>/img/<?php echo $_SESSION['avatar']; ?>" alt="profile" />
              <span class="nav-profile-name"><?php echo $_SESSION['user_name']; ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
              <a class="dropdown-item">
                <i class="mdi mdi-cog text-primary"></i>
                Settings
              </a>
              <a class="dropdown-item" href="<?= getBasePath() ?>?action=logout">
                <i class="mdi mdi-logout text-primary"></i>
                Đăng xuất
              </a>
            </div>
          </li>
          <li class="nav-item nav-settings d-none d-lg-flex">
            <a class="nav-link" href="#">
              <i class="mdi mdi-apps"></i>
            </a>
          </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
          data-toggle="offcanvas">
          <span class="mdi mdi-menu"></span>
        </button>
      </div>
    </nav>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">      
      <!-- partial:partials/_sidebar.html -->
      <nav class="sidebar sidebar-offcanvas" id="sidebar">
        <ul class="nav">
          <li class="nav-item">
            <a class="nav-link" href="<?= getBasePath() ?>/ad">
              <i class="mdi mdi-account-circle menu-icon" style="color: black;"></i>
              Thông tin cá nhân
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= getBasePath() ?>/ad/taikhoan" aria-expanded="false" aria-controls="auth">
              <i class="mdi mdi-account-multiple menu-icon" style="color: black;"></i>
              Quản lý tài khoản
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= getBasePath() ?>/ad/qldoanhthu" aria-expanded="false" aria-controls="form-elements">
              <i class="mdi mdi-cash-multiple menu-icon" style="color: black;"></i>
              Quản lý doanh thu
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= getBasePath() ?>/ad/loaisanpham" aria-expanded="false" aria-controls="form-elements">
              <i class="mdi mdi-format-list-bulleted menu-icon" style="color: black;"></i>
              Quản lý danh mục
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= getBasePath() ?>/ad/kdbaidang" aria-expanded="false" aria-controls="ui-basic">
              <i class="mdi mdi-checkbox-marked-outline menu-icon" style="color: black;"></i>
              Kiểm duyệt bài đăng
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= getBasePath() ?>/ad/kdnaptien" aria-expanded="false" aria-controls="auth">
              <i class="mdi mdi-bank-transfer menu-icon" style="color: black;"></i>
              Kiểm duyệt nạp tiền
            </a>
          </li>
        </ul>
      </nav>

      <!-- partial -->
      <div class="main-panel">
        <div class="content-wrapper">
          <?php
            if(isset($_GET["taikhoan"])){
              if(isset($_GET["ids"])){
                include_once("view/info-update.php");
              }else if(isset($_GET["them"])){
                include_once("view/info-insert.php");
              }else
                include_once("view/user-table.php");
            }else if(isset($_GET["kdbaidang"])){
              if(isset($_GET["id"])){
                include_once("view/kdbaidang-detail.php");
              }else
                include_once("view/kdbaidang-table.php");
            }else if(isset($_GET["qldoanhthu"])){
              include_once("view/qldoanhthu.php");
            }else if (isset($_GET['loaisanpham'])) {
                include("loaisanpham-table.php");
            }else if(isset($_GET["kdnaptien"])){
              include_once("view/duyetnaptien.php");
            }else{
              include_once("view/info-admin.php");
            }
          ?>
        </div>
        <!-- content-wrapper ends -->
        <!-- partial:partials/_footer.html -->
        <footer class="footer">
          <div class="d-sm-flex justify-content-center justify-content-sm-between">
            <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © 2024 <a
                href="https://www.bootstrapdash.com/" target="_blank">Bootstrapdash</a>. All rights reserved.</span>
            <span class="float-none float-sm-end d-block mt-1 mt-sm-0 text-center">Hand-crafted & made with <i
                class="mdi mdi-heart text-danger"></i></span>
          </div>
        </footer>
        <!-- partial -->
      </div>
      <!-- main-panel ends -->
    </div>
    <!-- page-body-wrapper ends -->
  </div>
  <!-- container-scroller -->

  <!-- plugins:js -->
  <script src="<?= getBasePath() ?>/admin/src/assets/vendors/js/vendor.bundle.base.js"></script>
  <!-- endinject -->
  <!-- Plugin js for this page-->
  <script src="<?= getBasePath() ?>/admin/src/assets/vendors/chart.js/chart.umd.js"></script>
  <script src="<?= getBasePath() ?>/admin/src/assets/vendors/datatables.net/jquery.dataTables.js"></script>
  <script src="<?= getBasePath() ?>/admin/src/assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>
  <!-- End plugin js for this page-->
  <!-- inject:js -->
  <script src="<?= getBasePath() ?>/admin/src/assets/js/off-canvas.js"></script>
  <script src="<?= getBasePath() ?>/admin/src/assets/js/hoverable-collapse.js"></script>
  <script src="<?= getBasePath() ?>/admin/src/assets/js/template.js"></script>
  <script src="<?= getBasePath() ?>/admin/src/assets/js/settings.js"></script>
  <script src="<?= getBasePath() ?>/admin/src/assets/js/todolist.js"></script>
  <!-- endinject -->
  <!-- Custom js for this page-->
  <script src="<?= getBasePath() ?>/admin/src/assets/js/dashboard.js"></script>
    <script src="<?= getBasePath() ?>/admin/src/assets/js/proBanner.js"></script>

  <!-- End custom js for this page-->
  <script src="<?= getBasePath() ?>/admin/src/assets/js/jquery.cookie.js" type="text/javascript"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php ob_end_flush(); // Kết thúc bộ đệm ?>