<?php
// Load Security class để sử dụng CSRF tokens
require_once __DIR__ . '/../helpers/Security.php';
require_once __DIR__ . '/../model/mLivestreamPackage.php';

// Khởi tạo model
$packageModel = new mLivestreamPackage();

// Lấy danh sách tất cả các gói
$packages = $packageModel->getAllPackages();

// Lấy user_id từ session
$user_id = $_SESSION['user_id'] ?? 0;

// Kiểm tra loại tài khoản và gói hiện tại của user
$activeRegistration = null;
$account_type = 'ca_nhan'; // Mặc định là cá nhân
$is_business = false;

if ($user_id > 0) {
    // Lấy thông tin tài khoản
    require_once __DIR__ . '/../model/mConnect.php';
    $conn = new Connect();
    $db = $conn->connect();
    
    $user_sql = "SELECT account_type FROM users WHERE id = ?";
    $user_stmt = $db->prepare($user_sql);
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    
    if ($user_data = $user_result->fetch_assoc()) {
        $account_type = $user_data['account_type'] ?? 'ca_nhan';
        $is_business = ($account_type === 'doanh_nghiep');
    }
    
    $user_stmt->close();
    $db->close();
    
    // Lấy gói đang active (nếu có)
    $activeRegistration = $packageModel->getActiveRegistration($user_id);
}

// Include header
include_once __DIR__ . '/header.php';
?>

<style>
        /* Custom styles for Livestream Packages Page */
        .livestream-packages-page {
            padding: 20px 0 40px 0;
        }

        .livestream-packages-page .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 50px;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .header p {
            font-size: 1.2em;
            opacity: 0.9;
        }

        /* Active Package Alert */
        .active-package-alert {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            animation: slideIn 0.5s ease;
        }

        .active-package-alert h3 {
            margin-bottom: 10px;
            font-size: 1.3em;
        }

        .active-package-alert p {
            font-size: 1em;
            opacity: 0.95;
        }

        /* Packages Grid */
        .packages-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-bottom: 40px;
        }

        .package-card {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-height: 600px;
        }

        .package-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }

        .package-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.25);
        }

        .package-card.vip {
            border: 3px solid #FFD700;
        }

        .package-card.vip::before {
            height: 8px;
            background: linear-gradient(90deg, #FFD700 0%, #FFA500 100%);
        }

        .package-badge {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85em;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .package-card.vip .package-badge {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #333;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        /* Màu riêng cho từng gói */
        .package-card:nth-child(1) .package-badge {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        
        .package-card:nth-child(2) .package-badge {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .package-name {
            font-size: 1.8em;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .package-description {
            color: #666;
            font-size: 0.95em;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .package-price {
            font-size: 2.5em;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }

        .package-card.vip .package-price {
            color: #FFA500;
        }

        .package-duration {
            color: #888;
            font-size: 0.9em;
            margin-bottom: 30px;
        }

        .package-features {
            text-align: left;
            margin-bottom: 30px;
            flex-grow: 1;
        }
        
        .package-buttons {
            margin-top: auto;
        }

        .package-features li {
            list-style: none;
            padding: 8px 0;
            color: #555;
            font-size: 0.95em;
        }

        .package-features li::before {
            content: "✓ ";
            color: #11998e;
            font-weight: bold;
            margin-right: 8px;
        }

        .btn-purchase {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 10px;
        }

        .btn-wallet {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .btn-wallet:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 20px rgba(17, 153, 142, 0.4);
        }

        .btn-vnpay {
            background: linear-gradient(135deg, #0070BA 0%, #1546A0 100%);
            color: white;
        }

        .btn-vnpay:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 20px rgba(0, 112, 186, 0.4);
        }

        .btn-disabled {
            background: #ccc;
            cursor: not-allowed;
            color: #666;
        }

        .btn-disabled:hover {
            transform: none;
            box-shadow: none;
        }

        /* Info Section */
        .info-section {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }

        .info-section h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.8em;
        }

        .info-section p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 15px;
        }

        .info-section ul {
            padding-left: 20px;
            margin-bottom: 20px;
        }

        .info-section li {
            color: #666;
            line-height: 1.8;
            margin-bottom: 10px;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .back-btn {
            display: inline-block;
            background: white;
            color: #667eea;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            margin-bottom: 30px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .back-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }

        @media (max-width: 1024px) {
            .packages-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .packages-grid {
                grid-template-columns: 1fr;
            }
            
            .header h1 {
                font-size: 2em;
            }
            
            .package-price {
                font-size: 2em;
            }
            
            .package-card {
                min-height: auto;
            }
            
            .package-card.vip {
                transform: scale(1);
            }
        }
    </style>

<div class="page-background">
    <div class="content-wrapper">
        <div class="container-fluid p-0">
<div class="livestream-packages-page">
    <div class="container">
        
        <div class="header">
            <?php if ($is_business): ?>
                <h1>🎥 Gia Hạn Gói Livestream</h1>
                <p style="color: #000">Chọn gói để gia hạn hoặc nâng cấp gói livestream của bạn</p>
            <?php else: ?>
                <h1>🎥 Đăng Ký Gói Livestream Doanh Nghiệp</h1>
                <p>Nâng cấp lên tài khoản doanh nghiệp để bắt đầu livestream bán hàng</p>
            <?php endif; ?>
        </div>

        <?php if ($user_id > 0 && !$is_business): ?>
        <!-- Thông báo cho tài khoản cá nhân -->
        <div class="active-package-alert" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <h3>ℹ️ Tài Khoản Cá Nhân</h3>
            <p>Bạn đang sử dụng tài khoản <strong>Cá Nhân</strong>. Để sử dụng tính năng livestream, vui lòng:</p>
            <ul style="text-align: left; margin: 10px 0; padding-left: 30px;">
                <li>Đăng ký gói livestream bên dưới</li>
                <li>Hệ thống sẽ <strong>tự động nâng cấp</strong> tài khoản của bạn lên <strong>Doanh Nghiệp</strong></li>
                <li>Sau đó bạn có thể livestream bán hàng không giới hạn theo gói đã chọn</li>
            </ul>
            <p style="margin-top: 15px;">💡 <strong>Lưu ý:</strong> Việc nâng cấp lên doanh nghiệp là <strong>MIỄN PHÍ</strong>, bạn chỉ cần thanh toán phí gói livestream.</p>
        </div>
        <?php endif; ?>

        <?php if ($activeRegistration): ?>
        <div class="active-package-alert">
            <h3>✅ Bạn đang sử dụng gói: <?= htmlspecialchars($activeRegistration['package_name']) ?></h3>
            <p><i class="fas fa-clock mr-1"></i>Hiệu lực đến: <strong><?= date('d/m/Y H:i', strtotime($activeRegistration['expiry_date'])) ?></strong></p>
            <p>Bạn có thể mua gói mới để gia hạn hoặc nâng cấp.</p>
        </div>
        <?php endif; ?>

        <div class="packages-grid">
            <?php foreach ($packages as $package): ?>
            <div class="package-card <?= $package['id'] == 3 ? 'vip' : '' ?>">
                <?php if ($package['id'] == 1): ?>
                    <div class="package-badge">⚡ Gói Thử Nghiệm</div>
                <?php elseif ($package['id'] == 2): ?>
                    <div class="package-badge">🔥 Phổ Biến</div>
                <?php else: ?>
                    <div class="package-badge">👑 KHÔNG GIỚI HẠN</div>
                <?php endif; ?>
                
                <h3 class="package-name"><?= htmlspecialchars($package['package_name']) ?></h3>
                <p class="package-description"><?= htmlspecialchars($package['description']) ?></p>
                
                <div class="package-price"><?= number_format($package['price']) ?>đ</div>
                <div class="package-duration">Thời hạn: <?= $package['duration_days'] ?> ngày</div>
                
                <ul class="package-features">
                    <li>Livestream bán hàng chuyên nghiệp</li>
                    <li>Chat tương tác real-time</li>
                    <li>Giỏ hàng & đặt hàng ngay trong live</li>
                    <li>Thống kê doanh thu chi tiết</li>
                    <?php if ($package['id'] == 3): ?>
                        <li><strong><i class="fas fa-gift mr-1"></i>Không giới hạn số lần livestream</strong></li>
                        <li><strong><i class="fas fa-gift mr-1"></i>Không giới hạn thời lượng</strong></li>
                    <?php endif; ?>
                </ul>
                
                <div class="package-buttons">
                    <?php if ($user_id > 0): ?>
                        <?php if ($is_business): ?>
                            <!-- Tài khoản doanh nghiệp: Gia hạn gói -->
                            <form method="POST" action="index.php?action=purchase-livestream-package-wallet" style="margin-bottom: 10px;">
                                <?= Security::csrfField() ?>
                                <input type="hidden" name="package_id" value="<?= $package['id'] ?>">
                                <button type="submit" class="btn-purchase btn-wallet">
                                    <i class="fas fa-wallet mr-2"></i>Gia Hạn bằng Ví
                                </button>
                            </form>
                            
                            <form method="POST" action="index.php?action=purchase-livestream-package-vnpay">
                                <?= Security::csrfField() ?>
                                <input type="hidden" name="package_id" value="<?= $package['id'] ?>">
                                <button type="submit" class="btn-purchase btn-vnpay">
                                    <i class="fas fa-university mr-2"></i>Gia Hạn qua VNPay
                                </button>
                            </form>
                        <?php else: ?>
                            <!-- Tài khoản cá nhân: Đăng ký gói -->
                            <form method="POST" action="index.php?action=purchase-livestream-package-wallet" style="margin-bottom: 10px;">
                                <?= Security::csrfField() ?>
                                <input type="hidden" name="package_id" value="<?= $package['id'] ?>">
                                <button type="submit" class="btn-purchase btn-wallet">
                                    <i class="fas fa-wallet mr-2"></i>Đăng Ký bằng Ví
                                </button>
                            </form>
                            
                            <form method="POST" action="index.php?action=purchase-livestream-package-vnpay">
                                <?= Security::csrfField() ?>
                                <input type="hidden" name="package_id" value="<?= $package['id'] ?>">
                                <button type="submit" class="btn-purchase btn-vnpay">
                                    <i class="fas fa-university mr-2"></i>Đăng Ký qua VNPay
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="btn-purchase btn-disabled" onclick="alert('Vui lòng đăng nhập để đăng ký gói!')">
                            🔒 Đăng nhập để đăng ký
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="info-section">
            <h2><i class="fas fa-info-circle mr-2"></i>Thông tin quan trọng</h2>
            <p><strong>✅ Ai được sử dụng tính năng livestream?</strong></p>
            <p>Chỉ tài khoản <strong>Doanh Nghiệp</strong> mới được phép mua gói và livestream bán hàng.</p>
            
            <p><strong>🔄 Chính sách gia hạn & nâng cấp:</strong></p>
            <ul>
                <li>Khi mua gói mới, gói cũ sẽ tự động bị hủy</li>
                <li>Thời hạn gói mới tính từ thời điểm thanh toán thành công</li>
                <li>Bạn có thể xem lịch sử mua gói trong trang cá nhân</li>
            </ul>
            
            <p><strong>💰 Phương thức thanh toán:</strong></p>
            <ul>
                <li><strong>Ví nội bộ:</strong> Thanh toán ngay lập tức bằng số dư trong ví</li>
                <li><strong>VNPay:</strong> Thanh toán qua cổng VNPay (ATM, Visa, Mastercard)</li>
            </ul>
            
            <p><strong>📞 Hỗ trợ:</strong></p>
            <p>Nếu gặp vấn đề, vui lòng liên hệ <strong>support@choviet29.com</strong> hoặc hotline <strong>1900 xxxx</strong></p>
        </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/footer.php'; ?>

