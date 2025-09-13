<?php
// include_once("controller/cTopUp.php");
// $userId = $_SESSION['user_id'] ?? 0;

// $cTopUp = new cTopUp();
// $cTopUp->xuLyNopTien($userId); // Xử lý nếu có POST

// $lichsu = $cTopUp->getLichSu($userId); // Lấy lịch sử cho view
?>
<!-- ...phần HTML giữ nguyên, chỉ dùng $lichsu để hiển thị bảng... -->

<?php

include_once 'controller/vnpay/connection.php';
// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$message = '';
$error = '';

if (isset($_GET['success']) && isset($_SESSION['payment_success'])) {
    $amount = $_SESSION['payment_amount'];
    $txn_ref = $_SESSION['payment_txn'];
    
    // Kiểm tra số dư đã được cộng thực sự
    try {
        $stmt = $pdo->prepare("SELECT balance FROM transfer_accounts WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($account && $account['balance'] >= $amount) {
            $message = "✅ Nạp tiền thành công! Số tiền " . number_format($amount) . " VND đã được cộng vào tài khoản. Mã GD: " . $txn_ref;
        } else {
            $error = "Có lỗi xảy ra trong quá trình cộng tiền. Vui lòng liên hệ hỗ trợ.";
        }
    } catch(PDOException $e) {
        $error = "Lỗi kiểm tra số dư: " . $e->getMessage();
    }
    
    // Xóa session thông báo
    unset($_SESSION['payment_success']);
    unset($_SESSION['payment_amount']);
    unset($_SESSION['payment_txn']);
}

if (isset($_GET['error']) && isset($_SESSION['payment_error'])) {
    $error = $_SESSION['payment_error'];
    unset($_SESSION['payment_error']);
}

// Lấy thông tin số dư hiện tại
try {
    $stmt = $pdo->prepare("SELECT balance FROM transfer_accounts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    $current_balance = $account ? $account['balance'] : 0;
} catch(PDOException $e) {
    $error = "Lỗi truy vấn database: " . $e->getMessage();
}

// Xử lý form nạp tiền
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = (int)$_POST['amount'];
    
    if ($amount < 50000) {
        $error = "Số tiền nạp tối thiểu là 50,000 VND";
    } else {
        // Chuyển hướng đến trang tạo thanh toán VNPay
        header("Location: controller/vnpay/vnpay_create_payment.php?amount=" . $amount);
        exit();
    }
}
?>

<?php include_once("view/header.php"); ?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nạp tiền</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="number"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        #submitBtn.btn {
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: point
            er;
            font-size: 16px;
            width: 100%;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .error {
            color: #dc3545;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8d7da;
            border-radius: 5px;
        }
        .success {
            color: #155724;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #d4edda;
            border-radius: 5px;
        }
        .balance {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .quick-amounts {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        .quick-amount {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .quick-amount:hover {
            background-color: #e9ecef;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 2s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Nạp tiền vào tài khoản</h2>
        
        <div class="balance">
            <strong>Số dư hiện tại: <?php echo number_format($current_balance); ?> VND</strong>
        </div>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="success"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" id="depositForm">
            <div class="form-group">
                <label for="amount">Số tiền cần nạp (VND):</label>
                
                <div class="quick-amounts">
                    <div class="quick-amount" onclick="setAmount(50000)">50,000</div>
                    <div class="quick-amount" onclick="setAmount(100000)">100,000</div>
                    <div class="quick-amount" onclick="setAmount(200000)">200,000</div>
                    <div class="quick-amount" onclick="setAmount(500000)">500,000</div>
                    <div class="quick-amount" onclick="setAmount(1000000)">1,000,000</div>
                </div>
                
                <input type="number" id="amount" name="amount" min="50000" step="1000" 
                       placeholder="Nhập số tiền (tối thiểu 50,000 VND)" required>
            </div>
            
            <button type="submit" class="btn" id="submitBtn">Nạp tiền qua VNPay</button>
        </form>
        
        <!-- Thêm thông báo chờ thanh toán -->
        <div id="waitingMessage" style="display: none; margin-top: 20px; padding: 15px; background-color: #fff3cd; border-radius: 5px; border: 1px solid #ffeaa7;">
            <p><strong>🔄 Đang chờ xác nhận thanh toán...</strong></p>
            <p>Vui lòng hoàn thành thanh toán trên VNPay. Hệ thống sẽ tự động cập nhật khi priceo dịch thành công.</p>
            <div style="text-align: center; margin-top: 10px;">
                <div class="spinner"></div>
            </div>
        </div>

        <p style="margin-top: 20px; font-size: 14px; color: #666;">
            <strong>Lưu ý:</strong> Số tiền nạp tối thiểu là 50,000 VND. 
            Bạn sẽ được chuy���n đến trang thanh toán VNPay để hoàn tất priceo dịch.
        </p>
    </div>

    <script>
        function setAmount(amount) {
            document.getElementById('amount').value = amount;
        }
        
        // Format số tiền khi nhập
        document.getElementById('amount').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            e.target.value = value;
        });
        
        function checkBalance() {
            window.location.reload();
        }
        
        let currentTxnRef = null;
        let checkInterval = null;
        
        // Lưu mã priceo dịch khi submit form
        document.getElementById('depositForm').addEventListener('submit', function(e) {
            const amount = document.getElementById('amount').value;
            if (amount < 50000) {
                alert('Số tiền nạp tối thiểu là 50,000 VND');
                e.preventDefault();
                return;
            }
            
            // Tạo mã priceo dịch unique
            currentTxnRef = <?php echo $user_id; ?> + '_' + Date.now();
            localStorage.setItem('pending_txn_ref', currentTxnRef);
            
            // Hiển thị thông báo chờ
            setTimeout(() => {
                document.getElementById('waitingMessage').style.display = 'block';
                startCheckingTransaction();
            }, 2000);
        });
        
        // Kiểm tra priceo dịch pending khi load trang
        window.addEventListener('load', function() {
            const pendingTxn = localStorage.getItem('pending_txn_ref');
            if (pendingTxn) {
                currentTxnRef = pendingTxn;
                document.getElementById('waitingMessage').style.display = 'block';
                startCheckingTransaction();
            }
        });
        
        function startCheckingTransaction() {
            if (!currentTxnRef) return;
            
            checkInterval = setInterval(() => {
                fetch(`check_transaction_status.php?txn_ref=${currentTxnRef}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            clearInterval(checkInterval);
                            localStorage.removeItem('pending_txn_ref');
                            
                            // Hiển thị thông báo thành công
                            document.getElementById('waitingMessage').innerHTML = `
                                <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px;">
                                    <h4>✅ Nạp tiền thành công!</h4>
                                    <p>Số tiền: ${new Intl.NumberFormat('vi-VN').format(data.amount)} VND</p>
                                    <p>Số dư mới: ${new Intl.NumberFormat('vi-VN').format(data.balance)} VND</p>
                                    <p>Mã GD: ${data.txn_ref}</p>
                                </div>
                            `;
                            
                            // Reload trang sau 3 giây
                            setTimeout(() => {
                                window.location.reload();
                            }, 3000);
                        }
                    })
                    .catch(error => {
                        console.error('Lỗi kiểm tra priceo dịch:', error);
                    });
            }, 3000); // Kiểm tra mỗi 3 giây
        }
        
        if (window.location.search.includes('success=1') || window.location.search.includes('error=1')) {
            // Xóa URL parameters sau 3 giây
            setTimeout(function() {
                window.history.replaceState({}, document.title, window.location.pathname);
            }, 3000);
        }
    </script>
</body>
</html>


<?php include_once("view/footer.php"); ?>

<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<!-- Toastify JS -->
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<!-- Hàm showToast -->
<script src="js/toast.js"></script>
<!-- Gọi toast nếu có -->
<?php include_once("toastify.php"); ?>
