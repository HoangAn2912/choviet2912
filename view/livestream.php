<?php
include_once("view/header.php");
include_once("controller/cCategory.php");
$cCategory = new cCategory();
$categories = $cCategory->index();
?>

<div class="container-fluid pt-4">
    <div class="row px-xl-5">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="section-title position-relative text-uppercase mb-0">
                    <span class="bg-secondary pr-3" style="color: #3D464D;">
                        <i class="fas fa-video text-danger mr-2"></i>Tất cả Live Stream
                    </span>
                </h2>
                <div class="btn-group" role="group" style="border: 1px solid #FFD333;">
                    <button type="button" class="btn btn-sm active" onclick="filterLiveStream('all')" style="background-color: #FFD333; color: #3D464D; border: none; border-right: 1px solid #FFD333;">Tất cả</button>
                    <button type="button" class="btn btn-sm" onclick="filterLiveStream('live')" style="background-color: white; color: #3D464D; border: none; border-right: 1px solid #FFD333;">Đang live</button>
                    <button type="button" class="btn btn-sm" onclick="filterLiveStream('upcoming')" style="background-color: white; color: #3D464D; border: none;">Sắp live</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter by Category -->
    <div class="row px-xl-5 mb-4">
        <div class="col-12">
            <div class="bg-light p-3 rounded">
                <h5 class="font-weight-bold mb-3" style="color: #3D464D;">
                    <i class="fas fa-filter mr-2"></i>Lọc theo danh mục
                </h5>
                <div class="row">
                    <div class="col-md-2 mb-2">
                        <button class="btn btn-sm w-100 active" onclick="filterByCategory('all')" style="background-color: #FFD333; color: #3D464D; border: 1px solid #FFD333;">
                            Tất cả
                        </button>
                    </div>
                    <?php foreach ($categories as $id_cha => $parent): ?>
                    <div class="col-md-2 mb-2">
                        <button class="btn btn-sm w-100" onclick="filterByCategory(<?= $id_cha ?>)" style="background-color: #3D464D; color: white; border: 1px solid #FFD333;">
                            <?= htmlspecialchars($parent['ten_cha']) ?>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Stream Grid - Layout ngang -->
    <div class="row px-xl-5" id="livestream-grid">
        <!-- Live Stream Card 1 -->
        <div class="col-12 mb-4 livestream-card" data-category="1" data-status="live">
            <div class="live-card bg-white border rounded-lg overflow-hidden shadow-sm">
                <div class="row no-gutters">
                    <div class="col-md-4">
                        <div class="position-relative" style="height: 200px;">
                            <img src="img/dienthoai.jpg" alt="Live Stream" class="img-fluid w-100 h-100" style="object-fit: cover;">
                            <div class="position-absolute top-0 left-0 m-2">
                                <span class="badge animate-pulse" style="background-color: #FF69B4; color: white; padding: 4px 8px; border-radius: 4px;">
                                    <i class="fas fa-circle text-white mr-1" style="font-size: 8px;"></i>LIVE
                                </span>
                            </div>
                            <div class="position-absolute top-0 right-0 m-2">
                                <span class="badge" style="background-color: rgba(0,0,0,0.7); color: white; padding: 4px 8px; border-radius: 4px;">
                                    <i class="fas fa-user mr-1"></i>1.2K
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="p-3">
                            <div class="d-flex align-items-center mb-2">
                                <img src="img/user.jpg" alt="Avatar" class="rounded-circle mr-3" style="width: 40px; height: 40px; object-fit: cover;">
                                <div>
                                    <h5 class="mb-1 font-weight-bold text-dark">Shop điện thoại cũ</h5>
                                    <small class="text-muted">Đang live • 1.2K người xem</small>
                                </div>
                            </div>
                            <h4 class="font-weight-bold text-dark mb-2">iPhone 13 Pro Max 128GB - Giá sốc chỉ 18.5M</h4>
                            <p class="text-muted mb-3">Điện thoại iPhone 13 Pro Max 128GB màu xanh, tình trạng 95%, còn bảo hành Apple. Giá tốt nhất thị trường, giao hàng tận nơi.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <button class="btn btn-danger">
                                    <i class="fas fa-play mr-1"></i>Xem ngay
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Stream Card 2 -->
        <div class="col-12 mb-4 livestream-card" data-category="3" data-status="upcoming">
            <div class="live-card bg-white border rounded-lg overflow-hidden shadow-sm">
                <div class="row no-gutters">
                    <div class="col-md-4">
                        <div class="position-relative" style="height: 200px;">
                            <img src="img/ao.jpg" alt="Live Stream" class="img-fluid w-100 h-100" style="object-fit: cover;">
                            <div class="position-absolute top-0 left-0 m-2">
                                <span class="badge" style="background-color: #28a745; color: white; padding: 4px 8px; border-radius: 4px;">
                                    <i class="fas fa-clock text-white mr-1"></i>Sắp live
                                </span>
                            </div>
                            <div class="position-absolute top-0 right-0 m-2">
                                <span class="badge" style="background-color: rgba(0,0,0,0.7); color: white; padding: 4px 8px; border-radius: 4px;">
                                    <i class="fas fa-user mr-1"></i>21
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="p-3">
                            <div class="d-flex align-items-center mb-2">
                                <img src="img/user1.jpg" alt="Avatar" class="rounded-circle mr-3" style="width: 40px; height: 40px; object-fit: cover;">
                                <div>
                                    <h5 class="mb-1 font-weight-bold text-dark">Thời trang nam nữ</h5>
                                    <small class="text-muted">Sắp live • 21 người quan tâm</small>
                                </div>
                            </div>
                            <h4 class="font-weight-bold text-dark mb-2">Áo polo Tesla chính hãng - Thời trang cao cấp</h4>
                            <p class="text-muted mb-3">Bộ sưu tập áo polo Tesla chính hãng, chất liệu cao cấp, thiết kế sang trọng. Phù hợp cho nam nữ, nhiều size và màu sắc.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <button class="btn btn-outline-warning">
                                    <i class="fas fa-bell mr-1"></i>Nhắc nhở
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Stream Card 3 -->
        <div class="col-12 mb-4 livestream-card" data-category="4" data-status="live">
            <div class="live-card bg-white border rounded-lg overflow-hidden shadow-sm">
                <div class="row no-gutters">
                    <div class="col-md-4">
                        <div class="position-relative" style="height: 200px;">
                            <img src="img/banghe.jpg" alt="Live Stream" class="img-fluid w-100 h-100" style="object-fit: cover;">
                            <div class="position-absolute top-0 left-0 m-2">
                                <span class="badge animate-pulse" style="background-color: #FF69B4; color: white; padding: 4px 8px; border-radius: 4px;">
                                    <i class="fas fa-circle text-white mr-1" style="font-size: 8px;"></i>LIVE
                                </span>
                            </div>
                            <div class="position-absolute top-0 right-0 m-2">
                                <span class="badge" style="background-color: rgba(0,0,0,0.7); color: white; padding: 4px 8px; border-radius: 4px;">
                                    <i class="fas fa-user mr-1"></i>14
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="p-3">
                            <div class="d-flex align-items-center mb-2">
                                <img src="img/user2.jpg" alt="Avatar" class="rounded-circle mr-3" style="width: 40px; height: 40px; object-fit: cover;">
                                <div>
                                    <h5 class="mb-1 font-weight-bold text-dark">Nội thất gia đình</h5>
                                    <small class="text-muted">Đang live • 14 người xem</small>
                                </div>
                            </div>
                            <h4 class="font-weight-bold text-dark mb-2">Bàn ghế hiện đại - Nội thất cao cấp</h4>
                            <p class="text-muted mb-3">Bộ bàn ghế hiện đại, thiết kế tối giản, chất liệu gỗ cao cấp. Phù hợp cho phòng khách, văn phòng. Giá tốt, giao hàng miễn phí.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <button class="btn btn-danger">
                                    <i class="fas fa-play mr-1"></i>Xem ngay
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Stream Card 4 -->
        <div class="col-12 mb-4 livestream-card" data-category="1" data-status="upcoming">
            <div class="live-card bg-white border rounded-lg overflow-hidden shadow-sm">
                <div class="row no-gutters">
                    <div class="col-md-4">
                        <div class="position-relative" style="height: 200px;">
                            <img src="img/xemay.jpg" alt="Live Stream" class="img-fluid w-100 h-100" style="object-fit: cover;">
                            <div class="position-absolute top-0 left-0 m-2">
                                <span class="badge" style="background-color: #ffc107; color: #3D464D; padding: 4px 8px; border-radius: 4px;">
                                    <i class="fas fa-clock mr-1"></i>Hôm nay
                                </span>
                            </div>
                            <div class="position-absolute top-0 right-0 m-2">
                                <span class="badge" style="background-color: rgba(0,0,0,0.7); color: white; padding: 4px 8px; border-radius: 4px;">
                                    <i class="fas fa-user mr-1"></i>13
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="p-3">
                            <div class="d-flex align-items-center mb-2">
                                <img src="img/user3.jpg" alt="Avatar" class="rounded-circle mr-3" style="width: 40px; height: 40px; object-fit: cover;">
                                <div>
                                    <h5 class="mb-1 font-weight-bold text-dark">Xe máy cũ giá rẻ</h5>
                                    <small class="text-muted">Hôm nay • 13 người quan tâm</small>
                                </div>
                            </div>
                            <h4 class="font-weight-bold text-dark mb-2">Honda Vision 2020 - Xe máy tay ga tiết kiệm</h4>
                            <p class="text-muted mb-3">Honda Vision 2020 màu đỏ, máy êm, tiết kiệm xăng. Xe đã qua sử dụng nhưng còn rất mới, đầy đủ giấy tờ, bảo dưỡng định kỳ.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <button class="btn btn-outline-warning">
                                    <i class="fas fa-bell mr-1"></i>Nhắc nhở
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Stream Card 5 -->
        <div class="col-12 mb-4 livestream-card" data-category="2" data-status="live">
            <div class="live-card bg-white border rounded-lg overflow-hidden shadow-sm">
                <div class="row no-gutters">
                    <div class="col-md-4">
                        <div class="position-relative" style="height: 200px;">
                            <img src="img/laptop.jpg" alt="Live Stream" class="img-fluid w-100 h-100" style="object-fit: cover;">
                            <div class="position-absolute top-0 left-0 m-2">
                                <span class="badge animate-pulse" style="background-color: #FF69B4; color: white; padding: 4px 8px; border-radius: 4px;">
                                    <i class="fas fa-circle text-white mr-1" style="font-size: 8px;"></i>LIVE
                                </span>
                            </div>
                            <div class="position-absolute top-0 right-0 m-2">
                                <span class="badge" style="background-color: rgba(0,0,0,0.7); color: white; padding: 4px 8px; border-radius: 4px;">
                                    <i class="fas fa-user mr-1"></i>7
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="p-3">
                            <div class="d-flex align-items-center mb-2">
                                <img src="img/user4.jpg" alt="Avatar" class="rounded-circle mr-3" style="width: 40px; height: 40px; object-fit: cover;">
                                <div>
                                    <h5 class="mb-1 font-weight-bold text-dark">Laptop cũ giá tốt</h5>
                                    <small class="text-muted">Đang live • 7 người xem</small>
                                </div>
                            </div>
                            <h4 class="font-weight-bold text-dark mb-2">Dell XPS 15 - Laptop cao cấp cho công việc</h4>
                            <p class="text-muted mb-3">Dell XPS 15 màn hình 15.6 inch, Intel i7, RAM 16GB, SSD 512GB. Laptop cao cấp phù hợp cho công việc, thiết kế, gaming.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <button class="btn btn-danger">
                                    <i class="fas fa-play mr-1"></i>Xem ngay
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function filterLiveStream(status) {
    // Remove active class from all buttons and reset styles
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
        btn.style.backgroundColor = 'white';
        btn.style.color = '#3D464D';
    });
    // Add active class to clicked button and set active style
    event.target.classList.add('active');
    event.target.style.backgroundColor = '#FFD333';
    event.target.style.color = '#3D464D';
    
    const cards = document.querySelectorAll('.livestream-card');
    cards.forEach(card => {
        if (status === 'all' || card.dataset.status === status) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function filterByCategory(categoryId) {
    // Remove active class from all category buttons and reset styles
    document.querySelectorAll('.bg-light .btn').forEach(btn => {
        btn.classList.remove('active');
        btn.style.backgroundColor = '#3D464D';
        btn.style.color = 'white';
        btn.style.border = '1px solid #FFD333';
    });
    // Add active class to clicked button and set active style
    event.target.classList.add('active');
    event.target.style.backgroundColor = '#FFD333';
    event.target.style.color = '#3D464D';
    event.target.style.border = '1px solid #FFD333';
    
    const cards = document.querySelectorAll('.livestream-card');
    cards.forEach(card => {
        if (categoryId === 'all' || card.dataset.category == categoryId) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

<?php include_once("view/footer.php"); ?>
