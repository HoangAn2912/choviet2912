<?php
require_once 'model/mConnect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit();
}

$user_id = $_SESSION['user_id'];
$txn_ref = $_GET['txn_ref'] ?? '';

if (empty($txn_ref)) {
    echo json_encode(['success' => false, 'message' => 'Thiếu mã giao dịch']);
    exit();
}

try {
    // Kiểm tra giao dịch trong database
    $stmt = $pdo->prepare("SELECT * FROM vnpay_transactions WHERE txn_ref = ? AND user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$txn_ref, $user_id]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($transaction && $transaction['status'] === 'success') {
        // Lấy số dư hiện tại
        $stmt = $pdo->prepare("SELECT balance FROM transfer_accounts WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        $current_balance = $account ? $account['balance'] : 0;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Giao dịch thành công!',
            'amount' => $transaction['amount'],
            'balance' => $current_balance,
            'txn_ref' => $txn_ref
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Giao dịch chưa hoàn thành']);
    }
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi database: ' . $e->getMessage()]);
}
?>
