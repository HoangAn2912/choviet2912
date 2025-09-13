<?php
include_once("controller/cQLdanhmuc.php");
$p = new cLoaiSanPham();

// Pagination settings
$itemsPerPage = 10; // Number of categories per page
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Get filter values
$parentFilter = isset($_GET['parent_filter']) ? $_GET['parent_filter'] : 'all';
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Get all parent categories for dropdowns
$parentCategories = $p->getAllParentCategories();

// Get total count for pagination
$totalCategories = $p->countChildCategories($parentFilter, $searchTerm);
$totalPages = ceil($totalCategories / $itemsPerPage);

// Ensure current page is within valid range
if ($currentPage > $totalPages && $totalPages > 0) {
    $currentPage = $totalPages;
    $offset = ($currentPage - 1) * $itemsPerPage;
}

// Get paginated categories
$childCategories = $p->getPaginatedChildCategories($offset, $itemsPerPage, $parentFilter, $searchTerm);

// Get category statistics
$stats = $p->getCategoryStats();

// Process add parent category
if (isset($_POST['btn_add_parent'])) {
    $name = $_POST['parent_name'];
    $result = $p->addParentCategory($name);
    
    if ($result['success']) {
        header("Location: ?loaisanpham&status=parent_added");
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
        header("Location: ?loaisanpham&status=child_added");
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
        header("Location: ?loaisanpham&status=parent_updated");
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
        header("Location: ?loaisanpham&status=child_updated");
        exit();
    } else {
        $errorMessage = $result['message'];
    }
}

// Process delete parent category
if (isset($_GET['delete_parent']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $p->deleteParentCategory($id);
    
    if ($result['success']) {
        header("Location: ?loaisanpham&status=parent_deleted");
        exit();
    } else {
        $errorMessage = $result['message'];
        header("Location: ?loaisanpham&status=error&message=" . urlencode($errorMessage));
        exit();
    }
}

// Process delete child category
if (isset($_GET['delete_child']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $p->deleteChildCategory($id);
    
    if ($result['success']) {
        header("Location: ?loaisanpham&status=child_deleted");
        exit();
    } else {
        $errorMessage = $result['message'];
        header("Location: ?loaisanpham&status=error&message=" . urlencode($errorMessage));
        exit();
    }
}

// Function to generate pagination URL
function getPaginationUrl($page, $parentFilter, $searchTerm) {
    $url = "?loaisanpham&page={$page}";
    if ($parentFilter !== 'all') $url .= "&parent_filter={$parentFilter}";
    if (!empty($searchTerm)) $url .= "&search=" . urlencode($searchTerm);
    return $url;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Quản lý danh mục sản phẩm</title>
  <!-- plugins:css -->
  <link rel="stylesheet" href="../admin/src/assets/vendors/mdi/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="../admin/src/assets/vendors/css/vendor.bundle.base.css">
  <!-- endinject -->
  <!-- inject:css -->
  <link rel="stylesheet" href="../admin/src/assets/css/style.css">
  <!-- endinject -->
  <link rel="shortcut icon" href="../admin/src/assets/images/favicon.ico" />
  <style>
    .btn a {
      text-decoration: none;
      color: #ffffff;
    }
    
    .action-message {
      padding: 10px 15px;
      margin-bottom: 20px;
      border-radius: 4px;
      background-color: #e3f2fd;
      color: #2196f3;
      display: flex;
      align-items: center;
    }
    
    .action-message i {
      margin-right: 10px;
      font-size: 20px;
    }
    
    .action-message.error {
      background-color: #ffebee;
      color: #f44336;
    }
    
    .filter-dropdown {
      display: inline-block;
      margin-right: 15px;
    }
    
    .filter-dropdown select {
      padding: 8px 12px;
      border-radius: 4px;
      border: 1px solid #ddd;
      background-color: #fff;
    }
    
    .top-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }
    
    .action-buttons {
      display: flex;
      gap: 10px;
    }
    
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
    
    .empty-table-message {
      text-align: center;
      padding: 30px;
      color: #666;
      font-style: italic;
    }
    
    .stats-cards {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      margin-bottom: 20px;
    }
    
    .stats-card {
      flex: 1;
      min-width: 200px;
      padding: 15px;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      display: flex;
      flex-direction: column;
    }
    
    .stats-card.parent {
      background-color: #e3f2fd;
      border-left: 4px solid #2196f3;
    }
    
    .stats-card.child {
      background-color: #e8f5e9;
      border-left: 4px solid #4caf50;
    }
    
    .stats-card .value {
      font-size: 24px;
      font-weight: bold;
      margin-bottom: 5px;
    }
    
    .stats-card .label {
      font-size: 14px;
      color: #666;
    }
    
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
    
    .filter-section {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      margin-bottom: 20px;
      align-items: flex-end;
    }
    
    .filter-group {
      flex: 1;
      min-width: 200px;
    }
    
    .filter-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: 500;
    }
    
    .filter-group select,
    .filter-group input {
      width: 100%;
      padding: 8px 12px;
      border-radius: 4px;
      border: 1px solid #ddd;
    }
    
    .filter-buttons {
      display: flex;
      gap: 10px;
    }
    
    .category-badge {
      display: inline-block;
      padding: 5px 10px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: 500;
      background-color: #e3f2fd;
      color: #2196f3;
      margin-right: 5px;
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
</head>

<body>
  <div class="col-lg-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title">Quản lý danh mục sản phẩm</h3>
        
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
          <?php elseif ($_GET['status'] == 'parent_deleted'): ?>
            <div class="action-message">
              <i class="mdi mdi-check-circle"></i>
              Danh mục cha đã được xóa thành công.
            </div>
          <?php elseif ($_GET['status'] == 'child_deleted'): ?>
            <div class="action-message">
              <i class="mdi mdi-check-circle"></i>
              Danh mục con đã được xóa thành công.
            </div>
          <?php elseif ($_GET['status'] == 'error'): ?>
            <div class="action-message error">
              <i class="mdi mdi-alert-circle"></i>
              <?php echo isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Đã xảy ra lỗi.'; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="stats-cards">
          <div class="stats-card parent">
            <div class="value"><?php echo $stats['total_parent_categories']; ?></div>
            <div class="label">Danh mục cha</div>
          </div>
          <div class="stats-card child">
            <div class="value"><?php echo $stats['total_child_categories']; ?></div>
            <div class="label">Danh mục con</div>
          </div>
        </div>
        
        <!-- Tab Navigation -->
        <div class="tab-navigation">
          <div class="tab-item" data-tab="parent-categories">Danh mục cha</div>
          <div class="tab-item active" data-tab="child-categories">Danh mục con</div>
        </div>
        
        <!-- Parent Categories Tab -->
        <div class="tab-content" id="parent-categories">
          <div class="top-actions">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addParentCategoryModal">
              <i class="mdi mdi-plus-circle"></i> Thêm danh mục cha mới
            </button>
          </div>
          
          <div class="parent-category-section">
            <h4>Danh sách danh mục cha</h4>
            
            <?php if (empty($parentCategories)): ?>
              <div class="empty-table-message">
                Chưa có danh mục cha nào. Vui lòng thêm danh mục cha mới.
              </div>
            <?php else: ?>
              <div class="parent-category-list">
                <?php foreach($parentCategories as $parent): ?>
                  <?php $childCount = $p->countChildCategories($parent['parent_category_id']); ?>
                  <div class="parent-category-item">
                    <div class="parent-category-name">
                      <?php echo $parent['parent_category_name']; ?>
                      <span class="child-count-badge"><?php echo $childCount; ?> danh mục con</span>
                    </div>
                    <div class="parent-category-actions">
                      <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#editParentCategoryModal" data-id="<?php echo $parent['parent_category_id']; ?>" data-name="<?php echo $parent['parent_category_name']; ?>">
                        <i class="mdi mdi-pencil"></i>
                      </button>
                      <?php if ($childCount == 0): ?>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmDeleteParent(<?php echo $parent['parent_category_id']; ?>, '<?php echo $parent['parent_category_name']; ?>')">
                          <i class="mdi mdi-delete"></i>
                        </button>
                      <?php else: ?>
                        <button type="button" class="btn btn-outline-danger btn-sm" disabled title="Không thể xóa danh mục có danh mục con">
                          <i class="mdi mdi-delete"></i>
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
          <div class="top-actions">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addChildCategoryModal">
              <i class="mdi mdi-plus-circle"></i> Thêm danh mục con mới
            </button>
            
            <div class="filter-section">
              <form method="GET" action="">
                <input type="hidden" name="loaisanpham" value="">
                <div class="filter-group">
                  <label for="parent_filter">Danh mục cha</label>
                  <select name="parent_filter" id="parent_filter">
                    <option value="all" <?php echo $parentFilter === 'all' ? 'selected' : ''; ?>>Tất cả danh mục cha</option>
                    <?php foreach($parentCategories as $parent): ?>
                      <option value="<?php echo $parent['parent_category_id']; ?>" <?php echo $parentFilter == $parent['parent_category_id'] ? 'selected' : ''; ?>>
                        <?php echo $parent['parent_category_name']; ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="filter-group">
                  <label for="search">Tìm kiếm</label>
                  <input type="text" name="search" id="search" placeholder="Tên danh mục" value="<?php echo $searchTerm; ?>">
                </div>
                <div class="filter-buttons">
                  <button type="submit" class="btn btn-primary">Lọc</button>
                  <a href="?loaisanpham" class="btn btn-outline-secondary">Đặt lại</a>
                </div>
              </form>
            </div>
          </div>
          
          <div class="table-responsive">
            <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Tên danh mục con</th>
                  <th>Danh mục cha</th>
                  <th>Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($childCategories)): ?>
                <tr>
                  <td colspan="4" class="empty-table-message">
                    Không tìm thấy danh mục con nào<?php echo $parentFilter !== 'all' || !empty($searchTerm) ? ' với điều kiện đã chọn' : ''; ?>.
                  </td>
                </tr>
                <?php else: ?>
                  <?php foreach($childCategories as $category): ?>
                    <tr>
                      <td><?php echo $category['id']; ?></td>
                      <td><?php echo $category['category_name']; ?></td>
                      <td>
                        <span class="category-badge"><?php echo $category['parent_category_name']; ?></span>
                      </td>
                      <td>
                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#editChildCategoryModal" 
                          data-id="<?php echo $category['id']; ?>" 
                          data-name="<?php echo $category['category_name']; ?>" 
                          data-parent="<?php echo $category['parent_category_id']; ?>">
                          <i class="mdi mdi-pencil"></i> Sửa
                        </button>
                        
                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteChild(<?php echo $category['id']; ?>, '<?php echo $category['category_name']; ?>')">
                          <i class="mdi mdi-delete"></i> Xóa
                        </button>
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
              Hiển thị <?php echo count($childCategories); ?> trên tổng số <?php echo $totalCategories; ?> danh mục con
            </div>
            <nav>
              <ul class="pagination">
                <!-- First page link -->
                <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                  <a class="page-link" href="<?php echo getPaginationUrl(1, $parentFilter, $searchTerm); ?>" aria-label="First">
                    <i class="mdi mdi-chevron-double-left"></i>
                  </a>
                </li>
                
                <!-- Previous page link -->
                <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                  <a class="page-link" href="<?php echo getPaginationUrl($currentPage - 1, $parentFilter, $searchTerm); ?>" aria-label="Previous">
                    <i class="mdi mdi-chevron-left"></i>
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
                    <a class="page-link" href="<?php echo getPaginationUrl($i, $parentFilter, $searchTerm); ?>"><?php echo $i; ?></a>
                  </li>
                <?php endfor; ?>
                
                <!-- Next page link -->
                <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                  <a class="page-link" href="<?php echo getPaginationUrl($currentPage + 1, $parentFilter, $searchTerm); ?>" aria-label="Next">
                    <i class="mdi mdi-chevron-right"></i>
                  </a>
                </li>
                
                <!-- Last page link -->
                <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                  <a class="page-link" href="<?php echo getPaginationUrl($totalPages, $parentFilter, $searchTerm); ?>" aria-label="Last">
                    <i class="mdi mdi-chevron-double-right"></i>
                  </a>
                </li>
              </ul>
            </nav>
          <?php endif; ?>
        </div>
        
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
  
  <!-- plugins:js -->
  <script src="../admin/src/assets/vendors/js/vendor.bundle.base.js"></script>
  <!-- endinject -->
  <!-- Plugin js for this page -->
  <!-- End plugin js for this page -->
  <!-- inject:js -->
  <script src="../admin/src/assets/js/off-canvas.js"></script>
  <script src="../admin/src/assets/js/hoverable-collapse.js"></script>
  <script src="../admin/src/assets/js/misc.js"></script>
  <!-- endinject -->
  
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
    
    // Confirm delete parent category
    function confirmDeleteParent(id, name) {
      if (confirm(`Bạn có chắc chắn muốn xóa danh mục cha "${name}"?`)) {
        window.location.href = `?loaisanpham&delete_parent&id=${id}`;
      }
    }
    
    // Confirm delete child category
    function confirmDeleteChild(id, name) {
      if (confirm(`Bạn có chắc chắn muốn xóa danh mục con "${name}"?`)) {
        window.location.href = `?loaisanpham&delete_child&id=${id}`;
      }
    }
    
    // Auto-submit form when parent filter changes
    document.getElementById('parent_filter').addEventListener('change', function() {
      this.form.submit();
    });
  </script>
</body>

</html>