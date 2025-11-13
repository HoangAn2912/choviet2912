<?php
// Include Security helper
include_once("helpers/Security.php");

// Khởi tạo session bảo mật
Security::initSecureSession();

// Validate session
Security::validateSession();

//xử lý đăng xuất
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header('Location: index.php?login');
    exit;
}
include_once("controller/cCategory.php");
$p = new cCategory();

include_once("controller/cProduct.php");
$c = new cProduct();

include_once("controller/cDetailProduct.php");
$controller = new cDetailProduct();
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Chợ Việt - Nơi trao đổi hàng hóa</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Free HTML Templates" name="keywords">
    <meta content="Free HTML Templates" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">  

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">



    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles-index.css">
</head>

<body>
    <?php
        if (isset($_GET['action']) && $_GET['action'] == 'capNhatTrangThai') {
            include_once("controller/cPost.php");
            $ctrl = new cPost();
            $ctrl->capNhatTrangThaiBan();
            exit;
        }else if (isset($_GET['action']) && $_GET['action'] == 'dangTin') {
            include_once "controller/cPost.php";
            $post = new cPost();
            $post->dangTin();
            exit; 
        } else if (isset($_GET['quan-ly-tin']) && isset($_GET['sua'])) {
            include_once("controller/cPost.php");
            $ctrl = new cPost();
            $tin = (new mPost())->laySanPhamTheoId($_GET['sua']);
            include_once("view/managePost.php");
            exit;        
        }else if (isset($_GET['daytin'])) {
            include_once("controller/cPost.php");
            $postCtrl = new cPost();
            $postCtrl->dayTin($_GET['daytin']);
        }else if (isset($_GET['action']) && $_GET['action'] === 'suaTin') {
            include_once("controller/cPost.php");
            $controller = new cPost();
            $controller->suaTin(); // Gọi hàm sửa tin
        } else if (isset($_GET['action']) && $_GET['action'] == 'capNhatThongTin') {
            include_once("controller/cProfile.php");
            $ctrl = new cProfile();
            $ctrl->capNhatThongTin();
        }else if (isset($_GET['tin-nhan'])) {
            include_once("view/chat.php");
            exit;
        }else if (isset($_GET['action']) && $_GET['action'] == 'danhgia') {
            include_once("view/review_form.php");
        }else if (isset($_GET['nap-tien'])) {
            include_once("view/naptien.php");
        }else if (isset($_GET['quan-ly-tin'])) {
            include_once("view/managePost.php");
        }else if (isset($_GET['advanced-search'])) {
            // Tìm kiếm nâng cao với filter
            include_once("view/advanced_search.php");
            exit;
        }else if (isset($_GET['inventory-management'])) {
            // Quản lý tồn kho
            include_once("view/inventory_management.php");
            exit;
        }else if (isset($_GET['seller-dashboard'])) {
            // Dashboard người bán
            include_once("view/seller_dashboard.php");
            exit;
        }else if (isset($_GET['seller-update-order-status'])) {
            // API: Cập nhật trạng thái đơn hàng
            include_once("controller/cSellerDashboard.php");
            $dashboard = new cSellerDashboard();
            $dashboard->updateOrderStatus();
        }else if (isset($_GET['seller-order-details'])) {
            // API: Chi tiết đơn hàng
            include_once("controller/cSellerDashboard.php");
            $dashboard = new cSellerDashboard();
            $dashboard->getOrderDetails();
        }else if (isset($_GET['inventory-update-settings'])) {
            // API: Cập nhật cài đặt tồn kho
            include_once("controller/cInventory.php");
            $inv = new cInventory();
            $inv->updateSettings();
        }else if (isset($_GET['inventory-adjust-stock'])) {
            // API: Điều chỉnh tồn kho
            include_once("controller/cInventory.php");
            $inv = new cInventory();
            $inv->adjustStock();
        }else if (isset($_GET['inventory-history'])) {
            // API: Lịch sử tồn kho
            include_once("controller/cInventory.php");
            $inv = new cInventory();
            $inv->getHistory();
        }else if (isset($_GET['search'])) {
            include_once("view/search.php");
        } else if (isset($_GET['category'])) {
            include_once("view/category.php");
        }else if(isset($_GET['shop'])){
            include_once("view/index.php");
        } else if(isset($_GET['cart'])){
            include_once("view/index.php");
        } else if(isset($_GET['checkout'])){
            include_once("checkout.php");
        } else if (isset($_GET['detail']) && isset($_GET['id'])) {
            $id = $_GET['id'];
            $controller->showDetail($id); 
        } else if(isset($_GET['contact'])){
            include_once("view/index.php");
        } else if(isset($_GET['login'])){
            include_once("loginlogout/login.php");
        } else if(isset($_GET['signup'])){
            include_once("loginlogout/signup.php");
        } else if(isset($_GET['thongtin'])){
            include_once("view/profile/index.php");
        } else if(isset($_GET['username'])){
            // Xử lý URL thân thiện cho trang cá nhân
            include_once("model/mProfile.php");
            $profileModel = new mProfile();
            $userId = $profileModel->getUserByUsername($_GET['username']);
            if($userId) {
                $_GET['thongtin'] = $userId;
                include_once("view/profile/index.php");
            } else {
                // Nếu không tìm thấy người dùng, chuyển hướng về trang chủ
                include_once("view/index.php");
            }
        } else if(isset($_GET['livestream'])){
            if(isset($_GET['id'])){
                // Hiển thị livestream chi tiết
                include_once("controller/cLivestream.php");
                $cLivestream = new cLivestream();
                $cLivestream->showLivestream();
            } else {
                // Hiển thị danh sách livestream
                include_once("view/livestream.php");
            }
        } else if(isset($_GET['create-livestream'])){
            // Trang tạo livestream mới - CHỈ DOANH NGHIỆP
            if (!isset($_SESSION['user_id'])) {
                header("Location: index.php?page=login");
                exit;
            }
            
            // Kiểm tra account_type
            require_once("model/mConnect.php");
            $conn = new Connect();
            $db = $conn->connect();
            $check_sql = "SELECT account_type FROM users WHERE id = ?";
            $check_stmt = $db->prepare($check_sql);
            $check_stmt->bind_param("i", $_SESSION['user_id']);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $check_user = $check_result->fetch_assoc();
            $check_stmt->close();
            $db->close();
            
            if (!$check_user || $check_user['account_type'] !== 'doanh_nghiep') {
                // Chuyển hướng đến trang đăng ký gói
                echo "<script>
                    alert('Chỉ tài khoản doanh nghiệp mới được tạo livestream. Vui lòng đăng ký gói livestream để nâng cấp tài khoản!');
                    window.location.href = 'index.php?livestream-packages';
                </script>";
                exit;
            }
            
            include_once("view/create_livestream.php");
        } else if(isset($_GET['my-livestreams'])){
            // Trang quản lý livestream của user - CHỈ DOANH NGHIỆP
            if (!isset($_SESSION['user_id'])) {
                header("Location: index.php?page=login");
                exit;
            }
            
            // Kiểm tra account_type
            require_once("model/mConnect.php");
            $conn = new Connect();
            $db = $conn->connect();
            $check_sql = "SELECT account_type FROM users WHERE id = ?";
            $check_stmt = $db->prepare($check_sql);
            $check_stmt->bind_param("i", $_SESSION['user_id']);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $check_user = $check_result->fetch_assoc();
            $check_stmt->close();
            $db->close();
            
            if (!$check_user || $check_user['account_type'] !== 'doanh_nghiệp') {
                echo "<script>
                    alert('Chỉ tài khoản doanh nghiệp mới có quyền quản lý livestream. Vui lòng đăng ký gói livestream để nâng cấp!');
                    window.location.href = 'index.php?livestream-packages';
                </script>";
                exit;
            }
            
            include_once("view/my_livestreams.php");
        } else if(isset($_GET['livestream-packages'])){
            // Trang mua gói livestream
            include_once("view/livestream_packages.php");
            exit;
        } else if(isset($_GET['action']) && $_GET['action'] == 'purchase-livestream-package-wallet'){
            // Mua gói livestream bằng ví
            include_once("controller/cLivestreamPackage.php");
            $cPackage = new cLivestreamPackage();
            $cPackage->purchaseByWallet();
            exit;
        } else if(isset($_GET['action']) && $_GET['action'] == 'purchase-livestream-package-vnpay'){
            // Mua gói livestream bằng VNPay
            include_once("controller/cLivestreamPackage.php");
            $cPackage = new cLivestreamPackage();
            $cPackage->purchaseByVNPay();
            exit;
        } else if(isset($_GET['livestream-package-history'])){
            // Lịch sử mua gói livestream - CHỈ DOANH NGHIỆP
            if (!isset($_SESSION['user_id'])) {
                header("Location: index.php?page=login");
                exit;
            }
            
            // Kiểm tra account_type
            require_once("model/mConnect.php");
            $conn = new Connect();
            $db = $conn->connect();
            $check_sql = "SELECT account_type FROM users WHERE id = ?";
            $check_stmt = $db->prepare($check_sql);
            $check_stmt->bind_param("i", $_SESSION['user_id']);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $check_user = $check_result->fetch_assoc();
            $check_stmt->close();
            $db->close();
            
            if (!$check_user || $check_user['account_type'] !== 'doanh_nghiep') {
                echo "<script>
                    alert('Chỉ tài khoản doanh nghiệp mới có lịch sử mua gói livestream.');
                    window.location.href = 'index.php?livestream-packages';
                </script>";
                exit;
            }
            
            include_once("controller/cLivestreamPackage.php");
            $cPackage = new cLivestreamPackage();
            $cPackage->showHistory();
            exit;
        } else if(isset($_GET['streamer'])){
            // Panel quản lý livestream cho streamer
            include_once("view/streamer_panel.php");
        } else if(isset($_GET['broadcast'])){
            // Trang phát sóng livestream cho doanh nghiệp - CHỈ DOANH NGHIỆP
            if (!isset($_SESSION['user_id'])) {
                header("Location: index.php?page=login");
                exit;
            }
            
            // Kiểm tra account_type
            require_once("model/mConnect.php");
            $conn = new Connect();
            $db = $conn->connect();
            $check_sql = "SELECT account_type FROM users WHERE id = ?";
            $check_stmt = $db->prepare($check_sql);
            $check_stmt->bind_param("i", $_SESSION['user_id']);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $check_user = $check_result->fetch_assoc();
            $check_stmt->close();
            $db->close();
            
            if (!$check_user || $check_user['account_type'] !== 'doanh_nghiep') {
                echo "<script>
                    alert('Chỉ tài khoản doanh nghiệp mới được phát sóng livestream. Vui lòng đăng ký gói livestream để nâng cấp!');
                    window.location.href = 'index.php?livestream-packages';
                </script>";
                exit;
            }
            
            include_once("view/livestream_broadcast.php");
        } else if(isset($_GET['watch'])){
            // Trang xem livestream cho người dùng
            include_once("view/livestream_viewer.php");
        } else if(isset($_GET['my-orders'])){
            // Trang quản lý đơn hàng
            include_once("my_orders.php");
        } else if(isset($_GET['vnpay-create'])){
            // Tạo thanh toán VNPay
            include_once("controller/vnpay/vnpay_create_payment.php");
        } else {
            include_once("view/index.php");
        }
    ?>

    


</body>

</html>