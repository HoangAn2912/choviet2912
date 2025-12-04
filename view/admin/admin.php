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
    
    // Kiểm tra session cho AJAX request - cho phép role: 1, 3, 4, 5
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], [1, 3, 4, 5])) {
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

<<<<<<< HEAD
// Kiểm tra quyền truy cập admin
// Cho phép: admin (1), moderator (3), adcontent (4), adbusiness (5)
// KHÔNG cho phép: user thường (2)
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 1 && $_SESSION['role'] != 3 && $_SESSION['role'] != 4 && $_SESSION['role'] != 5)) {
    error_log("Admin Access Denied - User ID: " . ($_SESSION['user_id'] ?? 'N/A') . ", Role: " . ($_SESSION['role'] ?? 'N/A'));
=======
// Cho phép role: 1 (admin), 3 (moderator), 4 (adcontent), 5 (adbusiness)
if (!in_array($_SESSION['role'], [1, 3, 4, 5])) {
>>>>>>> 65997a0 (up len web)
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
<!-- Icon Libraries -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<!-- Material Design Icons - CDN Fallback -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css">
<!-- Font Awesome - CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
    <!-- Header gộp thành 1 thẻ duy nhất -->
    <div class="navbar-header-wrapper d-flex align-items-center w-100">
        <!-- Left: Admin Brand -->
        <div class="navbar-brand-section d-flex align-items-center" style="padding: 0 30px !important;">
            <b class="navbar-brand-text">Admin</b>
        </div>
        
        <!-- Center: System Title (Desktop only) -->
        <div class="navbar-title-section d-none d-lg-flex align-items-center justify-content-center flex-grow-1">
            <span class="navbar-title-text" style="font-weight: 700 !important;">
                <i class="mdi mdi-store"></i> CHỢ VIỆT - Hệ thống quản lý
            </span>
        </div>
        
        <!-- Right: Profile + Hamburger (nằm bên phải) -->
        <div class="navbar-actions-section d-flex align-items-center" style="padding: 0 30px !important; margin-left: auto;">
            <!-- Profile Dropdown -->
            <ul class="navbar-nav navbar-nav-right d-flex align-items-center" style="margin-right: 10px;">
            <li class="nav-item nav-profile dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="true" id="profileDropdown" aria-expanded="false">
                    <img src="/img/<?php echo htmlspecialchars($_SESSION['avatar'] ?? 'default-avatar.jpg'); ?>" alt="profile" class="nav-profile-img" onerror="this.src='/img/default-avatar.jpg'" />
                    <span class="nav-profile-name d-none d-lg-inline"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></span>
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
            <!-- Hamburger Button (Mobile only) - nằm bên phải nhất -->
            <button class="navbar-toggler d-lg-none" type="button" id="sidebarToggle" aria-label="Toggle sidebar">
                <span class="mdi mdi-menu"></span>
            </button>
        </div>
    </div>
    </nav>
    <!-- partial -->
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
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
                </li>
                <li class="nav-item">
                    <a class="nav-link ' . ($currentPage == 'qlgiaodich' ? 'active' : '') . '" href="/admin?qlgiaodich">
                        <i class="mdi mdi-swap-horizontal menu-icon"></i>
                        Quản lý giao dịch
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
                </li>';
            }
            if($_SESSION['role'] == 5 || $_SESSION['role'] == 1){
                echo '<li class="nav-item">
                    <a class="nav-link ' . ($currentPage == 'qldonhang' ? 'active' : '') . '" href="/admin?qldonhang">
                        <i class="mdi mdi-cart-outline menu-icon"></i>
                        Quản lý đơn hàng
                    </a>
                </li>';
            }
            // Quản lý Banner - Hiển thị cuối cùng
            if($_SESSION['role'] == 4 || $_SESSION['role'] == 1){
                echo '<li class="nav-item">
                    <a class="nav-link ' . ($currentPage == 'qlbanner' ? 'active' : '') . '" href="/admin?qlbanner">
                        <i class="mdi mdi-image-edit menu-icon"></i>
                        Quản lý Banner
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
<!-- Chart.js - CDN với fallback -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
  // Fallback nếu CDN không load được
  if (typeof Chart === 'undefined') {
    var script = document.createElement('script');
    script.src = '/admin/src/assets/vendors/chart.js/chart.umd.js';
    script.onerror = function() {
      console.error('Chart.js không thể load từ cả CDN và local file');
    };
    document.head.appendChild(script);
  }
</script>
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
    'use strict';
    
    function initDropdown() {
        // Kiểm tra Bootstrap đã load chưa
        if (typeof bootstrap === 'undefined') {
            console.warn('Bootstrap chưa load, đợi thêm...');
            setTimeout(initDropdown, 100);
            return;
        }
        
        // Khởi tạo dropdown menu cho profile
        var profileDropdownToggle = document.getElementById('profileDropdown');
        var profileDropdownMenu = document.getElementById('profileDropdownMenu');
        
        if (!profileDropdownToggle || !profileDropdownMenu) {
            console.error('Profile dropdown elements not found');
            return;
        }
        
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
                    placement: 'bottom-end',
                    modifiers: [
                        {
                            name: 'offset',
                            options: {
                                offset: [0, 8]
                            }
                        }
                    ]
                }
            });
            
            // Xử lý sự kiện khi dropdown được hiển thị
            profileDropdownToggle.addEventListener('show.bs.dropdown', function() {
                // Đảm bảo dropdown menu có z-index cao
                profileDropdownMenu.style.zIndex = '1060';
                profileDropdownMenu.style.display = 'block';
                profileDropdownMenu.classList.add('show');
                
                // Đảm bảo không có element nào che mất
                var navbar = profileDropdownToggle.closest('.navbar');
                if (navbar) {
                    navbar.style.zIndex = '1030';
                }
            });
            
            // Xử lý sự kiện khi dropdown được ẩn
            profileDropdownToggle.addEventListener('hide.bs.dropdown', function() {
                profileDropdownMenu.classList.remove('show');
            });
            
            // Xử lý click trên dropdown items - đảm bảo không bị chặn
            var dropdownItems = profileDropdownMenu.querySelectorAll('.dropdown-item');
            dropdownItems.forEach(function(item) {
                item.addEventListener('click', function(e) {
                    // Cho phép click hoạt động bình thường
                    e.stopPropagation();
                });
            });
            
            // Đảm bảo dropdown toggle có thể click được
            profileDropdownToggle.style.pointerEvents = 'auto';
            profileDropdownToggle.style.cursor = 'pointer';
            profileDropdownToggle.style.position = 'relative';
            profileDropdownToggle.style.zIndex = '1031';
            
            console.log('Profile dropdown initialized successfully');
        } catch (e) {
            console.error('Error initializing dropdown:', e);
            
            // Fallback: Toggle thủ công
            profileDropdownToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var isShowing = profileDropdownMenu.classList.contains('show');
                if (isShowing) {
                    profileDropdownMenu.classList.remove('show');
                    profileDropdownMenu.style.display = 'none';
                } else {
                    profileDropdownMenu.classList.add('show');
                    profileDropdownMenu.style.display = 'block';
                    profileDropdownMenu.style.zIndex = '1060';
                }
                
                // Đóng khi click outside
                setTimeout(function() {
                    document.addEventListener('click', function closeDropdown(event) {
                        if (!profileDropdownToggle.contains(event.target) && 
                            !profileDropdownMenu.contains(event.target)) {
                            profileDropdownMenu.classList.remove('show');
                            profileDropdownMenu.style.display = 'none';
                            document.removeEventListener('click', closeDropdown);
                        }
                    });
                }, 10);
            });
        }
    }
    
    // Chạy khi DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initDropdown, 100);
        });
    } else {
        setTimeout(initDropdown, 100);
    }
    
    // Fallback: Chạy sau khi tất cả script load xong
    window.addEventListener('load', function() {
        setTimeout(initDropdown, 300);
    });
})();

// Sidebar Toggle for Mobile
(function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    if (!sidebar || !sidebarToggle || !sidebarOverlay) {
        return;
    }
    
    function toggleSidebar() {
        sidebar.classList.toggle('sidebar-offcanvas');
        sidebarOverlay.classList.toggle('active');
        
        // Prevent body scroll when sidebar is open on mobile
        if (sidebar.classList.contains('sidebar-offcanvas')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }
    
    function closeSidebar() {
        sidebar.classList.remove('sidebar-offcanvas');
        sidebarOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    // Toggle sidebar when hamburger button is clicked
    sidebarToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        toggleSidebar();
    });
    
    // Close sidebar when overlay is clicked
    sidebarOverlay.addEventListener('click', function() {
        closeSidebar();
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 991) {
            const isClickInsideSidebar = sidebar.contains(e.target);
            const isClickOnToggle = sidebarToggle.contains(e.target);
            
            if (!isClickInsideSidebar && !isClickOnToggle && sidebar.classList.contains('sidebar-offcanvas')) {
                closeSidebar();
            }
        }
    });
    
    // Close sidebar when window is resized to desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth > 991) {
            closeSidebar();
        }
    });
    
    // Close sidebar when a menu item is clicked on mobile
    const sidebarLinks = sidebar.querySelectorAll('.nav-link');
    sidebarLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 991) {
                setTimeout(closeSidebar, 200);
            }
        });
    });
})();
</script>
</body>
</html>
<?php ob_end_flush(); // Kết thúc bộ đệm ?>