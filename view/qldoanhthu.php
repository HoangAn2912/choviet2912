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

<div class="col-12">
  <div class="card">
    <div class="card-body">
      <h3 class="card-title">Quản lý doanh thu</h3>
      <p class="card-description">
        Thống kê doanh thu từ phí đăng bài (3% giá sản phẩm)
      </p>
      
      <!-- Filter Card -->
      <div class="card filter-card">
        <div class="card-body">
          <h4 class="card-title">Bộ lọc</h4>
          <form class="filter-form" method="GET" action="">
            <input type="hidden" name="qldoanhthu" value="">
            
            <div class="form-group">
              <label>Khoảng thời gian</label>
              <div class="input-group">
                <input type="date" class="form-control" name="start_date" value="<?php echo $startDate; ?>" placeholder="Từ ngày">
                <div class="input-group-append input-group-prepend">
                  <span class="input-group-text">đến</span>
                </div>
                <input type="date" class="form-control" name="end_date" value="<?php echo $endDate; ?>" placeholder="Đến ngày">
              </div>
            </div>
            
            <div class="form-group">
              <label>Người dùng</label>
              <select class="form-control select2" name="user_id">
                <option value="">Tất cả người dùng</option>
                <?php foreach ($allUsers as $user): ?>
                  <option value="<?php echo $user['id']; ?>" <?php echo ($userId == $user['id']) ? 'selected' : ''; ?>>
                    <?php echo $user['username']; ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <div class="btn-group">
              <button type="submit" class="btn btn-primary">
                <i class="mdi mdi-filter"></i> Lọc
              </button>
              <?php require_once '../helpers/url_helper.php'; ?>
              <a href="<?= getBasePath() ?>/ad/qldoanhthu" class="btn btn-outline-secondary">
                <i class="mdi mdi-refresh"></i> Đặt lại
              </a>
            </div>
          </form>
        </div>
      </div>
      
      <!-- Statistics Cards -->
      <div class="row mb-4 stats-container">
        <div class="col-md-4 mb-4 mb-md-0">
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
        
        <div class="col-md-4 mb-4 mb-md-0">
          <div class="card card-stats">
            <div class="card-body d-flex align-items-center">
              <div class="stats-icon bg-gradient-success">
                <i class="mdi mdi-file-document"></i>
              </div>
              <div class="stats-info">
                <h3><?php echo $summary['total_posts']; ?></h3>
                <p>Tổng bài đăng</p>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-md-4">
          <div class="card card-stats">
            <div class="card-body d-flex align-items-center">
              <div class="stats-icon bg-gradient-info">
                <i class="mdi mdi-account-multiple"></i>
              </div>
              <div class="stats-info">
                <h3><?php echo $summary['total_users']; ?></h3>
                <p>Người dùng có bài đăng</p>
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
              <h4 class="card-title">Top người dùng có doanh thu cao nhất</h4>
              <div class="table-responsive">
                <table class="table table-hover top-users-table">
                  <thead>
                    <tr>
                      <th>Xếp hạng</th>
                      <th>Người dùng</th>
                      <th>Doanh thu</th>
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
                            <div class="d-flex align-items-center">
                              <div class="user-rank rank-<?php echo min($rank, 3); ?>">
                                <?php echo $rank; ?>
                              </div>
                            </div>
                          </td>
                          <td>
                            <div class="d-flex flex-column">
                              <span class="font-weight-bold"><?php echo $user['username']; ?></span>
                              <small><?php echo $user['total_posts']; ?> bài đăng</small>
                            </div>
                          </td>
                          <td class="font-weight-bold">
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
                  <th>Tiêu đề sản phẩm</th>
                  <th>Giá sản phẩm</th>
                  <th>Người đăng</th>
                  <th>Ngày đăng</th>
                  <th>Phí doanh thu (11.000 đ)</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($revenueData)): ?>
                  <tr>
                    <td colspan="6" class="empty-table-message">
                                             Không tìm thấy dữ liệu doanh thu nào<?php echo ($startDate && $endDate) ? ' trong khoảng thời gian đã chọn' : ''; ?>.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($revenueData as $item): ?>
                    <tr>
                      <td><?php echo $item['product_id']; ?></td>
                      <td><?php echo $item['title']; ?></td>
                      <td><?php echo formatCurrency($item['price']); ?></td>
                      <td><?php echo $item['username']; ?></td>
                      <td><?php echo $item['created_date'] ? date('d/m/Y', strtotime($item['created_date'])) : 'N/A'; ?></td>
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
  
  .filter-card {
    margin-bottom: 20px;
  }
  
  .filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: flex-end;
  }
  
  .filter-form .form-group {
    flex: 1;
    min-width: 200px;
    margin-bottom: 0;
  }
  
  .filter-form .btn-group {
    display: flex;
    gap: 10px;
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
  
  .top-users-table td {
    vertical-align: middle;
  }
  
  .user-rank {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    margin-right: 10px;
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