
<?php
/**
 * API tạo giao dịch mới và generate QR code - Fixed version
 */

// Clean any previous output and set headers FIRST
if (ob_get_level()) {
    ob_end_clean();
}
ob_start();

// Disable error display for JSON API
ini_set('display_errors', 0);
error_reporting(0);

// Set JSON headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Method check
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    // Include files
    require_once '../config/config.php';
    require_once '../classes/PaymentManager.php';
    require_once '../classes/SieuThiCodeHandler.php';

    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON data');
    }
    
    $amount = isset($input['amount']) ? floatval($input['amount']) : 0;
    $userId = isset($input['user_id']) ? intval($input['user_id']) : 0;
    $notes = isset($input['notes']) ? trim($input['notes']) : '';
    
    // Validate data
    if ($amount < 10000 || $amount > 500000000) {
        throw new Exception('Số tiền không hợp lệ (10,000 - 500,000,000 VND)');
    }
    
    if ($userId <= 0) {
        throw new Exception('User ID không hợp lệ');
    }
    
    // Initialize classes
    $paymentManager = new PaymentManager();
    $sieuThiCode = new SieuThiCodeHandler();
    
    // Get account info
    $account = $paymentManager->getAccountByUserId($userId);
    if (!$account) {
        // Create account if doesn't exist
        $accountNumber = 'ACC' . str_pad($userId, 6, '0', STR_PAD_LEFT);
        $db = DatabaseManager::getInstance()->getDatabase();
        $stmt = $db->prepare("INSERT INTO transfer_accounts (account_number, user_id, balance) VALUES (?, ?, 0.00)");
        $stmt->bind_param("si", $accountNumber, $userId);
        
        if (!$stmt->execute()) {
            throw new Exception('Không thể tạo tài khoản');
        }
        $stmt->close();
        
        $account = $paymentManager->getAccountByUserId($userId);
    }
    
    // Create transaction
    $result = $paymentManager->createTransaction($userId, $account['id'], $amount, $notes);
    
    if (!$result['success']) {
        throw new Exception($result['error']);
    }
    
    // Generate QR code
    $qrResult = $sieuThiCode->createPaymentQR($amount, $result['transaction_id']);
    
    if (!$qrResult['success']) {
        throw new Exception('Lỗi tạo QR: ' . $qrResult['error']);
    }
    
    // Update QR URL in database
    $db = DatabaseManager::getInstance()->getDatabase();
    $stmt = $db->prepare("UPDATE transactions SET qr_code_url = ? WHERE transaction_id = ?");
    $stmt->bind_param("ss", $qrResult['qr_url'], $result['transaction_id']);
    $stmt->execute();
    $stmt->close();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'transaction_id' => $result['transaction_id'],
        'amount' => $amount,
        'formatted_amount' => number_format($amount, 0, ',', '.') . ' VND',
        'qr_url' => $qrResult['qr_url'],
        'account_info' => [
            'account_number' => $account['account_number'],
            'current_balance' => floatval($account['balance']),
            'formatted_balance' => number_format($account['balance'], 0, ',', '.') . ' VND'
        ],
        'payment_info' => [
            'bank_name' => 'Vietcombank',
            'bank_account' => $qrResult['account_number'],
            'account_name' => VIETQR_ACCOUNT_NAME,
            'content' => $qrResult['content']
        ]
    ]);
    
} catch (Exception $e) {
    // Log error
    error_log("Transaction creation error: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE ? $e->getTraceAsString() : null
    ]);
}

// Ensure clean output
if (ob_get_level()) {
    ob_end_flush();
}
?>