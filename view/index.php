
<?php
// if(isset($_SESSION['role']) && ($_SESSION['role'] == 1 ||  $_SESSION['role'] == 4 ||  $_SESSION['role'] == 5)){
//     header("Location: /admin");
// }
?>

<?php
include_once ("controller/cProduct.php");
include_once ("helpers/location_helper.php");
$p = new cProduct();

$DEFAULT_PROVINCE_CODE = 79;
$selectedProvinceCode = isset($_GET['province']) ? intval($_GET['province']) : $DEFAULT_PROVINCE_CODE;
$selectedDistrictCode = isset($_GET['district']) ? intval($_GET['district']) : 0;

if ($selectedProvinceCode <= 0) {
    $selectedProvinceCode = $DEFAULT_PROVINCE_CODE;
}

$selectedProvinceName = '';
$selectedDistrictName = '';

if ($selectedProvinceCode > 0) {
    [$selectedProvinceName, $selectedDistrictName] = resolveLocationNamesByCode(
        $selectedProvinceCode,
        $selectedDistrictCode > 0 ? $selectedDistrictCode : null
    );
}

if (empty($selectedProvinceName)) {
    [$selectedProvinceName] = resolveLocationNamesByCode($DEFAULT_PROVINCE_CODE);
    $selectedProvinceCode = $DEFAULT_PROVINCE_CODE;
}

$locationLabel = '';
if (!empty($selectedProvinceName)) {
    $locationLabel = $selectedDistrictName
        ? $selectedDistrictName . ', ' . $selectedProvinceName
        : $selectedProvinceName;
}

if (isset($_GET['keyword']) && !empty(trim($_GET['keyword']))) {
    $keyword = trim($_GET['keyword']);
    $products = $p->searchProducts($keyword, $selectedProvinceName, $selectedDistrictName);
} else {
    if (!empty($selectedProvinceName)) {
        $products = $p->getSanPhamMoiTheoTinh($selectedProvinceName, $selectedDistrictName);
} else {
    $products = $p->getSanPhamMoi();
    }
}

if (!empty($selectedProvinceName) && !empty($products)) {
    $products = array_values(array_filter($products, function ($product) use ($selectedProvinceName, $selectedDistrictName) {
        $address = $product['address'] ?? '';
        return addressMatchesLocation($address, $selectedProvinceName, $selectedDistrictName);
    }));
}

// Lấy sản phẩm hot nhất
$hotProducts = $p->getHotProducts(2);

// Lấy thông tin livestream
include_once("model/mLivestream.php");
$mLivestream = new mLivestream();
$livestreamCounts = $mLivestream->getLivestreamCounts();
$activeLivestreams = $mLivestream->getActiveLivestreamsForHomepage(10);

?>

<?php
include_once("model/mConnect.php");
$con = new connect();
$mysqli = $con->connect();

function getBanners() {
    global $mysqli;
    $banners = [];

    $sql = "SELECT * FROM banners WHERE status = 'active' ORDER BY display_order ASC";
    if ($result = $mysqli->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $banners[] = $row;
        }

        $result->free();
    }
    
    return $banners;
}


$banners = getBanners();
?>

<?php
include_once("view/header.php");
?>

<head>
    <style>
        /* Fix banner image clarity - remove all blur effects */
        .banner-image {
            filter: none !important;
            -webkit-filter: none !important;
            image-rendering: -webkit-optimize-contrast !important;
            image-rendering: crisp-edges !important;
        }
        .carousel-item {
            filter: none !important;
            -webkit-filter: none !important;
        }
        .carousel-inner {
            filter: none !important;
            -webkit-filter: none !important;
        }
        .carousel-caption {
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }
        
        /* Banner overlay - Lớp mờ đen phủ full ảnh */
        .banner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1;
        }
        
        /* Đảm bảo caption nằm trên overlay */
        .carousel-caption {
            position: absolute !important;
            z-index: 2 !important;
        }
        
        /* Custom carousel control buttons - smaller and more elegant */
        .carousel-control-prev,
        .carousel-control-next {
            width: 38px !important;
            height: 38px !important;
            background: rgba(255, 255, 255, 0.9) !important;
            border: 2px solid rgba(255, 215, 0, 0.8) !important;
            border-radius: 50% !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15) !important;
            opacity: 0.85 !important;
            transition: all 0.3s ease !important;
        }
        .carousel-control-prev:hover,
        .carousel-control-next:hover {
            opacity: 1 !important;
            background: rgba(255, 255, 255, 1) !important;
            border-color: #FFD333 !important;
            box-shadow: 0 4px 12px rgba(255, 215, 0, 0.4) !important;
            transform: translateY(-50%) scale(1.1) !important;
        }
        .carousel-control-prev {
            left: 15px !important;
        }
        .carousel-control-next {
            right: 15px !important;
        }
        
        .object-fit-cover {
            object-fit: cover;
        }

        /* Hero / Banner text */
        .banner-title {
            font-size: 2rem;
            font-weight: 700;
        }
        .banner-description {
            font-size: 1rem;
        }
        .btn-banner {
            padding: 0.55rem 1.4rem;
            font-weight: 600;
            border-radius: 999px;
        }
        .product-img-hover {
            position: relative;
            width: 100%;
            height: 150px;
            overflow: hidden;
            background-color: #f9f9f9;
            padding: 0;
            margin: 0;
            border-radius: 12px 12px 0 0;
        }

        .product-img-hover img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            object-position: center !important;
            transition: transform 0.3s ease;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
        }

        .product-img-hover:hover img {
            transform: scale(1.05);
        }

        .product-meta {
            font-size: 13px;
            color: #888;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .product-item .text-danger {
            font-size: 16px;
            font-weight: 600;
        }
        .category-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;       /* Không bị méo ảnh */
            object-position: center; /* Lấy tâm ảnh làm gốc */
            display: block;
        }

        /* Hover Effects */
        .hover-lift {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
        }

        .quick-category-card:hover .category-icon {
            transform: scale(1.1);
        }

        .category-icon {
            transition: transform 0.3s ease;
        }

        .live-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15) !important;
        }

        .featured-product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15) !important;
        }

        .product-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(255, 211, 51, 0.2) !important;
            border-color: #FFD333;
        }

        /* Badge Styles */
        .badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.375rem 0.75rem;
        }

        /* Section Titles */
        .section-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #3D464D;
            position: relative;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #FFD333, #FFA500);
            border-radius: 2px;
        }

        /* Button Styles */
        .btn {
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
        }
        
        /* Button với gradient vàng */
        .btn-gradient-yellow {
            background: linear-gradient(135deg, #FFD333, #FFA500);
            color: #333;
            font-weight: 600;
            border: none;
            box-shadow: 0 4px 15px rgba(255, 211, 51, 0.4);
        }
        
        .btn-gradient-yellow:hover {
            background: linear-gradient(135deg, #FFA500, #FFD333);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 211, 51, 0.6);
            color: #333;
        }

        /* Card Styles */
        .rounded-lg {
            border-radius: 12px !important;
        }
        
        /* Product card specific styles */
        .product-item {
            border-radius: 12px !important;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }
        
        .product-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(255, 211, 51, 0.2) !important;
            border-color: #FFD333;
        }
        
        .product-item .p-3 {
            border-radius: 0 0 12px 12px;
        }

        /* Animate pulse for live indicator */
        .animate-pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        /* Live stream specific styles */
        .live-card {
            transition: all 0.3s ease;
        }


        /* Gradient backgrounds */
        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        /* Ẩn scrollbar cho lives container */
        .lives-container::-webkit-scrollbar {
            display: none;
        }

        /* Hiệu ứng hover cho live cards */
        .live-card {
            position: relative;
            transition: all 0.3s ease;
        }

        .live-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15) !important;
        }

        .live-card:hover .live-overlay {
            opacity: 1;
            visibility: visible;
        }

        .live-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 15px;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 10;
        }

        .live-overlay h5 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
            line-height: 1.3;
            color: white !important;
            padding: 0 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .live-overlay p {
            font-size: 12px;
            text-align: center;
            line-height: 1.4;
            margin: 0;
            opacity: 0.9;
            color: white !important;
            padding: 0 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* CSS cho class img-live-stream */
        .img-live-stream {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            object-position: center !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            z-index: 1;
        }
        
        /* Đảm bảo container có kích thước cố định */
        .live-card .position-relative[style*="aspect-ratio"] {
            position: relative !important;
            display: block !important;
            overflow: hidden;
            width: 100% !important;
            height: 100% !important;
        }
        
        /* Đảm bảo live-card có kích thước đúng */
        .live-card {
            width: 100% !important;
            height: 100% !important;
            z-index: 1;
        }
        
        /* Đảm bảo ảnh chiếm hết container */
        .live-card .position-relative {
            width: 100% !important;
            height: 100% !important;
            min-height: 200px !important;
        }
        
        /* Ẩn khoảng trắng xung quanh ảnh */
        .live-card .position-relative img {
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
        }

        /* Improved contrast and readability */
        .text-muted {
            color: #6c757d !important;
        }

        .product-meta {
            color: #495057 !important;
            font-size: 13px;
        }

        .badge-light {
            background-color: #f8f9fa !important;
            color: #495057 !important;
            border: 1px solid #dee2e6;
        }

        .btn-outline-primary {
            color: #007bff !important;
            border-color: #007bff !important;
        }

        .btn-outline-primary:hover {
            background-color: #007bff !important;
            color: white !important;
        }

        .btn-outline-success {
            color: #28a745 !important;
            border-color: #28a745 !important;
        }

        .btn-outline-success:hover {
            background-color: #28a745 !important;
            color: white !important;
        }

        /* Better text contrast */
        .text-dark {
            color: #212529 !important;
        }

        .text-primary {
            color: #007bff !important;
        }

        /* Category card improvements */
        .cat-item h6 {
            color: #212529 !important;
            font-weight: 600;
        }

        .cat-item small {
            color: #6c757d !important;
        }

        /* Product card improvements */
        .product-item h6 {
            color: #212529 !important;
            font-weight: 600;
        }

        /* Filter buttons */
        .btn-group .btn {
            border-color: #007bff;
            color: #007bff;
        }

        .btn-group .btn.active {
            background-color: #007bff;
            color: white;
        }

        /* Button hover effects */
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 211, 51, 0.5) !important;
        }
        
        /* Container spacing improvements */
        .container-fluid {
            padding-top: 0.5rem;
            padding-bottom: 2rem;
        }
        
        .container-fluid.pt-4 {
            padding-top: 3rem !important;
        }
        
        .container-fluid.mb-4 {
            margin-bottom: 2rem !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .section-title {
                font-size: 1.5rem;
            }
            
            .section-title::after {
                width: 40px;
                height: 2px;
            }
            
            .quick-category-card {
                padding: 1rem !important;
            }
            
            .category-icon {
                width: 50px !important;
                height: 50px !important;
            }

            .live-card {
                margin-bottom: 1rem;
                width: calc(100% / 3 - 10px) !important;
                min-width: 150px;
            }
            
            .product-item-row {
                margin-bottom: 1rem;
            }
            
            /* Navigation buttons for lives on mobile */
            .lives-container + .position-absolute[style*="left: -15px"] {
                left: 5px !important;
                width: 35px !important;
                height: 35px !important;
            }
            
            .lives-container + .position-absolute[style*="right: -15px"] {
                right: 5px !important;
                width: 35px !important;
                height: 35px !important;
            }
        }

        @media (max-width: 576px) {
            .live-card .position-relative {
                max-height: 300px !important;
            }
            
            .section-title {
                font-size: 1.25rem;
            }
            
            .empty-state-icon {
                width: 60px !important;
                height: 60px !important;
            }
        }

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
            min-height: calc(100vh - 180px);
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
        
        /* Shadow style chuẩn */
        .product-item,
        .cat-item,
        .quick-category-card,
        .live-card,
        .product-card,
        .category-card {
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1) !important;
        }
        
        /* Hover shadow - Nâng cao hơn */
        .product-item:hover,
        .cat-item:hover,
        .quick-category-card:hover,
        .live-card:hover,
        .hover-lift:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
        }
        
        /* Panel boxes (Live Stream, Hot Products) */
        .p-3.rounded.mb-3,
        .p-3.rounded.shadow-sm,
        .bg-light.p-3.rounded {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
        }
        
        /* Banner carousel */
        #mainCarousel,
        .carousel.slide {
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.15) !important;
            border-radius: 12px;
            overflow: hidden;
        }
        
        /* Live Stream Panel - Gradient vàng - Shadow đậm hơn */
        .p-3.rounded.mb-3[style*="background: linear-gradient"] {
            box-shadow: 0 8px 30px rgba(255, 211, 51, 0.4) !important;
            transition: all 0.3s ease;
        }
        
        .p-3.rounded.mb-3[style*="background: linear-gradient"]:hover {
            box-shadow: 0 12px 40px rgba(255, 211, 51, 0.5) !important;
            transform: translateY(-3px);
        }
        
        /* Hot Products Panel - Background light - Shadow đậm hơn */
        .bg-light.p-3.rounded.shadow-sm {
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.12) !important;
            border: 1px solid #e0e0e0 !important;
            transition: all 0.3s ease;
        }
        
        .bg-light.p-3.rounded.shadow-sm:hover {
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.15) !important;
            transform: translateY(-3px);
        }
        
        /* About section box */
        .bg-white.p-4.rounded {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08) !important;
        }
        
        .container-fluid.pt-4,
        .container-fluid.pt-2 {
            background: transparent;
        }
        
        .empty-state-icon {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
        }
        
        .bg-light .empty-state-icon,
        [style*="background: linear-gradient"] .empty-state-icon {
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }
        
        .btn.px-4 {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12) !important;
        }
        
        .btn.px-4:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.18) !important;
        }
        
        .btn.btn-sm[style*="background-color: #3D464D"] {
            box-shadow: 0 4px 12px rgba(61, 70, 77, 0.35) !important;
        }
        
        /* Category toggle button */
        .btn.d-flex.align-items-center.justify-content-between {
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }
        
        /* Search button */
        .input-group-append .btn {
            box-shadow: 0 2px 8px rgba(255, 211, 51, 0.3);
        }
        
        /* Badge shadows */
        .badge.animate-pulse,
        .badge[style*="LIVE"] {
            box-shadow: 0 3px 10px rgba(255, 105, 180, 0.5);
        }
        
        .badge.badge-light {
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
        }
        
        /* Number badges trong Live/Hot panels */
        .bg-danger.rounded-circle,
        .bg-warning.rounded-circle {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        
        /* Icon circles trong Hot Products */
        .rounded-circle[style*="background: linear-gradient"] {
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.15) !important;
        }
        
        /* Product items trong Hot Products panel */
        .bg-light.p-3.rounded.shadow-sm .d-flex.align-items-center {
            transition: all 0.2s ease;
        }
        
        .bg-light.p-3.rounded.shadow-sm .d-flex.align-items-center:hover {
            transform: translateX(5px);
        }
        
        .bg-light.p-3.rounded.shadow-sm .d-flex.align-items-center:hover .rounded-circle {
            box-shadow: 0 5px 16px rgba(0, 0, 0, 0.2) !important;
        }
        
        /* Product image container */
        .product-img-hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        
        .product-img-hover:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }
        
        /* ========================================
           BORDER RADIUS - Hệ thống đồng bộ
        ======================================== */
        
        /* Container chính */
        .content-wrapper {
            border-radius: 16px !important;
        }
        
        /* Cards lớn - 12px */
        .rounded-lg,
        .product-item,
        .cat-item,
        .quick-category-card,
        .live-card,
        .product-card,
        .category-card {
            border-radius: 12px !important;
        }
        
        /* Panels - 12px */
        .p-3.rounded,
        .bg-light.p-3.rounded,
        .bg-white.p-4.rounded,
        #mainCarousel,
        .carousel.slide {
            border-radius: 12px !important;
        }
        
        /* Images trong cards - 8px */
        .product-item img,
        .cat-item .overflow-hidden,
        .live-card img,
        .product-img-hover {
            border-radius: 8px !important;
        }
        
        /* Badges - 6px */
        .badge:not(.rounded-circle) {
            border-radius: 6px !important;
        }
        
        /* Small badges (LIVE, viewers) - 4px */
        .badge.animate-pulse,
        .badge[style*="LIVE"],
        span.badge[style*="background-color"] {
            border-radius: 4px !important;
        }
        
        /* Buttons tròn */
        .rounded-circle,
        .btn.rounded-circle,
        .category-icon,
        .empty-state-icon,
        button[style*="border-radius: 50%"] {
            border-radius: 50% !important;
        }
        
        /* Buttons thường - 8px */
        .btn:not(.rounded-circle):not(.btn-link) {
            border-radius: 8px !important;
        }
        
        /* Carousel controls - 50% (tròn) */
        .carousel-control-prev,
        .carousel-control-next {
            border-radius: 50% !important;
        }
        
        /* Input groups - 8px */
        .form-control {
            border-radius: 8px !important;
        }
        
        /* Không override search bar trong header */
        .header-search .input-group .form-control:first-child {
            border-top-left-radius: 18px !important;
            border-bottom-left-radius: 18px !important;
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }
        
        .header-search .input-group-append .btn {
            border-top-right-radius: 18px !important;
            border-bottom-right-radius: 18px !important;
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
        }
        
        /* Input groups khác - 8px */
        .input-group:not(.header-search .input-group) .form-control:first-child {
            border-top-left-radius: 8px !important;
            border-bottom-left-radius: 8px !important;
        }
        
        .input-group-append:not(.header-search .input-group-append) .btn {
            border-top-right-radius: 8px !important;
            border-bottom-right-radius: 8px !important;
        }

        /* Container bên trong không cần padding thêm */
        .content-wrapper .container-fluid {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        
        /* Override px-xl-5 trong content-wrapper */
        .content-wrapper .px-xl-5 {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        
        /* Giữ lại padding cho row px-xl-5 nếu cần */
        .content-wrapper .row.px-xl-5 {
            margin-left: 0;
            margin-right: 0;
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
            
            /* Cards - giảm xuống 10px */
            .rounded-lg,
            .product-item,
            .cat-item,
            .quick-category-card,
            .live-card {
                border-radius: 10px !important;
            }
            
            .p-3.rounded,
            #mainCarousel {
                border-radius: 10px !important;
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
            
            /* Cards - giảm xuống 8px */
            .rounded-lg,
            .product-item,
            .cat-item,
            .quick-category-card,
            .live-card {
                border-radius: 8px !important;
            }
            
            .p-3.rounded,
            #mainCarousel {
                border-radius: 8px !important;
            }
            
            /* Images - 6px */
            .product-item img,
            .cat-item .overflow-hidden,
            .live-card img {
                border-radius: 6px !important;
            }
            
            /* Buttons - 6px */
            .btn:not(.rounded-circle) {
                border-radius: 6px !important;
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
            
            /* Cards - giảm xuống 6px */
            .rounded-lg,
            .product-item,
            .cat-item,
            .quick-category-card,
            .live-card {
                border-radius: 6px !important;
            }
            
            .p-3.rounded,
            #mainCarousel {
                border-radius: 6px !important;
            }
            
            /* Images - 4px */
            .product-item img,
            .cat-item .overflow-hidden,
            .live-card img {
                border-radius: 4px !important;
            }
            
            /* Buttons - 4px */
            .btn:not(.rounded-circle) {
                border-radius: 4px !important;
            }
            
            /* Badges - 3px */
            .badge:not(.rounded-circle) {
                border-radius: 3px !important;
            }
        }
</style>

</head>

<!-- Page Background Start -->
<div class="page-background">
    <!-- Content Wrapper Start -->
    <div class="content-wrapper">

<!-- Hero Section Start -->
<div class="container-fluid" style="padding-bottom: -32px !important;">
    <div class="row px-xl-5">
        <!-- Main Banner -->
        <div class="row px-xl-5">
            <div class="col-lg-8">
                <div id="mainCarousel" class="carousel slide carousel-fade mb-30 mb-lg-0" data-bs-ride="carousel" data-bs-interval="4000">
                    <?php if (!empty($banners)): ?>
                        <div class="carousel-indicators">
                            <?php foreach ($banners as $index => $banner): ?>
                                <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="<?= $index ?>" 
                                        class="<?= $index === 0 ? 'active' : '' ?>"></button>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="carousel-inner">
                            <?php foreach ($banners as $index => $banner): ?>
                                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?> position-relative">
                                    <img class="d-block w-100 banner-image" src="<?= htmlspecialchars($banner['image_url']) ?>" 
                                         alt="<?= htmlspecialchars($banner['title']) ?>" loading="lazy">
                                    <div class="banner-overlay"></div>
                                    <div class="carousel-caption d-none d-md-block">
                                        <h3 class="banner-title"><?= htmlspecialchars($banner['title']) ?></h3>
                                        <p class="banner-description"><?= htmlspecialchars($banner['description']) ?></p>
                                        <?php if (!empty($banner['button_text']) && !empty($banner['button_link'])): ?>
                                            <a href="<?= htmlspecialchars($banner['button_link']) ?>" class="btn btn-banner">
                                                <?= htmlspecialchars($banner['button_text']) ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    <?php else: ?>
                        <!-- Default banner when no banners in database -->
                        <div class="carousel-inner">
                            <div class="carousel-item active position-relative">
                                <img class="d-block w-100 banner-image" src="img/carousel-2.jpg?height=430&width=800" alt="Default Banner" loading="lazy">
                                <div class="banner-overlay"></div>
                                <div class="carousel-caption d-none d-md-block">
                                    <h3 class="banner-title">Chào mừng đến với Website</h3>
                                    <p class="banner-description">Khám phá các sản phẩm tuyệt vời của chúng tôi</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <!-- Live Stream Panel -->
        <div class="col-lg-4">
            <div class="p-3 rounded mb-3 shadow-sm" style="background: linear-gradient(135deg, #FFD333 0%, #FFA500 100%); min-height: 190px; border: 2px solid rgba(255,255,255,0.3);">
                <h5 class="font-weight-bold mb-3" style="color: #3D464D;">
                    <i class="fas fa-video mr-2"></i>Live Stream
                </h5>
                <div class="d-flex align-items-center mb-2">
                    <div class="bg-danger rounded-circle mr-3 animate-pulse" style="width: 8px; height: 8px;"></div>
                    <div>
                        <h6 class="mb-0 font-weight-bold" style="color: #3D464D;">
                            Đang live: <span class="badge badge-light text-dark"><?= $livestreamCounts['live_count'] ?></span>
                        </h6>
                        <small style="color: #3D464D; opacity: 0.8;">
                            <?php if (!empty($activeLivestreams)): ?>
                                <?= htmlspecialchars($activeLivestreams[0]['title'] ?? 'Livestream đang phát') ?>
                            <?php else: ?>
                                Chưa có livestream nào
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <div class="bg-warning rounded-circle mr-3" style="width: 8px; height: 8px;"></div>
                    <div>
                        <h6 class="mb-0 font-weight-bold" style="color: #3D464D;">
                            Sắp live: <span class="badge badge-light text-dark"><?= $livestreamCounts['upcoming_count'] ?></span>
                        </h6>
                        <small style="color: #3D464D; opacity: 0.8;">Livestream sắp diễn ra</small>
                    </div>
                </div>
                <a href="index.php?livestream" class="btn btn-sm mt-2 w-100" style="background-color: #3D464D; color: white; font-weight: 600; border: none; box-shadow: 0 2px 8px rgba(61, 70, 77, 0.3); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(61, 70, 77, 0.5)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(61, 70, 77, 0.3)';">
                    <i class="fas fa-play mr-1"></i>Xem tất cả Live
                </a>
            </div>
            
            <div class="bg-light p-3 rounded shadow-sm" style="min-height: 190px; border: 1px solid #e9ecef;">
                <h5 class="font-weight-bold mb-3 text-dark">
                    <i class="fas fa-fire mr-2 text-danger"></i>Hot hôm nay
                </h5>
                <?php if (!empty($hotProducts)): ?>
                    <?php foreach ($hotProducts as $index => $hotProduct): ?>
                <div class="d-flex align-items-center <?= $index == 0 ? 'mb-2' : '' ?>">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 text-white" style="width: 40px; height: 40px; background: linear-gradient(135deg, <?= $index == 0 ? '#FF6B6B, #FF8E8E' : '#FFD333, #FFA500' ?>); box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                        <i class="fas fa-<?= $index == 0 ? 'fire' : 'star' ?>"></i>
                    </div>
                    <div class="flex-grow-1">
                        <a href="index.php?detail&id=<?= $hotProduct['id'] ?>" class="text-decoration-none">
                            <h6 class="mb-0 font-weight-bold text-dark text-truncate" style="max-width: 120px;" title="<?= htmlspecialchars($hotProduct['title']) ?>">
                                <?= strlen($hotProduct['title']) > 15 ? substr($hotProduct['title'], 0, 15) . '...' : htmlspecialchars($hotProduct['title']) ?>
                            </h6>
                        </a>
                        <small class="text-muted"><?= number_format($hotProduct['price']) ?> đ</small>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-muted py-3">
                        <div class="empty-state-icon mb-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #FFD333, #FFA500); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin: 0 auto;">
                            <i class="fas fa-box fa-lg text-white"></i>
            </div>
                        <p class="mb-0">Chưa có sản phẩm hot</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<!-- Hero Section End -->

<!-- Quick Categories Start -->
<?php
// Lấy danh mục cha từ database
include_once "controller/cCategory.php";
$cCategory = new cCategory();
$parentCategories = $cCategory->index();

// Icon mapping cho các danh mục - Tất cả màu vàng
$categoryIcons = [
    'Xe cộ' => ['icon' => 'fa-car', 'color' => 'bg-warning'],
    'Đồ điện tử' => ['icon' => 'fa-laptop', 'color' => 'bg-warning'], // Laptop
    'Thời trang' => ['icon' => 'fa-tshirt', 'color' => 'bg-warning'],
    'Nội thất' => ['icon' => 'fa-couch', 'color' => 'bg-warning'],
    'Nhà cửa & Đời sống' => ['icon' => 'fa-home', 'color' => 'bg-warning'], // Ngôi nhà
    'Nhà cửa' => ['icon' => 'fa-home', 'color' => 'bg-warning'],
    'Đời sống' => ['icon' => 'fa-home', 'color' => 'bg-warning'],
    'Giải trí' => ['icon' => 'fa-gamepad', 'color' => 'bg-warning'],
    'Giải trí & Thể thao' => ['icon' => 'fa-gamepad', 'color' => 'bg-warning'], // Icon nắm tay chơi game
    'Nhạc cụ' => ['icon' => 'fa-music', 'color' => 'bg-warning'],
    'Thể thao' => ['icon' => 'fa-gamepad', 'color' => 'bg-warning'],
    'Bất động sản' => ['icon' => 'fa-building', 'color' => 'bg-warning'],
    'Việc làm' => ['icon' => 'fa-briefcase', 'color' => 'bg-warning'],
    'Dịch vụ' => ['icon' => 'fa-tools', 'color' => 'bg-warning'],
    'Khác' => ['icon' => 'fa-th-large', 'color' => 'bg-warning']
];

$defaultColor = 'bg-warning';

$featuredCategories = array_slice($parentCategories, 0, 5, true);
?>
<div class="container-fluid pt-2" style="padding-bottom: 0px;">
    <div class="row px-xl-5">
        <div class="col-12">
            <h4 class="font-weight-bold mb-3 pt-2 text-dark">
                <i class="fas fa-th-large text-primary mr-2"></i>Danh mục nổi bật
            </h4>
        </div>
        <?php if (!empty($featuredCategories)): ?>
            <?php foreach ($featuredCategories as $parentId => $parent): ?>
                <?php
                $categoryName = $parent['ten_cha'];
                $iconConfig = $categoryIcons[$categoryName] ?? null;
                $iconClass = $iconConfig ? $iconConfig['icon'] : 'fa-folder';
                $colorClass = $iconConfig ? $iconConfig['color'] : $defaultColor;
                
                // Lấy danh sách con để hiển thị
                $subCategories = $parent['con'] ?? [];
                $subText = '';
                if (!empty($subCategories)) {
                    $subNames = array_slice(array_column($subCategories, 'ten_con'), 0, 3);
                    $subText = implode(', ', $subNames);
                    if (count($subCategories) > 3) {
                        $subText .= '...';
                    }
                } else {
                    $subText = 'Xem thêm';
                }
                ?>
                <div class="col-6 col-md-4 col-lg-2 mb-3">
                    <a href="index.php?category=<?= $parentId ?>" class="text-decoration-none">
                        <div class="quick-category-card bg-white border rounded-lg p-3 text-center h-100 shadow-sm hover-lift">
                            <div class="category-icon <?= $colorClass ?> text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="fas <?= $iconClass ?> fa-lg"></i>
                            </div>
                            <h6 class="font-weight-bold mb-1 text-dark"><?= htmlspecialchars($categoryName) ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($subText) ?></small>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <!-- Danh mục "Khác" -->
        <div class="col-6 col-md-4 col-lg-2 mb-3">
            <a href="index.php?category=all" class="text-decoration-none">
                <div class="quick-category-card bg-white border rounded-lg p-3 text-center h-100 shadow-sm hover-lift">
                    <div class="category-icon bg-warning text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="fas fa-th-large fa-lg"></i>
                    </div>
                    <h6 class="font-weight-bold mb-1 text-dark">Khác</h6>
                    <small class="text-muted">Xem tất cả danh mục</small>
                </div>
            </a>
        </div>
    </div>
</div>
<!-- Quick Categories End -->

<!-- Lives Today Section Start -->
<div class="container-fluid">
    <div class="row px-xl-5">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="section-title position-relative mb-0" style="text-transform:none;">
                    <span class="pr-3">
                        <i class="fas fa-video text-danger mr-2"></i>Lives hôm nay
                    </span>
                </h2>
            </div>
        </div>
    </div>
    <div class="row px-xl-5">
        <div class="col-12">
            <div class="position-relative">
                <!-- Nút điều hướng trái -->
                <button class="btn position-absolute" style="left: -15px; top: 50%; transform: translateY(-50%); z-index: 10; border-radius: 50%; width: 45px; height: 45px; background: linear-gradient(135deg, #FFD333, #FFA500); color: #333; border: 2px solid white; box-shadow: 0 4px 15px rgba(255, 211, 51, 0.4); transition: all 0.3s ease;" onclick="scrollLives('left')" onmouseover="this.style.transform='translateY(-50%) scale(1.1)'; this.style.boxShadow='0 6px 20px rgba(255, 211, 51, 0.6)';" onmouseout="this.style.transform='translateY(-50%) scale(1)'; this.style.boxShadow='0 4px 15px rgba(255, 211, 51, 0.4)';">
                    <i class="fas fa-chevron-left"></i>
                </button>
                
                <!-- Nút điều hướng phải -->
                <button class="btn position-absolute" style="right: -15px; top: 50%; transform: translateY(-50%); z-index: 10; border-radius: 50%; width: 45px; height: 45px; background: linear-gradient(135deg, #FFD333, #FFA500); color: #333; border: 2px solid white; box-shadow: 0 4px 15px rgba(255, 211, 51, 0.4); transition: all 0.3s ease;" onclick="scrollLives('right')" onmouseover="this.style.transform='translateY(-50%) scale(1.1)'; this.style.boxShadow='0 6px 20px rgba(255, 211, 51, 0.6)';" onmouseout="this.style.transform='translateY(-50%) scale(1)'; this.style.boxShadow='0 4px 15px rgba(255, 211, 51, 0.4)';">
                    <i class="fas fa-chevron-right"></i>
                </button>
                
                <!-- Container cuộn ngang -->
                <div class="lives-container" style="overflow-x: auto; overflow-y: hidden; white-space: nowrap; padding: 10px 0; scroll-behavior: smooth; scrollbar-width: none; -ms-overflow-style: none;" id="livesScrollContainer">
                    <?php if (!empty($activeLivestreams)): ?>
                        <?php foreach ($activeLivestreams as $livestream): ?>
                            <?php
                            // Xử lý ảnh livestream
                            $livestreamImage = !empty($livestream['image']) ? $livestream['image'] : 'default-live.jpg';
                            if (!file_exists('img/' . $livestreamImage)) {
                                $livestreamImage = 'default-live.jpg';
                            }
                            
                            // Format số lượng viewers
                            $viewerCount = $livestream['current_viewers'] ?? 0;
                            $viewerText = $viewerCount > 1000 ? number_format($viewerCount/1000, 1) . 'K' : $viewerCount;
                            
                            // Rút gọn title và description
                            $title = htmlspecialchars($livestream['title'] ?? 'Livestream');
                            $shortTitle = strlen($title) > 50 ? substr($title, 0, 50) . '...' : $title;
                            $description = htmlspecialchars($livestream['description'] ?? '');
                            $shortDesc = strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
                            ?>
                    <div class="d-inline-block" style="width: calc(100% / 6 - 10px); margin-right: 10px; vertical-align: top;">
                                <a href="index.php?watch&id=<?= $livestream['id'] ?>" class="text-decoration-none">
                        <div class="live-card bg-white border rounded-lg overflow-hidden shadow-sm">
                            <div class="position-relative" style="aspect-ratio: 9/16; max-height: 200px;">
                                            <img src="img/<?= $livestreamImage ?>" 
                                                 alt="<?= $title ?>" 
                                                 class="img-live-stream" 
                                                 loading="lazy">
                                <!-- Status Badge - Top Left -->
                                <div class="position-absolute" style="top: 8px; left: 8px; z-index: 20;">
                                    <span class="badge animate-pulse" style="background-color: #FF69B4; color: white; padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: 600;">
                                        <i class="fas fa-circle text-white mr-1" style="font-size: 8px;"></i>LIVE
                                    </span>
                                </div>
                                <!-- Viewer Count - Top Right -->
                                <div class="position-absolute" style="top: 8px; right: 8px; z-index: 20;">
                                    <span class="badge" style="background-color: rgba(0,0,0,0.7); color: white; padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: 600;">
                                                    <i class="fas fa-user mr-1"></i><?= $viewerText ?>
                                    </span>
                                </div>
                                <!-- Overlay hiển thị khi hover -->
                                <div class="live-overlay">
                                                <h5><?= $shortTitle ?></h5>
                                                <p><?= $shortDesc ?: 'Livestream đang phát trực tiếp' ?></p>
                                </div>
                            </div>
                        </div>
                                </a>
                    </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Empty state khi không có livestream -->
                        <div class="d-inline-block w-100 text-center py-5">
                            <div class="empty-state-icon mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, #FFD333, #FFA500); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin: 0 auto;">
                                <i class="fas fa-video fa-2x text-white"></i>
                                </div>
                            <h5 class="text-muted mb-2">Chưa có livestream</h5>
                            <p class="text-muted mb-3">Hiện tại chưa có livestream nào đang phát</p>
                            <a href="index.php?livestream" class="btn px-4" style="background: linear-gradient(135deg, #FFD333, #FFA500); color: #333; font-weight: 600; border: none; box-shadow: 0 4px 15px rgba(255, 211, 51, 0.4); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(255, 211, 51, 0.6)'; this.style.background='linear-gradient(135deg, #FFA500, #FFD333)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(255, 211, 51, 0.4)'; this.style.background='linear-gradient(135deg, #FFD333, #FFA500)';">
                                <i class="fas fa-search mr-2"></i>Xem tất cả livestream
                            </a>
                                </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Lives Today Section End -->

<!-- Khám phá danh mục -->
<?php
include_once "controller/cCategory.php";
$cCategory = new cCategory();
$categories = $cCategory->showCategoriesWithCount();
$images = [
    'dienthoai.jpg',    // Âm thanh (loa, tai nghe, micro)
    'laptop.jpg',       // Bộ sưu tập  
    'giaydep.jpg',      // Chuồng & Lồng
    'dungcubep.jpg',    // Công cụ & Máy móc cũ
    'dungcutrangtri.jpg', // Dụng cụ bếp
    'ao.jpg',           // Giày dép
    'maytinhbang.jpg',  // Laptop
    'thietbithongminh.jpg', // Linh kiện điện tử
    'mayanh.jpg',       // Máy ảnh & Máy quay
    'thietbichoigame.jpg', // Máy chiếu
    'oto.jpg',          // Máy in & Máy photocopy
    'xemay.jpg',        // Máy tính bảng
    'banghe.jpg',       // Nội thất
    'nhaccu.jpg',       // Nhạc cụ
    'mu.jpg',           // Phụ kiện
    'phutungxe.jpg',    // Phụ tùng xe
    'quan.jpg',         // Quần áo
    'dothethao.jpg',    // Thể thao
    'tuke.jpg',         // Tủ kệ
    'tuixach.jpg',      // Túi xách
    'xedien.jpg'        // Xe điện
];
$i = 0;
?>

<div class="container-fluid">
    <div class="row px-xl-5">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="section-title position-relative mb-0" style="text-transform:none;">
                    <span class="pr-3">
                        <i class="fas fa-compass text-primary mr-2"></i>Khám phá danh mục
                    </span>
    </h2>
                
            </div>
        </div>
    </div>
    <div class="row px-xl-5 " id="category-list">
        <?php foreach ($categories as $cat): ?>
            <?php $img = $images[$i % count($images)]; ?>
            <div class="col-6 col-md-4 col-lg-2 pb-1 category-item <?= $i >= 12 ? 'd-none' : '' ?>">
                <a class="text-decoration-none" href="index.php?category=<?= $cat['id_loai'] ?>">
                    <div class="cat-item img-zoom d-flex align-items-center mb-4 bg-white border rounded-lg p-3 shadow-sm hover-lift">
                        <div class="overflow-hidden rounded" style="width: 80px; height: 80px;">
                            <img class="img-fluid" src="img/<?= $img ?>" alt="<?= htmlspecialchars($cat['category_name']) ?>" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
                        </div>
                        <div class="flex-fill pl-3">
                            <?php 
                            $categoryName = htmlspecialchars($cat['category_name']);
                            $displayName = mb_strlen($categoryName, 'UTF-8') > 9 ? mb_substr($categoryName, 0, 9, 'UTF-8') . '...' : $categoryName;
                            ?>
                            <h6 class="font-weight-bold mb-1 text-dark" title="<?= $categoryName ?>"><?= $displayName ?></h6>
                            <small class="text-muted"><?= $cat['quantity'] > 0 ? $cat['quantity'] . ' sản phẩm' : 'Chưa có sản phẩm' ?></small>
                        </div>
                    </div>
                </a>
            </div>
            <?php $i++; ?>
        <?php endforeach; ?>
    </div>

    <!-- Nút Xem thêm / Thu gọn -->
    <?php if (count($categories) > 12): ?>
    <div class="text-center mt-3">
        <button id="show-more-btn" class="btn px-4" style="background: linear-gradient(135deg, #FFD333, #FFA500); color: #333; font-weight: 600; border: none; box-shadow: 0 4px 15px rgba(255, 211, 51, 0.4); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(255, 211, 51, 0.6)'; this.style.background='linear-gradient(135deg, #FFA500, #FFD333)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(255, 211, 51, 0.4)'; this.style.background='linear-gradient(135deg, #FFD333, #FFA500)';">Xem thêm</button>
        <button id="collapse-btn" class="btn px-4 d-none" style="background: linear-gradient(135deg, #FFD333, #FFA500); color: #333; font-weight: 600; border: none; box-shadow: 0 4px 15px rgba(255, 211, 51, 0.4); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(255, 211, 51, 0.6)'; this.style.background='linear-gradient(135deg, #FFA500, #FFD333)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(255, 211, 51, 0.4)'; this.style.background='linear-gradient(135deg, #FFD333, #FFA500)';">Thu gọn</button>
    </div>
    <?php endif; ?>
</div>

<!-- Products Start -->
<div class="container-fluid pb-3">
    <div class="row px-xl-5">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="section-title position-relative mb-0" style="text-transform:none;">
                    <span class="pr-3">
                        <i class="fas fa-user-friends text-success mr-2"></i>Tin đăng mới nhất
                    </span>
    </h2>
                <div id="latest-products-badge-wrapper">
                    <?php if (!empty($locationLabel)): ?>
                        <span class="badge badge-pill px-3 py-2" style="background: rgba(40, 167, 69, 0.15); color: #218838; font-weight: 600;">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            <?= htmlspecialchars($locationLabel) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row px-xl-5" id="product-list">
        <?php if (!empty($products)): ?>
        <?php foreach ($products as $index => $sp): ?>
            <div class="col-6 col-sm-4 col-md-3 col-lg-2 pb-3 product-item-row <?= $index >= 18 ? 'd-none' : '' ?>">
                <div class="product-item bg-white border rounded-lg h-100 shadow-sm hover-lift">
                    <div class="product-img-hover position-relative">
                            <?php 
                            $productImage = !empty($sp['anh_dau']) ? $sp['anh_dau'] : 'default-product.jpg';
                            if (!file_exists('img/' . $productImage)) {
                                $productImage = 'default-product.jpg';
                            }
                            ?>
                            <img src="img/<?= htmlspecialchars($productImage) ?>" 
                                 alt="<?= htmlspecialchars($sp['title']) ?>" 
                                 class="img-fluid w-100" 
                                 style="height: 150px; object-fit: cover; width: 100% !important; min-width: 100%; max-width: 100%;" 
                                 loading="lazy"
                                 onerror="this.src='img/default-product.jpg'">
                        <div class="position-absolute top-0 right-0 m-2">
                            <button class="btn btn-sm btn-light rounded-circle">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                        <div class="position-absolute bottom-0 left-0 m-2">
                                <span class="badge text-white" style="background: linear-gradient(135deg, #28a745, #20c997); box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);">
                                <i class="fas fa-clock mr-1"></i>Mới
                            </span>
                        </div>
                    </div>
                    <div class="p-3">
                        <a class="h6 text-decoration-none text-truncate d-block mb-2 text-dark" href="index.php?detail&id=<?= $sp['id'] ?>">
                            <?= htmlspecialchars($sp['title']) ?>
                        </a>
                            <div class="product-meta mb-2 small text-muted"><?= htmlspecialchars($sp['description'] ?? '') ?></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-primary font-weight-bold"><?= number_format($sp['price']) ?> đ</span>
                            <div class="d-flex">
                                <button class="btn btn-outline-primary btn-sm mr-1" onclick="window.location.href='index.php?tin-nhan&to=<?= $sp['user_id'] ?>&product_id=<?= $sp['id'] ?>'">
                                    <i class="fas fa-comment"></i>
                                </button>
                                    <button class="btn btn-outline-success btn-sm" onclick="alert('Số điện thoại: <?= htmlspecialchars($sp['phone'] ?? 'N/A') ?>')">
                                    <i class="fas fa-phone"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php else: ?>
            <!-- Empty state khi không có sản phẩm -->
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="empty-state-icon mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, #FFD333, #FFA500); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin: 0 auto;">
                        <i class="fas fa-box-open fa-2x text-white"></i>
                    </div>
                    <h4 class="text-muted mb-2">Chưa có sản phẩm nào</h4>
                    <?php if (!empty($locationLabel)): ?>
                        <p class="text-muted mb-3">Hiện tại chưa có sản phẩm nào được đăng bán tại <?= htmlspecialchars($locationLabel) ?>.</p>
                        <button class="btn btn-outline-secondary" onclick="clearLocationFilter()">
                            <i class="fas fa-times mr-1"></i>Xóa bộ lọc vị trí
                        </button>
                    <?php else: ?>
                    <p class="text-muted">Hiện tại chưa có sản phẩm nào được đăng bán.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Nút Xem thêm / Thu gọn -->
    <?php if (true): ?>
<div class="text-center mt-2">
    <button id="show-more-btn2" class="btn px-4" style="background: linear-gradient(135deg, #FFD333, #FFA500); color: #333; font-weight: 600; border: none; box-shadow: 0 4px 15px rgba(255, 211, 51, 0.4); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(255, 211, 51, 0.6)'; this.style.background='linear-gradient(135deg, #FFA500, #FFD333)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(255, 211, 51, 0.4)'; this.style.background='linear-gradient(135deg, #FFD333, #FFA500)';">Xem thêm</button>
    <button id="collapse-btn2" class="btn px-4 d-none" style="background: linear-gradient(135deg, #FFD333, #FFA500); color: #333; font-weight: 600; border: none; box-shadow: 0 4px 15px rgba(255, 211, 51, 0.4); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(255, 211, 51, 0.6)'; this.style.background='linear-gradient(135deg, #FFA500, #FFD333)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(255, 211, 51, 0.4)'; this.style.background='linear-gradient(135deg, #FFD333, #FFA500)';">Thu gọn</button>
</div>
<?php endif; ?>
</div>
<!-- Products End -->

<!-- About Section -->
<div class="container-fluid mt-2" style="padding-bottom: 0px;">
    <div class="row px-xl-5">
        <div class="col-12">
            <div class="bg-white p-4 rounded shadow-sm">
                <h5 class="font-weight-bold mb-3">
                    <i class="fas fa-video text-primary mr-2"></i>Chợ Việt – Nền Tảng Livestream Bán Hàng C2C Hàng Đầu
                </h5>
                <div class="about-content-visible">
                <p>
                    <strong>Chợ Việt</strong> là nền tảng livestream bán hàng kết nối người bán và người mua đồ cũ trực tuyến theo mô hình <strong>C2C (Consumer to Consumer)</strong>. Với tính năng livestream độc đáo, người bán có thể <strong>trực tiếp giới thiệu sản phẩm</strong> và tương tác với khách hàng trong thời gian thực.
                </p>
                <p>
                    Tại Chợ Việt, bạn có thể <strong>livestream bán hàng hoàn toàn miễn phí</strong>, chia sẻ hình ảnh thực tế và mô tả chi tiết sản phẩm trực tiếp với khách hàng. Tất cả live stream sẽ được <strong>kiểm duyệt nội dung</strong> để đảm bảo chất lượng và tuân thủ chính sách cộng đồng.
                </p>
                </div>
                <div class="about-content-hidden" style="display: none;">
                <p>
                    <strong>Tính năng livestream độc đáo:</strong>
                    <ul class="mb-2">
                        <li><strong>Live bán hàng:</strong> Trực tiếp giới thiệu sản phẩm với khách hàng</li>
                        <li><strong>Tương tác real-time:</strong> Chat, hỏi đáp trực tiếp trong live</li>
                        <li><strong>Đặt hàng ngay:</strong> Khách hàng có thể mua ngay trong live stream</li>
                        <li><strong>Lưu trữ video:</strong> Xem lại các live stream đã kết thúc</li>
                        <li><strong>Thông báo live:</strong> Nhắc nhở khách hàng khi có live mới</li>
                    </ul>
                </p>
                <p>
                    Hệ thống phân loại sản phẩm rõ ràng hỗ trợ bạn dễ dàng tìm kiếm theo nhu cầu với các nhóm chính:
                    <ul class="mb-2">
                        <li><strong>Xe cộ:</strong> Xe máy, Ô tô, Xe điện, Phụ tùng xe</li>
                        <li><strong>Đồ điện tử:</strong> Laptop, Điện thoại, Máy tính bảng, Máy ảnh, Thiết bị thông minh</li>
                        <li><strong>Thời trang & Phụ kiện:</strong> Quần, Áo, Túi xách, Dép, Mũ</li>
                        <li><strong>Nội thất & Trang trí:</strong> Bàn ghế, Tủ kệ, Dụng cụ bếp, Dụng cụ trang trí</li>
                        <li><strong>Giải trí & Thể thao:</strong> Nhạc cụ, Đồ thể thao, Thiết bị chơi game</li>
                    </ul>
                </p>
                <p>
                    Ngoài việc livestream, người dùng còn có thể <strong>trò chuyện trực tiếp qua hệ thống tin nhắn nội bộ</strong> để thương lượng giá, hỏi thêm thông tin hoặc hẹn gặp. Sau giao dịch, bạn có thể để lại <strong>đánh giá người bán</strong>, giúp xây dựng một cộng đồng giao dịch minh bạch, đáng tin cậy.
                </p>
                <p>
                    Đừng để những món đồ cũ phủ bụi – hãy để <strong>Chợ Việt</strong> giúp bạn biến chúng thành giá trị cho người khác thông qua livestream. Rất đơn giản, bạn chỉ cần tạo live stream, chụp hình sản phẩm và bán hàng trực tiếp.
                </p>
                <p class="text-muted font-italic"><i class="fas fa-video mr-2 text-danger"></i>Livestream bán hàng – Cách mới để bán đồ cũ hiệu quả cùng Chợ Việt.</p>
                </div>
                <div class="text-center mt-3">
                    <button id="about-show-more-btn" class="btn px-4" style="background: linear-gradient(135deg, #FFD333, #FFA500); color: #333; font-weight: 600; border: none; box-shadow: 0 4px 15px rgba(255, 211, 51, 0.4); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(255, 211, 51, 0.6)'; this.style.background='linear-gradient(135deg, #FFA500, #FFD333)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(255, 211, 51, 0.4)'; this.style.background='linear-gradient(135deg, #FFD333, #FFA500)';">Xem thêm</button>
                    <button id="about-collapse-btn" class="btn px-4 d-none" style="background: linear-gradient(135deg, #FFD333, #FFA500); color: #333; font-weight: 600; border: none; box-shadow: 0 4px 15px rgba(255, 211, 51, 0.4); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(255, 211, 51, 0.6)'; this.style.background='linear-gradient(135deg, #FFA500, #FFD333)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(255, 211, 51, 0.4)'; this.style.background='linear-gradient(135deg, #FFD333, #FFA500)';">Thu gọn</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- About Section End -->

    </div>
    <!-- Content Wrapper End -->
</div>
<!-- Page Background End -->
    </div>
<?php
    include_once("view/footer.php");
?>

<!-- phần khám phá danh mục -->
<script>
const showMoreBtn = document.getElementById('show-more-btn');
const collapseBtn = document.getElementById('collapse-btn');

if (showMoreBtn && collapseBtn) {
    showMoreBtn.addEventListener('click', function () {
        document.querySelectorAll('.category-item.d-none').forEach(item => item.classList.remove('d-none'));
        showMoreBtn.classList.add('d-none');
        collapseBtn.classList.remove('d-none');
    });

    collapseBtn.addEventListener('click', function () {
        document.querySelectorAll('.category-item').forEach((item, index) => {
            if (index >= 12) item.classList.add('d-none');
        });
        collapseBtn.classList.add('d-none');
        showMoreBtn.classList.remove('d-none');
        window.scrollTo({ top: document.getElementById('category-list').offsetTop - 100, behavior: 'smooth' });
    });
}
</script>

<script>
function initProductShowMore() {
    const showMoreBtn2 = document.getElementById('show-more-btn2');
    const collapseBtn2 = document.getElementById('collapse-btn2');
    const productItems = document.querySelectorAll('.product-item-row');

    if (!showMoreBtn2 || !collapseBtn2 || productItems.length === 0) {
        if (showMoreBtn2) showMoreBtn2.classList.add('d-none');
        if (collapseBtn2) collapseBtn2.classList.add('d-none');
        return;
    }

    let visibleCount = Math.min(18, productItems.length);
    const increment = 6;

    const updateVisibility = () => {
        productItems.forEach((item, index) => {
            item.classList.toggle('d-none', index >= visibleCount);
        });

        if (visibleCount >= productItems.length) {
            showMoreBtn2.classList.add('d-none');
            collapseBtn2.classList.remove('d-none');
        } else {
            showMoreBtn2.classList.remove('d-none');
            collapseBtn2.classList.add('d-none');
        }
    };

    showMoreBtn2.onclick = () => {
        visibleCount = Math.min(visibleCount + increment, productItems.length);
        updateVisibility();
    };

    collapseBtn2.onclick = () => {
        visibleCount = Math.min(18, productItems.length);
        updateVisibility();
        window.scrollTo({ top: document.getElementById('product-list').offsetTop - 100, behavior: 'smooth' });
    };

    updateVisibility();
}

document.addEventListener('DOMContentLoaded', initProductShowMore);
</script>

<script>
window.updateHomepageProducts = async function(provinceCode, provinceName) {
    try {
        const url = new URL(window.location.href);
        if (provinceCode) {
            url.searchParams.set('province', provinceCode);
        } else {
            url.searchParams.delete('province');
        }

        const response = await fetch(url.toString(), {
            headers: { 'X-Requested-With': 'fetch' },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const htmlText = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(htmlText, 'text/html');

        const newProductList = doc.querySelector('#product-list');
        const currentProductList = document.querySelector('#product-list');
        if (newProductList && currentProductList) {
            currentProductList.innerHTML = newProductList.innerHTML;
        }

        const newBadgeWrapper = doc.getElementById('latest-products-badge-wrapper');
        const badgeWrapper = document.getElementById('latest-products-badge-wrapper');
        if (newBadgeWrapper && badgeWrapper) {
            badgeWrapper.innerHTML = newBadgeWrapper.innerHTML;
        }

        initProductShowMore();
        window.history.replaceState({}, '', url.toString());
    } catch (error) {
        console.error('Lỗi cập nhật sản phẩm:', error);
        const fallbackUrl = new URL(window.location.href);
        if (provinceCode) {
            fallbackUrl.searchParams.set('province', provinceCode);
        } else {
            fallbackUrl.searchParams.delete('province');
        }
        window.location.href = fallbackUrl.toString();
    }
};
</script>

<!-- Phần sản phầm -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    return;
    const showMoreBtn2 = document.getElementById('show-more-btn2');
    const collapseBtn2 = document.getElementById('collapse-btn2');
    const productItems = document.querySelectorAll('.product-item-row');
    let visibleCount = 18;
    const increment = 6;

    if (showMoreBtn2 && collapseBtn2) {
        showMoreBtn2.addEventListener('click', function () {
            const total = productItems.length;
            const nextVisible = Math.min(visibleCount + increment, total);

            productItems.forEach((item, index) => {
                if (index < nextVisible) item.classList.remove('d-none');
            });

            visibleCount = nextVisible;

            if (visibleCount >= total) {
                showMoreBtn2.classList.add('d-none');
                collapseBtn2.classList.remove('d-none');
            }
        });

        collapseBtn2.addEventListener('click', function () {
            productItems.forEach((item, index) => {
                item.classList.toggle('d-none', index >= 18);
            });
            visibleCount = 18;
            showMoreBtn2.classList.remove('d-none');
            collapseBtn2.classList.add('d-none');
            window.scrollTo({ top: document.getElementById('product-list').offsetTop - 100, behavior: 'smooth' });
        });
    }
});

// Chức năng cuộn ngang cho Lives hôm nay
function scrollLives(direction) {
    const container = document.getElementById('livesScrollContainer');
    const containerWidth = container.clientWidth;
    const scrollAmount = containerWidth / 3; // Cuộn nửa màn hình (3 cards)
    
    if (direction === 'left') {
        container.scrollBy({
            left: -scrollAmount,
            behavior: 'smooth'
        });
    } else if (direction === 'right') {
        container.scrollBy({
            left: scrollAmount,
            behavior: 'smooth'
        });
    }
}

// Cuộn bằng chuột (mouse wheel)
document.getElementById('livesScrollContainer').addEventListener('wheel', function(e) {
    e.preventDefault();
    const containerWidth = this.clientWidth;
    const scrollAmount = containerWidth / 2; // Cuộn nửa màn hình (3 cards)
    
    if (e.deltaY > 0) {
        // Cuộn xuống = cuộn phải
        this.scrollBy({
            left: scrollAmount,
            behavior: 'smooth'
        });
    } else {
        // Cuộn lên = cuộn trái
        this.scrollBy({
            left: -scrollAmount,
            behavior: 'smooth'
        });
    }
});

// Phần About Section - Xem thêm / Thu gọn
document.addEventListener('DOMContentLoaded', function () {
    const aboutShowMoreBtn = document.getElementById('about-show-more-btn');
    const aboutCollapseBtn = document.getElementById('about-collapse-btn');
    const aboutContentHidden = document.querySelector('.about-content-hidden');

    if (aboutShowMoreBtn && aboutCollapseBtn && aboutContentHidden) {
        aboutShowMoreBtn.addEventListener('click', function () {
            aboutContentHidden.style.display = 'block';
            aboutShowMoreBtn.classList.add('d-none');
            aboutCollapseBtn.classList.remove('d-none');
            // Cuộn mượt đến phần nội dung mới hiển thị
            setTimeout(() => {
                aboutContentHidden.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 100);
        });

        aboutCollapseBtn.addEventListener('click', function () {
            aboutContentHidden.style.display = 'none';
            aboutCollapseBtn.classList.add('d-none');
            aboutShowMoreBtn.classList.remove('d-none');
            // Cuộn mượt về phần đầu
            setTimeout(() => {
                document.querySelector('.about-content-visible').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 100);
        });
    }
});

</script>