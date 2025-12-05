<?php
// Cho phép role: 1 (admin), 4 (adcontent)
if (!in_array($_SESSION['role'], [1, 4])) {
    echo "<script>
        alert('Bạn không đủ thẩm quyền truy cập!');
        
    </script>";
    header("refresh: 0; url='/ad'");
    exit;
}
?>

<?php
include_once(__DIR__ . "/../../controller/cKDbaidang.php");
$p = new ckdbaidang();

// Pagination settings
$itemsPerPage = 10; // Number of posts per page
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Get filter values
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_product = isset($_GET['product_type']) ? $_GET['product_type'] : '';
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Get all posts for summary counts and filter options
$all_data = $p->getallbaidang();

// Get total count for pagination (with filters applied)
$totalPosts = $p->countFilteredPosts($filter_status, $filter_product, $search_term);
$totalPages = ceil($totalPosts / $itemsPerPage);

// Ensure current page is within valid range
if ($currentPage > $totalPages && $totalPages > 0) {
    $currentPage = $totalPages;
    $offset = ($currentPage - 1) * $itemsPerPage;
}

// Get paginated and filtered posts
$data = $p->getPaginatedPosts($offset, $itemsPerPage, $filter_status, $filter_product, $search_term);

// Process form submissions
if (isset($_POST['btn_duyet'])) {
    $result = $p->getduyetbai($_POST['idbv']);
    
    // Lưu thông báo vào session
    if ($result) {
        $_SESSION['kdbaiviet_message'] = 'approved';
        $_SESSION['kdbaiviet_message_text'] = 'Bài đăng đã được duyệt thành công!';
    } else {
        $_SESSION['kdbaiviet_message'] = 'error';
        $_SESSION['kdbaiviet_message_text'] = 'Có lỗi xảy ra khi duyệt bài đăng. Vui lòng thử lại.';
    }
    
    // Redirect về trang quản lý bài viết ban đầu (không có tham số)
    require_once __DIR__ . '/../../helpers/url_helper.php';
    header("Location: " . getBaseUrl() . "/admin?qlkdbaiviet");
    exit();
} elseif (isset($_POST['btn_tuchoi'])) {
    $id = $_POST['idbv'];
    $ghichu = isset($_POST['ly_do_tu_choi']) ? $_POST['ly_do_tu_choi'] : '';
    $result = $p->gettuchoi($id, $ghichu);
    
    // Lưu thông báo vào session
    if ($result) {
        $_SESSION['kdbaiviet_message'] = 'rejected';
        $_SESSION['kdbaiviet_message_text'] = 'Bài đăng đã bị từ chối thành công!';
    } else {
        $_SESSION['kdbaiviet_message'] = 'error';
        $_SESSION['kdbaiviet_message_text'] = 'Có lỗi xảy ra khi từ chối bài đăng. Vui lòng thử lại.';
    }
    
    // Redirect về trang quản lý bài viết ban đầu (không có tham số)
    require_once __DIR__ . '/../../helpers/url_helper.php';
    header("Location: " . getBaseUrl() . "/admin?qlkdbaiviet");
    exit();
} elseif (isset($_POST['btn_anbai'])) {
    $id = $_POST['idbv'];
    $result = $p->getanbai($id);
    
    // Lưu thông báo vào session
    if ($result) {
        $_SESSION['kdbaiviet_message'] = 'hidden';
        $_SESSION['kdbaiviet_message_text'] = 'Bài đăng đã được ẩn thành công!';
    } else {
        $_SESSION['kdbaiviet_message'] = 'error';
        $_SESSION['kdbaiviet_message_text'] = 'Có lỗi xảy ra khi ẩn bài đăng. Vui lòng thử lại.';
    }
    
    // Redirect về trang quản lý bài viết ban đầu (không có tham số)
    require_once __DIR__ . '/../../helpers/url_helper.php';
    header("Location: " . getBaseUrl() . "/admin?qlkdbaiviet");
    exit();
}

// Kiểm tra và hiển thị thông báo từ session
$message = '';
$messageType = '';
if (isset($_SESSION['kdbaiviet_message'])) {
    $messageType = $_SESSION['kdbaiviet_message'];
    $message = isset($_SESSION['kdbaiviet_message_text']) ? $_SESSION['kdbaiviet_message_text'] : '';
    // Xóa thông báo sau khi đã lấy
    unset($_SESSION['kdbaiviet_message']);
    unset($_SESSION['kdbaiviet_message_text']);
}

// Get unique product types for filter dropdown
$product_types = [];
foreach ($all_data as $item) {
    if (!in_array($item['category_name'], $product_types)) {
        $product_types[] = $item['category_name'];
    }
}

// Function to generate pagination URL
function getPaginationUrl($page, $status, $product_type, $search) {
    $url = "qlkdbaiviet&page={$page}";
    if (!empty($status)) $url .= "&status={$status}";
    if (!empty($product_type)) $url .= "&product_type={$product_type}";
    if (!empty($search)) $url .= "&search={$search}";
    return $url;
}

// Count posts by status for summary cards
$waiting_count = 0;
$approved_count = 0;
$rejected_count = 0;
$sold_count = 0;
$hidden_count = 0;

foreach($all_data as $item) {
    if($item['status'] == "Chờ duyệt") $waiting_count++;
    if($item['status'] == "Đã duyệt") $approved_count++;
    if($item['status'] == "Từ chối duyệt") $rejected_count++;
    if($item['sale_status'] == "Đã bán") $sold_count++;
    if($item['sale_status'] == "Đã ẩn") $hidden_count++;
}
?>
<?php require_once __DIR__ . '/../../helpers/url_helper.php'; ?>
<link rel="stylesheet" href="<?php echo getBasePath() ?>/css/admin-common.css">
<style>
        /* CSS riêng cho trang kiểm duyệt bài đăng */
        /* CSS riêng cho trang kiểm duyệt bài đăng - chỉ override nếu cần */
        .kdbaidang-container {
            /* Đã được định nghĩa trong admin-common.css */
        }
        
        .status-message {
            padding: 15px;
            padding-right: 40px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            position: relative;
        }
        
        .status-message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-message.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .status-message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .status-message.warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .status-message i {
            margin-right: 10px;
            font-size: 20px;
        }
        
        .status-message .close-btn {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: inherit;
            opacity: 0.7;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.3s ease;
        }
        
        .status-message .close-btn:hover {
            opacity: 1;
        }
        
        /* Status cards CSS đã được định nghĩa trong admin-common.css */
        .status-card.hidden {
            border-top: 3px solid #6c757d;
        }
        
        .status-card.hidden .count {
            color: #6c757d;
        }
        
        /* Image styling in table */
        .admin-table img {
            max-width: 80px !important;
            max-height: 80px !important;
            width: auto !important;
            height: auto !important;
            object-fit: cover;
            border-radius: 5px;
            display: block;
            margin: 0 auto;
        }
        
        .admin-table td {
            vertical-align: middle !important;
        }
        
        /* Căn giữa hình ảnh trong cột */
        .admin-table td:nth-child(4) {
            text-align: center !important;
            vertical-align: middle !important;
        }
        
        /* Ensure images in detail rows are also limited */
        .detail-row img {
            max-width: 200px !important;
            max-height: 200px !important;
            width: auto !important;
            height: auto !important;
            object-fit: cover;
            border-radius: 5px;
        }
        
        /* Action buttons styling */
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .action-buttons .btn {
            white-space: nowrap;
            font-size: 0.85rem;
            padding: 5px 10px;
        }
    </style>
<div class="kdbaidang-container">
        <div class="admin-card">
            <h3 class="admin-card-title">
                Kiểm duyệt bài viết
                <span style="font-size: 0.9rem; color: #666; font-weight: normal;">(<?php echo $totalPosts; ?> bài viết)</span>
            </h3>
                        
                        <?php if (!empty($message)): ?>
                        <div class="status-message <?php 
                            if ($messageType == 'approved') echo 'success';
                            elseif ($messageType == 'rejected') echo 'info';
                            elseif ($messageType == 'hidden') echo 'warning';
                            else echo 'error';
                        ?>" id="statusMessage">
                            <i class="fas <?php 
                                if ($messageType == 'approved') echo 'fa-check-circle';
                                elseif ($messageType == 'rejected') echo 'fa-info-circle';
                                elseif ($messageType == 'hidden') echo 'fa-eye-slash';
                                else echo 'fa-exclamation-circle';
                            ?>"></i>
                            <span><?php echo htmlspecialchars($message); ?></span>
                            <button type="button" class="close-btn" onclick="closeStatusMessage()" aria-label="Đóng">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Status Summary Cards -->
                        <div class="stats-grid">
                            <div class="stat-card warning">
                                <h3>Chờ duyệt</h3>
                                <div class="number"><?php echo number_format($waiting_count); ?></div>
                            </div>
                            <div class="stat-card success">
                                <h3>Đã duyệt</h3>
                                <div class="number"><?php echo number_format($approved_count); ?></div>
                            </div>
                            <div class="stat-card danger">
                                <h3>Từ chối</h3>
                                <div class="number"><?php echo number_format($rejected_count); ?></div>
                            </div>
                            <div class="stat-card primary">
                                <h3>Đã bán</h3>
                                <div class="number"><?php echo number_format($sold_count); ?></div>
                            </div>
                            <div class="stat-card secondary">
                                <h3>Đã ẩn</h3>
                                <div class="number"><?php echo number_format($hidden_count); ?></div>
                            </div>
                            <div class="stat-card info">
                                <h3>Tổng số bài viết</h3>
                                <div class="number"><?php echo number_format($totalPosts); ?></div>
                            </div>
                        </div>
                        
                        <!-- Filter Section -->
                        <div class="filters">
                            <form action="" method="GET">
                                <input type="hidden" name="qlkdbaiviet" value="">
                                <div class="form-group">
                                    <label for="status">Trạng thái duyệt</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">Tất cả trạng thái</option>
                                        <option value="Chờ duyệt" <?php echo $filter_status == 'Chờ duyệt' ? 'selected' : ''; ?>>Chờ duyệt</option>
                                        <option value="Đã duyệt" <?php echo $filter_status == 'Đã duyệt' ? 'selected' : ''; ?>>Đã duyệt</option>
                                        <option value="Từ chối duyệt" <?php echo $filter_status == 'Từ chối duyệt' ? 'selected' : ''; ?>>Từ chối duyệt</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="product_type">Loại sản phẩm</label>
                                    <select class="form-control" id="product_type" name="product_type">
                                        <option value="">Tất cả loại</option>
                                        <?php foreach($product_types as $type): ?>
                                            <option value="<?php echo $type; ?>" <?php echo $filter_product == $type ? 'selected' : ''; ?>><?php echo $type; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="search">Tìm kiếm</label>
                                    <input type="text" class="form-control" id="search" name="search" placeholder="Nhập ID hoặc tên sản phẩm" value="<?php echo $search_term; ?>">
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-funnel-fill"></i> Lọc
                                    </button>
                                    <a href="/admin?qlkdbaiviet" class="btn btn-secondary" style="margin-left: 10px;">
                                        <i class="bi bi-arrow-clockwise"></i> Đặt lại
                                    </a>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Posts Table -->
                        <div class="admin-table-wrapper">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;"></th>
                                        <th><b>ID</b></th>
                                        <th><b>Loại sản phẩm</b></th>
                                        <th><b>Hình ảnh</b></th>
                                        <th><b>Trạng thái</b></th>
                                        <th><b>Thời pricen</b></th>
                                        <th><b>Xem</b></th>
                                        <th><b>Thao tác</b></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                if(count($data) > 0) {
                                    foreach($data as $r){
                                        $trang_thai = $r['status'];
                                        $badge_class = "";
                                        $icon = "";
                                        
                                        if($trang_thai == "Đã duyệt"){
                                            $badge_class = "success";
                                            $icon = '<i class="fas fa-check-circle"></i> ';
                                        } else if ($trang_thai == "Chờ duyệt") {
                                            $badge_class = "warning";
                                            $icon = '<i class="fas fa-clock"></i> ';
                                        } else if($trang_thai == "Từ chối duyệt") {
                                            $badge_class = "danger";
                                            $icon = '<i class="fas fa-times-circle"></i> ';
                                        } else {
                                            $badge_class = "info";
                                            $icon = '';
                                        }

                                        $trang_thai_ban = $r['sale_status'];
                                        $ban_badge = "";
                                        $ban_icon = "";
                                        $show_sale_status = false; // Chỉ hiển thị sale_status khi đã duyệt

                                        // Chỉ hiển thị "Đang bán" khi bài viết đã được duyệt
                                        if ($trang_thai == "Đã duyệt" && $trang_thai_ban == "Đang bán") {
                                            $ban_badge = "primary";
                                            $ban_icon = '<i class="fas fa-tag"></i> ';
                                            $show_sale_status = true;
                                        } else if ($trang_thai_ban == "Đã bán") {
                                            $ban_badge = "info";
                                            $ban_icon = '<i class="fas fa-shopping-cart"></i> ';
                                            $show_sale_status = true;
                                        } else if ($trang_thai_ban == "Đã ẩn") {
                                            $ban_badge = "secondary";
                                            $ban_icon = '<i class="fas fa-eye-slash"></i> ';
                                            $show_sale_status = true;
                                        }
                                        
                                        // Simulate timeline data (in a real app, this would come from the database)
                                        $timeline = [
                                            [
                                                'status' => 'Tạo bài đăng',
                                                'date' => $r['created_date'],
                                                'class' => 'active'
                                            ]
                                        ];
                                        
                                        if($trang_thai == "Chờ duyệt") {
                                            $timeline[] = [
                                                'status' => 'Đang chờ duyệt',
                                                'date' => $r['created_date'],
                                                'class' => 'pending'
                                            ];
                                        } else if($trang_thai == "Đã duyệt") {
                                            $timeline[] = [
                                                'status' => 'Đang chờ duyệt',
                                                'date' => $r['created_date'],
                                                'class' => 'active'
                                            ];
                                            $timeline[] = [
                                                'status' => 'Đã duyệt bởi Admin',
                                                'date' => $r['updated_date'],
                                                'class' => 'active'
                                            ];
                                        } else if($trang_thai == "Từ chối duyệt") {
                                            $timeline[] = [
                                                'status' => 'Đang chờ duyệt',
                                                'date' => $r['created_date'],
                                                'class' => 'active'
                                            ];
                                            $timeline[] = [
                                                'status' => 'Từ chối bởi Admin',
                                                'date' => $r['updated_date'],
                                                'class' => 'rejected'
                                            ];
                                        }
                                        
                                        if($trang_thai_ban == "Đã bán") {
                                            $timeline[] = [
                                                'status' => 'Đã bán',
                                                'date' => '',
                                                'class' => 'sold'
                                            ];
                                        }

                                        echo '<tr id="row-'.$r['id'].'">
                                            <td class="text-center">
                                                <span class="expand-row" data-id="'.$r['id'].'">
                                                    <i class="fas fa-chevron-down"></i>
                                                </span>
                                            </td>
                                            <td>#'.$r['id'].'</td>
                                            <td>'.$r['category_name'].'</td>
                                            <td><img src="/img/'.explode(',', $r['image'])[0].'" alt=""></td>
                                            <td>
                                                <div class="status-badge '.$badge_class.'">'.$icon.$trang_thai.'</div>';
                                                if($show_sale_status) {
                                                    echo '<div class="status-badge '.$ban_badge.'" style="margin-top: 10px;">'.$ban_icon.$trang_thai_ban.'</div>';
                                                }
                                            echo '</td>
                                            <td>
                                                <div>Đăng: '.$r['created_date'].'</div>
                                                <div>Cập nhật: '.$r['updated_date'].'</div>
                                            </td>
                                            <td>
                                                <a href="/admin?qlkdbaiviet&id='.$r['id'].'" class="btn btn-primary btn-sm">
                                                    Chi tiết
                                                </a>
                                            </td>';
                                            echo '<td>';
                                            echo '<div class="action-buttons">';
                                            
                                            // Hiển thị nút Duyệt cho bài viết "Chờ duyệt"
                                            if($trang_thai == "Chờ duyệt"){
                                                echo '<button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal" data-id="'.$r['id'].'" style="margin-right: 5px;">
                                                    <i class="fas fa-check"></i> Duyệt
                                                </button>';
                                                echo '<button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal" data-id="'.$r['id'].'">
                                                    <i class="fas fa-times"></i> Từ chối
                                                </button>';
                                            }
                                            
                                            // Hiển thị nút Duyệt lại cho bài viết "Từ chối duyệt"
                                            if($trang_thai == "Từ chối duyệt"){
                                                echo '<button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal" data-id="'.$r['id'].'">
                                                    <i class="fas fa-check"></i> Duyệt lại
                                                </button>';
                                            }
                                            
                                            // Hiển thị nút Ẩn bài cho bài viết đã duyệt và đang bán
                                            if($trang_thai == "Đã duyệt" && $trang_thai_ban == "Đang bán"){
                                                echo '<button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#hideModal" data-id="'.$r['id'].'">
                                                    <i class="fas fa-eye-slash"></i> Ẩn bài
                                                </button>';
                                            }
                                            
                                            echo '</div>';
                                            echo '</td>';
                                                
                                            
                                            
                                        echo '</tr>';
                                        
                                        // Expanded row with details
                                        echo '<tr class="detail-row" id="detail-'.$r['id'].'" style="display: none;">
                                            <td colspan="8">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h5>Chi tiết bài đăng</h5>
                                                        <div class="detail-row">
                                                            <div class="detail-label">Người đăng:</div>
                                                            <div class="detail-value">'.$r['username'].'</div>
                                                        </div>
                                                        <div class="detail-row">
                                                            <div class="detail-label">Tiêu đề:</div>
                                                            <div class="detail-value">'.$r['title'].'</div>
                                                        </div>
                                                        <div class="detail-row">
                                                            <div class="detail-label">Giá:</div>
                                                            <div class="detail-value">'.$r['price'].' VNĐ</div>
                                                        </div>
                                                        <div class="detail-row">
                                                            <div class="detail-label">Mô tả:</div>
                                                            <div class="detail-value">'.$r['description'].'</div>
                                                        </div>
                                                        <div class="detail-row">
                                                            <div class="detail-label">Ngày đăng:</div>
                                                            <div class="detail-value">'.$r['created_date'].'</div>
                                                        </div>';
                                                        
                                                        if($trang_thai == "Từ chối duyệt") {
                                                            echo '<div class="detail-row">
                                                                <div class="detail-label">Lý do từ chối:</div>
                                                                <div class="detail-value text-danger">'.$r['note'].'</div>
                                                            </div>';
                                                        }
                                                        
                                                    echo '</div>
                                                    <div class="col-md-6">
                                                        <h5>Lịch sử trạng thái</h5>
                                                        <div class="status-timeline">';
                                                        
                                                        foreach($timeline as $item) {
                                                            echo '<div class="timeline-item '.$item['class'].'">
                                                                '.$item['status'].'
                                                                <span class="timeline-date">'.$item['date'].'</span>
                                                            </div>';
                                                        }
                                                        
                                                        echo '</div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="8" class="empty-message">Không tìm thấy bài đăng nào phù hợp với điều kiện lọc</td></tr>';
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <div class="pagination-info">
                                Hiển thị <?php echo count($data); ?> trên tổng số <?php echo $totalPosts; ?> bản ghi
                            </div>
                            <div class="pagination">
                                <?php if ($currentPage > 1): ?>
                                    <a href="?<?php echo getPaginationUrl($currentPage - 1, $filter_status, $filter_product, $search_term); ?>">&lt;</a>
                                <?php endif; ?>
                                
                                <?php
                                    // Determine range of page numbers to show (current page ± 1)
                                    $startPage = max(1, $currentPage - 1);
                                    $endPage = min($totalPages, $currentPage + 1);
                                    
                                    // Show first page if not in range
                                    if ($startPage > 1): ?>
                                        <a href="?<?php echo getPaginationUrl(1, $filter_status, $filter_product, $search_term); ?>">1</a>
                                        <?php if ($startPage > 2): ?>
                                            <span class="ellipsis">...</span>
                                        <?php endif; ?>
                                    <?php endif;
                                    
                                    // Generate page links
                                    for ($i = $startPage; $i <= $endPage; $i++):
                                ?>
                                    <?php if ($i == $currentPage): ?>
                                        <span class="current"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="?<?php echo getPaginationUrl($i, $filter_status, $filter_product, $search_term); ?>"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <?php
                                    // Show last page if not in range
                                    if ($endPage < $totalPages): ?>
                                        <?php if ($endPage < $totalPages - 1): ?>
                                            <span class="ellipsis">...</span>
                                        <?php endif; ?>
                                        <a href="?<?php echo getPaginationUrl($totalPages, $filter_status, $filter_product, $search_term); ?>"><?php echo $totalPages; ?></a>
                                    <?php endif; ?>
                                
                                <?php if ($currentPage < $totalPages): ?>
                                    <a href="?<?php echo getPaginationUrl($currentPage + 1, $filter_status, $filter_product, $search_term); ?>">&gt;</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
        </div>
    </div>
    
    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Xác nhận duyệt bài đăng</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Bạn có chắc chắn muốn duyệt bài đăng này?</p>
                        <p>Sau khi duyệt, bài đăng sẽ được hiển thị công khai trên hệ thống.</p>
                        <input type="hidden" name="idbv" id="approve_post_id">
                        <!-- Preserve pagination and filter parameters -->
                        <input type="hidden" name="page" value="<?php echo $currentPage; ?>">
                        <input type="hidden" name="status" value="<?php echo $filter_status; ?>">
                        <input type="hidden" name="product_type" value="<?php echo $filter_product; ?>">
                        <input type="hidden" name="search" value="<?php echo $search_term; ?>">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-success" name="btn_duyet">Xác nhận duyệt</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Từ chối bài đăng</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="rejectModalMessage">Vui lòng cung cấp lý do từ chối bài đăng:</p>
                        <div class="modal-reason">
                            <textarea name="ly_do_tu_choi" id="rejectReason" placeholder="Nhập lý do từ chối hoặc hủy duyệt..." required></textarea>
                        </div>
                        <input type="hidden" name="idbv" id="reject_post_id">
                        <!-- Preserve pagination and filter parameters -->
                        <input type="hidden" name="page" value="<?php echo $currentPage; ?>">
                        <input type="hidden" name="status" value="<?php echo $filter_status; ?>">
                        <input type="hidden" name="product_type" value="<?php echo $filter_product; ?>">
                        <input type="hidden" name="search" value="<?php echo $search_term; ?>">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger" name="btn_tuchoi">Xác nhận từ chối</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Hide Modal -->
    <div class="modal fade" id="hideModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Ẩn bài đăng</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Bạn có chắc chắn muốn ẩn bài đăng này?</p>
                        <p>Sau khi ẩn, bài đăng sẽ không hiển thị công khai trên hệ thống.</p>
                        <input type="hidden" name="idbv" id="hide_post_id">
                        <!-- Preserve pagination and filter parameters -->
                        <input type="hidden" name="page" value="<?php echo $currentPage; ?>">
                        <input type="hidden" name="status" value="<?php echo $filter_status; ?>">
                        <input type="hidden" name="product_type" value="<?php echo $filter_product; ?>">
                        <input type="hidden" name="search" value="<?php echo $search_term; ?>">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-warning" name="btn_anbai">Xác nhận ẩn</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
<script>
        // Toggle row details
        document.querySelectorAll('.expand-row').forEach(function(element) {
            element.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const detailRow = document.getElementById('detail-' + id);
                const icon = this.querySelector('i');
                const mainRow = document.getElementById('row-' + id);
                
                if (detailRow.style.display === 'none') {
                    detailRow.style.display = 'table-row';
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                    if (!icon.classList.contains('fas')) {
                        icon.classList.add('fas');
                    }
                    mainRow.classList.add('expanded');
                } else {
                    detailRow.style.display = 'none';
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                    if (!icon.classList.contains('fas')) {
                        icon.classList.add('fas');
                    }
                    mainRow.classList.remove('expanded');
                }
            });
        });
        
        // Set post ID in modals
        document.getElementById('approveModal').addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const postId = button.getAttribute('data-id');
            document.getElementById('approve_post_id').value = postId;
            
            // Update modal title and message based on button text
            const buttonText = button.textContent.trim();
            const modalTitle = this.querySelector('.modal-title');
            const modalMessage = this.querySelector('.modal-body p');
            
            if (buttonText.includes('Duyệt lại')) {
                modalTitle.textContent = 'Xác nhận duyệt lại bài đăng';
                modalMessage.textContent = 'Bạn có chắc chắn muốn duyệt lại bài đăng này? Sau khi duyệt lại, bài đăng sẽ được hiển thị công khai trên hệ thống.';
            } else {
                modalTitle.textContent = 'Xác nhận duyệt bài đăng';
                modalMessage.textContent = 'Bạn có chắc chắn muốn duyệt bài đăng này? Sau khi duyệt, bài đăng sẽ được hiển thị công khai trên hệ thống.';
            }
        });
        
        document.getElementById('rejectModal').addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const postId = button.getAttribute('data-id');
            document.getElementById('reject_post_id').value = postId;
            
            // Reset textarea
            document.getElementById('rejectReason').value = '';
        });
        
        document.getElementById('hideModal').addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const postId = button.getAttribute('data-id');
            document.getElementById('hide_post_id').value = postId;
        });
        
        // Đóng thông báo khi click nút X
        function closeStatusMessage() {
            const message = document.getElementById('statusMessage');
            if (message) {
                message.style.transition = 'opacity 0.3s ease, height 0.3s ease, margin 0.3s ease, padding 0.3s ease';
                message.style.opacity = '0';
                message.style.height = '0';
                message.style.margin = '0';
                message.style.padding = '0';
                message.style.overflow = 'hidden';
                setTimeout(function() {
                    message.style.display = 'none';
                }, 300);
            }
        }
        
        // Tự động ẩn thông báo sau 3 giây
        document.addEventListener('DOMContentLoaded', function() {
            const message = document.getElementById('statusMessage');
            if (message) {
                setTimeout(function() {
                    closeStatusMessage();
                }, 3000);
            }
        });
</script>