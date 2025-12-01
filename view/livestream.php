<?php
include_once("view/header.php");
include_once("controller/cCategory.php");
include_once("model/mLivestream.php");

$cCategory = new cCategory();
$categories = $cCategory->index();

// Lấy danh sách livestream từ database
$mLivestream = new mLivestream();
$allLivestreams = $mLivestream->getLivestreams(null, 50); // Lấy tối đa 50 livestream
?>

<style>
        /* Page Background - Gradient nhẹ */
        .page-background {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            min-height: calc(100vh - 180px);
            padding: 0 2rem 2rem 2rem;
        }

        /* Livestream icons đỏ */
        .content-wrapper i.fas.fa-video,
        .content-wrapper i.fas.fa-broadcast-tower,
        .content-wrapper i.fas.fa-play,
        .content-wrapper i.fas.fa-circle {
            color: #dc3545 !important;
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
        
        /* Bỏ padding của container bên trong content-wrapper */
        .content-wrapper .container,
        .content-wrapper .container-fluid {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        
        /* Override px-xl-5 trong content-wrapper */
        .content-wrapper .px-xl-5 {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        
        @media (max-width: 768px) {
            .page-background {
                padding: 0 1rem 1rem 1rem;
            }
            
            .content-wrapper {
                padding: 1.5rem;
                border-radius: 12px;
            }
        }
</style>

<!-- Page Background Start -->
<div class="page-background">
    <!-- Content Wrapper Start -->
    <div class="content-wrapper">

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
        <?php if (empty($allLivestreams)): ?>
            <!-- Empty State -->
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-video fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Chưa có livestream nào</h4>
                    <p class="text-muted">Hiện tại chưa có livestream nào đang diễn ra. Hãy quay lại sau!</p>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="index.php?create-livestream" class="btn btn-primary">
                            <i class="fas fa-plus mr-2"></i>Tạo livestream đầu tiên
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($allLivestreams as $livestream): ?>
                <?php
                // Xác định trạng thái và màu sắc
                $statusClass = '';
                $statusText = '';
                $statusIcon = '';
                $badgeColor = '';
                
                switch($livestream['status']) {
                    case 'dang_dien_ra':
                    case 'dang_live':
                        $statusClass = 'live';
                        $statusText = 'Đang live';
                        $statusIcon = 'fas fa-circle';
                        $badgeColor = '#FF69B4';
                        break;
                    case 'chua_bat_dau':
                        $statusClass = 'upcoming';
                        $statusText = 'Sắp live';
                        $statusIcon = 'fas fa-clock';
                        $badgeColor = '#28a745';
                        break;
                    case 'da_ket_thuc':
                        $statusClass = 'ended';
                        $statusText = 'Đã kết thúc';
                        $statusIcon = 'fas fa-stop';
                        $badgeColor = '#6c757d';
                        break;
                }
                
                // Xử lý ảnh
                $livestreamImage = !empty($livestream['image']) ? $livestream['image'] : 'default-live.jpg';
                $userAvatar = !empty($livestream['avatar']) ? $livestream['avatar'] : 'default-avatar.jpg';
                
                // Kiểm tra file ảnh có tồn tại không
                if (!file_exists('img/' . $livestreamImage)) {
                    $livestreamImage = 'default-live.jpg';
                }
                if (!file_exists('img/' . $userAvatar)) {
                    $userAvatar = 'default-avatar.jpg';
                }
                
                // Format số lượng viewers
                $viewerCount = $livestream['current_viewers'] ?? 0;
                $viewerText = $viewerCount > 1000 ? number_format($viewerCount/1000, 1) . 'K' : $viewerCount;
                ?>
                
                <div class="col-12 mb-4 livestream-card" data-category="1" data-status="<?= $statusClass ?>">
                    <div class="live-card bg-white border rounded-lg overflow-hidden shadow-sm">
                        <div class="row no-gutters">
                            <div class="col-md-4">
                                <div class="position-relative" style="height: 200px;">
                                    <img src="img/<?= htmlspecialchars($livestreamImage) ?>" 
                                         alt="<?= htmlspecialchars($livestream['title']) ?>" 
                                         class="img-fluid w-100 h-100" 
                                         style="object-fit: cover;">
                                    
                                    <!-- Status Badge - Top Left -->
                                    <div class="position-absolute" style="top: 8px; left: 8px; z-index: 10;">
                                        <span class="badge <?= $statusClass === 'live' ? 'animate-pulse' : '' ?>" 
                                              style="background-color: <?= $badgeColor ?>; color: white; padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: 600;">
                                            <i class="<?= $statusIcon ?> text-white mr-1" style="font-size: 8px;"></i><?= $statusText ?>
                                        </span>
                                    </div>
                                    
                                    <!-- Viewer Count - Top Right -->
                                    <div class="position-absolute" style="top: 8px; right: 8px; z-index: 10;">
                                        <span class="badge" style="background-color: rgba(0,0,0,0.7); color: white; padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: 600;">
                                            <i class="fas fa-user mr-1"></i><?= $viewerText ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-8">
                                <div class="p-3">
                                    <!-- Streamer Info -->
                                    <div class="d-flex align-items-center mb-2">
                                        <img src="img/<?= htmlspecialchars($userAvatar) ?>" 
                                             alt="<?= htmlspecialchars($livestream['username']) ?>" 
                                             class="rounded-circle mr-3" 
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                        <div>
                                            <h5 class="mb-1 font-weight-bold text-dark"><?= htmlspecialchars($livestream['username'] ?? 'Streamer') ?></h5>
                                            <small class="text-muted">
                                                <?= $statusText ?> • <?= $viewerText ?> người xem
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <!-- Livestream Title -->
                                    <h4 class="font-weight-bold text-dark mb-2"><?= htmlspecialchars($livestream['title']) ?></h4>
                                    
                                    <!-- Description -->
                                    <p class="text-muted mb-3">
                                        <?= htmlspecialchars(substr($livestream['description'], 0, 150)) ?>
                                        <?= strlen($livestream['description']) > 150 ? '...' : '' ?>
                                    </p>
                                    
                                    <!-- Action Button -->
                                    <div class="d-flex justify-content-between align-items-center">
                                        <?php if ($statusClass === 'live'): ?>
                                            <a href="index.php?watch&id=<?= $livestream['id'] ?>" class="btn btn-danger">
                                                <i class="fas fa-play mr-1"></i>Xem ngay
                                            </a>
                                        <?php elseif ($statusClass === 'upcoming'): ?>
                                            <button class="btn btn-outline-warning" onclick="setReminder(<?= $livestream['id'] ?>)">
                                                <i class="fas fa-bell mr-1"></i>Nhắc nhở
                                            </button>
                                        <?php else: ?>
                                            <a href="index.php?watch&id=<?= $livestream['id'] ?>" class="btn btn-outline-primary">
                                                <i class="fas fa-eye mr-1"></i>Xem lại
                                            </a>
                                        <?php endif; ?>
                                        
                                        <!-- Product Count -->
                                        <?php if ($livestream['product_count'] > 0): ?>
                                            <small class="text-muted">
                                                <i class="fas fa-box mr-1"></i><?= $livestream['product_count'] ?> sản phẩm
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

    </div>
    <!-- Content Wrapper End -->
</div>
<!-- Page Background End -->

<script>
// Function để đặt nhắc nhở
function setReminder(livestreamId) {
    if (confirm('Bạn có muốn nhận thông báo khi livestream này bắt đầu không?')) {
        // Lưu vào localStorage hoặc gửi request đến server
        let reminders = JSON.parse(localStorage.getItem('livestream_reminders') || '[]');
        if (!reminders.includes(livestreamId)) {
            reminders.push(livestreamId);
            localStorage.setItem('livestream_reminders', JSON.stringify(reminders));
            alert('Đã đặt nhắc nhở thành công!');
        } else {
            alert('Bạn đã đặt nhắc nhở cho livestream này rồi!');
        }
    }
}

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

// Auto refresh livestream data every 30 seconds
setInterval(function() {
    // Chỉ refresh nếu đang ở trang livestream
    if (window.location.href.includes('livestream')) {
        location.reload();
    }
}, 30000);
</script>

<?php include_once("view/footer.php"); ?>
