    <?php
    include_once(__DIR__ . "/../../controller/cQLthongtin.php");

    $message = '';

    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $p = new cqlthongtin();

    $hoten = isset($_POST['ho_ten']) ? trim($_POST['ho_ten']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $repassword = isset($_POST['repassword']) ? $_POST['repassword'] : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';

    // Validate required fields
    if (empty($hoten) || empty($email) || empty($password)) {
        $message = '<div class="alert alert-danger">Vui lòng điền đầy đủ thông tin bắt buộc.</div>';
    } elseif ($password !== $repassword) {
        $message = '<div class="alert alert-danger">Mật khẩu không khớp. Vui lòng nhập lại.</div>';
    } else {
        $mk = md5($password);
        $anh = 'default-avatar.jpg';

        // Xử lý ảnh đại diện nếu có
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
            if ($_FILES['avatar']['name']) {
                require_once __DIR__ . '/../../helpers/url_helper.php';
                $upload_dir = $_SERVER['DOCUMENT_ROOT'] . getBasePath() . '/img/';
                $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array(strtolower($ext), $allowed_extensions)) {
                    $ten_file = $phone ?: time(); // nếu không có số điện thoại thì dùng timestamp
                    $file_name = 'user_' . $ten_file . '_' . time() . '.' . $ext;
                    $target_path = $upload_dir . $file_name;

                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_path)) {
                        $anh = $file_name;
                    } else {
                        $message = '<div class="alert alert-danger">Có lỗi trong việc upload file.</div>';
                    }
                } else {
                    $message = '<div class="alert alert-danger">File ảnh không hợp lệ. Vui lòng chọn file jpg, png hoặc gif.</div>';
                }
            }
        }

        // Nếu không có lỗi ảnh
        if (!isset($message) || $message === '') {
            $result = $p->getinsertuser($hoten, $email, $mk, $phone, $address, $anh);

            if ($result) {
                require_once __DIR__ . '/../../helpers/url_helper.php';
                header("Location: ?taikhoan&status=added");
                exit();
            } else {
                $message = '<div class="alert alert-danger">Không thể thêm người dùng. Email có thể đã tồn tại.</div>';
            }
        }
    }
}

    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thêm Người Dùng Mới</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <?php require_once __DIR__ . '/../../helpers/url_helper.php'; ?>
    <link rel="stylesheet" href="<?= getBasePath() ?>/css/admin-them.css">
    <link rel="stylesheet" href="<?= getBasePath() ?>/css/admin-common.css">
    <style>
        /* CSS riêng cho trang thêm người dùng - chỉ override nếu cần */
        .info-insert-container {
            /* Đã được định nghĩa trong admin-common.css */
        }
        
        .info-insert-container .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: none;
        }
        
        .info-insert-container .card-header {
            background: white;
            border-bottom: 2px solid #f0f0f0;
            padding: 20px;
        }
        
        .info-insert-container .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        
        .info-insert-container .card-body {
            padding: 30px;
        }
    </style>
    </head>

    <body>
    <div class="info-insert-container">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Thêm người dùng mới</h3>
            </div>
            <div class="card-body">
                <?php echo $message; ?>
                
                <form method="POST" action="" enctype="multipart/form-data">
                <div class="row mb-3">
                    <div class="col-md-6">
                    <div class="mb-3">
                        <label for="ho_ten" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ho_ten" name="ho_ten" required>
                    </div>
                    </div>
                    <div class="col-md-6">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
<input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="repassword" class="form-label">Nhập lại mật khẩu <span class="text-danger">*</span></label>
<input type="password" class="form-control" id="repassword" name="repassword" required>
                    </div>
                    </div>
                    <div class="col-md-6">
                    <div class="mb-3">
                        <label for="phone" class="form-label">Số điện thoại</label>
<input type="text" class="form-control" id="phone" name="phone">
                    </div>
                    </div>
                </div>

                <div class="row mb-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="avatar" class="form-label">Ảnh đại diện</label>
<input type="file" class="form-control" id="avatar" name="avatar">
                        <small class="text-muted">Không chọn ảnh sẽ dùng ảnh mặc định</small>
                    </div>
                </div>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Địa chỉ</label>
<textarea class="form-control" id="address" name="address" rows="3"></textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="?taikhoan" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Quay lại
                    </a>
                    <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Thêm mới
                    </button>
                </div>
                </form>
            </div>
            </div>
        </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>

    </html>