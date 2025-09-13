<?php
include_once("controller/cKDbaidang.php");
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
    $p->getduyetbai($_POST['idbv']);
    
    // Preserve pagination and filter parameters
    require_once '../helpers/url_helper.php';
    $redirectUrl = getBasePath() . "/ad/kdbaidang?status=approved";
    if (!empty($filter_status)) $redirectUrl .= "&status={$filter_status}";
    if (!empty($filter_product)) $redirectUrl .= "&product_type={$filter_product}";
    if (!empty($search_term)) $redirectUrl .= "&search={$search_term}";
    if ($currentPage > 1) $redirectUrl .= "&page={$currentPage}";
    
    // Redirect to prevent form resubmission
    header("Location: {$redirectUrl}");
    exit();
} elseif (isset($_POST['btn_tuchoi'])) {
    $id = $_POST['idbv'];
    $ghichu = $_POST['ly_do_tu_choi'];
    $p->gettuchoi($id, $ghichu);
    
    // Preserve pagination and filter parameters
    $redirectUrl = getBasePath() . "/ad/kdbaidang?status=rejected";
    if (!empty($filter_status)) $redirectUrl .= "&status={$filter_status}";
    if (!empty($filter_product)) $redirectUrl .= "&product_type={$filter_product}";
    if (!empty($search_term)) $redirectUrl .= "&search={$search_term}";
    if ($currentPage > 1) $redirectUrl .= "&page={$currentPage}";
    
    header("Location: {$redirectUrl}");
    exit();
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
    $url = getBasePath() . "/ad/kdbaidang?page={$page}";
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

foreach($all_data as $item) {
    if($item['status'] == "Chờ duyệt") $waiting_count++;
    if($item['status'] == "Đã duyệt") $approved_count++;
    if($item['status'] == "Từ chối duyệt") $rejected_count++;
    if($item['status_ban'] == "Đã bán") $sold_count++;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Kiểm Duyệt Bài Đăng - Hệ Thống Quản Lý</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="../admin/src/assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../admin/src/assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- endinject -->
    <!-- inject:css -->
    <link rel="stylesheet" href="../admin/src/assets/css/style.css">
    <!-- endinject -->
    <link rel="shortcut icon" href="../admin/src/assets/images/favicon.ico" />
    <?php require_once '../helpers/url_helper.php'; ?>
    <link rel="stylesheet" href="<?= getBasePath() ?>/css/kdbaidang.css">
    <style>
        /* Pagination styles */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .pagination .page-item {
            margin: 0 3px;
        }
        
        .pagination .page-link {
            padding: 8px 16px;
            border-radius: 4px;
            border: 1px solid #ddd;
            color: #333;
            background-color: #fff;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .pagination .page-link:hover {
            background-color: #f5f5f5;
        }
        
        .pagination .active .page-link {
            background-color: #2196f3;
            color: white;
            border-color: #2196f3;
        }
        
        .pagination .disabled .page-link {
            color: #aaa;
            pointer-events: none;
            background-color: #f5f5f5;
        }
        
        .pagination-info {
            text-align: center;
            margin-top: 10px;
            color: #666;
            font-size: 14px;
        }
        
        /* Status message styles */
        .status-message {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            display: flex;
            align-items: center;
        }
        
        .status-message.success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }
        
        .status-message.info {
            background-color: #e3f2fd;
            color: #1565c0;
            border-left: 4px solid #1565c0;
        }
        
        .status-message i {
            margin-right: 10px;
            font-size: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title-with-count">
                            <h4 class="card-title">Kiểm Duyệt Bài Đăng</h4>
                            <span class="post-count"><?php echo $totalPosts; ?> bài đăng</span>
                        </div>
                        
                        <?php if (isset($_GET['status']) && $_GET['status'] == 'approved'): ?>
                        <div class="status-message success">
                            <i class="fa fa-check-circle"></i>
                            Bài đăng đã được duyệt thành công.
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['status']) && $_GET['status'] == 'rejected'): ?>
                        <div class="status-message info">
                            <i class="fa fa-info-circle"></i>
                            Bài đăng đã được từ chối.
                        </div>
                        <?php endif; ?>
                        
                        <!-- Status Summary Cards -->
                        <div class="status-summary">
                            <div class="status-card waiting">
                                <div class="count"><?php echo $waiting_count; ?></div>
                                <div class="label">Chờ duyệt</div>
                            </div>
                            <div class="status-card approved">
                                <div class="count"><?php echo $approved_count; ?></div>
                                <div class="label">Đã duyệt</div>
                            </div>
                            <div class="status-card rejected">
                                <div class="count"><?php echo $rejected_count; ?></div>
                                <div class="label">Từ chối</div>
                            </div>
                            <div class="status-card sold">
                                <div class="count"><?php echo $sold_count; ?></div>
                                <div class="label">Đã bán</div>
                            </div>
                        </div>
                        
                        <!-- Filter Section -->
                        <div class="filter-section">
                            <form action="" method="GET" class="filter-form">
                                <input type="hidden" name="kdbaidang" value="">
                                <div class="filter-group">
                                    <label for="status">Trạng thái duyệt</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">Tất cả trạng thái</option>
                                        <option value="Chờ duyệt" <?php echo $filter_status == 'Chờ duyệt' ? 'selected' : ''; ?>>Chờ duyệt</option>
                                        <option value="Đã duyệt" <?php echo $filter_status == 'Đã duyệt' ? 'selected' : ''; ?>>Đã duyệt</option>
                                        <option value="Từ chối duyệt" <?php echo $filter_status == 'Từ chối duyệt' ? 'selected' : ''; ?>>Từ chối duyệt</option>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="product_type">Loại sản phẩm</label>
                                    <select class="form-control" id="product_type" name="product_type">
                                        <option value="">Tất cả loại</option>
                                        <?php foreach($product_types as $type): ?>
                                            <option value="<?php echo $type; ?>" <?php echo $filter_product == $type ? 'selected' : ''; ?>><?php echo $type; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="search">Tìm kiếm</label>
                                    <input type="text" class="form-control" id="search" name="search" placeholder="Nhập ID hoặc tên sản phẩm" value="<?php echo $search_term; ?>">
                                </div>
                                <div class="filter-buttons">
                                    <button type="submit" class="btn btn-primary">Lọc</button>
                                    <a href="<?= getBasePath() ?>/ad/kdbaidang" class="btn btn-outline-secondary">Đặt lại</a>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Posts Table -->
                        <div class="table-responsive">
                            <table class="table border">
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
                                        $status = $r['status'];
                                        $badge_class = "";
                                        $icon = "";
                                        
                                        if($status == "Đã duyệt"){
                                            $badge_class = "bg-success text-white";
                                            $icon = '<i class="fa fa-check-circle"></i> ';
                                        } else if ($status == "Chờ duyệt") {
                                            $badge_class = "bg-warning text-dark";
                                            $icon = '<i class="fa fa-clock"></i> ';
                                        } else if($status == "Từ chối duyệt") {
                                            $badge_class = "bg-danger text-white";
                                            $icon = '<i class="fa fa-times-circle"></i> ';
                                        } else {
                                            $badge_class = "bg-secondary text-white";
                                            $icon = '';
                                        }

                                        $status_ban = $r['status_ban'];
                                        $ban_badge = "";
                                        $ban_icon = "";

                                        if ($status_ban == "Đã bán") {
                                            $ban_badge = "bg-info text-white";
                                            $ban_icon = '<i class="fa fa-shopping-cart"></i> ';
                                        } else if ($status_ban == "Đang bán") {
                                            $ban_badge = "bg-light text-dark";
                                            $ban_icon = '<i class="fa fa-tag"></i> ';
                                        } else {
                                            $ban_badge = "bg-secondary text-white";
                                            $ban_icon = '';
                                        }
                                        
                                        // Simulate timeline data (in a real app, this would come from the database)
                                        $timeline = [
                                            [
                                                'status' => 'Tạo bài đăng',
                                                'date' => $r['created_date'],
                                                'class' => 'active'
                                            ]
                                        ];
                                        
                                        if($status == "Chờ duyệt") {
                                            $timeline[] = [
                                                'status' => 'Đang chờ duyệt',
                                                'date' => $r['created_date'],
                                                'class' => 'pending'
                                            ];
                                        } else if($status == "Đã duyệt") {
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
                                        } else if($status == "Từ chối duyệt") {
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
                                        
                                        if($status_ban == "Đã bán") {
                                            $timeline[] = [
                                                'status' => 'Đã bán',
                                                'date' => '',
                                                'class' => 'sold'
                                            ];
                                        }

                                        echo '<tr id="row-'.$r['id'].'">
                                            <td class="text-center">
                                                <span class="expand-row" data-id="'.$r['id'].'">
                                                    <i class="fa fa-chevron-down"></i>
                                                </span>
                                            </td>
                                            <td>'.$r['id'].'</td>
                                            <td>'.$r['category_name'].'</td>
                                            <td><img src="<?= getBasePath() ?>/img/'.explode(',', $r['image'])[0].'" alt=""></td>
                                            <td>
                                                <div class="status-badge '.$badge_class.'">'.$icon.$status.'</div>
                                                <div class="status-badge '.$ban_badge.'" style="margin-top: 5px;">'.$ban_icon.$status_ban.'</div>
                                            </td>
                                            <td>
                                                <div>Đăng: '.$r['created_date'].'</div>
                                                <div>Cập nhật: '.$r['updated_date'].'</div>
                                            </td>
                                            <td>
                                                <form action="<?= getBasePath() ?>/ad/kdbaidang?ct&id='.$r['id'].'" method="post">
                                                    <input type="hidden" name="idsp" value="'.$r['id'].'">
                                                    <div class="action-buttons">
                                                        <button type="submit" class="btn btn-primary btn-sm" name="btn_ct" data-bs-toggle="modal" data-bs-target="#viewDetailsModal" data-id="'.$r['id'].'">
                                                            <i class="fa fa-eye"></i> Chi tiết
                                                        </button>
                                                    </div>
                                                </form>
                                            </td>';
                                            echo '<td>';
                                            if($status == "Chờ duyệt"){
                                                echo '<div class="action-buttons">  
                                                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal" data-id="'.$r['id'].'">
                                                        <i class="fa fa-check"></i> Duyệt
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal" data-id="'.$r['id'].'">
                                                        <i class="fa fa-times"></i> Từ chối
                                                    </button>
                                                </div>';
                                            } 
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
                                                            <div class="detail-value">'.$r['comment'].'</div>
                                                        </div>
                                                        <div class="detail-row">
                                                            <div class="detail-label">Ngày đăng:</div>
                                                            <div class="detail-value">'.$r['created_date'].'</div>
                                                        </div>';
                                                        
                                                        if($status == "Từ chối duyệt") {
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
                                    echo '<tr><td colspan="8" class="text-center">Không tìm thấy bài đăng nào phù hợp với điều kiện lọc</td></tr>';
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <div class="pagination-info">
                                Hiển thị <?php echo count($data); ?> trên tổng số <?php echo $totalPosts; ?> bài đăng
                            </div>
                            <nav aria-label="Page navigation">
                                <ul class="pagination">
                                    <!-- First page link -->
                                    <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?php echo getPaginationUrl(1, $filter_status, $filter_product, $search_term); ?>" aria-label="First">
                                            <i class="fa fa-angle-double-left"></i>
                                        </a>
                                    </li>
                                    
                                    <!-- Previous page link -->
                                    <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?php echo getPaginationUrl($currentPage - 1, $filter_status, $filter_product, $search_term); ?>" aria-label="Previous">
                                            <i class="fa fa-angle-left"></i>
                                        </a>
                                    </li>
                                    
                                    <!-- Page numbers -->
                                    <?php
                                        // Determine range of page numbers to show
                                        $startPage = max(1, $currentPage - 2);
                                        $endPage = min($totalPages, $startPage + 4);
                                        
                                        // Adjust start page if we're near the end
                                        if ($endPage - $startPage < 4) {
                                            $startPage = max(1, $endPage - 4);
                                        }
                                        
                                        // Generate page links
                                        for ($i = $startPage; $i <= $endPage; $i++):
                                    ?>
                                        <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                                            <a class="page-link" href="<?php echo getPaginationUrl($i, $filter_status, $filter_product, $search_term); ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <!-- Next page link -->
                                    <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?php echo getPaginationUrl($currentPage + 1, $filter_status, $filter_product, $search_term); ?>" aria-label="Next">
                                            <i class="fa fa-angle-right"></i>
                                        </a>
                                    </li>
                                    
                                    <!-- Last page link -->
                                    <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?php echo getPaginationUrl($totalPages, $filter_status, $filter_product, $search_term); ?>" aria-label="Last">
                                            <i class="fa fa-angle-double-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
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
                        <p>Vui lòng cung cấp lý do từ chối bài đăng:</p>
                        <div class="modal-reason">
                            <textarea name="ly_do_tu_choi" placeholder="Nhập lý do từ chối..." required></textarea>
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
    
    <!-- JavaScript -->
    <script src="../admin/src/assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="../admin/src/assets/js/off-canvas.js"></script>
    <script src="../admin/src/assets/js/hoverable-collapse.js"></script>
    <script src="../admin/src/assets/js/misc.js"></script>
    
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
                    mainRow.classList.add('expanded');
                } else {
                    detailRow.style.display = 'none';
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                    mainRow.classList.remove('expanded');
                }
            });
        });
        
        // Set post ID in modals
        document.getElementById('approveModal').addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const postId = button.getAttribute('data-id');
            document.getElementById('approve_post_id').value = postId;
        });
        
        document.getElementById('rejectModal').addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const postId = button.getAttribute('data-id');
            document.getElementById('reject_post_id').value = postId;
        });
    </script>
</body>
</html>