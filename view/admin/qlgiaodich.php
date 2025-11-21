<?php
/**
 * Trang quản lý giao dịch - Dashboard admin
 */

require_once 'model/mConnect.php';
require_once 'model/mQLgiaodich.php';

// Kiểm tra quyền admin
// $isAdmin = isset($_GET['admin']) && $_GET['admin'] === 'true';

// if (!$isAdmin) {
//     die('Access denied. Add ?admin=true to URL');
// }

$paymentManager = new PaymentManager();
$db = $paymentManager->getDb();
// $db = DatabaseManager::getInstance()->getDatabase();

// Xử lý actions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_status':
            $transactionId = $_POST['transaction_id'] ?? '';
            $newStatus = $_POST['new_status'] ?? '';
            
            if ($transactionId && $newStatus) {
                $stmt = $db->prepare("UPDATE transactions SET status = ? WHERE transaction_id = ?");
                $stmt->bind_param("ss", $newStatus, $transactionId);
                
                if ($stmt->execute()) {
                    $message = "Cập nhật trạng thái thành công!";
                } else {
                    $message = "Lỗi cập nhật trạng thái!";
                }
                $stmt->close();
            }
            break;
            
        case 'manual_complete':
            $transactionId = $_POST['transaction_id'] ?? '';
            
            if ($transactionId) {
                $result = $paymentManager->updateBalance($transactionId, 0, ['manual' => true, 'admin_user' => 'admin']);
                $message = $result['success'] ? $result['message'] : $result['error'];
            }
            break;
    }
}

// Lấy thống kê
$stats = [];

// Tổng số giao dịch
$result = $db->query("SELECT COUNT(*) as total FROM transactions");
$stats['total_transactions'] = $result->fetch_assoc()['total'];

// Giao dịch thành công
$result = $db->query("SELECT COUNT(*) as completed FROM transactions WHERE status = 'completed'");
$stats['completed_transactions'] = $result->fetch_assoc()['completed'];

// Giao dịch đang chờ
$result = $db->query("SELECT COUNT(*) as pending FROM transactions WHERE status = 'pending'");
$stats['pending_transactions'] = $result->fetch_assoc()['pending'];

// Tổng tiền đã nạp
$result = $db->query("SELECT SUM(amount) as total_amount FROM transactions WHERE status = 'completed'");
$stats['total_amount'] = $result->fetch_assoc()['total_amount'] ?? 0;

// Lấy danh sách giao dịch
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

$whereClause = "WHERE 1=1";
$params = [];
$types = "";

if ($filter !== 'all') {
    $whereClause .= " AND t.status = ?";
    $params[] = $filter;
    $types .= "s";
}

if ($search) {
    $whereClause .= " AND (t.transaction_id LIKE ? OR ta.account_number LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}

$sql = "
    SELECT t.*, ta.account_number, ta.balance 
    FROM transactions t 
    LEFT JOIN transfer_accounts ta ON t.account_id = ta.id 
    $whereClause 
    ORDER BY t.id DESC 
    LIMIT $limit OFFSET $offset
";

$stmt = $db->prepare($sql);
if (!$stmt) {
    die("Lỗi prepare SQL: " . $db->error);
}

if ($params) {
    $stmt->bind_param($types, ...$params);
}

if (!$stmt->execute()) {
    die("Lỗi execute SQL: " . $stmt->error);
}

$result = $stmt->get_result();
$transactions = [];
while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}
$stmt->close();

// Đếm tổng số trang
$countSql = "
    SELECT COUNT(*) as total 
    FROM transactions t 
    LEFT JOIN transfer_accounts ta ON t.account_id = ta.id 
    $whereClause
";

$stmt = $db->prepare($countSql);
if (!$stmt) {
    die("Lỗi prepare count SQL: " . $db->error);
}

if ($params) {
    $stmt->bind_param($types, ...$params);
}

if (!$stmt->execute()) {
    die("Lỗi execute count SQL: " . $stmt->error);
}

$result = $stmt->get_result();
$totalRecords = $result->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $limit);
$currentPage = $page;
$totalItems = $totalRecords;
$stmt->close();

// Function to generate pagination URL
function getPaginationUrl($page, $filter, $search) {
    $url = "qlgiaodich&page={$page}";
    if ($filter && $filter !== 'all') $url .= "&filter={$filter}";
    if ($search) $url .= "&search=" . urlencode($search);
    return $url;
}
?>

<?php require_once __DIR__ . '/../../helpers/url_helper.php'; ?>
<link rel="stylesheet" href="<?php echo getBasePath() ?>/css/admin-common.css">
<style>
        /* CSS riêng cho trang quản lý giao dịch */
        /* CSS riêng cho trang quản lý giao dịch - chỉ override nếu cần */
        .qlgiaodich-container {
            /* Đã được định nghĩa trong admin-common.css */
        }
        
        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .status.pending { background: #fff3cd; color: #856404; }
        .status.completed { background: #d4edda; color: #155724; }
        .status.failed { background: #f8d7da; color: #721c24; }
        .status.cancelled { background: #e2e3e5; color: #383d41; }
        
        .amount { font-weight: 600; color: #28a745; }
        .transaction-id { font-family: monospace; font-size: 0.9rem; color: #6c757d; }
        
        .transactions-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .modal { 
            display: none; 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            background: rgba(0,0,0,0.5); 
            z-index: 1000; 
        }
        
        .modal-content { 
            background: white; 
            margin: 10% auto; 
            padding: 20px; 
            width: 90%; 
            max-width: 500px; 
            border-radius: 10px; 
        }
        
        .modal-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 20px; 
        }
        
        .close { 
            font-size: 24px; 
            cursor: pointer; 
            background: none;
            border: none;
        }
    </style>

    <div class="qlgiaodich-container">
        <div class="admin-card">
            <h3 class="admin-card-title">Quản lý giao dịch</h3>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-grid">
            <div class="stat-card">
                <h3>Tổng Giao Dịch</h3>
                <div class="number"><?php echo number_format($stats['total_transactions']); ?></div>
            </div>
            <div class="stat-card success">
                <h3>Thành Công</h3>
                <div class="number"><?php echo number_format($stats['completed_transactions']); ?></div>
            </div>
            <div class="stat-card warning">
                <h3>Đang Chờ</h3>
                <div class="number"><?php echo number_format($stats['pending_transactions']); ?></div>
            </div>
            <div class="stat-card primary">
                <h3>Tổng Tiền Nạp</h3>
                <div class="number"><?php echo number_format($stats['total_amount'], 0, ',', '.'); ?> VND</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters">
            <form method="GET">
                <input type="hidden" name="qlgiaodich" value="">
                
                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="filter" class="form-control">
                        <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>Tất cả trạng thái</option>
                        <option value="pending" <?php echo $filter === 'pending' ? 'selected' : ''; ?>>Đang chờ</option>
                        <option value="completed" <?php echo $filter === 'completed' ? 'selected' : ''; ?>>Thành công</option>
                        <option value="failed" <?php echo $filter === 'failed' ? 'selected' : ''; ?>>Thất bại</option>
                        <option value="cancelled" <?php echo $filter === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Tìm kiếm</label>
                    <input type="text" name="search" class="form-control" placeholder="Mã GD hoặc STK..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-filter"></i> Lọc
                    </button>
                    <a href="?qlgiaodich" class="btn btn-secondary" style="margin-left: 10px;">
                        <i class="mdi mdi-refresh"></i> Đặt lại
                    </a>
                </div>
            </form>
        </div>

        <!-- Transactions Table -->
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Mã Giao Dịch</th>
                        <th>Tài Khoản</th>
                        <th>Số Tiền</th>
                        <th>Trạng Thái</th>
                        <th>Thời Gian</th>
                        <th>Ghi Chú</th>
                        <th>Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="7" class="empty-message">
                                Không có giao dịch nào
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td>
                                <div class="transaction-id">#<?php echo htmlspecialchars($transaction['transaction_id'] ?? 'N/A'); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($transaction['account_number'] ?? 'N/A'); ?></td>
                            <td>
                                <div class="amount"><?php echo number_format($transaction['amount'] ?? 0, 0, ',', '.'); ?> VND</div>
                            </td>
                            <td>
                                <span class="status <?php echo $transaction['status'] ?? 'pending'; ?>">
                                    <?php 
                                    $statusText = [
                                        'pending' => 'Đang chờ',
                                        'completed' => 'Thành công', 
                                        'failed' => 'Thất bại',
                                        'cancelled' => 'Đã hủy'
                                    ];
                                    $status = $transaction['status'] ?? 'pending';
                                    echo $statusText[$status] ?? $status;
                                    ?>
                                </span>
                            </td>
                            <td>
                                <div>ID: <?php echo htmlspecialchars($transaction['id'] ?? 'N/A'); ?></div>
                                <small style="color: #6c757d;">Loại: <?php 
                                    $typeText = [
                                        'deposit' => 'Nạp tiền',
                                        'withdrawal' => 'Rút tiền',
                                        'transfer' => 'Chuyển khoản'
                                    ];
                                    $type = $transaction['transaction_type'] ?? 'deposit';
                                    echo $typeText[$type] ?? $type;
                                ?></small>
                            </td>
                            <td>
                                <small><?php 
                                    $notes = '';
                                    if (isset($transaction['callback_data']) && !empty($transaction['callback_data'])) {
                                        $callbackData = json_decode($transaction['callback_data'], true);
                                        if (is_array($callbackData) && isset($callbackData['description'])) {
                                            $notes = $callbackData['description'];
                                        }
                                    }
                                    echo htmlspecialchars($notes ?: 'Không có');
                                ?></small>
                            </td>
                            <td>
                                <div class="actions">
                                    <?php if (($transaction['status'] ?? '') === 'pending'): ?>
                                        <button class="btn btn-success" onclick="completeTransaction('<?php echo htmlspecialchars($transaction['transaction_id'] ?? ''); ?>')">
                                            Hoàn thành
                                        </button>
                                        <button class="btn btn-danger" onclick="updateStatus('<?php echo htmlspecialchars($transaction['transaction_id'] ?? ''); ?>', 'failed')">
                                            Thất bại
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button class="btn btn-primary" onclick="viewDetails('<?php echo htmlspecialchars($transaction['transaction_id'] ?? ''); ?>')">
                                        Chi tiết
                                    </button>
                                </div>
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
                Hiển thị <?php echo count($transactions); ?> trên tổng số <?php echo $totalItems; ?> bản ghi
            </div>
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?<?php echo getPaginationUrl($currentPage - 1, $filter, $search); ?>">&lt;</a>
                <?php endif; ?>
                
                <?php
                    // Determine range of page numbers to show (current page ± 1)
                    $startPage = max(1, $currentPage - 1);
                    $endPage = min($totalPages, $currentPage + 1);
                    
                    // Show first page if not in range
                    if ($startPage > 1): ?>
                        <a href="?<?php echo getPaginationUrl(1, $filter, $search); ?>">1</a>
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
                        <a href="?<?php echo getPaginationUrl($i, $filter, $search); ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php
                    // Show last page if not in range
                    if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <span class="ellipsis">...</span>
                        <?php endif; ?>
                        <a href="?<?php echo getPaginationUrl($totalPages, $filter, $search); ?>"><?php echo $totalPages; ?></a>
                    <?php endif; ?>
                
                <?php if ($currentPage < $totalPages): ?>
                    <a href="?<?php echo getPaginationUrl($currentPage + 1, $filter, $search); ?>">&gt;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        </div>
    </div>

    <!-- Modal for transaction details -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Chi Tiết Giao Dịch</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div id="modalBody">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Hidden forms for actions -->
    <form id="statusForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="transaction_id" id="statusTransactionId">
        <input type="hidden" name="new_status" id="newStatus">
    </form>

    <form id="completeForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="manual_complete">
        <input type="hidden" name="transaction_id" id="completeTransactionId">
    </form>

    <script>
        function updateStatus(transactionId, status) {
            document.getElementById('updateStatusTransactionId').value = transactionId;
            document.getElementById('updateStatusNewStatus').value = status;
            const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
            modal.show();
        }

        function confirmUpdateStatus() {
            document.getElementById('statusTransactionId').value = document.getElementById('updateStatusTransactionId').value;
            document.getElementById('newStatus').value = document.getElementById('updateStatusNewStatus').value;
            document.getElementById('statusForm').submit();
        }

        function completeTransaction(transactionId) {
            document.getElementById('completeTransactionId').value = transactionId;
            const modal = new bootstrap.Modal(document.getElementById('completeTransactionModal'));
            modal.show();
        }

        function confirmCompleteTransaction() {
            document.getElementById('completeForm').submit();
        }

        function viewDetails(transactionId) {
            // Load transaction details via AJAX
            fetch('api/get_transaction_details.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    transaction_id: transactionId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showTransactionDetails(data.transaction);
                } else {
                    alert('Lỗi: ' + data.error);
                }
            })
            .catch(error => {
                alert('Lỗi kết nối: ' + error.message);
            });
        }

        function showTransactionDetails(transaction) {
            const modalBody = document.getElementById('modalBody');
            modalBody.innerHTML = `
                <table class="info-table" style="width: 100%; border-collapse: collapse;">
                    <tr><th style="padding: 8px; border: 1px solid #ddd;">Mã giao dịch</th><td style="padding: 8px; border: 1px solid #ddd;">#${transaction.transaction_id}</td></tr>
                    <tr><th style="padding: 8px; border: 1px solid #ddd;">Số tiền</th><td style="padding: 8px; border: 1px solid #ddd;">${new Intl.NumberFormat('vi-VN').format(transaction.amount)} VND</td></tr>
                    <tr><th style="padding: 8px; border: 1px solid #ddd;">Trạng thái</th><td style="padding: 8px; border: 1px solid #ddd;"><span class="status ${transaction.status}">${transaction.status}</span></td></tr>
                    <tr><th style="padding: 8px; border: 1px solid #ddd;">Tài khoản</th><td style="padding: 8px; border: 1px solid #ddd;">${transaction.account_number}</td></tr>
                    <tr><th style="padding: 8px; border: 1px solid #ddd;">ID</th><td style="padding: 8px; border: 1px solid #ddd;">${transaction.id || 'N/A'}</td></tr>
                    <tr><th style="padding: 8px; border: 1px solid #ddd;">Ghi chú</th><td style="padding: 8px; border: 1px solid #ddd;">${transaction.notes || 'Không có'}</td></tr>
                    ${transaction.qr_code_url ? `<tr><th style="padding: 8px; border: 1px solid #ddd;">QR Code</th><td style="padding: 8px; border: 1px solid #ddd;"><img src="${transaction.qr_code_url}" style="max-width: 200px;"></td></tr>` : ''}
                </table>
            `;
            
            document.getElementById('detailModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('detailModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('detailModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cập nhật trạng thái giao dịch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn cập nhật trạng thái giao dịch này?</p>
                <input type="hidden" id="updateStatusTransactionId">
                <input type="hidden" id="updateStatusNewStatus">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="confirmUpdateStatus()">Xác nhận cập nhật</button>
            </div>
        </div>
    </div>
</div>

<!-- Complete Transaction Modal -->
<div class="modal fade" id="completeTransactionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hoàn thành giao dịch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn hoàn thành giao dịch này?</p>
                <p>Sau khi hoàn thành, số dư sẽ được cộng vào tài khoản.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-success" onclick="confirmCompleteTransaction()">Xác nhận hoàn thành</button>
            </div>
        </div>
    </div>
</div>
