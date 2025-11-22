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
include_once(__DIR__ . "/../../controller/cQLdoanhthu.php");
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
    $url = "qldoanhthu&page={$page}";
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

<?php require_once __DIR__ . '/../../helpers/url_helper.php'; ?>
<link rel="stylesheet" href="<?php echo getBasePath() ?>/css/admin-common.css">
<style>
    /* CSS riêng cho trang quản lý doanh thu */
    .doanhthu-container {
        /* Đã được định nghĩa trong admin-common.css */
    }
    
    .doanhthu-container .card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    
    .doanhthu-container .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }
    
    /* Filters base styles */
    .doanhthu-container .filters {
        background: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .doanhthu-container .filters form {
        display: flex;
        gap: 15px;
        align-items: flex-end;
        flex-wrap: wrap;
    }
    
    .doanhthu-container .filters .form-group {
        flex: 1 1 auto;
        min-width: 180px;
        max-width: 100%;
        box-sizing: border-box;
    }
    
    /* Đảm bảo form-group không bị đè */
    .doanhthu-container .filters .form-group > * {
        width: 100%;
        box-sizing: border-box;
    }
    
    /* Date range wrapper - Đảm bảo không bị thu nhỏ quá */
    .doanhthu-container .filters .date-range-wrapper {
        min-width: 300px;
        flex: 1 1 auto;
    }
    
    .doanhthu-container .filters .date-range-wrapper input {
        flex: 1;
        min-width: 0;
    }
    
    /* Form actions group - đảm bảo không wrap sớm */
    .doanhthu-container .filters .form-actions-group {
        flex: 0 0 auto;
        display: flex;
        gap: 10px;
        align-items: center;
        white-space: nowrap;
    }
    
    /* Date range wrapper - Desktop */
    .doanhthu-container .date-range-wrapper {
        display: flex;
        gap: 10px;
        align-items: center;
        width: 100%;
        flex-wrap: nowrap;
    }
    
    .doanhthu-container .date-range-wrapper input {
        flex: 1;
        min-width: 0;
        width: auto;
    }
    
    .doanhthu-container .date-separator {
        color: #666;
        white-space: nowrap;
        flex-shrink: 0;
    }
    
    .doanhthu-container .btn-reset {
        margin-left: 10px;
    }
    
    /* Đảm bảo form-control không overflow */
    .doanhthu-container .filters .form-control {
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        overflow: hidden;
    }
</style>

<div class="doanhthu-container">
  <div class="admin-card">
    <h3 class="admin-card-title">Quản lý doanh thu</h3>
      <p style="color: #666; margin-bottom: 20px;">
        Thống kê doanh thu từ phí đăng tin (11.000 đ/bài) và phí đăng ký gói livestream
      </p>
      
      <!-- Filter Card -->
      <div class="filters">
        <form method="GET" action="">
            <input type="hidden" name="qldoanhthu" value="">
            
            <div class="form-group">
              <label>Khoảng thời gian</label>
              <div class="date-range-wrapper">
                <input type="date" class="form-control" name="start_date" value="<?php echo $startDate; ?>" placeholder="Từ ngày">
                <span class="date-separator">đến</span>
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
            
            <div class="form-group form-actions-group">
              <button type="submit" class="btn btn-primary">
                <i class="mdi mdi-filter"></i> Lọc
              </button>
              <a href="<?= getBasePath() ?>/admin" class="btn btn-secondary btn-reset">
                <i class="mdi mdi-refresh"></i> Đặt lại
              </a>
            </div>
          </form>
      </div>
      
      <!-- Statistics Cards -->
      <div class="stats-grid">
        <div class="stat-card primary">
          <h3>Tổng doanh thu</h3>
          <div class="number"><?php echo formatCurrency($summary['total_revenue']); ?></div>
        </div>
        
        <div class="stat-card success">
          <h3>Doanh thu đăng tin</h3>
          <div class="number"><?php echo formatCurrency($summary['post_revenue'] ?? 0); ?></div>
          <small style="display: block; margin-top: 5px; color: #666;"><?php echo $summary['total_posts']; ?> bài từ <?php echo $summary['total_users']; ?> người dùng</small>
        </div>
        
        <div class="stat-card warning">
          <h3>Doanh thu gói live</h3>
          <div class="number"><?php echo formatCurrency($summary['live_revenue'] ?? 0); ?></div>
          <small style="display: block; margin-top: 5px; color: #666;"><?php echo $summary['total_packages'] ?? 0; ?> gói từ <?php echo $summary['total_live_users'] ?? 0; ?> người dùng</small>
        </div>
        
        <div class="stat-card info">
          <h3>Tổng người dùng</h3>
          <div class="number"><?php echo $summary['total_unique_users'] ?? 0; ?></div>
        </div>
      </div>

      <div class="chart-topusers-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px;">
        <!-- Revenue Chart -->
        <div class="admin-card">
          <h4 class="admin-card-title">Biểu đồ doanh thu theo tháng (<?php echo $year; ?>)</h4>
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
        
        <!-- Top Users -->
        <div class="admin-card">
          <h4 class="admin-card-title">Top người dùng có số tiền trong tài khoản nhiều nhất</h4>
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
      
      <!-- Revenue Table -->
      <div class="admin-card">
        <h4 class="admin-card-title">Chi tiết doanh thu</h4>
        <div class="admin-table-wrapper">
          <table class="admin-table">
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
                    <td colspan="7" class="empty-message">
                      Không tìm thấy dữ liệu doanh thu nào<?php echo ($startDate && $endDate) ? ' trong khoảng thời gian đã chọn' : ''; ?>.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($revenueData as $item): ?>
                    <tr>
                      <td>#<?php echo $item['id']; ?></td>
                      <td>
                        <?php 
                          $title = htmlspecialchars($item['title']);
                          $titleFull = $title;
                          if (mb_strlen($title) > 29) {
                            $title = mb_substr($title, 0, 29) . '...';
                          }
                        ?>
                        <span title="<?php echo $titleFull; ?>"><?php echo $title; ?></span>
                      </td>
                      <td><?php echo $item['price'] > 0 ? formatCurrency($item['price']) : '-'; ?></td>
                      <td><?php echo htmlspecialchars($item['username']); ?></td>
                      <td><?php echo isset($item['revenue_date']) ? date('d/m/Y H:i', strtotime($item['revenue_date'])) : (isset($item['created_date']) ? date('d/m/Y H:i', strtotime($item['created_date'])) : 'N/A'); ?></td>
                      <td>
                        <span class="status-badge <?php echo ($item['revenue_type'] ?? '') == 'livestream_package' ? 'warning' : 'success'; ?>">
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
            <div class="pagination">
              <?php if ($currentPage > 1): ?>
                <a href="?<?php echo getPaginationUrl($currentPage - 1, $startDate, $endDate, $userId); ?>">&lt;</a>
              <?php endif; ?>
              
              <?php
                // Determine range of page numbers to show
                $startPage = max(1, $currentPage - 1);
                $endPage = min($totalPages, $currentPage + 1);
                
                // Show first page if not in range
                if ($startPage > 1): ?>
                  <a href="?<?php echo getPaginationUrl(1, $startDate, $endDate, $userId); ?>">1</a>
                  <?php if ($startPage > 2): ?>
                    <span class="ellipsis">...</span>
                  <?php endif; ?>
                <?php endif;
                
                // Generate page links
                for ($i = $startPage; $i <= $endPage; $i++):
              ?>
                <?php if ($i == $currentPage): ?>
                  <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                  <a href="?<?php echo getPaginationUrl($i, $startDate, $endDate, $userId); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
              <?php endfor; ?>
              
              <?php
                // Show last page if not in range
                if ($endPage < $totalPages): ?>
                  <?php if ($endPage < $totalPages - 1): ?>
                    <span class="ellipsis">...</span>
                  <?php endif; ?>
                  <a href="?<?php echo getPaginationUrl($totalPages, $startDate, $endDate, $userId); ?>"><?php echo $totalPages; ?></a>
                <?php endif; ?>
              
              <?php if ($currentPage < $totalPages): ?>
                <a href="?<?php echo getPaginationUrl($currentPage + 1, $startDate, $endDate, $userId); ?>">&gt;</a>
              <?php endif; ?>
            </div>
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
  
  /* Date range wrapper - Desktop */
  .date-range-wrapper {
    display: flex;
    gap: 10px;
    align-items: center;
  }
  
  .date-separator {
    color: #666;
    white-space: nowrap;
  }
  
  .btn-reset {
    margin-left: 10px;
  }
  
  /* Tablet large - Tránh đè khi màn hình vừa phải */
  @media (min-width: 992px) and (max-width: 1400px) {
    .doanhthu-container .filters {
      padding: 18px !important;
    }
    
    .doanhthu-container .filters form {
      flex-wrap: wrap;
      gap: 15px;
      align-items: flex-end;
    }
    
    .doanhthu-container .filters .form-group {
      flex: 1 1 calc(50% - 7.5px);
      min-width: 220px;
      max-width: 100%;
      margin-bottom: 0;
    }
    
    /* Khoảng thời gian - full width khi wrap */
    .doanhthu-container .filters .form-group:first-child {
      flex: 1 1 100%;
      min-width: 100%;
    }
    
    .doanhthu-container .filters .date-range-wrapper {
      min-width: 300px;
      max-width: 100%;
      display: flex;
      gap: 10px;
      flex-wrap: nowrap;
    }
    
    .doanhthu-container .filters .date-range-wrapper input {
      flex: 1;
      min-width: 120px;
      max-width: none;
    }
    
    .doanhthu-container .filters .form-actions-group {
      flex: 1 1 100%;
      justify-content: flex-start;
      margin-top: 5px;
      gap: 10px;
    }
    
    .doanhthu-container .filters .form-actions-group .btn {
      flex: 0 0 auto;
    }
  }
  
  /* Tablet medium - Xử lý màn hình nhỏ hơn một chút */
  @media (min-width: 992px) and (max-width: 1200px) {
    .doanhthu-container .filters .form-group {
      flex: 1 1 100%;
      min-width: 100%;
    }
    
    .doanhthu-container .filters .date-range-wrapper {
      min-width: 100%;
    }
  }
  
  /* Desktop lớn - Tối ưu layout */
  @media (min-width: 1401px) {
    .doanhthu-container .filters form {
      flex-wrap: nowrap;
      gap: 15px;
    }
    
    .doanhthu-container .filters .form-group {
      flex: 1 1 auto;
      min-width: 180px;
      max-width: none;
    }
    
    .doanhthu-container .filters .form-actions-group {
      flex: 0 0 auto;
      margin-top: 0;
    }
  }
  
  @media (max-width: 991px) {
    /* Fix form filters responsive - với selector cụ thể hơn */
    .doanhthu-container .filters {
      padding: 15px !important;
    }
    
    .doanhthu-container .filters form {
      display: flex !important;
      flex-direction: column !important;
      gap: 15px !important;
      align-items: stretch !important;
    }
    
    .doanhthu-container .filters .form-group {
      width: 100% !important;
      margin-bottom: 0 !important;
      flex: none !important;
      min-width: 0 !important;
    }
    
    .doanhthu-container .filters .form-group label {
      display: block !important;
      margin-bottom: 8px !important;
      font-weight: 600;
      color: #333;
    }
    
    /* Date range inputs - stack vertically on mobile */
    .doanhthu-container .filters .date-range-wrapper {
      display: flex !important;
      flex-direction: column !important;
      gap: 10px !important;
      align-items: stretch !important;
    }
    
    .doanhthu-container .filters .date-range-wrapper input {
      width: 100% !important;
      flex: 1 !important;
      min-width: 0 !important;
      max-width: 100% !important;
    }
    
    .doanhthu-container .filters .date-separator {
      display: none !important;
    }
    
    .doanhthu-container .filters .form-group .form-control {
      width: 100% !important;
      padding: 10px !important;
      font-size: 0.9rem !important;
      box-sizing: border-box !important;
    }
    
    .doanhthu-container .filters .form-actions-group {
      display: flex !important;
      flex-direction: column !important;
      gap: 10px !important;
      width: 100% !important;
    }
    
    .doanhthu-container .filters .form-actions-group .btn {
      width: 100% !important;
      margin-left: 0 !important;
      margin-top: 0 !important;
    }
    
    .doanhthu-container .filters .form-actions-group .btn-reset {
      margin-left: 0 !important;
    }
    
    .doanhthu-container .filters .form-actions-group a.btn {
      display: block !important;
      text-align: center !important;
    }
  }
  
  @media (max-width: 768px) {
    .doanhthu-container > div[style*="grid"] {
      grid-template-columns: 1fr !important;
    }
    
    /* Chart and Top Users grid - stack vertically on mobile */
    .chart-topusers-grid {
      display: grid !important;
      grid-template-columns: 1fr !important;
      gap: 20px !important;
    }
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