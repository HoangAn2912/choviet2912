<?php
include_once(__DIR__ . "/../../model/mConnect.php");
require_once __DIR__ . '/../../helpers/url_helper.php';
$con = new connect();
$mysqli = $con->connect();
?>

<?php
ob_start(); // Bắt đầu bộ đệm để tránh lỗi headers
// error_reporting(0);

// Xử lý AJAX request cho chi tiết tài khoản
if (isset($_GET['taikhoan']) && isset($_GET['action']) && $_GET['action'] === 'get_details' && isset($_GET['user_id'])) {
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Không đủ thẩm quyền'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    include_once(__DIR__ . "/../../controller/cQLthongtin.php");
    $controller = new cqlthongtin();
    
    header('Content-Type: application/json');
    
    try {
        $user_id = intval($_GET['user_id']);
        
        if ($user_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID tài khoản không hợp lệ'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $userData = $controller->getoneuser($user_id);
        
        if ($userData && !empty($userData)) {
            $user = $userData[0];
            echo json_encode(['success' => true, 'user' => $user], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy tài khoản'], JSON_UNESCAPED_UNICODE);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

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
    
    include_once(__DIR__ . "/../../controller/cQLdonhang.php");
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
<!-- Admin CSS -->
<link rel="stylesheet" href="/css/admin-style.css">
<link rel="stylesheet" href="/css/admin-common.css">
<link rel="shortcut icon" href="/admin/src/assets/images/favicon.ico" />

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
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="true" id="profileDropdown" aria-expanded="false" onclick="event.preventDefault();">
                <img src="/img/<?php echo htmlspecialchars($_SESSION['avatar'] ?? 'default-avatar.jpg'); ?>" alt="profile" class="nav-profile-img" onerror="this.src='/img/default-avatar.jpg'" />
                <span class="nav-profile-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></span>
                <i class="mdi mdi-chevron-down ms-2"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end navbar-dropdown" aria-labelledby="profileDropdown" id="profileDropdownMenu">
                <li><a class="dropdown-item" href="/admin?thongtincanhan">
                    <i class="mdi mdi-account text-primary me-2"></i>
                    Thông tin cá nhân
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="?action=logout">
                    <i class="mdi mdi-logout text-danger me-2"></i>
                Đăng xuất
                </a></li>
            </ul>
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
        <?php
        // Xác định menu item đang active
        $currentPage = '';
        if(isset($_GET["thongtincanhan"])){
            $currentPage = 'thongtincanhan';
        }else if(isset($_GET["qlnguoidung"]) || isset($_GET["taikhoan"])){
            $currentPage = 'taikhoan';
        }else if(isset($_GET['qldanhmuc'])){
            $currentPage = 'qldanhmuc';
        }else if(isset($_GET["qlkdbaiviet"])){
            $currentPage = 'qlkdbaiviet';
        }else if(isset($_GET["qlbanner"])){
            $currentPage = 'qlbanner';
        }else if(isset($_GET["qldoanhthu"])){
            $currentPage = 'qldoanhthu';
        }else if(isset($_GET["qldonhang"])){
            $currentPage = 'qldonhang';
        }else if(isset($_GET["qlgiaodich"])){
            $currentPage = 'qlgiaodich';
        }else{
            // Trang chủ mặc định: Quản lý doanh thu
            $currentPage = 'qldoanhthu';
        }
        ?>

        <?php
            // Quản lý doanh thu - Hiển thị đầu tiên
            if($_SESSION['role'] == 5 || $_SESSION['role'] == 1){
                echo '<li class="nav-item">
                    <a class="nav-link ' . ($currentPage == 'qldoanhthu' ? 'active' : '') . '" href="/admin">
                        <i class="mdi mdi-chart-line menu-icon"></i>
                        Quản lý doanh thu
                    </a>
                </li>';
            }
            
            if($_SESSION['role'] == 1){
                echo '<li class="nav-item">
                    <a class="nav-link ' . ($currentPage == 'taikhoan' ? 'active' : '') . '" href="/admin?qlnguoidung">
                        <i class="mdi mdi-account-cog menu-icon"></i>
                        Quản lý tài khoản
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link ' . ($currentPage == 'qldanhmuc' ? 'active' : '') . '" href="/admin?qldanhmuc">
                        <i class="mdi mdi-view-list menu-icon"></i>
                        Quản lý danh mục
                    </a>
                </li>';
                
            }
            if($_SESSION['role'] == 4 || $_SESSION['role'] == 1){
                echo '<li class="nav-item">
                    <a class="nav-link ' . ($currentPage == 'qlkdbaiviet' ? 'active' : '') . '" href="/admin?qlkdbaiviet">
                        <i class="mdi mdi-file-document-edit menu-icon"></i>
                        Quản lý bài viết
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link ' . ($currentPage == 'qlbanner' ? 'active' : '') . '" href="/admin?qlbanner">
                        <i class="mdi mdi-image-edit menu-icon"></i>
                        Quản lý Banner
                    </a>
                </li>';
            }
            if($_SESSION['role'] == 5 || $_SESSION['role'] == 1){
                echo '<li class="nav-item">
                    <a class="nav-link ' . ($currentPage == 'qldonhang' ? 'active' : '') . '" href="/admin?qldonhang">
                        <i class="mdi mdi-cart-outline menu-icon"></i>
                        Quản lý đơn hàng
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link ' . ($currentPage == 'qlgiaodich' ? 'active' : '') . '" href="/admin?qlgiaodich">
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
            if(isset($_GET["thongtincanhan"])){
                // Thông tin cá nhân - chỉ truy cập từ dropdown menu
                include_once(__DIR__ . "/info-admin.php");
            }else if(isset($_GET["qlnguoidung"]) || isset($_GET["taikhoan"])){
            if(isset($_GET["ids"]) || isset($_GET["sua"])){
                include_once(__DIR__ . "/info-update.php");
            }else if(isset($_GET["them"])){
                include_once(__DIR__ . "/info-insert.php");
            }else
                include_once(__DIR__ . "/user-table.php");
            }else if (isset($_GET['qldanhmuc'])) {
                include(__DIR__ . "/loaisanpham-table.php");
            }else if(isset($_GET["qlkdbaiviet"])){
            if(isset($_GET["id"])){
                include_once(__DIR__ . "/kdbaidang-detail.php");
            }else
                include_once(__DIR__ . "/kdbaidang-table.php");
            }else if (isset($_GET["qlbanner"]) || isset($_GET["edit"])) {
                // Nếu có tham số edit thì mở trang sửa banner
                if (isset($_GET['edit'])) {
                    include_once(__DIR__ . "/editbanner.php");
                // Nếu không có edit → mở trang danh sách banner
                } else {
                    include_once(__DIR__ . "/editbanner.php");
                }
            }else if(isset($_GET["qldonhang"])){
                include_once(__DIR__ . "/qldonhang.php");
            }else if(isset($_GET["qlgiaodich"])){
                include_once(__DIR__ . "/qlgiaodich.php");
            }else{
                // Trang chủ mặc định: Quản lý doanh thu
                include_once(__DIR__ . "/qldoanhthu.php");
            }
        ?>
        </div>
        <!-- content-wrapper ends -->
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
<script>
// Đảm bảo Bootstrap dropdown hoạt động
(function() {
    function initDropdown() {
        // Kiểm tra Bootstrap đã load chưa
        if (typeof bootstrap === 'undefined') {
            console.error('Bootstrap is not loaded, retrying...');
            setTimeout(initDropdown, 100); // Retry sau 100ms
            return;
        }
        
        // Khởi tạo dropdown menu cho profile
        var profileDropdownToggle = document.getElementById('profileDropdown');
        var profileDropdownMenu = document.getElementById('profileDropdownMenu');
        
        if (profileDropdownToggle && profileDropdownMenu) {
            // Xóa instance cũ nếu có
            var existingInstance = bootstrap.Dropdown.getInstance(profileDropdownToggle);
            if (existingInstance) {
                existingInstance.dispose();
            }
            
            // Khởi tạo Bootstrap Dropdown
            try {
                var profileDropdown = new bootstrap.Dropdown(profileDropdownToggle, {
                    boundary: 'viewport',
                    popperConfig: {
                        placement: 'bottom-end'
                    }
                });
                
                // Xóa onclick cũ và thêm event listener mới
                profileDropdownToggle.removeAttribute('onclick');
                profileDropdownToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    profileDropdown.toggle();
                    return false;
                });
                
                // Đảm bảo dropdown hiển thị khi click
                profileDropdownToggle.addEventListener('show.bs.dropdown', function() {
                    profileDropdownMenu.classList.add('show');
                });
                
                profileDropdownToggle.addEventListener('hide.bs.dropdown', function() {
                    profileDropdownMenu.classList.remove('show');
                });
                
                console.log('Profile dropdown initialized successfully');
            } catch (e) {
                console.error('Error initializing dropdown:', e);
                // Fallback: Toggle thủ công
                profileDropdownToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    profileDropdownMenu.classList.toggle('show');
                    return false;
                });
            }
        } else {
            console.error('Profile dropdown elements not found');
        }
    }
    
    // Chạy khi DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDropdown);
    } else {
        // DOM đã ready
        initDropdown();
    }
    
    // Fallback: Chạy sau khi tất cả script load xong
    window.addEventListener('load', function() {
        setTimeout(initDropdown, 200);
    });
})();
</script>
</body>
</html>
<?php ob_end_flush(); // Kết thúc bộ đệm ?>