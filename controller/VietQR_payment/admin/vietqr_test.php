<?php
/**
 * Trang test VietQR API - Dành cho admin
 */

require_once '../config/config.php';
require_once '../classes/VietQRGenerator.php';

// Kiểm tra quyền admin (đơn giản)
$isAdmin = isset($_GET['admin']) && $_GET['admin'] === 'true';

if (!$isAdmin) {
    die('Access denied. Add ?admin=true to URL');
}

$vietQR = new VietQRGenerator();
$testResults = [];

// Test các tính năng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $testAmount = isset($_POST['test_amount']) ? floatval($_POST['test_amount']) : 50000;
    $testDescription = isset($_POST['test_description']) ? $_POST['test_description'] : 'Test payment';
    
    // Test connection
    $testResults['connection'] = $vietQR->testConnection($testAmount);
    
    // Test generate QR
    try {
        $testResults['qr_basic'] = $vietQR->generateQRUrl($testAmount, $testDescription, 'TEST_' . time());
        $testResults['qr_template'] = $vietQR->generateQRUrlWithTemplate($testAmount, $testDescription, 'TEST_' . time());
        $testResults['qr_logo'] = $vietQR->generateQRUrlWithLogo($testAmount, $testDescription, 'TEST_' . time());
        $testResults['details'] = $vietQR->getTransactionDetails($testAmount, $testDescription, 'TEST_' . time());
    } catch (Exception $e) {
        $testResults['error'] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VietQR API Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .test-form { background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .result { background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #ffe6e6; color: #d00; }
        .qr-preview { text-align: center; margin: 20px 0; }
        .qr-preview img { max-width: 300px; border: 1px solid #ddd; border-radius: 5px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .info-table th, .info-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .info-table th { background: #f2f2f2; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 VietQR API Test Panel</h1>
        
        <div class="test-form">
            <h2>Test Configuration</h2>
            <form method="POST">
                <p>
                    <label>Test Amount (VND):</label><br>
                    <input type="number" name="test_amount" value="<?php echo isset($_POST['test_amount']) ? $_POST['test_amount'] : 50000; ?>" min="1000" max="500000000">
                </p>
                <p>
                    <label>Test Description:</label><br>
                    <input type="text" name="test_description" value="<?php echo isset($_POST['test_description']) ? htmlspecialchars($_POST['test_description']) : 'Test payment'; ?>">
                </p>
                <button type="submit" class="btn">Run Tests</button>
            </form>
        </div>

        <div class="result">
            <h2>Current Configuration</h2>
            <table class="info-table">
                <tr><th>Setting</th><th>Value</th></tr>
                <tr><td>Bank Code</td><td><?php echo VIETQR_BANK_CODE; ?></td></tr>
                <tr><td>Account Number</td><td><?php echo VIETQR_ACCOUNT_NUMBER; ?></td></tr>
                <tr><td>Account Name</td><td><?php echo VIETQR_ACCOUNT_NAME; ?></td></tr>
                <tr><td>API URL</td><td><?php echo $vietQR->getSupportedBanks()[VIETQR_BANK_CODE] ?? 'Unknown'; ?></td></tr>
            </table>
        </div>

        <?php if (!empty($testResults)): ?>
            <div class="result">
                <h2>Test Results</h2>
                
                <?php if (isset($testResults['connection'])): ?>
                    <h3>Connection Test</h3>
                    <div class="<?php echo $testResults['connection']['success'] ? 'result' : 'result error'; ?>">
                        <strong>Status:</strong> <?php echo $testResults['connection']['success'] ? 'SUCCESS' : 'FAILED'; ?><br>
                        <strong>Message:</strong> <?php echo $testResults['connection']['message']; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($testResults['qr_basic'])): ?>
                    <h3>QR Code Generation</h3>
                    
                    <h4>Basic QR Code</h4>
                    <div class="qr-preview">
                        <img src="<?php echo $testResults['qr_basic']; ?>" alt="Basic QR">
                        <p><small><?php echo $testResults['qr_basic']; ?></small></p>
                    </div>

                    <h4>Template QR Code</h4>
                    <div class="qr-preview">
                        <img src="<?php echo $testResults['qr_template']; ?>" alt="Template QR">
                        <p><small><?php echo $testResults['qr_template']; ?></small></p>
                    </div>

                    <h4>Logo QR Code</h4>
                    <div class="qr-preview">
                        <img src="<?php echo $testResults['qr_logo']; ?>" alt="Logo QR">
                        <p><small><?php echo $testResults['qr_logo']; ?></small></p>
                    </div>
                <?php endif; ?>

                <?php if (isset($testResults['details'])): ?>
                    <h3>Transaction Details</h3>
                    <table class="info-table">
                        <?php foreach ($testResults['details'] as $key => $value): ?>
                            <tr>
                                <th><?php echo ucfirst(str_replace('_', ' ', $key)); ?></th>
                                <td><?php echo is_array($value) ? json_encode($value) : htmlspecialchars($value); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>

                <?php if (isset($testResults['error'])): ?>
                    <div class="result error">
                        <strong>Error:</strong> <?php echo $testResults['error']; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="result">
            <h2>Supported Banks</h2>
            <table class="info-table">
                <tr><th>Bank Code</th><th>Bank Name</th></tr>
                <?php foreach ($vietQR->getSupportedBanks() as $code => $name): ?>
                    <tr>
                        <td><?php echo $code; ?></td>
                        <td><?php echo $name; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>
