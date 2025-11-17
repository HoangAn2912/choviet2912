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
                $stmt = $db->prepare("UPDATE transactions SET status = ?, updated_at = NOW() WHERE transaction_id = ?");
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
    JOIN transfer_accounts ta ON t.account_id = ta.id 
    $whereClause 
    ORDER BY t.created_at DESC 
    LIMIT $limit OFFSET $offset
";

$stmt = $db->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$transactions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Đếm tổng số trang
$countSql = "
    SELECT COUNT(*) as total 
    FROM transactions t 
    JOIN transfer_accounts ta ON t.account_id = ta.id 
    $whereClause
";

$stmt = $db->prepare($countSql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$totalRecords = $result->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $limit);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Giao Dịch</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8f9fa; }
        
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px 0; }
        .header .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .header h1 { font-size: 2rem; margin-bottom: 5px; }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .stat-card h3 { color: #666; font-size: 0.9rem; margin-bottom: 10px; text-transform: uppercase; }
        .stat-card .number { font-size: 2rem; font-weight: bold; color: #333; }
        .stat-card.success .number { color: #28a745; }
        .stat-card.warning .number { color: #ffc107; }
        .stat-card.primary .number { color: #007bff; }
        
        .filters { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .filters form { display: flex; gap: 15px; align-items: center; flex-wrap: wrap; }
        .filters select, .filters input { padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px; }
        .filters button { background: #007bff; color: white; padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer; }
        .filters button:hover { background: #0056b3; }
        
        .transactions-table { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .table { width: 100%; border-collapse: collapse; }
        .table th { background: #f8f9fa; padding: 15px; text-align: left; font-weight: 600; border-bottom: 1px solid #dee2e6; }
        .table td { padding: 15px; border-bottom: 1px solid #dee2e6; }
        .table tr:hover { background: #f8f9fa; }
        
        .status { padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; }
        .status.pending { background: #fff3cd; color: #856404; }
        .status.completed { background: #d4edda; color: #155724; }
        .status.failed { background: #f8d7da; color: #721c24; }
        .status.cancelled { background: #e2e3e5; color: #383d41; }
        
        .amount { font-weight: 600; color: #28a745; }
        .transaction-id { font-family: monospace; font-size: 0.9rem; color: #6c757d; }
        
        .actions { display: flex; gap: 5px; }
        .btn { padding: 4px 8px; border: none; border-radius: 4px; cursor: pointer; font-size: 0.8rem; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn:hover { opacity: 0.8; }
        
        .pagination { display: flex; justify-content: center; gap: 10px; margin-top: 20px; }
        .pagination a, .pagination span { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #007bff; }
        .pagination .current { background: #007bff; color: white; }
        
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { background: white; margin: 10% auto; padding: 20px; width: 90%; max-width: 500px; border-radius: 10px; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .close { font-size: 24px; cursor: pointer; }
        
        @media (max-width: 768px) {
            .table { font-size: 0.8rem; }
            .filters form { flex-direction: column; align-items: stretch; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <!-- <div class="header">
        <div class="container">
            <h1>📊 Quản Lý Giao Dịch</h1>
            <p>Dashboard quản lý thanh toán VietQR</p>
        </div>
    </div> -->

    <div class="container">
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
                <select name="filter">
                    <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>Tất cả trạng thái</option>
                    <option value="pending" <?php echo $filter === 'pending' ? 'selected' : ''; ?>>Đang chờ</option>
                    <option value="completed" <?php echo $filter === 'completed' ? 'selected' : ''; ?>>Thành công</option>
                    <option value="failed" <?php echo $filter === 'failed' ? 'selected' : ''; ?>>Thất bại</option>
                    <option value="cancelled" <?php echo $filter === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                </select>
                
                <input type="text" name="search" placeholder="Tìm theo mã GD hoặc STK..." value="<?php echo htmlspecialchars($search); ?>">
                
                <button type="submit">Lọc</button>
                
                <input type="hidden" name="admin" value="true">
            </form>
        </div>

        <!-- Transactions Table -->
        <div class="transactions-table">
            <table class="table">
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
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td>
                                <div class="transaction-id"><?php echo htmlspecialchars($transaction['transaction_id']); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($transaction['account_number']); ?></td>
                            <td>
                                <div class="amount"><?php echo number_format($transaction['amount'], 0, ',', '.'); ?> VND</div>
                            </td>
                            <td>
                                <span class="status <?php echo $transaction['status']; ?>">
                                    <?php 
                                    $statusText = [
                                        'pending' => 'Đang chờ',
                                        'completed' => 'Thành công', 
                                        'failed' => 'Thất bại',
                                        'cancelled' => 'Đã hủy'
                                    ];
                                    echo $statusText[$transaction['status']] ?? $transaction['status'];
                                    ?>
                                </span>
                            </td>
                            <td>
                                <div><?php echo date('d/m/Y H:i', strtotime($transaction['created_at'])); ?></div>
                                <?php if ($transaction['updated_at'] !== $transaction['created_at']): ?>
                                    <small style="color: #6c757d;">Cập nhật: <?php echo date('d/m/Y H:i', strtotime($transaction['updated_at'])); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small><?php echo htmlspecialchars($transaction['notes'] ?? ''); ?></small>
                            </td>
                            <td>
                                <div class="actions">
                                    <?php if ($transaction['status'] === 'pending'): ?>
                                        <button class="btn btn-success" onclick="completeTransaction('<?php echo $transaction['transaction_id']; ?>')">
                                            Hoàn thành
                                        </button>
                                        <button class="btn btn-danger" onclick="updateStatus('<?php echo $transaction['transaction_id']; ?>', 'failed')">
                                            Thất bại
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button class="btn btn-primary" onclick="viewDetails('<?php echo $transaction['transaction_id']; ?>')">
                                        Chi tiết
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>&admin=true">« Trước</a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>&admin=true"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>&admin=true">Sau »</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
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
            if (confirm('Bạn có chắc muốn cập nhật trạng thái giao dịch này?')) {
                document.getElementById('statusTransactionId').value = transactionId;
                document.getElementById('newStatus').value = status;
                document.getElementById('statusForm').submit();
            }
        }

        function completeTransaction(transactionId) {
            if (confirm('Bạn có chắc muốn hoàn thành giao dịch này? Số dư sẽ được cộng vào tài khoản.')) {
                document.getElementById('completeTransactionId').value = transactionId;
                document.getElementById('completeForm').submit();
            }
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
                    <tr><th style="padding: 8px; border: 1px solid #ddd;">Mã giao dịch</th><td style="padding: 8px; border: 1px solid #ddd;">${transaction.transaction_id}</td></tr>
                    <tr><th style="padding: 8px; border: 1px solid #ddd;">Số tiền</th><td style="padding: 8px; border: 1px solid #ddd;">${new Intl.NumberFormat('vi-VN').format(transaction.amount)} VND</td></tr>
                    <tr><th style="padding: 8px; border: 1px solid #ddd;">Trạng thái</th><td style="padding: 8px; border: 1px solid #ddd;"><span class="status ${transaction.status}">${transaction.status}</span></td></tr>
                    <tr><th style="padding: 8px; border: 1px solid #ddd;">Tài khoản</th><td style="padding: 8px; border: 1px solid #ddd;">${transaction.account_number}</td></tr>
                    <tr><th style="padding: 8px; border: 1px solid #ddd;">Thời gian tạo</th><td style="padding: 8px; border: 1px solid #ddd;">${transaction.created_at}</td></tr>
                    <tr><th style="padding: 8px; border: 1px solid #ddd;">Cập nhật cuối</th><td style="padding: 8px; border: 1px solid #ddd;">${transaction.updated_at}</td></tr>
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
</body>
</html>
