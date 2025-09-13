
<?php
include_once ("controller/cProduct.php");
$p = new cProduct();
if (isset($_GET['keyword']) && !empty(trim($_GET['keyword']))) {
    $keyword = trim($_GET['keyword']);
    $products = $p->searchProducts($keyword);
} else {
    $products = $p->getSanPhamMoi();
}

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
        .object-fit-cover {
            object-fit: cover;
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
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15) !important;
        }

        /* Badge Styles */
        .badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.375rem 0.75rem;
        }

        /* Section Titles */
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
        }

        /* Button Styles */
        .btn {
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        /* Card Styles */
        .rounded-lg {
            border-radius: 12px !important;
        }
        
        /* Product card specific styles */
        .product-item {
            border-radius: 12px !important;
            overflow: hidden;
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

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .section-title {
                font-size: 1.25rem;
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
            }
        }

        @media (max-width: 576px) {
            .live-card .position-relative {
                max-height: 300px !important;
            }
        }
</style>

</head>
<!-- Hero Section Start -->
<div class="container-fluid mb-4">
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
                                         alt="<?= htmlspecialchars($banner['title']) ?>">
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
                                <img class="d-block w-100 banner-image" src="img/carousel-2.jpg?height=430&width=800" alt="Default Banner">
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
            <div class="bg-primary p-3 rounded mb-3" style="min-height: 190px;">
                <h5 class="font-weight-bold mb-3" style="color: #3D464D;">
                    <i class="fas fa-video mr-2"></i>Live Stream
                </h5>
                <div class="d-flex align-items-center mb-2">
                    <div class="bg-danger rounded-circle mr-3 animate-pulse" style="width: 8px; height: 8px;"></div>
                    <div>
                        <h6 class="mb-0 font-weight-bold" style="color: #3D464D;">Đang live: <span class="badge badge-light text-dark">12</span></h6>
                        <small style="color: #3D464D; opacity: 0.8;">Shop điện thoại cũ</small>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <div class="bg-warning rounded-circle mr-3" style="width: 8px; height: 8px;"></div>
                    <div>
                        <h6 class="mb-0 font-weight-bold" style="color: #3D464D;">Sắp live: <span class="badge badge-light text-dark">8</span></h6>
                        <small style="color: #3D464D; opacity: 0.8;">Thời trang nam nữ</small>
                    </div>
                </div>
                <a href="index.php?livestream=all" class="btn btn-sm mt-2 w-100" style="background-color: #3D464D; color: white; font-weight: 600; border: none;">
                    <i class="fas fa-play mr-1"></i>Xem tất cả
                </a>
            </div>
            
            <div class="bg-light p-3 rounded" style="height: 190px;">
                <h5 class="font-weight-bold mb-3 text-dark">
                    <i class="fas fa-fire mr-2 text-danger"></i>Hot hôm nay
                </h5>
                <?php 
                // Lấy 2 sản phẩm mới nhất
                $hotProducts = array_slice($products, 0, 2);
                foreach ($hotProducts as $index => $hotProduct): 
                ?>
                <div class="d-flex align-items-center <?= $index == 0 ? 'mb-2' : '' ?>">
                    <div class="bg-<?= $index == 0 ? 'danger' : 'warning' ?> text-white rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 40px; height: 40px;">
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
            </div>
        </div>
    </div>
</div>
<!-- Hero Section End -->

<!-- Quick Categories Start -->
<div class="container-fluid pt-2">
    <div class="row px-xl-5 ">
        <div class="col-12">
            <h4 class="font-weight-bold mb-3 text-dark">
                <i class="fas fa-th-large text-primary mr-2"></i>Danh mục nổi bật
            </h4>
        </div>
        <div class="col-6 col-md-4 col-lg-2 mb-3">
            <a href="index.php?category=1" class="text-decoration-none">
                <div class="quick-category-card bg-white border rounded-lg p-3 text-center h-100 shadow-sm hover-lift">
                    <div class="category-icon bg-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="fas fa-car fa-lg"></i>
                    </div>
                    <h6 class="font-weight-bold mb-1 text-dark">Xe cộ</h6>
                    <small class="text-muted">Xe máy, ô tô, xe điện</small>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2 mb-3">
            <a href="index.php?category=2" class="text-decoration-none">
                <div class="quick-category-card bg-white border rounded-lg p-3 text-center h-100 shadow-sm hover-lift">
                    <div class="category-icon bg-success text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="fas fa-mobile-alt fa-lg"></i>
                    </div>
                    <h6 class="font-weight-bold mb-1 text-dark">Điện tử</h6>
                    <small class="text-muted">Điện thoại, laptop</small>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2 mb-3">
            <a href="index.php?category=3" class="text-decoration-none">
                <div class="quick-category-card bg-white border rounded-lg p-3 text-center h-100 shadow-sm hover-lift">
                    <div class="category-icon bg-warning text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="fas fa-tshirt fa-lg"></i>
                    </div>
                    <h6 class="font-weight-bold mb-1 text-dark">Thời trang</h6>
                    <small class="text-muted">Quần áo, giày dép</small>
            </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2 mb-3">
            <a href="index.php?category=4" class="text-decoration-none">
                <div class="quick-category-card bg-white border rounded-lg p-3 text-center h-100 shadow-sm hover-lift">
                    <div class="category-icon bg-info text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="fas fa-couch fa-lg"></i>
                    </div>
                    <h6 class="font-weight-bold mb-1 text-dark">Nội thất</h6>
                    <small class="text-muted">Bàn ghế, tủ kệ</small>
            </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2 mb-3">
            <a href="index.php?category=5" class="text-decoration-none">
                <div class="quick-category-card bg-white border rounded-lg p-3 text-center h-100 shadow-sm hover-lift">
                    <div class="category-icon bg-danger text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="fas fa-gamepad fa-lg"></i>
                    </div>
                    <h6 class="font-weight-bold mb-1 text-dark">Giải trí</h6>
                    <small class="text-muted">Game, nhạc cụ</small>
            </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2 mb-2">
            <a href="index.php?category=6" class="text-decoration-none">
                <div class="quick-category-card bg-white border rounded-lg p-3 text-center h-100 shadow-sm hover-lift">
                    <div class="category-icon bg-secondary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="fas fa-ellipsis-h fa-lg"></i>
                    </div>
                    <h6 class="font-weight-bold mb-1 text-dark">Khác</h6>
                    <small class="text-muted">Sản phẩm khác</small>
            </div>
            </a>
        </div>
    </div>
</div>
<!-- Quick Categories End -->

<!-- Lives Today Section Start -->
<div class="container-fluid pt-4">
    <div class="row px-xl-5">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="section-title position-relative text-uppercase mb-0">
                    <span class="bg-secondary pr-3">
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
                <button class="btn btn-light position-absolute" style="left: -15px; top: 50%; transform: translateY(-50%); z-index: 10; border-radius: 50%; width: 40px; height: 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);" onclick="scrollLives('left')">
                    <i class="fas fa-chevron-left"></i>
                </button>
                
                <!-- Nút điều hướng phải -->
                <button class="btn btn-light position-absolute" style="right: -15px; top: 50%; transform: translateY(-50%); z-index: 10; border-radius: 50%; width: 40px; height: 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);" onclick="scrollLives('right')">
                    <i class="fas fa-chevron-right"></i>
                </button>
                
                <!-- Container cuộn ngang -->
                <div class="lives-container" style="overflow-x: auto; overflow-y: hidden; white-space: nowrap; padding: 10px 0; scroll-behavior: smooth; scrollbar-width: none; -ms-overflow-style: none;" id="livesScrollContainer">
                    <!-- Live Stream Card 1 -->
                    <div class="d-inline-block" style="width: calc(100% / 6 - 10px); margin-right: 10px; vertical-align: top;">
                        <div class="live-card bg-white border rounded-lg overflow-hidden shadow-sm">
                            <div class="position-relative" style="aspect-ratio: 9/16; max-height: 200px;">
                                <img src="img/dienthoai.jpg" alt="Live Stream" class="img-live-stream">
                                <div class="position-absolute top-0 left-0 m-2" style="z-index: 20;">
                                    <span class="badge animate-pulse" style="background-color: #FF69B4; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                                        <i class="fas fa-circle text-white mr-1" style="font-size: 8px;"></i>LIVE
                                    </span>
                                </div>
                                <div class="position-absolute top-0 right-0 m-2" style="z-index: 20;">
                                    <span class="badge" style="background-color: rgba(0,0,0,0.7); color: white; padding: 4px 8px; border-radius: 4px;">
                                        <i class="fas fa-user mr-1"></i>1.2K
                                    </span>
                                </div>
                                <!-- Overlay hiển thị khi hover -->
                                <div class="live-overlay">
                                    <h5>iPhone 13 Pro Max 128GB - Giá sốc chỉ 18.5M</h5>
                                    <p>Điện thoại iPhone 13 Pro Max 128GB màu xanh, tình trạng 95%, còn bảo hành Apple. Giá tốt nhất thị trường, giao hàng tận nơi...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Live Stream Card 2 -->
                    <div class="d-inline-block" style="width: calc(100% / 6 - 10px); margin-right: 10px; vertical-align: top;">
                        <div class="live-card bg-white border rounded-lg overflow-hidden shadow-sm">
                            <div class="position-relative" style="aspect-ratio: 9/16; max-height: 200px;">
                                <img src="img/ao.jpg" alt="Live Stream" class="img-live-stream">
                                <div class="position-absolute top-0 left-0 m-2" style="z-index: 20;">
                                    <span class="badge animate-pulse" style="background-color: #FF69B4; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                                        <i class="fas fa-circle text-white mr-1" style="font-size: 8px;"></i>LIVE
                                    </span>
                                </div>
                                <div class="position-absolute top-0 right-0 m-2">
                                    <span class="badge" style="background-color: rgba(0,0,0,0.7); color: white; padding: 4px 8px; border-radius: 4px;">
                                        <i class="fas fa-user mr-1"></i>21
                                    </span>
                                </div>
                                <!-- Overlay hiển thị khi hover -->
                                <div class="live-overlay">
                                    <h5>Áo polo Tesla - Thời trang nam nữ cao cấp</h5>
                                    <p>Áo polo Tesla chính hãng, chất liệu cotton cao cấp, thiết kế sang trọng. Phù hợp cho công sở, dự tiệc...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Live Stream Card 3 -->
                    <div class="d-inline-block" style="width: calc(100% / 6 - 10px); margin-right: 10px; vertical-align: top;">
                        <div class="live-card bg-white border rounded-lg overflow-hidden shadow-sm">
                            <div class="position-relative" style="aspect-ratio: 9/16; max-height: 200px;">
                                <img src="img/banghe.jpg" alt="Live Stream" class="img-live-stream" >
                                <div class="position-absolute top-0 left-0 m-2" style="z-index: 20;">
                                    <span class="badge animate-pulse" style="background-color: #FF69B4; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                                        <i class="fas fa-circle text-white mr-1" style="font-size: 8px;"></i>LIVE
                                    </span>
                                </div>
                                <div class="position-absolute top-0 right-0 m-2">
                                    <span class="badge" style="background-color: rgba(0,0,0,0.7); color: white; padding: 4px 8px; border-radius: 4px;">
                                        <i class="fas fa-user mr-1"></i>14
                                    </span>
                                </div>
                                <!-- Overlay hiển thị khi hover -->
                                <div class="live-overlay">
                                    <h5>Bàn ghế hiện đại - Nội thất cao cấp</h5>
                                    <p>Bộ bàn ghế hiện đại, thiết kế tối giản, chất liệu gỗ cao cấp. Phù hợp cho phòng khách, văn phòng...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Live Stream Card 4 -->
                    <div class="d-inline-block" style="width: calc(100% / 6 - 10px); margin-right: 10px; vertical-align: top;">
                        <div class="live-card bg-white border rounded-lg overflow-hidden shadow-sm">
                            <div class="position-relative" style="aspect-ratio: 9/16; max-height: 200px;">
                                <img src="img/xemay.jpg" alt="Live Stream" class="img-live-stream" >
                                <div class="position-absolute top-0 left-0 m-2" style="z-index: 20;">
                                    <span class="badge animate-pulse" style="background-color: #FF69B4; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                                        <i class="fas fa-circle text-white mr-1" style="font-size: 8px;"></i>LIVE
                                    </span>
                                </div>
                                <div class="position-absolute top-0 right-0 m-2">
                                    <span class="badge" style="background-color: rgba(0,0,0,0.7); color: white; padding: 4px 8px; border-radius: 4px;">
                                        <i class="fas fa-user mr-1"></i>13
                                    </span>
                                </div>
                                <!-- Overlay hiển thị khi hover -->
                                <div class="live-overlay">
                                    <h5>Honda Vision 2020 - Xe máy tay ga tiết kiệm</h5>
                                    <p>Honda Vision 2020 màu đỏ, máy êm, tiết kiệm xăng. Xe đã qua sử dụng nhưng còn rất mới, đầy đủ giấy tờ...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Live Stream Card 5 -->
                    <div class="d-inline-block" style="width: calc(100% / 6 - 10px); margin-right: 10px; vertical-align: top;">
                        <div class="live-card bg-white border rounded-lg overflow-hidden shadow-sm">
                            <div class="position-relative" style="aspect-ratio: 9/16; max-height: 200px;">
                                <img src="img/laptop.jpg" alt="Live Stream" class="img-live-stream" >
                                <div class="position-absolute top-0 left-0 m-2" style="z-index: 20;">
                                    <span class="badge animate-pulse" style="background-color: #FF69B4; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                                        <i class="fas fa-circle text-white mr-1" style="font-size: 8px;"></i>LIVE
                                    </span>
                                </div>
                                <div class="position-absolute top-0 right-0 m-2">
                                    <span class="badge" style="background-color: rgba(0,0,0,0.7); color: white; padding: 4px 8px; border-radius: 4px;">
                                        <i class="fas fa-user mr-1"></i>7
                                    </span>
                                </div>
                                <!-- Overlay hiển thị khi hover -->
                                <div class="live-overlay">
                                    <h5>Dell XPS 15 - Laptop cao cấp cho công việc</h5>
                                    <p>Dell XPS 15 màn hình 15.6 inch, Intel i7, RAM 16GB, SSD 512GB. Laptop cao cấp phù hợp cho công việc...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Live Stream Card 6 -->
                    <div class="d-inline-block" style="width: calc(100% / 6 - 10px); margin-right: 10px; vertical-align: top;">
                        <div class="live-card bg-white border rounded-lg overflow-hidden shadow-sm">
                            <div class="position-relative" style="aspect-ratio: 9/16; max-height: 200px;">
                                <img src="img/nhaccu.jpg" alt="Live Stream" class="img-live-stream" >
                                <div class="position-absolute top-0 left-0 m-2" style="z-index: 20;">
                                    <span class="badge animate-pulse" style="background-color: #FF69B4; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                                        <i class="fas fa-circle text-white mr-1" style="font-size: 8px;"></i>LIVE
                                    </span>
                                </div>
                                <div class="position-absolute top-0 right-0 m-2">
                                    <span class="badge" style="background-color: rgba(0,0,0,0.7); color: white; padding: 4px 8px; border-radius: 4px;">
                                        <i class="fas fa-user mr-1"></i>6
                                    </span>
                                </div>
                                <!-- Overlay hiển thị khi hover -->
                                <div class="live-overlay">
                                    <h5>Nhạc cụ cũ - Guitar, Piano chất lượng cao</h5>
                                    <p>Bộ sưu tập nhạc cụ cũ chất lượng cao, guitar acoustic, piano điện. Phù hợp cho người mới học...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Live Stream Card 7 - Thêm mới -->
                    <div class="d-inline-block" style="width: calc(100% / 6 - 10px); margin-right: 10px; vertical-align: top;">
                        <div class="live-card bg-white border rounded-lg overflow-hidden shadow-sm">
                            <div class="position-relative" style="aspect-ratio: 9/16; max-height: 200px;">
                                <img src="img/dienthoai.jpg" alt="Live Stream" class="img-live-stream" >
                                <div class="position-absolute top-0 left-0 m-2" style="z-index: 20;">
                                    <span class="badge animate-pulse" style="background-color: #FF69B4; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                                        <i class="fas fa-circle text-white mr-1" style="font-size: 8px;"></i>LIVE
                                    </span>
                                </div>
                                <div class="position-absolute top-0 right-0 m-2">
                                    <span class="badge" style="background-color: rgba(0,0,0,0.7); color: white; padding: 4px 8px; border-radius: 4px;">
                                        <i class="fas fa-user mr-1"></i>89
                                    </span>
                                </div>
                                <!-- Overlay hiển thị khi hover -->
                                <div class="live-overlay">
                                    <h5>Samsung Galaxy S21 - Điện thoại Android cao cấp</h5>
                                    <p>Samsung Galaxy S21 128GB, camera 64MP, màn hình 6.2 inch. Điện thoại Android cao cấp, hiệu năng mạnh mẽ...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Live Stream Card 8 - Thêm mới -->
                    <div class="d-inline-block" style="width: calc(100% / 6 - 10px); margin-right: 10px; vertical-align: top;">
                        <div class="live-card bg-white border rounded-lg overflow-hidden shadow-sm">
                            <div class="position-relative" style="aspect-ratio: 9/16; max-height: 200px;">
                                <img src="img/ao.jpg" alt="Live Stream" class="img-live-stream" >
                                <div class="position-absolute top-0 left-0 m-2" style="z-index: 20;">
                                    <span class="badge animate-pulse" style="background-color: #FF69B4; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                                        <i class="fas fa-circle text-white mr-1" style="font-size: 8px;"></i>LIVE
                                    </span>
                                </div>
                                <div class="position-absolute top-0 right-0 m-2">
                                    <span class="badge" style="background-color: rgba(0,0,0,0.7); color: white; padding: 4px 8px; border-radius: 4px;">
                                        <i class="fas fa-user mr-1"></i>45
                                    </span>
                                </div>
                                <!-- Overlay hiển thị khi hover -->
                                <div class="live-overlay">
                                    <h5>Váy đầm cao cấp - Thời trang nữ sang trọng</h5>
                                    <p>Bộ sưu tập váy đầm cao cấp cho phụ nữ, thiết kế sang trọng, chất liệu cao cấp. Phù hợp cho các dịp đặc biệt...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Live Stream Card 9 - Thêm mới -->
                    <div class="d-inline-block" style="width: calc(100% / 6 - 10px); margin-right: 10px; vertical-align: top;">
                        <div class="live-card bg-white border rounded-lg overflow-hidden shadow-sm">
                            <div class="position-relative" style="aspect-ratio: 9/16; max-height: 200px;">
                                <img src="img/banghe.jpg" alt="Live Stream" class="img-live-stream" >
                                <div class="position-absolute top-0 left-0 m-2" style="z-index: 20;">
                                    <span class="badge animate-pulse" style="background-color: #FF69B4; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                                        <i class="fas fa-circle text-white mr-1" style="font-size: 8px;"></i>LIVE
                                    </span>
                                </div>
                                <div class="position-absolute top-0 right-0 m-2">
                                    <span class="badge" style="background-color: rgba(0,0,0,0.7); color: white; padding: 4px 8px; border-radius: 4px;">
                                        <i class="fas fa-user mr-1"></i>32
                                    </span>
                                </div>
                                <!-- Overlay hiển thị khi hover -->
                                <div class="live-overlay">
                                    <h5>Bàn làm việc văn phòng - Nội thất công sở</h5>
                                    <p>Bàn làm việc văn phòng hiện đại, thiết kế ergonomic, chất liệu gỗ cao cấp. Phù hợp cho văn phòng...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Live Stream Card 10 - Thêm mới -->
                    <div class="d-inline-block" style="width: calc(100% / 6 - 10px); margin-right: 10px; vertical-align: top;">
                        <div class="live-card bg-white border rounded-lg overflow-hidden shadow-sm">
                            <div class="position-relative" style="aspect-ratio: 9/16; max-height: 200px;">
                                <img src="img/xemay.jpg" alt="Live Stream" class="img-live-stream" >
                                <div class="position-absolute top-0 left-0 m-2" style="z-index: 20;">
                                    <span class="badge animate-pulse" style="background-color: #FF69B4; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                                        <i class="fas fa-circle text-white mr-1" style="font-size: 8px;"></i>LIVE
                                    </span>
                                </div>
                                <div class="position-absolute top-0 right-0 m-2">
                                    <span class="badge" style="background-color: rgba(0,0,0,0.7); color: white; padding: 4px 8px; border-radius: 4px;">
                                        <i class="fas fa-user mr-1"></i>18
                                    </span>
                                </div>
                                <!-- Overlay hiển thị khi hover -->
                                <div class="live-overlay">
                                    <h5>Yamaha Exciter 150 - Xe máy thể thao mạnh mẽ</h5>
                                    <p>Yamaha Exciter 150 màu đỏ, động cơ 150cc mạnh mẽ, thiết kế thể thao. Xe máy cũ nhưng còn rất mới...</p>
                                </div>
                            </div>
                        </div>
                    </div>
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

<div class="container-fluid pt-4">
    <div class="row px-xl-5">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="section-title position-relative text-uppercase mb-0">
                    <span class="bg-secondary pr-3">
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
                            <img class="img-fluid" src="img/<?= $img ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div class="flex-fill pl-3">
                            <h6 class="font-weight-bold mb-1 text-dark"><?= htmlspecialchars($cat['category_name']) ?></h6>
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
        <button id="show-more-btn" class="btn btn-primary px-4">Xem thêm</button>
        <button id="collapse-btn" class="btn btn-primary px-4 d-none">Thu gọn</button>
    </div>
    <?php endif; ?>
</div>



<!-- Products Start -->
<div class="container-fluid pt-4 pb-3">
    <div class="row px-xl-5">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="section-title position-relative text-uppercase mb-0">
                    <span class="bg-secondary pr-3">
                        <i class="fas fa-user-friends text-success mr-2"></i>Tin đăng mới nhất
                    </span>
    </h2>
                
            </div>
        </div>
    </div>
    <div class="row px-xl-5" id="product-list">
        <?php foreach ($products as $index => $sp): ?>
            <div class="col-6 col-sm-4 col-md-3 col-lg-2 pb-3 product-item-row <?= $index >= 18 ? 'd-none' : '' ?>">
                <div class="product-item bg-white border rounded-lg h-100 shadow-sm hover-lift">
                    <div class="product-img-hover position-relative">
                        <img src="img/<?= htmlspecialchars($sp['anh_dau']) ?>" alt="" class="img-fluid w-100" style="height: 150px; object-fit: cover; width: 100% !important; min-width: 100%; max-width: 100%;">
                        <div class="position-absolute top-0 right-0 m-2">
                            <button class="btn btn-sm btn-light rounded-circle">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                        <div class="position-absolute bottom-0 left-0 m-2">
                            <span class="badge badge-success text-white">
                                <i class="fas fa-clock mr-1"></i>Mới
                            </span>
                        </div>
                    </div>
                    <div class="p-3">
                        <a class="h6 text-decoration-none text-truncate d-block mb-2 text-dark" href="index.php?detail&id=<?= $sp['id'] ?>">
                            <?= htmlspecialchars($sp['title']) ?>
                        </a>
                        <div class="product-meta mb-2 small text-muted"><?= htmlspecialchars($sp['description']) ?></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-primary font-weight-bold"><?= number_format($sp['price']) ?> đ</span>
                            <div class="d-flex">
                                <button class="btn btn-outline-primary btn-sm mr-1" onclick="window.location.href='index.php?tin-nhan&to=<?= $sp['user_id'] ?>&product_id=<?= $sp['id'] ?>'">
                                    <i class="fas fa-comment"></i>
                                </button>
                                <button class="btn btn-outline-success btn-sm" onclick="alert('Số điện thoại: <?= htmlspecialchars($sp['phone']) ?>')">
                                    <i class="fas fa-phone"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Nút Xem thêm / Thu gọn -->
    <?php if (true): ?>
<div class="text-center mt-2">
    <button id="show-more-btn2" class="btn btn-primary px-4">Xem thêm</button>
    <button id="collapse-btn2" class="btn btn-primary px-4 d-none">Thu gọn</button>
</div>
<?php endif; ?>
</div>
<!-- Products End -->

<!-- About Section -->
<div class="container-fluid mt-2 mb-4">
    <div class="row px-xl-5">
        <div class="col-12">
            <div class="bg-white p-4 rounded shadow-sm">
                <h5 class="font-weight-bold mb-3">
                    <i class="fas fa-video text-primary mr-2"></i>Chợ Việt – Nền Tảng Livestream Bán Hàng C2C Hàng Đầu
                </h5>
                <p>
                    <strong>Chợ Việt</strong> là nền tảng livestream bán hàng kết nối người bán và người mua đồ cũ trực tuyến theo mô hình <strong>C2C (Consumer to Consumer)</strong>. Với tính năng livestream độc đáo, người bán có thể <strong>trực tiếp giới thiệu sản phẩm</strong> và tương tác với khách hàng trong thời gian thực.
                </p>
                <p>
                    Tại Chợ Việt, bạn có thể <strong>livestream bán hàng hoàn toàn miễn phí</strong>, chia sẻ hình ảnh thực tế và mô tả chi tiết sản phẩm trực tiếp với khách hàng. Tất cả live stream sẽ được <strong>kiểm duyệt nội dung</strong> để đảm bảo chất lượng và tuân thủ chính sách cộng đồng.
                </p>
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
                <p class="text-muted font-italic">🎥 Livestream bán hàng – Cách mới để bán đồ cũ hiệu quả cùng Chợ Việt.</p>
            </div>
        </div>
    </div>
</div>
<!-- About Section End -->

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

<!-- Phần sản phầm -->
<script>
document.addEventListener('DOMContentLoaded', function () {
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

</script>