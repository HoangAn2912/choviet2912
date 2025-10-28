<?php
// Include security and utility classes
include_once __DIR__ . "/../helpers/SecurityValidator.php";
include_once __DIR__ . "/../helpers/SessionManager.php";
include_once __DIR__ . "/../helpers/ApiResponse.php";
include_once __DIR__ . "/../helpers/RateLimiter.php";
include_once __DIR__ . "/../helpers/Logger.php";

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Start session
SessionManager::start();

// Set up logging
Logger::setLogFile(__DIR__ . '/../logs/livestream_api.log');
Logger::setLevel(Logger::LEVEL_INFO);

try {
    include_once __DIR__ . "/../model/mLivestream.php";
} catch (Exception $e) {
    Logger::error('Model loading error', ['error' => $e->getMessage()]);
    ApiResponse::sendInternalError('Lỗi hệ thống: ' . $e->getMessage());
}

$model = new mLivestream();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Log API request
Logger::apiRequest($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $_REQUEST);

switch ($action) {
    case 'get_livestreams':
        try {
            // Validate input
            $validation = SecurityValidator::validateInputs([
                'status' => [
                    'value' => $_GET['status'] ?? null,
                    'type' => 'enum',
                    'allowed_values' => ['chua_bat_dau', 'dang_dien_ra', 'da_ket_thuc', null]
                ],
                'limit' => [
                    'value' => $_GET['limit'] ?? 20,
                    'type' => 'int',
                    'min' => 1,
                    'max' => 100
                ]
            ]);
            
            if (!$validation['valid']) {
                ApiResponse::sendValidationError($validation['errors']);
            }
            
            $status = $validation['data']['status'];
            $limit = $validation['data']['limit'];
            
            $livestreams = $model->getLivestreams($status, $limit);
            
            Logger::info('Livestreams retrieved via API', [
                'status' => $status,
                'limit' => $limit,
                'count' => count($livestreams)
            ]);
            
            ApiResponse::sendSuccess($livestreams, 'Lấy danh sách livestream thành công');
            
        } catch (Exception $e) {
            Logger::error('Error in get_livestreams', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            ApiResponse::sendInternalError('Có lỗi xảy ra khi lấy danh sách livestream');
        }
        break;
        
    case 'get_user_products':
        $user_id = $_SESSION['user_id'] ?? null;
        if (!$user_id) {
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            break;
        }
        
        include_once __DIR__ . "/../model/mProduct.php";
        $productModel = new mProduct();
        $products = $productModel->getSanPhamByUserId($user_id);
        
        // Sửa tên cột để frontend hiểu
        foreach ($products as &$product) {
            $product['image'] = $product['anh_dau'] ?? $product['image'] ?? '';
        }
        
        echo json_encode(['success' => true, 'products' => $products]);
        break;
        
    case 'update_status':
        try {
            $livestream_id = $_POST['livestream_id'] ?? null;
            $status = $_POST['status'] ?? null;
            
            if (!$livestream_id || !$status) {
                echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
                break;
            }
            
            $result = $model->updateLivestreamStatus($livestream_id, $status);
            echo json_encode(['success' => $result, 'message' => $result ? 'Cập nhật thành công' : 'Cập nhật thất bại']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        break;
        
    case 'pin_product':
        $livestream_id = $_POST['livestream_id'] ?? null;
        $product_id = $_POST['product_id'] ?? null;
        
        if (!$livestream_id || !$product_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            break;
        }
        
        $result = $model->pinProductInLivestream($livestream_id, $product_id);
        echo json_encode(['success' => $result]);
        break;
        
    case 'unpin_product':
        $livestream_id = $_POST['livestream_id'] ?? null;
        
        if (!$livestream_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            break;
        }
        
        $result = $model->unpinProductInLivestream($livestream_id);
        echo json_encode(['success' => $result]);
        break;
        
    case 'get_livestream':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID livestream']);
            break;
        }
        $livestream = $model->getLivestreamById($id);
        if ($livestream) {
            $products = $model->getLivestreamProducts($id);
            $pinned_product = $model->getPinnedProduct($id);
            $stats = $model->getLivestreamStats($id);
            
            echo json_encode([
                'success' => true,
                'livestream' => $livestream,
                'products' => $products,
                'pinned_product' => $pinned_product,
                'stats' => $stats
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy livestream']);
        }
        break;
        
    case 'add_viewer':
        $livestream_id = $_POST['livestream_id'] ?? null;
        $user_id = $_SESSION['user_id'] ?? null;
        
        if (!$livestream_id || !$user_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            break;
        }
        
        $result = $model->addViewer($livestream_id, $user_id);
        if ($result) {
            $viewer_count = $model->getViewerCount($livestream_id);
            echo json_encode(['success' => true, 'viewer_count' => $viewer_count]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
        break;
        
    case 'record_interaction':
        $livestream_id = $_POST['livestream_id'] ?? null;
        $action_type = $_POST['action_type'] ?? null;
        $user_id = $_SESSION['user_id'] ?? null;
        
        if (!$livestream_id || !$action_type || !$user_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            break;
        }
        
        $result = $model->recordInteraction($livestream_id, $user_id, $action_type);
        echo json_encode(['success' => $result]);
        break;
        
    case 'get_chat_messages':
        $livestream_id = $_GET['livestream_id'] ?? null;
        if (!$livestream_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID livestream']);
            break;
        }
        
        // Lấy tin nhắn chat từ database
        $sql = "SELECT lm.*, u.username, u.avatar 
                FROM livestream_messages lm 
                LEFT JOIN users u ON lm.user_id = u.id 
                WHERE lm.livestream_id = ? 
                ORDER BY lm.created_time ASC 
                LIMIT 50";
        
        $conn = (new Connect())->connect();
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $livestream_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        
        echo json_encode(['success' => true, 'messages' => $messages]);
        break;
        
    case 'send_chat_message':
        $livestream_id = $_POST['livestream_id'] ?? null;
        $content = $_POST['content'] ?? null;
        $user_id = $_SESSION['user_id'] ?? null;
        
        if (!$livestream_id || !$content || !$user_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            break;
        }
        
        // Lưu tin nhắn vào database
        $sql = "INSERT INTO livestream_messages (livestream_id, user_id, content, message_type) 
                VALUES (?, ?, ?, 'text')";
        
        $conn = (new Connect())->connect();
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $livestream_id, $user_id, $content);
        
        if ($stmt->execute()) {
            // Lấy thông tin user
            $user_sql = "SELECT username, avatar FROM users WHERE id = ?";
            $user_stmt = $conn->prepare($user_sql);
            $user_stmt->bind_param("i", $user_id);
            $user_stmt->execute();
            $user_result = $user_stmt->get_result();
            $user_data = $user_result->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'message' => [
                    'id' => $conn->insert_id,
                    'content' => $content,
                    'username' => $user_data['username'],
                    'avatar' => $user_data['avatar'],
                    'created_time' => date('Y-m-d H:i:s')
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
        break;
        
    case 'pin_product':
        $livestream_id = $_POST['livestream_id'] ?? null;
        $product_id = $_POST['product_id'] ?? null;
        $user_id = $_SESSION['user_id'] ?? null;
        
        if (!$livestream_id || !$product_id || !$user_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            break;
        }
        
        // Kiểm tra quyền (chỉ streamer mới được ghim)
        $livestream = $model->getLivestreamById($livestream_id);
        if ($livestream['user_id'] != $user_id) {
            echo json_encode(['success' => false, 'message' => 'Không có quyền']);
            break;
        }
        
        $result = $model->pinProduct($livestream_id, $product_id);
        if ($result) {
            $pinned_product = $model->getPinnedProduct($livestream_id);
            
            // Lưu tin nhắn hệ thống
            $sql = "INSERT INTO livestream_messages (livestream_id, user_id, content, message_type, product_id, is_system_message) 
                    VALUES (?, ?, ?, 'product_pin', ?, 1)";
            $conn = (new Connect())->connect();
            $stmt = $conn->prepare($sql);
            $message = "Đã ghim sản phẩm: " . $pinned_product['title'];
            $stmt->bind_param("iisi", $livestream_id, $user_id, $message, $product_id);
            $stmt->execute();
            
            echo json_encode([
                'success' => true,
                'pinned_product' => $pinned_product
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
        break;
        
    case 'get_products':
        $livestream_id = $_GET['livestream_id'] ?? null;
        if (!$livestream_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID livestream']);
            break;
        }
        
        $products = $model->getLivestreamProducts($livestream_id);
        $pinned_product = $model->getPinnedProduct($livestream_id);
        
        echo json_encode([
            'success' => true,
            'products' => $products,
            'pinned_product' => $pinned_product
        ]);
        break;
        
    case 'add_to_cart':
        $livestream_id = $_POST['livestream_id'] ?? null;
        $product_id = $_POST['product_id'] ?? null;
        $quantity = $_POST['quantity'] ?? 1;
        $user_id = $_SESSION['user_id'] ?? null;
        
        if (!$livestream_id || !$product_id || !$user_id) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            break;
        }
        
        $result = $model->addToCart($user_id, $livestream_id, $product_id, $quantity);
        if ($result) {
            $cart = $model->getCart($user_id, $livestream_id);
            echo json_encode(['success' => true, 'cart' => $cart]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
        break;
        
    case 'get_cart':
        $livestream_id = $_GET['livestream_id'] ?? null;
        $user_id = $_SESSION['user_id'] ?? null;
        
        if (!$livestream_id || !$user_id) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            break;
        }
        
        $cart = $model->getCart($user_id, $livestream_id);
        echo json_encode(['success' => true, 'cart' => $cart]);
        break;
        
    case 'update_cart_quantity':
        $livestream_id = $_POST['livestream_id'] ?? null;
        $product_id = $_POST['product_id'] ?? null;
        $quantity = $_POST['quantity'] ?? 1;
        $user_id = $_SESSION['user_id'] ?? null;
        
        if (!$livestream_id || !$product_id || !$user_id) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            break;
        }
        
        $result = $model->updateCartQuantity($user_id, $livestream_id, $product_id, $quantity);
        if ($result) {
            $cart = $model->getCart($user_id, $livestream_id);
            echo json_encode(['success' => true, 'cart' => $cart]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
        break;
        
    case 'remove_from_cart':
        $livestream_id = $_POST['livestream_id'] ?? null;
        $product_id = $_POST['product_id'] ?? null;
        $user_id = $_SESSION['user_id'] ?? null;
        
        if (!$livestream_id || !$product_id || !$user_id) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            break;
        }
        
        $result = $model->removeFromCart($user_id, $livestream_id, $product_id);
        if ($result) {
            $cart = $model->getCart($user_id, $livestream_id);
            echo json_encode(['success' => true, 'cart' => $cart]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
        break;
        
    case 'checkout':
        $livestream_id = $_POST['livestream_id'] ?? null;
        $user_id = $_SESSION['user_id'] ?? null;
        
        if (!$livestream_id || !$user_id) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            break;
        }
        
        $cart = $model->getCart($user_id, $livestream_id);
        if (empty($cart['items'])) {
            echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống']);
            break;
        }
        
        $order_id = $model->createOrder($user_id, $livestream_id, $cart['items']);
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi tạo đơn hàng']);
            break;
        }
        
        $order = $model->getOrder($order_id);
        
        // Tạo URL thanh toán VNPay
        require_once __DIR__ . '/../controller/vnpay/vnpay_config.php';
        
        $vnp_TxnRef = 'LIVE_' . $order['order_code'] . '_' . time();
        $vnp_OrderInfo = "Thanh toan don hang livestream - " . $order['order_code'];
        $vnp_OrderType = "billpayment";
        $vnp_Amount = $order['total_amount'] * 100;
        $vnp_Locale = "vn";
        $vnp_BankCode = "";
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

        $model->updateOrderStatus($order_id, 'pending', $vnp_TxnRef);

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }
        
        echo json_encode([
            'success' => true,
            'payment_url' => $vnp_Url,
            'order_id' => $order_id
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
}

?>
