<?php
include_once("controller/cQLpriceodich.php");
$p = new cGiaodich();

// Pagination settings
$itemsPerPage = 5; // Number of transactions per page
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Get filter values
$statusFilter = isset($_GET['status_filter']) ? $_GET['status_filter'] : 'all';
$typeFilter = isset($_GET['type_filter']) ? $_GET['type_filter'] : 'all';
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Get total count for pagination
$totalTransactions = $p->countTransactions($statusFilter, $typeFilter, $searchTerm);
$totalPages = ceil($totalTransactions / $itemsPerPage);

// Ensure current page is within valid range
if ($currentPage > $totalPages && $totalPages > 0) {
    $currentPage = $totalPages;
    $offset = ($currentPage - 1) * $itemsPerPage;
}

// Get paginated transactions
$data = $p->getPaginatedTransactions($offset, $itemsPerPage, $statusFilter, $typeFilter, $searchTerm);

// Get transaction types for filter dropdown
$transactionTypes = array('Nạp tiền', 'Rút tiền', 'Thanh toán');

// Get transaction statistics
$stats = $p->getTransactionStats();

// Process bulk actions
if (isset($_POST['bulk_action']) && isset($_POST['transaction_ids'])) {
    $action = $_POST['bulk_action'];
    $transactionIds = $_POST['transaction_ids'];
    
    $successCount = 0;
    
    if ($action === 'complete' || $action === 'process' || $action === 'cancel') {
        $status = '';
        switch ($action) {
            case 'complete':
                $status = 'Hoàn thành';
                break;
            case 'process':
                $status = 'Đang xử lý';
                break;
            case 'cancel':
                $status = 'Hủy';
                break;
        }
        
        $result = $p->bulkUpdateStatus($transactionIds, $status);
        if ($result) $successCount = count($transactionIds);
    }
    
    if ($successCount > 0) {
        $actionText = '';
        switch ($action) {
            case 'complete':
                $actionText = 'hoàn thành';
                break;
            case 'process':
                $actionText = 'đang xử lý';
                break;
            case 'cancel':
                $actionText = 'hủy';
                break;
        }
        
        // Preserve pagination and filter parameters
        $redirectUrl = "?priceodich&bulk_status={$action}&count={$successCount}";
        if ($statusFilter !== 'all') $redirectUrl .= "&status_filter={$statusFilter}";
        if ($typeFilter !== 'all') $redirectUrl .= "&type_filter={$typeFilter}";
        if (!empty($searchTerm)) $redirectUrl .= "&search=" . urlencode($searchTerm);
        if ($currentPage > 1) $redirectUrl .= "&page={$currentPage}";
        
        header("Location: {$redirectUrl}");
        exit();
    }
}

// Process individual status update actions
if (isset($_GET['update_status']) && isset($_GET['id']) && isset($_GET['status'])) {
    $id = $_GET['id'];
    $status = $_GET['status'];
    
    $result = $p->updateTransactionStatus($id, $status);
    if ($result) {
        // Preserve pagination and filter parameters
        $redirectUrl = "?priceodich&status=updated&action={$status}";
        if ($statusFilter !== 'all') $redirectUrl .= "&status_filter={$statusFilter}";
        if ($typeFilter !== 'all') $redirectUrl .= "&type_filter={$typeFilter}";
        if (!empty($searchTerm)) $redirectUrl .= "&search=" . urlencode($searchTerm);
        if ($currentPage > 1) $redirectUrl .= "&page={$currentPage}";
        
        header("Location: {$redirectUrl}");
        exit();
    }
}

// Process add new transaction
if (isset($_POST['btn_add_transaction'])) {
    $userId = $_POST['user_id'];
    $type = $_POST['transaction_type'];
    $amount = $_POST['amount'];
    $status = $_POST['status'];
    
    $result = $p->addTransaction($userId, $type, $amount, $status);
    if ($result) {
        header("Location: ?priceodich&status=added");
        exit();
    }
}

// Function to generate pagination URL
function getPaginationUrl($page, $statusFilter, $typeFilter, $searchTerm) {
    $url = "?priceodich&page={$page}";
    if ($statusFilter !== 'all') $url .= "&status_filter={$statusFilter}";
    if ($typeFilter !== 'all') $url .= "&type_filter={$typeFilter}";
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
  <title>Quản lý priceo dịch</title>
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
    
    .status-badge {
      padding: 5px 10px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: 500;
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
    
    .transaction-checkbox {
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
    
    .stats-card.total {
      background-color: #e3f2fd;
      border-left: 4px solid #2196f3;
    }
    
    .stats-card.completed {
      background-color: #e8f5e9;
      border-left: 4px solid #4caf50;
    }
    
    .stats-card.processing {
      background-color: #fff8e1;
      border-left: 4px solid #ffc107;
    }
    
    .stats-card.cancelled {
      background-color: #ffebee;
      border-left: 4px solid #f44336;
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
    
    .detail-row {
      display: flex;
      margin-bottom: 10px;
      border-bottom: 1px solid #eee;
      padding-bottom: 10px;
    }
    
    .detail-label {
      font-weight: 500;
      width: 150px;
      color: #666;
    }
    
    .detail-value {
      flex: 1;
    }
    
    .search-box {
      display: flex;
      margin-bottom: 20px;
    }
    
    .search-box input {
      flex: 1;
      padding: 8px 12px;
      border-radius: 4px 0 0 4px;
      border: 1px solid #ddd;
      border-right: none;
    }
    
    .search-box button {
      padding: 8px 15px;
      border-radius: 0 4px 4px 0;
      border: 1px solid #2196f3;
      background-color: #2196f3;
      color: white;
      cursor: pointer;
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
  </style>
</head>

<body>
  <div class="col-lg-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title">Quản lý priceo dịch</h3>
        
        <?php if (isset($_GET['status']) && $_GET['status'] == 'updated'): ?>
        <div class="action-message">
          <i class="mdi mdi-check-circle"></i>
          <?php 
            $actionText = '';
            switch ($_GET['action']) {
              case 'Hoàn thành':
                $actionText = 'hoàn thành';
                break;
              case 'Đang xử lý':
                $actionText = 'đang xử lý';
                break;
              case 'Hủy':
                $actionText = 'hủy';
                break;
            }
            echo "Giao dịch đã được cập nhật thành {$actionText} thành công.";
          ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['status']) && $_GET['status'] == 'added'): ?>
        <div class="action-message">
          <i class="mdi mdi-check-circle"></i>
          Giao dịch mới đã được thêm thành công.
        </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['bulk_status'])): ?>
        <div class="action-message">
          <i class="mdi mdi-check-circle"></i>
          <?php 
            $count = isset($_GET['count']) ? intval($_GET['count']) : 0;
            $actionText = '';
            switch ($_GET['bulk_status']) {
              case 'complete':
                $actionText = 'hoàn thành';
                break;
              case 'process':
                $actionText = 'đang xử lý';
                break;
              case 'cancel':
                $actionText = 'hủy';
                break;
            }
            echo "Đã cập nhật thành {$actionText} thành công {$count} priceo dịch.";
          ?>
        </div>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="stats-cards">
          <div class="stats-card total">
            <div class="value"><?php echo $stats['total_transactions']; ?></div>
            <div class="label">Tổng priceo dịch</div>
          </div>
          <div class="stats-card completed">
            <div class="value"><?php echo $stats['completed']; ?></div>
            <div class="label">Hoàn thành</div>
          </div>
          <div class="stats-card processing">
            <div class="value"><?php echo $stats['processing']; ?></div>
            <div class="label">Đang xử lý</div>
          </div>
          <div class="stats-card cancelled">
            <div class="value"><?php echo $stats['cancelled']; ?></div>
            <div class="label">Đã hủy</div>
          </div>
        </div>
        
        <div class="top-actions">
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
            <i class="mdi mdi-plus-circle"></i> Thêm priceo dịch mới
          </button>
          
          <div class="filter-section">
            <form method="GET" action="">
              <input type="hidden" name="priceodich" value="">
              <div class="filter-group">
                <label for="status_filter">Trạng thái</label>
                <select name="status_filter" id="status_filter">
                  <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>Tất cả trạng thái</option>
                  <option value="Hoàn thành" <?php echo $statusFilter === 'Hoàn thành' ? 'selected' : ''; ?>>Hoàn thành</option>
                  <option value="Đang xử lý" <?php echo $statusFilter === 'Đang xử lý' ? 'selected' : ''; ?>>Đang xử lý</option>
                  <option value="Hủy" <?php echo $statusFilter === 'Hủy' ? 'selected' : ''; ?>>Hủy</option>
                </select>
              </div>
              <div class="filter-group">
                <label for="type_filter">Loại priceo dịch</label>
                <select name="type_filter" id="type_filter">
                  <option value="all" <?php echo $typeFilter === 'all' ? 'selected' : ''; ?>>Tất cả loại</option>
                  <?php foreach($transactionTypes as $type): ?>
                    <option value="<?php echo $type; ?>" <?php echo $typeFilter === $type ? 'selected' : ''; ?>><?php echo $type; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="filter-group">
                <label for="search">Tìm kiếm</label>
                <input type="text" name="search" id="search" placeholder="ID hoặc tên người dùng" value="<?php echo $searchTerm; ?>">
              </div>
              <div class="filter-buttons">
                <button type="submit" class="btn btn-primary">Lọc</button>
                <a href="?priceodich" class="btn btn-outline-secondary">Đặt lại</a>
              </div>
            </form>
          </div>
        </div>
        
        <form id="bulkActionForm" method="POST" action="">
          <!-- Preserve pagination and filter when submitting form -->
          <input type="hidden" name="page" value="<?php echo $currentPage; ?>">
          <input type="hidden" name="status_filter" value="<?php echo $statusFilter; ?>">
          <input type="hidden" name="type_filter" value="<?php echo $typeFilter; ?>">
          <input type="hidden" name="search" value="<?php echo $searchTerm; ?>">
          
          <div class="bulk-actions">
            <select name="bulk_action" id="bulkAction">
              <option value="">-- Chọn hành động --</option>
              <option value="complete">Đánh dấu hoàn thành</option>
              <option value="process">Đánh dấu đang xử lý</option>
              <option value="cancel">Đánh dấu hủy</option>
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
                  <th>ID</th>
                  <th>Người dùng</th>
                  <th>Loại priceo dịch</th>
                  <th>Số tiền</th>
                  <th class="status-header" onclick="toggleStatusFilter()">
                    Trạng thái <i class="mdi mdi-arrow-down-drop-circle"></i>
                  </th>
                  <th>Ngày tạo</th>
                  <th>Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($data)): ?>
                <tr>
                  <td colspan="8" class="empty-table-message">
                    Không tìm thấy priceo dịch nào<?php echo $statusFilter !== 'all' || $typeFilter !== 'all' || !empty($searchTerm) ? ' với điều kiện đã chọn' : ''; ?>.
                  </td>
                </tr>
                <?php else: ?>
                  <?php foreach($data as $transaction): ?>
                    <?php
                      $statusClass = $p->getStatusBadgeClass($transaction['status']);
                      $typeClass = $p->getTypeBadgeClass($transaction['loai_priceo_dich']);
                    ?>
                    <tr>
                      <td>
                        <input type="checkbox" class="transaction-checkbox" name="transaction_ids[]" value="<?php echo $transaction['id']; ?>">
                      </td>
                      <td><?php echo $transaction['id']; ?></td>
                      <td><?php echo $transaction['username']; ?></td>
                      <td><span class="status-badge <?php echo $typeClass; ?>"><?php echo $transaction['loai_priceo_dich']; ?></span></td>
                      <td><?php echo $p->formatCurrency($transaction['amount']); ?></td>
                      <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo $transaction['status']; ?></span></td>
                      <td><?php echo $transaction['created_date']; ?></td>
                      <td>
                        <button type="button" class="btn btn-info btn-sm mb-2" data-bs-toggle="modal" data-bs-target="#viewTransactionModal" data-id="<?php echo $transaction['id']; ?>">
                          <i class="mdi mdi-eye"></i> Chi tiết
                        </button>
                        
                        <div class="dropdown d-inline-block">
                          <button class="btn btn-primary btn-sm mb-2 dropdown-toggle" type="button" id="dropdownMenuButton<?php echo $transaction['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="mdi mdi-pencil"></i> Cập nhật
                          </button>
                          <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $transaction['id']; ?>">
                            <?php if ($transaction['status'] != 'Hoàn thành'): ?>
                            <li>
                              <a class="dropdown-item" href="?priceodich&update_status&id=<?php echo $transaction['id']; ?>&status=Hoàn thành&page=<?php echo $currentPage; ?>&status_filter=<?php echo $statusFilter; ?>&type_filter=<?php echo $typeFilter; ?>&search=<?php echo urlencode($searchTerm); ?>" onclick="return confirm('Bạn có chắc chắn muốn đánh dấu priceo dịch này là hoàn thành?');">
                                <i class="mdi mdi-check-circle text-success"></i> Đánh dấu hoàn thành
                              </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php if ($transaction['status'] != 'Đang xử lý'): ?>
                            <li>
                              <a class="dropdown-item" href="?priceodich&update_status&id=<?php echo $transaction['id']; ?>&status=Đang xử lý&page=<?php echo $currentPage; ?>&status_filter=<?php echo $statusFilter; ?>&type_filter=<?php echo $typeFilter; ?>&search=<?php echo urlencode($searchTerm); ?>" onclick="return confirm('Bạn có chắc chắn muốn đánh dấu priceo dịch này là đang xử lý?');">
                                <i class="mdi mdi-clock-outline text-warning"></i> Đánh dấu đang xử lý
                              </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php if ($transaction['status'] != 'Hủy'): ?>
                            <li>
                              <a class="dropdown-item" href="?priceodich&update_status&id=<?php echo $transaction['id']; ?>&status=Hủy&page=<?php echo $currentPage; ?>&status_filter=<?php echo $statusFilter; ?>&type_filter=<?php echo $typeFilter; ?>&search=<?php echo urlencode($searchTerm); ?>" onclick="return confirm('Bạn có chắc chắn muốn hủy priceo dịch này?');">
                                <i class="mdi mdi-close-circle text-danger"></i> Hủy priceo dịch
                              </a>
                            </li>
                            <?php endif; ?>
                          </ul>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </form>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
          <div class="pagination-info">
            Hiển thị <?php echo count($data); ?> trên tổng số <?php echo $totalTransactions; ?> priceo dịch
          </div>
          <nav>
            <ul class="pagination">
              <!-- First page link -->
              <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo getPaginationUrl(1, $statusFilter, $typeFilter, $searchTerm); ?>" aria-label="First">
                  <i class="mdi mdi-chevron-double-left"></i>
                </a>
              </li>
              
              <!-- Previous page link -->
              <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo getPaginationUrl($currentPage - 1, $statusFilter, $typeFilter, $searchTerm); ?>" aria-label="Previous">
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
                  <a class="page-link" href="<?php echo getPaginationUrl($i, $statusFilter, $typeFilter, $searchTerm); ?>"><?php echo $i; ?></a>
                </li>
              <?php endfor; ?>
              
              <!-- Next page link -->
              <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo getPaginationUrl($currentPage + 1, $statusFilter, $typeFilter, $searchTerm); ?>" aria-label="Next">
                  <i class="mdi mdi-chevron-right"></i>
                </a>
              </li>
              
              <!-- Last page link -->
              <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo getPaginationUrl($totalPages, $statusFilter, $typeFilter, $searchTerm); ?>" aria-label="Last">
                  <i class="mdi mdi-chevron-double-right"></i>
                </a>
              </li>
            </ul>
          </nav>
        <?php endif; ?>
        
      </div>
    </div>
  </div>
  
  <!-- Add Transaction Modal -->
  <div class="modal fade" id="addTransactionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form method="POST">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Thêm priceo dịch mới</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="modal-form-group">
              <label for="user_id">Người dùng</label>
              <select name="user_id" id="user_id" required>
                <option value="">-- Chọn người dùng --</option>
                <?php 
                $users = $p->getUsers();
                foreach($users as $user): 
                ?>
                  <option value="<?php echo $user['id']; ?>"><?php echo $user['username']; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="modal-form-group">
              <label for="transaction_type">Loại priceo dịch</label>
              <select name="transaction_type" id="transaction_type" required>
                <option value="">-- Chọn loại priceo dịch --</option>
                <?php foreach($transactionTypes as $type): ?>
                  <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="modal-form-group">
              <label for="amount">Số tiền</label>
              <input type="number" name="amount" id="amount" min="1000" step="1000" required placeholder="Nhập số tiền">
            </div>
            <div class="modal-form-group">
              <label for="status">Trạng thái</label>
              <select name="status" id="status" required>
                <option value="">-- Chọn trạng thái --</option>
                <option value="Hoàn thành">Hoàn thành</option>
                <option value="Đang xử lý">Đang xử lý</option>
                <option value="Hủy">Hủy</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" class="btn btn-primary" name="btn_add_transaction">Thêm priceo dịch</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  
  <!-- View Transaction Modal -->
  <div class="modal fade" id="viewTransactionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Chi tiết priceo dịch</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="transactionDetails">
          <!-- Transaction details will be loaded here via AJAX -->
          <div class="text-center">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p>Đang tải thông tin priceo dịch...</p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
        </div>
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
      document.getElementById('status_filter').click();
    }
    
    // Handle select all checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
      const isChecked = this.checked;
      const checkboxes = document.querySelectorAll('.transaction-checkbox');
      
      checkboxes.forEach(checkbox => {
        checkbox.checked = isChecked;
      });
      
      updateBulkActionButton();
    });
    
    // Handle individual checkboxes
    document.querySelectorAll('.transaction-checkbox').forEach(checkbox => {
      checkbox.addEventListener('change', function() {
        updateBulkActionButton();
        
        // Update "select all" checkbox
        const allCheckboxes = document.querySelectorAll('.transaction-checkbox');
        const checkedCheckboxes = document.querySelectorAll('.transaction-checkbox:checked');
        
        document.getElementById('selectAll').checked = 
          allCheckboxes.length === checkedCheckboxes.length && allCheckboxes.length > 0;
      });
    });
    
    // Enable/disable bulk action button based on selections
    function updateBulkActionButton() {
      const checkedCheckboxes = document.querySelectorAll('.transaction-checkbox:checked');
      const bulkActionButton = document.getElementById('applyBulkAction');
      
      bulkActionButton.disabled = checkedCheckboxes.length === 0;
    }
    
    // Validate bulk action form before submission
    document.getElementById('bulkActionForm').addEventListener('submit', function(e) {
      const action = document.getElementById('bulkAction').value;
      const checkedCheckboxes = document.querySelectorAll('.transaction-checkbox:checked');
      
      if (action === '' || checkedCheckboxes.length === 0) {
        e.preventDefault();
        alert('Vui lòng chọn hành động và ít nhất một priceo dịch.');
      } else {
        let actionText = '';
        switch (action) {
          case 'complete':
            actionText = 'hoàn thành';
            break;
          case 'process':
            actionText = 'đang xử lý';
            break;
          case 'cancel':
            actionText = 'hủy';
            break;
        }
        
        if (!confirm(`Bạn có chắc chắn muốn đánh dấu ${checkedCheckboxes.length} priceo dịch đã chọn là ${actionText}?`)) {
          e.preventDefault();
        }
      }
    });
    
    // Format currency in amount field
    document.getElementById('amount').addEventListener('input', function(e) {
      let value = this.value.replace(/\D/g, '');
      if (value) {
        value = parseInt(value).toLocaleString('vi-VN');
        this.value = value.replace(/\./g, '');
      }
    });
    
    // Load transaction details in modal
    document.getElementById('viewTransactionModal').addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      const transactionId = button.getAttribute('data-id');
      const detailsContainer = document.getElementById('transactionDetails');
      
      // In a real application, you would use AJAX to fetch transaction details
      // For this example, we'll simulate it with a timeout
      detailsContainer.innerHTML = `
        <div class="text-center">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p>Đang tải thông tin priceo dịch...</p>
        </div>
      `;
      
      setTimeout(() => {
        // Find the transaction in the data array
        <?php 
        $transactionDetails = array();
        foreach($data as $t) {
          $transactionDetails[$t['id']] = $t;
        }
        ?>
        
        const transactions = <?php echo json_encode($transactionDetails); ?>;
        const transaction = transactions[transactionId];
        
        if (transaction) {
          let statusClass = '';
          switch (transaction.status) {
            case 'Hoàn thành':
              statusClass = 'bg-success text-white';
              break;
            case 'Đang xử lý':
              statusClass = 'bg-warning text-dark';
              break;
            case 'Hủy':
              statusClass = 'bg-danger text-white';
              break;
          }
          
          let typeClass = '';
          switch (transaction.loai_priceo_dich) {
            case 'Nạp tiền':
              typeClass = 'bg-primary text-white';
              break;
            case 'Rút tiền':
              typeClass = 'bg-info text-white';
              break;
            case 'Thanh toán':
              typeClass = 'bg-dark text-white';
              break;
          }
          
          detailsContainer.innerHTML = `
            <div class="detail-row">
              <div class="detail-label">ID priceo dịch:</div>
              <div class="detail-value">${transaction.id}</div>
            </div>
            <div class="detail-row">
              <div class="detail-label">Người dùng:</div>
              <div class="detail-value">${transaction.username}</div>
            </div>
            <div class="detail-row">
              <div class="detail-label">Loại priceo dịch:</div>
              <div class="detail-value">
                <span class="status-badge ${typeClass}">${transaction.loai_priceo_dich}</span>
              </div>
            </div>
            <div class="detail-row">
              <div class="detail-label">Số tiền:</div>
              <div class="detail-value">${parseInt(transaction.amount).toLocaleString('vi-VN')} VNĐ</div>
            </div>
            <div class="detail-row">
              <div class="detail-label">Trạng thái:</div>
              <div class="detail-value">
                <span class="status-badge ${statusClass}">${transaction.status}</span>
              </div>
            </div>
            <div class="detail-row">
              <div class="detail-label">Ngày tạo:</div>
              <div class="detail-value">${transaction.created_date}</div>
            </div>
          `;
        } else {
          detailsContainer.innerHTML = `
            <div class="alert alert-warning">
              Không tìm thấy thông tin priceo dịch với ID: ${transactionId}
            </div>
          `;
        }
      }, 500);
    });
  </script>
</body>

</html>