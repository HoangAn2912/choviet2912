<?php
/**
 * Trang test SieuThiCode API - Dành cho admin
 */

require_once '../config/config.php';
require_once '../classes/SieuThiCodeHandler.php';

// Kiểm tra quyền admin
$isAdmin = isset($_GET['admin']) && $_GET['admin'] === 'true';

if (!$isAdmin) {
    die('Access denied. Add ?admin=true to URL');
}

$sieuThiCode = new SieuThiCodeHandler();
$testResults = [];

// Test API
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'test_qr':
            $amount = floatval($_POST['amount'] ?? 50000);
            $transactionId = $_POST['transaction_id'] ?? 'TXN_' . time() . '_0000';
            
            $testResults['qr'] = $sieuThiCode->createPaymentQR($amount, $transactionId);
            break;
            
        case 'test_check':
            $transactionId = $_POST['transaction_id'] ?? 'TXN_' . time() . '_0000';
            
            $testResults['check'] = $sieuThiCode->checkTransactionStatus($transactionId);
            break;
    }
}

$config = $sieuThiCode->getConfig();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SieuThiCode API Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .test-form { background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .result { background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #ffe6e6; color: #d00; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .btn-success { background: #28a745; }
        .btn-warning { background: #ffc107; color: #000; }
        .info-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .info-table th, .info-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .info-table th { background: #f2f2f2; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .qr-preview { text-align: center; margin: 20px 0; }
        .qr-preview img { max-width: 300px; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔗 SieuThiCode API Test Panel</h1>
        
        <div class="result">
            <h2>Current Configuration</h2>
            <table class="info-table">
                <tr><th>Setting</th><th>Value</th></tr>
                <tr><td>API URL</td><td><?php echo $config['api_url']; ?></td></tr>
                <tr><td>Has Token</td><td><?php echo $config['has_token'] ? 'Yes' : 'No'; ?></td></tr>
                <tr><td>Bank Code</td><td><?php echo $config['bank_code']; ?></td></tr>
                <tr><td>Account Number</td><td><?php echo $config['account_number']; ?></td></tr>
            </table>
        </div>

        <div class="test-form">
            <h2>Test QR Generation</h2>
            
            <form method="POST" style="margin-bottom: 20px;">
                <p>
                    <label>Transaction ID:</label><br>
                    <input type="text" name="transaction_id" value="TXN_<?php echo time(); ?>_0001" style="width: 300px; padding: 5px;">
                </p>
                <p>
                    <label>Amount (VND):</label><br>
                    <input type="number" name="amount" value="50000" min="1000" style="width: 200px; padding: 5px;">
                </p>
                <button type="submit" name="action" value="test_qr" class="btn btn-success">Generate QR Code</button>
            </form>
            
            <form method="POST">
                <h3>Test Transaction Check</h3>
                <p>
                    <label>Transaction ID:</label><br>
                    <input type="text" name="transaction_id" value="TXN_<?php echo time(); ?>_0002" style="width: 300px; padding: 5px;">
                </p>
                <button type="submit" name="action" value="test_check" class="btn btn-warning">Check Transaction</button>
            </form>
        </div>

        <?php if (!empty($testResults)): ?>
            <div class="result">
                <h2>Test Results</h2>
                
                <?php foreach ($testResults as $type => $result): ?>
                    <h3><?php echo ucfirst($type); ?> Test</h3>
                    <div class="<?php echo $result['success'] ? 'result' : 'result error'; ?>">
                        <strong>Status:</strong> <?php echo $result['success'] ? 'SUCCESS' : 'FAILED'; ?><br>
                        
                        <?php if ($type === 'qr' && $result['success']): ?>
                            <strong>QR URL:</strong> <a href="<?php echo $result['qr_url']; ?>" target="_blank">View QR</a><br>
                            <strong>Amount:</strong> <?php echo number_format($result['amount'], 0, ',', '.'); ?> VND<br>
                            <strong>Content:</strong> <?php echo $result['content']; ?><br>
                            
                            <div class="qr-preview">
                                <h4>QR Code Preview:</h4>
                                <img src="<?php echo $result['qr_url']; ?>" alt="QR Code">
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($type === 'check'): ?>
                            <strong>Found:</strong> <?php echo $result['found'] ? 'Yes' : 'No'; ?><br>
                            <?php if ($result['found']): ?>
                                <strong>Bank Amount:</strong> <?php echo number_format($result['transaction']['amount'], 0, ',', '.'); ?> VND<br>
                                <strong>Description:</strong> <?php echo $result['transaction']['description']; ?><br>
                                <strong>Time:</strong> <?php echo $result['transaction']['time']; ?><br>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php if (isset($result['error'])): ?>
                            <strong>Error:</strong> <?php echo $result['error']; ?><br>
                        <?php endif; ?>
                    </div>
                    
                    <h4>Full Response:</h4>
                    <pre><?php echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="result">
            <h2>Setup Instructions</h2>
            <ol>
                <li><strong>Token đã cấu hình:</strong> <?php echo SIEUTHICODE_TOKEN; ?></li>
                <li><strong>API URL:</strong> <?php echo $config['api_url']; ?></li>
                <li><strong>Ngân hàng:</strong> Vietcombank (<?php echo $config['account_number']; ?>)</li>
                <li><strong>Test:</strong> Sử dụng form trên để test tạo QR và check giao dịch</li>
            </ol>
        </div>
    </div>
</body>
</html>
