<?php
// Load Security class
require_once __DIR__ . '/../helpers/Security.php';
// Dùng chung header của site
include_once __DIR__ . '/header.php';
?>
<style>
        .livestream-history-page {
            padding: 20px 0 40px 0;
        }

        .livestream-history-page .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .livestream-history-page .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .livestream-history-page .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
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
            .livestream-history-page .header h1 {
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

<div class="page-background">
    <div class="content-wrapper">
        <div class="container-fluid p-0">
            <div class="livestream-history-page">
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-receipt mr-2"></i>Lịch Sử Mua Gói Livestream</h1>
        </div>

        <!-- Lịch sử đăng ký gói -->
        <div class="section">
            <h2><i class="fas fa-ticket-alt mr-2"></i>Lịch Sử Đăng Ký Gói</h2>
            
            <?php if (empty($registrations)): ?>
                <div class="empty-state">
                    <div style="font-size: 4em; margin-bottom: 20px;">
                        <i class="fas fa-box-open" style="opacity: 0.3;"></i>
                    </div>
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
                                    <span class="badge badge-expired"><i class="fas fa-clock mr-1"></i>Đã Hết Hạn</span>
                                <?php else: ?>
                                    <span class="badge badge-cancelled">✗ Đã Hủy</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($reg['payment_method'] == 'wallet'): ?>
                                    <i class="fas fa-wallet mr-1"></i>Ví
                                <?php else: ?>
                                    <i class="fas fa-university mr-1"></i>VNPay
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Thống kê tổng quan -->
        <?php if (!empty($registrations) || !empty($payments)): ?>
        <div class="section">
            <h2><i class="fas fa-chart-bar mr-2"></i>Thống Kê</h2>
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
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/footer.php'; ?>









