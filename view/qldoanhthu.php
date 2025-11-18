<?php
if ($_SESSION['role'] != 1 && $_SESSION['role'] !=5) {
    echo "<script>
        alert('Bạn không đủ thẩm quyền truy cập!');
        
    </script>";
    header("refresh: 0; url='/ad'");
    exit;
}
?>

<?php
include_once("controller/cQLdoanhthu.php");
$p = new cqldoanhthu();

// Set default date range to be unlimited if not specified
$startDate = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
$endDate = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;
$userId = isset($_GET['user_id']) ? $_GET['user_id'] : null;
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Pagination settings
$itemsPerPage = 10;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Get total count for pagination
$totalItems = $p->countTotalRevenue($startDate, $endDate, $userId);
$totalPages = ceil($totalItems / $itemsPerPage);

// Ensure current page is within valid range
if ($currentPage > $totalPages && $totalPages > 0) {
    $currentPage = $totalPages;
    $offset = ($currentPage - 1) * $itemsPerPage;
}

// Get paginated revenue data
$revenueData = $p->getRevenueData($offset, $itemsPerPage, $startDate, $endDate, $userId);

// Get summary statistics
$summary = $p->getRevenueSummary($startDate, $endDate);

// Get top users by revenue
$topUsers = $p->getTopUsersByRevenue(5, $startDate, $endDate);

// Get monthly revenue data for chart
$monthlyRevenue = $p->getMonthlyRevenue($year);

// Get all users for filter dropdown
$allUsers = $p->getAllUsers();

// Handle CSV export


// Function to generate pagination URL
function getPaginationUrl($page, $startDate, $endDate, $userId) {
    $url = "qldoanhthu?tbl&page={$page}";
    if ($startDate) $url .= "&start_date={$startDate}";
    if ($endDate) $url .= "&end_date={$endDate}";
    if ($userId) $url .= "&user_id={$userId}";
    return $url;
}

// Function to format currency
function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.') . ' đ';
}

// Prepare monthly data for chart
$chartLabels = [];
$chartData = [];
$monthNames = [
    1 => 'Tháng 1', 2 => 'Tháng 2', 3 => 'Tháng 3', 4 => 'Tháng 4', 
    5 => 'Tháng 5', 6 => 'Tháng 6', 7 => 'Tháng 7', 8 => 'Tháng 8',
    9 => 'Tháng 9', 10 => 'Tháng 10', 11 => 'Tháng 11', 12 => 'Tháng 12'
];

// Initialize all months with zero
for ($i = 1; $i <= 12; $i++) {
    $chartLabels[] = $monthNames[$i];
    $chartData[$i] = 0;
}

// Fill in actual data
foreach ($monthlyRevenue as $item) {
    $chartData[$item['month']] = $item['monthly_revenue'];
}

// Convert to JSON for chart
$chartLabelsJson = json_encode($chartLabels);
$chartDataJson = json_encode(array_values($chartData));
?>

<style>
    /* CSS riêng cho trang quản lý doanh thu */
    .doanhthu-container { 
        max-width: 1200px; 
        margin: 0 auto; 
        padding: 20px;
        margin-top: 40px;
    }
</style>

<div class="doanhthu-container">
<div class="col-12">
  <div class="card">
    <div class="card-body">
      <h3 class="card-title">Quản lý doanh thu</h3>
      <p class="card-description">
        Thống kê doanh thu từ phí đăng tin (11.000 đ/bài) và phí đăng ký gói livestream
      </p>
      
      <!-- Filter Card -->
      <div class="card filter-card">
        <div class="card-body">
          <h5 class="card-title mb-3" style="font-size: 1rem; font-weight: 600;">Bộ lọc</h5>
          <form class="filter-form" method="GET" action="">
            <input type="hidden" name="qldoanhthu" value="">
            
            <div class="form-group">
              <label style="font-size: 0.875rem; margin-bottom: 5px; font-weight: 500;">Khoảng thời gian</label>
              <div class="input-group" style="height: 38px;">
                <input type="date" class="form-control" name="start_date" value="<?php echo $startDate; ?>" placeholder="Từ ngày" style="font-size: 0.875rem;">
                <div class="input-group-append input-group-prepend">
                  <span class="input-group-text" style="font-size: 0.875rem; padding: 6px 12px;">đến</span>
                </div>
                <input type="date" class="form-control" name="end_date" value="<?php echo $endDate; ?>" placeholder="Đến ngày" style="font-size: 0.875rem;">
              </div>
            </div>
            
            <div class="form-group">
              <label style="font-size: 0.875rem; margin-bottom: 5px; font-weight: 500;">Người dùng</label>
              <select class="form-control select2" name="user_id" style="font-size: 0.875rem; height: 38px;">
                <option value="">Tất cả người dùng</option>
                <?php foreach ($allUsers as $user): ?>
                  <option value="<?php echo $user['id']; ?>" <?php echo ($userId == $user['id']) ? 'selected' : ''; ?>>
                    <?php echo $user['username']; ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <div class="btn-group">
              <button type="submit" class="btn btn-primary" style="font-size: 0.875rem; padding: 6px 16px; height: 38px;">
                <i class="mdi mdi-filter"></i> Lọc
              </button>
              <?php require_once __DIR__ . '/../helpers/url_helper.php'; ?>
              <a href="<?= getBasePath() ?>/ad/qldoanhthu" class="btn btn-outline-secondary" style="font-size: 0.875rem; padding: 6px 16px; height: 38px;">
                <i class="mdi mdi-refresh"></i> Đặt lại
              </a>
            </div>
          </form>
        </div>
      </div>
      
      <!-- Statistics Cards -->
      <div class="row mb-4 stats-container">
        <div class="col-md-3 mb-4 mb-md-0">
          <div class="card card-stats">
            <div class="card-body d-flex align-items-center">
              <div class="stats-icon bg-gradient-primary">
                <i class="mdi mdi-currency-usd"></i>
              </div>
              <div class="stats-info">
                <h3><?php echo formatCurrency($summary['total_revenue']); ?></h3>
                <p>Tổng doanh thu</p>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-md-3 mb-4 mb-md-0">
          <div class="card card-stats">
            <div class="card-body d-flex align-items-center">
              <div class="stats-icon bg-gradient-success">
                <i class="mdi mdi-file-document"></i>
              </div>
              <div class="stats-info">
                <h3><?php echo formatCurrency($summary['post_revenue'] ?? 0); ?></h3>
                <p>Doanh thu đăng tin</p>
                <small><?php echo $summary['total_posts']; ?> bài từ <?php echo $summary['total_users']; ?> người dùng</small>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-md-3 mb-4 mb-md-0">
          <div class="card card-stats">
            <div class="card-body d-flex align-items-center">
              <div class="stats-icon bg-gradient-warning">
                <i class="mdi mdi-video"></i>
              </div>
              <div class="stats-info">
                <h3><?php echo formatCurrency($summary['live_revenue'] ?? 0); ?></h3>
                <p>Doanh thu gói live</p>
                <small><?php echo $summary['total_packages'] ?? 0; ?> gói từ <?php echo $summary['total_live_users'] ?? 0; ?> người dùng</small>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-md-3">
          <div class="card card-stats">
            <div class="card-body d-flex align-items-center">
              <div class="stats-icon bg-gradient-info">
                <i class="mdi mdi-account-multiple"></i>
              </div>
              <div class="stats-info">
                <h3><?php echo $summary['total_unique_users'] ?? 0; ?></h3>
                <p>Tổng người dùng</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Revenue Chart -->
        <div class="col-lg-8 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Biểu đồ doanh thu theo tháng (<?php echo $year; ?>)</h4>
              <div class="d-flex justify-content-between mb-3">
                <div>
                  <form id="yearForm" method="GET" action="" class="d-flex align-items-center">
                    <input type="hidden" name="qldoanhthu" value="">
                    <?php if ($startDate): ?>
                      <input type="hidden" name="start_date" value="<?php echo $startDate; ?>">
                    <?php endif; ?>
                    <?php if ($endDate): ?>
                      <input type="hidden" name="end_date" value="<?php echo $endDate; ?>">
                    <?php endif; ?>
                    <?php if ($userId): ?>
                      <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                    <?php endif; ?>
                    
                    <select name="year" id="yearSelect" class="form-control form-control-sm" style="width: 100px;">
                      <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                        <option value="<?php echo $y; ?>" <?php echo ($year == $y) ? 'selected' : ''; ?>>
                          <?php echo $y; ?>
                        </option>
                      <?php endfor; ?>
                    </select>
                  </form>
                </div>
              </div>
              <div class="chart-container">
                <canvas id="revenueChart"></canvas>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Top Users -->
        <div class="col-lg-4 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Top người dùng có số tiền trong tài khoản nhiều nhất</h4>
              <div class="table-responsive top-users-wrapper">
                <table class="table table-hover top-users-table">
                  <thead>
                    <tr>
                      <th style="width: 60px;">Xếp hạng</th>
                      <th>Người dùng</th>
                      <th style="text-align: right; white-space: nowrap;">Số dư</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($topUsers)): ?>
                      <tr>
                        <td colspan="3" class="text-center">Không có dữ liệu</td>
                      </tr>
                    <?php else: ?>
                      <?php $rank = 1; ?>
                      <?php foreach ($topUsers as $user): ?>
                        <tr>
                          <td>
                            <div class="d-flex align-items-center justify-content-center">
                              <div class="user-rank rank-<?php echo min($rank, 3); ?>">
                                <?php echo $rank; ?>
                              </div>
                            </div>
                          </td>
                          <td>
                            <span class="font-weight-bold"><?php echo htmlspecialchars($user['username']); ?></span>
                          </td>
                          <td class="font-weight-bold text-success" style="text-align: right; white-space: nowrap;">
                            <?php echo formatCurrency($user['total_revenue']); ?>
                          </td>
                        </tr>
                        <?php $rank++; ?>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Revenue Table -->
      <div class="card mt-4">
        <div class="card-body">
          <h4 class="card-title">Chi tiết doanh thu</h4>
          <div class="table-responsive">
            <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Mô tả</th>
                  <th>Giá sản phẩm</th>
                  <th>Người dùng</th>
                  <th>Ngày</th>
                  <th>Loại</th>
                  <th>Phí doanh thu</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($revenueData)): ?>
                  <tr>
                    <td colspan="7" class="empty-table-message">
                      Không tìm thấy dữ liệu doanh thu nào<?php echo ($startDate && $endDate) ? ' trong khoảng thời gian đã chọn' : ''; ?>.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($revenueData as $item): ?>
                    <tr>
                      <td><?php echo $item['id']; ?></td>
                      <td><?php echo htmlspecialchars($item['title']); ?></td>
                      <td><?php echo $item['price'] > 0 ? formatCurrency($item['price']) : '-'; ?></td>
                      <td><?php echo htmlspecialchars($item['username']); ?></td>
                      <td><?php echo isset($item['revenue_date']) ? date('d/m/Y H:i', strtotime($item['revenue_date'])) : (isset($item['created_date']) ? date('d/m/Y H:i', strtotime($item['created_date'])) : 'N/A'); ?></td>
                      <td>
                        <span class="badge badge-<?php echo ($item['revenue_type'] ?? '') == 'livestream_package' ? 'warning' : 'success'; ?>">
                          <?php echo $item['revenue_type_name'] ?? 'Phí đăng tin'; ?>
                        </span>
                      </td>
                      <td class="font-weight-bold text-success"><?php echo formatCurrency($item['revenue_fee']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          
          <!-- Pagination -->
          <?php if ($totalPages > 1): ?>
            <div class="pagination-info">
              Hiển thị <?php echo count($revenueData); ?> trên tổng số <?php echo $totalItems; ?> bản ghi
            </div>
            <nav>
              <ul class="pagination">
                <!-- First page link -->
                <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                  <a class="page-link" href="<?php echo getPaginationUrl(1, $startDate, $endDate, $userId); ?>" aria-label="First">
                    <i class="mdi mdi-chevron-double-left"></i>
                  </a>
                </li>
                
                <!-- Previous page link -->
                <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                  <a class="page-link" href="<?php echo getPaginationUrl($currentPage - 1, $startDate, $endDate, $userId); ?>" aria-label="Previous">
                    <i class="mdi mdi-chevron-left"></i>
                  </a>
                </li>
                
                <!-- Page numbers -->
                <?php
                  // Determine range of page numbers to show
                  $startPage = max(1, $currentPage - 2);
                  $endPage = min($totalPages, $startPage + 4);
                  
                  // Adjust start page if we're near the end
                  if ($endPage - $startPage < 4) {
                    $startPage = max(1, $endPage - 4);
                  }
                  
                  // Generate page links
                  for ($i = $startPage; $i <= $endPage; $i++):
                ?>
                  <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo getPaginationUrl($i, $startDate, $endDate, $userId); ?>"><?php echo $i; ?></a>
                  </li>
                <?php endfor; ?>
                
                <!-- Next page link -->
                <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                  <a class="page-link" href="<?php echo getPaginationUrl($currentPage + 1, $startDate, $endDate, $userId); ?>" aria-label="Next">
                    <i class="mdi mdi-chevron-right"></i>
                  </a>
                </li>
                
                <!-- Last page link -->
                <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                  <a class="page-link" href="<?php echo getPaginationUrl($totalPages, $startDate, $endDate, $userId); ?>" aria-label="Last">
                    <i class="mdi mdi-chevron-double-right"></i>
                  </a>
                </li>
              </ul>
            </nav>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
</div>

<style>
  .card-stats {
    transition: all 0.3s ease;
  }
  
  .card-stats:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
  }
  
  .stats-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    margin-right: 15px;
  }
  
  .stats-icon i {
    font-size: 24px;
    color: white;
  }
  
  .stats-info h3 {
    font-size: 24px;
    margin-bottom: 5px;
  }
  
  .stats-info p {
    margin-bottom: 0;
    color: #6c757d;
  }
  
  .bg-gradient-primary {
    background: linear-gradient(to right, #da8cff, #9a55ff);
  }
  
  .bg-gradient-success {
    background: linear-gradient(to right, #84d9d2, #07cdae);
  }
  
  .bg-gradient-info {
    background: linear-gradient(to right, #90caf9, #047edf);
  }
  
  .bg-gradient-warning {
    background: linear-gradient(to right, #ffb74d, #ff9800);
  }
  
  .stats-info small {
    display: block;
    font-size: 0.75rem;
    color: #999;
    margin-top: 2px;
  }
  
  .badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 500;
  }
  
  .badge-success {
    background-color: #28a745;
    color: white;
  }
  
  .badge-warning {
    background-color: #ffc107;
    color: #333;
  }
  
  .filter-card {
    margin-bottom: 20px;
  }
  
  .filter-card .card-body {
    padding: 15px 20px;
  }
  
  .filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: flex-end;
  }
  
  .filter-form .form-group {
    flex: 1;
    min-width: 180px;
    margin-bottom: 0;
  }
  
  .filter-form .form-group label {
    display: block;
    margin-bottom: 5px;
    font-size: 0.875rem;
    font-weight: 500;
    color: #333;
  }
  
  .filter-form .input-group-text {
    font-size: 0.875rem;
    padding: 6px 12px;
    background-color: #f8f9fa;
    border-color: #ced4da;
  }
  
  .filter-form .form-control {
    font-size: 0.875rem;
    padding: 6px 12px;
    height: 38px;
  }
  
  .filter-form .btn-group {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
  }
  
  .filter-form .btn {
    font-size: 0.875rem;
    padding: 6px 16px;
    height: 38px;
    white-space: nowrap;
  }
  
  .table th {
    white-space: nowrap;
  }
  
  .pagination {
    display: flex;
    justify-content: center;
    margin-top: 30px;
    flex-wrap: wrap;
  }
  
  .pagination .page-item {
    margin: 0 3px;
  }
  
  .pagination .page-link {
    padding: 8px 16px;
    border-radius: 4px;
    border: 1px solid #ddd;
    color: #333;
    background-color: #fff;
    text-decoration: none;
    transition: all 0.2s;
  }
  
  .pagination .page-link:hover {
    background-color: #f5f5f5;
  }
  
  .pagination .active .page-link {
    background-color: #9a55ff;
    color: white;
    border-color: #9a55ff;
  }
  
  .pagination .disabled .page-link {
    color: #aaa;
    pointer-events: none;
    background-color: #f5f5f5;
  }
  
  .pagination-info {
    text-align: center;
    margin-top: 10px;
    color: #666;
    font-size: 14px;
  }
  
  .empty-table-message {
    text-align: center;
    padding: 30px;
    color: #666;
    font-style: italic;
  }
  
  .top-users-wrapper {
    overflow-x: hidden;
    max-width: 100%;
  }
  
  .top-users-table {
    width: 100%;
    table-layout: fixed;
    margin-bottom: 0;
  }
  
  .top-users-table th,
  .top-users-table td {
    padding: 10px 8px;
    vertical-align: middle;
    word-wrap: break-word;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  
  .top-users-table th:first-child,
  .top-users-table td:first-child {
    width: 60px;
    min-width: 60px;
    max-width: 60px;
    text-align: center;
    padding: 10px 5px;
  }
  
  .top-users-table th:last-child,
  .top-users-table td:last-child {
    width: 120px;
    min-width: 120px;
    text-align: right;
    padding-right: 10px;
  }
  
  .top-users-table th:nth-child(2),
  .top-users-table td:nth-child(2) {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  
  .user-rank {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 0.9rem;
    flex-shrink: 0;
  }
  
  .rank-1 {
    background-color: #FFD700;
  }
  
  .rank-2 {
    background-color: #C0C0C0;
  }
  
  .rank-3 {
    background-color: #CD7F32;
  }
  
  .rank-other {
    background-color: #6c757d;
  }
  
  .chart-container {
    position: relative;
    height: 300px;
  }
  
  @media (max-width: 768px) {
    .filter-form {
      flex-direction: column;
    }
    
    .filter-form .form-group {
      width: 100%;
    }
    
    .stats-container {
      flex-direction: column;
    }
    
    .card-stats {
      width: 100%;
      margin-bottom: 15px;
    }
  }
</style>

<!-- Include Chart.js library -->
<!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2 if available
    if (typeof $.fn.select2 !== 'undefined') {
      $('.select2').select2();
    }
    
    // Year select change event
    document.getElementById('yearSelect').addEventListener('change', function() {
      document.getElementById('yearForm').submit();
    });
    
    // Revenue Chart
    var ctx = document.getElementById('revenueChart').getContext('2d');
    var revenueChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: <?php echo $chartLabelsJson; ?>,
        datasets: [{
          label: 'Doanh thu (đ)',
          data: <?php echo $chartDataJson; ?>,
          backgroundColor: 'rgba(154, 85, 255, 0.6)',
          borderColor: 'rgba(154, 85, 255, 1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + ' đ';
              }
            }
          }
        },
        plugins: {
          tooltip: {
            callbacks: {
              label: function(context) {
                var value = context.raw;
                return 'Doanh thu: ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + ' đ';
              }
            }
          }
        }
      }
    });
  });
</script>