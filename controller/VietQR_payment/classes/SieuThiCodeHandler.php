<?php
/**
 * Class xử lý API của SieuThiCode.net - FIXED VERSION
 * Khắc phục vấn đề format số tiền và tìm kiếm transaction ID
 */

require_once __DIR__ . '/../config/config.php';

class SieuThiCodeHandler {
    private $apiUrl = 'https://api.sieuthicode.net/historyapivcb/';
    private $token;
    private $db;
    
    public function __construct($token = '') {
        $this->token = $token ?: SIEUTHICODE_TOKEN;
        $this->db = DatabaseManager::getInstance()->getDatabase();
    }
    
    /**
     * Tạo QR code thanh toán
     */
    public function createPaymentQR($amount, $transactionId, $accountNumber = null) {
        try {
            $accountNumber = $accountNumber ?: VIETQR_ACCOUNT_NUMBER;
            
            // Tạo nội dung chuyển khoản
            $content = $transactionId;
            
            // Tạo URL VietQR
            $qrUrl = $this->generateVietQRUrl($accountNumber, $amount, $content);
            
            return [
                'success' => true,
                'qr_url' => $qrUrl,
                'amount' => $amount,
                'content' => $content,
                'account_number' => $accountNumber,
                'bank_code' => VIETQR_BANK_CODE
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Kiểm tra trạng thái giao dịch - FIXED để xử lý format số tiền từ API
     */
    public function checkTransactionStatus($transactionId) {
        try {
            // First check if transaction is already processed
            $stmt = $this->db->prepare("SELECT status, amount FROM transactions WHERE transaction_id = ?");
            $stmt->bind_param("s", $transactionId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if ($result && $result['status'] !== 'pending') {
                return [
                    'success' => true,
                    'found' => true,
                    'status' => $result['status'],
                    'already_processed' => true
                ];
            }
            
            // Get expected amount from database
            $expectedAmount = $result ? floatval($result['amount']) : 0;
            
            // Call API to check bank transactions
            $apiUrl = $this->apiUrl . $this->token;
            error_log("Calling SieuThiCode API: " . $apiUrl);
            
            $response = $this->callAPI('GET', $apiUrl);
            
            if (!$response['success']) {
                throw new Exception('API call failed: ' . $response['error']);
            }
            
            error_log("API Response: " . json_encode($response['data']));
            
            // Parse response
            $responseData = $response['data'];
            
            if (isset($responseData['transactions'])) {
                $transactions = $responseData['transactions'];
            } elseif (isset($responseData['data'])) {
                $transactions = $responseData['data'];
            } elseif (is_array($responseData)) {
                $transactions = $responseData;
            } else {
                throw new Exception('Invalid API response format');
            }
            
            // Find matching transaction
            foreach ($transactions as $txn) {
                // Get description from various possible fields
                $description = '';
                if (isset($txn['Description'])) {
                    $description = $txn['Description'];
                } elseif (isset($txn['Remark'])) {
                    $description = $txn['Remark'];
                } elseif (isset($txn['comment'])) {
                    $description = $txn['comment'];
                } elseif (isset($txn['description'])) {
                    $description = $txn['description'];
                } elseif (isset($txn['content'])) {
                    $description = $txn['content'];
                } elseif (isset($txn['memo'])) {
                    $description = $txn['memo'];
                }
                
                error_log("Checking transaction: " . json_encode($txn) . " - Description: " . $description);
                
                // Check if transaction ID exists in description (case insensitive)
                if (stripos($description, $transactionId) !== false) {
                    // Parse amount - FIXED to handle comma-separated format
                    $bankAmount = $this->parseAmountFromAPI($txn);
                    
                    error_log("Found matching transaction! Expected: $expectedAmount, Bank: $bankAmount");
                    
                    // Verify amount matches (allow 1 VND difference for rounding)
                    if ($expectedAmount > 0 && abs($bankAmount - $expectedAmount) > 1) {
                        error_log("Amount mismatch: Expected $expectedAmount, got $bankAmount");
                        continue; // Skip this transaction, keep looking
                    }
                    
                    // Get time
                    $time = '';
                    if (isset($txn['EffDate'])) {
                        $time = $txn['EffDate'];
                        if (isset($txn['PostingTime'])) {
                            $time .= ' ' . $txn['PostingTime'];
                        }
                    } elseif (isset($txn['TransactionDate'])) {
                        $time = $txn['TransactionDate'];
                        if (isset($txn['PCTime'])) {
                            $time .= ' ' . $txn['PCTime'];
                        }
                    } elseif (isset($txn['time'])) {
                        $time = $txn['time'];
                    } elseif (isset($txn['date'])) {
                        $time = $txn['date'];
                    } elseif (isset($txn['created_at'])) {
                        $time = $txn['created_at'];
                    }
                    
                    return [
                        'success' => true,
                        'found' => true,
                        'transaction' => [
                            'id' => $txn['Reference'] ?? $txn['SeqNo'] ?? $txn['id'] ?? uniqid(),
                            'amount' => $bankAmount,
                            'description' => $description,
                            'time' => $time,
                            'type' => ($txn['CD'] ?? $txn['type'] ?? 'in') === '+' ? 'in' : 'out',
                            'raw_data' => $txn
                        ]
                    ];
                }
            }
            
            return [
                'success' => true,
                'found' => false,
                'message' => 'Transaction not found in bank history',
                'searched_for' => $transactionId,
                'expected_amount' => $expectedAmount
            ];
            
        } catch (Exception $e) {
            error_log("Error in checkTransactionStatus: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Parse amount from API response - FIXED to handle comma format
     */
    private function parseAmountFromAPI($transaction) {
        $amount = 0;
        
        // Try different amount fields
        $possibleFields = ['Amount', 'amount', 'value', 'money', 'Value'];
        
        foreach ($possibleFields as $field) {
            if (isset($transaction[$field])) {
                $rawAmount = $transaction[$field];
                
                // Convert string amount with commas to float
                if (is_string($rawAmount)) {
                    // Remove commas and convert to float
                    $amount = floatval(str_replace(',', '', $rawAmount));
                } else {
                    $amount = floatval($rawAmount);
                }
                
                if ($amount > 0) {
                    break;
                }
            }
        }
        
        error_log("Parsed amount: $amount from raw data: " . json_encode($transaction));
        return $amount;
    }
    
    /**
     * Xử lý khi tìm thấy giao dịch thành công - UPDATED với better error handling
     */
    public function processFoundTransaction($transactionId, $bankTransaction) {
        try {
            error_log("Processing found transaction: " . $transactionId . " with bank data: " . json_encode($bankTransaction));
            
            $this->db->getConnection()->begin_transaction();
            
            // Get transaction info with lock
            $stmt = $this->db->prepare("
                SELECT t.*, ta.id as account_id, ta.balance 
                FROM transactions t 
                JOIN transfer_accounts ta ON t.account_id = ta.id 
                WHERE t.transaction_id = ? AND t.status = 'pending'
                FOR UPDATE
            ");
            $stmt->bind_param("s", $transactionId);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to get transaction: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $transaction = $result->fetch_assoc();
            $stmt->close();
            
            if (!$transaction) {
                $this->db->getConnection()->rollback();
                throw new Exception("Transaction not found or already processed: " . $transactionId);
            }
            
            $bankAmount = floatval($bankTransaction['amount']);
            $originalAmount = floatval($transaction['amount']);
            $oldBalance = floatval($transaction['balance']);
            
            error_log("Original amount: $originalAmount, Bank amount: $bankAmount, Old balance: $oldBalance");
            
            // Check amount match (allow 1 VND difference)
            if (abs($bankAmount - $originalAmount) > 1) {
                $this->db->getConnection()->rollback();
                throw new Exception("Amount mismatch. Expected: $originalAmount, Received: $bankAmount");
            }
            
            // Use original amount to ensure consistency
            $amountToAdd = $originalAmount;
            
            // Update balance
            $stmt = $this->db->prepare("
                UPDATE transfer_accounts 
                SET balance = balance + ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("di", $amountToAdd, $transaction['account_id']);
            
            if (!$stmt->execute()) {
                $this->db->getConnection()->rollback();
                throw new Exception("Failed to update balance: " . $stmt->error);
            }
            
            $affectedRows = $stmt->affected_rows;
            $stmt->close();
            
            if ($affectedRows === 0) {
                $this->db->getConnection()->rollback();
                throw new Exception("No rows affected when updating balance");
            }
            
            // Update transaction status
            $callbackJson = json_encode($bankTransaction);
            $stmt = $this->db->prepare("
                UPDATE transactions 
                SET status = 'completed', 
                    callback_data = ?, 
                    notes = CONCAT(COALESCE(notes, ''), ' - Bank TXN: ', ?),
                    updated_at = NOW() 
                WHERE transaction_id = ?
            ");
            $stmt->bind_param("sss", $callbackJson, $bankTransaction['id'], $transactionId);
            
            if (!$stmt->execute()) {
                $this->db->getConnection()->rollback();
                throw new Exception("Failed to update transaction: " . $stmt->error);
            }
            $stmt->close();
            
            // Get new balance
            $stmt = $this->db->prepare("SELECT balance FROM transfer_accounts WHERE id = ?");
            $stmt->bind_param("i", $transaction['account_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $newBalance = floatval($result->fetch_assoc()['balance']);
            $stmt->close();
            
            $this->db->getConnection()->commit();
            
            error_log("Transaction completed successfully: $transactionId - Amount: $amountToAdd VND - Old Balance: $oldBalance - New Balance: $newBalance");
            
            return [
                'success' => true,
                'message' => 'Transaction completed successfully',
                'transaction_id' => $transactionId,
                'amount' => $amountToAdd,
                'old_balance' => $oldBalance,
                'new_balance' => $newBalance,
                'bank_transaction_id' => $bankTransaction['id'] ?? 'unknown'
            ];
            
        } catch (Exception $e) {
            if (isset($this->db) && method_exists($this->db, 'getConnection') && $this->db->getConnection()->inTransaction ?? false) {
                $this->db->getConnection()->rollback();
            }
            error_log("Transaction processing failed: $transactionId - Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Tạo URL VietQR
     */
    private function generateVietQRUrl($accountNumber, $amount, $content) {
        $bankCode = VIETQR_BANK_CODE;
        $accountName = VIETQR_ACCOUNT_NAME;
        
        // Encode content để tránh lỗi URL
        $encodedContent = urlencode($content);
        
        // Template: compact2 (nhỏ gọn)
        $template = 'compact2';
        
        return "https://img.vietqr.io/image/{$bankCode}-{$accountNumber}-{$template}.png?amount={$amount}&addInfo={$encodedContent}&accountName=" . urlencode($accountName);
    }
    
    /**
     * Gọi API
     */
    private function callAPI($method, $url, $data = null) {
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'User-Agent: VietQR-Payment/1.0',
                'Accept: application/json',
                'Cache-Control: no-cache'
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        
        if ($method === 'POST' && $data) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array_merge(
                curl_getopt($curl, CURLOPT_HTTPHEADER),
                ['Content-Type: application/json']
            ));
        }
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        
        error_log("CURL Response - HTTP Code: $httpCode, Error: $error, Response: " . substr($response, 0, 500));
        
        curl_close($curl);
        
        if ($error) {
            return [
                'success' => false,
                'error' => 'CURL Error: ' . $error
            ];
        }
        
        if ($httpCode !== 200) {
            return [
                'success' => false,
                'error' => 'HTTP Error: ' . $httpCode . ' - Response: ' . $response
            ];
        }
        
        $decodedResponse = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'error' => 'JSON Decode Error: ' . json_last_error_msg() . ' - Raw response: ' . $response
            ];
        }
        
        return [
            'success' => true,
            'data' => $decodedResponse
        ];
    }
    
    /**
     * Test API connection
     */
    public function testAPI() {
        try {
            $apiUrl = $this->apiUrl . $this->token;
            error_log("Testing API: " . $apiUrl);
            
            $response = $this->callAPI('GET', $apiUrl);
            
            if ($response['success']) {
                return [
                    'success' => true,
                    'message' => 'API connection successful',
                    'data_sample' => array_slice($response['data']['transactions'] ?? $response['data'], 0, 2)
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response['error']
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get configuration
     */
    public function getConfig() {
        return [
            'api_url' => $this->apiUrl . $this->token,
            'has_token' => !empty($this->token),
            'bank_code' => VIETQR_BANK_CODE,
            'account_number' => VIETQR_ACCOUNT_NUMBER
        ];
    }
}
?>