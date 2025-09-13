<?php
require_once 'vnpay_config.php';
require_once 'connection.php';

// Kiểm tra đăng nhập

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$amount = (int  )$_GET['amount'];
if ($amount < 50000) {
    header('Location: nap_tien.php');
    exit();
}

$vnp_TxnRef = $user_id . '_' . (time() * 1000 + mt_rand(100, 999));
$vnp_OrderInfo = "Nap tien tai khoan - User ID: " . $user_id;
$vnp_OrderType = "billpayment";
$vnp_Amount = $amount * 100; // VNPay yêu cầu nhân với 100
$vnp_Locale = "vn";
$vnp_BankCode = "";
$vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

// Lưu thông tin giao dịch tạm thời vào database
try {
    $stmt = $pdo->prepare("INSERT INTO vnpay_transactions (txn_ref, user_id, amount, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
    $stmt->execute([$vnp_TxnRef, $user_id, $amount]);
} catch(PDOException $e) {
    die("Lỗi tạo giao dịch: " . $e->getMessage());
}

$_SESSION['current_txn_ref'] = $vnp_TxnRef;

$inputData = array(
    "vnp_Version" => "2.1.0",
    "vnp_TmnCode" => $vnp_TmnCode,
    "vnp_Amount" => $vnp_Amount,
    "vnp_Command" => "pay",
    "vnp_CreateDate" => date('YmdHis'),
    "vnp_CurrCode" => "VND",
    "vnp_IpAddr" => $vnp_IpAddr,
    "vnp_Locale" => $vnp_Locale,
    "vnp_OrderInfo" => $vnp_OrderInfo,
    "vnp_OrderType" => $vnp_OrderType,
    "vnp_ReturnUrl" => $vnp_Returnurl,
    "vnp_TxnRef" => $vnp_TxnRef,
);

if (isset($vnp_BankCode) && $vnp_BankCode != "") {
    $inputData['vnp_BankCode'] = $vnp_BankCode;
}

ksort($inputData);
$query = "";
$i = 0;
$hashdata = "";
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashdata .= urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
    $query .= urlencode($key) . "=" . urlencode($value) . '&';
}

$vnp_Url = $vnp_Url . "?" . $query;
if (isset($vnp_HashSecret)) {
    $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
    $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Đang chuyển hướng...</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            text-align: center; 
            padding: 50px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 400px;
            margin: 0 auto;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h3>Đang chuyển đến VNPay...</h3>
        <div class="spinner"></div>
        <p>Vui lòng đợi trong giây lát...</p>
    </div>
    
    <script>
        // Lưu mã giao dịch vào localStorage
        localStorage.setItem('pending_txn_ref', '<?php echo $vnp_TxnRef; ?>');
        
        // Chuyển hướng sau 2 giây
        setTimeout(function() {
            window.location.href = '<?php echo $vnp_Url; ?>';
        }, 2000);
    </script>
</body>
</html>
