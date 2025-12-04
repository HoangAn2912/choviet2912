<?php
// Cho phép role: 1 (admin), 3 (moderator), 4 (adcontent)
if (!in_array($_SESSION['role'], [1, 3, 4])) {
    echo "<script>
        alert('Bạn không đủ thẩm quyền truy cập!');
    </script>";
    header("refresh: 0; url='/ad'");
    exit;
}

include_once(__DIR__ . "/../../controller/cKDbaidang.php");
$p = new ckdbaidang();
if(isset($_GET['id'])){
    $dt = $p->getonebaidang($_GET['id']);
}
?>
<?php require_once __DIR__ . '/../../helpers/url_helper.php'; ?>
<?php 
// Kiểm tra nếu trang này được load trong admin.php thì không cần include header/footer
$isAdminFrame = isset($_GET['admin_frame']) || strpos($_SERVER['REQUEST_URI'], '/admin') !== false;
?>
<?php if (!$isAdminFrame): ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết bài đăng - Admin</title>
    <link rel="stylesheet" href="/admin/src/assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= getBasePath() ?>/css/admin-style.css">
    <link rel="stylesheet" href="<?= getBasePath() ?>/css/admin-common.css">
</head>
<body>
<?php else: ?>
<!-- Đảm bảo MDI và Font Awesome được load khi trang này được include trong admin.php -->
<link rel="stylesheet" href="/admin/src/assets/vendors/mdi/css/materialdesignicons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<?php endif; ?>

<style>
    .product-detail-container {
        margin: 0;
        padding: 20px;
    }
    
    .product-detail-container .admin-card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    
    .product-detail-container .admin-card-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .detail-section {
        margin-bottom: 25px;
    }
    
    .detail-section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .detail-row {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .detail-row:last-child {
        border-bottom: none;
    }
    
    .detail-label {
        font-weight: 600;
        color: #666;
        width: 150px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .detail-value {
        color: #333;
        flex: 1;
    }
    
    /* Image Gallery */
    .image-gallery-wrapper {
        position: relative;
    }
    
    .main-image-container {
        position: relative;
        width: 100%;
        height: 500px;
        margin-bottom: 15px;
        border-radius: 10px;
        overflow: hidden;
        background: #f8f9fa;
    }
    
    .main-image-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .image-counter {
        position: absolute;
        bottom: 15px;
        right: 15px;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .carousel-btn {
        position: absolute;
        top: calc(50% - 5px);
        transform: translateY(-50%);
        background: rgba(128, 128, 128, 0.6);
        border: none;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 10;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        backdrop-filter: blur(5px);
        padding: 0;
        margin: 0;
    }
    
    .carousel-btn:hover {
        background: rgba(128, 128, 128, 0.8);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        transform: translateY(-50%) scale(1.1);
    }
    
    .carousel-btn-prev {
        left: 15px;
    }
    
    .carousel-btn-next {
        right: 15px;
    }
    
    .carousel-btn .carousel-arrow {
        color: #FFD333;
        font-size: 36px;
        font-weight: bold;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0;
        padding: 0;
        width: 100%;
        height: 100%;
        text-align: center;
        position: relative;
        top: -6px;
    }
    
    .carousel-btn:hover .carousel-arrow {
        color: #ffd700;
    }
    
    .thumbnail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 10px;
        margin-top: 15px;
    }
    
    .thumbnail-item {
        position: relative;
        width: 100%;
        padding-top: 100%; /* 1:1 Aspect Ratio */
        border-radius: 8px;
        overflow: hidden;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }
    
    .thumbnail-item:hover {
        border-color: #0d6efd;
        transform: scale(1.05);
    }
    
    .thumbnail-item.active {
        border-color: #0d6efd;
        box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25);
    }
    
    .thumbnail-item img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .status-badge-detail {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .status-badge-detail.success {
        background-color: #d4edda;
        color: #155724;
    }
    
    .status-badge-detail.warning {
        background-color: #fff3cd;
        color: #856404;
    }
    
    .status-badge-detail.danger {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .status-badge-detail.info {
        background-color: #d1ecf1;
        color: #0c5460;
    }
    
    .status-badge-detail.secondary {
        background-color: #e2e3e5;
        color: #383d41;
    }
    
    .user-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #f0f0f0;
    }
    
    .btn-toggle-details {
        background: transparent !important;
        border: 1px solid #ddd !important;
        padding: 5px 10px !important;
        cursor: pointer !important;
        color: #666 !important;
        transition: all 0.3s ease !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 4px !important;
        margin-left: 10px !important;
        font-size: 16px !important;
        min-width: 30px !important;
        height: 30px !important;
        line-height: 1 !important;
    }
    
    .btn-toggle-details:hover {
        background: #f0f0f0 !important;
        color: #333 !important;
        border-color: #0d6efd !important;
    }
    
    .btn-toggle-details:focus {
        outline: none !important;
        box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25) !important;
    }
    
    .btn-toggle-details .toggle-arrow-text {
        display: inline-block !important;
        font-size: 14px !important;
        line-height: 1 !important;
        transition: transform 0.3s ease !important;
        font-weight: bold !important;
    }
    
    .btn-toggle-details[aria-expanded="true"] .toggle-arrow-text {
        transform: rotate(180deg) !important;
    }
    
    .seller-details-collapsible {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #f0f0f0;
        animation: slideDown 0.3s ease;
    }
    
    .seller-details-collapsible .detail-row {
        padding: 10px 0;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            max-height: 0;
        }
        to {
            opacity: 1;
            max-height: 500px;
        }
    }
    
    .description-box {
        background: #f8f9fa;
        padding: 0 15px 15px 15px !important;
        border-radius: 8px;
        border-left: 4px solid #0d6efd;
        white-space: pre-wrap;
        word-wrap: break-word;
        margin: 0 !important;
        margin-top: 0 !important;
        line-height: 1.6;
        text-align: left;
        vertical-align: top;
        text-indent: 0 !important;
    }
    
    /* Xóa hoàn toàn khoảng trắng ở đầu description-box */
    .description-box::before {
        content: '';
        display: none !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    /* Ẩn hoàn toàn <br> đầu tiên nếu có */
    .description-box br:first-child,
    .description-box > br:first-child,
    .description-box > br:nth-child(1) {
        display: none !important;
        visibility: hidden !important;
        margin: 0 !important;
        padding: 0 !important;
        height: 0 !important;
        line-height: 0 !important;
        font-size: 0 !important;
    }
    
    /* Loại bỏ khoảng trắng ở phần tử đầu tiên */
    .description-box > *:first-child {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
    }
    
    /* Loại bỏ text-indent */
    .description-box {
        text-indent: 0 !important;
    }
    
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    
    .timeline-item {
        position: relative;
        padding-bottom: 15px;
        color: #6c757d;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -24px;
        top: 4px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #adb5bd;
    }
    
    .timeline-item.active::before {
        background-color: #4CAF50;
        box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
    }
    
    .timeline-item.rejected::before {
        background-color: #F44336;
        box-shadow: 0 0 0 3px rgba(244, 67, 54, 0.2);
    }
    
    .timeline-item.pending::before {
        background-color: #FFC107;
        box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.2);
    }
    
    .timeline-item.sold::before {
        background-color: #2196F3;
        box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.2);
    }
    
    .timeline-date {
        font-size: 0.75rem;
        color: #adb5bd;
        margin-left: 5px;
    }
    
    @media (max-width: 768px) {
        .main-image-container {
            height: 350px;
        }
        
        .thumbnail-grid {
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
        }
        
        .detail-row {
            flex-direction: column;
            gap: 5px;
        }
        
        .detail-label {
            width: 100%;
        }
    }
</style>

<div class="product-detail-container admin-container">
    <?php if (empty($dt)): ?>
        <div class="admin-card">
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> Không tìm thấy bài đăng với ID này.
            </div>
            <a href="<?= getBasePath() ?>/admin?qlkdbaiviet" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    <?php else: ?>
        <?php foreach($dt as $ct): ?>
            <?php 
            $images = array_filter(array_map('trim', explode(',', $ct['image'])));
            $user_avatar = !empty($ct['user_avatar']) ? $ct['user_avatar'] : 'default-avatar.jpg';
            ?>
            
            <!-- Header với nút quay lại -->
            <div class="admin-card">
                <div class="admin-card-title">
                    <span><i class="fas fa-file-alt me-2"></i> Chi tiết bài viết</span>
                    <a href="<?= getBasePath() ?>/admin?qlkdbaiviet" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
                
                <div class="row g-4">
                    <!-- Cột trái: Ảnh sản phẩm -->
                    <div class="col-lg-5">
                        <div class="image-gallery-wrapper">
                            <!-- Ảnh chính với carousel -->
                            <?php if (!empty($images)): ?>
                                <div class="main-image-container" id="mainImageContainer">
                                    <?php foreach($images as $index => $img): ?>
                                        <img src="<?= getBasePath() ?>/img/<?php echo $img; ?>" 
                                             alt="<?php echo htmlspecialchars($ct['title']); ?> - Ảnh <?php echo $index + 1; ?>" 
                                             class="main-image <?php echo $index === 0 ? 'active' : ''; ?>" 
                                             data-index="<?php echo $index; ?>"
                                             style="display: <?php echo $index === 0 ? 'block' : 'none'; ?>;">
                                    <?php endforeach; ?>
                                    
                                    <!-- Navigation Buttons -->
                                    <?php if (count($images) > 1): ?>
                                        <button class="carousel-btn carousel-btn-prev" onclick="changeSlide(-1)">
                                            <span class="carousel-arrow">‹</span>
                                        </button>
                                        <button class="carousel-btn carousel-btn-next" onclick="changeSlide(1)">
                                            <span class="carousel-arrow">›</span>
                                        </button>
                                        <div class="image-counter">
                                            <span id="currentImage">1</span> / <span id="totalImages"><?php echo count($images); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Thumbnail Grid -->
                                <?php if (count($images) > 1): ?>
                                    <div class="thumbnail-grid" id="thumbnailGrid">
                                        <?php foreach($images as $index => $img): ?>
                                            <div class="thumbnail-item <?php echo $index === 0 ? 'active' : ''; ?>" 
                                                 onclick="goToSlide(<?php echo $index; ?>)"
                                                 data-index="<?php echo $index; ?>">
                                                <img src="<?= getBasePath() ?>/img/<?php echo $img; ?>" alt="Thumbnail <?php echo $index + 1; ?>">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="main-image-container">
                                    <div class="d-flex align-items-center justify-content-center h-100">
                                        <span class="text-muted">Không có ảnh</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Cột phải: Thông tin sản phẩm -->
                    <div class="col-lg-7">
                        <div class="admin-card">
                            <div class="detail-section">
                                <h2 class="mb-2"><?php echo htmlspecialchars($ct['title']); ?></h2>
                                <div class="mb-2">
                                    <span class="h4 text-primary fw-bold"><?php echo number_format($ct['price'], 0, ',', '.'); ?> VNĐ</span>
                                </div>
                                <div class="mb-3 d-flex flex-wrap gap-2">
                                    <?php
                                    $status_class = '';
                                    $status_icon = '';
                                    if($ct['status'] == 'Đã duyệt') {
                                        $status_class = 'success';
                                        $status_icon = '<i class="fas fa-check-circle"></i> ';
                                    } elseif($ct['status'] == 'Chờ duyệt') {
                                        $status_class = 'warning';
                                        $status_icon = '<i class="fas fa-clock"></i> ';
                                    } elseif($ct['status'] == 'Từ chối duyệt') {
                                        $status_class = 'danger';
                                        $status_icon = '<i class="fas fa-times-circle"></i> ';
                                    } else {
                                        $status_class = 'info';
                                    }
                                    ?>
                                    <span class="status-badge-detail <?php echo $status_class; ?>">
                                        <?php echo $status_icon . htmlspecialchars($ct['status']); ?>
                                    </span>
                                    
                                    <?php if (!empty($ct['sale_status'])): ?>
                                        <?php
                                        $sale_status_class = '';
                                        $sale_status_icon = '';
                                        if($ct['sale_status'] == 'Đang bán') {
                                            $sale_status_class = 'info';
                                            $sale_status_icon = '<i class="fas fa-tag"></i> ';
                                        } elseif($ct['sale_status'] == 'Đã bán') {
                                            $sale_status_class = 'success';
                                            $sale_status_icon = '<i class="fas fa-shopping-cart"></i> ';
                                        } elseif($ct['sale_status'] == 'Đã ẩn') {
                                            $sale_status_class = 'secondary';
                                            $sale_status_icon = '<i class="fas fa-eye-slash"></i> ';
                                        }
                                        ?>
                                        <span class="status-badge-detail <?php echo $sale_status_class; ?>">
                                            <?php echo $sale_status_icon . htmlspecialchars($ct['sale_status']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="detail-section">
                                <div class="detail-section-title">
                                    <i class="fas fa-info-circle"></i> Thông tin sản phẩm
                                </div>
                                
                                <div class="detail-row">
                                    <div class="detail-label">
                                        <i class="fas fa-hashtag"></i> ID bài viết
                                    </div>
                                    <div class="detail-value">
                                        <strong>#<?php echo $ct['id']; ?></strong>
                                    </div>
                                </div>
                                
                                <div class="detail-row">
                                    <div class="detail-label">
                                        <i class="fas fa-tag"></i> Danh mục
                                    </div>
                                    <div class="detail-value"><?php echo htmlspecialchars($ct['category_name']); ?></div>
                                </div>
                                
                                <div class="detail-row">
                                    <div class="detail-label">
                                        <i class="far fa-calendar"></i> Ngày đăng
                                    </div>
                                    <div class="detail-value"><?php echo $ct['created_date']; ?></div>
                                </div>
                                
                                <div class="detail-row">
                                    <div class="detail-label">
                                        <i class="fas fa-sync-alt"></i> Cập nhật lần cuối
                                    </div>
                                    <div class="detail-value"><?php echo $ct['updated_date']; ?></div>
                                </div>
                                
                                <?php if($ct['status'] == 'Từ chối duyệt' && !empty($ct['note'])): ?>
                                <div class="detail-row">
                                    <div class="detail-label">
                                        <i class="fas fa-exclamation-triangle text-danger"></i> Lý do từ chối
                                    </div>
                                    <div class="detail-value text-danger"><?php echo htmlspecialchars($ct['note']); ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <hr>
                            
                            <div class="detail-section">
                                <div class="detail-section-title">
                                    <i class="fas fa-user"></i> Thông tin người bán
                                </div>
                                
                                <div class="detail-row">
                                    <div class="detail-label">
                                        <i class="fas fa-user-circle"></i> Họ và tên
                                    </div>
                                    <div class="detail-value">
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="<?= getBasePath() ?>/img/<?php echo $user_avatar; ?>" 
                                                 alt="Avatar" 
                                                 class="user-avatar"
                                                 onerror="this.src='<?= getBasePath() ?>/img/default-avatar.jpg'">
                                            <strong><?php echo htmlspecialchars($ct['ho_ten']); ?></strong>
                                            <button type="button" 
                                                    class="btn-toggle-details" 
                                                    onclick="toggleSellerDetails()"
                                                    aria-expanded="false"
                                                    aria-label="Xem thông tin chi tiết"
                                                    id="sellerDetailsToggle"
                                                    title="Xem thông tin chi tiết">
                                                <span class="toggle-arrow-text">▼</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="seller-details-collapsible" id="sellerDetailsCollapsible" style="display: none;">
                                    <div class="detail-row">
                                        <div class="detail-label">
                                            <i class="fas fa-hashtag"></i> Mã người dùng
                                        </div>
                                        <div class="detail-value">
                                            <strong>#<?php echo $ct['user_id']; ?></strong>
                                        </div>
                                    </div>
                                    
                                    <div class="detail-row">
                                        <div class="detail-label">
                                            <i class="fas fa-envelope"></i> Email
                                        </div>
                                        <div class="detail-value">
                                            <?php echo !empty($ct['user_email']) ? htmlspecialchars($ct['user_email']) : '<span class="text-muted">Chưa cập nhật</span>'; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="detail-row">
                                        <div class="detail-label">
                                            <i class="fas fa-phone"></i> Số điện thoại
                                        </div>
                                        <div class="detail-value">
                                            <?php echo !empty($ct['user_phone']) ? htmlspecialchars($ct['user_phone']) : '<span class="text-muted">Chưa cập nhật</span>'; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="detail-row">
                                        <div class="detail-label">
                                            <i class="fas fa-map-marker-alt"></i> Địa chỉ
                                        </div>
                                        <div class="detail-value">
                                            <?php echo !empty($ct['user_address']) ? htmlspecialchars($ct['user_address']) : '<span class="text-muted">Chưa cập nhật</span>'; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Mô tả sản phẩm -->
            <?php if (!empty($ct['comment'])): ?>
            <?php
            // Xử lý mô tả: loại bỏ khoảng trắng dư thừa
            $description = trim($ct['comment']);
            // Loại bỏ khoảng trắng dư thừa ở đầu và cuối mỗi dòng
            $lines = explode("\n", $description);
            $cleaned_lines = array_map('trim', $lines);
            // Loại bỏ các dòng trống
            $cleaned_lines = array_filter($cleaned_lines, function($line) {
                return !empty($line);
            });
            // Join lại và loại bỏ khoảng trắng dư thừa giữa các từ
            $cleaned_description = implode("\n", $cleaned_lines);
            // Loại bỏ nhiều khoảng trắng liên tiếp thành 1 khoảng trắng
            $cleaned_description = preg_replace('/[ \t]+/', ' ', $cleaned_description);
            // Loại bỏ nhiều line breaks liên tiếp thành 2 line breaks tối đa
            $cleaned_description = preg_replace('/\n{3,}/', "\n\n", $cleaned_description);
            // Trim lại để đảm bảo không có khoảng trắng ở đầu/cuối
            $cleaned_description = trim($cleaned_description);
            
            // Escape HTML trước khi xử lý
            $escaped = htmlspecialchars($cleaned_description, ENT_QUOTES, 'UTF-8');
            
            // Chuyển newline thành <br>
            $output = nl2br($escaped);
            
            // Loại bỏ HOÀN TOÀN <br> và whitespace ở đầu chuỗi
            // Lặp lại cho đến khi không còn thay đổi
            do {
                $old_output = $output;
                $output = preg_replace('/^(<br\s*\/?>[\s\n\r\t]*)+/i', '', $output);
                $output = preg_replace('/^[\s\n\r\t]+/', '', $output);
                $output = ltrim($output);
            } while ($old_output !== $output);
            
            // Trim lại lần cuối
            $output = trim($output);
            
            // Đảm bảo chắc chắn không có <br> ở đầu
            $output = preg_replace('/^(<br\s*\/?>[\s\n\r\t]*)+/i', '', $output);
            ?>
            <div class="admin-card">
                <div class="detail-section-title">
                    <i class="fas fa-align-left"></i> Mô tả sản phẩm
                </div>
                <div class="description-box"><?php echo trim($output); ?></div>
            </div>
            <?php endif; ?>
            
            <!-- Lịch sử trạng thái -->
            <?php
            $status = isset($ct['status']) ? $ct['status'] : '';
            $status_ban = isset($ct['sale_status']) ? $ct['sale_status'] : '';
            $timeline = [
                [
                    'status' => 'Tạo bài đăng',
                    'date' => $ct['created_date'],
                    'class' => 'active'
                ]
            ];
            
            if($status == "Chờ duyệt") {
                $timeline[] = [
                    'status' => 'Đang chờ duyệt',
                    'date' => $ct['created_date'],
                    'class' => 'pending'
                ];
            } else if($status == "Đã duyệt") {
                $timeline[] = [
                    'status' => 'Đang chờ duyệt',
                    'date' => $ct['created_date'],
                    'class' => 'active'
                ];
                $timeline[] = [
                    'status' => 'Đã duyệt bởi Admin',
                    'date' => $ct['updated_date'],
                    'class' => 'active'
                ];
            } else if($status == "Từ chối duyệt") {
                $timeline[] = [
                    'status' => 'Đang chờ duyệt',
                    'date' => $ct['created_date'],
                    'class' => 'active'
                ];
                $timeline[] = [
                    'status' => 'Từ chối bởi Admin',
                    'date' => $ct['updated_date'],
                    'class' => 'rejected'
                ];
            }
            
            if($status_ban == "Đã bán") {
                $timeline[] = [
                    'status' => 'Đã bán',
                    'date' => '',
                    'class' => 'sold'
                ];
            }
            ?>
            <div class="admin-card">
                <div class="detail-section-title">
                    <i class="fas fa-history"></i> Lịch sử trạng thái
                </div>
                <div class="timeline">
                    <?php foreach($timeline as $item): ?>
                        <div class="timeline-item <?php echo $item['class']; ?>">
                            <?php echo htmlspecialchars($item['status']); ?>
                            <?php if (!empty($item['date'])): ?>
                                <span class="timeline-date"><?php echo $item['date']; ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
    let currentSlide = 0;
    const totalSlides = <?php echo !empty($images) ? count($images) : 0; ?>;
    const mainImages = document.querySelectorAll('.main-image');
    const thumbnails = document.querySelectorAll('.thumbnail-item');
    
    function showSlide(index) {
        if (totalSlides === 0) return;
        
        // Ensure index is within bounds
        if (index >= totalSlides) {
            currentSlide = 0;
        } else if (index < 0) {
            currentSlide = totalSlides - 1;
        } else {
            currentSlide = index;
        }
        
        // Update main images
        mainImages.forEach((img, i) => {
            if (i === currentSlide) {
                img.style.display = 'block';
                img.classList.add('active');
            } else {
                img.style.display = 'none';
                img.classList.remove('active');
            }
        });
        
        // Update thumbnails
        thumbnails.forEach((thumb, i) => {
            if (i === currentSlide) {
                thumb.classList.add('active');
                // Scroll thumbnail into view
                thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            } else {
                thumb.classList.remove('active');
            }
        });
        
        // Update counter
        if (document.getElementById('currentImage')) {
            document.getElementById('currentImage').textContent = currentSlide + 1;
        }
    }
    
    function changeSlide(direction) {
        showSlide(currentSlide + direction);
    }
    
    function goToSlide(index) {
        showSlide(index);
    }
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            changeSlide(-1);
        } else if (e.key === 'ArrowRight') {
            changeSlide(1);
        }
    });
    
    // Initialize first slide
    if (totalSlides > 0) {
        showSlide(0);
    }
    
    // Toggle seller details
    function toggleSellerDetails() {
        const collapsible = document.getElementById('sellerDetailsCollapsible');
        const toggle = document.getElementById('sellerDetailsToggle');
        
        if (!collapsible || !toggle) {
            console.error('Không tìm thấy phần tử collapse hoặc toggle button');
            return;
        }
        
        const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
        
        if (isExpanded) {
            collapsible.style.display = 'none';
            toggle.setAttribute('aria-expanded', 'false');
        } else {
            collapsible.style.display = 'block';
            toggle.setAttribute('aria-expanded', 'true');
        }
    }
    
    // Đảm bảo function được khai báo global
    window.toggleSellerDetails = toggleSellerDetails;
</script>

<?php if (!$isAdminFrame): ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php endif; ?>
