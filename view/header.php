<?php
error_reporting(0);
include_once "controller/cCategory.php";

$cCategory = new cCategory();
$data = $cCategory->index();

$userHeader = null;
$is_business_account = false;
$user_account_type = 'ca_nhan';

$hasUnread = false;
if (isset($_SESSION['user_id'])) {
    $userHeader = $cCategory->getUserById($_SESSION['user_id']);
    
    // Kiểm tra tin nhắn chưa đọc
    try {
        require_once __DIR__ . '/../controller/cChat.php';
        $cChat = new cChat();
        $unreadCount = $cChat->demTinNhanChuaDoc($_SESSION['user_id']);
        $hasUnread = ($unreadCount > 0);
    } catch (Exception $e) {
        $hasUnread = false;
    }
    
    // Lấy account_type để kiểm tra quyền truy cập
    // Query trực tiếp để tránh dependency vào $cCategory
    try {
        require_once __DIR__ . '/../model/mConnect.php';
        $headerConn = new Connect();
        $headerDb = $headerConn->connect();
        if ($headerDb) {
            $header_sql = "SELECT account_type FROM users WHERE id = ?";
            $header_stmt = $headerDb->prepare($header_sql);
            if ($header_stmt) {
                $header_stmt->bind_param("i", $_SESSION['user_id']);
                $header_stmt->execute();
                $header_result = $header_stmt->get_result();
                if ($header_user = $header_result->fetch_assoc()) {
                    $user_account_type = $header_user['account_type'] ?? 'ca_nhan';
                    $is_business_account = ($user_account_type === 'doanh_nghiep');
                }
                $header_stmt->close();
            }
            $headerDb->close();
        }
    } catch (Exception $e) {
        // Fallback to default values if query fails
        $is_business_account = false;
        $user_account_type = 'ca_nhan';
    }
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
    
    <?php 
    // CSRF Token Meta Tag
    if (class_exists('Security')) {
        echo Security::csrfMetaTag(); 
    }
    ?>

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
    <link href="css/responsive.css" rel="stylesheet">
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
    
    <!-- CSRF Handler -->
    <script src="js/csrf-handler.js"></script>

    <!-- Custom Dropdown Styles -->
    <style>
        /* ========================================
           DROPDOWN MENU CUSTOM STYLES
        ======================================== */
        
        /* ========================================
           DROPDOWN MENU CHUNG - THỐNG NHẤT TẤT CẢ HEADER
           Style từ navbar-vertical (danh mục con)
        ======================================== */
        
        /* Dropdown Menu Container - Áp dụng cho TẤT CẢ dropdown */
        .dropdown-menu {
            position: absolute !important;
            border: none !important;
            border-radius: 12px !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
            padding: 8px 0 !important;
            margin-top: 0 !important;
            top: 100% !important;
            right: 0 !important;
            left: auto !important;
            min-width: 240px;
            max-width: 300px;
            z-index: 1050 !important;
            background: #fff !important;
            opacity: 0;
            pointer-events: none;
            transform: scale(0.95);
            transition: opacity 0.25s ease, transform 0.25s ease;
        }
        
        /* Dropdown-menu-end (căn phải) */
        .dropdown-menu-end {
            right: 0 !important;
            left: auto !important;
        }
        
        /* Tạo vùng hover "invisible" để không bị mất hover */
        .dropdown-menu::before {
            content: '';
            position: absolute;
            top: -10px;
            left: 0;
            right: 0;
            height: 10px;
            background: transparent;
        }
        
        /* Hiển thị dropdown khi hover vào .dropdown */
        .dropdown:hover > .dropdown-menu {
            opacity: 1 !important;
            pointer-events: auto !important;
            transform: scale(1) !important;
            display: block !important;
        }
        
        /* Z-index phân cấp */
        .header-user .dropdown-menu {
            z-index: 1060 !important;
            min-width: 220px;
        }
        
        .header-line-2 .dropdown-menu {
            z-index: 1055 !important;
        }

        /* ========================================
           DROPDOWN ITEMS CHUNG - STYLE TỪ NAVBAR-VERTICAL
           Áp dụng cho TẤT CẢ dropdown trong header
        ======================================== */
        
        /* Dropdown Items - Style đẹp từ danh mục con */
        .dropdown-item {
            /* Layout cơ bản */
            padding: 12px 20px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            color: #555 !important;
            text-decoration: none !important;
            
            /* Display */
            display: flex !important;
            align-items: center !important;
            cursor: pointer;
            position: relative;
            
            /* Border trái (ẩn ban đầu) */
            border-left: 3px solid transparent;
            
            /* Transition mượt */
            transition: all 0.25s ease !important;
            background: transparent !important;
        }
        
        /* Thanh border vàng bên trái (hiệu ứng đẹp) */
        .dropdown-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: #FFD333;
            transform: scaleY(0);
            transition: transform 0.25s ease;
        }

        /* Icon trong dropdown-item */
        .dropdown-item i {
            width: 20px;
            min-width: 20px;
            margin-right: 12px;
            color: #666;
            transition: color 0.25s ease;
        }

        /* HOVER EFFECT - Gradient vàng đẹp giống danh mục con */
        .dropdown-item:hover {
            background: linear-gradient(to right, #FFF9E6 0%, #FFE5B4 100%) !important;
            color: #8B6914 !important;
            border-left-color: #FFD333;
        }
        
        /* Thanh vàng bên trái xuất hiện khi hover */
        .dropdown-item:hover::before {
            transform: scaleY(1);
        }

        /* Icon đổi màu khi hover */
        .dropdown-item:hover i {
            color: #8B6914 !important;
        }

        /* Active state (khi click) */
        .dropdown-item:active {
            background: linear-gradient(to right, #FFE5B4 0%, #FFD700 100%) !important;
            color: #8B6914 !important;
        }
        
        /* Divider - Gạch ngang phân cách */
        .dropdown-divider {
            margin: 6px 0 !important;
            border-top: 1px solid #e9ecef !important;
        }
        
        /* Dropdown Header - Tiêu đề section */
        .dropdown-header {
            font-size: 12px !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            color: #666 !important;
            padding: 8px 20px 4px 20px !important;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }
        
        .dropdown-header i {
            color: #FFD333;
            margin-right: 8px;
        }


        /* Special Item - Đăng Ký Gói (với icon vàng) */
        .dropdown-item .text-warning {
            color: #ffc107 !important;
        }

        .dropdown-item:hover .text-warning {
            color: #fff !important;
        }

        /* Dropdown Toggle */
        .dropdown-toggle {
            position: relative;
            font-weight: 600;
        }

        .dropdown-toggle::after {
            margin-left: 8px;
            transition: transform 0.2s ease;
        }

        .dropdown.show .dropdown-toggle::after {
            transform: rotate(180deg);
        }
        
        /* Đảm bảo dropdown không bị layout shift */
        .dropdown {
            position: relative;
        }
        
        /* VÔ HIỆU HÓA CLICK cho dropdown toggle - CHỈ DÙNG HOVER */
        .dropdown > [data-bs-toggle="dropdown"] {
            pointer-events: auto !important;
            cursor: pointer;
        }
        
        /* Ngăn Bootstrap xử lý click event */
        .dropdown > [data-bs-toggle="dropdown"]:not([href]) {
            pointer-events: none !important; /* Chặn click */
        }
        
        /* Nhưng vẫn cho phép hover */
        .dropdown:hover > [data-bs-toggle="dropdown"] {
            pointer-events: auto !important;
        }
        
        /* Đảm bảo dropdown-menu luôn có thể tương tác khi visible */
        .dropdown:hover .dropdown-menu {
            pointer-events: auto !important;
        }

        /* Responsive - Mobile */
        @media (max-width: 991px) {
            .dropdown-menu {
                position: static !important;
                float: none;
                width: 100%;
                margin-top: 0 !important;
                border-radius: 8px !important;
                box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.1) !important;
            }
            
            .navbar {
                min-height: auto !important;
                padding: 0.5rem 0 !important;
            }
            
            .navbar-nav {
                gap: 8px !important;
            }
            
            .nav-link {
                padding: 0.5rem 0.75rem !important;
                font-size: 0.9rem !important;
            }
            
            .btn-dang-tin {
                padding: 0.4rem 0.8rem !important;
                font-size: 0.85rem !important;
            }
            
            .input-group input {
                height: 36px !important;
                font-size: 0.9rem !important;
            }
            
            .input-group-append button {
                height: 36px !important;
                width: 36px !important;
            }
        }
        
        @media (max-width: 768px) {
            .navbar-nav {
                gap: 5px !important;
            }
            
            .nav-link {
                padding: 0.4rem 0.5rem !important;
                font-size: 0.85rem !important;
            }
            
            .nav-link i {
                margin-right: 0.3rem !important;
            }
            
            .btn-dang-tin {
                padding: 0.35rem 0.6rem !important;
                font-size: 0.8rem !important;
            }
            
            .btn-dang-tin i {
                margin-right: 0.3rem !important;
            }
            
            .search-input-group {
                max-width: 100% !important;
                min-width: 150px !important;
            }
        }
        
        /* Header 2 Dòng - Layout mới */
        .navbar-sticky .row {
            margin: 0 !important;
        }
        
        /* LINE 1 - Grid Layout để căn giữa search */
        .header-logo {
            flex-shrink: 0;
        }
        
        .header-search {
            width: 100%;
            min-width: 0;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
        }
        
        .search-form-full {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            display: block;
        }
        
        .search-form-full .input-group {
            width: 100%;
            display: flex;
        }
        
        .search-form-full .input-group .form-control {
            flex: 1;
            min-width: 0;
        }
        
        .header-user {
            flex-shrink: 0;
            white-space: nowrap;
        }
        
        /* Responsive cho Grid Layout */
        @media (max-width: 1200px) {
            .search-form-full {
                max-width: 100%;
            }
        }
        
        @media (max-width: 991px) {
            .header-search,
            .header-logo,
            .header-user {
                grid-column: 1 / -1;
            }
                        .search-form-full {
                max-width: 100%;
                width: 100%;
            }
        }
        
        /* Nav link hover effects cho LINE 2 */
        .navbar-sticky .nav-link {
            transition: all 0.3s ease;
        }
        
        .navbar-sticky .nav-link:hover {
            background: rgba(255, 255, 255, 0.1) !important;
            transform: translateY(-1px);
        }
        
        /* Responsive cho header 2 dòng */
        @media (max-width: 991px) {
            .navbar-sticky .row:first-child {
                gap: 10px;
                padding: 0.75rem 1rem !important;
            }
            
            .header-logo,
            .header-location,
            .header-user {
                width: 100%;
                padding: 0.25rem 0;
                justify-content: center;
            }
            
            .navbar-sticky .row:last-child {
                flex-wrap: wrap;
                gap: 5px;
                padding: 0.5rem 1rem !important;
            }
            
            .navbar-sticky .row:last-child .nav-link {
                font-size: 0.8rem !important;
                padding: 0.4rem 0.8rem !important;
            }
            
            .navbar-sticky .row:last-child .col-auto {
                width: 100%;
            }
            
            .navbar-sticky .row:last-child .col {
                width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            .navbar-sticky .row:last-child .nav-link {
                font-size: 0.75rem !important;
                padding: 0.35rem 0.6rem !important;
            }
            
            .navbar-sticky .row:last-child .nav-link i {
                margin-right: 0.3rem !important;
            }
        }

        /* ========================================
           DANH MỤC SIDEBAR - STYLES MỚI
        ======================================== */
        
        /* Container danh mục sidebar */
        #navbar-vertical {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
            border-radius: 0 0 8px 8px !important;
            max-height: 600px;
            overflow-y: auto;
            overflow-x: hidden;
        }
        
        #navbar-vertical::-webkit-scrollbar {
            width: 6px;
        }
        
        #navbar-vertical::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        #navbar-vertical::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }
        
        #navbar-vertical::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        /* Danh mục cha - Nav Item */
        .navbar-vertical .nav-item {
            position: relative;
            margin: 0 !important;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .navbar-vertical .nav-item:last-child {
            border-bottom: none;
        }
        
        /* Danh mục cha - Nav Link */
        .navbar-vertical .nav-link {
            padding: 14px 20px !important;
            font-size: 14px !important;
            font-weight: 600 !important;
            color: #333 !important;
            text-decoration: none !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            transition: all 0.3s ease !important;
            background: #fff !important;
            border-left: 4px solid transparent !important;
            position: relative;
        }
        
        .navbar-vertical .nav-link:hover {
            background: linear-gradient(to right, #FFF9E6 0%, #FFE5B4 100%) !important;
            color: #8B6914 !important;
            padding-left: 24px !important;
            border-left-color: #FFD333 !important;
            transform: translateX(3px);
            box-shadow: 0 2px 8px rgba(255, 211, 51, 0.2);
        }
        
        .navbar-vertical .nav-link.show {
            background: linear-gradient(to right, #FFF9E6 0%, #FFE5B4 100%) !important;
            color: #8B6914 !important;
            border-left-color: #FFD333 !important;
        }
        
        /* Icon góc phải trong danh mục cha */
        .navbar-vertical .nav-link .fa-angle-right {
            font-size: 14px;
            transition: all 0.3s ease;
            color: #999;
        }
        
        .navbar-vertical .nav-link:hover .fa-angle-right,
        .navbar-vertical .nav-link.show .fa-angle-right {
            color: #8B6914 !important;
            transform: translateX(5px) rotate(90deg);
        }
        
        /* Category toggle icon animation */
        #category-toggle-icon {
            transition: transform 0.3s ease;
        }
        
        /* Đảm bảo menu đóng được khi click bên ngoài */
        #navbar-vertical {
            transition: all 0.3s ease;
        }
        
        /* Dropdown menu chứa danh mục con - Tối ưu hover */
        .navbar-vertical .dropright .dropdown-menu {
            position: absolute !important;
            left: 100% !important;
            top: 0 !important;
            margin-left: 0 !important; /* Loại bỏ margin để hover liền mạch */
            border-radius: 12px !important;
            border: none !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
            padding: 8px 0 !important;
            min-width: 220px;
            max-width: 280px;
            background: #fff !important;
            opacity: 0;
            pointer-events: none;
            transform: translateX(0) scale(0.95);
            transition: opacity 0.2s ease, transform 0.2s ease;
            z-index: 1051 !important;
        }
        
        /* Tạo vùng hover invisible bên trái */
        .navbar-vertical .dropright .dropdown-menu::before {
            content: '';
            position: absolute;
            left: -8px; /* Vùng hover 8px bên trái */
            top: 0;
            bottom: 0;
            width: 8px;
            background: transparent;
        }
        
        /* Hiển thị dropdown con khi hover vào dropright */
        .navbar-vertical .dropright:hover > .dropdown-menu {
            opacity: 1 !important;
            pointer-events: auto !important;
            transform: translateX(0) scale(1) !important;
            display: block !important;
        }
        
        /* Danh mục con - Dropdown Item (Dùng style chung từ .dropdown-item) */
        /* Không cần override vì đã có CSS chung ở trên */
        
        /* Danh mục không có con - Nav Link đơn giản */
        .navbar-vertical .nav-item:not(.dropdown) .nav-link {
            cursor: pointer;
        }
        
        .navbar-vertical .nav-item:not(.dropdown) .nav-link:hover {
            background: linear-gradient(to right, #FFF9E6 0%, #FFE5B4 100%) !important;
            color: #8B6914 !important;
            padding-left: 24px !important;
            border-left-color: #FFD333 !important;
        }
        
        /* Responsive - Mobile */
        @media (max-width: 991px) {
            .navbar-vertical .dropright .dropdown-menu {
                position: static !important;
                left: auto !important;
                top: auto !important;
                margin-left: 0 !important;
                margin-top: 5px !important;
                width: 100% !important;
                max-width: 100% !important;
                box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.1) !important;
                border-radius: 8px !important;
            }
            
            .navbar-vertical .nav-link {
                padding: 12px 16px !important;
                font-size: 13px !important;
            }
            
            .navbar-vertical .dropdown-item {
                padding: 10px 16px 10px 32px !important;
                font-size: 12px !important;
            }
        }

        /* Badge/Count in Dropdown */
        .dropdown-item .badge {
            margin-left: auto;
        }

        /* Icon Rotation on Hover */
        .dropdown-item:hover i.fa-angle-right {
            transform: translateX(3px);
        }

        /* Prevent dropdown close on click inside (for filters) */
        .dropdown-menu.keep-open {
            display: block;
        }

        /* Dropdown Header Styles */
        .dropdown-header {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #666 !important;
            padding: 8px 20px 4px 20px !important;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }

        .dropdown-header i {
            color: #FFD333;
            margin-right: 8px;
        }

        /* ========================================
           HEADER NAVBAR - NO WRAP STYLES
        ======================================== */
        
        /* Logo Text Styles - Thiết kế đẹp */
        .logo-text {
            display: flex !important;
            align-items: center !important;
            padding: 0 !important;
            margin-right: 1.5rem !important;
            position: relative;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .logo-icon {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .logo-text:hover .logo-icon {
            transform: rotate(10deg) scale(1.1);
            filter: drop-shadow(0 0 12px rgba(255, 215, 0, 0.9));
        }
        
        .logo-text:active .logo-icon {
            transform: rotate(10deg) scale(1.3);
            filter: drop-shadow(0 0 15px rgba(255, 215, 0, 1));
        }
        
        .logo-main {
            display: inline-block;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-weight: 800;
            font-size: 1.75rem;
            letter-spacing: 2px;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 50%, #FF8C00 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
            text-shadow: 0 0 30px rgba(255, 215, 0, 0.5);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 0.25rem 0.5rem;
            border-radius: 8px;
        }
        
        /* Glow effect */
        .logo-main::before {
            content: 'Chợ Việt';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 50%, #FF8C00 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            filter: blur(8px);
            opacity: 0.6;
            z-index: -1;
            transition: all 0.4s ease;
        }
        
        /* Hover effects */
        .logo-text:hover {
            transform: translateY(-2px);
        }
        
        .logo-text:hover .logo-main {
            background: linear-gradient(135deg, #FFED4E 0%, #FFB84D 50%, #FF9F40 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            transform: scale(1.05);
            text-shadow: 0 0 40px rgba(255, 215, 0, 0.8);
        }
        
        .logo-text:hover .logo-main::before {
            opacity: 0.9;
            filter: blur(12px);
        }
        
        /* Active state */
        .logo-text:active {
            transform: translateY(0);
        }
        
        .logo-text:active .logo-main {
            transform: scale(0.98);
        }
        
        /* Logo Slogan Styles */
        .logo-slogan {
            font-size: 0.65rem;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 400;
            letter-spacing: 0.3px;
            margin-top: -3px;
            line-height: 1.2;
            max-width: 200px;
            white-space: normal;
            word-wrap: break-word;
            transition: all 0.3s ease;
        }
        
        .logo-text:hover .logo-slogan {
            color: rgba(255, 255, 255, 0.9);
        }
        
        /* Responsive */
        @media (max-width: 991px) {
            .logo-main {
                font-size: 1.5rem;
                letter-spacing: 1.5px;
            }
            
            .logo-slogan {
                font-size: 0.6rem;
                max-width: 180px;
            }
            
            .logo-icon {
                font-size: 1.2rem !important;
            }
        }
        
        @media (max-width: 768px) {
            .logo-main {
                font-size: 1.3rem;
                letter-spacing: 1px;
                padding: 0.2rem 0.4rem;
            }
            
            .logo-slogan {
                font-size: 0.55rem;
                max-width: 150px;
            }
            
            .logo-icon {
                font-size: 1.1rem !important;
            }
        }
        
        @media (max-width: 576px) {
            .logo-main {
                font-size: 1.1rem;
                letter-spacing: 0.5px;
            }
            
            .logo-text {
                margin-right: 1rem !important;
            }
            
            .logo-icon {
                font-size: 1rem !important;
                margin-right: 0.4rem !important;
            }
            
            .logo-slogan {
                font-size: 0.5rem;
                max-width: 120px;
            }
        }
        
        @media (max-width: 480px) {
            .logo-slogan {
                display: none; /* Ẩn slogan trên màn hình rất nhỏ */
            }
        }
        
        /* Animation khi load trang */
        @keyframes logoFadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo-text {
            animation: logoFadeIn 0.6s ease-out;
        }
        
        /* Navbar Collapse - Không wrap - Chỉ áp dụng desktop */
        @media (min-width: 992px) {
            #navbarCollapse {
                flex-wrap: nowrap !important;
                overflow: visible !important;
                display: flex !important;
                align-items: center !important;
                min-height: 50px !important;
                justify-content: space-between !important;
                width: 100% !important;
            }
        }
        
        /* Search bar styling - Tối ưu */
        .search-form-wrapper {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .search-input-group {
            flex: 1;
            max-width: 500px;
            min-width: 200px;
        }
        
        .search-input-group .form-control:focus {
            border-color: #FFD333;
            box-shadow: 0 0 0 0.2rem rgba(255, 211, 51, 0.25);
        }
        
        /* Đảm bảo search form responsive */
        @media (min-width: 1400px) {
            .search-form-wrapper {
                max-width: 1400px;
            }
        }
        
        @media (max-width: 1200px) {
            .search-form-wrapper {
                max-width: 100%;
            }
        }
        
        /* Sticky Header */
        .navbar-sticky {
            position: sticky !important;
            top: 0 !important;
            z-index: 1000 !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2) !important;
            transition: all 0.3s ease !important;
            overflow: visible !important; /* ← Quan trọng: cho phép dropdown hiển thị ra ngoài */
        }
        
        /* Header Line 1 - Thêm border bottom động */
        .header-line-1 {
            border-bottom: 1px solid rgba(255,255,255,0.1);
            transition: border-bottom 0.3s ease;
        }
        
        .header-line-1.no-border {
            border-bottom: none !important;
        }
        
        /* Header 2 ẩn khi cuộn xuống */
        .header-line-2 {
            max-height: 500px;
            opacity: 1;
            overflow: visible !important; /* ← Quan trọng: cho phép dropdown bung ra */
            transition: max-height 0.3s ease, opacity 0.3s ease, margin 0.3s ease, padding 0.3s ease, border 0.3s ease;
        }
        
        .header-line-2.hidden {
            max-height: 0 !important;
            opacity: 0 !important;
            margin: 0 !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            min-height: 0 !important;
            border: none !important;
            overflow: hidden !important; /* Khi ẩn thì mới dùng overflow hidden */
        }
        
        /* Compact Header Styles */
        .navbar-compact {
            min-height: 50px !important;
        }
        
        .navbar-compact .nav-link {
            padding: 0.35rem 0.5rem !important;
            font-size: 0.85rem !important;
        }
        
        .navbar-compact .btn {
            height: 36px !important;
            font-size: 0.85rem !important;
            padding: 0.35rem 0.7rem !important;
        }
        
        /* Header spacing - Đồng nhất cho tất cả trang */
        .navbar-sticky {
            margin-bottom: 1rem !important;
        }
        
        /* Đảm bảo header không quá cao */
        .navbar-sticky .row {
            margin: 0 !important;
        }

        /* Menu navbar - Giữ trên 1 dòng */
        .navbar-nav {
            flex-wrap: nowrap !important;
            white-space: nowrap !important;
        }

        /* Nav link - Không wrap */
        .nav-link {
            white-space: nowrap !important;
            flex-shrink: 0 !important;
            padding: 0.5rem 0.75rem !important;
        }

        /* Dropdown - Không wrap, overflow visible */
        .dropdown {
            flex-shrink: 0 !important;
            white-space: nowrap !important;
            position: relative !important;
            overflow: visible !important;
        }

        /* Dropdown menu - Luôn hiển thị đúng vị trí */
        .dropdown-menu {
            position: absolute !important;
            z-index: 1050 !important;
            overflow: visible !important;
        }

        /* Giảm gap và font-size cho màn hình trung bình */
        @media (min-width: 992px) and (max-width: 1400px) {
            .navbar-nav {
                gap: 12px !important;
            }
            
            .nav-link {
                font-size: 0.9rem !important;
                padding: 0.4rem 0.6rem !important;
            }
            
            .nav-link i {
                font-size: 0.9rem !important;
                margin-right: 0.4rem !important;
            }

            /* Giảm kích thước nút Đăng Tin */
            .btn-dang-tin {
                padding: 0.4rem 0.8rem !important;
                font-size: 0.9rem !important;
            }

            .btn-dang-tin i {
                margin-right: 0.4rem !important;
            }
        }

        /* Màn hình lớn hơn 1400px - Giữ nguyên */
        @media (min-width: 1401px) {
            .navbar-nav {
                gap: 20px !important;
            }
        }

        /* Màn hình nhỏ hơn 1200px - Giảm thêm */
        @media (min-width: 992px) and (max-width: 1200px) {
            .navbar-nav {
                gap: 8px !important;
            }
            
            .nav-link {
                font-size: 0.85rem !important;
                padding: 0.35rem 0.5rem !important;
            }

            .btn-dang-tin {
                padding: 0.35rem 0.7rem !important;
                font-size: 0.85rem !important;
            }
        }

        /* Search bar - Tự động điều chỉnh */
        .input-group {
            min-width: 200px;
            padding: 0 20%;
        }

        @media (min-width: 992px) and (max-width: 1200px) {
            .input-group {
                min-width: 150px;
                max-width: 300px;
            }
        }

        /* Avatar section - Không wrap */
        .navbar-nav:last-child {
            flex-wrap: nowrap !important;
            white-space: nowrap !important;
        }

        /* Đảm bảo dropdown không bị cắt - Chỉ desktop */
        @media (min-width: 992px) {
            .navbar-collapse {
                overflow: visible !important;
            }
        }

        .navbar {
            overflow: visible !important;
        }
    </style>

</head>

<body>
    <!-- Header 2 Dòng - Thiết kế mới -->
    <div class="container-fluid bg-dark navbar-sticky" style="position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.2); margin-bottom: 1rem !important; padding: 0px !important;">
        <!-- LINE 1: Logo - Tìm kiếm (Canh giữa) - Đăng nhập -->
        <div class="px-xl-5 py-2 header-line-1" style="min-height: 50px; display: grid; grid-template-columns: auto 1fr auto; align-items: center; gap: 1.5rem;">
            <!-- Logo - Dạng chữ - Thiết kế đẹp -->
            <div class="header-logo d-flex align-items-center" style="flex-shrink: 0;">
                <a href="index.php" class="navbar-brand logo-text" style="padding: 0; text-decoration: none;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-store logo-icon" style="color: #FFD700; font-size: 1.3rem; filter: drop-shadow(0 0 8px rgba(255, 215, 0, 0.6)); margin-right: 0.5rem;"></i>
                        <div class="d-flex flex-column">
                            <span class="logo-main">Chợ Việt</span>
                            <span class="logo-slogan">Chợ của người Việt - Nơi kết nối qua hàng hóa</span>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Tìm kiếm - Canh giữa -->
            <div class="header-search d-flex align-items-center justify-content-center" style="width: 100%; min-width: 0;">
                <form action="index.php" method="get" class="search-form-full" style="width: 100%;">
                    <input type="hidden" name="search" value="1">
                    <div class="input-group" style="
            max-width: 100%;">
                        <input type="text" name="keyword" class="form-control" placeholder="Tìm kiếm sản phẩm..." required style="height: 36px; font-size: 0.9rem; border-radius: 18px 0 0 18px;">
                        <div class="input-group-append">
                            <button class="btn d-flex align-items-center justify-content-center" type="submit" style="background: linear-gradient(135deg, #FFD333, #FFA500); height: 36px; padding: 0 15px; border-radius: 0 18px 18px 0; border: none; white-space: nowrap;">
                                <i class="fa fa-search text-white mr-2"></i>
                                <span class="text-white" style="font-size: 0.9rem; font-weight: 500;">Tìm kiếm</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Đăng nhập / Avatar -->
            <div class="header-user d-flex align-items-center px-2" style="flex-shrink: 0;">
                <?php if (isset($_SESSION['user_id']) && $userHeader): ?>
                    <div class="dropdown">
                        <button type="button" class="btn px-0 dropdown-toggle d-flex align-items-center" style="gap: 6px; color: white; background: none; border: none; padding: 0.35rem 0.5rem;" data-bs-toggle="dropdown">
                            <?php
                            $avatarPath = 'img/';
                            $avatarFile = 'default-avatar.jpg';
                            if (!empty($userHeader['avatar']) && file_exists($avatarPath . $userHeader['avatar'])) {
                                $avatarFile = $userHeader['avatar'];
                            }
                            ?>
                            <img src="<?= $avatarPath . htmlspecialchars($avatarFile) ?>" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                            <span style="font-size: 0.9rem;" class="d-none d-md-inline">
                                <?= strlen($userHeader['username']) > 10 ? substr(htmlspecialchars($userHeader['username']), 0, 10) . '...' : htmlspecialchars($userHeader['username']) ?>
                            </span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="index.php?thongtin=<?= $_SESSION['user_id'] ?>">
                                <i class="fas fa-user mr-2"></i>Quản lý thông tin
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="?action=logout">
                                <i class="fas fa-sign-out-alt mr-2"></i>Đăng xuất
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="index.php?login" class="btn btn-outline-light d-flex align-items-center" style="height: 36px; font-size: 0.9rem; border-radius: 18px;">
                        <i class="fas fa-sign-in-alt mr-2"></i> ĐĂNG NHẬP
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- LINE 2: Danh mục sản phẩm -->
        <div class="row px-xl-5 py-2 header-line-2" id="header-line-2" style="min-height: 45px; align-items: center; border-top: 1px solid rgba(255,255,255,0.1);">
            <!-- Danh mục sản phẩm - Góc trái -->
            <div class="col-auto">
                <button type="button" class="btn d-flex align-items-center justify-content-between" id="category-toggle-btn" style="background: linear-gradient(135deg, #FFD333, #FFA500); color: #333; height: 40px; padding: 0 20px; border-radius: 20px; border: none; font-weight: 600; font-size: 0.9rem;">
                    <i class="fa fa-bars mr-2"></i>
                    <span>Danh mục sản phẩm</span>
                    <i class="fa fa-angle-down ml-2" id="category-toggle-icon"></i>
                </button>
                <nav class="collapse position-absolute navbar navbar-vertical navbar-light align-items-start p-0 bg-light" id="navbar-vertical" style="width: 280px; z-index: 999; display: none; margin-top: 5px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                    <div class="w-100">
                        <?php if (!empty($data)) : ?>
                            <?php foreach ($data as $parent) : ?>
                                <?php if (!empty($parent['con'])) : ?>
                                    <div class="nav-item dropdown dropright">
                                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
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
                                    <a href="index.php?category=<?= $parent['id_cha'] ?>" class="nav-item nav-link">
                                        <?= htmlspecialchars($parent['ten_cha']) ?>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </nav>
            </div>
            
            <!-- Menu items - Chính giữa -->
            <div class="col d-flex align-items-center justify-content-center" style="gap: 10px; flex-wrap: wrap;">
                <!-- Đăng bài -->
                <a href="javascript:void(0);" class="btn btn-dang-tin text-white d-flex align-items-center px-3" style="font-weight: 600; height: 40px; font-size: 0.9rem; background: linear-gradient(135deg, #FFD333, #FFA500); border: none; border-radius: 20px;">
                    <i class="fas fa-edit mr-2"></i> ĐĂNG BÀI
                </a>
                
                <a href="index.php?tin-nhan" class="nav-link d-flex align-items-center position-relative text-white" style="padding: 0.5rem 1rem; font-size: 0.9rem; border-radius: 20px; transition: all 0.3s;">
                    <i class="fas fa-envelope mr-2"></i> Tin nhắn
                    <?php if (isset($hasUnread) && $hasUnread): ?>
                        <span class="position-absolute" style="top: 5px; right: 5px; width: 8px; height: 8px; background: red; border-radius: 50%;"></span>
                    <?php endif; ?>
                </a>
                
                <a href="index.php?quan-ly-tin" class="nav-link d-flex align-items-center text-white" style="padding: 0.5rem 1rem; font-size: 0.9rem; border-radius: 20px; transition: all 0.3s;">
                    <i class="fas fa-tasks mr-2"></i> Quản lý bài viết
                </a>
                
                <a href="index.php?nap-tien" class="nav-link d-flex align-items-center text-white" style="padding: 0.5rem 1rem; font-size: 0.9rem; border-radius: 20px; transition: all 0.3s;">
                    <i class="fas fa-coins mr-2"></i> Nạp tiền
                </a>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="dropdown">
                    <a href="#" class="nav-link d-flex align-items-center dropdown-toggle text-white" data-bs-toggle="dropdown" style="padding: 0.5rem 1rem; font-size: 0.9rem; border-radius: 20px; transition: all 0.3s;">
                        <i class="fas fa-shopping-bag mr-2"></i> Đơn hàng
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="index.php?my-orders"><i class="fas fa-list mr-2"></i>Tất cả đơn hàng</a>
                        <a class="dropdown-item" href="index.php?my-orders&status=pending"><i class="fas fa-clock mr-2"></i>Chờ xác nhận</a>
                        <a class="dropdown-item" href="index.php?my-orders&status=confirmed"><i class="fas fa-check mr-2"></i>Đã xác nhận</a>
                        <a class="dropdown-item" href="index.php?my-orders&status=shipping"><i class="fas fa-truck mr-2"></i>Đang giao hàng</a>
                        <a class="dropdown-item" href="index.php?my-orders&status=delivered"><i class="fas fa-check-circle mr-2"></i>Đã giao hàng</a>
                        <a class="dropdown-item" href="index.php?my-orders&status=cancelled"><i class="fas fa-times mr-2"></i>Đã hủy</a>
                    </div>
                </div>
                
                <div class="dropdown">
                    <a href="#" class="nav-link d-flex align-items-center dropdown-toggle text-white" data-bs-toggle="dropdown" style="padding: 0.5rem 1rem; font-size: 0.9rem; border-radius: 20px; transition: all 0.3s;">
                        <i class="fas fa-video mr-2"></i> Livestream
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <h6 class="dropdown-header"><i class="fas fa-video mr-2"></i>Live Stream</h6>
                        <a class="dropdown-item" href="index.php?livestream"><i class="fas fa-list mr-2"></i>Tất cả Live Stream</a>
                        <?php if ($is_business_account): ?>
                        <a class="dropdown-item" href="index.php?create-livestream"><i class="fas fa-plus mr-2"></i>Tạo Livestream</a>
                        <a class="dropdown-item" href="index.php?my-livestreams"><i class="fas fa-user mr-2"></i>Livestream của tôi</a>
                        <?php endif; ?>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="index.php?livestream-packages">
                            <i class="fas fa-crown mr-2 text-warning"></i>
                            <?= $is_business_account ? 'Gia Hạn Gói Livestream' : 'Đăng Ký Gói Livestream' ?>
                        </a>
                        <a class="dropdown-item" href="index.php?livestream-package-history"><i class="fas fa-history mr-2"></i>Lịch Sử Mua Gói</a>
                        <?php if ($is_business_account): ?>
                        <div class="dropdown-divider"></div>
                        <h6 class="dropdown-header"><i class="fas fa-store mr-2"></i>Quản Lý Bán Hàng</h6>
                        <a class="dropdown-item" href="index.php?seller-dashboard"><i class="fas fa-chart-line mr-2"></i>Dashboard & Thống Kê</a>
                        <a class="dropdown-item" href="index.php?inventory-management"><i class="fas fa-boxes mr-2"></i>Quản Lý Tồn Kho</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="index.php?advanced-search"><i class="fas fa-search-plus mr-2"></i>Tìm Kiếm Nâng Cao</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Vị trí (Lọc) - Bên phải -->
            <div class="col-auto">
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center" type="button" id="locationFilter" data-bs-toggle="dropdown" aria-expanded="false" style="height: 40px; font-size: 0.9rem; border-radius: 20px; padding: 0.5rem 1rem;">
                        <i class="fas fa-map-marker-alt mr-2"></i>
                        <span id="location-text">Vị trí</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="locationFilter" style="min-width: 300px; max-height: 400px; overflow-y: auto;">
                        <li class="dropdown-header"><i class="fas fa-filter mr-2"></i>Lọc theo vị trí</li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <div class="px-3 py-2">
                                <select id="filter-province" class="form-control form-control-sm mb-2" onchange="loadFilterDistricts()">
                                    <option value="">Chọn Tỉnh/Thành phố</option>
                                </select>
                                <select id="filter-district" class="form-control form-control-sm" onchange="applyLocationFilter()">
                                    <option value="">Chọn Quận/Huyện</option>
                                </select>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0);" onclick="clearLocationFilter()">
                                <i class="fas fa-times mr-2"></i>Xóa bộ lọc
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- Header End -->

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

<!-- Script để xử lý đóng/mở danh mục và lọc vị trí -->
<script>
(function() {
    'use strict';
    
    // Xử lý menu danh mục
    function initCategoryMenu() {
        const categoryBtn = document.getElementById('category-toggle-btn');
        const categoryMenu = document.getElementById('navbar-vertical');
        const categoryIcon = document.getElementById('category-toggle-icon');
        
        if (!categoryBtn || !categoryMenu) {
            return;
        }
        
        function showMenu() {
            categoryMenu.style.display = 'block';
            categoryMenu.classList.add('show');
            categoryBtn.setAttribute('aria-expanded', 'true');
            if (categoryIcon) {
                categoryIcon.style.transform = 'rotate(180deg)';
            }
            setTimeout(function() {
                categoryMenu.style.opacity = '1';
            }, 10);
        }
        
        function hideMenu() {
            categoryMenu.style.opacity = '0';
            setTimeout(function() {
                categoryMenu.style.display = 'none';
                categoryMenu.classList.remove('show');
                categoryBtn.setAttribute('aria-expanded', 'false');
                if (categoryIcon) {
                    categoryIcon.style.transform = 'rotate(0deg)';
                }
            }, 200);
        }
        
        function toggleMenu() {
            if (categoryMenu.classList.contains('show')) {
                hideMenu();
            } else {
                showMenu();
            }
        }
        
        categoryMenu.style.transition = 'opacity 0.2s ease';
        categoryMenu.style.opacity = '0';
        
        categoryBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleMenu();
        });
        
        document.addEventListener('click', function(event) {
            const isClickInsideMenu = categoryMenu.contains(event.target);
            const isClickOnButton = categoryBtn.contains(event.target);
            
            if (!isClickInsideMenu && !isClickOnButton && categoryMenu.classList.contains('show')) {
                hideMenu();
            }
        });
        
        const menuItems = categoryMenu.querySelectorAll('.nav-link:not(.dropdown-toggle)');
        menuItems.forEach(function(item) {
            item.addEventListener('click', function(e) {
                if (!this.classList.contains('dropdown-toggle')) {
                    e.stopPropagation();
                    setTimeout(function() {
                        hideMenu();
                    }, 150);
                }
            });
        });
        
        const dropdownItems = categoryMenu.querySelectorAll('.dropdown-item');
        dropdownItems.forEach(function(item) {
            item.addEventListener('click', function() {
                setTimeout(function() {
                    hideMenu();
                }, 150);
            });
        });
        
        const dropdownMenus = categoryMenu.querySelectorAll('.dropdown-menu');
        dropdownMenus.forEach(function(menu) {
            menu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && categoryMenu.classList.contains('show')) {
                hideMenu();
            }
        });
    }
    
    // Xử lý lọc vị trí
    let filterProvinces = [];
    let filterDistricts = [];
    
    async function loadFilterProvinces() {
        try {
            const response = await fetch('https://provinces.open-api.vn/api/');
            filterProvinces = await response.json();
            
            const provinceSelect = document.getElementById('filter-province');
            const locationText = document.getElementById('location-text');
            
            if (provinceSelect) {
                provinceSelect.innerHTML = '<option value="">Chọn Tỉnh/Thành phố</option>';
                let hcmFound = false;
                
                filterProvinces.forEach(province => {
                    const option = document.createElement('option');
                    option.value = province.code;
                    option.textContent = province.name;
                    // Set Hồ Chí Minh (code 79) as default
                    if (province.code === 79) {
                        option.selected = true;
                        hcmFound = true;
                    }
                    provinceSelect.appendChild(option);
                });
                
                // Auto load districts for Hồ Chí Minh if selected
                if (hcmFound) {
                    provinceSelect.value = '79';
                    await loadFilterDistricts();
                    // Update location text
                    if (locationText) {
                        locationText.textContent = 'Hồ Chí Minh';
                    }
                }
            }
        } catch (error) {
            console.error('Lỗi khi tải danh sách tỉnh/thành phố:', error);
        }
    }
    
    async function loadFilterDistricts() {
        const provinceSelect = document.getElementById('filter-province');
        const districtSelect = document.getElementById('filter-district');
        
        if (!provinceSelect || !districtSelect || !provinceSelect.value) {
            if (districtSelect) {
                districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
            }
            return;
        }
        
        districtSelect.disabled = true;
        try {
            const response = await fetch(`https://provinces.open-api.vn/api/p/${provinceSelect.value}?depth=2`);
            const data = await response.json();
            
            districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
            data.districts.forEach(district => {
                const option = document.createElement('option');
                option.value = district.code;
                option.textContent = district.name;
                districtSelect.appendChild(option);
            });
            
            districtSelect.disabled = false;
        } catch (error) {
            console.error('Lỗi khi tải danh sách quận/huyện:', error);
            districtSelect.disabled = false;
        }
    }
    
    function applyLocationFilter() {
        const provinceSelect = document.getElementById('filter-province');
        const districtSelect = document.getElementById('filter-district');
        const locationText = document.getElementById('location-text');
        
        if (provinceSelect && districtSelect) {
            const provinceName = provinceSelect.selectedOptions[0]?.textContent || '';
            const districtName = districtSelect.selectedOptions[0]?.textContent || '';
            
            // Update location text
            if (locationText) {
                if (districtName) {
                    locationText.textContent = districtName + ', ' + provinceName;
                } else if (provinceName) {
                    locationText.textContent = provinceName;
                }
            }
            
            if (districtSelect.value) {
                // Lưu vào sessionStorage hoặc cookie để filter sản phẩm
                sessionStorage.setItem('filter_location', JSON.stringify({
                    province: provinceSelect.value,
                    province_name: provinceName,
                    district: districtSelect.value,
                    district_name: districtName
                }));
                
                // Reload trang với filter
                const url = new URL(window.location.href);
                url.searchParams.set('province', provinceSelect.value);
                url.searchParams.set('district', districtSelect.value);
                window.location.href = url.toString();
            }
        }
    }
    
    function clearLocationFilter() {
        sessionStorage.removeItem('filter_location');
        const url = new URL(window.location.href);
        url.searchParams.delete('province');
        url.searchParams.delete('district');
        window.location.href = url.toString();
    }
    
    // Vô hiệu hóa Bootstrap dropdown click - CHỈ DÙNG HOVER
    function disableDropdownClick() {
        const dropdownToggles = document.querySelectorAll('[data-bs-toggle="dropdown"]');
        
        dropdownToggles.forEach(toggle => {
            // Ngăn Bootstrap xử lý click event
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            });
            
            // Ngăn Bootstrap khởi tạo dropdown
            toggle.removeAttribute('data-bs-toggle');
            toggle.setAttribute('data-hover-dropdown', 'true');
        });
    }
    
    // Xử lý ẩn/hiện header line 2 khi cuộn
    function initHeaderScroll() {
        const headerLine1 = document.querySelector('.header-line-1');
        const headerLine2 = document.getElementById('header-line-2');
        if (!headerLine2) return;
        
        let lastScrollTop = 0;
        let scrollThreshold = 50; // Ngưỡng cuộn tối thiểu để trigger
        let isScrolling = false;
        
        window.addEventListener('scroll', function() {
            if (isScrolling) return;
            
            isScrolling = true;
            requestAnimationFrame(function() {
                const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
                
                // Nếu ở đầu trang (scrollTop < 100), luôn hiện header line 2
                if (currentScroll < 100) {
                    headerLine2.classList.remove('hidden');
                    if (headerLine1) headerLine1.classList.remove('no-border');
                    lastScrollTop = currentScroll;
                    isScrolling = false;
                    return;
                }
                
                // Kiểm tra hướng cuộn
                if (Math.abs(currentScroll - lastScrollTop) > scrollThreshold) {
                    if (currentScroll > lastScrollTop) {
                        // Cuộn xuống - Ẩn header line 2
                        headerLine2.classList.add('hidden');
                        if (headerLine1) headerLine1.classList.add('no-border');
                    } else {
                        // Cuộn lên - Hiện header line 2
                        headerLine2.classList.remove('hidden');
                        if (headerLine1) headerLine1.classList.remove('no-border');
                    }
                    lastScrollTop = currentScroll;
                }
                
                isScrolling = false;
            });
        });
    }
    
    // Khởi tạo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            disableDropdownClick();
            initCategoryMenu();
            loadFilterProvinces();
            initHeaderScroll();
        });
    } else {
        disableDropdownClick();
        initCategoryMenu();
        loadFilterProvinces();
        initHeaderScroll();
    }
    
    // Export functions
    window.loadFilterDistricts = loadFilterDistricts;
    window.applyLocationFilter = applyLocationFilter;
    window.clearLocationFilter = clearLocationFilter;
})();
</script>


