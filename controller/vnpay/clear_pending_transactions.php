<?php
require_once 'model/mConnect.php';

try {
    // Xóa các giao dịch pending cũ (hơn 30 phút)
    $stmt = $pdo->prepare("DELETE FROM vnpay_transactions WHERE status = 'pending' AND created_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
    $result = $stmt->execute();
    
    $deleted_count = $stmt->rowCount();
    
    // Clear session
    unset($_SESSION['payment_success']);
    unset($_SESSION['payment_error']);
    unset($_SESSION['payment_amount']);
    unset($_SESSION['payment_txn']);
    
    echo "<h2>Đã reset hệ thống thanh toán</h2>";
    echo "<p>Đã xóa $deleted_count giao dịch pending cũ</p>";
    echo "<p>Đã clear session</p>";
    echo "<p><a href='nap_tien.php'>Quay lại trang nạp tiền</a></p>";
    
} catch (Exception $e) {
    echo "<h2>Lỗi reset hệ thống</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p><a href='nap_tien.php'>Quay lại trang nạp tiền</a></p>";
}
?>
