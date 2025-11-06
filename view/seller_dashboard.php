<?php
require_once __DIR__ . '/../helpers/Security.php';
require_once __DIR__ . '/../controller/cSellerDashboard.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: loginlogout/login.php");
    exit;
}

$controller = new cSellerDashboard();
$data = $controller->showDashboard();
$summary = $data['summary'];
$daily_revenue = $data['daily_revenue'];
$top_products = $data['top_products'];
$recent_orders = $data['recent_orders'];
$review_stats = $data['review_stats'];
$days = $data['days'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Người Bán</title>
    <?php echo Security::csrfMetaTag(); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 25px;
            border-radius: 15px;
            color: white;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card.revenue {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .stat-card.orders {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .stat-card.pending {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .stat-card.rating {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .stat-card h3 {
            font-size: 2.5em;
            margin: 0;
            font-weight: bold;
        }

        .stat-card p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 1.1em;
        }

        .chart-container {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.5em;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .product-image {
            width: 100%;
            aspect-ratio: 1;
            object-fit: cover;
        }

        .product-info {
            padding: 15px;
        }

        .product-title {
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 0.95em;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .orders-table {
            width: 100%;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .orders-table th {
            background: #667eea;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        .orders-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .orders-table tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cfe2ff; color: #084298; }
        .status-completed { background: #d1e7dd; color: #0f5132; }
        .status-cancelled { background: #f8d7da; color: #842029; }

        .period-selector {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .period-btn {
            padding: 10px 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .period-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .period-btn:hover {
            border-color: #667eea;
        }
    </style>
</head>

<?php include_once("view/header.php"); ?>

<div class="dashboard-container">
    <h2 class="mb-4"><i class="fas fa-chart-line mr-2"></i>Dashboard Người Bán</h2>

    <!-- Period Selector -->
    <div class="period-selector">
        <span><strong>Thời gian:</strong></span>
        <button class="period-btn <?= $days == 7 ? 'active' : '' ?>" onclick="changePeriod(7)">7 ngày</button>
        <button class="period-btn <?= $days == 30 ? 'active' : '' ?>" onclick="changePeriod(30)">30 ngày</button>
        <button class="period-btn <?= $days == 90 ? 'active' : '' ?>" onclick="changePeriod(90)">90 ngày</button>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card revenue">
            <h3><?= number_format($summary['total_revenue']) ?>đ</h3>
            <p><i class="fas fa-coins"></i> Tổng doanh thu</p>
        </div>
        <div class="stat-card orders">
            <h3><?= $summary['total_orders'] ?></h3>
            <p><i class="fas fa-shopping-cart"></i> Đơn hàng thành công</p>
        </div>
        <div class="stat-card pending">
            <h3><?= $summary['total_pending'] ?></h3>
            <p><i class="fas fa-clock"></i> Đơn chờ xử lý</p>
        </div>
        <div class="stat-card rating">
            <h3><?= number_format($review_stats['avg_rating'] ?? 0, 1) ?> ⭐</h3>
            <p><i class="fas fa-star"></i> Đánh giá trung bình (<?= $review_stats['total_reviews'] ?? 0 ?> reviews)</p>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="chart-container">
        <h3 class="section-title">Doanh Thu Theo Ngày</h3>
        <canvas id="revenueChart" height="80"></canvas>
    </div>

    <!-- Top Products -->
    <?php if (!empty($top_products)): ?>
    <div class="chart-container">
        <h3 class="section-title">Top Sản Phẩm Bán Chạy</h3>
        <div class="products-grid">
            <?php foreach ($top_products as $p): ?>
            <div class="product-card">
                <?php if (!empty($p['first_image'])): ?>
                <img src="img/<?= htmlspecialchars($p['first_image']) ?>" class="product-image" alt="">
                <?php endif; ?>
                <div class="product-info">
                    <div class="product-title"><?= htmlspecialchars($p['title']) ?></div>
                    <div style="color: #666; font-size: 0.9em;">Đã bán: <strong><?= $p['total_sold'] ?></strong></div>
                    <div style="color: #f5576c; font-weight: 600; margin-top: 5px;">
                        <?= number_format($p['total_revenue']) ?>đ
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Orders -->
    <?php if (!empty($recent_orders)): ?>
    <div class="chart-container">
        <h3 class="section-title">Đơn Hàng Gần Đây</h3>
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Mã ĐH</th>
                    <th>Người mua</th>
                    <th>Số tiền</th>
                    <th>Số sản phẩm</th>
                    <th>Trạng thái</th>
                    <th>Ngày đặt</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_orders as $order): ?>
                <tr>
                    <td>#<?= $order['id'] ?></td>
                    <td><?= htmlspecialchars($order['buyer_name']) ?></td>
                    <td><?= number_format($order['total_amount']) ?>đ</td>
                    <td><?= $order['items_count'] ?> SP</td>
                    <td>
                        <?php
                        $status_class = 'status-pending';
                        if (in_array($order['order_status'], ['completed', 'delivered'])) $status_class = 'status-completed';
                        elseif ($order['order_status'] == 'processing') $status_class = 'status-processing';
                        elseif ($order['order_status'] == 'cancelled') $status_class = 'status-cancelled';
                        ?>
                        <span class="status-badge <?= $status_class ?>"><?= htmlspecialchars($order['order_status']) ?></span>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($order['created_date'])) ?></td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="viewOrder(<?= $order['id'] ?>)">
                            <i class="fas fa-eye"></i> Xem
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script src="js/csrf-handler.js"></script>
<script>
// Chart Data
const dailyData = <?= json_encode($daily_revenue) ?>;

// Prepare chart data
const labels = dailyData.map(d => d.date).reverse();
const revenues = dailyData.map(d => parseFloat(d.revenue)).reverse();

// Create chart
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: revenues,
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString('vi-VN') + 'đ';
                    }
                }
            }
        }
    }
});

function changePeriod(days) {
    window.location.href = '?seller-dashboard&days=' + days;
}

function viewOrder(orderId) {
    // TODO: Implement order detail modal
    alert('Chi tiết đơn hàng #' + orderId);
}
</script>

<?php include_once("view/footer.php"); ?>











