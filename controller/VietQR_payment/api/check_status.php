<?php
/**
 * API kiểm tra trạng thái giao dịch - Fixed version
 */

// Clean output and set headers first
if (ob_get_level()) {
    ob_end_clean();
}

ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    require_once '../config/config.php';
    require_once '../classes/SieuThiCodeHandler.php';
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['transaction_id'])) {
        throw new Exception('Transaction ID is required');
    }
    
    $transactionId = trim($input['transaction_id']);
    
    // Get transaction info from database
    $db = DatabaseManager::getInstance()->getDatabase();
    $stmt = $db->prepare("
        SELECT t.*, ta.balance, ta.account_number 
        FROM transactions t 
        JOIN transfer_accounts ta ON t.account_id = ta.id 
        WHERE t.transaction_id = ?
    ");
    $stmt->bind_param("s", $transactionId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $transaction = $result->fetch_assoc();
    $stmt->close();
    
    if (!$transaction) {
        throw new Exception('Transaction not found');
    }
    
    // If transaction is still pending, check with SieuThiCode API
    if ($transaction['status'] === 'pending') {
        $sieuThiCode = new SieuThiCodeHandler();
        $checkResult = $sieuThiCode->checkTransactionStatus($transactionId);
        
        if ($checkResult['success'] && $checkResult['found']) {
            // Process the found transaction
            $processResult = $sieuThiCode->processFoundTransaction($transactionId, $checkResult['transaction']);
            
            if ($processResult['success']) {
                // Update transaction info in response
                $transaction['status'] = 'completed';
                $transaction['balance'] = $processResult['new_balance'];
                $transaction['updated_at'] = date('Y-m-d H:i:s');
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'transaction_id' => $transaction['transaction_id'],
        'status' => $transaction['status'],
        'amount' => floatval($transaction['amount']),
        'current_balance' => floatval($transaction['balance']),
        'created_at' => $transaction['created_at'],
        'updated_at' => $transaction['updated_at'],
        'formatted_amount' => number_format($transaction['amount'], 0, ',', '.') . ' VND',
        'formatted_balance' => number_format($transaction['balance'], 0, ',', '.') . ' VND'
    ]);
    
} catch (Exception $e) {
    error_log("Error in check_status.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>