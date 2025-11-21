<?php
// Kiểm tra quyền truy cập
if ($_SESSION['role'] != 1) {
    echo "<script>
        alert('Bạn không đủ thẩm quyền truy cập!');
    </script>";
    header("refresh: 0; url='/ad'");
    exit;
}

include_once(__DIR__ . "/../../controller/cQLthongtin.php");
$p = new cqlthongtin();

// Pagination settings
$itemsPerPage = 20;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Get filters
$statusFilter = isset($_GET['status_filter']) ? $_GET['status_filter'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get statistics
$stats = $p->getUserStats();

// Get total count for pagination
$totalUsers = $p->countUsers($statusFilter, $search);
$totalPages = ceil($totalUsers / $itemsPerPage);

// Ensure current page is within valid range
if ($totalPages > 0 && $currentPage > $totalPages) {
    $currentPage = $totalPages;
    $offset = ($currentPage - 1) * $itemsPerPage;
}

// Get paginated users
$data = $p->getpaginatedusers($offset, $itemsPerPage, $statusFilter, $search);


// Process individual disable/restore user actions
if (isset($_GET['disable']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $p->disableuser($id);
    if ($result) {
        $redirectUrl = "?taikhoan&status=disabled";
        if ($statusFilter !== 'all') $redirectUrl .= "&status_filter={$statusFilter}";
        if (!empty($search)) $redirectUrl .= "&search=" . urlencode($search);
        if ($currentPage > 1) $redirectUrl .= "&page={$currentPage}";
        
        header("Location: {$redirectUrl}");
        exit();
    }
}

if (isset($_GET['restore']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $p->restoreuser($id);
    if ($result) {
        $redirectUrl = "?taikhoan&status=restored";
        if ($statusFilter !== 'all') $redirectUrl .= "&status_filter={$statusFilter}";
        if (!empty($search)) $redirectUrl .= "&search=" . urlencode($search);
        if ($currentPage > 1) $redirectUrl .= "&page={$currentPage}";
        
        header("Location: {$redirectUrl}");
        exit();
    }
}

// Function to generate pagination URL
function getPaginationUrl($page, $statusFilter, $search) {
    $url = "taikhoan&page={$page}";
    if ($statusFilter !== 'all') $url .= "&status_filter={$statusFilter}";
    if (!empty($search)) $url .= "&search=" . urlencode($search);
    return $url;
}

require_once __DIR__ . '/../../helpers/url_helper.php';
?>

<style>
    /* CSS riêng cho trang quản lý tài khoản */
    /* Import common admin styles */
    @import url('../css/admin-common.css');
    
    /* CSS riêng cho trang quản lý tài khoản - chỉ override nếu cần */
    .user-container {
        /* Đã được định nghĩa trong admin-common.css */
    }
    
    .admin-table-wrapper {
        margin-bottom: 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .admin-table {
        table-layout: auto;
        width: 100%;
        margin: 0;
    }
    
    .admin-table td,
    .admin-table th {
        padding: 15px;
        vertical-align: middle;
    }
    
    .admin-table thead th {
        background: #f8f9fa;
        font-weight: 600;
        color: #333;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
    }
    
    .admin-table tbody tr {
        border-bottom: 1px solid #f0f0f0;
    }
    
    .admin-table tbody tr:hover {
        background: #f8f9fa;
    }
    
    .btn a {
        text-decoration: none;
        color: #ffffff;
    }
    
    .disabled-user {
        opacity: 0.6;
        background-color: #f5f5f5;
    }
    
    .user-avatar {
        width: 50px !important;
        height: 50px !important;
        min-width: 50px !important;
        min-height: 50px !important;
        max-width: 50px !important;
        max-height: 50px !important;
        aspect-ratio: 1 / 1;
        object-fit: cover;
        object-position: center;
        border-radius: 4px;
        border: 2px solid #dee2e6;
        display: block;
        margin: 0 auto;
    }
    
    .actions {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
    }
    
    .actions .btn {
        white-space: nowrap;
        margin: 0;
    }
    
    .admin-table td {
        vertical-align: middle;
    }
    
    .admin-table th {
        text-align: left;
    }
    
    .admin-table th:first-child,
    .admin-table td:first-child {
        text-align: center;
    }
    
    .admin-table th:nth-child(2),
    .admin-table td:nth-child(2) {
        text-align: center;
    }
    
    .admin-table th:nth-child(7),
    .admin-table td:nth-child(7) {
        text-align: center;
    }
    
    .admin-table th:nth-child(8),
    .admin-table td:nth-child(8) {
        text-align: center;
    }
    
    
    .top-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        overflow-y: auto;
    }
    
    .modal-content {
        background: white;
        margin: 3% auto;
        padding: 30px;
        width: 90%;
        max-width: 800px;
        border-radius: 10px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        position: relative;
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 2px solid #dee2e6;
        padding-bottom: 15px;
    }
    
    .modal-header h3 {
        margin: 0;
        color: #333;
    }
    
    .close {
        font-size: 28px;
        cursor: pointer;
        color: #999;
        background: none;
        border: none;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .close:hover {
        color: #333;
    }
    
    .detail-section {
        margin-bottom: 25px;
    }
    
    .detail-section h4 {
        color: #007bff;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #dee2e6;
    }
    
    .detail-row {
        display: flex;
        margin-bottom: 12px;
        align-items: flex-start;
    }
    
    .detail-label {
        font-weight: 600;
        width: 150px;
        color: #666;
        flex-shrink: 0;
    }
    
    .detail-value {
        flex: 1;
        color: #333;
    }
    
    .user-detail-avatar {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 50%;
        border: 3px solid #dee2e6;
        margin-bottom: 15px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .loading-spinner {
        display: inline-block;
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
</style>

<div class="user-container admin-container">
    <div class="admin-card">
        <h3 class="admin-card-title">Quản lý tài khoản</h3>
        
        <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] == 'disabled'): ?>
            <div class="alert alert-success action-message">
                <i class="mdi mdi-check-circle"></i>
                Người dùng đã được vô hiệu hóa thành công.
            </div>
        <?php elseif ($_GET['status'] == 'restored'): ?>
        <div class="alert alert-success action-message">
            <i class="mdi mdi-check-circle"></i>
            Người dùng đã được khôi phục thành công.
        </div>
        <?php elseif ($_GET['status'] == 'added'): ?>
        <div class="alert alert-success action-message">
            <i class="mdi mdi-check-circle"></i>
            Đã thêm người dùng mới thành công.
        </div>
        <?php elseif ($_GET['status'] == 'updated'): ?>
        <div class="alert alert-success action-message">
            <i class="mdi mdi-check-circle"></i>
            Đã cập nhật thông tin người dùng thành công.
        </div>
        <?php endif; ?>
    <?php endif; ?>
    

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <h3>Tổng số tài khoản</h3>
            <div class="number"><?php echo number_format($stats['total_users']); ?></div>
        </div>
        <div class="stat-card success">
            <h3>Đang hoạt động</h3>
            <div class="number"><?php echo number_format($stats['active_users']); ?></div>
        </div>
        <div class="stat-card danger">
            <h3>Đã vô hiệu hóa</h3>
            <div class="number"><?php echo number_format($stats['disabled_users']); ?></div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters">
        <form method="GET">
            <input type="hidden" name="taikhoan" value="">
            
            <div class="form-group">
                <label>Tìm kiếm</label>
                <input type="text" name="search" class="form-control" placeholder="Tên, email hoặc số điện thoại..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div class="form-group">
                <label>Trạng thái</label>
                <select name="status_filter" class="form-control">
                    <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>Tất cả trạng thái</option>
                    <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                    <option value="disabled" <?php echo $statusFilter === 'disabled' ? 'selected' : ''; ?>>Vô hiệu hóa</option>
                </select>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="mdi mdi-filter"></i> Lọc
                </button>
                <a href="?taikhoan" class="btn btn-secondary" style="margin-left: 10px;">
                    <i class="mdi mdi-refresh"></i> Đặt lại
                </a>
            </div>
        </form>
    </div>

    <!-- Top Actions -->
    <div class="top-actions">
        <button type="button" class="btn btn-primary">
            <a href="?taikhoan&them">  
                <i class="mdi mdi-account-plus"></i> Thêm người dùng mới
            </a>
        </button>
        
        <div class="pagination-info" style="margin: 0;">
            Hiển thị <strong><?php echo count($data); ?></strong> / <strong><?php echo number_format($totalUsers); ?></strong> tài khoản
        </div>
    </div>

    <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 70px; text-align: center;">ID</th>
                        <th style="width: 80px; text-align: center;">Ảnh đại diện</th>
                        <th style="min-width: 150px;">Họ và tên</th>
                        <th style="min-width: 200px;">Email</th>
                        <th style="width: 130px;">Số điện thoại</th>
                        <th style="min-width: 200px;">Địa chỉ</th>
                        <th style="width: 120px; text-align: center;">Trạng thái</th>
                        <th style="width: 250px; text-align: center;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                    <tr>
                        <td colspan="8" class="empty-table-message" style="text-align: center; padding: 40px;">
                            <i class="mdi mdi-information-outline" style="font-size: 48px; color: #ccc; margin-bottom: 10px;"></i>
                            <p style="color: #666; margin: 0;">Không tìm thấy tài khoản nào<?php echo ($statusFilter !== 'all' || !empty($search)) ? ' với điều kiện đã chọn' : ''; ?>.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach($data as $u): ?>
                            <?php if ($u['role_id'] == 2): ?>
                                <?php
                                    $isActive = $u['is_active'] == 1;
                                    $rowClass = !$isActive ? 'disabled-user' : '';
                                    $statusText = $isActive ? 'Hoạt động' : 'Vô hiệu hóa';
                                    $avatarPath = getBasePath() . '/img/' . ($u['avatar'] ?? 'default-avatar.jpg');
                                ?>
                                <tr class="<?php echo $rowClass; ?>">
                                    <td>
                                        <strong>#<?php echo $u['id']; ?></strong>
                                    </td>
                                    <td>
                                        <img src="<?php echo $avatarPath; ?>" alt="Avatar" class="user-avatar" onerror="this.src='<?php echo getBasePath(); ?>/img/default-avatar.jpg'">
                                    </td>
                                    <td>
                                        <div style="word-wrap: break-word;">
                                            <?php echo htmlspecialchars($u['username'] ?? ''); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="word-wrap: break-word;">
                                            <?php echo htmlspecialchars($u['email'] ?? ''); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="white-space: nowrap;">
                                            <?php echo htmlspecialchars($u['phone'] ?? '-'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="word-wrap: break-word;">
                                            <?php echo htmlspecialchars($u['address'] ?? '-'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $isActive ? 'success' : 'danger'; ?>" style="display: inline-block;">
                                            <?php echo $statusText; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <button type="button" class="btn btn-info btn-sm" onclick="showUserDetails(<?php echo $u['id']; ?>)" title="Xem chi tiết">
                                                <i class="mdi mdi-eye"></i> Chi tiết
                                            </button>
                                            <a href="?taikhoan&sua&ids=<?php echo $u['id']; ?>" class="btn btn-primary btn-sm" title="Chỉnh sửa">
                                                <i class="mdi mdi-pencil"></i> Sửa
                                            </a>
                                            <?php if ($isActive): ?>
                                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#disableUserModal" data-id="<?php echo $u['id']; ?>" data-name="<?php echo htmlspecialchars($u['username']); ?>" title="Vô hiệu hóa">
                                                    <i class="mdi mdi-account-off"></i> Vô hiệu hóa
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#restoreUserModal" data-id="<?php echo $u['id']; ?>" data-name="<?php echo htmlspecialchars($u['username']); ?>" title="Khôi phục">
                                                    <i class="mdi mdi-account-check"></i> Khôi phục
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination-info">
            Hiển thị <?php echo count($data); ?> trên tổng số <?php echo number_format($totalUsers); ?> bản ghi
        </div>
        <div class="pagination">
            <?php if ($currentPage > 1): ?>
                <a href="?<?php echo getPaginationUrl($currentPage - 1, $statusFilter, $search); ?>">&lt;</a>
            <?php endif; ?>
            
            <?php
                // Determine range of page numbers to show (current page ± 1)
                $startPage = max(1, $currentPage - 1);
                $endPage = min($totalPages, $currentPage + 1);
                
                // Show first page if not in range
                if ($startPage > 1): ?>
                    <a href="?<?php echo getPaginationUrl(1, $statusFilter, $search); ?>">1</a>
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
                    <a href="?<?php echo getPaginationUrl($i, $statusFilter, $search); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php
                // Show last page if not in range
                if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                        <span class="ellipsis">...</span>
                    <?php endif; ?>
                    <a href="?<?php echo getPaginationUrl($totalPages, $statusFilter, $search); ?>"><?php echo $totalPages; ?></a>
                <?php endif; ?>
            
            <?php if ($currentPage < $totalPages): ?>
                <a href="?<?php echo getPaginationUrl($currentPage + 1, $statusFilter, $search); ?>">&gt;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    </div>
</div>

<!-- Modal Chi tiết tài khoản -->
<div id="userModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Chi tiết tài khoản</h3>
            <button class="close" onclick="closeUserModal()">&times;</button>
        </div>
        <div id="userDetails"></div>
    </div>
</div>

<script>

// Show user details modal
function showUserDetails(userId) {
    // Show loading
    document.getElementById('userDetails').innerHTML = '<div style="text-align: center; padding: 40px;"><div class="loading-spinner"></div><p style="margin-top: 15px; color: #666;">Đang tải thông tin tài khoản...</p></div>';
    document.getElementById('userModal').style.display = 'block';
    
    // Fetch user details via AJAX
    const url = new URL(window.location.href);
    url.searchParams.set('taikhoan', '');
    url.searchParams.set('action', 'get_details');
    url.searchParams.set('user_id', userId);
    
    // Remove other params to avoid conflicts
    url.searchParams.delete('page');
    url.searchParams.delete('status_filter');
    url.searchParams.delete('search');
    
    fetch(url.toString())
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const user = data.user;
                const avatarPath = '<?php echo getBasePath(); ?>/img/' + (user.avatar || 'default-avatar.jpg');
                const roleNames = {
                    1: 'Admin',
                    2: 'Người dùng',
                    3: 'Moderator',
                    4: 'Kiểm duyệt viên',
                    5: 'Doanh nghiệp'
                };
                
                let html = `
                    <div style="text-align: center; margin-bottom: 20px;">
                        <img src="${avatarPath}" alt="Avatar" class="user-detail-avatar" onerror="this.src='<?php echo getBasePath(); ?>/img/default-avatar.jpg'">
                    </div>
                    
                    <div class="detail-section">
                        <h4>Thông tin cơ bản</h4>
                        <div class="detail-row">
                            <span class="detail-label">ID:</span>
                            <span class="detail-value"><strong>#${user.id}</strong></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Họ và tên:</span>
                            <span class="detail-value">${user.username || 'N/A'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value">${user.email || 'N/A'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Số điện thoại:</span>
                            <span class="detail-value">${user.phone || 'N/A'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Địa chỉ:</span>
                            <span class="detail-value">${user.address || 'N/A'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Vai trò:</span>
                            <span class="detail-value">${roleNames[user.role_id] || 'Người dùng'}</span>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h4>Trạng thái</h4>
                        <div class="detail-row">
                            <span class="detail-label">Trạng thái:</span>
                            <span class="detail-value">
                                <span class="status-badge ${user.is_active == 1 ? 'success' : 'danger'}">
                                    ${user.is_active == 1 ? 'Hoạt động' : 'Vô hiệu hóa'}
                                </span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Ngày tạo:</span>
                            <span class="detail-value">${user.created_date ? formatDate(user.created_date) : 'N/A'}</span>
                        </div>
                        ${user.updated_date ? `
                        <div class="detail-row">
                            <span class="detail-label">Cập nhật lần cuối:</span>
                            <span class="detail-value">${formatDate(user.updated_date)}</span>
                        </div>
                        ` : ''}
                    </div>
                    
                    <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                        <a href="?taikhoan&sua&ids=${user.id}" class="btn btn-primary">
                            <i class="mdi mdi-pencil"></i> Chỉnh sửa
                        </a>
                        <button class="btn btn-secondary" onclick="closeUserModal()">
                            <i class="mdi mdi-close"></i> Đóng
                        </button>
                    </div>
                `;
                
                document.getElementById('userDetails').innerHTML = html;
                document.getElementById('userModal').style.display = 'block';
            } else {
                const errorMsg = data.message || 'Không thể tải thông tin tài khoản';
                document.getElementById('userDetails').innerHTML = '<div style="text-align: center; padding: 40px; color: #dc3545;"><p>' + errorMsg + '</p></div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('userDetails').innerHTML = '<div style="text-align: center; padding: 40px; color: #dc3545;"><p>Lỗi khi tải thông tin tài khoản: ' + error.message + '</p><p style="font-size: 0.9rem; margin-top: 10px;">Vui lòng thử lại sau hoặc liên hệ quản trị viên.</p></div>';
        });
}

function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'});
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('userModal');
    if (event.target === modal) {
        closeUserModal();
    }
}

// Disable User Modal
document.getElementById('disableUserModal')?.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const id = button.getAttribute('data-id');
    const name = button.getAttribute('data-name');
    
    document.getElementById('disableUserId').value = id;
    document.getElementById('disableUserName').textContent = name;
});

// Restore User Modal
document.getElementById('restoreUserModal')?.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const id = button.getAttribute('data-id');
    const name = button.getAttribute('data-name');
    
    document.getElementById('restoreUserId').value = id;
    document.getElementById('restoreUserName').textContent = name;
});
</script>

<!-- Disable User Modal -->
<div class="modal fade" id="disableUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="GET" action="">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Vô hiệu hóa tài khoản</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn vô hiệu hóa tài khoản "<span id="disableUserName"></span>"?</p>
                    <p>Sau khi vô hiệu hóa, tài khoản này sẽ không thể đăng nhập vào hệ thống.</p>
                    <input type="hidden" name="taikhoan" value="">
                    <input type="hidden" name="disable" value="">
                    <input type="hidden" name="id" id="disableUserId">
                    <input type="hidden" name="page" value="<?php echo $currentPage; ?>">
                    <input type="hidden" name="status_filter" value="<?php echo $statusFilter; ?>">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xác nhận vô hiệu hóa</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Restore User Modal -->
<div class="modal fade" id="restoreUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="GET" action="">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Khôi phục tài khoản</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn khôi phục tài khoản "<span id="restoreUserName"></span>"?</p>
                    <p>Sau khi khôi phục, tài khoản này sẽ có thể đăng nhập vào hệ thống trở lại.</p>
                    <input type="hidden" name="taikhoan" value="">
                    <input type="hidden" name="restore" value="">
                    <input type="hidden" name="id" id="restoreUserId">
                    <input type="hidden" name="page" value="<?php echo $currentPage; ?>">
                    <input type="hidden" name="status_filter" value="<?php echo $statusFilter; ?>">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Xác nhận khôi phục</button>
                </div>
            </div>
        </form>
    </div>
</div>
