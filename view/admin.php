<?php
include_once("model/mConnect.php");
$con = new connect();
$mysqli = $con->connect();
?>

<?php
ob_start(); // Bắt đầu bộ đệm để tránh lỗi headers
// error_reporting(0);

// Xử lý AJAX request TRƯỚC KHI output HTML
if (isset($_GET['qldonhang']) && isset($_GET['action']) && $_GET['action'] === 'get_details' && isset($_GET['order_id'])) {
    // Xóa output buffer để đảm bảo không có HTML nào được output
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Kiểm tra session cho AJAX request
    if (!isset($_SESSION['role']) || ($_SESSION['role'] != 1 && $_SESSION['role'] != 4 && $_SESSION['role'] != 5)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Không đủ thẩm quyền'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    include_once("controller/cQLdonhang.php");
    $controller = new cQLdonhang();
    
    header('Content-Type: application/json');
    
    try {
        $order_id = intval($_GET['order_id']);
        
        if ($order_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID đơn hàng không hợp lệ'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $order = $controller->getOrderDetails($order_id);
        
        if ($order) {
            // Đảm bảo tất cả các trường cần thiết đều có giá trị
            $order['order_code'] = $order['order_code'] ?? '';
            $order['status'] = $order['status'] ?? 'pending';
            $order['total_amount'] = $order['total_amount'] ?? 0;
            $order['payment_method'] = $order['payment_method'] ?? '';
            $order['created_at'] = $order['created_at'] ?? date('Y-m-d H:i:s');
            $order['updated_at'] = $order['updated_at'] ?? $order['created_at'];
            $order['items'] = $order['items'] ?? [];
            
            echo json_encode(['success' => true, 'order' => $order], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng'], JSON_UNESCAPED_UNICODE);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

if ($_SESSION['role'] != 1 && $_SESSION['role'] != 4 && $_SESSION['role'] != 5) {
    echo "<script>
        alert('Bạn không đủ thẩm quyền truy cập!');
        
    </script>";
    header("refresh: 0; url='/index.php?login'");
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
<link rel="stylesheet" href="/admin/src/assets/vendors/mdi/css/materialdesignicons.min.css">
<link rel="stylesheet" href="/admin/src/assets/vendors/css/vendor.bundle.base.css">
<!-- endinject -->
<!-- plugin css for this page -->
<link rel="stylesheet" href="/admin/src/assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<!-- End plugin css for this page -->
<!-- inject:css -->
<link rel="stylesheet" href="/admin/src/assets/css/style.css">
<!-- endinject -->
<link rel="shortcut icon" href="/admin/src/assets/images/favicon.ico" />
<style>
    /* Cố định sidebar */
    @media (min-width: 992px) {
        .sidebar {
            position: fixed;
            top: 60px;
            left: 0;
            height: calc(100vh - 60px);
            overflow-y: auto;
            overflow-x: hidden;
        }
        .main-panel {
            margin-left: 257px;
        }
        .page-body-wrapper {
            padding-top: 0;
        }
    }
    
    /* Tạo khoảng cách giữa header và nội dung */
    .content-wrapper {
        padding-top: 20px !important;
        padding-bottom: 20px;
    }
    
    /* Đảm bảo nội dung không bị đè bởi navbar fixed */
    .main-panel {
        padding-top: 0;
    }
</style>

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
        <li class="nav-item d-none d-lg-block w-100">
            <div class="d-flex align-items-center justify-content-center h-100">
                <span style="font-size: 1.2rem; font-weight: 600; color: #333;">
                    <i class="mdi mdi-store"></i> CHỢ VIỆT - Hệ thống quản lý
                </span>
            </div>
        </li>
        </ul>
        <ul class="navbar-nav navbar-nav-right">
        
        <li class="nav-item nav-profile dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" id="profileDropdown">
            <img src="/img/<?php echo $_SESSION['avatar']; ?>" alt="profile" />
            <span class="nav-profile-name"><?php echo $_SESSION['user_name']; ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
            <a class="dropdown-item">
                <i class="mdi mdi-cog text-primary"></i>
                Settings
            </a>
            <a class="dropdown-item" href="?action=logout">
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
            <a class="nav-link" href="/ad">
                <i class="mdi mdi-account-box menu-icon"></i>
                Thông tin cá nhân
            </a>
        </li>

        <?php
            if($_SESSION['role'] == 1){
                echo '<li class="nav-item">
                    <a class="nav-link" href="/ad/qlnguoidung">
                        <i class="mdi mdi-account-cog menu-icon"></i>
                        Quản lý tài khoản
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/ad/qldanhmuc">
                        <i class="mdi mdi-view-list menu-icon"></i>
                        Quản lý danh mục
                    </a>
                </li>';
                
            }
            if($_SESSION['role'] == 4 || $_SESSION['role'] == 1){
                echo '<li class="nav-item">
                    <a class="nav-link" href="/ad/qlkdbaiviet">
                        <i class="mdi mdi-file-document-edit menu-icon"></i>
                        Quản lý/ Kiểm duyệt bài viết
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/ad/qlbanner">
                        <i class="mdi mdi-image-edit menu-icon"></i>
                        Quản lý Banner
                    </a>
                </li>';
            }
            if($_SESSION['role'] == 5 || $_SESSION['role'] == 1){
                echo '<li class="nav-item">
                    <a class="nav-link" href="/ad/qldoanhthu">
                        <i class="mdi mdi-chart-line menu-icon"></i>
                        Quản lý doanh thu
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/ad/qldonhang">
                        <i class="mdi mdi-cart-outline menu-icon"></i>
                        Quản lý đơn hàng
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/ad/qlgiaodich">
                        <i class="mdi mdi-swap-horizontal menu-icon"></i>
                        Quản lý giao dịch
                    </a>
                </li>';
            }
        ?>

        

        

    </ul>
</nav>

    <!-- partial -->
    <div class="main-panel">
        <div class="content-wrapper">
        <?php
            if(isset($_GET["qlnguoidung"]) || isset($_GET["taikhoan"])){
            if(isset($_GET["ids"]) || isset($_GET["sua"])){
                include_once("view/info-update.php");
            }else if(isset($_GET["them"])){
                include_once("view/info-insert.php");
            }else
                include_once("view/user-table.php");
            }else if (isset($_GET['qldanhmuc'])) {
                include("loaisanpham-table.php");
            }else if(isset($_GET["qlkdbaiviet"])){
            if(isset($_GET["id"])){
                include_once("view/kdbaidang-detail.php");
            }else
                include_once("view/kdbaidang-table.php");
            }else if (isset($_GET["qlbanner"]) || isset($_GET["edit"])) {
                // Nếu có tham số edit thì mở trang sửa banner
                if (isset($_GET['edit'])) {
                    include_once("view/editbanner.php");
                // Nếu không có edit → mở trang danh sách banner
                } else {
                    include_once("view/editbanner.php");
                }
            }else if(isset($_GET["qldoanhthu"])){
                include_once("view/qldoanhthu.php");
            }else if(isset($_GET["qldonhang"])){
                include_once("view/qldonhang.php");
            }else if(isset($_GET["qlgiaodich"])){
                include_once("view/qlgiaodich.php");
            }else{
                include_once("view/info-admin.php");
            }
        ?>
        </div>
        <!-- content-wrapper ends -->
        <!-- partial:partials/_footer.html -->
        <footer class="footer">
        <div class="d-sm-flex justify-content-center justify-content-sm-between">
            <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">
                CÔNG TY TNHH CHỢ VIỆT - Người đại diện: Nguyễn Phúc Hoàng An, Trần Thái Bảo;
            </span>
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
<script src="/admin/src/assets/vendors/js/vendor.bundle.base.js"></script>
<!-- endinject -->
<!-- Plugin js for this page-->
<script src="/admin/src/assets/vendors/chart.js/chart.umd.js"></script>
<script src="/admin/src/assets/vendors/datatables.net/jquery.dataTables.js"></script>
<script src="/admin/src/assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>
<!-- End plugin js for this page-->
<!-- inject:js -->
<script src="/admin/src/assets/js/off-canvas.js"></script>
<script src="/admin/src/assets/js/hoverable-collapse.js"></script>
<script src="/admin/src/assets/js/template.js"></script>
<script src="/admin/src/assets/js/settings.js"></script>
<script src="/admin/src/assets/js/todolist.js"></script>
<!-- endinject -->
<!-- Custom js for this page-->
<script src="/admin/src/assets/js/dashboard.js"></script>
    <script src="/admin/src/assets/js/proBanner.js"></script>
<!-- End custom js for this page-->
<script src="/admin/src/assets/js/jquery.cookie.js" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php ob_end_flush(); // Kết thúc bộ đệm ?>