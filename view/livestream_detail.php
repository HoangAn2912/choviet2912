<?php
include_once("view/header.php");
?>

<style>
/* CSS cho trang livestream chi tiết */
.livestream-container {
    background: #000;
    min-height: 100vh;
    color: white;
}

.video-section {
    position: relative;
    background: #000;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 20px;
}

.video-player {
    position: relative;
    width: 100%;
    height: 500px;
    background: #000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.video-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, #1a1a1a, #2d2d2d);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #ccc;
}

.live-overlay {
    position: absolute;
    top: 20px;
    left: 20px;
    right: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 10;
}

.live-indicator {
    background: #ff0000;
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 8px;
}

.live-indicator .pulse {
    width: 8px;
    height: 8px;
    background: white;
    border-radius: 50%;
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.3; }
    100% { opacity: 1; }
}

.viewer-count {
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.livestream-sidebar {
    background: #1a1a1a;
    border-radius: 10px;
    padding: 20px;
    height: 500px;
    overflow-y: auto;
}

.chat-section {
    margin-bottom: 20px;
}

.chat-messages {
    height: 300px;
    overflow-y: auto;
    padding: 10px;
    background: #2d2d2d;
    border-radius: 8px;
    margin-bottom: 10px;
}

.chat-message {
    margin-bottom: 10px;
    padding: 8px;
    background: #3d3d3d;
    border-radius: 8px;
}

.chat-message .username {
    font-weight: bold;
    color: #4CAF50;
    margin-right: 8px;
}

.chat-message .content {
    color: #fff;
}

.chat-input {
    display: flex;
    gap: 10px;
}

.chat-input input {
    flex: 1;
    padding: 10px;
    border: none;
    border-radius: 20px;
    background: #3d3d3d;
    color: white;
}

.chat-input button {
    padding: 10px 20px;
    background: #4CAF50;
    color: white;
    border: none;
    border-radius: 20px;
    cursor: pointer;
}

.cart-section {
    background: #2d2d2d;
    border-radius: 8px;
    padding: 15px;
}

.cart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #444;
}

.cart-items {
    max-height: 200px;
    overflow-y: auto;
    margin-bottom: 15px;
}

.cart-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 0;
    border-bottom: 1px solid #444;
}

.cart-item img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 5px;
}

.cart-item-info {
    flex: 1;
}

.cart-item-info h6 {
    margin: 0;
    color: white;
    font-size: 14px;
}

.cart-item-info .price {
    color: #4CAF50;
    font-weight: bold;
}

.cart-item-quantity {
    display: flex;
    align-items: center;
    gap: 5px;
}

.cart-item-quantity button {
    width: 25px;
    height: 25px;
    border: none;
    background: #4CAF50;
    color: white;
    border-radius: 3px;
    cursor: pointer;
}

.cart-item-quantity input {
    width: 40px;
    text-align: center;
    background: #3d3d3d;
    color: white;
    border: none;
    border-radius: 3px;
}

.cart-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-top: 1px solid #444;
    font-weight: bold;
    font-size: 18px;
}

.cart-actions {
    display: flex;
    gap: 10px;
}

.cart-actions button {
    flex: 1;
    padding: 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
}

.btn-view-cart {
    background: #2196F3;
    color: white;
}

.btn-checkout {
    background: #f44336;
    color: white;
}

.pinned-product-display {
    background: #2d2d2d;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}

.pinned-product {
    display: flex;
    gap: 15px;
    align-items: center;
}

.pinned-product img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}

.pinned-product-info h5 {
    margin: 0 0 5px 0;
    color: white;
}

.pinned-product-info .price {
    color: #4CAF50;
    font-weight: bold;
    font-size: 18px;
}

.pinned-product-info .description {
    color: #ccc;
    font-size: 14px;
    margin: 5px 0;
}

.pinned-product-actions {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.pinned-product-actions button {
    padding: 8px 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
}

.btn-add-to-cart {
    background: #4CAF50;
    color: white;
}

.btn-contact {
    background: #2196F3;
    color: white;
}

.streamer-info {
    background: #2d2d2d;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}

.streamer-info h5 {
    margin: 0 0 10px 0;
    color: white;
}

.streamer-info .stats {
    display: flex;
    gap: 20px;
    margin-top: 10px;
}

.stat-item {
    text-align: center;
}

.stat-item .number {
    font-size: 18px;
    font-weight: bold;
    color: #4CAF50;
}

.stat-item .label {
    font-size: 12px;
    color: #ccc;
}

/* Responsive */
@media (max-width: 768px) {
    .livestream-container {
        padding: 10px;
    }
    
    .video-player {
        height: 300px;
    }
    
    .livestream-sidebar {
        height: auto;
        margin-top: 20px;
    }
    
    .chat-messages {
        height: 200px;
    }
}
</style>

<div class="container-fluid livestream-container">
    <div class="row">
        <!-- Video Section -->
        <div class="col-lg-8">
            <div class="video-section">
                <div class="video-player">
                    <div class="video-placeholder">
                        <i class="fas fa-video fa-3x mb-3"></i>
                        <h4>Livestream: <?= htmlspecialchars($livestream['title']) ?></h4>
                        <p>Đang chuẩn bị phát sóng...</p>
                    </div>
                    
                    <div class="live-overlay">
                        <div class="live-indicator">
                            <div class="pulse"></div>
                            LIVE
                        </div>
                        <div class="viewer-count">
                            <i class="fas fa-eye"></i>
                            <span id="viewer-count"><?= $stats['total_viewers'] ?? 0 ?></span> đang xem
                        </div>
                    </div>
                </div>
                
                <!-- Pinned Product Display -->
                <?php if ($pinned_product): ?>
                <div class="pinned-product-display">
                    <h5><i class="fas fa-thumbtack text-danger"></i> Sản phẩm đang bán</h5>
                    <div class="pinned-product">
                        <img src="img/<?= htmlspecialchars($pinned_product['anh_dau']) ?>" alt="Sản phẩm">
                        <div class="pinned-product-info">
                            <h5><?= htmlspecialchars($pinned_product['title']) ?></h5>
                            <div class="price">
                                <?= number_format($pinned_product['special_price'] ?: $pinned_product['price']) ?> đ
                            </div>
                            <div class="description">
                                <?= htmlspecialchars($pinned_product['description']) ?>
                            </div>
                            <div class="pinned-product-actions">
                                <button class="btn-add-to-cart" onclick="addToCart(<?= $pinned_product['product_id'] ?>)">
                                    <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                                </button>
                                <button class="btn-contact" onclick="contactSeller('<?= htmlspecialchars($livestream['phone']) ?>')">
                                    <i class="fas fa-phone"></i> Liên hệ
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="livestream-sidebar">
                <!-- Streamer Info -->
                <div class="streamer-info">
                    <h5>
                        <img src="img/<?= htmlspecialchars($livestream['avatar'] ?: 'default-avatar.jpg') ?>" 
                             class="rounded-circle mr-2" width="30" height="30" style="object-fit: cover;">
                        <?= htmlspecialchars($livestream['username']) ?>
                    </h5>
                    <p class="mb-0"><?= htmlspecialchars($livestream['description']) ?></p>
                    <div class="stats">
                        <div class="stat-item">
                            <div class="number"><?= $stats['total_viewers'] ?? 0 ?></div>
                            <div class="label">Người xem</div>
                        </div>
                        <div class="stat-item">
                            <div class="number"><?= $stats['total_orders'] ?? 0 ?></div>
                            <div class="label">Đơn hàng</div>
                        </div>
                        <div class="stat-item">
                            <div class="number"><?= number_format($stats['total_revenue'] ?? 0) ?></div>
                            <div class="label">Doanh thu</div>
                        </div>
                    </div>
                </div>
                
                <!-- Chat Section -->
                <div class="chat-section">
                    <h6><i class="fas fa-comments"></i> Chat trực tiếp</h6>
                    <div class="chat-messages" id="chat-messages">
                        <!-- Chat messages will be loaded here -->
                    </div>
                    <div class="chat-input">
                        <input type="text" id="chat-input" placeholder="Nhập tin nhắn..." maxlength="200">
                        <button onclick="sendMessage()">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Cart Section -->
                <div class="cart-section">
                    <div class="cart-header">
                        <h6><i class="fas fa-shopping-cart"></i> Giỏ hàng Live</h6>
                        <span class="badge badge-primary" id="cart-count">0</span>
                    </div>
                    <div class="cart-items" id="cart-items">
                        <!-- Cart items will be loaded here -->
                    </div>
                    <div class="cart-total">
                        <span>Tổng:</span>
                        <span id="cart-total">0 đ</span>
                    </div>
                    <div class="cart-actions">
                        <button class="btn-view-cart" onclick="viewCart()">
                            <i class="fas fa-eye"></i> Xem giỏ
                        </button>
                        <button class="btn-checkout" onclick="checkout()">
                            <i class="fas fa-credit-card"></i> Thanh toán
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Biến global
const livestreamId = <?= $livestream['id'] ?>;
const userId = <?= $_SESSION['user_id'] ?? 0 ?>;
let cart = { items: [], total: 0, item_count: 0 };

// WebSocket connection cho chat realtime
let liveSocket = null;

// Khởi tạo
document.addEventListener('DOMContentLoaded', function() {
    if (userId > 0) {
        connectWebSocket();
        loadCart();
        loadChatMessages();
    }
    
    // Cập nhật viewer count mỗi 5 giây
    setInterval(updateViewerCount, 5000);
});

// Kết nối WebSocket
function connectWebSocket() {
    const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
    const wsUrl = `${protocol}//${window.location.hostname}:3000/livestream`;
    
    liveSocket = new WebSocket(wsUrl);
    
    liveSocket.onopen = function() {
        console.log('Connected to livestream WebSocket');
        // Đăng ký với livestream
        liveSocket.send(JSON.stringify({
            type: 'join_livestream',
            livestream_id: livestreamId,
            user_id: userId
        }));
    };
    
    liveSocket.onmessage = function(event) {
        const data = JSON.parse(event.data);
        handleWebSocketMessage(data);
    };
    
    liveSocket.onclose = function() {
        console.log('Disconnected from WebSocket');
        // Thử kết nối lại sau 3 giây
        setTimeout(connectWebSocket, 3000);
    };
}

// Xử lý tin nhắn WebSocket
function handleWebSocketMessage(data) {
    switch(data.type) {
        case 'chat_message':
            addChatMessage(data);
            break;
        case 'product_pinned':
            updatePinnedProduct(data.product);
            break;
        case 'cart_updated':
            updateCartDisplay(data.cart);
            break;
        case 'viewer_count':
            updateViewerCountDisplay(data.count);
            break;
        case 'order_placed':
            showOrderNotification(data.order);
            break;
    }
}

// Gửi tin nhắn chat
function sendMessage() {
    const input = document.getElementById('chat-input');
    const message = input.value.trim();
    
    if (message && liveSocket && liveSocket.readyState === WebSocket.OPEN) {
        liveSocket.send(JSON.stringify({
            type: 'chat_message',
            livestream_id: livestreamId,
            user_id: userId,
            content: message
        }));
        input.value = '';
    }
}

// Thêm tin nhắn vào chat
function addChatMessage(data) {
    const chatMessages = document.getElementById('chat-messages');
    const messageDiv = document.createElement('div');
    messageDiv.className = 'chat-message';
    messageDiv.innerHTML = `
        <span class="username">${data.username}:</span>
        <span class="content">${data.content}</span>
    `;
    
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Thêm vào giỏ hàng
function addToCart(productId) {
    if (userId === 0) {
        alert('Vui lòng đăng nhập để mua hàng');
        return;
    }
    
    fetch('controller/cLivestream.php?action=add_to_cart', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `livestream_id=${livestreamId}&product_id=${productId}&quantity=1`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cart = data.cart;
            updateCartDisplay(cart);
            showToast('Đã thêm vào giỏ hàng', 'success');
        } else {
            showToast(data.message, 'error');
        }
    });
}

// Tải giỏ hàng
function loadCart() {
    fetch(`controller/cLivestream.php?action=get_cart&livestream_id=${livestreamId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cart = data.cart;
            updateCartDisplay(cart);
        }
    });
}

// Cập nhật hiển thị giỏ hàng
function updateCartDisplay(cartData) {
    const cartItems = document.getElementById('cart-items');
    const cartCount = document.getElementById('cart-count');
    const cartTotal = document.getElementById('cart-total');
    
    cartCount.textContent = cartData.item_count;
    cartTotal.textContent = formatMoney(cartData.total);
    
    cartItems.innerHTML = '';
    
    cartData.items.forEach(item => {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'cart-item';
        itemDiv.innerHTML = `
            <img src="img/${item.anh_dau}" alt="${item.title}">
            <div class="cart-item-info">
                <h6>${item.title}</h6>
                <div class="price">${formatMoney(item.price)}</div>
            </div>
            <div class="cart-item-quantity">
                <button onclick="updateQuantity(${item.product_id}, ${item.quantity - 1})">-</button>
                <input type="number" value="${item.quantity}" min="1" 
                       onchange="updateQuantity(${item.product_id}, this.value)">
                <button onclick="updateQuantity(${item.product_id}, ${item.quantity + 1})">+</button>
            </div>
        `;
        cartItems.appendChild(itemDiv);
    });
}

// Cập nhật số lượng
function updateQuantity(productId, quantity) {
    fetch('controller/cLivestream.php?action=update_cart_quantity', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `livestream_id=${livestreamId}&product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cart = data.cart;
            updateCartDisplay(cart);
        }
    });
}

// Thanh toán
function checkout() {
    if (cart.item_count === 0) {
        alert('Giỏ hàng trống');
        return;
    }
    
    if (confirm(`Xác nhận thanh toán ${formatMoney(cart.total)}?`)) {
        fetch('controller/cLivestream.php?action=checkout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `livestream_id=${livestreamId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.payment_url;
            } else {
                showToast(data.message, 'error');
            }
        });
    }
}

// Liên hệ người bán
function contactSeller(phone) {
    if (phone) {
        window.open(`tel:${phone}`, '_self');
    } else {
        alert('Số điện thoại không có sẵn');
    }
}

// Cập nhật số lượng người xem
function updateViewerCount() {
    fetch(`controller/cLivestream.php?action=get_stats&livestream_id=${livestreamId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateViewerCountDisplay(data.stats.total_viewers);
        }
    });
}

function updateViewerCountDisplay(count) {
    document.getElementById('viewer-count').textContent = count;
}

// Tải tin nhắn chat
function loadChatMessages() {
    // Load chat messages from database
    // Implementation depends on your chat system
}

// Hiển thị thông báo
function showToast(message, type = 'info') {
    // Sử dụng Toastify hoặc tạo toast đơn giản
    if (typeof Toastify !== 'undefined') {
        Toastify({
            text: message,
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: type === 'success' ? '#4CAF50' : type === 'error' ? '#f44336' : '#2196F3'
        }).showToast();
    } else {
        alert(message);
    }
}

// Format tiền
function formatMoney(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

// Xem giỏ hàng chi tiết
function viewCart() {
    // Mở modal hoặc trang giỏ hàng chi tiết
    alert('Tính năng xem giỏ hàng chi tiết');
}

// Enter để gửi tin nhắn
document.getElementById('chat-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        sendMessage();
    }
});
</script>

<?php include_once("view/footer.php"); ?>




