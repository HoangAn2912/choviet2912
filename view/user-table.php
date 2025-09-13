<?php
include_once("controller/cQLthongtin.php");
$p = new cqlthongtin();

// Pagination settings
$itemsPerPage = 10; // Number of users per page
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Get status filter if set
$statusFilter = isset($_GET['status_filter']) ? $_GET['status_filter'] : 'all';

// Get total count for pagination
$totalUsers = $p->countUsers($statusFilter);
$totalPages = ceil($totalUsers / $itemsPerPage);

// Ensure current page is within valid range
if ($currentPage > $totalPages && $totalPages > 0) {
    $currentPage = $totalPages;
    $offset = ($currentPage - 1) * $itemsPerPage;
}

// Get paginated users
$data = $p->getpaginatedusers($offset, $itemsPerPage, $statusFilter);

// Process bulk actions
if (isset($_POST['bulk_action']) && isset($_POST['user_ids'])) {
    $action = $_POST['bulk_action'];
    $userIds = $_POST['user_ids'];
    
    $successCount = 0;
    
    foreach ($userIds as $id) {
        if ($action === 'disable') {
            $result = $p->disableuser($id);
            if ($result) $successCount++;
        } else if ($action === 'restore') {
            $result = $p->restoreuser($id);
            if ($result) $successCount++;
        }
    }
    
    if ($successCount > 0) {
        $actionText = $action === 'disable' ? 'vô hiệu hóa' : 'khôi phục';
        // Preserve pagination and filter parameters
        $redirectUrl = "?taikhoan&bulk_status={$action}&count={$successCount}";
        if ($statusFilter !== 'all') $redirectUrl .= "&status_filter={$statusFilter}";
        if ($currentPage > 1) $redirectUrl .= "&page={$currentPage}";
        
        header("Location: {$redirectUrl}");
        exit();
    }
}

// Process individual disable/restore user actions
if (isset($_GET['disable']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $p->disableuser($id);
    if ($result) {
        // Preserve pagination and filter parameters
        $redirectUrl = "?taikhoan&status=disabled";
        if ($statusFilter !== 'all') $redirectUrl .= "&status_filter={$statusFilter}";
        if ($currentPage > 1) $redirectUrl .= "&page={$currentPage}";
        
        header("Location: {$redirectUrl}");
        exit();
    }
}

if (isset($_GET['restore']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $p->restoreuser($id);
    if ($result) {
        // Preserve pagination and filter parameters
        $redirectUrl = "?taikhoan&status=restored";
        if ($statusFilter !== 'all') $redirectUrl .= "&status_filter={$statusFilter}";
        if ($currentPage > 1) $redirectUrl .= "&page={$currentPage}";
        
        header("Location: {$redirectUrl}");
        exit();
    }
}

// Function to generate pagination URL
function getPaginationUrl($page, $statusFilter) {
    $url = "?taikhoan&page={$page}";
    if ($statusFilter !== 'all') $url .= "&status_filter={$statusFilter}";
    return $url;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Quản lý người dùng</title>
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
    
    .disabled-user {
      opacity: 0.6;
      background-color: #f5f5f5;
    }
    
    .status-badge {
      padding: 5px 10px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: 500;
    }
    
    .status-active {
      background-color: #e6f7ee;
      color: #00c853;
    }
    
    .status-disabled {
      background-color: #feeae6;
      color: #ff5252;
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
    
    .bulk-actions {
      display: flex;
      align-items: center;
      margin-bottom: 20px;
    }
    
    .bulk-actions select {
      margin-right: 10px;
      padding: 8px 12px;
      border-radius: 4px;
      border: 1px solid #ddd;
    }
    
    .status-header {
      cursor: pointer;
      display: flex;
      align-items: center;
    }
    
    .status-header i {
      margin-left: 5px;
    }
    
    .select-all-checkbox {
      width: 18px;
      height: 18px;
    }
    
    .user-checkbox {
      width: 16px;
      height: 16px;
    }
    
    .top-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
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
  </style>
</head>

<body>
  <div class="col-lg-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title">Quản lý thông tin người dùng</h3>
        
        <?php if (isset($_GET['status']) && $_GET['status'] == 'disabled'): ?>
        <div class="action-message">
          <i class="mdi mdi-check-circle"></i>
          Người dùng đã được vô hiệu hóa thành công.
        </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['status']) && $_GET['status'] == 'restored'): ?>
        <div class="action-message">
          <i class="mdi mdi-check-circle"></i>
          Người dùng đã được khôi phục thành công.
        </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['bulk_status'])): ?>
        <div class="action-message">
          <i class="mdi mdi-check-circle"></i>
          <?php 
            $count = isset($_GET['count']) ? intval($_GET['count']) : 0;
            $actionText = $_GET['bulk_status'] === 'disable' ? 'vô hiệu hóa' : 'khôi phục';
            echo "Đã {$actionText} thành công {$count} người dùng.";
          ?>
        </div>
        <?php endif; ?>
        
        <div class="top-actions">
          <button type="button" class="btn btn-primary">
            <a href="?them">  
              <i class="mdi mdi-account-plus"></i> Thêm người dùng mới
            </a>
          </button>
          
          <div class="filter-dropdown">
            <form id="statusFilterForm" method="GET" action="">
              <input type="hidden" name="taikhoan" value="">
              <select name="status_filter" id="statusFilter" onchange="this.form.submit()">
                <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>Tất cả trạng thái</option>
                <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                <option value="disabled" <?php echo $statusFilter === 'disabled' ? 'selected' : ''; ?>>Vô hiệu hóa</option>
              </select>
            </form>
          </div>
        </div>
        
        <form id="bulkActionForm" method="POST" action="">
          <!-- Preserve pagination and filter when submitting form -->
          <input type="hidden" name="page" value="<?php echo $currentPage; ?>">
          <input type="hidden" name="status_filter" value="<?php echo $statusFilter; ?>">
          
          <div class="bulk-actions">
            <select name="bulk_action" id="bulkAction">
              <option value="">-- Chọn hành động --</option>
              <option value="disable">Vô hiệu hóa đã chọn</option>
              <option value="restore">Khôi phục đã chọn</option>
            </select>
            <button type="submit" class="btn btn-primary" id="applyBulkAction" disabled>
              Áp dụng
            </button>
          </div>
          
          <div class="table-responsive">
            <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th>
                    <input type="checkbox" class="select-all-checkbox" id="selectAll">
                  </th>
                  <th>Avata</th>
                  <th>ID</th>
                  <th>Họ và tên</th>
                  <th>Email</th>
                  <th>Số điện thoại</th>
                  <th>Địa chỉ</th>
                  <th class="status-header" onclick="toggleStatusFilter()">
                    Trạng thái <i class="mdi mdi-arrow-down-drop-circle"></i>
                  </th>
                  <th>Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($data)): ?>
                <tr>
                  <td colspan="9" class="empty-table-message">
                    Không tìm thấy người dùng nào<?php echo $statusFilter !== 'all' ? ' với trạng thái đã chọn' : ''; ?>.
                  </td>
                </tr>
                <?php else: ?>
                  <?php foreach($data as $u): ?>
                    <?php if ($u['role_id'] == 2): ?>
                      <?php
                        $isActive = $u['is_active'] == 1;
                        $rowClass = !$isActive ? 'disabled-user' : '';
                        $statusClass = $isActive ? 'status-active' : 'status-disabled';
                        $statusText = $isActive ? 'Hoạt động' : 'Vô hiệu hóa';
                      ?>
                      <tr class="<?php echo $rowClass; ?>">
                        <td>
                          <input type="checkbox" class="user-checkbox" name="user_ids[]" value="<?php echo $u['id']; ?>">
                        </td>
                        <td class="py-1">
                          <img src="../img/<?php echo $u['avatar']; ?>" alt="image"/>
                        </td>
                        <td><?php echo $u['id']; ?></td>
                        <td><?php echo $u['username']; ?></td>
                        <td><?php echo $u['email']; ?></td>
                        <td><?php echo $u['phone']; ?></td>
                        <td><?php echo $u['address']; ?></td>
                        <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                        <td>
                          <button type="button" class="btn btn-info mb-2">
                            <a href="?taikhoan&sua&ids=<?php echo $u['id']; ?>">
                              <i class="mdi mdi-pencil"></i> Sửa
                            </a>
                          </button>
                          
                          <?php if ($isActive): ?>
                            <button type="button" class="btn btn-danger mb-2">
                              <a href="?taikhoan&disable&id=<?php echo $u['id']; ?>&page=<?php echo $currentPage; ?>&status_filter=<?php echo $statusFilter; ?>"
                                class="text-white text-decoration-none"
                                onclick="return confirm('Bạn có chắc chắn muốn vô hiệu hóa người dùng này?');">
                                <i class="mdi mdi-account-off"></i> Vô hiệu hóa
                              </a>
                            </button>
                          <?php else: ?>
                            <button type="button" class="btn btn-success mb-2">
                              <a href="?taikhoan&restore&id=<?php echo $u['id']; ?>&page=<?php echo $currentPage; ?>&status_filter=<?php echo $statusFilter; ?>"
                                class="text-white text-decoration-none"
                                onclick="return confirm('Bạn có chắc chắn muốn khôi phục người dùng này?');">
                                <i class="mdi mdi-account-check"></i> Khôi phục
                              </a>
                            </button>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endif; ?>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </form>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
          <div class="pagination-info">
            Hiển thị <?php echo count($data); ?> trên tổng số <?php echo $totalUsers; ?> người dùng
          </div>
          <nav>
            <ul class="pagination">
              <!-- First page link -->
              <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo getPaginationUrl(1, $statusFilter); ?>" aria-label="First">
                  <i class="mdi mdi-chevron-double-left"></i>
                </a>
              </li>
              
              <!-- Previous page link -->
              <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo getPaginationUrl($currentPage - 1, $statusFilter); ?>" aria-label="Previous">
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
                  <a class="page-link" href="<?php echo getPaginationUrl($i, $statusFilter); ?>"><?php echo $i; ?></a>
                </li>
              <?php endfor; ?>
              
              <!-- Next page link -->
              <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo getPaginationUrl($currentPage + 1, $statusFilter); ?>" aria-label="Next">
                  <i class="mdi mdi-chevron-right"></i>
                </a>
              </li>
              
              <!-- Last page link -->
              <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo getPaginationUrl($totalPages, $statusFilter); ?>" aria-label="Last">
                  <i class="mdi mdi-chevron-double-right"></i>
                </a>
              </li>
            </ul>
          </nav>
        <?php endif; ?>
        
      </div>
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
    // Toggle status filter dropdown
    function toggleStatusFilter() {
      document.getElementById('statusFilter').click();
    }
    
    // Handle select all checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
      const isChecked = this.checked;
      const checkboxes = document.querySelectorAll('.user-checkbox');
      
      checkboxes.forEach(checkbox => {
        checkbox.checked = isChecked;
      });
      
      updateBulkActionButton();
    });
    
    // Handle individual checkboxes
    document.querySelectorAll('.user-checkbox').forEach(checkbox => {
      checkbox.addEventListener('change', function() {
        updateBulkActionButton();
        
        // Update "select all" checkbox
        const allCheckboxes = document.querySelectorAll('.user-checkbox');
        const checkedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
        
        document.getElementById('selectAll').checked = 
          allCheckboxes.length === checkedCheckboxes.length && allCheckboxes.length > 0;
      });
    });
    
    // Enable/disable bulk action button based on selections
    function updateBulkActionButton() {
      const checkedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
      const bulkActionButton = document.getElementById('applyBulkAction');
      
      bulkActionButton.disabled = checkedCheckboxes.length === 0;
    }
    
    // Validate bulk action form before submission
    document.getElementById('bulkActionForm').addEventListener('submit', function(e) {
      const action = document.getElementById('bulkAction').value;
      const checkedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
      
      if (action === '' || checkedCheckboxes.length === 0) {
        e.preventDefault();
        alert('Vui lòng chọn hành động và ít nhất một người dùng.');
      } else {
        const actionText = action === 'disable' ? 'vô hiệu hóa' : 'khôi phục';
        if (!confirm(`Bạn có chắc chắn muốn ${actionText} ${checkedCheckboxes.length} người dùng đã chọn?`)) {
          e.preventDefault();
        }
      }
    });
  </script>
</body>

</html>