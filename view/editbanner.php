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

<style>
        /* CSS riêng cho trang quản lý banner */
        .banner-container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 20px;
            margin-top: 40px;
        }
        
        /* Card background trắng */
        .banner-container .card {
            background: white;
        }
        
        /* Tiêu đề giống các trang khác */
        .banner-container .card-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 20px;
        }
    </style>

   <div class="banner-container">
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?= $success_message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> <?= $edit_banner ? 'Cập nhật' : 'Thêm mới' ?>
                                </button>
                                <?php if ($edit_banner): ?>
                                    <a href="/ad/edit-banner" class="btn btn-secondary">
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
                            <div class="text-center py-5">
                                <i class="fas fa-images fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Chưa có banner nào. Hãy thêm banner đầu tiên!</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
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
                                                    <span class="badge bg-info"><?= $banner['display_order'] ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= $banner['status'] === 'active' ? 'success' : 'secondary' ?>">
                                                        <?= $banner['status'] === 'active' ? 'Kích hoạt' : 'Tạm dừng' ?>
                                                    </span>
                                                </td>
                                                <td><?= date('d/m/Y H:i', strtotime($banner['created_at'])) ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="qlbanner?edit=<?= $banner['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                            Sửa<i class="fas fa-edit"></i>
                                                        </a>
                                                        <form method="POST" style="display: inline;" 
                                                              onsubmit="return confirm('Bạn có chắc chắn muốn xóa banner này?')">
                                                            <input type="hidden" name="action" value="delete_banner">
                                                            <input type="hidden" name="id" value="<?= $banner['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                Xóa<i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
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


