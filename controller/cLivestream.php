<?php
include_once __DIR__ . "/../model/mLivestream.php";
include_once __DIR__ . "/../model/mConnect.php";

class cLivestream {
    private $model;

    public function __construct() {
        $this->model = new mLivestream();
    }

    // =============================================
    // QUẢN LÝ LIVESTREAM
    // =============================================

    // Hiển thị danh sách livestream
    public function index() {
        $status = $_GET['status'] ?? null;
        $livestreams = $this->model->getLivestreams($status);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $livestreams
        ]);
    }

    // Hiển thị trang livestream
    public function showLivestream() {
        $livestream_id = $_GET['id'] ?? null;
        
        if (!$livestream_id) {
            header('Location: index.php?livestream=all');
            exit;
        }

        $livestream = $this->model->getLivestreamById($livestream_id);
        if (!$livestream) {
            header('Location: index.php?livestream=all');
            exit;
        }

        $products = $this->model->getLivestreamProducts($livestream_id);
        $pinned_product = $this->model->getPinnedProduct($livestream_id);
        $stats = $this->model->getLivestreamStats($livestream_id);

        include_once __DIR__ . "/../view/livestream_detail.php";
    }

    // Tạo livestream mới
    public function createLivestream() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        // Xử lý upload ảnh
        $imageName = 'default-live.jpg';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'img/';
            $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            
            // Kiểm tra định dạng file
            if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
                $imageName = 'livestream_' . uniqid() . '.' . $fileExtension;
                $uploadPath = $uploadDir . $imageName;
                
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    echo json_encode(['success' => false, 'message' => 'Lỗi upload ảnh']);
                    return;
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Chỉ cho phép file ảnh JPG, PNG, GIF']);
                return;
            }
        }

        $data = [
            'user_id' => $_SESSION['user_id'],
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'start_time' => $_POST['start_time'] ?? date('Y-m-d H:i:s'),
            'end_time' => $_POST['end_time'] ?? null,
            'status' => 'chua_bat_dau',
            'image' => $imageName
        ];

        $livestream_id = $this->model->createLivestream($data);
        
        if ($livestream_id) {
            // Thêm sản phẩm nếu có
            if (isset($_POST['products'])) {
                $products = json_decode($_POST['products'], true);
                if (is_array($products)) {
                    foreach ($products as $product_id) {
                        $this->model->addProductToLivestream($livestream_id, $product_id);
                    }
                }
            }

            echo json_encode([
                'success' => true, 
                'message' => 'Tạo livestream thành công',
                'livestream_id' => $livestream_id
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    // Bắt đầu/kết thúc livestream
    public function toggleStatus() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        $livestream_id = $_POST['livestream_id'] ?? null;
        $status = $_POST['status'] ?? null;

        if (!$livestream_id || !$status) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            return;
        }

        $result = $this->model->updateLivestreamStatus($livestream_id, $status);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    // =============================================
    // QUẢN LÝ SẢN PHẨM TRONG LIVESTREAM
    // =============================================

    // Thêm sản phẩm vào livestream
    public function addProduct() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        $livestream_id = $_POST['livestream_id'] ?? null;
        $product_id = $_POST['product_id'] ?? null;
        $special_price = $_POST['special_price'] ?? null;
        $stock_quantity = $_POST['stock_quantity'] ?? null;

        if (!$livestream_id || !$product_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            return;
        }

        $result = $this->model->addProductToLivestream($livestream_id, $product_id, $special_price, $stock_quantity);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Thêm sản phẩm thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    // Ghim sản phẩm
    public function pinProduct() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        $livestream_id = $_POST['livestream_id'] ?? null;
        $product_id = $_POST['product_id'] ?? null;

        if (!$livestream_id || !$product_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            return;
        }

        $result = $this->model->pinProduct($livestream_id, $product_id);
        
        if ($result) {
            $pinned_product = $this->model->getPinnedProduct($livestream_id);
            echo json_encode([
                'success' => true, 
                'message' => 'Ghim sản phẩm thành công',
                'product' => $pinned_product
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    // Lấy danh sách sản phẩm trong livestream
    public function getProducts() {
        $livestream_id = $_GET['livestream_id'] ?? null;
        
        if (!$livestream_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            return;
        }

        $products = $this->model->getLivestreamProducts($livestream_id);
        $pinned_product = $this->model->getPinnedProduct($livestream_id);
        
        echo json_encode([
            'success' => true,
            'products' => $products,
            'pinned_product' => $pinned_product
        ]);
    }

    // =============================================
    // QUẢN LÝ GIỎ HÀNG
    // =============================================

    // Thêm vào giỏ hàng
    public function addToCart() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        $livestream_id = $_POST['livestream_id'] ?? null;
        $product_id = $_POST['product_id'] ?? null;
        $quantity = $_POST['quantity'] ?? 1;

        if (!$livestream_id || !$product_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            return;
        }

        $result = $this->model->addToCart($_SESSION['user_id'], $livestream_id, $product_id, $quantity);
        
        if ($result) {
            $cart = $this->model->getCart($_SESSION['user_id'], $livestream_id);
            echo json_encode([
                'success' => true, 
                'message' => 'Thêm vào giỏ hàng thành công',
                'cart' => $cart
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    // Lấy giỏ hàng
    public function getCart() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        $livestream_id = $_GET['livestream_id'] ?? null;
        
        if (!$livestream_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            return;
        }

        $cart = $this->model->getCart($_SESSION['user_id'], $livestream_id);
        
        echo json_encode([
            'success' => true,
            'cart' => $cart
        ]);
    }

    // Cập nhật số lượng trong giỏ hàng
    public function updateCartQuantity() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        $livestream_id = $_POST['livestream_id'] ?? null;
        $product_id = $_POST['product_id'] ?? null;
        $quantity = $_POST['quantity'] ?? 1;

        if (!$livestream_id || !$product_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            return;
        }

        $result = $this->model->updateCartQuantity($_SESSION['user_id'], $livestream_id, $product_id, $quantity);
        
        if ($result) {
            $cart = $this->model->getCart($_SESSION['user_id'], $livestream_id);
            echo json_encode([
                'success' => true, 
                'message' => 'Cập nhật giỏ hàng thành công',
                'cart' => $cart
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    // Xóa khỏi giỏ hàng
    public function removeFromCart() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        $livestream_id = $_POST['livestream_id'] ?? null;
        $product_id = $_POST['product_id'] ?? null;

        if (!$livestream_id || !$product_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            return;
        }

        $result = $this->model->removeFromCart($_SESSION['user_id'], $livestream_id, $product_id);
        
        if ($result) {
            $cart = $this->model->getCart($_SESSION['user_id'], $livestream_id);
            echo json_encode([
                'success' => true, 
                'message' => 'Xóa khỏi giỏ hàng thành công',
                'cart' => $cart
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    // =============================================
    // THANH TOÁN
    // =============================================

    // Tạo đơn hàng và chuyển đến VNPay
    public function checkout() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        $livestream_id = $_POST['livestream_id'] ?? null;
        
        if (!$livestream_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            return;
        }

        // Lấy giỏ hàng
        $cart = $this->model->getCart($_SESSION['user_id'], $livestream_id);
        
        if (empty($cart['items'])) {
            echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống']);
            return;
        }

        // Tạo đơn hàng
        $order_id = $this->model->createOrder($_SESSION['user_id'], $livestream_id, $cart['items']);
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi tạo đơn hàng']);
            return;
        }

        // Lấy thông tin đơn hàng
        $order = $this->model->getOrder($order_id);
        
        // Tạo URL thanh toán VNPay
        $vnpay_url = $this->createVNPayPayment($order);
        
        if ($vnpay_url) {
            echo json_encode([
                'success' => true, 
                'message' => 'Tạo đơn hàng thành công',
                'payment_url' => $vnpay_url,
                'order_id' => $order_id
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi tạo thanh toán']);
        }
    }

    // Tạo URL thanh toán VNPay
    private function createVNPayPayment($order) {
        require_once __DIR__ . '/vnpay/vnpay_config.php';
        
        $vnp_TxnRef = 'LIVE_' . $order['order_code'] . '_' . time();
        $vnp_OrderInfo = "Thanh toan don hang livestream - " . $order['order_code'];
        $vnp_OrderType = "billpayment";
        $vnp_Amount = $order['total_amount'] * 100; // VNPay yêu cầu nhân với 100
        $vnp_Locale = "vn";
        $vnp_BankCode = "";
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

        // Lưu thông tin giao dịch
        $this->model->updateOrderStatus($order['id'], 'pending', $vnp_TxnRef);

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

        return $vnp_Url;
    }

    // =============================================
    // TƯƠNG TÁC VÀ THỐNG KÊ
    // =============================================

    // Ghi nhận tương tác
    public function recordInteraction() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        $livestream_id = $_POST['livestream_id'] ?? null;
        $action_type = $_POST['action_type'] ?? null;

        if (!$livestream_id || !$action_type) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            return;
        }

        $result = $this->model->recordInteraction($livestream_id, $_SESSION['user_id'], $action_type);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Ghi nhận tương tác thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    // Lấy thống kê
    public function getStats() {
        $livestream_id = $_GET['livestream_id'] ?? null;
        
        if (!$livestream_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            return;
        }

        $stats = $this->model->getLivestreamStats($livestream_id);
        
        echo json_encode([
            'success' => true,
            'stats' => $stats
        ]);
    }
}

// Xử lý API requests
if (isset($_GET['action'])) {
    session_start();
    $controller = new cLivestream();
    
    switch ($_GET['action']) {
        case 'index':
            $controller->index();
            break;
        case 'create':
            $controller->createLivestream();
            break;
        case 'toggle_status':
            $controller->toggleStatus();
            break;
        case 'add_product':
            $controller->addProduct();
            break;
        case 'pin_product':
            $controller->pinProduct();
            break;
        case 'get_products':
            $controller->getProducts();
            break;
        case 'add_to_cart':
            $controller->addToCart();
            break;
        case 'get_cart':
            $controller->getCart();
            break;
        case 'update_cart_quantity':
            $controller->updateCartQuantity();
            break;
        case 'remove_from_cart':
            $controller->removeFromCart();
            break;
        case 'checkout':
            $controller->checkout();
            break;
        case 'record_interaction':
            $controller->recordInteraction();
            break;
        case 'get_stats':
            $controller->getStats();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
    }
}
?>




