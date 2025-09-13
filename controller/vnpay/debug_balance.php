<?php
require_once 'model/mConnect.php';

if (!isset($_SESSION['user_id'])) {
    die("Chưa đăng nhập");
}

$user_id = $_SESSION['user_id'];

echo "<h3>Debug Balance for User ID: $user_id</h3>";

// Kiểm tra tài khoản
        $stmt = $pdo->prepare("SELECT * FROM transfer_accounts WHERE user_id = ?");
$stmt->execute([$user_id]);
$account = $stmt->fetch(PDO::FETCH_ASSOC);

if ($account) {
    echo "<p>Tài khoản tồn tại:</p>";
    echo "<pre>" . print_r($account, true) . "</pre>";
} else {
    echo "<p>Tài khoản chưa tồn tại</p>";
}

// Kiểm tra giao dịch VNPay
$stmt = $pdo->prepare("SELECT * FROM vnpay_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h4>5 giao dịch gần nhất:</h4>";
if ($transactions) {
    echo "<pre>" . print_r($transactions, true) . "</pre>";
} else {
    echo "<p>Chưa có giao dịch nào</p>";
}

// Test cộng tiền thủ công
if (isset($_GET['test_add'])) {
    $amount = 50000;
    try {
        if ($account) {
            $stmt = $pdo->prepare("UPDATE transfer_accounts SET balance = balance + ? WHERE user_id = ?");
            $result = $stmt->execute([$amount, $user_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO transfer_accounts (account_number, user_id, balance) VALUES (?, ?, ?)");
            $result = $stmt->execute(['TEST_' . time(), $user_id, $amount]);
        }
        
        if ($result) {
            echo "<p style='color: green;'>✅ Test cộng $amount VND thành công!</p>";
        } else {
            echo "<p style='color: red;'>❌ Test cộng tiền thất bại!</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
    }
}

echo "<br><a href='?test_add=1'>🧪 Test cộng 50,000 VND</a>";
echo "<br><a href='nap_tien.php'>← Quay lại trang nạp tiền</a>";
?>
