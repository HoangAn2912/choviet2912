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
$messageType = 'success'; // 'success' hoặc 'error'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_status':
            // Xử lý khi admin đánh dấu "Thất bại"
            $transactionId = $_POST['transaction_id'] ?? '';
            $newStatus = $_POST['new_status'] ?? 'failed';
            
            if (empty($transactionId)) {
                $message = "Lỗi: Thiếu mã giao dịch!";
                $messageType = 'error';
                break;
            }
            
            // Bắt đầu transaction để đảm bảo tính nhất quán
            $db->begin_transaction();
            
            try {
                // Lấy thông tin giao dịch với user_id và account_id
                $stmt = $db->prepare("
                    SELECT t.*, ta.user_id, ta.id as account_id, ta.account_number
                    FROM transactions t 
                    LEFT JOIN transfer_accounts ta ON t.account_id = ta.id 
                    WHERE t.transaction_id = ? AND t.status = 'pending'
                    FOR UPDATE
                ");
                
                if (!$stmt) {
                    throw new Exception("Lỗi prepare SQL: " . $db->error);
                }
                
                $stmt->bind_param("s", $transactionId);
                $stmt->execute();
                $result = $stmt->get_result();
                $transaction = $result->fetch_assoc();
                $stmt->close();
                
                if (!$transaction) {
                    throw new Exception("Giao dịch không tồn tại, đã được xử lý, hoặc không ở trạng thái 'pending'!");
                }
                
                // Chỉ cập nhật trạng thái, KHÔNG cộng tiền
                $stmt = $db->prepare("
                    UPDATE transactions 
                    SET status = ?, 
                        callback_data = ?,
                        updated_at = NOW()
                    WHERE transaction_id = ? AND status = 'pending'
                ");
                
                if (!$stmt) {
                    throw new Exception("Lỗi prepare SQL: " . $db->error);
                }
                
                $callbackData = json_encode([
                    'manual' => true,
                    'admin_user' => $_SESSION['username'] ?? 'admin',
                    'action' => 'mark_failed',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                
                $stmt->bind_param("sss", $newStatus, $callbackData, $transactionId);
                
                if (!$stmt->execute()) {
                    throw new Exception("Lỗi cập nhật trạng thái: " . $stmt->error);
                }
                
                $stmt->close();
                
                // Commit transaction
                $db->commit();
                $message = "Đã cập nhật trạng thái giao dịch thành '{$newStatus}' thành công!";
                $messageType = 'success';
                
            } catch (Exception $e) {
                $db->rollback();
                $message = "Lỗi: " . $e->getMessage();
                $messageType = 'error';
                error_log("Transaction update_status error: " . $e->getMessage());
            }
            break;
            
        case 'manual_complete':
            // Xử lý khi admin đánh dấu "Hoàn thành"
            $transactionId = $_POST['transaction_id'] ?? '';
            
            if (empty($transactionId)) {
                $message = "Lỗi: Thiếu mã giao dịch!";
                $messageType = 'error';
                break;
            }
            
            // Bắt đầu transaction để đảm bảo tính nhất quán
            $db->begin_transaction();
            
            try {
                // Lấy thông tin giao dịch - LẤY user_id TRỰC TIẾP TỪ transactions
                $stmt = $db->prepare("
                    SELECT 
                        t.*, 
                        t.user_id,  -- Lấy trực tiếp từ transactions
                        t.account_id,  -- Lấy trực tiếp từ transactions
                        ta.account_number, 
                        ta.balance as current_balance
                    FROM transactions t 
                    LEFT JOIN transfer_accounts ta ON t.account_id = ta.id 
                    WHERE t.transaction_id = ? AND t.status = 'pending'
                    FOR UPDATE
                ");
                
                if (!$stmt) {
                    throw new Exception("Lỗi prepare SQL: " . $db->error);
                }
                
                $stmt->bind_param("s", $transactionId);
                $stmt->execute();
                $result = $stmt->get_result();
                $transaction = $result->fetch_assoc();
                $stmt->close();
                
                if (!$transaction) {
                    throw new Exception("Giao dịch không tồn tại, đã được xử lý, hoặc không ở trạng thái 'pending'!");
                }
                
                // Kiểm tra user_id từ transactions (bắt buộc)
                if (empty($transaction['user_id'])) {
                    throw new Exception("Giao dịch không có thông tin người dùng (user_id)!");
                }
                
                $userId = intval($transaction['user_id']);
                $accountId = !empty($transaction['account_id']) ? intval($transaction['account_id']) : null;
                
                // Xử lý trường hợp không có account_id hoặc account không tồn tại
                if (empty($accountId) || empty($transaction['current_balance']) && $transaction['current_balance'] !== '0') {
                    // Tìm account từ user_id
                    $stmt = $db->prepare("SELECT id, balance FROM transfer_accounts WHERE user_id = ? LIMIT 1 FOR UPDATE");
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $account = $result->fetch_assoc();
                    $stmt->close();
                    
                    if (!$account) {
                        // Tạo tài khoản mới nếu chưa có
                        $accountNumber = 'ACC' . str_pad($userId, 8, '0', STR_PAD_LEFT);
                        $stmt = $db->prepare("INSERT INTO transfer_accounts (account_number, user_id, balance) VALUES (?, ?, 0)");
                        $stmt->bind_param("si", $accountNumber, $userId);
                        if (!$stmt->execute()) {
                            throw new Exception("Lỗi tạo tài khoản mới: " . $stmt->error);
                        }
                        $accountId = $stmt->insert_id;
                        $stmt->close();
                        
                        // Cập nhật account_id vào transaction nếu chưa có
                        if (empty($transaction['account_id'])) {
                            $stmt = $db->prepare("UPDATE transactions SET account_id = ? WHERE transaction_id = ?");
                            $stmt->bind_param("is", $accountId, $transactionId);
                            $stmt->execute();
                            $stmt->close();
                        }
                        
                        $currentBalance = 0;
                    } else {
                        $accountId = intval($account['id']);
                        $currentBalance = floatval($account['balance']);
                        
                        // Cập nhật account_id vào transaction nếu chưa có
                        if (empty($transaction['account_id'])) {
                            $stmt = $db->prepare("UPDATE transactions SET account_id = ? WHERE transaction_id = ?");
                            $stmt->bind_param("is", $accountId, $transactionId);
                            $stmt->execute();
                            $stmt->close();
                        }
                    }
                } else {
                    $accountId = intval($accountId);
                    $currentBalance = floatval($transaction['current_balance'] ?? 0);
                }
                
                // Kiểm tra amount hợp lệ
                $amount = floatval($transaction['amount'] ?? 0);
                if ($amount <= 0) {
                    throw new Exception("Số tiền giao dịch không hợp lệ: " . $amount);
                }
                
                // Cộng tiền vào tài khoản của user
                $stmt = $db->prepare("
                    UPDATE transfer_accounts 
                    SET balance = balance + ?
                    WHERE id = ? AND user_id = ?
                ");
                
                if (!$stmt) {
                    throw new Exception("Lỗi prepare SQL: " . $db->error);
                }
                
                $stmt->bind_param("dii", $amount, $accountId, $userId);
                
                if (!$stmt->execute()) {
                    throw new Exception("Lỗi cập nhật số dư: " . $stmt->error);
                }
                
                $affectedRows = $stmt->affected_rows;
                $stmt->close();
                
                if ($affectedRows === 0) {
                    throw new Exception("Không thể cập nhật số dư. Kiểm tra lại account_id={$accountId} và user_id={$userId}!");
                }
                
                // Lấy số dư mới để log
                $stmt = $db->prepare("SELECT balance FROM transfer_accounts WHERE id = ?");
                $stmt->bind_param("i", $accountId);
                $stmt->execute();
                $result = $stmt->get_result();
                $newBalanceRow = $result->fetch_assoc();
                $newBalance = floatval($newBalanceRow['balance'] ?? 0);
                $stmt->close();
                
                // Cập nhật trạng thái transaction thành 'completed'
                $callbackData = json_encode([
                    'manual' => true,
                    'admin_user' => $_SESSION['username'] ?? 'admin',
                    'action' => 'manual_complete',
                    'amount_added' => $amount,
                    'old_balance' => $currentBalance,
                    'new_balance' => $newBalance,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                
                $stmt = $db->prepare("
                    UPDATE transactions 
                    SET status = 'completed', 
                        callback_data = ?,
                        updated_at = NOW()
                    WHERE transaction_id = ? AND status = 'pending'
                ");
                
                if (!$stmt) {
                    throw new Exception("Lỗi prepare SQL: " . $db->error);
                }
                
                $stmt->bind_param("ss", $callbackData, $transactionId);
                
                if (!$stmt->execute()) {
                    throw new Exception("Lỗi cập nhật trạng thái giao dịch: " . $stmt->error);
                }
                
                $affectedRows = $stmt->affected_rows;
                $stmt->close();
                
                if ($affectedRows === 0) {
                    // Rollback vì transaction đã bị thay đổi bởi process khác
                    throw new Exception("Giao dịch đã được xử lý bởi process khác. Đã rollback thay đổi số dư.");
                }
                
                // Commit transaction
                $db->commit();
                $message = "Đã hoàn thành giao dịch thành công! Đã cộng " . number_format($amount, 0, ',', '.') . " VND vào tài khoản người dùng.";
                $messageType = 'success';
                
                // Log thành công
                error_log("Manual complete transaction success: transaction_id={$transactionId}, user_id={$userId}, account_id={$accountId}, amount={$amount}, old_balance={$currentBalance}, new_balance={$newBalance}");
                
            } catch (Exception $e) {
                $db->rollback();
                $message = "Lỗi: " . $e->getMessage();
                $messageType = 'error';
                error_log("Transaction manual_complete error: " . $e->getMessage());
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
        
        /* Bootstrap Modal Override - Fix z-index và backdrop */
        .modal {
            z-index: 1055 !important;
        }
        
        .modal-backdrop {
            z-index: 1050 !important;
            background-color: rgba(0, 0, 0, 0.5) !important;
        }
        
        .modal-backdrop.show {
            opacity: 0.5 !important;
        }
        
        .modal-dialog {
            z-index: 1056 !important;
            margin: 1.75rem auto;
        }
        
        .modal-content {
            border: none;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }
        
        .modal-header {
            border-bottom: 1px solid #dee2e6;
            padding: 1rem 1.5rem;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-footer {
            border-top: 1px solid #dee2e6;
            padding: 1rem 1.5rem;
        }
        
        /* Actions buttons */
        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .actions .btn {
            white-space: nowrap;
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .modal-dialog {
                margin: 0.5rem;
                max-width: calc(100% - 1rem);
            }
            
            .actions {
                flex-direction: column;
            }
            
            .actions .btn {
                width: 100%;
            }
        }
    </style>

    <div class="qlgiaodich-container">
        <div class="admin-card">
            <h3 class="admin-card-title">Quản lý giao dịch</h3>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                    <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-grid">
            <div class="stat-card info">
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
                <div class="number"><?php echo number_format($stats['total_amount'], 0, ',', '.'); ?> ₫</div>
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
                        <i class="bi bi-funnel-fill"></i> Lọc
                    </button>
                    <a href="?qlgiaodich" class="btn btn-secondary" style="margin-left: 10px;">
                        <i class="bi bi-arrow-clockwise"></i> Đặt lại
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
                                        <button class="btn btn-success btn-sm" onclick="completeTransaction('<?php echo htmlspecialchars($transaction['transaction_id'] ?? ''); ?>')">
                                            <i class="bi bi-check-circle"></i> Hoàn thành
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="updateStatus('<?php echo htmlspecialchars($transaction['transaction_id'] ?? ''); ?>', 'failed')">
                                            <i class="bi bi-x-circle"></i> Thất bại
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
        // Đảm bảo Bootstrap modal hoạt động đúng
        function updateStatus(transactionId, status) {
            if (!transactionId || !status) {
                console.error('Thiếu thông tin transactionId hoặc status');
                return;
            }
            
            // Set giá trị vào hidden inputs
            var updateStatusTransactionId = document.getElementById('updateStatusTransactionId');
            var updateStatusNewStatus = document.getElementById('updateStatusNewStatus');
            
            if (!updateStatusTransactionId || !updateStatusNewStatus) {
                console.error('Không tìm thấy input elements');
                return;
            }
            
            updateStatusTransactionId.value = transactionId;
            updateStatusNewStatus.value = status;
            
            // Kiểm tra Bootstrap đã load chưa
            if (typeof bootstrap === 'undefined') {
                console.error('Bootstrap chưa được load');
                alert('Hệ thống đang tải, vui lòng thử lại sau vài giây.');
                return;
            }
            
            // Lấy modal element
            var modalElement = document.getElementById('updateStatusModal');
            if (!modalElement) {
                console.error('Không tìm thấy modal updateStatusModal');
                return;
            }
            
            // Tạo và hiển thị modal
            try {
                // Xóa instance cũ nếu có
                var existingModal = bootstrap.Modal.getInstance(modalElement);
                if (existingModal) {
                    existingModal.dispose();
                }
                
                // Tạo modal mới với cấu hình đúng
                var modal = new bootstrap.Modal(modalElement, {
                    backdrop: true,
                    keyboard: true,
                    focus: true
                });
                
                // Xử lý sự kiện khi modal được hiển thị
                modalElement.addEventListener('shown.bs.modal', function() {
                    // Đảm bảo modal có z-index cao
                    modalElement.style.zIndex = '1055';
                    var backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.style.zIndex = '1050';
                    }
                }, { once: true });
                
                // Hiển thị modal
                modal.show();
            } catch (error) {
                console.error('Lỗi khi hiển thị modal:', error);
                alert('Không thể hiển thị modal. Vui lòng refresh trang.');
            }
        }

        function confirmUpdateStatus() {
            var statusTransactionId = document.getElementById('statusTransactionId');
            var newStatus = document.getElementById('newStatus');
            var updateStatusTransactionId = document.getElementById('updateStatusTransactionId');
            var updateStatusNewStatus = document.getElementById('updateStatusNewStatus');
            
            if (!statusTransactionId || !newStatus || !updateStatusTransactionId || !updateStatusNewStatus) {
                alert('Lỗi: Không tìm thấy thông tin giao dịch');
                return;
            }
            
            // Copy giá trị từ modal inputs sang form inputs
            statusTransactionId.value = updateStatusTransactionId.value;
            newStatus.value = updateStatusNewStatus.value;
            
            // Đóng modal trước khi submit
            var modalElement = document.getElementById('updateStatusModal');
            if (modalElement && typeof bootstrap !== 'undefined') {
                var modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }
            }
            
            // Submit form
            document.getElementById('statusForm').submit();
        }

        function completeTransaction(transactionId) {
            if (!transactionId) {
                console.error('Thiếu transactionId');
                return;
            }
            
            // Set giá trị vào hidden input
            var completeTransactionId = document.getElementById('completeTransactionId');
            if (!completeTransactionId) {
                console.error('Không tìm thấy input completeTransactionId');
                return;
            }
            
            completeTransactionId.value = transactionId;
            
            // Kiểm tra Bootstrap đã load chưa
            if (typeof bootstrap === 'undefined') {
                console.error('Bootstrap chưa được load');
                alert('Hệ thống đang tải, vui lòng thử lại sau vài giây.');
                return;
            }
            
            // Lấy modal element
            var modalElement = document.getElementById('completeTransactionModal');
            if (!modalElement) {
                console.error('Không tìm thấy modal completeTransactionModal');
                return;
            }
            
            // Tạo và hiển thị modal
            try {
                // Xóa instance cũ nếu có
                var existingModal = bootstrap.Modal.getInstance(modalElement);
                if (existingModal) {
                    existingModal.dispose();
                }
                
                // Tạo modal mới với cấu hình đúng
                var modal = new bootstrap.Modal(modalElement, {
                    backdrop: true,
                    keyboard: true,
                    focus: true
                });
                
                // Xử lý sự kiện khi modal được hiển thị
                modalElement.addEventListener('shown.bs.modal', function() {
                    // Đảm bảo modal có z-index cao
                    modalElement.style.zIndex = '1055';
                    var backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.style.zIndex = '1050';
                    }
                }, { once: true });
                
                // Hiển thị modal
                modal.show();
            } catch (error) {
                console.error('Lỗi khi hiển thị modal:', error);
                alert('Không thể hiển thị modal. Vui lòng refresh trang.');
            }
        }

        function confirmCompleteTransaction() {
            // Đóng modal trước khi submit
            var modalElement = document.getElementById('completeTransactionModal');
            if (modalElement && typeof bootstrap !== 'undefined') {
                var modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }
            }
            
            // Submit form
            document.getElementById('completeForm').submit();
        }
        
        // Xử lý cleanup khi modal đóng
        document.addEventListener('DOMContentLoaded', function() {
            // Cleanup backdrop khi modal đóng
            var modals = ['updateStatusModal', 'completeTransactionModal'];
            modals.forEach(function(modalId) {
                var modalElement = document.getElementById(modalId);
                if (modalElement) {
                    modalElement.addEventListener('hidden.bs.modal', function() {
                        // Xóa backdrop nếu còn sót lại
                        var backdrops = document.querySelectorAll('.modal-backdrop');
                        backdrops.forEach(function(backdrop) {
                            if (!document.querySelector('.modal.show')) {
                                backdrop.remove();
                            }
                        });
                        
                        // Đảm bảo body không bị lock
                        document.body.classList.remove('modal-open');
                        document.body.style.overflow = '';
                        document.body.style.paddingRight = '';
                    });
                }
            });
        });
    </script>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateStatusModalLabel">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>Cập nhật trạng thái giao dịch
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn cập nhật trạng thái giao dịch này thành <strong>"Thất bại"</strong>?</p>
                <p class="text-muted small mb-0">Hành động này không thể hoàn tác.</p>
                <input type="hidden" id="updateStatusTransactionId">
                <input type="hidden" id="updateStatusNewStatus">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Hủy
                </button>
                <button type="button" class="btn btn-danger" onclick="confirmUpdateStatus()">
                    <i class="bi bi-check-circle me-1"></i>Xác nhận cập nhật
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Complete Transaction Modal -->
<div class="modal fade" id="completeTransactionModal" tabindex="-1" aria-labelledby="completeTransactionModalLabel" aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="completeTransactionModalLabel">
                    <i class="bi bi-check-circle text-success me-2"></i>Hoàn thành giao dịch
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn hoàn thành giao dịch này?</p>
                <p class="text-muted small mb-0">Sau khi hoàn thành, số dư sẽ được cộng vào tài khoản người dùng.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Hủy
                </button>
                <button type="button" class="btn btn-success" onclick="confirmCompleteTransaction()">
                    <i class="bi bi-check-circle me-1"></i>Xác nhận hoàn thành
                </button>
            </div>
        </div>
    </div>
</div>
