<?php
if ($_SESSION['role'] != 1 && $_SESSION['role'] !=4) {
    echo "<script>
        alert('Bạn không đủ thẩm quyền truy cập!');
        
    </script>";
    header("refresh: 0; url='/ad'");
    exit;
}
?>

<?php

// Handle banner operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_banner':
                $title = $_POST['title'] ?? '';
                $description = $_POST['description'] ?? '';
                $image_url = $_POST['image_url'] ?? '';
                $button_text = $_POST['button_text'] ?? '';
                $button_link = $_POST['button_link'] ?? '';
                $display_order = $_POST['display_order'] ?? 1;
                $status = $_POST['status'] ?? 'active';

                $stmt = $mysqli->prepare("INSERT INTO banners (title, description, image_url, button_text, button_link, display_order, status, created_at) 
                                          VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("sssssis", $title, $description, $image_url, $button_text, $button_link, $display_order, $status);
                $stmt->execute();
                $stmt->close();

                $success_message = "Banner đã được thêm thành công!";
                break;

            case 'update_banner':
                $id = $_POST['id'];
                $title = $_POST['title'] ?? '';
                $description = $_POST['description'] ?? '';
                $image_url = $_POST['image_url'] ?? '';
                $button_text = $_POST['button_text'] ?? '';
                $button_link = $_POST['button_link'] ?? '';
                $display_order = $_POST['display_order'] ?? 1;
                $status = $_POST['status'] ?? 'active';

                $stmt = $mysqli->prepare("UPDATE banners 
                                          SET title=?, description=?, image_url=?, button_text=?, button_link=?, display_order=?, status=?, updated_at=NOW() 
                                          WHERE id=?");
                $stmt->bind_param("sssssisi", $title, $description, $image_url, $button_text, $button_link, $display_order, $status, $id);
                $stmt->execute();
                $stmt->close();

                $success_message = "Banner đã được cập nhật thành công!";
                break;

            case 'delete_banner':
                $id = $_POST['id'];
                $stmt = $mysqli->prepare("DELETE FROM banners WHERE id=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();

                $success_message = "Banner đã được xóa thành công!";
                break;
        }
    }
}

// Get all banners
$banners = [];
$result = $mysqli->query("SELECT * FROM banners ORDER BY display_order ASC, created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $banners[] = $row;
    }
    $result->free();
}

// Get banner for editing
$edit_banner = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $mysqli->prepare("SELECT * FROM banners WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_banner = $result->fetch_assoc();
    $stmt->close();
}
?>

<?php require_once __DIR__ . '/../../helpers/url_helper.php'; ?>
<link rel="stylesheet" href="<?php echo getBasePath() ?>/css/admin-common.css">
<style>
        /* CSS riêng cho trang quản lý banner */
        /* CSS riêng cho trang quản lý banner - chỉ override nếu cần */
        .banner-container {
            /* Đã được định nghĩa trong admin-common.css */
        }
        
        .banner-container .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .banner-container .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .banner-thumbnail {
            width: 100px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .form-card {
            margin-bottom: 20px;
        }
    </style>

   <div class="banner-container">
        <div class="admin-card">
            <h3 class="admin-card-title">Quản lý Banner</h3>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= $success_message ?>
                </div>
            <?php endif; ?>

            <!-- Banner Form -->
            <div class="card form-card">
                    <div class="card-body">
                        <h3 class="card-title">
                            <i class="fas fa-<?= $edit_banner ? 'edit' : 'plus' ?>"></i>
                            <?= $edit_banner ? 'Chỉnh sửa Banner' : 'Thêm Banner mới' ?>
                        </h3>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="<?= $edit_banner ? 'update_banner' : 'add_banner' ?>">
                            <?php if ($edit_banner): ?>
                                <input type="hidden" name="id" value="<?= $edit_banner['id'] ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Tiêu đề Banner</label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?= $edit_banner['title'] ?? '' ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="display_order" class="form-label">Thứ tự hiển thị</label>
                                        <input type="number" class="form-control" id="display_order" name="display_order" 
                                               value="<?= $edit_banner['display_order'] ?? 1 ?>" min="1">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Mô tả</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?= $edit_banner['description'] ?? '' ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="image_url" class="form-label">URL hình ảnh</label>
                                <input type="url" class="form-control" id="image_url" name="image_url" 
                                       value="<?= $edit_banner['image_url'] ?? '' ?>" required>
                                <div class="form-text">Nhập URL đầy đủ của hình ảnh (ví dụ: https://example.com/image.jpg)</div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="button_text" class="form-label">Text nút bấm (tùy chọn)</label>
                                        <input type="text" class="form-control" id="button_text" name="button_text" 
                                               value="<?= $edit_banner['button_text'] ?? '' ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="button_link" class="form-label">Link nút bấm (tùy chọn)</label>
                                        <input type="url" class="form-control" id="button_link" name="button_link" 
                                               value="<?= $edit_banner['button_link'] ?? '' ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">Trạng thái</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?= ($edit_banner['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Kích hoạt</option>
                                    <option value="inactive" <?= ($edit_banner['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Tạm dừng</option>
                                </select>
                            </div>
                            
                            <div class="actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> <?= $edit_banner ? 'Cập nhật' : 'Thêm mới' ?>
                                </button>
                                <?php if ($edit_banner): ?>
                                    <a href="/admin?qlbanner" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Hủy
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Banner List -->
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title"><i class="fas fa-list"></i> Danh sách Banner</h3>
                        <?php if (empty($banners)): ?>
                            <div class="empty-message">
                                <i class="fas fa-images fa-3x" style="color: #999; margin-bottom: 15px;"></i>
                                <p>Chưa có banner nào. Hãy thêm banner đầu tiên!</p>
                            </div>
                        <?php else: ?>
                            <div class="admin-table-wrapper">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Hình ảnh</th>
                                            <th>Tiêu đề</th>
                                            <th>Mô tả</th>
                                            <th>Thứ tự</th>
                                            <th>Trạng thái</th>
                                            <th>Ngày tạo</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($banners as $banner): ?>
                                            <tr class="br-tr">
                                                <td>
                                                    <img src="<?= htmlspecialchars($banner['image_url']) ?>" 
                                                         alt="<?= htmlspecialchars($banner['title']) ?>" 
                                                         class="banner-thumbnail">
                                                </td>
                                                <td>
                                                    <strong><?= htmlspecialchars($banner['title']) ?></strong>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars(substr($banner['description'], 0, 50)) ?>
                                                    <?= strlen($banner['description']) > 50 ? '...' : '' ?>
                                                </td>
                                                <td>
                                                    <span class="status-badge info"><?= $banner['display_order'] ?></span>
                                                </td>
                                                <td>
                                                    <span class="status-badge <?= $banner['status'] === 'active' ? 'success' : 'danger' ?>">
                                                        <?= $banner['status'] === 'active' ? 'Kích hoạt' : 'Tạm dừng' ?>
                                                    </span>
                                                </td>
                                                <td><?= date('d/m/Y H:i', strtotime($banner['created_at'])) ?></td>
                                                <td>
                                                    <div class="actions">
                                                        <a href="qlbanner?edit=<?= $banner['id'] ?>" class="btn btn-info btn-sm">
                                                            <i class="fas fa-edit"></i> Sửa
                                                        </a>
                                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteBannerModal" data-id="<?= $banner['id'] ?>" data-name="<?= htmlspecialchars($banner['title'] ?? 'Banner #' . $banner['id']) ?>">
                                                            <i class="fas fa-trash"></i> Xóa
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>   
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>

<!-- Delete Banner Modal -->
<div class="modal fade" id="deleteBannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Xóa banner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa banner "<span id="deleteBannerName"></span>"?</p>
                    <p>Sau khi xóa, banner sẽ không thể khôi phục.</p>
                    <input type="hidden" name="action" value="delete_banner">
                    <input type="hidden" name="id" id="deleteBannerId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xác nhận xóa</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Delete Banner Modal
    document.getElementById('deleteBannerModal')?.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');
        
        document.getElementById('deleteBannerId').value = id;
        document.getElementById('deleteBannerName').textContent = name;
    });
</script>

