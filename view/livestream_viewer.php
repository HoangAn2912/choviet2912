<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . "/../model/mLivestream.php";
include_once __DIR__ . "/../model/mConnect.php";

$livestream_id = $_GET['id'] ?? null;
if (!$livestream_id) {
    header('Location: index.php');
    exit;
}

$model = new mLivestream();
$livestream = $model->getLivestreamById($livestream_id);

if (!$livestream) {
    header('Location: index.php');
    exit;
}

// Kiểm tra trạng thái livestream
if ($livestream['status'] !== 'dang_live' && $livestream['status'] !== 'dang_dien_ra') {
    // Nếu không đang live, redirect về trang danh sách
    header('Location: index.php?livestream');
    exit;
}

// Lấy sản phẩm đang được ghim
$pinned_product = $model->getPinnedProduct($livestream_id);

// Kiểm tra xem user hiện tại có phải là streamer không
$is_streamer = isset($_SESSION['user_id']) && $livestream['user_id'] == $_SESSION['user_id'];

// Lấy sản phẩm trong livestream
$products = $model->getLivestreamProducts($livestream_id);

// Sắp xếp sản phẩm: ghim lên đầu, sau đó theo id (số thứ tự)
usort($products, function($a, $b) {
    if ($a['is_pinned'] && !$b['is_pinned']) return -1;
    if (!$a['is_pinned'] && $b['is_pinned']) return 1;
    return $a['id'] - $b['id'];
});

// Lấy giỏ hàng của user (nếu đã đăng nhập)
$cart_items = [];
if (isset($_SESSION['user_id'])) {
    $cart_items = $model->getCart($_SESSION['user_id'], $livestream_id);
}

// Lấy thông tin streamer
$streamer_info = null;
if ($livestream['user_id']) {
    include_once __DIR__ . "/../model/mUser.php";
    $mUser = new mUser();
    $streamer_info = $mUser->getUserById($livestream['user_id']);
}

// Lấy số lượng viewer hiện tại (bao gồm cả guest)
$current_viewers = $model->getCurrentViewerCount($livestream_id);

// Include header
include_once __DIR__ . "/header.php";

// Override title for livestream page
echo "<script>document.title = '" . htmlspecialchars($livestream['title']) . " - Xem Livestream - Chợ Việt';</script>";
?>
    <style>
        .livestream-container {
            background: #0e0e0e;
            color: #eee;
            font-family: system-ui, Segoe UI, Arial, sans-serif;
            min-height: 100vh;
        }
        
        .panel {
            background: #151515;
            border: 1px solid #242424;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 16px;
        }
        
        .panel h5 {
            margin: 0 0 10px 0;
            color: #fff;
        }
        
        .live-item {
            background: #1d1d1d;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .live-item:hover {
            background: #2a2a2a;
        }
        
        .live-item.active {
            background: #ff4444;
        }
        
        .live-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 4px;
        }
        
        .live-streamer {
            font-size: 12px;
            color: #ccc;
        }
        
        .live-viewers {
            font-size: 12px;
            color: #ffd700;
        }

        .live-status {
            margin-top: 8px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        .status-badge.live {
            background: #dc3545;
            color: white;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
            border: 2px solid #c82333;
        }

        .status-badge.ended {
            background: #6c757d;
            color: white;
        }

        .status-badge.waiting {
            background: #ffc107;
            color: #333;
        }

        .status-badge i {
            font-size: 8px;
        }
        
        .stat {
            background: #1d1d1d;
            border-radius: 10px;
            padding: 12px;
            text-align: center;
        }
        
        .stat .num {
            font-size: 22px;
            font-weight: bold;
            color: #ffd700;
        }
        
        #livestream-video {
            width: 100%;
            height: 560px;
            background: #000;
            border-radius: 10px;
            object-fit: cover;
        }
        
        .video-placeholder {
            width: 100%;
            height: 560px;
            background: #000;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #666;
        }
        
        .chat-section {
            height: 280px;
            display: flex;
            flex-direction: column;
        }
        
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            background: #0e0e0e;
            border-radius: 8px;
            padding: 8px;
            margin-bottom: 8px;
        }
        
        .chat-message {
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .chat-message .username {
            font-weight: bold;
            color: #ffd700;
        }
        
        .chat-input {
            display: flex;
            gap: 8px;
        }
        
        .chat-input input {
            flex: 1;
            background: #222;
            border: 1px solid #333;
            color: #fff;
            border-radius: 8px;
            padding: 8px;
        }
        
        .products-section {
            height: auto;
            max-height: 400px;
        }
        
        .product-item {
            background: #1d1d1d;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
        }
        
        .product-item:hover {
            background: #2a2a2a;
            transform: translateY(-2px);
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            flex-shrink: 0;
        }
        
        .product-info {
            flex: 1;
            min-width: 0;
        }
        
        .product-name {
            font-weight: bold;
            margin-bottom: 4px;
            color: #fff;
            font-size: 14px;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-price {
            color: #ffd700;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
            line-height: 1.2;
        }
        
        .product-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        
        .btn-add-cart {
            background: #28a745;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .btn-add-cart:hover {
            background: #218838;
            transform: scale(1.05);
        }
        
        .btn-add-cart:active {
            transform: scale(0.95);
        }

        .btn-pin {
            background: #ffc107;
            color: #333;
            border: none;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            margin-right: 8px;
        }
        
        .btn-pin:hover {
            background: #e0a800;
            transform: scale(1.05);
        }

        .btn-pin.pinned {
            background: #dc3545;
            color: white;
        }

        .btn-pin.pinned:hover {
            background: #c82333;
        }

        .product-item {
            position: relative;
            transition: all 0.3s ease;
        }

        .product-item.pinned {
            border: 2px solid #ffd700;
            background: rgba(255, 215, 0, 0.2);
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.3);
            transition: all 0.3s ease;
        }

        .product-item.pinned .product-number {
            background: #ff6b00;
            color: #fff;
            font-weight: bold;
        }

        .product-item.pinned .product-name {
            color: #ffd700;
            font-weight: bold;
        }

        .product-item.pinned .product-price {
            color: #ff6b00;
            font-weight: bold;
            font-size: 18px;
        }

        .product-number {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #ffc107;
            color: #333;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            z-index: 1;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .quantity-btn {
            width: 24px;
            height: 24px;
            border: none;
            background: #4a4a4a;
            color: #fff;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
        
        .quantity-btn:hover {
            background: #5a5a5a;
        }
        
        .quantity-input {
            width: 40px;
            height: 24px;
            text-align: center;
            background: #333;
            color: #fff;
            border: 1px solid #555;
            border-radius: 4px;
            font-size: 12px;
            -moz-appearance: textfield;
            -webkit-appearance: none;
            appearance: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Animation cho sản phẩm ghim */
        @keyframes pinGlow {
            0% { box-shadow: 0 0 10px rgba(255, 215, 0, 0.3); }
            50% { box-shadow: 0 0 20px rgba(255, 215, 0, 0.8); }
            100% { box-shadow: 0 0 10px rgba(255, 215, 0, 0.3); }
        }

        .product-item.pinning {
            animation: pinGlow 1s ease-in-out;
        }
            background: #333;
            color: #fff;
            border: 1px solid #555;
            border-radius: 4px;
            font-size: 12px;
            line-height: 24px;
            -moz-appearance: textfield;
        }
        
        .quantity-input::-webkit-outer-spin-button,
        .quantity-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
            display: none;
        }
        
        .quantity-input::-ms-clear {
            display: none;
        }
        
        .cart-section {
            background: #1d1d1d;
            border-radius: 8px;
            padding: 12px;
            margin-top: 16px;
        }
        
        .cart-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #333;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .cart-item-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
            flex-shrink: 0;
        }
        
        .cart-item-info {
            flex: 1;
            min-width: 0;
        }
        
        .cart-item-name {
            font-weight: bold;
            color: #fff;
            font-size: 14px;
            margin-bottom: 4px;
            line-height: 1.3;
        }
        
        .cart-item-details {
            color: #ccc;
            font-size: 12px;
            margin-bottom: 8px;
        }
        
        .cart-item-actions {
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: space-between;
        }
        
        .cart-item-subtotal {
            color: #ffd700;
            font-weight: bold;
            font-size: 14px;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .product-actions .quantity-controls {
            justify-content: center;
        }
        
        .btn-checkout {
            background: #28a745;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            width: 100%;
            margin-top: 12px;
            cursor: pointer;
        }
        
        /* CSS cho nút âm thanh */
        #audio-toggle-btn {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        #audio-toggle-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }
        
        #audio-toggle-btn:active {
            transform: scale(0.95);
        }
        
        /* Animation pulse cho nút âm thanh */
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
            }
        }
        
        /* Hiệu ứng khi âm thanh được bật */
        .audio-enabled {
            background: linear-gradient(45deg, #28a745, #20c997) !important;
            color: white !important;
            border: 2px solid #fff !important;
        }
        
        /* Hiệu ứng khi âm thanh bị tắt */
        .audio-disabled {
            background: linear-gradient(45deg, #6c757d, #495057) !important;
            color: white !important;
        }
    </style>

    <div class="livestream-container">
    <div class="container-fluid py-3">
        <div class="row g-3">
            <!-- Layout 1: Danh sách các phiên live liên quan -->
            <div class="col-lg-3 col-md-4">
                <div class="panel">
                    <h5><i class="fas fa-broadcast-tower"></i> Livestream đang phát</h5>
                    <div class="live-item active">
                        <div class="live-title"><?= htmlspecialchars($livestream['title']) ?></div>
                        <div class="live-streamer">
                            <i class="fas fa-user-circle"></i> 
                            <?= htmlspecialchars($streamer_info['username'] ?? $streamer_info['full_name'] ?? 'Streamer') ?>
                        </div>
                        <div class="live-viewers">
                            <i class="fas fa-eye"></i> 
                            <span id="viewer-count"><?= $current_viewers ?></span> đang xem
                        </div>
                        <div class="live-status">
                            <?php if ($livestream['status'] == 'dang_phat' || $livestream['status'] == 'dang_live'): ?>
                                <span class="status-badge live"><i class="fas fa-circle"></i> Đang phát</span>
                            <?php elseif ($livestream['status'] == 'da_ket_thuc'): ?>
                                <span class="status-badge ended"><i class="fas fa-stop-circle"></i> Đã kết thúc</span>
                            <?php elseif ($livestream['status'] == 'cho_phat' || $livestream['status'] == 'dang_chuan_bi'): ?>
                                <span class="status-badge waiting"><i class="fas fa-clock"></i> Chờ phát</span>
                            <?php else: ?>
                                <span class="status-badge waiting"><i class="fas fa-clock"></i> Chờ phát</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="live-item">
                        <div class="live-title">Livestream khác 1</div>
                        <div class="live-streamer">Streamer 2</div>
                        <div class="live-viewers"><i class="fas fa-eye"></i> 15 đang xem</div>
                    </div>
                    
                    <div class="live-item">
                        <div class="live-title">Livestream khác 2</div>
                        <div class="live-streamer">Streamer 3</div>
                        <div class="live-viewers"><i class="fas fa-eye"></i> 8 đang xem</div>
                    </div>
                </div>
                
                <div class="panel">
                    <h5><i class="fas fa-heart"></i> Thống kê</h5>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="stat">
                                <div class="num" id="viewer-count-stat">0</div>
                                <div>Đang xem</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat">
                                <div class="num" id="like-count-stat">0</div>
                                <div>Lượt thích</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Layout 2: Video phát livestream chính -->
            <div class="col-lg-6 col-md-8">
                <div class="panel">
                    <h5><?= htmlspecialchars($livestream['title']) ?></h5>
                    <div class="text-secondary small mb-2"><?= htmlspecialchars($livestream['description']) ?></div>
                    <div class="streamer-info mb-2">
                        <i class="fas fa-user-circle"></i>
                        <span><?= htmlspecialchars($livestream['username'] ?? 'Streamer') ?></span>
                    </div>
                    
                    <video id="livestream-video" autoplay muted playsinline style="display: none;" onclick="togglePlay()">
                        <source src="" type="video/webm">
                    </video>
                    <div class="video-placeholder" id="video-placeholder">
                        <i class="fas fa-video"></i>
                        <h3>Livestream Video</h3>
                        <p id="connection-status">Đang kết nối với streamer...</p>
                        <div class="loading-spinner" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i>
                            <p>Đang kết nối...</p>
                        </div>
                    </div>
                    
                    <div class="mt-2">
                        <button class="btn btn-outline-light btn-sm" onclick="toggleLike()">
                            <i class="fas fa-heart"></i> Thích
                        </button>
                        <button class="btn btn-outline-light btn-sm ms-2" onclick="shareLivestream()">
                            <i class="fas fa-share"></i> Chia sẻ
                        </button>
                        <button class="btn btn-success btn-sm ms-2" onclick="togglePlay()" id="audio-toggle-btn">
                            <i class="fas fa-volume-mute"></i> Bật âm thanh
                        </button>
                    </div>
                </div>
            </div>

            <!-- Layout 3: Chat trực tiếp và sản phẩm -->
            <div class="col-lg-3">
                <!-- Chat Section -->
                <div class="panel chat-section">
                    <h5><i class="fas fa-comments"></i> Chat trực tiếp</h5>
                    <div class="chat-messages" id="chat-messages">
                        <div class="chat-message">
                            <span class="username">Hệ thống:</span>
                            <span>Chào mừng bạn đến với livestream!</span>
                        </div>
                    </div>
                    <div class="chat-input">
                        <input type="text" id="chat-input" placeholder="Nhập tin nhắn..." maxlength="200">
                        <button class="btn btn-warning" onclick="sendMessage()">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>

                <!-- Products Section -->
                <div class="panel">
                    <h5><i class="fas fa-shopping-bag"></i> Sản phẩm trong lives</h5>
                    <div class="products-section" id="products-section">
                        <?php if (!empty($products)): ?>
                            <?php $index = 1; foreach ($products as $product): ?>
                            <div class="product-item <?= $product['is_pinned'] ? 'pinned' : '' ?>" data-product-id="<?= $product['product_id'] ?>" data-livestream-product-id="<?= $product['id'] ?>" data-display-order="<?= $index ?>">
                                <div class="product-number"><?= $index++ ?></div>
                                <?php 
                                $productImage = $product['anh_dau'] ?? 'default-product.jpg';
                                if (!file_exists('img/' . $productImage)) {
                                    $productImage = 'default-product.jpg';
                                }
                                ?>
                                <img src="img/<?= htmlspecialchars($productImage) ?>" 
                                     alt="<?= htmlspecialchars($product['title']) ?>" 
                                     class="product-image">
                                
                                <div class="product-info">
                                <div class="product-name"><?= htmlspecialchars($product['title']) ?></div>
                                    <div class="product-price">
                                        <?= number_format($product['special_price'] ?: $product['price']) ?> đ
                                        <?php if ($product['special_price'] && $product['special_price'] != $product['price']): ?>
                                            <br><small style="color: #ccc; text-decoration: line-through; font-size: 12px; margin-left: 0;">
                                                <?= number_format($product['price']) ?> đ
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="product-actions">
                                    <div class="quantity-controls">
                                        <button class="quantity-btn" onclick="changeQuantity(<?= $product['product_id'] ?>, -1)">-</button>
                                        <input type="number" class="quantity-input" id="qty-<?= $product['product_id'] ?>" 
                                               value="1" min="1" max="99" onchange="updateQuantity(<?= $product['product_id'] ?>, this.value)">
                                        <button class="quantity-btn" onclick="changeQuantity(<?= $product['product_id'] ?>, 1)">+</button>
                                    </div>
                                    <?php if ($is_streamer): ?>
                                    <button class="btn-pin <?= $product['is_pinned'] ? 'pinned' : '' ?>" 
                                            onclick="pinProduct(<?= $product['product_id'] ?>)">
                                        <i class="fas fa-thumbtack"></i> <?= $product['is_pinned'] ? 'Bỏ ghim' : 'Ghim' ?>
                                    </button>
                                    <?php endif; ?>
                                    <button class="btn-add-cart" onclick="addToCart(<?= $product['product_id'] ?>)">
                                    <i class="fas fa-cart-plus"></i> Thêm
                                </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-secondary text-center py-3">
                                <i class="fas fa-box fa-2x mb-2" style="color: #666;"></i>
                                <p>Chưa có sản phẩm nào</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Cart Section -->
                <div class="panel">
                    <h5><i class="fas fa-shopping-cart"></i> Giỏ hàng của bạn</h5>
                    <div class="cart-section">
                        <div id="cart-items">
                            <?php if (!empty($cart_items)): ?>
                                <?php 
                                $total = 0;
                                foreach ($cart_items as $item): 
                                    $price = $item['price'] ?? 0;
                                    $quantity = $item['quantity'] ?? 0;
                                    $total += $price * $quantity;
                                    
                                    $itemImage = $item['anh_dau'] ?? $item['image'] ?? 'default-product.jpg';
                                    if (!file_exists('img/' . $itemImage)) {
                                        $itemImage = 'default-product.jpg';
                                    }
                                ?>
                                <div class="cart-item" data-item-id="<?= $item['id'] ?? 0 ?>">
                                    <img src="img/<?= htmlspecialchars($itemImage) ?>" 
                                         alt="<?= htmlspecialchars($item['title'] ?? 'Unknown Product') ?>" 
                                         class="cart-item-image">
                                    
                                    <div class="cart-item-info">
                                        <div class="cart-item-name"><?= htmlspecialchars($item['title'] ?? 'Unknown Product') ?></div>
                                        <div class="cart-item-details">
                                            <span><?= number_format($price) ?> đ x <?= $quantity ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="cart-item-actions">
                                        <div class="cart-item-subtotal">
                                            <?= number_format($price * $quantity) ?> đ
                                        </div>
                                        
                                        <button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(<?= $item['id'] ?? 0 ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-shopping-cart fa-2x mb-2" style="color: #666;"></i>
                                    <p>Giỏ hàng trống</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-2">
                            <strong>Tổng cộng: <span id="cart-total"><?= number_format($total) ?></span> đ</strong>
                        </div>
                        <button class="btn-checkout" onclick="checkout()">
                            <i class="fas fa-credit-card"></i> Thanh toán
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Variables
        let ws = null;
        let isConnected = false;
        let viewerCount = <?= $current_viewers ?>; // Khởi tạo từ PHP
        let likeCount = 0;
        let viewerPeer;
        
        const LIVESTREAM_ID = <?= $livestream_id ?>;

        // Initialize WebSocket
        function initWebSocket() {
            ws = new WebSocket('ws://localhost:3000');
            
            ws.onopen = function() {
                console.log('WebSocket connected');
                isConnected = true;
                
                // Join livestream room as viewer
                const userId = <?= isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0 ?>;
                const anonId = userId || ('viewer_' + Date.now());
                ws.send(JSON.stringify({
                    type: 'join_livestream',
                    livestream_id: <?= $livestream_id ?>,
                    user_id: anonId,
                    user_type: 'viewer'
                }));
                
                // Record viewer join in database (cả guest và user đã đăng nhập)
                recordViewerJoin();
                
                // Request current livestream status & ask streamer to send offer
                console.log('📡 Requesting livestream status...');
                ws.send(JSON.stringify({
                    type: 'get_livestream_status',
                    livestream_id: <?= $livestream_id ?>
                }));
                
                // Luôn request offer từ streamer khi kết nối
                console.log('📡 Requesting offer from streamer...');
                ws.send(JSON.stringify({
                    type: 'request_offer',
                    livestream_id: <?= $livestream_id ?>
                }));
                
                // Thêm delay để đảm bảo streamer đã sẵn sàng
                setTimeout(() => {
                    if (ws && ws.readyState === WebSocket.OPEN) {
                        console.log('📡 Re-requesting offer from streamer (delayed)...');
                        ws.send(JSON.stringify({
                            type: 'request_offer',
                            livestream_id: <?= $livestream_id ?>
                        }));
                    }
                }, 2000);
                
                // Also check if livestream is already live
                checkLivestreamStatus();
            };
            
            ws.onmessage = function(event) {
                try {
                    const data = JSON.parse(event.data);
                    handleWebSocketMessage(data);
                } catch (e) {
                    console.error('Error parsing WebSocket message:', e);
                }
            };
            
            ws.onclose = function() {
                console.log('WebSocket disconnected');
                isConnected = false;
                setTimeout(initWebSocket, 3000);
            };
            
            ws.onerror = function(error) {
                console.error('WebSocket error:', error);
            };
        }

        // Handle WebSocket messages
        function handleWebSocketMessage(data) {
            console.log('Viewer received:', data);
            switch(data.type) {
                case 'livestream_viewer_count':
                    viewerCount = data.count;
                    updateViewerCount();
                    break;
                case 'viewers_count_update':
                    // Cập nhật số người xem real-time từ WebSocket
                    viewerCount = data.viewers_count || 0;
                    updateViewerCount();
                    console.log('📊 Viewer count updated:', viewerCount);
                    break;
                case 'viewer_joined':
                    // Có người mới join, cập nhật số người xem
                    viewerCount = data.viewers_count || 0;
                    updateViewerCount();
                    break;
                case 'viewer_left':
                    // Có người rời, cập nhật số người xem
                    viewerCount = data.viewers_count || 0;
                    updateViewerCount();
                    break;
                case 'livestream_joined':
                    console.log('✅ Successfully joined livestream room');
                    // Cập nhật số người xem khi join thành công
                    if (data.viewers_count !== undefined) {
                        viewerCount = data.viewers_count;
                        updateViewerCount();
                    }
                    break;
                case 'livestream_like_count':
                    // Cập nhật lượt thích real-time
                    console.log('❤️ Viewer received livestream_like_count:', data);
                    likeCount = data.count || 0;
                    console.log('❤️ Viewer: Updating like count to', likeCount);
                    updateLikeCount();
                    console.log('❤️ Viewer: Like count updated successfully to', likeCount);
                    break;
                case 'livestream_chat':
                    const displayName = data.username || 'Khách';
                    const isStreamer = data.user_id == <?= $livestream['user_id'] ?? 0 ?>;
                    const nameWithIcon = isStreamer ? displayName + ' <i class="fas fa-home text-warning"></i>' : displayName;
                    addChatMessage(nameWithIcon, data.message);
                    break;
                case 'product_pinned':
                    updatePinnedProduct(data.product);
                    break;
                case 'product_unpinned':
                    removePinnedProduct();
                    break;
                case 'livestream_started':
                    startLivestreamVideo();
                    connectToVideoStream();
                    break;
                case 'livestream_stopped':
                    stopLivestreamVideo();
                    break;
                case 'webrtc_offer':
                    console.log('🎯 Received offer from streamer → creating answer...');
                    (async()=>{
                        try {
                            await ensurePeer();
                            
                            // Kiểm tra trạng thái signaling trước khi xử lý
                            if (viewerPeer.signalingState === 'stable') {
                                console.log('📡 Setting remote description...');
                                await viewerPeer.setRemoteDescription(new RTCSessionDescription(data.sdp));
                                console.log('📡 Creating answer...');
                                const answer = await viewerPeer.createAnswer();
                                console.log('📡 Setting local description...');
                                await viewerPeer.setLocalDescription(answer);
                                console.log('📡 Sending answer to WebSocket...');
                                ws.send(JSON.stringify({type:'webrtc_answer', livestream_id: <?= $livestream_id ?>, sdp: answer}));
                                console.log('✅ Answer sent ✔');
                            } else {
                                console.log('⚠️ Signaling state not stable:', viewerPeer.signalingState, '- skipping offer');
                            }
                        } catch (error) {
                            console.error('❌ Error in webrtc_offer handling:', error);
                        }
                    })();
                    break;
                case 'webrtc_ice':
                    console.log('🧊 Received ICE candidate from streamer');
                    (async()=>{
                        try {
                            await ensurePeer();
                            if (data.candidate) {
                                await viewerPeer.addIceCandidate(new RTCIceCandidate(data.candidate)); 
                                console.log('✅ ICE candidate added');
                            }
                        } catch (e) {
                            console.log('❌ ICE candidate error:', e);
                        }
                    })();
                    break;
                default:
                    console.log('❓ Unknown message type:', data.type);
            }
        }

        // WebRTC functions
        async function ensurePeer(){
            if (viewerPeer) {
                console.log('Using existing RTCPeerConnection');
                return viewerPeer;
            }
            console.log('Creating new RTCPeerConnection...');
            viewerPeer = new RTCPeerConnection({
                iceServers:[
                    {urls:'stun:stun.l.google.com:19302'},
                    {urls:'stun:stun1.l.google.com:19302'}
                ]
            });
            viewerPeer.ontrack = ev => {
                console.log('🎥 Remote track received ✔', ev.streams[0]);
                const video = document.getElementById('livestream-video');
                const spinner = document.querySelector('.loading-spinner');
                if (spinner) spinner.style.display = 'none';
                video.srcObject = ev.streams[0];
                video.style.display = 'block';
                document.getElementById('video-placeholder').style.display = 'none';
                console.log('✅ Video element updated with remote stream');
                
                // Debug audio tracks
                const stream = ev.streams[0];
                const audioTracks = stream.getAudioTracks();
                const videoTracks = stream.getVideoTracks();
                console.log('🎵 Audio tracks:', audioTracks.length);
                console.log('🎥 Video tracks:', videoTracks.length);
                
                if (audioTracks.length > 0) {
                    console.log('🔊 Audio track details:', {
                        id: audioTracks[0].id,
                        kind: audioTracks[0].kind,
                        enabled: audioTracks[0].enabled,
                        muted: audioTracks[0].muted,
                        readyState: audioTracks[0].readyState
                    });
                    
                    // Hiển thị thông báo hướng dẫn bật âm thanh
                    setTimeout(() => {
                        const audioBtn = document.getElementById('audio-toggle-btn');
                        if (audioBtn && video.muted) {
                            audioBtn.style.animation = 'pulse 2s infinite';
                            console.log('💡 Video loaded with audio - click button to unmute');
                        }
                    }, 1000);
                } else {
                    console.log('⚠️ No audio tracks found in stream');
                }
            };
            viewerPeer.onicecandidate = ev => {
                if (ev.candidate) {
                    console.log('🧊 Sending ICE candidate to streamer');
                    ws && ws.readyState === 1 && ws.send(JSON.stringify({
                        type:'webrtc_ice', livestream_id: <?= $livestream_id ?>, candidate: ev.candidate
                    }));
                }
            };
            viewerPeer.onconnectionstatechange = () => {
                console.log('🔗 Connection state:', viewerPeer.connectionState);
                if (viewerPeer.connectionState === 'disconnected' || viewerPeer.connectionState === 'failed') {
                    console.log('🔄 Connection lost, attempting to reconnect...');
                    // Reset peer connection
                    viewerPeer = null;
                    // Request new offer
                    setTimeout(() => {
                        if (ws && ws.readyState === WebSocket.OPEN) {
                            console.log('🔄 Requesting new offer...');
                            ws.send(JSON.stringify({type:'request_offer', livestream_id: <?= $livestream_id ?>}));
                        }
                    }, 1000);
                }
            };
            viewerPeer.oniceconnectionstatechange = () => {
                console.log('🧊 ICE connection state:', viewerPeer.iceConnectionState);
                if (viewerPeer.iceConnectionState === 'disconnected' || viewerPeer.iceConnectionState === 'failed') {
                    console.log('🧊 ICE connection lost');
                }
            };
            return viewerPeer;
        }
        
        function connectToVideoStream() {
            console.log('connectToVideoStream called');
            if (!ws || ws.readyState !== WebSocket.OPEN) {
                console.log('WebSocket not ready:', ws ? ws.readyState : 'null');
                const statusEl = document.getElementById('connection-status');
                if (statusEl) statusEl.textContent = 'Đang kết nối WebSocket...';
                return;
            }
            
            // Cập nhật thông báo
            const statusEl = document.getElementById('connection-status');
            if (statusEl) statusEl.textContent = 'Đang yêu cầu video từ streamer...';
            
            // Ask streamer for an offer immediately
            console.log('Requesting offer from streamer...');
            ws.send(JSON.stringify({type:'request_offer', livestream_id: <?= $livestream_id ?>}));
            
            // Retry nếu không nhận được offer trong 5 giây
            setTimeout(() => {
                const video = document.getElementById('livestream-video');
                if (video && video.style.display === 'none') {
                    console.log('No video received, retrying...');
                    if (statusEl) statusEl.textContent = 'Đang thử kết nối lại...';
                    ws.send(JSON.stringify({type:'request_offer', livestream_id: <?= $livestream_id ?>}));
                }
            }, 5000);
        }

        // Video functions
        function startLivestreamVideo() {
            console.log('Livestream started - connecting to video stream...');
            const spinner = document.querySelector('.loading-spinner');
            if (spinner) spinner.style.display = 'block';
            connectToVideoStream();
        }

        function stopLivestreamVideo() {
            console.log('Livestream stopped');
            const video = document.getElementById('livestream-video');
            const placeholder = document.getElementById('video-placeholder');
            video.style.display = 'none';
            placeholder.style.display = 'flex';
            const spinner = document.querySelector('.loading-spinner');
            if (spinner) spinner.style.display = 'none';
        }

        // Toggle play and unmute
        function togglePlay() {
            const video = document.getElementById('livestream-video');
            const audioBtn = document.getElementById('audio-toggle-btn');
            console.log('🎵 Audio button clicked - toggling audio');
            
            if (video.muted) {
                video.muted = false;
                video.volume = 1.0;
                console.log('🔊 Audio unmuted, volume set to 1.0');
                
                // Cập nhật nút
                audioBtn.innerHTML = '<i class="fas fa-volume-up"></i> Tắt âm thanh';
                audioBtn.className = 'btn btn-warning btn-sm ms-2';
                
                // Thêm visual indicator
                video.style.border = '3px solid #28a745';
                setTimeout(() => {
                    video.style.border = 'none';
                }, 2000);
            } else {
                video.muted = true;
                console.log('🔇 Audio muted');
                
                // Cập nhật nút
                audioBtn.innerHTML = '<i class="fas fa-volume-mute"></i> Bật âm thanh';
                audioBtn.className = 'btn btn-success btn-sm ms-2';
            }
            
            // Đảm bảo video đang play
            if (video.paused) {
                video.play().catch(e => console.log('Play error:', e));
            }
        }

        // Check livestream status
        function checkLivestreamStatus() {
            fetch('api/livestream-api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_status&livestream_id=<?= $livestream_id ?>'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.status === 'dang_live') {
                    console.log('Livestream is already live, starting video..');
                    startLivestreamVideo();
                }
            })
            .catch(error => {
                console.error('Error checking livestream status:', error);
            });
        }

        // Update viewer count
        function updateViewerCount() {
            const viewerCountEl = document.getElementById('viewer-count');
            const viewerCountStatEl = document.getElementById('viewer-count-stat');
            if (viewerCountEl) viewerCountEl.textContent = viewerCount;
            if (viewerCountStatEl) viewerCountStatEl.textContent = viewerCount;
        }

        // Update like count
        function updateLikeCount() {
            const likeCountEl = document.getElementById('like-count-stat');
            console.log('❤️ updateLikeCount called, likeCount:', likeCount, 'Element:', likeCountEl);
            if (likeCountEl) {
                likeCountEl.textContent = likeCount;
                console.log('✅ Like count element updated to:', likeCount);
            } else {
                console.warn('⚠️ Like count element not found!');
            }
        }

        // Record viewer join in database
        function recordViewerJoin() {
            fetch('api/livestream-api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=join_livestream&livestream_id=${LIVESTREAM_ID}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('✅ Viewer join recorded in database');
                } else {
                    console.warn('⚠️ Failed to record viewer join:', data.message);
                }
            })
            .catch(error => {
                console.error('Error recording viewer join:', error);
            });
        }

        // Khởi tạo số người xem ban đầu từ database
        function initViewerCount() {
            // Số người xem ban đầu từ PHP
            viewerCount = <?= $current_viewers ?>;
            updateViewerCount();
        }
        
        // Gọi khi trang load
        initViewerCount();

        // Chat functions
        function addChatMessage(sender, message) {
            const chatMessages = document.getElementById('chat-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'chat-message';
            messageDiv.innerHTML = `<span class="username"><b>${sender}:</b></span> <span>${message}</span>`;
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function sendMessage() {
            const chatInput = document.getElementById('chat-input');
            const message = chatInput.value.trim();
            
            if (message && ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({
                    type: 'livestream_chat',
                    livestream_id: <?= $livestream_id ?>,
                    message: message,
                    username: '<?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Khách' ?>',
                    user_id: <?= isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0 ?>
                }));
                
                chatInput.value = '';
            }
        }

        // Product functions
        function addToCart(productId) {
            const quantity = document.getElementById('qty-' + productId).value || 1;
            
            if (quantity < 1) {
                alert('Số lượng phải lớn hơn 0');
                return;
            }
            
            fetch('api/livestream-api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add_to_cart&product_id=${productId}&livestream_id=<?= $livestream_id ?>&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Đã thêm sản phẩm vào giỏ hàng!', 'success');
                    loadCart();
                } else {
                    showToast('Lỗi: ' + (data.message || 'Không thể thêm sản phẩm'), 'error');
                }
            })
            .catch(error => {
                console.error('Error adding to cart:', error);
                showToast('Lỗi khi thêm sản phẩm vào giỏ hàng', 'error');
            });
        }

        function pinProduct(productId) {
            fetch('api/livestream-api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=pin_product&product_id=${productId}&livestream_id=<?= $livestream_id ?>`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Đã cập nhật trạng thái ghim sản phẩm!', 'success');
                    location.reload(); // Reload để cập nhật UI
                } else {
                    showToast('Lỗi: ' + (data.message || 'Không thể ghim sản phẩm'), 'error');
                }
            })
            .catch(error => {
                console.error('Error pinning product:', error);
                showToast('Lỗi khi ghim sản phẩm', 'error');
            });
        }
        
        function changeQuantity(productId, change) {
            const input = document.getElementById('qty-' + productId);
            let newValue = parseInt(input.value) + change;
            
            if (newValue < 1) newValue = 1;
            if (newValue > 99) newValue = 99;
            
            input.value = newValue;
        }
        
        function updateQuantity(productId, value) {
            const input = document.getElementById('qty-' + productId);
            let newValue = parseInt(value);
            
            if (isNaN(newValue) || newValue < 1) newValue = 1;
            if (newValue > 99) newValue = 99;
            
            input.value = newValue;
        }
        
        function changeCartQuantity(itemId, change) {
            const input = document.getElementById('cart-qty-' + itemId);
            const newQuantity = parseInt(input.value) + change;
            if (newQuantity < 1) return;
            
            input.value = newQuantity;
            updateCartQuantity(itemId, newQuantity);
        }
        
        function updateCartQuantity(itemId, newQuantity) {
            if (newQuantity < 1) {
                removeFromCart(itemId);
                return;
            }
            
            fetch('api/livestream-api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_cart_quantity_by_item&item_id=${itemId}&quantity=${newQuantity}&livestream_id=<?= $livestream_id ?>`
            })
            .then(response => response.json())
            .then(data => {
                console.log('Update cart quantity response:', data);
                if (data.success) {
                    updateCartDisplay(data.cart);
                } else {
                    console.error('Update cart quantity error:', data.message);
                    showToast('Lỗi: ' + (data.message || 'Không thể cập nhật số lượng'), 'error');
                }
            })
            .catch(error => {
                console.error('Error updating cart quantity:', error);
                showToast('Lỗi khi cập nhật số lượng', 'error');
            });
        }
        
        function loadCart() {
            fetch(`api/livestream-api.php?action=get_cart&livestream_id=<?= $livestream_id ?>`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartDisplay(data.cart);
                }
            })
            .catch(error => {
                console.error('Error loading cart:', error);
            });
        }
        
        function updateCartDisplay(cart) {
            const cartItems = document.getElementById('cart-items');
            const cartTotal = document.getElementById('cart-total');
            
            if (cart.items && cart.items.length > 0) {
                cartItems.innerHTML = cart.items.map(item => {
                    const itemImage = item.anh_dau || item.image || 'default-product.jpg';
                    return `
                        <div class="cart-item" data-item-id="${item.id}">
                            <img src="img/${itemImage}" alt="${item.title}" class="cart-item-image">
                            <div class="cart-item-info">
                                <div class="cart-item-name">${item.title}</div>
                                <div class="cart-item-details">
                                    <span>${formatMoney(item.price)} đ x ${item.quantity}</span>
                                </div>
                            </div>
                            <div class="cart-item-actions">
                                <div class="cart-item-subtotal">
                                    ${formatMoney(item.price * item.quantity)} đ
                                </div>
                                <div class="quantity-controls">
                                    <button class="quantity-btn" onclick="changeCartQuantity(${item.id}, -1)">-</button>
                                    <input type="number" class="quantity-input" id="cart-qty-${item.id}" 
                                           value="${item.quantity}" min="1" max="99" 
                                           onchange="updateCartQuantity(${item.id}, this.value)">
                                    <button class="quantity-btn" onclick="changeCartQuantity(${item.id}, 1)">+</button>
                                </div>
                                <button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${item.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                cartItems.innerHTML = `
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-shopping-cart fa-2x mb-2" style="color: #666;"></i>
                        <p>Giỏ hàng trống</p>
                    </div>
                `;
            }
            
            if (cartTotal) {
                cartTotal.textContent = formatMoney(cart.total || 0);
            }
        }
        
        function formatMoney(amount) {
            return new Intl.NumberFormat('vi-VN').format(amount);
        }
        
        function showToast(message, type = 'info') {
            // Sử dụng Toastify nếu có, hoặc alert
            if (typeof Toastify !== 'undefined') {
                Toastify({
                    text: message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#007bff'
                }).showToast();
            } else {
                alert(message);
            }
        }

        function removeFromCart(itemId) {
            if (confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
            fetch('api/livestream-api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                    body: `action=remove_from_cart_by_item&item_id=${itemId}&livestream_id=<?= $livestream_id ?>`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                        showToast('Đã xóa sản phẩm khỏi giỏ hàng', 'success');
                        updateCartDisplay(data.cart);
                } else {
                        showToast('Lỗi: ' + (data.message || 'Không thể xóa sản phẩm'), 'error');
                }
            })
            .catch(error => {
                console.error('Error removing from cart:', error);
                    showToast('Lỗi khi xóa sản phẩm khỏi giỏ hàng', 'error');
            });
            }
        }

        function checkout() {
            const cartItems = document.getElementById('cart-items');
            if (cartItems.children.length === 0 || cartItems.querySelector('.text-center')) {
                showToast('Giỏ hàng trống!', 'error');
                return;
            }
            
            // Chuyển đến trang thanh toán
            window.location.href = `/index.php?checkout&livestream_id=<?= $livestream_id ?>`;
        }

        // Like function - không giới hạn số lần thích
        function toggleLike() {
            const userId = <?= isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0 ?>;
            const livestreamId = <?= $livestream_id ?>;
            
            // Chỉ cho phép user đã đăng nhập mới có thể thích
            if (!userId || userId <= 0) {
                alert('Vui lòng đăng nhập để thích livestream này!');
                return;
            }
            
            console.log('❤️ User clicking like button, userId:', userId, 'livestreamId:', livestreamId);
            
            // Visual feedback ngay lập tức
            const likeBtn = event.target.closest('button');
            if (likeBtn) {
                likeBtn.style.transform = 'scale(1.2)';
                likeBtn.style.color = '#ff4757';
                setTimeout(() => {
                    likeBtn.style.transform = 'scale(1)';
                    likeBtn.style.color = '';
                }, 200);
            }
            
            // Gọi API trực tiếp từ frontend (đơn giản hơn và dễ debug)
            const formData = new FormData();
            formData.append('action', 'record_interaction');
            formData.append('livestream_id', livestreamId);
            formData.append('user_id', userId);
            formData.append('action_type', 'like');
            
            console.log('❤️ Calling API directly to record like');
            fetch('api/livestream-api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('📥 API response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('📥 API response data:', data);
                if (data.success) {
                    console.log('✅ Like recorded successfully via API');
                    // Sau khi ghi thành công, gửi qua WebSocket để broadcast
                    if (ws && ws.readyState === WebSocket.OPEN) {
                        console.log('📡 Broadcasting like via WebSocket');
                        ws.send(JSON.stringify({
                            type: 'livestream_like_broadcast',
                            livestream_id: livestreamId
                        }));
                    }
                    // Refresh like count
                    refreshLikeCount();
                } else {
                    console.error('❌ Failed to record like:', data.message);
                    alert('Không thể thích: ' + (data.message || 'Lỗi không xác định'));
                }
            })
            .catch(error => {
                console.error('❌ Error calling like API:', error);
                alert('Có lỗi xảy ra khi thích. Vui lòng thử lại!');
            });
        }
        
        // Refresh like count từ API
        function refreshLikeCount() {
            const livestreamId = <?= $livestream_id ?>;
            fetch(`api/livestream-api.php?action=get_realtime_stats&livestream_id=${livestreamId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.stats) {
                        const newLikeCount = data.stats.like_count || 0;
                        console.log('📊 Refreshed like count:', newLikeCount);
                        likeCount = newLikeCount;
                        updateLikeCount();
                    }
                })
                .catch(error => {
                    console.error('❌ Error refreshing like count:', error);
                });
        }

        // Share function
        function shareLivestream() {
            if (navigator.share) {
                navigator.share({
                    title: '<?= htmlspecialchars($livestream['title']) ?>',
                    text: 'Xem livestream này!',
                    url: window.location.href
                });
            } else {
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Đã copy link vào clipboard!');
                });
            }
        }

        // Enter key for chat
        document.addEventListener('DOMContentLoaded', function() {
            const chatInput = document.getElementById('chat-input');
            if (chatInput) {
                chatInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        sendMessage();
                    }
                });
            }
            
            initWebSocket();
            loadCart(); // Load giỏ hàng khi trang load
            
            // Tự động kết nối video sau khi WebSocket kết nối
            setTimeout(() => {
                connectToVideoStream();
            }, 3000);
            
            // Kiểm tra trạng thái livestream định kỳ
            setInterval(function() {
                fetch('api/livestream-api.php?action=get_livestream&id=<?= $livestream_id ?>')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.livestream) {
                            const status = data.livestream.status;
                            if (status !== 'dang_live' && status !== 'dang_dien_ra') {
                                // Livestream đã kết thúc, redirect về trang danh sách
                                alert('Livestream đã kết thúc');
                                window.location.href = 'index.php?livestream';
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error checking livestream status:', error);
                    });
            }, 10000); // Kiểm tra mỗi 10 giây

            // Real-time update cho sản phẩm ghim
            setInterval(function() {
                updatePinnedProducts();
            }, 3000); // Cập nhật mỗi 3 giây
         });

        // Hàm cập nhật sản phẩm ghim real-time
        function updatePinnedProducts() {
            fetch(`api/livestream-api.php?action=get_products_status&livestream_id=<?= $livestream_id ?>`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.products) {
                        updateProductsDisplay(data.products);
                    }
                })
                .catch(error => {
                    console.error('Error updating pinned products:', error);
                });
        }

        // Hàm cập nhật hiển thị sản phẩm
        function updateProductsDisplay(products) {
            const productsSection = document.getElementById('products-section');
            if (!productsSection) return;

            // Lưu trạng thái hiện tại của các sản phẩm
            const currentProducts = Array.from(productsSection.querySelectorAll('.product-item')).map(item => {
                return {
                    id: item.dataset.productId,
                    isPinned: item.classList.contains('pinned')
                };
            });

            // Cập nhật trạng thái ghim cho từng sản phẩm
            products.forEach((product) => {
                const productElement = productsSection.querySelector(`[data-product-id="${product.product_id}"]`);
                if (productElement) {
                    const wasPinned = productElement.classList.contains('pinned');
                    const isPinned = product.is_pinned;

                    // Cập nhật class pinned
                    if (isPinned && !wasPinned) {
                        productElement.classList.add('pinned');
                        // Thêm hiệu ứng khi sản phẩm được ghim
                        showPinAnimation(productElement);
                    } else if (!isPinned && wasPinned) {
                        productElement.classList.remove('pinned');
                        // Thêm hiệu ứng khi sản phẩm bị bỏ ghim
                        showUnpinAnimation(productElement);
                    }

                    // Cập nhật nút ghim
                    const pinButton = productElement.querySelector('.btn-pin');
                    if (pinButton) {
                        if (isPinned) {
                            pinButton.classList.add('pinned');
                            pinButton.innerHTML = '<i class="fas fa-thumbtack"></i> Bỏ ghim';
                        } else {
                            pinButton.classList.remove('pinned');
                            pinButton.innerHTML = '<i class="fas fa-thumbtack"></i> Ghim';
                        }
                    }
                }
            });

            // Sắp xếp lại sản phẩm: ghim lên đầu nhưng giữ nguyên số thứ tự
            const allProducts = Array.from(productsSection.querySelectorAll('.product-item'));
            
            // Sắp xếp: ghim trước, sau đó theo số thứ tự hiển thị
            allProducts.sort((a, b) => {
                const aPinned = a.classList.contains('pinned');
                const bPinned = b.classList.contains('pinned');
                const aOrder = parseInt(a.dataset.displayOrder);
                const bOrder = parseInt(b.dataset.displayOrder);
                
                if (aPinned && !bPinned) return -1;
                if (!aPinned && bPinned) return 1;
                return aOrder - bOrder;
            });
            
            // Xóa tất cả sản phẩm
            productsSection.innerHTML = '';
            
            // Thêm lại sản phẩm đã sắp xếp và cập nhật số thứ tự
            allProducts.forEach((product, index) => {
                const productNumber = product.querySelector('.product-number');
                if (productNumber) {
                    productNumber.textContent = index + 1;
                }
                productsSection.appendChild(product);
            });
        }

        // Hiệu ứng khi sản phẩm được ghim
        function showPinAnimation(element) {
            element.classList.add('pinning');
            
            setTimeout(() => {
                element.classList.remove('pinning');
            }, 1000);
        }

        // Hiệu ứng khi sản phẩm bị bỏ ghim
        function showUnpinAnimation(element) {
            element.style.transform = 'scale(0.95)';
            element.style.opacity = '0.7';
            
            setTimeout(() => {
                element.style.transform = 'scale(1)';
                element.style.opacity = '1';
            }, 500);
        }
     </script>
    </div>

<?php include_once __DIR__ . "/footer.php"; ?>
