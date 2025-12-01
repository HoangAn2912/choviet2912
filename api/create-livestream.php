<?php
// Tắt hiển thị lỗi để tránh corrupt JSON response
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

try {
    include_once __DIR__ . "/../model/mLivestream.php";
    include_once __DIR__ . "/../model/mConnect.php";
    include_once __DIR__ . "/../model/mLivestreamPackage.php";
    
    $model = new mLivestream();
    $packageModel = new mLivestreamPackage();
    
    $permission = $packageModel->checkLivestreamPermission($_SESSION['user_id']);
    if (!$permission['has_permission'] || empty($permission['registration'])) {
        echo json_encode([
            'success' => false,
            'message' => $permission['message'] ?? 'Gói livestream đã hết hạn. Vui lòng gia hạn gói live để tiếp tục.'
        ]);
        exit;
    }
    
    $registration = $permission['registration'];
    $registrationStart = new DateTime($registration['registration_date']);
    $registrationEnd = new DateTime($registration['expiry_date']);
    
    // Xử lý upload ảnh
    $imageName = 'default-live.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../img/'; // Đường dẫn tuyệt đối
        $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        // Kiểm tra định dạng file
        if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $imageName = 'livestream_' . uniqid() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $imageName;
            
            // Kiểm tra thư mục có tồn tại không
            if (!is_dir($uploadDir)) {
                echo json_encode(['success' => false, 'message' => 'Thư mục upload không tồn tại']);
                exit;
            }
            
            // Kiểm tra quyền ghi
            if (!is_writable($uploadDir)) {
                echo json_encode(['success' => false, 'message' => 'Không có quyền ghi vào thư mục upload']);
                exit;
            }
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                echo json_encode(['success' => false, 'message' => 'Lỗi upload ảnh: ' . error_get_last()['message']]);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Chỉ cho phép file ảnh JPG, PNG, GIF']);
            exit;
        }
    }

    $startInput = trim($_POST['start_time'] ?? '');
    if ($startInput === '') {
        $startDate = new DateTime();
    } else {
        $startDate = new DateTime($startInput);
    }
    
    if ($startDate < $registrationStart || $startDate > $registrationEnd) {
        echo json_encode([
            'success' => false,
            'message' => 'Thời gian bắt đầu phải nằm trong hiệu lực gói livestream. Vui lòng gia hạn gói live.'
        ]);
        exit;
    }
    
    $endInput = trim($_POST['end_time'] ?? '');
    $endDate = null;
    if ($endInput !== '') {
        $endDate = new DateTime($endInput);
        if ($endDate <= $startDate) {
            echo json_encode([
                'success' => false,
                'message' => 'Thời gian kết thúc phải sau thời gian bắt đầu.'
            ]);
            exit;
        }
        if ($endDate > $registrationEnd) {
            echo json_encode([
                'success' => false,
                'message' => 'Thời gian kết thúc vượt quá thời hạn gói livestream. Vui lòng gia hạn gói live.'
            ]);
            exit;
        }
    }

    $data = [
        'user_id' => $_SESSION['user_id'],
        'title' => $_POST['title'] ?? '',
        'description' => $_POST['description'] ?? '',
        'start_time' => $startDate->format('Y-m-d H:i:s'),
        'end_time' => $endDate ? $endDate->format('Y-m-d H:i:s') : null,
        'status' => 'chua_bat_dau',
        'image' => $imageName
    ];

    $livestream_id = $model->createLivestream($data);
    
    if ($livestream_id) {
        // Thêm sản phẩm nếu có
        if (isset($_POST['products'])) {
            $products = json_decode($_POST['products'], true);
            if (is_array($products)) {
                foreach ($products as $product_id) {
                    $model->addProductToLivestream($livestream_id, $product_id);
                }
            }
        }

        echo json_encode([
            'success' => true, 
            'message' => 'Tạo livestream thành công',
            'livestream_id' => $livestream_id,
            'redirect_url' => 'index.php?streamer&id=' . $livestream_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi tạo livestream']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
