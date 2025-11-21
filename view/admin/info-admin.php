<?php
    require_once __DIR__ . '/../../helpers/url_helper.php';
    include_once(__DIR__ . "/../../controller/cQLthongtin.php");
    $p = new cqlthongtin();
    
    if (isset($_SESSION['user_id'])) {
        $id = $_SESSION['user_id'];
    }
    if (isset($_SESSION['role'])) {
        $idrole = $_SESSION['role'];
    }
    // Check if ID is provided

    $userData = $p->getoneuser($id);
    
    // Check if user exists
    if (!$userData || empty($userData)) {
        require_once __DIR__ . '/../../helpers/url_helper.php';
        $baseUrl = rtrim(getBaseUrl(), '/');
        header("Location: " . $baseUrl . "/index.php");
        exit();
    }
    
    // Lấy phần tử đầu tiên của mảng (vì selectOneUser trả về array of arrays)
    $u = $userData[0];
    
    // Đảm bảo các giá trị có tồn tại với giá trị mặc định
    $u['username'] = isset($u['username']) && !empty($u['username']) ? $u['username'] : '';
    $u['email'] = isset($u['email']) && !empty($u['email']) ? $u['email'] : '';
    $u['phone'] = isset($u['phone']) && !empty($u['phone']) ? $u['phone'] : '';
    $u['address'] = isset($u['address']) && !empty($u['address']) ? $u['address'] : '';
    $u['avatar'] = isset($u['avatar']) && !empty($u['avatar']) ? $u['avatar'] : 'default-avatar.jpg';
    $u['role_id'] = isset($u['role_id']) ? intval($u['role_id']) : 2;
    
    // Kiểm tra và hiển thị thông báo từ session
    $message = '';
    $messageType = '';
    if (isset($_SESSION['info_admin_message'])) {
        $messageType = $_SESSION['info_admin_message'];
        $messageText = isset($_SESSION['info_admin_message_text']) ? $_SESSION['info_admin_message_text'] : '';
        
        // Xóa thông báo khỏi session sau khi lấy
        unset($_SESSION['info_admin_message']);
        unset($_SESSION['info_admin_message_text']);
        
        // Tạo HTML cho thông báo
        if ($messageType === 'success') {
            $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>' . htmlspecialchars($messageText) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        } else {
            $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>' . htmlspecialchars($messageText) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }
    }
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $vt = $_POST['role_id'];

        $avatar = $_POST['avatar_cu']; // mặc định giữ ảnh cũ

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
            $target_dir = $_SERVER['DOCUMENT_ROOT'] . getBasePath() . "/img/";
            $imageFileType = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($imageFileType, $allowed_types)) {
                $unique_name = uniqid('user_', true) . "." . $imageFileType;
                $target_file = $target_dir . $unique_name;

                if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
                    $avatar = $unique_name; // Cập nhật avatar với file mới
                }
            }
        }

        // Xử lý mật khẩu
        if (!empty($_POST['password'])) {
            $password = md5($_POST['password']);
            $result = $p->getupdateuser_with_password($id, $username, $email, $password, $phone, $address, $avatar, $vt);
        } else {
            $result = $p->getupdateuser($id, $username, $email, $phone, $address, $avatar, $vt);
        }

        // Lưu thông báo vào session
        if ($result) {
            $_SESSION['info_admin_message'] = 'success';
            $_SESSION['info_admin_message_text'] = 'Cập nhật thông tin thành công!';
        } else {
            $_SESSION['info_admin_message'] = 'error';
            $_SESSION['info_admin_message_text'] = 'Cập nhật thất bại! Vui lòng thử lại.';
        }
        
        // Redirect về trang admin với URL đầy đủ
        require_once __DIR__ . '/../../helpers/url_helper.php';
        $baseUrl = rtrim(getBaseUrl(), '/');
        header("Location: " . $baseUrl . "/ad");
        exit();
    }

    // Function to safely output data
    function e($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin cá nhân</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= getBasePath() ?>/css/admin-common.css">
    <style>
        /* CSS riêng cho trang thông tin cá nhân - chỉ override nếu cần */
        /* Loại bỏ khung xanh từ container-fluid và page-body-wrapper */
        .container-fluid,
        .page-body-wrapper,
        .content-wrapper {
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
        }
        
        /* Loại bỏ khung xanh từ main-panel */
        .main-panel {
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
        }
        
        /* Loại bỏ shadow và border của admin-card */
        .info-admin-container .admin-card {
            margin-bottom: 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08) !important;
        }
    </style>
</head>
<body>
    <div class="info-admin-container">
        <div class="admin-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">
                    <i class="bi bi-person-fill me-2"></i>Thông tin cá nhân
                </h3>
            </div>
            <div class="card-body">
                        <?php echo $message; ?>
                        
                        <div class="profile-header" style="display: flex !important; justify-content: center !important; align-items: center !important; width: 100% !important; text-align: center !important;">
                            <div class="avatar-wrapper" style="display: flex !important; justify-content: center !important; align-items: center !important; width: 100% !important; margin: 0 auto !important;">
                                <div class="avatar-section" style="display: flex !important; flex-direction: column !important; align-items: center !important; justify-content: center !important; margin: 0 auto !important;">
                                    <div class="avatar-preview" style="width: 140px !important; height: 140px !important; border-radius: 50% !important; overflow: hidden !important; margin: 0 auto !important; display: flex !important; align-items: center !important; justify-content: center !important;">
                                        <img src="<?= getBasePath() ?>/img/<?php echo e($u['avatar']); ?>" alt="Avatar" id="avatarPreview" class="avatar-img" style="width: 100% !important; height: 100% !important; border-radius: 50% !important; object-fit: cover !important; display: block !important; margin: 0 !important; padding: 0 !important;" onerror="this.src='<?= getBasePath() ?>/img/default-avatar.jpg'" />
                                    </div>
                                    <button type="button" class="btn-change-avatar" onclick="document.getElementById('avatar').click();" style="margin-top: 0.5rem !important; margin-bottom: 0.5rem !important;">
                                        <i class="bi bi-camera-fill me-1"></i>Thay ảnh
                                    </button>
                                    <div class="avatar-role-info" style="margin: 0 auto !important;">
                                        <span class="role-label">Vai trò:</span>
                                        <span class="role-text">
                                            <?php 
                                            $roleNames = [1 => 'Admin', 2 => 'Người dùng', 3 => 'Moderator', 4 => 'Kiểm duyệt viên', 5 => 'Doanh nghiệp'];
                                            echo $roleNames[$u['role_id']] ?? 'Người dùng';
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <form method="POST" action="" class="needs-validation profile-form" enctype="multipart/form-data" novalidate>
                            <div class="form-section">
                                <h5 class="section-title">
                                    <i class="bi bi-person-circle me-2"></i>Thông tin cơ bản
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="username" class="form-label">
                                                <i class="bi bi-person me-1"></i>Họ và tên <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="username" name="username"
                                                   value="<?php echo e($u['username']); ?>" required>
                                            <div class="invalid-feedback">
                                                Vui lòng nhập họ và tên.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email" class="form-label">
                                                <i class="bi bi-envelope me-1"></i>Email <span class="text-danger">*</span>
                                            </label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo e($u['email']); ?>" required>
                                            <div class="invalid-feedback">
                                                Vui lòng nhập email hợp lệ.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="phone" class="form-label">
                                                <i class="bi bi-telephone me-1"></i>Số điện thoại
                                            </label>
                                            <input type="text" class="form-control" id="phone" name="phone"
                                                   value="<?php echo e($u['phone']); ?>" placeholder="Nhập số điện thoại">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <input type="hidden" name="role_id" value="<?php echo $u['role_id']; ?>">
                                            <label for="role_id" class="form-label">
                                                <i class="bi bi-shield-check me-1"></i>Vai trò <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="role_id" name="role_id" disabled>
                                                <option value="2" <?php echo $u['role_id'] == 2 ? 'selected' : ''; ?>>Người dùng</option>
                                                <option value="1" <?php echo $u['role_id'] == 1 ? 'selected' : ''; ?>>Admin</option>
                                                <option value="3" <?php echo $u['role_id'] == 3 ? 'selected' : ''; ?>>Moderator</option>
                                                <option value="4" <?php echo $u['role_id'] == 4 ? 'selected' : ''; ?>>Kiểm duyệt viên</option>
                                                <option value="5" <?php echo $u['role_id'] == 5 ? 'selected' : ''; ?>>Doanh nghiệp</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h5 class="section-title">
                                    <i class="bi bi-lock me-2"></i>Bảo mật
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password" class="form-label">
                                                <i class="bi bi-key me-1"></i>Mật khẩu mới
                                            </label>
                                            <input type="password" class="form-control" id="password" name="password" 
                                                   placeholder="Để trống nếu không đổi mật khẩu">
                                            <div class="form-text">
                                                <i class="bi bi-info-circle me-1"></i>Để trống nếu không muốn thay đổi mật khẩu.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="avatar" class="form-label">
                                                <i class="bi bi-image me-1"></i>Ảnh đại diện
                                            </label>
                                            <div class="file-upload-wrapper">
                                                <input type="file" class="form-control file-input" id="avatar" name="avatar" accept="image/*">
                                                <input type="hidden" name="avatar_cu" value="<?php echo e($u['avatar']); ?>">
                                                <small class="file-hint">Chọn ảnh (JPG, PNG, GIF - tối đa 5MB)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h5 class="section-title">
                                    <i class="bi bi-geo-alt me-2"></i>Địa chỉ
                                </h5>
                                
                                <div class="form-group">
                                    <label for="address" class="form-label">
                                        <i class="bi bi-house me-1"></i>Địa chỉ
                                    </label>
                                    <textarea class="form-control" id="address" name="address" rows="3" 
                                              placeholder="Nhập địa chỉ của bạn"><?php echo e($u['address']); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary btn-update">
                                    <i class="bi bi-check-circle me-2"></i>Cập nhật thông tin
                                </button>
                                <?php 
                                require_once __DIR__ . '/../../helpers/url_helper.php';
                                $baseUrl = rtrim(getBaseUrl(), '/');
                                ?>
                                <a href="<?= $baseUrl ?>/ad" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Quay lại
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Form validation script -->
    <script>
    (function () {
        'use strict'
        
        // Fetch all forms we want to apply validation styles to
        var forms = document.querySelectorAll('.needs-validation')
        
        // Loop over them and prevent submission
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    
                    form.classList.add('was-validated')
                }, false)
                
                // Remove was-validated class on input change to reset validation state
                var inputs = form.querySelectorAll('input, select, textarea');
                inputs.forEach(function(input) {
                    input.addEventListener('input', function() {
                        if (form.classList.contains('was-validated')) {
                            // Only remove validation styling if field becomes valid
                            if (input.checkValidity()) {
                                input.classList.remove('is-invalid');
                            }
                        }
                    });
                });
            })
    })()
    
    // Avatar preview on file change
    document.getElementById('avatar').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarPreview').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });
    </script>
</body>
</html>