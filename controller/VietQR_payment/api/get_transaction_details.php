<?php
/**
 * API lấy chi tiết giao dịch
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/config.php';

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
    
    // Lấy chi tiết giao dịch
    $db = DatabaseManager::getInstance()->getDatabase();
    $stmt = $db->prepare("
        SELECT t.*, ta.account_number, ta.balance 
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
    
    // Parse callback data nếu có
    if ($transaction['callback_data']) {
        $transaction['callback_data'] = json_decode($transaction['callback_data'], true);
    }
    
    echo json_encode([
        'success' => true,
        'transaction' => $transaction
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
