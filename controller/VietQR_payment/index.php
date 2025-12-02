<?php
require_once 'config/config.php';
require_once 'classes/PaymentManager.php';
require_once 'classes/VietQRGenerator.php';

// Giả sử user_id = 1 cho demo (trong thực tế sẽ lấy từ session)
$userId = $_SESSION['user_id'];

$paymentManager = new PaymentManager();
$account = $paymentManager->getAccountByUserId($userId);

// Nếu chưa có account thì tạo mới
if (!$account) {
    // Tạo account mới (trong thực tế sẽ có form đăng ký)
    $accountNumber = 'ACC' . str_pad($userId, 6, '0', STR_PAD_LEFT);
    $stmt = DatabaseManager::getInstance()->getDatabase()->prepare("
        INSERT INTO transfer_accounts (account_number, user_id, balance) VALUES (?, ?, 0.00)
    ");
    $stmt->bind_param("si", $accountNumber, $userId);
    $stmt->execute();
    $stmt->close();
    
    $account = $paymentManager->getAccountByUserId($userId);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nạp Tiền VietQR</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ffff;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #ffe139ff 0%, #ffaa0cff 100%);
            color: black;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .balance-info {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .balance-info h3 {
            font-size: 1.2rem;
            margin-bottom: 5px;
        }
        
        .balance {
            font-size: 2rem;
            font-weight: bold;
        }
        
        .nav-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            justify-content: center;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .content {
            padding: 40px;
        }
        
        .amount-selection {
            margin-bottom: 30px;
        }
        
        .amount-selection h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        
        .amount-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .amount-btn {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            padding: 15px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .amount-btn:hover {
            background: #e3f2fd;
            border-color: #2196F3;
            transform: translateY(-2px);
        }
        
        .amount-btn.selected {
            background: #2196F3;
            color: white;
            border-color: #1976D2;
        }
        
        .custom-amount {
            margin-top: 20px;
        }
        
        .custom-amount input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1.1rem;
            margin-bottom: 10px;
        }
        
        .generate-btn {
            background: linear-gradient(135deg, #FF6B6B 0%, #ee5a52 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .generate-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255,107,107,0.3);
        }
        
        .generate-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .qr-section {
            display: none;
            text-align: center;
            margin-top: 30px;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 15px;
        }
        
        .qr-code {
            max-width: 300px;
            margin: 20px auto;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .transaction-info {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            text-align: left;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .status-check {
            margin-top: 20px;
        }
        
        .status-btn {
            background: #17a2b8;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
        }
        
        .loading {
            display: none;
            text-align: center;
            margin-top: 20px;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        
        .alert-error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        
        .simulate-section {
            background: #fff3cd;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #ffeaa7;
            margin-top: 20px;
            display: none;
        }
        
        .simulate-btn {
            background: #ffc107;
            color: #212529;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }

        .debug-info {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            display: none;
        }
        #debugInfo {
            display: none !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💳 Nạp Tiền VietQR</h1>
            <p>Nạp tiền nhanh chóng và an toàn</p>
            
            <div class="balance-info">
                <h3>Số dư hiện tại:</h3>
                <div class="balance"><?php echo number_format($account['balance'], 0, ',', '.'); ?> VND</div>
                <small>Tài khoản: <?php echo $account['account_number']; ?></small>
            </div>
            
            <div class="nav-links">
                <a href="user/history.php?user_id=<?php echo $userId; ?>" class="nav-link">📋 Lịch sử giao dịch</a>
                <a href="admin/transactions.php?admin=true" class="nav-link">⚙️ Quản lý (Admin)</a>
            </div>
        </div>
        
        <div class="content">
            <div id="alert-container"></div>
            
            <div class="amount-selection">
                <h2>Chọn số tiền cần nạp:</h2>
                
                <div class="amount-grid">
                    <?php 
                    // Kiểm tra xem PAYMENT_AMOUNTS có được định nghĩa không
                    $amounts = defined('PAYMENT_AMOUNTS') ? PAYMENT_AMOUNTS : [
                        50000, 100000, 200000, 500000, 1000000, 2000000
                    ];
                    ?>
                    <?php foreach ($amounts as $amount): ?>
                        <div class="amount-btn" data-amount="<?php echo $amount; ?>">
                            <?php echo number_format($amount, 0, ',', '.'); ?> VND
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="custom-amount">
                    <input type="number" id="customAmount" placeholder="Hoặc nhập số tiền khác..." min="10000" max="500000000">
                    <small style="color: #666;">Tối thiểu 10,000 VND - Tối đa 500,000,000 VND</small>
                </div>
                
                <button class="generate-btn" id="generateQR" disabled>
                    Tạo Mã QR Thanh Toán
                </button>
            </div>
            
            <div class="debug-info" id="debugInfo">
                <h4>Debug Information:</h4>
                <div id="debugContent"></div>
            </div>
            
            <div class="qr-section" id="qrSection">
                <h3>Quét mã QR để thanh toán</h3>
                <img class="qr-code" id="qrImage" src="" alt="QR Code" style="display: none;">
                <div id="qr-loading" style="padding: 20px;">
                    <div class="spinner"></div>
                    <p>Đang tải QR Code...</p>
                </div>
                
                <div class="transaction-info" id="transactionInfo">
                    <!-- Thông tin giao dịch sẽ được load bằng JavaScript -->
                </div>
                
                <div class="status-check">
                    <button class="status-btn" onclick="checkStatus()">Kiểm tra trạng thái</button>
                    <button class="status-btn" onclick="location.reload()">Tạo giao dịch mới</button>
                </div>
                
                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <p>Đang kiểm tra thanh toán...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedAmount = null;
        let currentTransactionId = '';
        let statusInterval;

        // Debug function
        function showDebug(message) {
            const debugInfo = document.getElementById('debugInfo');
            const debugContent = document.getElementById('debugContent');
            debugContent.innerHTML += '<p>' + new Date().toLocaleTimeString() + ': ' + message + '</p>';
            debugInfo.style.display = 'block';
            console.log('DEBUG:', message);
        }

        // Error display function
        function showError(message) {
            const alertContainer = document.getElementById('alert-container');
            alertContainer.innerHTML = `<div class="alert alert-error">${message}</div>`;
            showDebug('ERROR: ' + message);
        }

        // Success display function
        function showSuccess(message) {
            const alertContainer = document.getElementById('alert-container');
            alertContainer.innerHTML = `<div class="alert alert-success">${message}</div>`;
            showDebug('SUCCESS: ' + message);
        }

        document.addEventListener('DOMContentLoaded', function() {
            showDebug('DOM loaded, initializing event listeners');
            
            // Amount button click handlers
            document.querySelectorAll(".amount-btn").forEach(btn => {
                btn.addEventListener("click", function() {
                    const amount = this.getAttribute("data-amount");
                    showDebug('Amount button clicked: ' + amount);
                    
                    // Bỏ highlight các nút khác
                    document.querySelectorAll(".amount-btn").forEach(b => b.classList.remove("selected"));
                    // Highlight nút được chọn
                    this.classList.add("selected");
                    
                    // Lưu số tiền được chọn
                    selectedAmount = parseInt(amount);
                    showDebug('Selected amount set to: ' + selectedAmount);

                    // Bỏ giá trị customAmount nếu có
                    document.getElementById("customAmount").value = "";

                    // Bật nút tạo QR
                    document.getElementById("generateQR").disabled = false;
                    showDebug('Generate QR button enabled');
                });
            });

            // Custom amount input handler
            document.getElementById("customAmount").addEventListener("input", function(e) {
                const value = parseInt(e.target.value);
                showDebug('Custom amount input: ' + value);
                
                selectedAmount = value;
                if (selectedAmount >= 10000 && selectedAmount <= 500000000) {
                    document.getElementById("generateQR").disabled = false;
                    // Bỏ highlight các nút preset
                    document.querySelectorAll(".amount-btn").forEach(b => b.classList.remove("selected"));
                    showDebug('Custom amount valid, button enabled');
                } else {
                    document.getElementById("generateQR").disabled = true;
                    showDebug('Custom amount invalid, button disabled');
                }
            });

            // Generate QR button handler
            document.getElementById("generateQR").addEventListener("click", async function() {
                showDebug('Generate QR button clicked, selectedAmount = ' + selectedAmount);
                
                if (!selectedAmount || selectedAmount < 10000) {
                    showError("Vui lòng chọn hoặc nhập số tiền hợp lệ (tối thiểu 10,000 VND)!");
                    return;
                }

                // Disable button
                const btn = this;
                btn.disabled = true;
                btn.textContent = "Đang tạo...";
                showDebug('Button disabled, making API call');

                try {
                    const requestData = {
                        user_id: <?php echo $userId; ?>,
                        amount: selectedAmount,
                        notes: "Nạp tiền",
                        template: "compact"
                    };
                    
                    showDebug('Sending request to API with data: ' + JSON.stringify(requestData));

                    const response = await fetch("api/create_transaction.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify(requestData)
                    });

                    showDebug('API response status: ' + response.status);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const data = await response.json();
                    showDebug('API response data: ' + JSON.stringify(data));

                    if (data.success) {
                        currentTransactionId = data.transaction_id;
                        showQRCode(data.qr_url, data);
                        startStatusCheck();
                        showSuccess('Mã QR đã được tạo thành công!');
                    } else {
                        showError("Lỗi tạo giao dịch: " + (data.error || 'Không xác định'));
                    }
                } catch (err) {
                    showError("Không thể kết nối API: " + err.message);
                } finally {
                    btn.disabled = false;
                    btn.textContent = "Tạo Mã QR Thanh Toán";
                }
            });
        });

        function showQRCode(qrUrl, transactionData) {
            showDebug('Showing QR code: ' + qrUrl);
            
            const qrImage = document.getElementById('qrImage');
            
            // Add error handling for QR image loading
            qrImage.onload = function() {
                showDebug('QR image loaded successfully');
                document.getElementById('qr-loading').style.display = 'none';
                qrImage.style.display = 'block';
            };
            
            qrImage.onerror = function() {
                showError('Không thể tải hình ảnh QR code. URL: ' + qrUrl);
                showDebug('QR image failed to load');
                document.getElementById('qr-loading').style.display = 'none';
                
                // Fallback: show QR URL as clickable link
                const fallbackDiv = document.createElement('div');
                fallbackDiv.innerHTML = `
                    <div style="background: #fff3cd; padding: 15px; border-radius: 10px; margin: 10px 0;">
                        <p><strong>❌ Không thể hiển thị QR code</strong></p>
                        <p>Vui lòng thực hiện chuyển khoản thủ công với thông tin sau:</p>
                        <div style="background: white; padding: 10px; border-radius: 5px; margin: 10px 0; text-align: left;">
                            <strong>Ngân hàng:</strong> Vietcombank<br>
                            <strong>Số TK:</strong> ${transactionData.payment_info?.bank_account || '1026479899'}<br>
                            <strong>Tên TK:</strong> ${transactionData.payment_info?.account_name || 'NGUYEN VAN A'}<br>
                            <strong>Số tiền:</strong> ${transactionData.formatted_amount || new Intl.NumberFormat('vi-VN').format(transactionData.amount) + ' VND'}<br>
                            <strong>Nội dung:</strong> ${transactionData.transaction_id}
                        </div>
                        <p><em>Hoặc click vào link sau để xem QR code:</em></p>
                        <a href="${qrUrl}" target="_blank" style="color: #007bff; text-decoration: underline; font-weight: bold;">
                            🔗 Mở QR Code trong tab mới
                        </a>
                    </div>
                `;
                qrImage.parentNode.insertBefore(fallbackDiv, qrImage);
            };
            
            qrImage.src = qrUrl;
            
            // Hiển thị thông tin giao dịch
            const transactionInfo = document.getElementById('transactionInfo');
            const accountNumber = transactionData.payment_info?.bank_account || 
                                transactionData.account_info?.account_number || 
                                '1026479899';
            const accountName = transactionData.payment_info?.account_name || 'NGUYEN VAN A';
            
            transactionInfo.innerHTML = `
                <div class="info-row">
                    <span><strong>Mã giao dịch:</strong></span>
                    <span>${transactionData.transaction_id}</span>
                </div>
                <div class="info-row">
                    <span><strong>Số tiền:</strong></span>
                    <span>${transactionData.formatted_amount || new Intl.NumberFormat('vi-VN').format(transactionData.amount) + ' VND'}</span>
                </div>
                <div class="info-row">
                    <span><strong>Ngân hàng:</strong></span>
                    <span>${transactionData.payment_info?.bank_name || 'Vietcombank'}</span>
                </div>
                <div class="info-row">
                    <span><strong>Số tài khoản:</strong></span>
                    <span>${accountNumber}</span>
                </div>
                <div class="info-row">
                    <span><strong>Chủ tài khoản:</strong></span>
                    <span>${accountName}</span>
                </div>
                <div class="info-row">
                    <span><strong>Nội dung:</strong></span>
                    <span>${transactionData.transaction_id}</span>
                </div>
                <div class="info-row">
                    <span><strong>Trạng thái:</strong></span>
                    <span id="status">Chờ thanh toán</span>
                </div>
            `;
            
            document.getElementById('qrSection').style.display = 'block';
            
            <?php if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE): ?>
            document.getElementById('simulateSection').style.display = 'block';
            <?php endif; ?>
        }
        
        function startStatusCheck() {
            showDebug('Starting status check interval');
            statusInterval = setInterval(checkTransactionStatus, 5000);
        }
        
        function stopStatusCheck() {
            if (statusInterval) {
                clearInterval(statusInterval);
                showDebug('Status check stopped');
            }
        }
        
        function checkTransactionStatus() {
            if (!currentTransactionId) return;
            
            showDebug('Checking transaction status for ID: ' + currentTransactionId);
            
            fetch('api/check_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    transaction_id: currentTransactionId
                })
            })
            .then(response => response.json())
            .then(data => {
                showDebug('Status check response: ' + JSON.stringify(data));
                const statusElement = document.getElementById('status');
                
                if (data.status === 'completed') {
                    statusElement.textContent = 'Thanh toán thành công!';
                    statusElement.style.color = '#28a745';
                    showSuccess('Thanh toán thành công! Số dư đã được cập nhật.');
                    stopStatusCheck();
                    
                    setTimeout(() => {
                        location.reload();
                    }, 3000);
                } else if (data.status === 'failed') {
                    statusElement.textContent = 'Thanh toán thất bại';
                    statusElement.style.color = '#dc3545';
                    stopStatusCheck();
                } else {
                    statusElement.textContent = 'Chờ thanh toán...';
                }
            })
            .catch(error => {
                showError('Error checking status: ' + error.message);
            });
        }

        // Manual status check button
        function checkStatus() {
            document.getElementById('loading').style.display = 'block';
            
            setTimeout(() => {
                checkTransactionStatus();
                document.getElementById('loading').style.display = 'none';
            }, 1000);
        }
        
        <?php if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE): ?>
        function simulatePayment() {
            if (!currentTransactionId) {
                showError('Không có giao dịch để giả lập!');
                return;
            }
            
            if (!confirm('Giả lập thanh toán thành công cho giao dịch này?')) {
                return;
            }
            
            showDebug('Simulating payment for transaction: ' + currentTransactionId);
            
            fetch('api/simulate_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    transaction_id: currentTransactionId,
                    amount: selectedAmount
                })
            })
            .then(response => response.json())
            .then(data => {
                showDebug('Simulate payment response: ' + JSON.stringify(data));
                if (data.success) {
                    showSuccess('Giả lập thanh toán thành công!');
                    checkTransactionStatus();
                } else {
                    showError('Lỗi giả lập: ' + data.error);
                }
            })
            .catch(error => {
                showError('Lỗi kết nối: ' + error.message);
            });
        }
        <?php endif; ?>
    </script>
</body>
</html>