<?php
if ($_SESSION['role'] != 1) {
    echo "<script>
        alert('Bạn không đủ thẩm quyền truy cập!');
        
    </script>";
    header("refresh: 0; url='/ad'");
    exit;
}
?>

<?php
include_once(__DIR__ . "/../../controller/cQLdanhmuc.php");
$p = new cLoaiSanPham();

// Pagination settings
$itemsPerPage = 10; // Number of categories per page
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Get filter values
$parentFilter = isset($_GET['parent_filter']) ? $_GET['parent_filter'] : 'all';
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
// Mặc định hiển thị cả danh mục đã ẩn trong admin
$showHidden = isset($_GET['show_hidden']) ? ($_GET['show_hidden'] == '1') : true;

// Get all parent categories for dropdowns (bao gồm cả đã ẩn)
$parentCategories = $p->getAllParentCategories(true);

// Get total count for pagination
$totalCategories = $p->countChildCategories($parentFilter, $searchTerm, $showHidden);
$totalPages = ceil($totalCategories / $itemsPerPage);

// Ensure current page is within valid range
if ($currentPage > $totalPages && $totalPages > 0) {
    $currentPage = $totalPages;
    $offset = ($currentPage - 1) * $itemsPerPage;
}

// Get paginated categories
$childCategories = $p->getPaginatedChildCategories($offset, $itemsPerPage, $parentFilter, $searchTerm, $showHidden);

// Get category statistics
$stats = $p->getCategoryStats();

// Process add parent category
if (isset($_POST['btn_add_parent'])) {
    $name = $_POST['parent_name'];
    $result = $p->addParentCategory($name);
    
    if ($result['success']) {
        header("Location: ?qldanhmuc&status=parent_added");
        exit();
    } else {
        $errorMessage = $result['message'];
    }
}

// Process add child category
if (isset($_POST['btn_add_child'])) {
    $name = $_POST['child_name'];
    $parentId = $_POST['parent_id'];
    $result = $p->addChildCategory($name, $parentId);
    
    if ($result['success']) {
        header("Location: ?qldanhmuc&status=child_added");
        exit();
    } else {
        $errorMessage = $result['message'];
    }
}

// Process update parent category
if (isset($_POST['btn_update_parent'])) {
    $id = $_POST['parent_id'];
    $name = $_POST['parent_name'];
    $result = $p->updateParentCategory($id, $name);
    
    if ($result['success']) {
        header("Location: ?qldanhmuc&status=parent_updated");
        exit();
    } else {
        $errorMessage = $result['message'];
    }
}

// Process update child category
if (isset($_POST['btn_update_child'])) {
    $id = $_POST['child_id'];
    $name = $_POST['child_name'];
    $parentId = $_POST['parent_id'];
    $result = $p->updateChildCategory($id, $name, $parentId);
    
    if ($result['success']) {
        header("Location: ?qldanhmuc&status=child_updated");
        exit();
    } else {
        $errorMessage = $result['message'];
    }
}

// Process hide parent category
if (isset($_GET['hide_parent']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $p->hideParentCategory($id);
    
    if ($result['success']) {
        header("Location: ?qldanhmuc&status=parent_hidden");
        exit();
    } else {
        $errorMessage = $result['message'];
        header("Location: ?qldanhmuc&status=error&message=" . urlencode($errorMessage));
        exit();
    }
}

// Process hide child category
if (isset($_GET['hide_child']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $p->hideChildCategory($id);
    
    if ($result['success']) {
        header("Location: ?qldanhmuc&status=child_hidden");
        exit();
    } else {
        $errorMessage = $result['message'];
        header("Location: ?qldanhmuc&status=error&message=" . urlencode($errorMessage));
        exit();
    }
}

// Process restore parent category
if (isset($_GET['restore_parent']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $p->restoreParentCategory($id);
    
    if ($result['success']) {
        header("Location: ?qldanhmuc&status=parent_restored");
        exit();
    } else {
        $errorMessage = $result['message'];
        header("Location: ?qldanhmuc&status=error&message=" . urlencode($errorMessage));
        exit();
    }
}

// Process restore child category
if (isset($_GET['restore_child']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $p->restoreChildCategory($id);
    
    if ($result['success']) {
        header("Location: ?qldanhmuc&status=child_restored");
        exit();
    } else {
        $errorMessage = $result['message'];
        header("Location: ?qldanhmuc&status=error&message=" . urlencode($errorMessage));
        exit();
    }
}

// Function to generate pagination URL
function getPaginationUrl($page, $parentFilter, $searchTerm, $showHidden = false) {
    $url = "qldanhmuc&page={$page}";
    if ($parentFilter !== 'all') $url .= "&parent_filter={$parentFilter}";
    if (!empty($searchTerm)) $url .= "&search=" . urlencode($searchTerm);
    if ($showHidden) $url .= "&show_hidden=1";
    return $url;
}
?>

<?php require_once __DIR__ . '/../../helpers/url_helper.php'; ?>
<link rel="stylesheet" href="<?php echo getBasePath() ?>/css/admin-common.css">
<style>
    /* CSS riêng cho trang quản lý danh mục */
    /* CSS riêng cho trang quản lý danh mục - chỉ override nếu cần */
    .category-container {
        /* Đã được định nghĩa trong admin-common.css */
    }
    .btn a {
      text-decoration: none;
      color: #ffffff;
    }
    
    .action-message {
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 5px;
      display: flex;
      align-items: center;
    }
    
    .action-message i {
      margin-right: 10px;
      font-size: 20px;
    }
    
    .action-message.error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    
    .action-message:not(.error) {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    
    .top-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      flex-wrap: wrap;
      gap: 15px;
    }
    
    .action-buttons {
      display: flex;
      gap: 10px;
    }
    
    /* Stats cards CSS đã được định nghĩa trong admin-common.css */
    
    .modal-form-group {
      margin-bottom: 15px;
    }
    
    .modal-form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: 500;
    }
    
    .modal-form-group input,
    .modal-form-group select {
      width: 100%;
      padding: 8px 12px;
      border-radius: 4px;
      border: 1px solid #ddd;
    }
    
    
    .category-badge {
      display: inline-block;
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 500;
      background-color: #d4edda;
      color: #155724;
      margin-right: 5px;
      white-space: nowrap;
    }
    
    .status-badge-category {
      display: inline-block;
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 500;
      white-space: nowrap;
    }
    
    .status-badge-category.success {
      background-color: #cce5ff;
      color: #004085;
    }
    
    .status-badge-category.secondary {
      background-color: #e2e3e5;
      color: #383d41;
    }
    
    .parent-category-section {
      margin-bottom: 30px;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 20px;
      background-color: #f9f9f9;
    }
    
    .parent-category-section h4 {
      margin-top: 0;
      margin-bottom: 20px;
      color: #333;
      border-bottom: 1px solid #ddd;
      padding-bottom: 10px;
    }
    
    .parent-category-list {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-bottom: 20px;
    }
    
    .parent-category-item {
      display: flex;
      align-items: center;
      background-color: #fff;
      border: 1px solid #ddd;
      border-radius: 4px;
      padding: 8px 12px;
    }
    
    .parent-category-name {
      margin-right: 10px;
      font-weight: 500;
    }
    
    .parent-category-actions {
      display: flex;
      gap: 5px;
    }
    
    .parent-category-actions button {
      padding: 2px 5px;
      font-size: 12px;
    }
    
    .child-count-badge {
      background-color: #f0f0f0;
      color: #666;
      border-radius: 10px;
      padding: 2px 8px;
      font-size: 11px;
      margin-left: 5px;
    }
    
    .tab-navigation {
      display: flex;
      margin-bottom: 20px;
      border-bottom: 1px solid #ddd;
    }
    
    .tab-item {
      padding: 10px 20px;
      cursor: pointer;
      border: 1px solid transparent;
      border-bottom: none;
      border-radius: 4px 4px 0 0;
      margin-right: 5px;
      background-color: #f5f5f5;
    }
    
    .tab-item.active {
      background-color: #fff;
      border-color: #ddd;
      border-bottom-color: #fff;
      font-weight: 500;
      margin-bottom: -1px;
    }
    
    .tab-content {
      display: none;
    }
    
    .tab-content.active {
      display: block;
    }
  </style>
<div class="category-container">
    <div class="admin-card">
      <h3 class="admin-card-title">Quản lý danh mục sản phẩm</h3>
        
        <?php if (isset($_GET['status'])): ?>
          <?php if ($_GET['status'] == 'parent_added'): ?>
            <div class="action-message">
              <i class="mdi mdi-check-circle"></i>
              Danh mục cha đã được thêm thành công.
            </div>
          <?php elseif ($_GET['status'] == 'child_added'): ?>
            <div class="action-message">
              <i class="mdi mdi-check-circle"></i>
              Danh mục con đã được thêm thành công.
            </div>
          <?php elseif ($_GET['status'] == 'parent_updated'): ?>
            <div class="action-message">
              <i class="mdi mdi-check-circle"></i>
              Danh mục cha đã được cập nhật thành công.
            </div>
          <?php elseif ($_GET['status'] == 'child_updated'): ?>
            <div class="action-message">
              <i class="mdi mdi-check-circle"></i>
              Danh mục con đã được cập nhật thành công.
            </div>
          <?php elseif ($_GET['status'] == 'parent_hidden'): ?>
            <div class="action-message">
              <i class="mdi mdi-check-circle"></i>
              Danh mục cha đã được ẩn thành công.
            </div>
          <?php elseif ($_GET['status'] == 'child_hidden'): ?>
            <div class="action-message">
              <i class="mdi mdi-check-circle"></i>
              Danh mục con đã được ẩn thành công.
            </div>
          <?php elseif ($_GET['status'] == 'parent_restored'): ?>
            <div class="action-message">
              <i class="mdi mdi-check-circle"></i>
              Danh mục cha đã được hiển thị lại thành công.
            </div>
          <?php elseif ($_GET['status'] == 'child_restored'): ?>
            <div class="action-message">
              <i class="mdi mdi-check-circle"></i>
              Danh mục con đã được hiển thị lại thành công.
            </div>
          <?php elseif ($_GET['status'] == 'error'): ?>
            <div class="action-message error">
              <i class="mdi mdi-alert-circle"></i>
              <?php echo isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Đã xảy ra lỗi.'; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
          <div class="stat-card primary">
            <h3>Tổng danh mục cha</h3>
            <div class="number"><?php echo number_format($stats['total_parent_categories']); ?></div>
          </div>
          <div class="stat-card success">
            <h3>Tổng danh mục con</h3>
            <div class="number"><?php echo number_format($stats['total_child_categories']); ?></div>
          </div>
          <div class="stat-card info">
            <h3>Tổng số danh mục</h3>
            <div class="number"><?php echo number_format($stats['total_parent_categories'] + $stats['total_child_categories']); ?></div>
          </div>
        </div>
        
        <!-- Tab Navigation -->
        <div class="tab-navigation">
          <div class="tab-item" data-tab="parent-categories">Danh mục cha</div>
          <div class="tab-item active" data-tab="child-categories">Danh mục con</div>
        </div>
        
        <!-- Parent Categories Tab -->
        <div class="tab-content" id="parent-categories">
          <!-- Top Actions -->
          <div class="top-actions">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addParentCategoryModal">
              <i class="mdi mdi-plus-circle"></i> Thêm danh mục cha mới
            </button>
          </div>
          
          <div class="parent-category-section">
            <h4>Danh sách danh mục cha</h4>
            
            <?php if (empty($parentCategories)): ?>
              <div class="empty-message">
                Chưa có danh mục cha nào. Vui lòng thêm danh mục cha mới.
              </div>
            <?php else: ?>
              <div class="parent-category-list">
                <?php foreach($parentCategories as $parent): ?>
                  <?php 
                    $childCount = $p->countChildCategories($parent['parent_category_id']);
                    $isHidden = isset($parent['is_hidden']) && $parent['is_hidden'] == 1;
                  ?>
                  <div class="parent-category-item" <?php echo $isHidden ? 'style="opacity: 0.6; background-color: #f5f5f5;"' : ''; ?>>
                    <div class="parent-category-name">
                      <?php echo $parent['parent_category_name']; ?>
                      <?php if ($isHidden): ?>
                        <span class="badge bg-secondary" style="margin-left: 5px;">Đã ẩn</span>
                      <?php endif; ?>
                      <span class="child-count-badge"><?php echo $childCount; ?> danh mục con</span>
                    </div>
                    <div class="parent-category-actions">
                      <?php if (!$isHidden): ?>
                        <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#editParentCategoryModal" data-id="<?php echo $parent['parent_category_id']; ?>" data-name="<?php echo $parent['parent_category_name']; ?>">
                          <i class="mdi mdi-pencil"></i>
                        </button>
                        <?php if ($childCount == 0): ?>
                          <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#hideParentModal" data-id="<?php echo $parent['parent_category_id']; ?>" data-name="<?php echo htmlspecialchars($parent['parent_category_name']); ?>">
                            <i class="mdi mdi-eye-off"></i>
                          </button>
                        <?php else: ?>
                          <button type="button" class="btn btn-outline-warning btn-sm" disabled title="Không thể ẩn danh mục có danh mục con">
                            <i class="mdi mdi-eye-off"></i>
                          </button>
                        <?php endif; ?>
                      <?php else: ?>
                        <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#restoreParentModal" data-id="<?php echo $parent['parent_category_id']; ?>" data-name="<?php echo htmlspecialchars($parent['parent_category_name']); ?>">
                          <i class="mdi mdi-eye"></i> Hiển thị lại
                        </button>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
        
        <!-- Child Categories Tab -->
        <div class="tab-content active" id="child-categories">
          <!-- Filter Section -->
          <div class="filters">
            <form method="GET" action="">
              <input type="hidden" name="qldanhmuc" value="">
              <div class="form-group">
                <label for="parent_filter">Danh mục cha</label>
                <select name="parent_filter" id="parent_filter" class="form-control">
                  <option value="all" <?php echo $parentFilter === 'all' ? 'selected' : ''; ?>>Tất cả danh mục cha</option>
                  <?php foreach($parentCategories as $parent): ?>
                    <option value="<?php echo $parent['parent_category_id']; ?>" <?php echo $parentFilter == $parent['parent_category_id'] ? 'selected' : ''; ?>>
                      <?php echo $parent['parent_category_name']; ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label for="search">Tìm kiếm</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Tên danh mục" value="<?php echo $searchTerm; ?>">
              </div>
              <div class="form-group">
                <button type="submit" class="btn btn-primary">
                  <i class="bi bi-funnel-fill"></i> Lọc
                </button>
                <a href="?qldanhmuc" class="btn btn-secondary" style="margin-left: 10px;">
                  <i class="bi bi-arrow-clockwise"></i> Đặt lại
                </a>
              </div>
            </form>
          </div>
          
          <!-- Top Actions -->
          <div class="top-actions">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addChildCategoryModal">
              <i class="mdi mdi-plus-circle"></i> Thêm danh mục con mới
            </button>
          </div>
          
          <div class="admin-table-wrapper">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Tên danh mục con</th>
                  <th>Danh mục cha</th>
                  <th>Trạng thái</th>
                  <th>Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($childCategories)): ?>
                <tr>
                  <td colspan="5" class="empty-message">
                    Không tìm thấy danh mục con nào<?php echo $parentFilter !== 'all' || !empty($searchTerm) ? ' với điều kiện đã chọn' : ''; ?>.
                  </td>
                </tr>
                <?php else: ?>
                  <?php foreach($childCategories as $category): ?>
                    <?php $isHidden = isset($category['is_hidden']) && $category['is_hidden'] == 1; ?>
                    <tr <?php echo $isHidden ? 'style="opacity: 0.6; background-color: #f5f5f5;"' : ''; ?>>
                      <td><strong>#<?php echo $category['id']; ?></strong></td>
                      <td><?php echo $category['category_name']; ?></td>
                      <td>
                        <span class="category-badge"><?php echo $category['parent_category_name']; ?></span>
                      </td>
                      <td>
                        <?php if ($isHidden): ?>
                          <span class="status-badge-category secondary">Đã ẩn</span>
                        <?php else: ?>
                          <span class="status-badge-category success">Đang hoạt động</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if (!$isHidden): ?>
                          <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#editChildCategoryModal" 
                            data-id="<?php echo $category['id']; ?>" 
                            data-name="<?php echo $category['category_name']; ?>" 
                            data-parent="<?php echo $category['parent_category_id']; ?>">
                            <i class="mdi mdi-pencil"></i> Sửa
                          </button>
                          
                          <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#hideChildModal" data-id="<?php echo $category['id']; ?>" data-name="<?php echo htmlspecialchars($category['category_name']); ?>">
                            <i class="mdi mdi-eye-off"></i> Ẩn
                          </button>
                        <?php else: ?>
                          <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#restoreChildModal" data-id="<?php echo $category['id']; ?>" data-name="<?php echo htmlspecialchars($category['category_name']); ?>">
                            <i class="mdi mdi-eye"></i> Hiển thị lại
                          </button>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          
          <!-- Pagination -->
          <?php if ($totalPages > 1): ?>
            <div class="pagination-info">
              Hiển thị <?php echo count($childCategories); ?> trên tổng số <?php echo $totalCategories; ?> bản ghi
            </div>
            <div class="pagination">
              <?php if ($currentPage > 1): ?>
                <a href="?<?php echo getPaginationUrl($currentPage - 1, $parentFilter, $searchTerm, $showHidden); ?>">&lt;</a>
              <?php endif; ?>
              
              <?php
                // Determine range of page numbers to show (current page ± 1)
                $startPage = max(1, $currentPage - 1);
                $endPage = min($totalPages, $currentPage + 1);
                
                // Show first page if not in range
                if ($startPage > 1): ?>
                  <a href="?<?php echo getPaginationUrl(1, $parentFilter, $searchTerm, $showHidden); ?>">1</a>
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
                  <a href="?<?php echo getPaginationUrl($i, $parentFilter, $searchTerm, $showHidden); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
              <?php endfor; ?>
              
              <?php
                // Show last page if not in range
                if ($endPage < $totalPages): ?>
                  <?php if ($endPage < $totalPages - 1): ?>
                    <span class="ellipsis">...</span>
                  <?php endif; ?>
                  <a href="?<?php echo getPaginationUrl($totalPages, $parentFilter, $searchTerm, $showHidden); ?>"><?php echo $totalPages; ?></a>
                <?php endif; ?>
              
              <?php if ($currentPage < $totalPages): ?>
                <a href="?<?php echo getPaginationUrl($currentPage + 1, $parentFilter, $searchTerm, $showHidden); ?>">&gt;</a>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
    </div>
  </div>
  
  <!-- Add Parent Category Modal -->
  <div class="modal fade" id="addParentCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form method="POST">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Thêm danh mục cha mới</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="modal-form-group">
              <label for="parent_name">Tên danh mục cha</label>
              <input type="text" name="parent_name" id="parent_name" required placeholder="Nhập tên danh mục cha">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" class="btn btn-primary" name="btn_add_parent">Thêm danh mục</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  
  <!-- Edit Parent Category Modal -->
  <div class="modal fade" id="editParentCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form method="POST">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Sửa danh mục cha</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="modal-form-group">
              <label for="edit_parent_name">Tên danh mục cha</label>
              <input type="text" name="parent_name" id="edit_parent_name" required>
              <input type="hidden" name="parent_id" id="edit_parent_id">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" class="btn btn-primary" name="btn_update_parent">Cập nhật</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  
  <!-- Add Child Category Modal -->
  <div class="modal fade" id="addChildCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form method="POST">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Thêm danh mục con mới</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="modal-form-group">
              <label for="child_name">Tên danh mục con</label>
              <input type="text" name="child_name" id="child_name" required placeholder="Nhập tên danh mục con">
            </div>
            <div class="modal-form-group">
              <label for="parent_id">Danh mục cha</label>
              <select name="parent_id" id="parent_id" required>
                <option value="">-- Chọn danh mục cha --</option>
                <?php foreach($parentCategories as $parent): ?>
                  <option value="<?php echo $parent['parent_category_id']; ?>"><?php echo $parent['parent_category_name']; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" class="btn btn-primary" name="btn_add_child">Thêm danh mục</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  
  <!-- Edit Child Category Modal -->
  <div class="modal fade" id="editChildCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form method="POST">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Sửa danh mục con</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="modal-form-group">
              <label for="edit_child_name">Tên danh mục con</label>
              <input type="text" name="child_name" id="edit_child_name" required>
            </div>
            <div class="modal-form-group">
              <label for="edit_child_parent">Danh mục cha</label>
              <select name="parent_id" id="edit_child_parent" required>
                <option value="">-- Chọn danh mục cha --</option>
                <?php foreach($parentCategories as $parent): ?>
                  <option value="<?php echo $parent['parent_category_id']; ?>"><?php echo $parent['parent_category_name']; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <input type="hidden" name="child_id" id="edit_child_id">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" class="btn btn-primary" name="btn_update_child">Cập nhật</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  
  <!-- Hide Parent Category Modal -->
  <div class="modal fade" id="hideParentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form method="GET" action="">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Ẩn danh mục cha</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Bạn có chắc chắn muốn ẩn danh mục cha "<span id="hideParentName"></span>"?</p>
            <p>Sau khi ẩn, danh mục sẽ không hiển thị công khai trên hệ thống.</p>
            <input type="hidden" name="qldanhmuc" value="">
            <input type="hidden" name="hide_parent" value="">
            <input type="hidden" name="id" id="hideParentId">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" class="btn btn-warning">Xác nhận ẩn</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  
  <!-- Restore Parent Category Modal -->
  <div class="modal fade" id="restoreParentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form method="GET" action="">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Hiển thị lại danh mục cha</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Bạn có chắc chắn muốn hiển thị lại danh mục cha "<span id="restoreParentName"></span>"?</p>
            <p>Sau khi hiển thị lại, danh mục sẽ được hiển thị công khai trên hệ thống.</p>
            <input type="hidden" name="qldanhmuc" value="">
            <input type="hidden" name="restore_parent" value="">
            <input type="hidden" name="id" id="restoreParentId">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" class="btn btn-success">Xác nhận hiển thị lại</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  
  <!-- Hide Child Category Modal -->
  <div class="modal fade" id="hideChildModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form method="GET" action="">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Ẩn danh mục con</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Bạn có chắc chắn muốn ẩn danh mục con "<span id="hideChildName"></span>"?</p>
            <p>Sau khi ẩn, danh mục sẽ không hiển thị công khai trên hệ thống.</p>
            <input type="hidden" name="qldanhmuc" value="">
            <input type="hidden" name="hide_child" value="">
            <input type="hidden" name="id" id="hideChildId">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" class="btn btn-warning">Xác nhận ẩn</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  
  <!-- Restore Child Category Modal -->
  <div class="modal fade" id="restoreChildModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form method="GET" action="">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Hiển thị lại danh mục con</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Bạn có chắc chắn muốn hiển thị lại danh mục con "<span id="restoreChildName"></span>"?</p>
            <p>Sau khi hiển thị lại, danh mục sẽ được hiển thị công khai trên hệ thống.</p>
            <input type="hidden" name="qldanhmuc" value="">
            <input type="hidden" name="restore_child" value="">
            <input type="hidden" name="id" id="restoreChildId">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" class="btn btn-success">Xác nhận hiển thị lại</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  
<script>
    // Tab navigation
    document.querySelectorAll('.tab-item').forEach(function(tab) {
      tab.addEventListener('click', function() {
        // Remove active class from all tabs
        document.querySelectorAll('.tab-item').forEach(function(t) {
          t.classList.remove('active');
        });
        
        // Add active class to clicked tab
        this.classList.add('active');
        
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(function(content) {
          content.classList.remove('active');
        });
        
        // Show the corresponding tab content
        const tabId = this.getAttribute('data-tab');
        document.getElementById(tabId).classList.add('active');
      });
    });
    
    // Edit parent category modal
    document.getElementById('editParentCategoryModal').addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      const id = button.getAttribute('data-id');
      const name = button.getAttribute('data-name');
      
      document.getElementById('edit_parent_id').value = id;
      document.getElementById('edit_parent_name').value = name;
    });
    
    // Edit child category modal
    document.getElementById('editChildCategoryModal').addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      const id = button.getAttribute('data-id');
      const name = button.getAttribute('data-name');
      const parentId = button.getAttribute('data-parent');
      
      document.getElementById('edit_child_id').value = id;
      document.getElementById('edit_child_name').value = name;
      document.getElementById('edit_child_parent').value = parentId;
    });
    
    // Hide parent category modal
    document.getElementById('hideParentModal').addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      const id = button.getAttribute('data-id');
      const name = button.getAttribute('data-name');
      
      document.getElementById('hideParentId').value = id;
      document.getElementById('hideParentName').textContent = name;
    });
    
    // Restore parent category modal
    document.getElementById('restoreParentModal').addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      const id = button.getAttribute('data-id');
      const name = button.getAttribute('data-name');
      
      document.getElementById('restoreParentId').value = id;
      document.getElementById('restoreParentName').textContent = name;
    });
    
    // Hide child category modal
    document.getElementById('hideChildModal').addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      const id = button.getAttribute('data-id');
      const name = button.getAttribute('data-name');
      
      document.getElementById('hideChildId').value = id;
      document.getElementById('hideChildName').textContent = name;
    });
    
    // Restore child category modal
    document.getElementById('restoreChildModal').addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      const id = button.getAttribute('data-id');
      const name = button.getAttribute('data-name');
      
      document.getElementById('restoreChildId').value = id;
      document.getElementById('restoreChildName').textContent = name;
    });
    
    // Auto-submit form when parent filter changes
    document.getElementById('parent_filter').addEventListener('change', function() {
      this.form.submit();
    });
  </script>