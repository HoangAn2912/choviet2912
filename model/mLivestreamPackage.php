<?php
/**
 * Model quản lý gói Livestream
 * Chức năng:
 * - Lấy danh sách gói
 * - Đăng ký gói
 * - Kiểm tra quyền livestream
 * - Quản lý hết hạn
 */

require_once __DIR__ . '/mConnect.php';

class mLivestreamPackage {
    private $conn;

    public function __construct() {
        $db = new Connect();
        $this->conn = $db->connect();
    }

    // =============================================
    // LẤY THÔNG TIN GÓI
    // =============================================

    /**
     * Lấy tất cả gói livestream đang hoạt động
     */
    public function getAllPackages() {
        $sql = "SELECT * FROM livestream_packages WHERE status = 1 ORDER BY price ASC";
        $result = $this->conn->query($sql);
        
        $packages = [];
        while ($row = $result->fetch_assoc()) {
            $packages[] = $row;
        }
        
        return $packages;
    }

    /**
     * Lấy thông tin 1 gói theo ID
     */
    public function getPackageById($package_id) {
        $sql = "SELECT * FROM livestream_packages WHERE id = ? AND status = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $package_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    // =============================================
    // ĐĂNG KÝ GÓI LIVESTREAM
    // =============================================

    /**
     * Đăng ký gói livestream (sau khi thanh toán thành công)
     * 
     * @param int $user_id ID người dùng
     * @param int $package_id ID gói
     * @param string $payment_method Phương thức thanh toán
     * @param string|null $vnpay_txn_ref Mã giao dịch VNPay (nếu có)
     * @return array ['success' => bool, 'registration_id' => int, 'message' => string]
     */
    public function registerPackage($user_id, $package_id, $payment_method = 'vnpay', $vnpay_txn_ref = null) {
        try {
            $this->conn->begin_transaction();

            // 1. Lấy thông tin gói
            $package = $this->getPackageById($package_id);
            if (!$package) {
                throw new Exception("Gói livestream không tồn tại");
            }

            // 2. Kiểm tra và tự động nâng cấp tài khoản lên doanh nghiệp (nếu cần)
            $user_sql = "SELECT account_type FROM users WHERE id = ?";
            $user_stmt = $this->conn->prepare($user_sql);
            $user_stmt->bind_param("i", $user_id);
            $user_stmt->execute();
            $user = $user_stmt->get_result()->fetch_assoc();

            if (!$user) {
                throw new Exception("Người dùng không tồn tại");
            }

            // Nếu là tài khoản cá nhân, tự động nâng cấp lên doanh nghiệp
            if ($user['account_type'] !== 'doanh_nghiep') {
                $upgrade_sql = "UPDATE users SET account_type = 'doanh_nghiep', updated_date = NOW() WHERE id = ?";
                $upgrade_stmt = $this->conn->prepare($upgrade_sql);
                $upgrade_stmt->bind_param("i", $user_id);
                
                if (!$upgrade_stmt->execute()) {
                    throw new Exception("Không thể nâng cấp tài khoản lên doanh nghiệp");
                }
                $upgrade_stmt->close();
            }

            // 3. Tính ngày hết hạn
            $duration_days = $package['duration_days'];
            $expiry_date = date('Y-m-d H:i:s', strtotime("+{$duration_days} days"));

            // 4. Hủy các gói đang active (nếu có) - chỉ cho phép 1 gói active cùng lúc
            $cancel_sql = "UPDATE livestream_registrations 
                          SET status = 'cancelled', updated_at = NOW() 
                          WHERE user_id = ? AND status = 'active'";
            $cancel_stmt = $this->conn->prepare($cancel_sql);
            $cancel_stmt->bind_param("i", $user_id);
            $cancel_stmt->execute();

            // 5. Tạo đăng ký mới
            $register_sql = "INSERT INTO livestream_registrations 
                           (user_id, package_id, registration_date, expiry_date, status, payment_method, vnpay_txn_ref)
                           VALUES (?, ?, NOW(), ?, 'active', ?, ?)";
            $register_stmt = $this->conn->prepare($register_sql);
            $register_stmt->bind_param("iisss", $user_id, $package_id, $expiry_date, $payment_method, $vnpay_txn_ref);
            $register_stmt->execute();
            
            $registration_id = $this->conn->insert_id;

            // 6. Lưu lịch sử thanh toán
            $payment_sql = "INSERT INTO livestream_payment_history 
                          (user_id, registration_id, package_id, amount, payment_method, payment_status, vnpay_txn_ref)
                          VALUES (?, ?, ?, ?, ?, 'success', ?)";
            $payment_stmt = $this->conn->prepare($payment_sql);
            $payment_stmt->bind_param("iiidss", 
                $user_id, $registration_id, $package_id, 
                $package['price'], $payment_method, $vnpay_txn_ref
            );
            $payment_stmt->execute();

            $this->conn->commit();

            return [
                'success' => true,
                'registration_id' => $registration_id,
                'expiry_date' => $expiry_date,
                'message' => "Đăng ký gói '{$package['package_name']}' thành công! Hiệu lực đến " . date('d/m/Y H:i', strtotime($expiry_date))
            ];

        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error registering livestream package: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // =============================================
    // KIỂM TRA QUYỀN LIVESTREAM
    // =============================================

    /**
     * Kiểm tra user có quyền livestream không
     * 
     * @param int $user_id
     * @return array ['has_permission' => bool, 'registration' => array|null, 'message' => string]
     */
    public function checkLivestreamPermission($user_id) {
        // 1. Kiểm tra account_type
        $user_sql = "SELECT account_type FROM users WHERE id = ?";
        $user_stmt = $this->conn->prepare($user_sql);
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user = $user_stmt->get_result()->fetch_assoc();

        if (!$user || $user['account_type'] !== 'doanh_nghiep') {
            return [
                'has_permission' => false,
                'registration' => null,
                'message' => 'Chỉ tài khoản doanh nghiệp mới được phép livestream'
            ];
        }

        // 2. Kiểm tra có gói đang active không
        $reg_sql = "SELECT r.*, p.package_name, p.price, p.duration_days
                   FROM livestream_registrations r
                   JOIN livestream_packages p ON r.package_id = p.id
                   WHERE r.user_id = ? 
                   AND r.status = 'active'
                   AND r.expiry_date > NOW()
                   ORDER BY r.expiry_date DESC
                   LIMIT 1";
        
        $reg_stmt = $this->conn->prepare($reg_sql);
        $reg_stmt->bind_param("i", $user_id);
        $reg_stmt->execute();
        $registration = $reg_stmt->get_result()->fetch_assoc();

        if (!$registration) {
            return [
                'has_permission' => false,
                'registration' => null,
                'message' => 'Bạn chưa đăng ký gói livestream hoặc gói đã hết hạn'
            ];
        }

        return [
            'has_permission' => true,
            'registration' => $registration,
            'message' => 'Gói livestream đang hoạt động đến ' . date('d/m/Y H:i', strtotime($registration['expiry_date']))
        ];
    }

    /**
     * Lấy thông tin gói đang active của user
     */
    public function getActiveRegistration($user_id) {
        $permission = $this->checkLivestreamPermission($user_id);
        return $permission['registration'] ?? null;
    }

    // =============================================
    // QUẢN LÝ HẾT HẠN
    // =============================================

    /**
     * Cập nhật trạng thái các gói đã hết hạn
     * Chạy bằng cron job hàng ngày
     */
    public function updateExpiredRegistrations() {
        $sql = "UPDATE livestream_registrations 
                SET status = 'expired', updated_at = NOW()
                WHERE status = 'active' 
                AND expiry_date <= NOW()";
        
        $result = $this->conn->query($sql);
        $affected = $this->conn->affected_rows;
        
        error_log("Updated {$affected} expired livestream registrations");
        
        return $affected;
    }

    /**
     * Lấy danh sách gói sắp hết hạn (trong 3 ngày)
     * Để gửi email nhắc nhở
     */
    public function getExpiringRegistrations($days_before = 3) {
        $sql = "SELECT r.*, u.username, u.email, p.package_name
                FROM livestream_registrations r
                JOIN users u ON r.user_id = u.id
                JOIN livestream_packages p ON r.package_id = p.id
                WHERE r.status = 'active'
                AND r.expiry_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ? DAY)
                ORDER BY r.expiry_date ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $days_before);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $registrations = [];
        while ($row = $result->fetch_assoc()) {
            $registrations[] = $row;
        }
        
        return $registrations;
    }

    // =============================================
    // LỊCH SỬ & THỐNG KÊ
    // =============================================

    /**
     * Lấy lịch sử đăng ký gói của user
     */
    public function getRegistrationHistory($user_id) {
        $sql = "SELECT r.*, p.package_name, p.price
                FROM livestream_registrations r
                JOIN livestream_packages p ON r.package_id = p.id
                WHERE r.user_id = ?
                ORDER BY r.registration_date DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        
        return $history;
    }

    /**
     * Lấy lịch sử thanh toán gói livestream
     */
    public function getPaymentHistory($user_id) {
        $sql = "SELECT ph.*, p.package_name
                FROM livestream_payment_history ph
                JOIN livestream_packages p ON ph.package_id = p.id
                WHERE ph.user_id = ?
                ORDER BY ph.payment_date DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        
        return $history;
    }

    /**
     * Hủy gói livestream
     */
    public function cancelRegistration($registration_id, $user_id) {
        $sql = "UPDATE livestream_registrations 
                SET status = 'cancelled', updated_at = NOW()
                WHERE id = ? AND user_id = ? AND status = 'active'";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $registration_id, $user_id);
        $stmt->execute();
        
        return $stmt->affected_rows > 0;
    }

    // =============================================
    // THANH TOÁN BẰNG VÍ
    // =============================================

    /**
     * Thanh toán gói livestream bằng ví nội bộ
     */
    public function payByWallet($user_id, $package_id) {
        try {
            $this->conn->begin_transaction();

            // 1. Lấy thông tin gói
            $package = $this->getPackageById($package_id);
            if (!$package) {
                throw new Exception("Gói không tồn tại");
            }

            $amount = $package['price'];

            // 2. Kiểm tra số dư
            $balance_sql = "SELECT balance FROM transfer_accounts WHERE user_id = ?";
            $balance_stmt = $this->conn->prepare($balance_sql);
            $balance_stmt->bind_param("i", $user_id);
            $balance_stmt->execute();
            $account = $balance_stmt->get_result()->fetch_assoc();

            if (!$account || $account['balance'] < $amount) {
                throw new Exception("Số dư không đủ. Cần " . number_format($amount) . " VNĐ");
            }

            // 3. Trừ tiền
            $update_balance_sql = "UPDATE transfer_accounts SET balance = balance - ? WHERE user_id = ?";
            $update_stmt = $this->conn->prepare($update_balance_sql);
            $update_stmt->bind_param("di", $amount, $user_id);
            $update_stmt->execute();

            // 4. Đăng ký gói
            $registration = $this->registerPackage($user_id, $package_id, 'wallet');

            if (!$registration['success']) {
                throw new Exception($registration['message']);
            }

            $this->conn->commit();

            return [
                'success' => true,
                'message' => "Thanh toán thành công! " . $registration['message']
            ];

        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error paying livestream package by wallet: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
?>

