<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add debug logging for AJAX requests
if (isset($_GET['ajax'])) {
    error_log("AJAX Request received: " . file_get_contents('php://input'));
}

include_once 'controller/cDuyetNapTien.php';
$controller = new cDuyetNapTien();

// Handle AJAX requests
if (isset($_GET['ajax'])) {
    $controller->handleAjaxRequest();
    exit;
}

try {
    

    // Get filter parameters
    $status = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null;
    $userId = isset($_GET['user_id']) && $_GET['user_id'] !== '' ? (int)$_GET['user_id'] : null;
    $search = isset($_GET['search']) && $_GET['search'] !== '' ? $_GET['search'] : null;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = 10;

    // Get transactions based on filters
    $transactionData = $controller->getAllTransactions($status, $userId, $search, $page, $perPage);
    $transactions = $transactionData['data'];
    $pagination = $transactionData['pagination'];

    // Get transaction statistics
    $stats = $controller->getTransactionStatistics();

    // Get all users for dropdown
    $users = $controller->getAllUsers();

    // Get transaction details if ID is provided
    $transactionDetails = null;
    if (isset($_GET['view']) && is_numeric($_GET['view'])) {
        $transactionDetails = $controller->getTransactionById((int)$_GET['view']);
    }

    // Debug information (remove in production)
    if (isset($_GET['debug'])) {
        echo "<pre>";
        echo "Transactions count: " . count($transactions) . "\n";
        echo "Stats: " . print_r($stats, true) . "\n";
        echo "Users count: " . count($users) . "\n";
        echo "</pre>";
    }

} catch (Exception $e) {
    // Handle any errors gracefully
    error_log("Error in vDuyetNapTien.php: " . $e->getMessage());
    
    // Set default values to prevent errors
    $transactions = [];
    $pagination = ['total' => 0, 'per_page' => 10, 'current_page' => 1, 'total_pages' => 0];
    $stats = ['total_transactions' => 0, 'pending_count' => 0, 'approved_count' => 0, 'rejected_count' => 0];
    $users = [];
    $transactionDetails = null;
    
    // Show error message
    $errorMessage = "Có lỗi xảy ra khi tải dữ liệu. Vui lòng thử lại sau.";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Quản lý giao dịch nạp tiền</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php require_once '../helpers/url_helper.php'; ?>
    <link rel="stylesheet" href="<?= getBasePath() ?>/css/duyetnaptien.css">
</head>
<body>
    <?php if (isset($errorMessage)): ?>
<div class="container-fluid py-4">
    <div class="alert alert-danger" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?php echo $errorMessage; ?>
        <br><small>Hệ thống đang sử dụng dữ liệu mẫu để demo.</small>
    </div>
</div>
<?php endif; ?>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title mb-0">
                                <i class="fas fa-money-check-alt me-2"></i>
                                Quản lý giao dịch nạp tiền
                            </h4>
                            <?php if (isset($stats['pending_count']) && $stats['pending_count'] > 0): ?>
                            <span class="badge bg-warning text-dark fs-6">
                                <i class="fas fa-clock me-1"></i>
                                <?php echo $stats['pending_count']; ?> giao dịch đang chờ xử lý
                            </span> 
                            <?php endif; ?>
                        </div>
                        
                        <!-- Alert Messages -->
                        <div id="alertContainer"></div>
                        
                        <?php if ($transactionDetails): ?>
                        <!-- Transaction Details Section -->
                        <div class="transaction-details bg-light p-4 rounded mb-4">
                            <h5 class="mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                Chi tiết giao dịch #<?php echo $transactionDetails['history_id']; ?>
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>ID giao dịch:</strong></td>
                                            <td><?php echo $transactionDetails['history_id']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Người dùng:</strong></td>
                                            <td><?php echo $transactionDetails['username']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td><?php echo $transactionDetails['email']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Nội dung:</strong></td>
                                            <td><?php echo $transactionDetails['transfer_content']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Trạng thái:</strong></td>
                                            <td><?php echo getStatusBadge($transactionDetails['transfer_status']); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Số dư hiện tại:</strong></td>
                                            <td class="text-success fw-bold"><?php echo number_format($transactionDetails['balance'], 0, ',', '.'); ?> VND</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Ngày tạo:</strong></td>
                                            <td><?php echo date('d/m/Y H:i:s', strtotime($transactionDetails['created_date'])); ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <?php if (!empty($transactionDetails['transfer_image'])): ?>
                                        <p><strong>Hình ảnh chuyển khoản:</strong></p>
                                        <img src="<?= getBasePath() ?>/img/<?php echo $transactionDetails['transfer_image']; ?>" 
                                             alt="Transfer Image" 
                                             class="img-fluid rounded border"
                                             style="max-height: 300px; cursor: pointer;"
                                             onclick="openImageModal(this.src)">
                                    <?php else: ?>
                                        <div class="text-center text-muted py-5">
                                            <i class="fas fa-image fa-3x mb-3"></i>
                                            <p>Không có hình ảnh</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if ($transactionDetails['transfer_status'] == 'Đang chờ duyệt'): ?>
                            <div class="mt-4 border-top pt-3">
                                <button class="btn btn-success me-2" onclick="showApproveModal(<?php echo $transactionDetails['history_id']; ?>, '<?php echo $transactionDetails['transfer_content']; ?>')">
                                    <i class="fas fa-check me-1"></i> Phê duyệt
                                </button>
                                <button class="btn btn-danger me-2" onclick="rejectTransaction(<?php echo $transactionDetails['history_id']; ?>)">
                                    <i class="fas fa-times me-1"></i> Từ chối
                                </button>
                            </div>
                            <?php endif; ?>
                            
                            <div class="mt-3">
                                <a href="?" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
                                </a>
                            </div>
                        </div>
                        <?php else: ?>
                        <!-- Statistics Section -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="stats-card bg-primary text-white">
                                    <div class="stats-icon">
                                        <i class="fas fa-list"></i>
                                    </div>
                                    <div class="stats-content">
                                        <h3><?php echo $stats['total_transactions']; ?></h3>
                                        <p>Tổng số giao dịch</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card bg-warning text-dark">
                                    <div class="stats-icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="stats-content">
                                        <h3><?php echo $stats['pending_count']; ?></h3>
                                        <p>Đang chờ xác nhận</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card bg-success text-white">
                                    <div class="stats-icon">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="stats-content">
                                        <h3><?php echo $stats['approved_count']; ?></h3>
                                        <p>Đã xác nhận</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card bg-danger text-white">
                                    <div class="stats-icon">
                                        <i class="fas fa-times-circle"></i>
                                    </div>
                                    <div class="stats-content">
                                        <h3><?php echo $stats['rejected_count']; ?></h3>
                                        <p>Đã từ chối</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Search and Filter Section -->
                        <div class="filter-section bg-light p-3 rounded mb-4">
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <form method="GET" class="d-flex">
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-search"></i>
                                            </span>
                                            <input type="text" class="form-control" name="search" 
                                                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" 
                                                   placeholder="Tìm kiếm theo tên, email hoặc nội dung...">
                                            <button class="btn btn-primary" type="submit">
                                                Tìm kiếm
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <form method="GET" class="row g-3">
                                <div class="col-md-4">
                                    <label for="status" class="form-label">Trạng thái</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">Tất cả trạng thái</option>
                                        <option value="Đang chờ duyệt" <?php echo $status === 'Đang chờ duyệt' ? 'selected' : ''; ?>>Đang chờ xác nhận</option>
                                        <option value="Đã duyệt" <?php echo $status === 'Đã duyệt' ? 'selected' : ''; ?>>Đã xác nhận</option>
                                        <option value="Từ chối duyệt" <?php echo $status === 'Từ chối duyệt' ? 'selected' : ''; ?>>Đã từ chối</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="user_id" class="form-label">Người dùng</label>
                                    <select class="form-select" id="user_id" name="user_id">
                                        <option value="">Tất cả người dùng</option>
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?php echo $user['id']; ?>" <?php echo $userId === $user['id'] ? 'selected' : ''; ?>>
                                                <?php echo $user['username']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-filter me-1"></i> Lọc
                                    </button>
                                    <a href="?" class="btn btn-secondary">
                                        <i class="fas fa-redo me-1"></i> Đặt lại
                                    </a>
                                </div>
                                <?php if (isset($_GET['search'])): ?>
                                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                                <?php endif; ?>
                            </form>
                        </div>
                        
                        <!-- Bulk Actions -->
                        <div class="bulk-actions mb-3" id="bulkActions" style="display: none;">
                            <div class="d-flex align-items-center gap-3 p-3 bg-info bg-opacity-10 rounded">
                                <span class="fw-bold">Đã chọn <span id="selectedCount">0</span> giao dịch:</span>
                                <button type="button" class="btn btn-success btn-sm" onclick="bulkApprove()">
                                    <i class="fas fa-check me-1"></i> Phê duyệt đã chọn
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="bulkReject()">
                                    <i class="fas fa-times me-1"></i> Từ chối đã chọn
                                </button>
                            </div>
                        </div>
                        
                        <!-- Transactions Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th>ID</th>
                                        <th>Người dùng</th>
                                        <th>Nội dung</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày tạo</th>
                                        <th width="200">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($transactions)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">Không có giao dịch nào</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($transactions as $transaction): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($transaction['transfer_status'] == 'Đang chờ duyệt'): ?>
                                                        <input type="checkbox" name="transaction_ids[]" 
                                                               value="<?php echo $transaction['history_id']; ?>" 
                                                               class="form-check-input transaction-checkbox">
                                                    <?php endif; ?>
                                                </td>
                                                <td class="fw-bold"><?php echo $transaction['history_id']; ?></td>
                                                <td>
                                                    <div>
                                                        <strong><?php echo $transaction['username']; ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo $transaction['email']; ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                                                          title="<?php echo $transaction['transfer_content']; ?>">
                                                        <?php echo $transaction['transfer_content']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo getStatusBadge($transaction['status_ck']); ?></td>
                                                <td>
                                                    <small><?php echo date('d/m/Y H:i', strtotime($transaction['created_date'])); ?></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group-vertical btn-group-sm" role="group">
                                                        <a href="?view=<?php echo $transaction['history_id']; ?>" 
                                                           class="btn btn-info btn-sm">
                                                            <i class="fas fa-eye"></i> Xem
                                                        </a>
                                                        
                                                        <?php if ($transaction['transfer_status'] == 'Đang chờ duyệt'): ?>
                                                            <button class="btn btn-success btn-sm" 
                                                                    onclick="showApproveModal(<?php echo $transaction['history_id']; ?>, '<?php echo $transaction['transfer_content']; ?>')">
                                                                <i class="fas fa-check"></i> Duyệt
                                                            </button>
                                                            <button class="btn btn-danger btn-sm" 
                                                                    onclick="rejectTransaction(<?php echo $transaction['history_id']; ?>)">
                                                                <i class="fas fa-times"></i> Từ chối
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($pagination['total_pages'] > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=1<?php echo buildQueryString(); ?>">
                                                <i class="fas fa-angle-double-left"></i>
                                            </a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo buildQueryString(); ?>">
                                                <i class="fas fa-angle-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php
                                    $startPage = max(1, $page - 2);
                                    $endPage = min($pagination['total_pages'], $page + 2);
                                    
                                    for ($i = $startPage; $i <= $endPage; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo buildQueryString(); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $pagination['total_pages']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo buildQueryString(); ?>">
                                                <i class="fas fa-angle-right"></i>
                                            </a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $pagination['total_pages']; ?><?php echo buildQueryString(); ?>">
                                                <i class="fas fa-angle-double-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Phê duyệt giao dịch
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Giao dịch:</label>
                        <p id="transactionInfo" class="text-muted"></p>
                    </div>
                    <div class="mb-3">
                        <label for="approveAmount" class="form-label fw-bold">Số tiền cộng vào tài khoản (VND):</label>
                        <input type="number" class="form-control" id="approveAmount" min="1" step="1000">
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Số tiền được trích xuất tự động: <span id="extractedAmount" class="fw-bold text-primary"></span> VND
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Hủy
                    </button>
                    <button type="button" class="btn btn-success" onclick="confirmApprove()">
                        <i class="fas fa-check me-1"></i> Xác nhận phê duyệt
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hình ảnh chuyển khoản</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="Transfer Image" class="img-fluid">
                </div>
                


            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay" style="display: none;">
        <div class="loading-content">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Đang xử lý...</p>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="<?= getBasePath() ?>/js/duyetnaptienscript.js"></script>
</body>
</html>

<?php
// Helper functions
function getStatusBadge($status) {
    switch($status) {
        case 'Đang chờ duyệt':
            return '<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> Đang chờ duyệt</span>';
        case 'Đã duyệt':
            return '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Đã duyệt</span>';
        case 'Từ chối duyệt':
            return '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Từ chối duyệt</span>';
        default:
            return '<span class="badge bg-secondary">Không xác định</span>';
    }
}

function buildQueryString() {
    $params = [];
    if (isset($_GET['status']) && $_GET['status'] !== '') {
        $params[] = 'status=' . urlencode($_GET['status']);
    }
    if (isset($_GET['user_id']) && $_GET['user_id'] !== '') {
        $params[] = 'user_id=' . urlencode($_GET['user_id']);
    }
    if (isset($_GET['search']) && $_GET['search'] !== '') {
        $params[] = 'search=' . urlencode($_GET['search']);
    }
    return !empty($params) ? '&' . implode('&', $params) : '';
}
?>
