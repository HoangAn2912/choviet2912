<?php
/**
 * Trang lịch sử giao dịch cho user
 */

require_once '../config/config.php';
require_once '../classes/PaymentManager.php';

// Giả sử user_id = 1 (trong thực tế lấy từ session)
$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 1;

$paymentManager = new PaymentManager();
$account = $paymentManager->getAccountByUserId($userId);

if (!$account) {
    die('Không tìm thấy tài khoản');
}

// Lấy lịch sử giao dịch
$transactions = $paymentManager->getTransactionHistory($userId, 50);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Sử Giao Dịch</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8f9fa; }
        
        .header { background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); color: white; padding: 30px 0; }
        .header .container { max-width: 800px; margin: 0 auto; padding: 0 20px; text-align: center; }
        .header h1 { font-size: 2rem; margin-bottom: 10px; }
        
        .balance-card { background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; margin-top: 20px; }
        .balance-amount { font-size: 2.5rem; font-weight: bold; margin-bottom: 5px; }
        
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        
        .transaction-card { background: white; border-radius: 10px; padding: 20px; margin-bottom: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .transaction-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .transaction-id { font-family: monospace; color: #6c757d; font-size: 0.9rem; }
        .transaction-amount { font-size: 1.5rem; font-weight: bold; color: #28a745; }
        
        .transaction-details { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; }
        .detail-item { }
        .detail-label { font-size: 0.8rem; color: #6c757d; text-transform: uppercase; margin-bottom: 5px; }
        .detail-value { font-weight: 600; }
        
        .status { padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; }
        .status.pending { background: #fff3cd; color: #856404; }
        .status.completed { background: #d4edda; color: #155724; }
        .status.failed { background: #f8d7da; color: #721c24; }
        
        .empty-state { text-align: center; padding: 60px 20px; color: #6c757d; }
        .empty-state h3 { margin-bottom: 10px; }
        
        .back-btn { display: inline-block; background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-bottom: 20px; }
        .back-btn:hover { background: #0056b3; }
        
        @media (max-width: 768px) {
            .transaction-header { flex-direction: column; align-items: flex-start; gap: 10px; }
            .transaction-details { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>📋 Lịch Sử Giao Dịch</h1>
            <p>Tài khoản: <?php echo htmlspecialchars($account['account_number']); ?></p>
            
            <div class="balance-card">
                <div class="balance-amount"><?php echo number_format($account['balance'], 0, ',', '.'); ?> VND</div>
                <div>Số dư hiện tại</div>
            </div>
        </div>
    </div>

    <div class="container">
        <a href="../index.php" class="back-btn">← Quay lại nạp tiền</a>
        
        <?php if (empty($transactions)): ?>
            <div class="empty-state">
                <h3>Chưa có giao dịch nào</h3>
                <p>Bạn chưa thực hiện giao dịch nào. Hãy thử nạp tiền để bắt đầu!</p>
            </div>
        <?php else: ?>
            <?php foreach ($transactions as $transaction): ?>
                <div class="transaction-card">
                    <div class="transaction-header">
                        <div>
                            <div class="transaction-id"><?php echo htmlspecialchars($transaction['transaction_id']); ?></div>
                            <div class="transaction-amount">+<?php echo number_format($transaction['amount'], 0, ',', '.'); ?> VND</div>
                        </div>
                        <div>
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
                        </div>
                    </div>
                    
                    <div class="transaction-details">
                        <div class="detail-item">
                            <div class="detail-label">Thời gian</div>
                            <div class="detail-value"><?php echo date('d/m/Y H:i', strtotime($transaction['created_at'])); ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Phương thức</div>
                            <div class="detail-value">VietQR - <?php echo $transaction['bank_code']; ?></div>
                        </div>
                        
                        <?php if ($transaction['notes']): ?>
                            <div class="detail-item">
                                <div class="detail-label">Ghi chú</div>
                                <div class="detail-value"><?php echo htmlspecialchars($transaction['notes']); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($transaction['status'] === 'completed' && $transaction['updated_at'] !== $transaction['created_at']): ?>
                            <div class="detail-item">
                                <div class="detail-label">Hoàn thành</div>
                                <div class="detail-value"><?php echo date('d/m/Y H:i', strtotime($transaction['updated_at'])); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
