<?php
require_once 'model/mConnect.php';

// API để kiểm tra số dư (dùng cho AJAX)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Chưa đăng nhập']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
            $stmt = $pdo->prepare("SELECT balance FROM transfer_accounts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $balance = $account ? $account['balance'] : 0;
    
    echo json_encode([
        'success' => true,
        'balance' => $balance,
        'formatted_balance' => number_format($balance)
    ]);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Lỗi truy vấn database']);
}
?>
