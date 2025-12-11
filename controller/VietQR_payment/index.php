<?php
require_once 'config/config.php';
require_once 'classes/PaymentManager.php';
require_once 'classes/VietQRGenerator.php';
require_once __DIR__ . '/../../controller/cTopUp.php';

// Giả sử user_id = 1 cho demo (trong thực tế sẽ lấy từ session)
$userId = $_SESSION['user_id'];

$paymentManager = new PaymentManager();
$account = $paymentManager->getAccountByUserId($userId);

// Lấy lịch sử chuyển khoản
$cTopUp = new cTopUp();
$lichSuChuyenKhoan = $cTopUp->getLichSu($userId);

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
        }
        
        /* Page background - Lớp ngoài cùng (xám nhẹ) */
        .page-background {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            min-height: calc(100vh - 180px);
            padding: 0 2rem 2rem 2rem;
        }
        
        /* Content wrapper - Khối trắng bên trong */
        .content-wrapper {
            background: #ffffff;
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 6px 30px rgba(0, 0, 0, 0.12);
        }
        
        .container {
            max-width: 100%;
            margin: 0;
            background: transparent;
            border-radius: 0;
            box-shadow: none;
            overflow: visible;
        }
        
        @media (max-width: 768px) {
            .page-background {
                padding: 0 1rem 1rem 1rem;
            }
            
            .content-wrapper {
                padding: 1.5rem;
                border-radius: 12px;
            }
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
        
        .two-column-layout {
            display: grid;
            grid-template-columns: 1fr 1fr; /* 50% : 50% */
            gap: 30px;
            margin-top: 20px;
            align-items: stretch; /* Đảm bảo 2 cột có chiều cao bằng nhau */
        }
        
        .column {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        /* Phần lịch sử có thể cuộn dọc */
        .column:last-child {
            max-height: 600px;
            overflow: hidden;
        }
        
        .history-content-wrapper {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            min-height: 0;
        }
        
        .column h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5rem;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        
        @media (max-width: 1024px) {
            .two-column-layout {
                grid-template-columns: 1fr;
            }
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
    <!-- Page Background Start -->
    <div class="page-background">
        <!-- Content Wrapper Start -->
        <div class="content-wrapper">
            <div class="container">
        <div class="header">
            <h1>💳 Nạp Tiền VietQR</h1>
            <p>Nạp tiền nhanh chóng và an toàn</p>
            
            <div class="balance-info">
                <h3>Số dư hiện tại:</h3>
                <div class="balance"><?php echo number_format($account['balance'], 0, ',', '.'); ?> VND</div>
                <small>Tài khoản: <?php echo $account['account_number']; ?></small>
            </div>
        </div>
        
        <div class="content">
            <div id="alert-container"></div>
            
            <div class="two-column-layout">
                <!-- Cột 1: Nạp tiền -->
                <div class="column">
                    <h2>💳 Nạp tiền</h2>
                    
                    <div class="amount-selection">
                        <h3 style="color: #333; margin-bottom: 15px; font-size: 1.2rem;">Chọn số tiền cần nạp:</h3>
                        
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
                
                <!-- Cột 2: Lịch sử chuyển khoản -->
                <div class="column">
                    <h2>📋 Lịch sử chuyển khoản</h2>
                    
                    <?php if (empty($lichSuChuyenKhoan)): ?>
                        <div style="text-align: center; padding: 40px; background: white; border-radius: 10px; color: #666;">
                            <p style="font-size: 1.1rem;">Chưa có lịch sử chuyển khoản nào</p>
                        </div>
                    <?php else: ?>
                        <div class="history-content-wrapper">
                            <div class="history-table-wrapper">
                                <table class="history-table" style="width: 100%; min-width: 800px; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                                <thead>
                                    <tr style="background: linear-gradient(135deg, #ffe139ff 0%, #ffaa0cff 100%); color: black;">
                                        <th style="padding: 12px; text-align: left; font-weight: 600; font-size: 0.9rem;">STT</th>
                                        <th style="padding: 12px; text-align: left; font-weight: 600; font-size: 0.9rem;">Mã giao dịch</th>
                                        <th style="padding: 12px; text-align: left; font-weight: 600; font-size: 0.9rem;">Số tiền</th>
                                        <th style="padding: 12px; text-align: left; font-weight: 600; font-size: 0.9rem;">Ghi chú</th>
                                        <th style="padding: 12px; text-align: left; font-weight: 600; font-size: 0.9rem;">Trạng thái</th>
                                        <th style="padding: 12px; text-align: left; font-weight: 600; font-size: 0.9rem;">Ngày tạo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $stt = 1;
                                    foreach ($lichSuChuyenKhoan as $item): 
                                        // Xác định màu và text cho trạng thái
                                        $statusClass = '';
                                        $statusText = '';
                                        switch($item['status']) {
                                            case 'completed':
                                                $statusClass = 'status-approved';
                                                $statusText = 'Thành công';
                                                break;
                                            case 'pending':
                                                $statusClass = 'status-pending';
                                                $statusText = 'Đang chờ';
                                                break;
                                            case 'failed':
                                                $statusClass = 'status-rejected';
                                                $statusText = 'Thất bại';
                                                break;
                                            case 'cancelled':
                                                $statusClass = 'status-rejected';
                                                $statusText = 'Đã hủy';
                                                break;
                                            default:
                                                $statusClass = 'status-pending';
                                                $statusText = $item['status'];
                                        }
                                    ?>
                                    <tr style="border-bottom: 1px solid #e9ecef; transition: background 0.3s ease;" 
                                        onmouseover="this.style.background='#f8f9fa'" 
                                        onmouseout="this.style.background='white'">
                                        <td style="padding: 12px; font-size: 0.9rem;"><?php echo $stt++; ?></td>
                                        <td style="padding: 12px; font-weight: 600; color: #2196F3; font-size: 0.9rem;" title="<?php echo htmlspecialchars($item['transaction_id']); ?>">
                                            <?php echo htmlspecialchars($item['transaction_id']); ?>
                                        </td>
                                        <td style="padding: 12px; font-weight: 600; color: #28a745; font-size: 0.9rem;" title="<?php echo number_format($item['amount'], 0, ',', '.'); ?> VND">
                                            <?php echo number_format($item['amount'], 0, ',', '.'); ?> VND
                                        </td>
                                        <td style="padding: 12px; color: #666; font-size: 0.9rem;" title="<?php echo !empty($item['notes']) ? htmlspecialchars($item['notes']) : 'Nạp tiền'; ?>">
                                            <?php echo !empty($item['notes']) ? htmlspecialchars($item['notes']) : 'Nạp tiền'; ?>
                                        </td>
                                        <td style="padding: 12px;">
                                            <span class="status-badge <?php echo $statusClass; ?>" 
                                                  style="padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 500; display: inline-block;">
                                                <?php echo $statusText; ?>
                                            </span>
                                        </td>
                                        <td style="padding: 12px; color: #666; font-size: 0.9rem;">
                                            <?php 
                                            $date = new DateTime($item['created_at']);
                                            echo $date->format('d/m/Y H:i');
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal để xem ảnh lớn -->
    <div id="imageModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.9); cursor: pointer;"
         onclick="closeImageModal()">
        <span style="position: absolute; top: 20px; right: 35px; color: #f1f1f1; font-size: 40px; font-weight: bold;">&times;</span>
        <img id="modalImage" style="margin: auto; display: block; max-width: 90%; max-height: 90%; margin-top: 5%; border-radius: 10px;">
    </div>

    <style>
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        /* Style cho bảng lịch sử với thanh cuộn */
        .history-table-wrapper {
            overflow-x: auto;
            overflow-y: visible;
            max-width: 100%;
            position: relative;
            -webkit-overflow-scrolling: touch; /* Smooth scrolling trên iOS */
        }
        
        /* Custom scrollbar cho bảng lịch sử */
        .history-table-wrapper::-webkit-scrollbar {
            width: 10px; /* Chiều rộng cho scrollbar dọc */
            height: 10px; /* Chiều cao cho scrollbar ngang */
        }
        
        .history-table-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .history-table-wrapper::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #ffe139ff 0%, #ffaa0cff 100%);
            border-radius: 10px;
        }
        
        .history-table-wrapper::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #ffd700 0%, #ff9500 100%);
        }
        
        /* Firefox scrollbar */
        .history-table-wrapper {
            scrollbar-width: thin;
            scrollbar-color: #ffaa0cff #f1f1f1;
        }
        
        /* Text ellipsis cho các cột dài */
        .history-table td {
            max-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        /* Cột mã giao dịch */
        .history-table td:nth-child(2) {
            max-width: 150px;
        }
        
        /* Cột số tiền */
        .history-table td:nth-child(3) {
            max-width: 120px;
        }
        
        /* Cột ghi chú */
        .history-table td:nth-child(4) {
            max-width: 150px;
        }
        
        /* Cột ngày tạo - không cần ellipsis */
        .history-table td:nth-child(6) {
            max-width: none;
            white-space: normal;
        }
        
        .history-table {
            width: 100%;
            min-width: 800px; /* Đảm bảo bảng có chiều rộng tối thiểu để kích hoạt scroll */
        }
        
        @media (max-width: 768px) {
            .history-table {
                min-width: 700px;
                font-size: 0.9rem;
            }
            .history-table th, 
            .history-table td {
                padding: 10px !important;
            }
        }
        
        @media (max-width: 576px) {
            .history-table {
                min-width: 600px;
            }
        }
    </style>

    <script>
        function showImageModal(imageSrc) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            modal.style.display = 'block';
            modalImg.src = imageSrc;
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }
    </script>

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

                    const response = await fetch("controller/VietQR_payment/api/create_transaction.php", {
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
            
            fetch('controller/VietQR_payment/api/check_status.php', {
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
            
            fetch('controller/VietQR_payment/api/simulate_payment.php', {
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
            </div>
        <!-- Content Wrapper End -->
    </div>
    <!-- Page Background End -->
</body>
</html>
