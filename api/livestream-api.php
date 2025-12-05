<?php
// Tắt hiển thị lỗi trên màn hình để tránh làm hỏng JSON response
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
// Log all errors to file
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/api_errors.log');
error_reporting(E_ALL);

// Đảm bảo không có output nào trước JSON
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');

// Include Security helpers
include_once __DIR__ . "/../helpers/Security.php";
include_once __DIR__ . "/../helpers/RateLimiter.php";

// Khởi tạo session bảo mật
Security::initSecureSession();

// Rate limiting cho API - 100 requests/phút
RateLimiter::middleware('livestream_api', 100, 60);

try {
    include_once __DIR__ . "/../model/mLivestream.php";
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Model Error: ' . $e->getMessage()]);
    exit;
}

// Lưu JSON body để dùng lại (php://input chỉ đọc được một lần)
$jsonBody = null;
$jsonBodyData = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    // Kiểm tra nếu request là JSON (application/json)
    if (strpos($contentType, 'application/json') !== false || empty($_POST)) {
        $jsonBody = file_get_contents('php://input');
        if (!empty($jsonBody)) {
            $jsonBodyData = json_decode($jsonBody, true);
        }
    }
}

// Validate CSRF token cho POST requests
// Bỏ qua CSRF cho các request từ Node.js server (có user_id trong POST và không có session)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Đọc action từ POST hoặc JSON body
    $action = $_POST['action'] ?? ($jsonBodyData['action'] ?? '');
    
    $hasUserIdInPost = isset($_POST['user_id']);
    $hasSession = isset($_SESSION['user_id']);
    
    // Nếu là request từ Node.js (có user_id trong POST nhưng không có session), bỏ qua CSRF
    // Hoặc nếu là action record_interaction từ Node.js
    // Hoặc nếu có session user_id và action là add_product/update_product/batch_update_products (đã được authenticate qua session)
    $skipCSRF = ($hasUserIdInPost && !$hasSession && $action === 'record_interaction') ||
                ($hasSession && ($action === 'add_product' || $action === 'update_product' || $action === 'batch_update_products'));
    
    if (!$skipCSRF) {
        // Lấy CSRF token từ POST hoặc JSON body hoặc header
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($jsonBodyData['csrf_token'] ?? '');
        
        if (!Security::validateCSRFToken($token)) {
            echo json_encode(['success' => false, 'message' => 'CSRF token không hợp lệ. Vui lòng refresh trang.']);
            exit;
        }
    }
}

$model = new mLivestream();

// Đọc action từ GET, POST hoặc JSON body
$action = $_GET['action'] ?? $_POST['action'] ?? ($jsonBodyData['action'] ?? '');

switch ($action) {
    case 'get_livestreams':
        $status = $_GET['status'] ?? null;
        $livestreams = $model->getLivestreams($status);
        echo json_encode(['success' => true, 'data' => $livestreams]);
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
        
    case 'update_livestream_info':
        // Cập nhật tiêu đề và mô tả livestream từ streamer panel
        $livestream_id = $_POST['livestream_id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (!$livestream_id || $title === '') {
            echo json_encode(['success' => false, 'message' => 'Tiêu đề không được để trống']);
            break;
        }

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            break;
        }

        // Chỉ cho phép chủ livestream sửa
        $livestream = $model->getLivestreamById($livestream_id);
        if (!$livestream || (int)$livestream['user_id'] !== (int)$_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền sửa livestream này']);
            break;
        }

        $sql = "UPDATE livestream SET title = ?, description = ? WHERE id = ?";
        $conn = (new Connect())->connect();
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $title, $description, $livestream_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật livestream thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể cập nhật livestream']);
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
        // Cho phép user_id từ POST (khi gọi từ Node.js) hoặc từ session (khi gọi từ frontend)
        $user_id = $_POST['user_id'] ?? $_SESSION['user_id'] ?? null;
        
        if (!$livestream_id || !$action_type || !$user_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            break;
        }
        
        // Chỉ chấp nhận user_id là integer (user đã đăng nhập)
        // Guest user (có ID dạng string như 'viewer_xxx') không thể thích vì database yêu cầu integer
        $user_id_int = is_numeric($user_id) ? (int)$user_id : null;
        if (!$user_id_int || $user_id_int <= 0) {
            echo json_encode(['success' => false, 'message' => 'Chỉ user đã đăng nhập mới có thể thích']);
            break;
        }
        
        // Không giới hạn số lần thích - mỗi lần nhấn là 1 lượt thích mới
        $result = $model->recordInteraction($livestream_id, $user_id_int, $action_type);
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
        if (is_array($result) && isset($result['success'])) {
            if ($result['success']) {
                $cart = $model->getCart($user_id, $livestream_id);
                echo json_encode(['success' => true, 'cart' => $cart]);
            } else {
                echo json_encode(['success' => false, 'message' => $result['message'] ?? 'Có lỗi xảy ra']);
            }
        } else {
            // Fallback cho code cũ
            if ($result) {
                $cart = $model->getCart($user_id, $livestream_id);
                echo json_encode(['success' => true, 'cart' => $cart]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
            }
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
        
    case 'update_cart_quantity_by_item':
        $item_id = $_POST['item_id'] ?? null;
        $quantity = $_POST['quantity'] ?? null;
        $livestream_id = $_POST['livestream_id'] ?? null;
        $user_id = $_SESSION['user_id'] ?? null;
        
        if (!$item_id || !$quantity || !$livestream_id || !$user_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            break;
        }
        
        $new_quantity = (int)$quantity;
        
        // Sử dụng method public để cập nhật số lượng
        $result = $model->updateCartItemQuantity($item_id, $new_quantity, $user_id, $livestream_id);
        
        // Xử lý response mới (có thể là array hoặc boolean)
        if (is_array($result)) {
            if ($result['success']) {
                $cart = $model->getCart($user_id, $livestream_id);
                echo json_encode(['success' => true, 'cart' => $cart]);
            } else {
                echo json_encode(['success' => false, 'message' => $result['message'] ?? 'Có lỗi xảy ra khi cập nhật số lượng']);
            }
        } else {
            // Fallback cho code cũ
            if ($result) {
                $cart = $model->getCart($user_id, $livestream_id);
                echo json_encode(['success' => true, 'cart' => $cart]);
            } else {
                error_log("Update cart quantity failed for item_id: $item_id, quantity: $new_quantity");
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi cập nhật số lượng']);
            }
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
        
    case 'remove_from_cart_by_item':
        $item_id = $_POST['item_id'] ?? null;
        $livestream_id = $_POST['livestream_id'] ?? null;
        $user_id = $_SESSION['user_id'] ?? null;
        
        if (!$item_id || !$livestream_id || !$user_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            break;
        }
        
        $sql = "DELETE FROM livestream_cart_items WHERE id = ? AND user_id = ? AND livestream_id = ?";
        $conn = (new Connect())->connect();
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $item_id, $user_id, $livestream_id);
        $result = $stmt->execute();
        
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
        $payment_method = $_POST['payment_method'] ?? 'vnpay';
        
        if (!$livestream_id || !$user_id) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            break;
        }
        
        $cart = $model->getCart($user_id, $livestream_id);
        if (empty($cart['items'])) {
            echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống']);
            break;
        }
        
        // Kiểm tra số dư nếu thanh toán bằng ví (từ transfer_accounts)
        // Chỉ check số dư nếu đơn hàng > 0đ
        if ($payment_method === 'wallet' && $cart['total'] > 0) {
            try {
                // Sử dụng config từ mConnect thay vì hardcode
                require_once __DIR__ . '/../helpers/url_helper.php';
                $host = config('db_host', 'localhost');
                $user = config('db_user', 'admin');
                $pass = config('db_pass', '123456');
                $dbname = config('db_name', 'choviet29');
                
                $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(PDO::ATTR_TIMEOUT, 10);
                
                $balance_sql = "SELECT balance FROM transfer_accounts WHERE user_id = ?";
                $balance_stmt = $pdo->prepare($balance_sql);
                $balance_stmt->execute([$user_id]);
                $account = $balance_stmt->fetch(PDO::FETCH_ASSOC);
                
                // Xử lý balance an toàn - tạo tài khoản nếu chưa có
                if (!$account) {
                    // Tạo tài khoản mới với số dư 0
                    $account_number = 'ACC' . str_pad($user_id, 8, '0', STR_PAD_LEFT);
                    $create_sql = "INSERT INTO transfer_accounts (account_number, user_id, balance) VALUES (?, ?, 0)";
                    $create_stmt = $pdo->prepare($create_sql);
                    $create_stmt->execute([$account_number, $user_id]);
                    $balance = 0.0;
                } else {
                    $balance = floatval($account['balance'] ?? 0);
                }
                
                $cart_total = floatval($cart['total']);
                
                if ($balance < $cart_total) {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Số dư tài khoản không đủ để thanh toán. Số dư hiện tại: ' . number_format($balance) . ' VNĐ, cần: ' . number_format($cart_total) . ' VNĐ'
                    ]);
                    break;
                }
            } catch (PDOException $e) {
                // Log chi tiết hơn để debug
                $error_details = [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'sql_state' => $e->errorInfo[0] ?? null,
                    'driver_code' => $e->errorInfo[1] ?? null,
                    'driver_message' => $e->errorInfo[2] ?? null,
                    'user_id' => $user_id,
                    'cart_total' => $cart['total'] ?? 0,
                    'timestamp' => date('Y-m-d H:i:s')
                ];
                
                error_log("Error checking balance from transfer_accounts: " . json_encode($error_details, JSON_UNESCAPED_UNICODE));
                echo json_encode([
                    'success' => false, 
                    'message' => 'Lỗi khi kiểm tra số dư tài khoản: ' . $e->getMessage()
                ]);
                break;
            }
        }
        
        // Lấy thông tin địa chỉ
        $address_data = [
            'full_name' => $_POST['full_name'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'province' => $_POST['province'] ?? '',
            'district' => $_POST['district'] ?? '',
            'ward' => $_POST['ward'] ?? '',
            'street' => $_POST['street'] ?? '',
            'address' => $_POST['address'] ?? ''
        ];
        
        $order_result = $model->createOrder($user_id, $livestream_id, $cart['items'], $payment_method, $address_data);
        
        // Xử lý response mới (có thể là array hoặc order_id)
        if (is_array($order_result)) {
            if (!$order_result['success']) {
                echo json_encode(['success' => false, 'message' => $order_result['message'] ?? 'Có lỗi xảy ra khi tạo đơn hàng']);
                break;
            }
            $order_id = $order_result['order_id'] ?? null;
        } else {
            $order_id = $order_result;
        }
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi tạo đơn hàng']);
            break;
        }
        
        $order = $model->getOrder($order_id);
        
        // Trả về thông tin đơn hàng để frontend có thể gửi qua WebSocket
        // Frontend sẽ gửi message order_created qua WebSocket sau khi nhận response
        
        if ($payment_method === 'vnpay') {
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
                'order_id' => $order_id,
                'order_code' => $order['order_code'] ?? '',
                'total_amount' => $order['total_amount'] ?? 0
            ]);
        } else if ($payment_method === 'wallet') {
            // Thanh toán bằng ví
            // Nếu đơn hàng = 0đ, không cần trừ tiền ví, chỉ cập nhật trạng thái
            if ($order['total_amount'] <= 0) {
                // Cập nhật trạng thái đơn hàng thành confirmed
                $model->updateOrderStatus($order_id, 'confirmed', null);
                // Trừ số lượng sản phẩm
                $model->deductStockQuantity($livestream_id, $order_id);
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Thanh toán thành công',
                    'order_id' => $order_id,
                    'order_code' => $order['order_code'] ?? '',
                    'total_amount' => $order['total_amount'] ?? 0,
                    'redirect_url' => 'index.php?my-orders'
                ]);
            } else {
                // Chỉ gọi processWalletPayment() nếu đơn hàng > 0đ
                $result = $model->processWalletPayment($order_id, $user_id);
                if ($result) {
                    // Trừ số lượng sản phẩm sau khi thanh toán thành công
                    $model->deductStockQuantity($livestream_id, $order_id);
                    
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Thanh toán thành công',
                        'order_id' => $order_id,
                        'order_code' => $order['order_code'] ?? '',
                        'total_amount' => $order['total_amount'] ?? 0,
                        'redirect_url' => 'index.php?my-orders'
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi thanh toán']);
                }
            }
        } else if ($payment_method === 'cash') {
            // Giao trực tiếp
            echo json_encode([
                'success' => true, 
                'message' => 'Đơn hàng đã được tạo. Bạn sẽ thanh toán khi nhận hàng.',
                'order_id' => $order_id,
                'order_code' => $order['order_code'] ?? '',
                'total_amount' => $order['total_amount'] ?? 0,
                'redirect_url' => 'index.php?my-orders'
            ]);
        }
        break;
        
    case 'remove_product':
        $livestream_id = $_POST['livestream_id'] ?? null;
        $product_id = $_POST['product_id'] ?? null;
        $user_id = $_SESSION['user_id'] ?? null;
        
        if (!$livestream_id || !$product_id || !$user_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            break;
        }
        
        // Kiểm tra quyền (chỉ streamer mới được xóa)
        $livestream = $model->getLivestreamById($livestream_id);
        if ($livestream['user_id'] != $user_id) {
            echo json_encode(['success' => false, 'message' => 'Không có quyền']);
            break;
        }
        
        $result = $model->removeProductFromLivestream($livestream_id, $product_id);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Đã xóa sản phẩm khỏi livestream']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi xóa sản phẩm']);
        }
        break;
        
    case 'get_available_products':
        $user_id = $_SESSION['user_id'] ?? null;
        $livestream_id = $_GET['livestream_id'] ?? null;
        
        if (!$user_id) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            break;
        }
        
        // Lấy danh sách sản phẩm của user
        include_once __DIR__ . "/../model/mProduct.php";
        $mProduct = new mProduct();
        $products = $mProduct->getProductsByUserId($user_id);
        
        // Nếu có livestream_id, lấy thông tin sản phẩm đã có trong livestream
        $livestream_products = [];
        if ($livestream_id) {
            $livestream_products = $model->getLivestreamProducts($livestream_id);
        }
        
        // Tạo map để tra cứu nhanh
        $livestream_products_map = [];
        foreach ($livestream_products as $lp) {
            $livestream_products_map[$lp['product_id']] = [
                'special_price' => $lp['special_price'],
                'stock_quantity' => $lp['stock_quantity'],
                'is_in_livestream' => true
            ];
        }
        
        // Gắn thông tin livestream vào từng sản phẩm
        foreach ($products as &$product) {
            if (isset($livestream_products_map[$product['id']])) {
                $product['is_in_livestream'] = true;
                $product['livestream_special_price'] = $livestream_products_map[$product['id']]['special_price'];
                $product['livestream_stock_quantity'] = $livestream_products_map[$product['id']]['stock_quantity'];
            } else {
                $product['is_in_livestream'] = false;
                $product['livestream_special_price'] = null;
                $product['livestream_stock_quantity'] = null;
            }
        }
        
        echo json_encode(['success' => true, 'products' => $products]);
        break;
        
    case 'unpin_product':
        $livestream_id = $_POST['livestream_id'] ?? null;
        $product_id = $_POST['product_id'] ?? null;
        $user_id = $_SESSION['user_id'] ?? null;
        
        if (!$livestream_id || !$product_id || !$user_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            break;
        }
        
        // Kiểm tra quyền (chỉ streamer mới được bỏ ghim)
        $livestream = $model->getLivestreamById($livestream_id);
        if ($livestream['user_id'] != $user_id) {
            echo json_encode(['success' => false, 'message' => 'Không có quyền']);
            break;
        }
        
        $result = $model->unpinProduct($livestream_id, $product_id);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Đã bỏ ghim sản phẩm']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi bỏ ghim sản phẩm']);
        }
        break;
        
    case 'add_product':
        try {
            $livestream_id = $_POST['livestream_id'] ?? null;
            $product_id = $_POST['product_id'] ?? null;
            $special_price = $_POST['special_price'] ?? null;
            $stock_quantity = $_POST['stock_quantity'] ?? 1;
            $user_id = $_SESSION['user_id'] ?? null;
            
            if (!$livestream_id || !$product_id || !$user_id) {
                echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
                break;
            }
            
            // Kiểm tra quyền (chỉ streamer mới được thêm sản phẩm)
            $livestream = $model->getLivestreamById($livestream_id);
            if (!$livestream) {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy livestream']);
                break;
            }
            
            if ($livestream['user_id'] != $user_id) {
                echo json_encode(['success' => false, 'message' => 'Không có quyền']);
                break;
            }
            
            // Xử lý giá đặc biệt: nếu là chuỗi rỗng thì set thành null
            $update_special_price = isset($_POST['special_price']); // Kiểm tra xem có truyền special_price không
            if ($special_price === '') {
                $special_price = null;
            } else if ($special_price !== null) {
                $special_price = floatval($special_price);
            }
            
            // Xử lý số lượng: nếu là chuỗi rỗng thì set thành null
            if ($stock_quantity === '') {
                $stock_quantity = null;
            } else if ($stock_quantity !== null) {
                $stock_quantity = intval($stock_quantity);
            }
            
            // Kiểm tra xem sản phẩm đã có trong livestream chưa
            $existing_products = $model->getLivestreamProducts($livestream_id);
            $product_exists = false;
            foreach ($existing_products as $p) {
                if ($p['product_id'] == $product_id) {
                    $product_exists = true;
                    break;
                }
            }
            
            if ($product_exists) {
                // Cập nhật sản phẩm đã có - truyền thêm flag update_special_price
                $result = $model->updateProductInLivestream($livestream_id, $product_id, $special_price, $stock_quantity, $update_special_price);
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Đã cập nhật sản phẩm trong livestream']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi cập nhật sản phẩm']);
                }
            } else {
                // Thêm sản phẩm mới
                $result = $model->addProductToLivestream($livestream_id, $product_id, $special_price, $stock_quantity);
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Đã thêm sản phẩm vào livestream']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi thêm sản phẩm']);
                }
            }
        } catch (Exception $e) {
            error_log("Error in add_product: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        break;
        
    case 'update_product':
        $livestream_id = $_POST['livestream_id'] ?? null;
        $product_id = $_POST['product_id'] ?? null;
        $special_price = $_POST['special_price'] ?? null;
        $stock_quantity = $_POST['stock_quantity'] ?? null;
        $user_id = $_SESSION['user_id'] ?? null;
        
        if (!$livestream_id || !$product_id || !$user_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            break;
        }
        
        // Kiểm tra quyền (chỉ streamer mới được cập nhật sản phẩm)
        $livestream = $model->getLivestreamById($livestream_id);
        if ($livestream['user_id'] != $user_id) {
            echo json_encode(['success' => false, 'message' => 'Không có quyền']);
            break;
        }
        
        // Xử lý giá đặc biệt: nếu là chuỗi rỗng thì set thành null
        $update_special_price = isset($_POST['special_price']); // Kiểm tra xem có truyền special_price không
        if ($special_price === '') {
            $special_price = null;
        } else if ($special_price !== null) {
            $special_price = floatval($special_price);
        }
        
        // Xử lý số lượng: nếu là chuỗi rỗng thì set thành null
        if ($stock_quantity === '') {
            $stock_quantity = null;
        } else if ($stock_quantity !== null) {
            $stock_quantity = intval($stock_quantity);
        }
        
        $result = $model->updateProductInLivestream($livestream_id, $product_id, $special_price, $stock_quantity, $update_special_price);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Đã cập nhật sản phẩm trong livestream']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi cập nhật sản phẩm']);
        }
        break;
        
    case 'join_livestream':
        $livestream_id = $_POST['livestream_id'] ?? null;
        $user_id = $_SESSION['user_id'] ?? 0;
        $session_id = session_id();
        
        if (!$livestream_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin livestream']);
            break;
        }
        
        $result = $model->recordViewerJoin($livestream_id, $user_id, $session_id);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Đã ghi nhận viewer join']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
        break;
        
    case 'cancel_order':
        try {
            $order_id = $_POST['order_id'] ?? null;
            $user_id = $_SESSION['user_id'] ?? null;
            
            if (!$order_id || !$user_id) {
                echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
                break;
            }
            
            $result = $model->cancelOrder($order_id, $user_id);
            if ($result) {
                // Lấy thông tin đơn hàng để kiểm tra phương thức thanh toán
                try {
                    $order = $model->getOrderInfo($order_id, $user_id);
                    
                    $message = 'Đã hủy đơn hàng thành công';
                    if ($order && $order['payment_method'] == 'wallet') {
                        $message .= '. Số tiền ' . number_format($order['total_amount']) . ' đ đã được hoàn lại vào ví của bạn.';
                    }
                    
                    echo json_encode(['success' => true, 'message' => $message]);
                } catch (Exception $e) {
                    error_log("Error getting order info after cancel: " . $e->getMessage());
                    echo json_encode(['success' => true, 'message' => 'Đã hủy đơn hàng thành công']);
                }
            } else {
                // Kiểm tra xem có phải lỗi do hoàn tiền không
                try {
                    $order = $model->getOrderInfo($order_id, $user_id);
                    
                    if ($order && $order['payment_method'] == 'wallet') {
                        // Kiểm tra xem có hoàn tiền thành công không
                        $account = $model->checkTransferAccount($user_id);
                        
                        if ($account) {
                            // Nếu có tài khoản transfer_accounts, có thể đã hoàn tiền thành công
                            echo json_encode(['success' => true, 'message' => 'Đã hủy đơn hàng thành công. Số tiền ' . number_format($order['total_amount']) . ' đ đã được hoàn lại vào ví của bạn.']);
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Không thể hủy đơn hàng hoặc đơn hàng không tồn tại']);
                        }
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Không thể hủy đơn hàng hoặc đơn hàng không tồn tại']);
                    }
                } catch (Exception $e) {
                    error_log("Error checking refund status: " . $e->getMessage());
                    echo json_encode(['success' => false, 'message' => 'Không thể hủy đơn hàng hoặc đơn hàng không tồn tại']);
                }
            }
        } catch (Exception $e) {
            error_log("Cancel order API error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
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
        
        // Kiểm tra quyền (chỉ streamer mới được ghim sản phẩm)
        $livestream = $model->getLivestreamById($livestream_id);
        if ($livestream['user_id'] != $user_id) {
            echo json_encode(['success' => false, 'message' => 'Không có quyền ghim sản phẩm']);
            break;
        }
        
        // Kiểm tra sản phẩm hiện tại có được ghim không
        $products = $model->getLivestreamProducts($livestream_id);
        $product = null;
        foreach ($products as $p) {
            if ($p['product_id'] == $product_id) {
                $product = $p;
                break;
            }
        }
        
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại trong livestream']);
            break;
        }
        
        $is_pinned = $product['is_pinned'];
        
        if ($is_pinned) {
            // Bỏ ghim sản phẩm
            $result = $model->unpinProduct($livestream_id, $product_id);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Đã bỏ ghim sản phẩm']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi bỏ ghim sản phẩm']);
            }
        } else {
            // Ghim sản phẩm
            $result = $model->pinProduct($livestream_id, $product_id);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Đã ghim sản phẩm']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi ghim sản phẩm']);
            }
        }
        break;

    case 'get_pinned_product':
        $livestream_id = $_GET['livestream_id'] ?? null;
        if (!$livestream_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu livestream_id']);
            break;
        }
        
        try {
            $pinned_product = $model->getPinnedProduct($livestream_id);
            echo json_encode(['success' => true, 'pinned_product' => $pinned_product]);
        } catch (Exception $e) {
            error_log("Get pinned product error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi khi lấy sản phẩm ghim: ' . $e->getMessage()]);
        }
        break;

    case 'get_products_status':
        $livestream_id = $_GET['livestream_id'] ?? null;
        if (!$livestream_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu livestream_id']);
            break;
        }
        
        try {
            $products = $model->getLivestreamProducts($livestream_id);
            // Sắp xếp: ghim trước, sau đó theo id (thứ tự thêm vào)
            usort($products, function($a, $b) {
                if ($a['is_pinned'] && !$b['is_pinned']) return -1;
                if (!$a['is_pinned'] && $b['is_pinned']) return 1;
                return $a['id'] - $b['id'];
            });
            
            // Thêm thứ tự hiển thị (1, 2, 3, ...)
            foreach ($products as $index => &$product) {
                $product['display_order'] = $index + 1;
            }
            
            echo json_encode(['success' => true, 'products' => $products]);
        } catch (Exception $e) {
            error_log("Get products status error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi khi lấy trạng thái sản phẩm: ' . $e->getMessage()]);
        }
        break;

    case 'get_realtime_stats':
        $livestream_id = $_GET['livestream_id'] ?? null;
        if (!$livestream_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu livestream_id']);
            break;
        }
        
        try {
            $stats = $model->getRealTimeStats($livestream_id);
            echo json_encode(['success' => true, 'stats' => $stats]);
        } catch (Exception $e) {
            error_log("Get realtime stats error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi khi lấy thống kê: ' . $e->getMessage()]);
        }
        break;

    case 'batch_update_products':
        try {
            $user_id = $_SESSION['user_id'] ?? null;
            
            // Sử dụng JSON body đã đọc ở đầu file hoặc đọc từ POST
            if ($jsonBodyData && !empty($jsonBodyData)) {
                $livestream_id = $jsonBodyData['livestream_id'] ?? null;
                $products = $jsonBodyData['products'] ?? [];
            } else {
                $livestream_id = $_POST['livestream_id'] ?? null;
                $products = json_decode($_POST['products'] ?? '[]', true);
            }
            
            if (!$livestream_id || empty($products) || !$user_id) {
                echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
                break;
            }
            
            // Kiểm tra quyền
            $livestream = $model->getLivestreamById($livestream_id);
            if (!$livestream) {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy livestream']);
                break;
            }
            
            if ($livestream['user_id'] != $user_id) {
                echo json_encode(['success' => false, 'message' => 'Không có quyền']);
                break;
            }
            
            // Xử lý từng sản phẩm
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            
            foreach ($products as $productData) {
                $product_id = $productData['product_id'] ?? null;
                $special_price = $productData['special_price'] ?? null;
                $stock_quantity = $productData['stock_quantity'] ?? null;
                
                if (!$product_id) {
                    $errorCount++;
                    continue;
                }
                
                // Xử lý giá đặc biệt
                $update_special_price = isset($productData['special_price']);
                $final_special_price = null;
                if ($special_price !== null && $special_price !== '') {
                    $final_special_price = floatval($special_price);
                }
                
                // Xử lý số lượng
                $final_stock_quantity = null;
                if ($stock_quantity !== null && $stock_quantity !== '') {
                    $final_stock_quantity = intval($stock_quantity);
                }
                
                // Kiểm tra sản phẩm đã có trong livestream chưa
                $existing_products = $model->getLivestreamProducts($livestream_id);
                $product_exists = false;
                foreach ($existing_products as $p) {
                    if ($p['product_id'] == $product_id) {
                        $product_exists = true;
                        break;
                    }
                }
                
                if ($product_exists) {
                    // Cập nhật sản phẩm đã có
                    // Nếu update_special_price = true, luôn cập nhật special_price (kể cả set về NULL)
                    $result = $model->updateProductInLivestream($livestream_id, $product_id, $final_special_price, $final_stock_quantity, $update_special_price);
                    if ($result) {
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                } else {
                    // Thêm sản phẩm mới
                    $result = $model->addProductToLivestream($livestream_id, $product_id, $final_special_price, $final_stock_quantity);
                    if ($result) {
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                }
            }
            
            if ($successCount > 0) {
                $totalProducts = count($products);
                if ($successCount === $totalProducts) {
                    $message = "Đã cập nhật {$successCount} sản phẩm thành công";
                } else {
                    $message = "Đã cập nhật {$successCount}/{$totalProducts} sản phẩm";
                    if ($errorCount > 0) {
                        $message .= ". Có {$errorCount} sản phẩm lỗi";
                    }
                }
                echo json_encode(['success' => true, 'message' => $message]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không thể cập nhật sản phẩm nào']);
            }
        } catch (Exception $e) {
            error_log("Error in batch_update_products: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
}

?>
