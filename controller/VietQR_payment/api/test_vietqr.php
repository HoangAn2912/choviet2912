<?php
/**
 * API test kết nối VietQR
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/config.php';
require_once '../classes/VietQRGenerator.php';

try {
    $vietQR = new VietQRGenerator();
    
    // Test connection
    $testResult = $vietQR->testConnection(50000);
    
    // Lấy thông tin chi tiết
    $details = $vietQR->getTransactionDetails(50000, 'Test transaction', 'TEST_' . time());
    
    // Lấy danh sách ngân hàng hỗ trợ
    $supportedBanks = $vietQR->getSupportedBanks();
    
    echo json_encode([
        'success' => true,
        'connection_test' => $testResult,
        'transaction_details' => $details,
        'supported_banks' => $supportedBanks,
        'current_config' => [
            'bank_code' => VIETQR_BANK_CODE,
            'account_number' => VIETQR_ACCOUNT_NUMBER,
            'account_name' => VIETQR_ACCOUNT_NAME
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
