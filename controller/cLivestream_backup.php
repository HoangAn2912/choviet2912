<?php
include_once __DIR__ . "/../model/mLivestream.php";
include_once __DIR__ . "/../model/mConnect.php";
include_once __DIR__ . "/../helpers/SecurityValidator.php";
include_once __DIR__ . "/../helpers/FileUploadValidator.php";
include_once __DIR__ . "/../helpers/SessionManager.php";
include_once __DIR__ . "/../helpers/ApiResponse.php";
include_once __DIR__ . "/../helpers/AuthenticationTrait.php";
include_once __DIR__ . "/../helpers/CacheManager.php";
include_once __DIR__ . "/../helpers/RateLimiter.php";
include_once __DIR__ . "/../helpers/Logger.php";

class cLivestream {
    use AuthenticationTrait;
    
    private $model;

    public function __construct() {
        $this->model = new mLivestream();
        SessionManager::start();
    }

    // =============================================
    // QUẢN LÝ LIVESTREAM
    // =============================================

    // Hiển thị danh sách livestream
    public function index() {
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
            
            // Use cache for better performance
            $cacheKey = CacheManager::generateKey('livestreams', $status, $limit);
            $livestreams = CacheManager::remember($cacheKey, function() use ($status, $limit) {
                return $this->model->getLivestreams($status, $limit);
            }, 300); // Cache for 5 minutes
            
            Logger::info('Livestreams retrieved', [
                'status' => $status,
                'limit' => $limit,
                'count' => count($livestreams)
            ]);
            
            ApiResponse::sendSuccess($livestreams, 'Lấy danh sách livestream thành công');
            
        } catch (Exception $e) {
            Logger::error('Error retrieving livestreams', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            ApiResponse::sendInternalError('Có lỗi xảy ra khi lấy danh sách livestream');
        }
    }

    // Hiển thị trang livestream
    public function showLivestream() {
        try {
            // Validate livestream ID
            $validation = SecurityValidator::validateInt($_GET['id'] ?? null, 1);
            if (!$validation['valid']) {
                header('Location: index.php?livestream=all&error=invalid_id');
                exit;
            }
            
            $livestream_id = $validation['value'];
            
            // Get livestream with cache
            $cacheKey = CacheManager::generateKey('livestream_detail', $livestream_id);
            $livestream = CacheManager::remember($cacheKey, function() use ($livestream_id) {
                return $this->model->getLivestreamById($livestream_id);
            }, 60); // Cache for 1 minute
            
            if (!$livestream) {
                header('Location: index.php?livestream=all&error=not_found');
                exit;
            }

            // Get products and stats
            $products = $this->model->getLivestreamProducts($livestream_id);
            $pinned_product = $this->model->getPinnedProduct($livestream_id);
            $stats = $this->model->getLivestreamStats($livestream_id);
            
            Logger::info('Livestream viewed', [
                'livestream_id' => $livestream_id,
                'user_id' => SessionManager::getUserId()
            ]);

            include_once __DIR__ . "/../view/livestream_detail.php";
            
        } catch (Exception $e) {
            Logger::error('Error showing livestream', [
                'livestream_id' => $_GET['id'] ?? null,
                'error' => $e->getMessage()
            ]);
            header('Location: index.php?livestream=all&error=system_error');
            exit;
        }
    }

    // Tạo livestream mới
    public function createLivestream() {
        try {
            // Require authentication
            $userId = $this->requireAuth();
            
            // Check rate limit
            $this->checkRateLimit('create_livestream', 5, 300); // Max 5 per 5 minutes
            
            // Validate input
            $validation = SecurityValidator::validateInputs([
                'title' => [
                    'value' => $_POST['title'] ?? '',
                    'type' => 'string',
                    'min_length' => 1,
                    'max_length' => 255
                ],
                'description' => [
                    'value' => $_POST['description'] ?? '',
                    'type' => 'string',
                    'min_length' => 0,
                    'max_length' => 1000
                ],
                'start_time' => [
                    'value' => $_POST['start_time'] ?? date('Y-m-d H:i:s'),
                    'type' => 'string'
                ],
                'end_time' => [
                    'value' => $_POST['end_time'] ?? null,
                    'type' => 'string'
                ]
            ]);
            
            if (!$validation['valid']) {
                ApiResponse::sendValidationError($validation['errors']);
            }
            
            $data = $validation['data'];
            
            // Validate datetime
            $startTime = DateTime::createFromFormat('Y-m-d H:i:s', $data['start_time']);
            if (!$startTime) {
                ApiResponse::sendValidationError(['start_time' => 'Định dạng thời gian không hợp lệ']);
            }
            
            if ($data['end_time']) {
                $endTime = DateTime::createFromFormat('Y-m-d H:i:s', $data['end_time']);
                if (!$endTime) {
                    ApiResponse::sendValidationError(['end_time' => 'Định dạng thời gian không hợp lệ']);
                }
                if ($endTime <= $startTime) {
                    ApiResponse::sendValidationError(['end_time' => 'Thời gian kết thúc phải sau thời gian bắt đầu']);
                }
            }
            
            // Handle image upload
            $imageName = 'default-live.jpg';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imageValidation = FileUploadValidator::validateImage($_FILES['image'], 5242880); // 5MB
                
                if (!$imageValidation['valid']) {
                    ApiResponse::sendValidationError(['image' => $imageValidation['message']]);
                }
                
                $imageName = FileUploadValidator::generateSafeFilename($_FILES['image']['name'], 'livestream_');
                $uploadDir = __DIR__ . '/../img/';
                $uploadPath = $uploadDir . $imageName;
                
                // Create directory if not exists
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Resize image if needed
                if (FileUploadValidator::resizeImage($_FILES['image']['tmp_name'], $uploadPath, 1920, 1080)) {
                    // Image resized successfully
                } elseif (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    ApiResponse::sendError('Lỗi upload ảnh');
                }
            }
            
            // Prepare data
            $livestreamData = [
                'user_id' => $userId,
                'title' => $data['title'],
                'description' => $data['description'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'status' => 'chua_bat_dau',
                'image' => $imageName
            ];
            
            // Create livestream
            $livestream_id = $this->model->createLivestream($livestreamData);
            
            if (!$livestream_id) {
                ApiResponse::sendError('Không thể tạo livestream');
            }
            
            // Add products if provided
            if (isset($_POST['products'])) {
                $products = json_decode($_POST['products'], true);
                if (is_array($products)) {
                    foreach ($products as $product_id) {
                        $productValidation = SecurityValidator::validateInt($product_id, 1);
                        if ($productValidation['valid']) {
                            $this->model->addProductToLivestream($livestream_id, $productValidation['value']);
                        }
                    }
                }
            }
            
            // Clear cache
            CacheManager::forget(CacheManager::generateKey('livestreams'));
            
            Logger::info('Livestream created', [
                'livestream_id' => $livestream_id,
                'user_id' => $userId,
                'title' => $data['title']
            ]);
            
            ApiResponse::sendSuccess([
                'livestream_id' => $livestream_id
            ], 'Tạo livestream thành công');
            
        } catch (Exception $e) {
            Logger::error('Error creating livestream', [
                'user_id' => SessionManager::getUserId(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            ApiResponse::sendInternalError('Có lỗi xảy ra khi tạo livestream');
        }
    }

    // Bắt đầu/kết thúc livestream
    public function toggleStatus() {
        try {
            // Require authentication and check if user is streamer
            $userId = $this->requireStreamer($_POST['livestream_id'] ?? null);
            
            // Check rate limit
            $this->checkRateLimit('toggle_livestream_status', 10, 60); // Max 10 per minute
            
            // Validate input
            $validation = SecurityValidator::validateInputs([
                'livestream_id' => [
                    'value' => $_POST['livestream_id'] ?? null,
                    'type' => 'int',
                    'min' => 1
                ],
                'status' => [
                    'value' => $_POST['status'] ?? null,
                    'type' => 'enum',
                    'allowed_values' => ['chua_bat_dau', 'dang_dien_ra', 'da_ket_thuc']
                ]
            ]);
            
            if (!$validation['valid']) {
                ApiResponse::sendValidationError($validation['errors']);
            }
            
            $livestream_id = $validation['data']['livestream_id'];
            $status = $validation['data']['status'];
            
            // Update status
            $result = $this->model->updateLivestreamStatus($livestream_id, $status);
            
            if (!$result) {
                ApiResponse::sendError('Không thể cập nhật trạng thái');
            }
            
            // Clear cache
            CacheManager::forget(CacheManager::generateKey('livestream_detail', $livestream_id));
            CacheManager::forget(CacheManager::generateKey('livestreams'));
            
            Logger::info('Livestream status updated', [
                'livestream_id' => $livestream_id,
                'user_id' => $userId,
                'status' => $status
            ]);
            
            ApiResponse::sendSuccess(null, 'Cập nhật trạng thái thành công');
            
        } catch (Exception $e) {
            Logger::error('Error updating livestream status', [
                'livestream_id' => $_POST['livestream_id'] ?? null,
                'user_id' => SessionManager::getUserId(),
                'error' => $e->getMessage()
            ]);
            ApiResponse::sendInternalError('Có lỗi xảy ra khi cập nhật trạng thái');
        }
    }

    // =============================================
    // QUẢN LÝ SẢN PHẨM TRONG LIVESTREAM
    // =============================================

    // Thêm sản phẩm vào livestream
    public function addProduct() {
        try {
            // Require authentication and check if user is streamer
            $userId = $this->requireStreamer($_POST['livestream_id'] ?? null);
            
            // Check rate limit
            $this->checkRateLimit('add_product_to_livestream', 20, 60); // Max 20 per minute
            
            // Validate input
            $validation = SecurityValidator::validateInputs([
                'livestream_id' => [
                    'value' => $_POST['livestream_id'] ?? null,
                    'type' => 'int',
                    'min' => 1
                ],
                'product_id' => [
                    'value' => $_POST['product_id'] ?? null,
                    'type' => 'int',
                    'min' => 1
                ],
                'special_price' => [
                    'value' => $_POST['special_price'] ?? null,
                    'type' => 'int',
                    'min' => 0
                ],
                'stock_quantity' => [
                    'value' => $_POST['stock_quantity'] ?? null,
                    'type' => 'int',
                    'min' => 0
                ]
            ]);
            
            if (!$validation['valid']) {
                ApiResponse::sendValidationError($validation['errors']);
            }
            
            $data = $validation['data'];
            
            // Add product to livestream
            $result = $this->model->addProductToLivestream(
                $data['livestream_id'], 
                $data['product_id'], 
                $data['special_price'], 
                $data['stock_quantity']
            );
            
            if (!$result) {
                ApiResponse::sendError('Không thể thêm sản phẩm');
            }
            
            // Clear cache
            CacheManager::forget(CacheManager::generateKey('livestream_detail', $data['livestream_id']));
            
            Logger::info('Product added to livestream', [
                'livestream_id' => $data['livestream_id'],
                'product_id' => $data['product_id'],
                'user_id' => $userId
            ]);
            
            ApiResponse::sendSuccess(null, 'Thêm sản phẩm thành công');
            
        } catch (Exception $e) {
            Logger::error('Error adding product to livestream', [
                'livestream_id' => $_POST['livestream_id'] ?? null,
                'product_id' => $_POST['product_id'] ?? null,
                'user_id' => SessionManager::getUserId(),
                'error' => $e->getMessage()
            ]);
            ApiResponse::sendInternalError('Có lỗi xảy ra khi thêm sản phẩm');
        }
    }

    // Ghim sản phẩm
    public function pinProduct() {
        try {
            // Require authentication and check if user is streamer
            $userId = $this->requireStreamer($_POST['livestream_id'] ?? null);
            
            // Check rate limit
            $this->checkRateLimit('pin_product', 10, 60); // Max 10 per minute
            
            // Validate input
            $validation = SecurityValidator::validateInputs([
                'livestream_id' => [
                    'value' => $_POST['livestream_id'] ?? null,
                    'type' => 'int',
                    'min' => 1
                ],
                'product_id' => [
                    'value' => $_POST['product_id'] ?? null,
                    'type' => 'int',
                    'min' => 1
                ]
            ]);
            
            if (!$validation['valid']) {
                ApiResponse::sendValidationError($validation['errors']);
            }
            
            $data = $validation['data'];
            
            // Pin product
            $result = $this->model->pinProduct($data['livestream_id'], $data['product_id']);
            
            if (!$result) {
                ApiResponse::sendError('Không thể ghim sản phẩm');
            }
            
            // Get pinned product info
            $pinned_product = $this->model->getPinnedProduct($data['livestream_id']);
            
            // Clear cache
            CacheManager::forget(CacheManager::generateKey('livestream_detail', $data['livestream_id']));
            
            Logger::info('Product pinned', [
                'livestream_id' => $data['livestream_id'],
                'product_id' => $data['product_id'],
                'user_id' => $userId
            ]);
            
            ApiResponse::sendSuccess([
                'product' => $pinned_product
            ], 'Ghim sản phẩm thành công');
            
        } catch (Exception $e) {
            Logger::error('Error pinning product', [
                'livestream_id' => $_POST['livestream_id'] ?? null,
                'product_id' => $_POST['product_id'] ?? null,
                'user_id' => SessionManager::getUserId(),
                'error' => $e->getMessage()
            ]);
            ApiResponse::sendInternalError('Có lỗi xảy ra khi ghim sản phẩm');
        }
    }

    // Lấy danh sách sản phẩm trong livestream
    public function getProducts() {
        try {
            // Validate input
            $validation = SecurityValidator::validateInt($_GET['livestream_id'] ?? null, 1);
            if (!$validation['valid']) {
                ApiResponse::sendValidationError(['livestream_id' => $validation['message']]);
            }
            
            $livestream_id = $validation['value'];
            
            // Use cache for better performance
            $cacheKey = CacheManager::generateKey('livestream_products', $livestream_id);
            $data = CacheManager::remember($cacheKey, function() use ($livestream_id) {
                $products = $this->model->getLivestreamProducts($livestream_id);
                $pinned_product = $this->model->getPinnedProduct($livestream_id);
                
                return [
                    'products' => $products,
                    'pinned_product' => $pinned_product
                ];
            }, 60); // Cache for 1 minute
            
            Logger::info('Livestream products retrieved', [
                'livestream_id' => $livestream_id,
                'product_count' => count($data['products'])
            ]);
            
            ApiResponse::sendSuccess($data, 'Lấy danh sách sản phẩm thành công');
            
        } catch (Exception $e) {
            Logger::error('Error retrieving livestream products', [
                'livestream_id' => $_GET['livestream_id'] ?? null,
                'error' => $e->getMessage()
            ]);
            ApiResponse::sendInternalError('Có lỗi xảy ra khi lấy danh sách sản phẩm');
        }
    }

    // =============================================
    // QUẢN LÝ GIỎ HÀNG
    // =============================================

    // Thêm vào giỏ hàng
    public function addToCart() {
        try {
            // Require authentication
            $userId = $this->requireAuth();
            
            // Check rate limit
            $this->checkRateLimit('add_to_cart', 30, 60); // Max 30 per minute
            
            // Validate input
            $validation = SecurityValidator::validateInputs([
                'livestream_id' => [
                    'value' => $_POST['livestream_id'] ?? null,
                    'type' => 'int',
                    'min' => 1
                ],
                'product_id' => [
                    'value' => $_POST['product_id'] ?? null,
                    'type' => 'int',
                    'min' => 1
                ],
                'quantity' => [
                    'value' => $_POST['quantity'] ?? 1,
                    'type' => 'int',
                    'min' => 1,
                    'max' => 100
                ]
            ]);
            
            if (!$validation['valid']) {
                ApiResponse::sendValidationError($validation['errors']);
            }
            
            $data = $validation['data'];
            
            // Check if livestream is active
            $livestream = $this->model->getLivestreamById($data['livestream_id']);
            if (!$livestream || $livestream['status'] !== 'dang_dien_ra') {
                ApiResponse::sendError('Livestream không khả dụng');
            }
            
            // Add to cart
            $result = $this->model->addToCart($userId, $data['livestream_id'], $data['product_id'], $data['quantity']);
            
            if (!$result) {
                ApiResponse::sendError('Không thể thêm vào giỏ hàng');
            }
            
            // Get updated cart
            $cart = $this->model->getCart($userId, $data['livestream_id']);
            
            Logger::info('Product added to cart', [
                'user_id' => $userId,
                'livestream_id' => $data['livestream_id'],
                'product_id' => $data['product_id'],
                'quantity' => $data['quantity']
            ]);
            
            ApiResponse::sendSuccess([
                'cart' => $cart
            ], 'Thêm vào giỏ hàng thành công');
            
        } catch (Exception $e) {
            Logger::error('Error adding to cart', [
                'user_id' => SessionManager::getUserId(),
                'livestream_id' => $_POST['livestream_id'] ?? null,
                'product_id' => $_POST['product_id'] ?? null,
                'error' => $e->getMessage()
            ]);
            ApiResponse::sendInternalError('Có lỗi xảy ra khi thêm vào giỏ hàng');
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




