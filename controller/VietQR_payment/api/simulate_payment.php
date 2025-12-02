<?php
/**
 * API giả lập thanh toán - FIXED VERSION
 */

// Clean output first
if (ob_get_level()) {
    ob_end_clean();
}

ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/config.php';

// Only allow in development mode
if (!defined('DEVELOPMENT_MODE') || !DEVELOPMENT_MODE) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Not available in production']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['transaction_id'])) {
        throw new Exception('Transaction ID is required');
    }
    
    $transactionId = trim($input['transaction_id']);
    $db = DatabaseManager::getInstance()->getDatabase();
    
    // Start transaction
    $db->getConnection()->begin_transaction();
    
    // Get transaction info with lock
    $stmt = $db->prepare("
        SELECT t.*, ta.id as account_id, ta.balance 
        FROM transactions t 
        JOIN transfer_accounts ta ON t.account_id = ta.id 
        WHERE t.transaction_id = ? AND t.status = 'pending'
        FOR UPDATE
    ");
    $stmt->bind_param("s", $transactionId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $transaction = $result->fetch_assoc();
    $stmt->close();
    
    if (!$transaction) {
        $db->getConnection()->rollback();
        throw new Exception("Transaction not found or already processed");
    }
    
    $amount = floatval($transaction['amount']);
    $oldBalance = floatval($transaction['balance']);
    
    // Update balance - KEY FIX HERE
    $stmt = $db->prepare("
        UPDATE transfer_accounts 
        SET balance = balance + ?, updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->bind_param("di", $amount, $transaction['account_id']);
    
    if (!$stmt->execute()) {
        $db->getConnection()->rollback();
        throw new Exception("Failed to update balance: " . $stmt->error);
    }
    $stmt->close();
    
    // Update transaction status
    $stmt = $db->prepare("
        UPDATE transactions 
        SET status = 'completed', 
            notes = CONCAT(COALESCE(notes, ''), ' - Simulated payment'),
            updated_at = NOW() 
        WHERE transaction_id = ?
    ");
    $stmt->bind_param("s", $transactionId);
    
    if (!$stmt->execute()) {
        $db->getConnection()->rollback();
        throw new Exception("Failed to update transaction: " . $stmt->error);
    }
    $stmt->close();
    
    // Get new balance
    $stmt = $db->prepare("SELECT balance FROM transfer_accounts WHERE id = ?");
    $stmt->bind_param("i", $transaction['account_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $newBalance = floatval($result->fetch_assoc()['balance']);
    $stmt->close();
    
    // Commit transaction
    $db->getConnection()->commit();
    
    error_log("Simulated payment completed: $transactionId - Amount: $amount - New Balance: $newBalance");
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment simulated successfully',
        'transaction_id' => $transactionId,
        'amount' => $amount,
        'old_balance' => $oldBalance,
        'new_balance' => $newBalance
    ]);
    
} catch (Exception $e) {
    if (isset($db) && $db->getConnection()->inTransaction) {
        $db->getConnection()->rollback();
    }
    
    error_log("Simulation error: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>