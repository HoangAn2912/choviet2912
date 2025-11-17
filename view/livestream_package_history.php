<?php
// Load Security class
require_once __DIR__ . '/../helpers/Security.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Sử Mua Gói Livestream - ChoViet29</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
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

        .section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }

        .section h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5em;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 4em;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
        }

        .history-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #dee2e6;
        }

        .history-table td {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            color: #555;
        }

        .history-table tr:hover {
            background: #f8f9fa;
        }

        .badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
        }

        .badge-active {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .badge-expired {
            background: #e0e0e0;
            color: #666;
        }

        .badge-cancelled {
            background: #ff6b6b;
            color: white;
        }

        .badge-success {
            background: #38ef7d;
            color: white;
        }

        .badge-pending {
            background: #ffd93d;
            color: #333;
        }

        .badge-failed {
            background: #ff6b6b;
            color: white;
        }

        .price {
            font-weight: bold;
            color: #667eea;
            font-size: 1.1em;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.8em;
            }

            .history-table {
                font-size: 0.9em;
            }

            .history-table th,
            .history-table td {
                padding: 10px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php?livestream-packages" class="back-btn">← Quay lại trang gói</a>
        
        <div class="header">
            <h1>📋 Lịch Sử Mua Gói Livestream</h1>
        </div>

        <!-- Lịch sử đăng ký gói -->
        <div class="section">
            <h2>🎫 Lịch Sử Đăng Ký Gói</h2>
            
            <?php if (empty($registrations)): ?>
                <div class="empty-state">
                    <div style="font-size: 4em; margin-bottom: 20px;">📦</div>
                    <p style="font-size: 1.2em; color: #999;">Bạn chưa từng đăng ký gói nào</p>
                    <p style="margin-top: 10px;">
                        <a href="index.php?livestream-packages" style="color: #667eea; text-decoration: none; font-weight: bold;">
                            → Mua gói ngay
                        </a>
                    </p>
                </div>
            <?php else: ?>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Mã Đăng Ký</th>
                            <th>Gói</th>
                            <th>Giá</th>
                            <th>Ngày Đăng Ký</th>
                            <th>Hết Hạn</th>
                            <th>Trạng Thái</th>
                            <th>Thanh Toán</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrations as $reg): ?>
                        <tr>
                            <td>#<?= $reg['id'] ?></td>
                            <td><strong><?= htmlspecialchars($reg['package_name']) ?></strong></td>
                            <td class="price"><?= number_format($reg['price']) ?>đ</td>
                            <td><?= date('d/m/Y H:i', strtotime($reg['registration_date'])) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($reg['expiry_date'])) ?></td>
                            <td>
                                <?php if ($reg['status'] == 'active'): ?>
                                    <span class="badge badge-active">✓ Đang Hoạt Động</span>
                                <?php elseif ($reg['status'] == 'expired'): ?>
                                    <span class="badge badge-expired">⏰ Đã Hết Hạn</span>
                                <?php else: ?>
                                    <span class="badge badge-cancelled">✗ Đã Hủy</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($reg['payment_method'] == 'wallet'): ?>
                                    💳 Ví
                                <?php else: ?>
                                    🏦 VNPay
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Lịch sử thanh toán -->
        <div class="section">
            <h2>💰 Lịch Sử Thanh Toán</h2>
            
            <?php if (empty($payments)): ?>
                <div class="empty-state">
                    <div style="font-size: 4em; margin-bottom: 20px;">💳</div>
                    <p style="font-size: 1.2em; color: #999;">Chưa có giao dịch thanh toán nào</p>
                </div>
            <?php else: ?>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Mã GD</th>
                            <th>Gói</th>
                            <th>Số Tiền</th>
                            <th>Phương Thức</th>
                            <th>Trạng Thái</th>
                            <th>Ngày Thanh Toán</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td>#<?= $payment['id'] ?></td>
                            <td><?= htmlspecialchars($payment['package_name']) ?></td>
                            <td class="price"><?= number_format($payment['amount']) ?>đ</td>
                            <td>
                                <?php if ($payment['payment_method'] == 'wallet'): ?>
                                    💳 Ví Nội Bộ
                                <?php else: ?>
                                    🏦 VNPay
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($payment['payment_status'] == 'success'): ?>
                                    <span class="badge badge-success">✓ Thành Công</span>
                                <?php elseif ($payment['payment_status'] == 'pending'): ?>
                                    <span class="badge badge-pending">⏳ Đang Chờ</span>
                                <?php else: ?>
                                    <span class="badge badge-failed">✗ Thất Bại</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($payment['payment_date'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Thống kê tổng quan -->
        <?php if (!empty($registrations) || !empty($payments)): ?>
        <div class="section">
            <h2>📊 Thống Kê</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                    <div style="font-size: 2em; color: #667eea; font-weight: bold;">
                        <?= count($registrations) ?>
                    </div>
                    <div style="color: #666; margin-top: 5px;">Tổng Số Gói Đã Mua</div>
                </div>
                
                <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                    <div style="font-size: 2em; color: #11998e; font-weight: bold;">
                        <?php
                        $totalSpent = 0;
                        foreach ($payments as $p) {
                            if ($p['payment_status'] == 'success') {
                                $totalSpent += $p['amount'];
                            }
                        }
                        echo number_format($totalSpent);
                        ?>đ
                    </div>
                    <div style="color: #666; margin-top: 5px;">Tổng Chi Phí</div>
                </div>
                
                <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                    <div style="font-size: 2em; color: #38ef7d; font-weight: bold;">
                        <?php
                        $activeCount = 0;
                        foreach ($registrations as $r) {
                            if ($r['status'] == 'active') $activeCount++;
                        }
                        echo $activeCount;
                        ?>
                    </div>
                    <div style="color: #666; margin-top: 5px;">Gói Đang Hoạt Động</div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>




























