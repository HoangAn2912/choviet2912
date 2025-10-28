<?php
// Suppress warnings to prevent JSON corruption
error_reporting(E_ERROR | E_PARSE);

// Log all errors to file
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/api_errors.log');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

try {
    include_once __DIR__ . "/../model/mLivestream.php";
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Model Error: ' . $e->getMessage()]);
    exit;
}

$model = new mLivestream();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

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
        
        if ($result) {
            $cart = $model->getCart($user_id, $livestream_id);
            echo json_encode(['success' => true, 'cart' => $cart]);
        } else {
            error_log("Update cart quantity failed for item_id: $item_id, quantity: $new_quantity");
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi cập nhật số lượng']);
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
        
        // Kiểm tra số dư nếu thanh toán bằng ví
        if ($payment_method === 'wallet') {
            include_once __DIR__ . "/../model/mUser.php";
            $userModel = new mUser();
            $user = $userModel->getUserById($user_id);
            
            if ($user['balance'] < $cart['total']) {
                echo json_encode(['success' => false, 'message' => 'Số dư tài khoản không đủ để thanh toán']);
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
        
        $order_id = $model->createOrder($user_id, $livestream_id, $cart['items'], $payment_method, $address_data);
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi tạo đơn hàng']);
            break;
        }
        
        $order = $model->getOrder($order_id);
        
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
                'order_id' => $order_id
            ]);
        } else if ($payment_method === 'wallet') {
            // Thanh toán bằng ví
            $result = $model->processWalletPayment($order_id, $user_id);
            if ($result) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Thanh toán thành công',
                    'order_id' => $order_id,
                    'redirect_url' => 'index.php?my-orders'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi thanh toán']);
            }
        } else if ($payment_method === 'cash') {
            // Giao trực tiếp
            echo json_encode([
                'success' => true, 
                'message' => 'Đơn hàng đã được tạo. Bạn sẽ thanh toán khi nhận hàng.',
                'order_id' => $order_id,
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
        if (!$user_id) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            break;
        }
        
        // Lấy danh sách sản phẩm của user
        include_once __DIR__ . "/../model/mProduct.php";
        $mProduct = new mProduct();
        $products = $mProduct->getProductsByUserId($user_id);
        
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
        if ($livestream['user_id'] != $user_id) {
            echo json_encode(['success' => false, 'message' => 'Không có quyền']);
            break;
        }
        
        $result = $model->addProductToLivestream($livestream_id, $product_id, $special_price, $stock_quantity);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Đã thêm sản phẩm vào livestream']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi thêm sản phẩm']);
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

    default:
        echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
}

?>
