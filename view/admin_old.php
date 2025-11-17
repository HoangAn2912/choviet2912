<?php
include_once("model/mConnect.php");
$con = new connect();
$mysqli = $con->connect();
?>

<?php
// ob_start(); // Bắt đầu bộ đệm để tránh lỗi headers
// // error_reporting(0);
// if (!in_array((int)($_SESSION['role'] ?? 0), [1, 4, 5], true)) {
//     echo "<script>
//         alert('Bạn không đủ thẩm quyền truy cập!');
        
//     </script>";
//     require_once 'helpers/url_helper.php';
//     header("refresh: 0; url='" . getBaseUrl() . "/index.php?login'");
//     exit;
// }
?>




<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Banner - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <link href="css/admin.css" rel="stylesheet">
    <link href="../css/admin.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
                <a class="navbar-brand" href="admin.php">
                    <i class="fas fa-cogs"></i>
                    <span>Chợ Việt</span>
                </a>
                
                <div class="navbar-nav ms-auto d-flex align-items-center" style="gap: 10px;">
                    <a class="nav-link" href="/index.php">
                        <i class="fas fa-home"></i> Xem trang chủ
                    </a>
                    <button id="themeToggle" class="nav-link theme-toggle-btn-nav" style="background: none; border: none; cursor: pointer;">
                        <i class="fas fa-sun" id="themeIcon"></i> Đổi theme
                    </button>
                    <a class="nav-link" href="/index.php?action=logout" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất?');">
                        <i class="fas fa-sign-out-alt"></i> Đăng xuất
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="sidebar-content">
                    <h5 class="sidebar-title">
                        <i class="fas fa-user"></i>
                        Admin Panel
                    </h5>
                    
                    <?php
                    if($_SESSION['role'] == 1){
                        echo '<ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/ad">
                                <i class="fas fa-plus"></i> Thông tin cá nhân
                            </a>
                        </li>
                    </ul>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/ad/taikhoan">
                                <i class="fas fa-plus"></i> Quản lý tài khoản
                            </a>
                        </li>
                    </ul>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/ad/qldoanhthu">
                                <i class="fas fa-plus"></i> Quản lý doanh thu
                            </a>
                        </li>
                    </ul>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/ad/loaisanpham">
                                <i class="fas fa-plus"></i> Quản lý danh mục
                            </a>
                        </li>
                    </ul>';
                    }
                    if($_SESSION['role'] == 4 || $_SESSION['role'] == 1){
                        echo '<ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link" href="/ad/edit-banner">
                                    <i class="fas fa-list"></i> Quản lý Banner
                                </a>
                            </li>
                        </ul>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link" href="/ad/kdbaidang">
                                    <i class="fas fa-list"></i> Quản lý Bài viết
                                </a>
                            </li>
                        </ul>';
                    }elseif($_SESSION['role'] == 5 || $_SESSION['role'] == 1){
                        echo '<ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/ad/qldoanhthu">
                                <i class="fas fa-plus"></i> Quản lý doanh thu
                            </a>
                        </li>
                    </ul>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/ad/qlgiaodich">
                                <i class="fas fa-plus"></i> Quản lý giao dịch
                            </a>
                        </li>
                    </ul>';
                    }

                    ?>
                </div>
            </div>

            <!-- Main Content -->
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
            }else if(isset($_GET["edit-banner"])){
              include_once("view/editbanner.php");
            }else{
              include_once("view/info-admin.php");
            }
          ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/theme-toggle.js"></script>
</body>
</html>


<?php ob_end_flush(); // Kết thúc bộ đệm ?>