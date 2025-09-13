<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


try {
    if (!file_exists('vnpay_config.php')) {
        throw new Exception("Config file not found");
    }
    if (!file_exists('../../model/mConnect.php')) {
        throw new Exception("Database connection file not found");
    }
    
    require_once 'vnpay_config.php';
    require_once 'connection.php';

    error_log("[v0] VNPay return started");

    if (!isset($_GET['vnp_SecureHash']) || !isset($_GET['vnp_TxnRef']) || !isset($_GET['vnp_Amount']) || !isset($_GET['vnp_ResponseCode'])) {
        throw new Exception("Missing required VNPay parameters");
    }

    $vnp_SecureHash = $_GET['vnp_SecureHash'];
    $inputData = array();
    foreach ($_GET as $key => $value) {
        if (substr($key, 0, 4) == "vnp_") {
            $inputData[$key] = $value;
        }
    }

    unset($inputData['vnp_SecureHash']);
    ksort($inputData);
    $i = 0;
    $hashData = "";
    foreach ($inputData as $key => $value) {
        if ($i == 1) {
            $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
    }

    $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

    $vnp_TxnRef = $_GET['vnp_TxnRef'];
    $vnp_Amount = $_GET['vnp_Amount'] / 100; // Chia cho 100 để về số tiền gốc
    $vnp_ResponseCode = $_GET['vnp_ResponseCode'];

    error_log("[v0] Transaction info - TxnRef: $vnp_TxnRef, Amount: $vnp_Amount, ResponseCode: $vnp_ResponseCode");
    error_log("[v0] Hash comparison - Generated: $secureHash, Received: $vnp_SecureHash");

    if ($secureHash == $vnp_SecureHash) {
        if ($vnp_ResponseCode == '00') {
            // Giao dịch thành công
            try {
                // Lấy thông tin user từ mã giao dịch
                $user_id = explode('_', $vnp_TxnRef)[0];
                error_log("[v0] Processing for user_id: $user_id");
                
                if (!isset($pdo)) {
                    throw new Exception("Database connection not available");
                }
                
                $stmt = $pdo->prepare("SELECT * FROM vnpay_transactions WHERE txn_ref = ?");
                $stmt->execute([$vnp_TxnRef]);
                $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($transaction) {
                    error_log("[v0] Found transaction with status: " . $transaction['status']);
                    
                    if ($transaction['status'] != 'success') {
                        // Bắt đầu transaction
                        $pdo->beginTransaction();
                        
                        // Cập nhật trạng thái giao dịch
                        $stmt = $pdo->prepare("UPDATE vnpay_transactions SET status = 'success', vnpay_response_code = ?, updated_at = NOW() WHERE txn_ref = ?");
                        $update_result = $stmt->execute([$vnp_ResponseCode, $vnp_TxnRef]);
                        error_log("[v0] Transaction status update result: " . ($update_result ? 'success' : 'failed'));
                        
                        $stmt = $pdo->prepare("SELECT * FROM transfer_accounts WHERE user_id = ?");
                        $stmt->execute([$user_id]);
                        $account = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($account) {
                            $old_balance = $account['balance'];
                            error_log("[v0] Current balance: $old_balance, Adding: $vnp_Amount");
                            
                            $stmt = $pdo->prepare("UPDATE transfer_accounts SET balance = balance + ? WHERE user_id = ?");
                            $result = $stmt->execute([$vnp_Amount, $user_id]);
                            error_log("[v0] Balance update execute result: " . ($result ? 'success' : 'failed'));
                            
                            if ($result) {
                                // Kiểm tra số dư sau khi cập nhật
                                $stmt = $pdo->prepare("SELECT balance FROM transfer_accounts WHERE user_id = ?");
                                $stmt->execute([$user_id]);
                                $new_balance = $stmt->fetchColumn();
                                error_log("[v0] Balance updated successfully. Old: $old_balance, New: $new_balance");
                            } else {
                                error_log("[v0] Failed to update balance - SQL error");
                                throw new Exception("Failed to update balance");
                            }
                        } else {
                            error_log("[v0] Creating new account with balance: $vnp_Amount");
                            $stmt = $pdo->prepare("INSERT INTO transfer_accounts (account_number, user_id, balance) VALUES (?, ?, ?)");
                            $result = $stmt->execute([$vnp_TxnRef, $user_id, $vnp_Amount]);
                            error_log("[v0] New account creation result: " . ($result ? 'success' : 'failed'));
                            
                            if (!$result) {
                                error_log("[v0] Failed to create new account - SQL error");
                                throw new Exception("Failed to create new account");
                            }
                        }
                        
                        $pdo->commit();
                        error_log("[v0] Transaction committed successfully");
                        
                        $_SESSION['payment_success'] = true;
                        $_SESSION['payment_amount'] = $vnp_Amount;
                        $_SESSION['payment_txn'] = $vnp_TxnRef;
                    } else {
                        error_log("[v0] Transaction already processed successfully");
                        $_SESSION['payment_success'] = true;
                        $_SESSION['payment_amount'] = $vnp_Amount;
                        $_SESSION['payment_txn'] = $vnp_TxnRef;
                    }
                    
                    header('Location: nap_tien.php?success=1');
                    exit();
                } else {
                    error_log("[v0] Transaction not found in database");
                    $_SESSION['payment_error'] = "Giao dịch không tồn tại trong hệ thống.";
                    header('Location: nap_tien.php?error=1');
                    exit();
                }

            } catch (Exception $e) {
                if (isset($pdo)) {
                    $pdo->rollBack();
                }
                error_log("[v0] Exception occurred: " . $e->getMessage());
                $_SESSION['payment_error'] = "Lỗi xử lý giao dịch: " . $e->getMessage();
                header('Location: nap_tien.php?error=1');
                exit();
            }
        } else {
            // Giao dịch thất bại
            error_log("[v0] Transaction failed with code: $vnp_ResponseCode");
            if (isset($pdo)) {
                $stmt = $pdo->prepare("UPDATE vnpay_transactions SET status = 'failed', vnpay_response_code = ?, updated_at = NOW() WHERE txn_ref = ?");
                $stmt->execute([$vnp_ResponseCode, $vnp_TxnRef]);
            }
            
            $_SESSION['payment_error'] = "Giao dịch thất bại. Mã lỗi: " . $vnp_ResponseCode;
            header('Location: nap_tien.php?error=1');
            exit();
        }
    } else {
        error_log("[v0] Invalid secure hash");
        $_SESSION['payment_error'] = "Chữ ký không hợp lệ";
        header('Location: nap_tien.php?error=1');
        exit();
    }

} catch (Exception $e) {
    error_log("[v0] Fatal error: " . $e->getMessage());
    echo "<h1>Lỗi xử lý thanh toán</h1>";
    echo "<p><strong>Chi tiết lỗi:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Dòng:</strong> " . $e->getLine() . "</p>";
    echo "<p><a href='nap_tien.php'>Quay lại trang nạp tiền</a></p>";
    
    // Also set session error for fallback
    $_SESSION['payment_error'] = "Lỗi hệ thống: " . $e->getMessage();
}
?>
