<?php
    include_once("controller/cQLthongtin.php");
    $p = new cqlthongtin();
    
    // Check if ID is provided
    if (!isset($_GET['ids'])) {
        header("Location: index.php");
        exit();
    }
    if (isset($_SESSION['role'])) {
        $idrole = $_SESSION['role'];
    }
    $id = $_GET['ids'];
    $user = $p->getoneuser($id);
    
    // Check if user exists
    if (!$user) {
        require_once '../helpers/url_helper.php';
        header("Location: " . getBasePath() . "/ad/taikhoan");
        exit();
    }
    
    $message = '';
    
    
    // Process form submission
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
                    $anh = $unique_name;
                }
            }
        }

        // Xử lý mật khẩu
        if (!empty($_POST['password'])) {
    $password = md5($_POST['password']);
    $result = $p->getupdateuser_with_password($id, $username, $email, $password, $phone, $address, $avatar, $vt);
        } else {
            $result = $p->getupdateuser($id, $hoten, $email, $sdt, $dc, $anh, $vt);
        }

        if ($result) {
            require_once '../helpers/url_helper.php';
        header("Location: " . getBasePath() . "/ad/taikhoan");
            exit();
        } else {
            $message = '<div class="alert alert-danger">Không thể cập nhật người dùng. Vui lòng thử lại.</div>';
        }
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
    <title>Quản trị - Chỉnh sửa người dùng</title>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <?php require_once '../helpers/url_helper.php'; ?>
    <link rel="stylesheet" href="<?= getBasePath() ?>/css/infoad.css">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">
                            <i class="bi bi-person-fill me-2"></i>Chỉnh sửa người dùng
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        
                        <?php foreach($user as $u): ?>
                        <div class="avatar-container text-center">
                            <img src="<?= getBasePath() ?>/img/<?php echo e($u['avatar']); ?>" alt="Avatar" class="avatar-img" />
                            <div class="user-info">
                                <h4 class="mb-0"><?php echo e($u['username']); ?></h4>
                                <!-- <span class="user-role"> echo $_SESSION['role_id']; </span> -->
                            </div>
                        </div>
                        
                        <form method="POST" action="" class="needs-validation" enctype="multipart/form-data" novalidate>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Họ và tên <span class="text-danger">*</span></label>
<input type="text" class="form-control" id="username" name="username"
value="<?php echo e($u['username']); ?>" required>
                                        <div class="invalid-feedback">
                                            Vui lòng nhập họ và tên.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo e($u['email']); ?>" required>
                                        <div class="invalid-feedback">
                                            Vui lòng nhập email hợp lệ.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Mật khẩu mới</label>
<input type="password" class="form-control" id="password" name="password" 
                                               placeholder="Để trống nếu không đổi mật khẩu">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Số điện thoại</label>
<input type="text" class="form-control" id="phone" name="phone"
value="<?php echo e($u['phone']); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="role_id" class="form-label">Vai trò <span class="text-danger">*</span></label>
                                    <select class="form-select" id="role_id" name="role_id" required>
                                        <option value="2" <?php echo $idrole == 2 ? 'selected' : ''; ?>>Người dùng</option>
                                        <option value="1" <?php echo $idrole == 1; ?>>Admin</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="avatar" class="form-label">Ảnh đại diện</label>
<input type="file" class="form-control" id="avatar" name="avatar">
<input type="hidden" name="avatar_cu" value="<?php echo e($u['avatar']); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="address" class="form-label">Địa chỉ</label>
<textarea class="form-control" id="address" name="address" rows="3"><?php echo e($u['address']); ?></textarea>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="<?= getBasePath() ?>/ad/taikhoan" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Quay lại
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Cập nhật
                                </button>
                            </div>
                        </form>
                        <?php endforeach; ?>
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
            })
    })()
    </script>
</body>
</html>