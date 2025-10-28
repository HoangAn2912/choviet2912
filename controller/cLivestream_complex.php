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
            $status = $_GET['status'] ?? null;
            $limit = intval($_GET['limit'] ?? 20);
            
            // Basic validation
            if ($limit < 1 || $limit > 100) {
                $limit = 20;
            }
            
            if ($status && !in_array($status, ['chua_bat_dau', 'dang_dien_ra', 'da_ket_thuc'])) {
                $status = null;
            }
            
            $livestreams = $this->model->getLivestreams($status, $limit);
            
            // Return data for view
            return [
                'success' => true,
                'data' => $livestreams
            ];
            
        } catch (Exception $e) {
            error_log('Error retrieving livestreams: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách livestream'
            ];
        }
    }

    // Hiển thị trang livestream
    public function showLivestream() {
        try {
            $livestream_id = intval($_GET['id'] ?? 0);
            
            if ($livestream_id <= 0) {
                header('Location: index.php?livestream=all&error=invalid_id');
                exit;
            }
            
            $livestream = $this->model->getLivestreamById($livestream_id);
            
            if (!$livestream) {
                header('Location: index.php?livestream=all&error=not_found');
                exit;
            }

            // Get products and stats
            $products = $this->model->getLivestreamProducts($livestream_id);
            $pinned_product = $this->model->getPinnedProduct($livestream_id);
            $stats = $this->model->getLivestreamStats($livestream_id);

            include_once __DIR__ . "/../view/livestream_detail.php";
            
        } catch (Exception $e) {
            error_log('Error showing livestream: ' . $e->getMessage());
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
        try {
            // Require authentication
            $userId = $this->requireAuth();
            
            // Validate input
            $validation = SecurityValidator::validateInt($_GET['livestream_id'] ?? null, 1);
            if (!$validation['valid']) {
                ApiResponse::sendValidationError(['livestream_id' => $validation['message']]);
            }
            
            $livestream_id = $validation['value'];
            
            // Get cart
            $cart = $this->model->getCart($userId, $livestream_id);
            
            Logger::info('Cart retrieved', [
                'user_id' => $userId,
                'livestream_id' => $livestream_id,
                'item_count' => $cart['item_count']
            ]);
            
            ApiResponse::sendSuccess([
                'cart' => $cart
            ], 'Lấy giỏ hàng thành công');
            
        } catch (Exception $e) {
            Logger::error('Error retrieving cart', [
                'user_id' => SessionManager::getUserId(),
                'livestream_id' => $_GET['livestream_id'] ?? null,
                'error' => $e->getMessage()
            ]);
            ApiResponse::sendInternalError('Có lỗi xảy ra khi lấy giỏ hàng');
        }
    }

    // Cập nhật số lượng trong giỏ hàng
    public function updateCartQuantity() {
        try {
            // Require authentication
            $userId = $this->requireAuth();
            
            // Check rate limit
            $this->checkRateLimit('update_cart_quantity', 50, 60); // Max 50 per minute
            
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
                    'min' => 0,
                    'max' => 100
                ]
            ]);
            
            if (!$validation['valid']) {
                ApiResponse::sendValidationError($validation['errors']);
            }
            
            $data = $validation['data'];
            
            // Update quantity
            $result = $this->model->updateCartQuantity($userId, $data['livestream_id'], $data['product_id'], $data['quantity']);
            
            if (!$result) {
                ApiResponse::sendError('Không thể cập nhật số lượng');
            }
            
            // Get updated cart
            $cart = $this->model->getCart($userId, $data['livestream_id']);
            
            Logger::info('Cart quantity updated', [
                'user_id' => $userId,
                'livestream_id' => $data['livestream_id'],
                'product_id' => $data['product_id'],
                'quantity' => $data['quantity']
            ]);
            
            ApiResponse::sendSuccess([
                'cart' => $cart
            ], 'Cập nhật số lượng thành công');
            
        } catch (Exception $e) {
            Logger::error('Error updating cart quantity', [
                'user_id' => SessionManager::getUserId(),
                'livestream_id' => $_POST['livestream_id'] ?? null,
                'product_id' => $_POST['product_id'] ?? null,
                'error' => $e->getMessage()
            ]);
            ApiResponse::sendInternalError('Có lỗi xảy ra khi cập nhật số lượng');
        }
    }

    // Xóa khỏi giỏ hàng
    public function removeFromCart() {
        try {
            // Require authentication
            $userId = $this->requireAuth();
            
            // Check rate limit
            $this->checkRateLimit('remove_from_cart', 30, 60); // Max 30 per minute
            
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
            
            // Remove from cart
            $result = $this->model->removeFromCart($userId, $data['livestream_id'], $data['product_id']);
            
            if (!$result) {
                ApiResponse::sendError('Không thể xóa khỏi giỏ hàng');
            }
            
            // Get updated cart
            $cart = $this->model->getCart($userId, $data['livestream_id']);
            
            Logger::info('Product removed from cart', [
                'user_id' => $userId,
                'livestream_id' => $data['livestream_id'],
                'product_id' => $data['product_id']
            ]);
            
            ApiResponse::sendSuccess([
                'cart' => $cart
            ], 'Xóa khỏi giỏ hàng thành công');
            
        } catch (Exception $e) {
            Logger::error('Error removing from cart', [
                'user_id' => SessionManager::getUserId(),
                'livestream_id' => $_POST['livestream_id'] ?? null,
                'product_id' => $_POST['product_id'] ?? null,
                'error' => $e->getMessage()
            ]);
            ApiResponse::sendInternalError('Có lỗi xảy ra khi xóa khỏi giỏ hàng');
        }
    }

    // =============================================
    // THANH TOÁN
    // =============================================

    // Tạo đơn hàng và chuyển đến VNPay
    public function checkout() {
        try {
            // Require authentication
            $userId = $this->requireAuth();
            
            // Check rate limit
            $this->checkRateLimit('checkout', 5, 300); // Max 5 per 5 minutes
            
            // Validate input
            $validation = SecurityValidator::validateInt($_POST['livestream_id'] ?? null, 1);
            if (!$validation['valid']) {
                ApiResponse::sendValidationError(['livestream_id' => $validation['message']]);
            }
            
            $livestream_id = $validation['value'];
            
            // Get cart
            $cart = $this->model->getCart($userId, $livestream_id);
            
            if (empty($cart['items'])) {
                ApiResponse::sendError('Giỏ hàng trống');
            }
            
            // Check if livestream is active
            $livestream = $this->model->getLivestreamById($livestream_id);
            if (!$livestream || $livestream['status'] !== 'dang_dien_ra') {
                ApiResponse::sendError('Livestream không khả dụng');
            }
            
            // Create order
            $order_id = $this->model->createOrder($userId, $livestream_id, $cart['items']);
            
            if (!$order_id) {
                ApiResponse::sendError('Không thể tạo đơn hàng');
            }
            
            // Get order info
            $order = $this->model->getOrder($order_id);
            
            // Create VNPay payment URL
            $vnpay_url = $this->createVNPayPayment($order);
            
            if (!$vnpay_url) {
                ApiResponse::sendError('Không thể tạo thanh toán');
            }
            
            Logger::info('Order created', [
                'user_id' => $userId,
                'livestream_id' => $livestream_id,
                'order_id' => $order_id,
                'total_amount' => $order['total_amount']
            ]);
            
            ApiResponse::sendSuccess([
                'payment_url' => $vnpay_url,
                'order_id' => $order_id
            ], 'Tạo đơn hàng thành công');
            
        } catch (Exception $e) {
            Logger::error('Error during checkout', [
                'user_id' => SessionManager::getUserId(),
                'livestream_id' => $_POST['livestream_id'] ?? null,
                'error' => $e->getMessage()
            ]);
            ApiResponse::sendInternalError('Có lỗi xảy ra khi thanh toán');
        }
    }

    // Tạo URL thanh toán VNPay
    private function createVNPayPayment($order) {
        try {
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
            
        } catch (Exception $e) {
            Logger::error('Error creating VNPay payment', [
                'order_id' => $order['id'] ?? null,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    // =============================================
    // TƯƠNG TÁC VÀ THỐNG KÊ
    // =============================================

    // Ghi nhận tương tác
    public function recordInteraction() {
        try {
            // Require authentication
            $userId = $this->requireAuth();
            
            // Check rate limit
            $this->checkRateLimit('record_interaction', 100, 60); // Max 100 per minute
            
            // Validate input
            $validation = SecurityValidator::validateInputs([
                'livestream_id' => [
                    'value' => $_POST['livestream_id'] ?? null,
                    'type' => 'int',
                    'min' => 1
                ],
                'action_type' => [
                    'value' => $_POST['action_type'] ?? null,
                    'type' => 'enum',
                    'allowed_values' => ['like', 'share', 'follow', 'purchase', 'view']
                ]
            ]);
            
            if (!$validation['valid']) {
                ApiResponse::sendValidationError($validation['errors']);
            }
            
            $data = $validation['data'];
            
            // Record interaction
            $result = $this->model->recordInteraction($data['livestream_id'], $userId, $data['action_type']);
            
            if (!$result) {
                ApiResponse::sendError('Không thể ghi nhận tương tác');
            }
            
            Logger::info('Interaction recorded', [
                'user_id' => $userId,
                'livestream_id' => $data['livestream_id'],
                'action_type' => $data['action_type']
            ]);
            
            ApiResponse::sendSuccess(null, 'Ghi nhận tương tác thành công');
            
        } catch (Exception $e) {
            Logger::error('Error recording interaction', [
                'user_id' => SessionManager::getUserId(),
                'livestream_id' => $_POST['livestream_id'] ?? null,
                'action_type' => $_POST['action_type'] ?? null,
                'error' => $e->getMessage()
            ]);
            ApiResponse::sendInternalError('Có lỗi xảy ra khi ghi nhận tương tác');
        }
    }

    // Lấy thống kê
    public function getStats() {
        try {
            // Validate input
            $validation = SecurityValidator::validateInt($_GET['livestream_id'] ?? null, 1);
            if (!$validation['valid']) {
                ApiResponse::sendValidationError(['livestream_id' => $validation['message']]);
            }
            
            $livestream_id = $validation['value'];
            
            // Use cache for stats
            $cacheKey = CacheManager::generateKey('livestream_stats', $livestream_id);
            $stats = CacheManager::remember($cacheKey, function() use ($livestream_id) {
                return $this->model->getLivestreamStats($livestream_id);
            }, 30); // Cache for 30 seconds
            
            Logger::info('Livestream stats retrieved', [
                'livestream_id' => $livestream_id
            ]);
            
            ApiResponse::sendSuccess([
                'stats' => $stats
            ], 'Lấy thống kê thành công');
            
        } catch (Exception $e) {
            Logger::error('Error retrieving livestream stats', [
                'livestream_id' => $_GET['livestream_id'] ?? null,
                'error' => $e->getMessage()
            ]);
            ApiResponse::sendInternalError('Có lỗi xảy ra khi lấy thống kê');
        }
    }
}

// Xử lý API requests
if (isset($_GET['action'])) {
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
            ApiResponse::sendError('Action không hợp lệ', 400);
    }
}
?>
