<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$livestream_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($livestream_id <= 0) { echo "<p style='padding:20px'>Thiếu tham số livestream id.</p>"; exit; }

// Include header
include_once __DIR__ . "/header.php";

// Lấy thông tin livestream và sản phẩm
include_once __DIR__ . "/../model/mLivestream.php";
$mLivestream = new mLivestream();
$livestream = $mLivestream->getLivestreamById($livestream_id);

// Kiểm tra livestream có tồn tại không
if (!$livestream) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border-radius: 5px;'>";
    echo "<h3>Lỗi: Không tìm thấy livestream</h3>";
    echo "<p>Livestream không tồn tại hoặc đã bị xóa.</p>";
    echo "<a href='index.php?my-livestreams' class='btn btn-primary'>Quay lại danh sách livestream</a>";
    echo "</div>";
    include_once __DIR__ . "/footer.php";
    exit;
}

// Kiểm tra quyền truy cập
if ($livestream['user_id'] != $_SESSION['user_id']) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border-radius: 5px;'>";
    echo "<h3>Lỗi: Không có quyền truy cập</h3>";
    echo "<p>Bạn không có quyền truy cập livestream này.</p>";
    echo "<a href='index.php?my-livestreams' class='btn btn-primary'>Quay lại danh sách livestream</a>";
    echo "</div>";
    include_once __DIR__ . "/footer.php";
    exit;
}

$products = $mLivestream->getLivestreamProducts($livestream_id);
$pinned_product = $mLivestream->getPinnedProduct($livestream_id);

// Override title for broadcast page
echo "<script>document.title = 'Broadcast Livestream - Chợ Việt';</script>";
?>
    <style>
        .chat-message {
            margin-bottom: 8px;
            padding: 4px 0;
            border-bottom: 1px solid #333;
        }
        .chat-message:last-child {
            border-bottom: none;
        }
        .chat-message .username {
            font-weight: bold;
            color: #ffd700;
            margin-right: 8px;
        }
        #preview {
            transform: scaleX(-1);
        }
        .livestream-container {
            background: #0e0e0e;
            color: #eee;
            font-family: system-ui, Segoe UI, Arial, sans-serif;
            min-height: 100vh;
        }
        .panel{background:#151515;border:1px solid #242424;border-radius:10px;padding:16px;margin-bottom:16px}
        .panel h5{margin:0 0 10px 0; color: #fff;}
        .btn-live{display:block;width:100%;padding:10px 14px;border-radius:8px;border:0;margin-bottom:10px}
        .btn-start{background:#ff2f2f;color:#fff}
        .btn-stop{background:#666;color:#fff;display:none}
        .btn-preview{background:#ffc107;color:#000}
        .stat{background:#1d1d1d;border-radius:10px;padding:12px;text-align:center}
        .stat .num{font-size:22px;font-weight:bold; color: #ffd700;}
        #preview{width:100%;height:560px;background:#000;border-radius:10px;object-fit:cover}
        .chat{height:560px;display:flex;flex-direction:column}
        .chat .box{flex:1;overflow:auto;background:#0e0e0e;border-radius:8px;padding:8px;margin-bottom:8px}
        .chat .row-send{display:flex;gap:8px}
        .chat input{flex:1;background:#222;border:1px solid #333;color:#fff;border-radius:8px;padding:8px}
        .product-item{background:#1d1d1d;border-radius:8px;padding:8px;margin-bottom:8px;border:1px solid #333}
        .product-item.pinned{border-color:#ffd700;background:#2a2a1a}
        .product-item img{width:40px;height:40px;object-fit:cover;border-radius:4px;margin-right:8px}
        .product-info{flex:1}
        .product-title{font-size:12px;color:#fff;margin:0;line-height:1.2}
        .product-price{font-size:11px;color:#ffd700;font-weight:bold}
        .product-actions{display:flex;gap:4px;margin-top:4px;justify-content:flex-end}
        .btn-pin{background:#ffd700;color:#000;border:0;border-radius:4px;padding:4px 8px;font-size:10px;display:flex;align-items:center;justify-content:center;gap:4px}
        .btn-unpin{background:#dc3545;color:#fff;border:0;border-radius:4px;padding:4px 8px;font-size:10px;display:flex;align-items:center;justify-content:center;gap:4px}
        .product-number{position:absolute;top:5px;left:5px;background:#ffd700;color:#000;width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:10px;z-index:1}
        .product-item.pinned{border:2px solid #ffd700;background:rgba(255,215,0,0.2);box-shadow:0 0 10px rgba(255,215,0,0.3)}
        .product-item.pinned .product-number{background:#ff6b00;color:#fff;font-weight:bold}
        .product-item.pinned .product-title{color:#ffd700;font-weight:bold}
        .product-item.pinned .product-price{color:#ff6b00;font-weight:bold;font-size:12px}
        .product-list-item{display:flex;align-items:center;padding:10px;border:1px solid #ddd;border-radius:8px;margin-bottom:10px;cursor:pointer;transition:all 0.2s}
        .product-list-item:hover{background:#f8f9fa;border-color:#ffd700}
        .product-list-item.selected{background:#fff9e6;border-color:#ffd700}
        .product-list-item img{width:60px;height:60px;object-fit:cover;border-radius:6px;margin-right:15px}
        .product-list-item .product-info{flex:1}
        .product-list-item .product-info h6{margin:0 0 5px 0;font-size:14px}
        .product-list-item .product-info p{margin:0;color:#666;font-size:12px}
    </style>

    <div class="livestream-container">
<div class="container-fluid py-3">
  <div class="row g-3">
    <!-- Left: Control + Products + Stats -->
    <div class="col-lg-3 col-md-4">
      <div class="panel">
        <h5>Điều khiển</h5>
        <button id="btnStart" class="btn-live btn-start"><i class="fas fa-video"></i> Bắt đầu Live</button>
        <button id="btnStop" class="btn-live btn-stop"><i class="fas fa-stop"></i> Dừng Live</button>
        <div id="status" class="mt-2 text-success small">Sẵn sàng.</div>
      </div>
      <div class="panel">
        <h5>Sản phẩm đang bán</h5>
        <button class="btn btn-sm btn-warning w-100 mb-2" onclick="addProduct()"><i class="fas fa-plus"></i> Thêm sản phẩm</button>
        
        <?php if (empty($products)): ?>
          <div class="small text-secondary">Chưa có sản phẩm nào</div>
        <?php else: ?>
          <div id="products-list" style="max-height: 300px; overflow-y: auto;">
            <?php 
            // Sắp xếp sản phẩm: ghim lên đầu, sau đó theo thứ tự
            $pinned_products = array_filter($products, function($p) { return $p['is_pinned']; });
            $unpinned_products = array_filter($products, function($p) { return !$p['is_pinned']; });
            $sorted_products = array_merge($pinned_products, $unpinned_products);
            $index = 1;
            ?>
            <?php foreach ($sorted_products as $product): ?>
              <?php 
              $productImage = $product['anh_dau'] ?? 'default-product.jpg';
              if (!file_exists('img/' . $productImage)) {
                  $productImage = 'default-product.jpg';
              }
              ?>
              <div class="product-item d-flex align-items-center <?= $product['is_pinned'] ? 'pinned' : '' ?>" 
                   data-product-id="<?= $product['product_id'] ?>" style="position: relative;">
                <div class="product-number"><?= $index++ ?></div>
                <img src="img/<?= htmlspecialchars($productImage) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
                <div class="product-info">
                  <div class="product-title"><?= htmlspecialchars($product['title']) ?></div>
                  <div class="product-price"><?= number_format($product['special_price'] ?: $product['price']) ?> đ</div>
                  <div class="product-actions">
                    <?php if ($product['is_pinned']): ?>
                      <button class="btn-unpin" onclick="unpinProduct(<?= $product['product_id'] ?>)">
                        <i class="fas fa-thumbtack"></i> Bỏ ghim
                      </button>
                    <?php else: ?>
                      <button class="btn-pin" onclick="pinProduct(<?= $product['product_id'] ?>)">
                        <i class="fas fa-thumbtack"></i> Ghim
                      </button>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      <div class="panel">
        <h5>Thống kê</h5>
        <div class="row g-2">
          <div class="col-6"><div class="stat"><div class="num" id="live-viewers">0</div><div>Đang xem</div></div></div>
          <div class="col-6"><div class="stat"><div class="num" id="live-likes">0</div><div>Lượt thích</div></div></div>
          <div class="col-6"><div class="stat"><div class="num" id="live-orders">0</div><div>Đơn hàng</div></div></div>
          <div class="col-6"><div class="stat"><div class="num" id="live-revenue">0</div><div>Doanh thu</div></div></div>
        </div>
      </div>
    </div>

    <!-- Center: Video -->
    <div class="col-lg-6 col-md-8">
      <div class="panel">
        <h5><?= htmlspecialchars($livestream['title']) ?></h5>
        <div class="text-secondary small mb-2"><?= htmlspecialchars($livestream['description']) ?></div>
        <video id="preview" autoplay muted playsinline></video>
      </div>
    </div>

    <!-- Right: Chat -->
    <div class="col-lg-3">
      <div class="panel chat">
        <h5>Chat trực tiếp</h5>
        <div id="chatBox" class="box">
        <div class="chat-message">
                            <span class="username">Hệ thống:</span>
                            <span>Chào mừng bạn đến với livestream!</span>
                        </div>
        </div>
        <div class="row-send">
          <input id="chatInput" placeholder="Nhập tin nhắn..." />
          <button class="btn btn-warning" id="btnSend"><i class="fas fa-paper-plane"></i></button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const LIVESTREAM_ID = <?= $livestream_id ?>;
const USER_ID = <?= isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0 ?>;
const USERNAME = '<?= isset($_SESSION['username']) ? addslashes($_SESSION['username']) : 'Streamer' ?>';
const log = (m)=>{ document.getElementById('status').textContent = m; console.log('Streamer:', m); };

let localStream=null, broadcastWs=null, pc=null;

function initWs(){
  broadcastWs = new WebSocket('ws://localhost:3000');
  broadcastWs.onopen = ()=>{
    log('WebSocket streamer đã kết nối');
    broadcastWs.send(JSON.stringify({ type:'join_livestream', livestream_id:LIVESTREAM_ID, user_id: USER_ID||('broadcaster_'+Date.now()), user_type:'streamer' }));
  };
  broadcastWs.onmessage = (ev)=>{
    const msg = JSON.parse(ev.data||'{}');
    console.log('Streamer nhận message:', msg);
    if (msg.type==='webrtc_answer' && pc && msg.sdp){ 
      console.log('Streamer: Nhận answer từ viewer');
      pc.setRemoteDescription(new RTCSessionDescription(msg.sdp)).catch(()=>{}); 
    }
    else if (msg.type==='webrtc_ice' && pc && msg.candidate){ 
      console.log('Streamer: Nhận ICE candidate từ viewer');
      pc.addIceCandidate(new RTCIceCandidate(msg.candidate)).catch(()=>{}); 
    }
    else if (msg.type==='request_offer'){ 
      console.log('Viewer yêu cầu offer, streamer đang gửi...');
      if (pc && pc.localDescription){
        console.log('Streamer: Gửi lại offer hiện có cho viewer');
        broadcastWs.send(JSON.stringify({type:'webrtc_offer', livestream_id:LIVESTREAM_ID, sdp: pc.localDescription}));
      } else {
        console.log('Streamer: Chưa có localDescription, tạo offer mới...');
        // Tạo offer mới nếu chưa có
        if (pc && localStream) {
          pc.createOffer().then(offer => {
            pc.setLocalDescription(offer);
            broadcastWs.send(JSON.stringify({type:'webrtc_offer', livestream_id:LIVESTREAM_ID, sdp: offer}));
            console.log('Streamer: Đã tạo offer mới và gửi cho viewer');
          }).catch(err => {
            console.error('Error creating offer for viewer:', err);
          });
        } else {
          console.log('Streamer: Không thể tạo offer vì thiếu pc hoặc localStream');
        }
      }
    }
    else if (msg.type==='livestream_chat'){ 
      const displayName = msg.username || 'Khách';
      const isStreamer = msg.user_id == USER_ID;
      const nameWithIcon = isStreamer ? displayName + ' <i class="fas fa-home text-warning"></i>' : displayName;
      appendChat(nameWithIcon, msg.message||''); 
    }
    else if (msg.type==='viewers_count_update'){ 
      // Cập nhật số người xem real-time từ WebSocket (ưu tiên cao nhất)
      updateViewersCount(msg.viewers_count || 0);
    }
    else if (msg.type==='viewer_joined'){ 
      // Có người mới join, cập nhật số người xem
      updateViewersCount(msg.viewers_count || 0);
    }
    else if (msg.type==='viewer_left'){ 
      // Có người rời, cập nhật số người xem
      updateViewersCount(msg.viewers_count || 0);
    }
    else if (msg.type==='livestream_joined'){ 
      // Streamer join thành công, nhận số người xem ban đầu
      if (msg.viewers_count !== undefined) {
        updateViewersCount(msg.viewers_count);
      }
    }
    else if (msg.type==='order_created'){ 
      // Cập nhật thống kê khi có đơn hàng mới
      console.log('Streamer: Nhận order_created:', msg);
      refreshStats();
    }
    else if (msg.type==='livestream_stats_update'){ 
      // Cập nhật thống kê real-time từ WebSocket
      console.log('Streamer: Nhận cập nhật thống kê:', msg.stats);
      if (msg.stats) {
        document.getElementById('live-orders').textContent = msg.stats.order_count || 0;
        document.getElementById('live-revenue').textContent = formatRevenue(msg.stats.total_revenue || 0);
        document.getElementById('live-likes').textContent = msg.stats.like_count || 0;
        // Số người xem vẫn ưu tiên từ viewers_count_update
        if (msg.stats.current_viewers && currentViewersCount === 0) {
          updateViewersCount(msg.stats.current_viewers);
        }
      }
    }
    else if (msg.type==='livestream_like_count'){ 
      // Cập nhật lượt thích real-time
      updateLikesCount(msg.count || 0);
      console.log('Streamer: Số lượt thích đã cập nhật =', msg.count || 0);
    }
  };
}


async function startLive(){
  // Tự động kết nối camera/mic nếu chưa có
  if(!localStream){
    log('Đang kết nối Camera/Mic...');
    try{
      localStream = await navigator.mediaDevices.getUserMedia({ 
        video:{width:{ideal:1280},height:{ideal:720}}, 
        audio:{echoCancellation:true, noiseSuppression:true, autoGainControl:true} 
      });
      document.getElementById('preview').srcObject = localStream;
      log('Đã kết nối Camera/Mic ✔');
    }catch(e){ 
      log('Không thể truy cập camera/mic: '+e.message); 
      return; 
    }
  }
  
  if(!broadcastWs || broadcastWs.readyState!==1){ log('WebSocket chưa sẵn sàng.'); return; }

  console.log('Streamer: Bắt đầu livestream...');
  fetch('api/livestream-api.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'action=update_status&livestream_id='+LIVESTREAM_ID+'&status=dang_live' }).catch(()=>{});

  pc = new RTCPeerConnection({ iceServers:[{urls:'stun:stun.l.google.com:19302'},{urls:'stun:stun1.l.google.com:19302'}] });
  console.log('Created RTCPeerConnection');
  
  localStream.getTracks().forEach(t=> {
    console.log('Adding track:', t.kind, {
      id: t.id,
      enabled: t.enabled,
      muted: t.muted,
      readyState: t.readyState
    });
    pc.addTrack(t, localStream);
  });
  
  pc.onicecandidate = ev=>{ 
    if(ev.candidate){ 
      console.log('Sending ICE candidate to viewers');
      broadcastWs.send(JSON.stringify({type:'webrtc_ice', livestream_id:LIVESTREAM_ID, candidate:ev.candidate})); 
    } 
  };
  
  pc.onconnectionstatechange = () => {
    console.log('Streamer connection state:', pc.connectionState);
    if (pc.connectionState === 'disconnected' || pc.connectionState === 'failed') {
      console.log('Streamer connection lost, attempting to reconnect...');
      // Có thể restart livestream hoặc thông báo cho viewers
    }
  };
  
  pc.oniceconnectionstatechange = () => {
    console.log('Streamer ICE connection state:', pc.iceConnectionState);
    if (pc.iceConnectionState === 'disconnected' || pc.iceConnectionState === 'failed') {
      console.log('Streamer ICE connection lost');
    }
  };
  
  const offer = await pc.createOffer({offerToReceiveAudio:true,offerToReceiveVideo:true});
  console.log('📤 Created offer:', offer.type);
  await pc.setLocalDescription(offer);
  console.log('📤 Set local description');
  broadcastWs.send(JSON.stringify({type:'webrtc_offer', livestream_id:LIVESTREAM_ID, sdp: offer}));
  console.log('📤 Sent offer to WebSocket');
  broadcastWs.send(JSON.stringify({type:'livestream_status_update', livestream_id:LIVESTREAM_ID, status:'dang_live'}));
  document.getElementById('btnStart').style.display='none';
  document.getElementById('btnStop').style.display='block';
  log('Đang phát live...');
}

function stopLive(){
  try{ if(pc){ pc.getSenders().forEach(s=> s.track && s.track.stop()); } if(localStream){ localStream.getTracks().forEach(t=>t.stop()); } }catch{}
  fetch('api/livestream-api.php',{ method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'action=update_status&livestream_id='+LIVESTREAM_ID+'&status=da_ket_thuc' }).catch(()=>{});
  document.getElementById('btnStart').style.display='block';
  document.getElementById('btnStop').style.display='none';
  log('Đã dừng live.');
}

function appendChat(u,m){ const box=document.getElementById('chatBox'); const d=document.createElement('div'); d.className='chat-message'; d.innerHTML='<span class="username">'+u+':</span> <span>'+m+'</span>'; box.appendChild(d); box.scrollTop=box.scrollHeight; }
function sendChat(){ const i=document.getElementById('chatInput'); const m=i.value.trim(); if(!m) return; if(broadcastWs&&broadcastWs.readyState===1){ broadcastWs.send(JSON.stringify({type:'livestream_chat', livestream_id:LIVESTREAM_ID, message:m, username:USERNAME, user_id:USER_ID})); i.value=''; } }

// Product management functions
function pinProduct(productId) {
    fetch('api/livestream-api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=pin_product&livestream_id=${LIVESTREAM_ID}&product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI without reload
            updateProductDisplay();
            showToast('Đã ghim sản phẩm!', 'success');
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra');
    });
}

function unpinProduct(productId) {
    fetch('api/livestream-api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=unpin_product&livestream_id=${LIVESTREAM_ID}&product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI without reload
            updateProductDisplay();
            showToast('Đã bỏ ghim sản phẩm!', 'success');
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra');
    });
}

const addProductModalState = {
    backdropEl: null,
    isManualOpen: false,
    scrollBarCompensation: ''
};

function canUseBootstrapModal() {
    return typeof window.jQuery !== 'undefined'
        && typeof window.jQuery.fn !== 'undefined'
        && typeof window.jQuery.fn.modal === 'function';
}

function showAddProductModal() {
    const modal = document.getElementById('addProductModal');
    if (!modal) {
        return;
    }

    // Reset danh sách sản phẩm đã chọn khi mở modal
    selectedProducts = {};
    updateSelectedProductsList();
    updateSelectedCount();
    updateAddButton();
    
    loadProducts();

    if (canUseBootstrapModal()) {
        window.jQuery('#addProductModal').modal('show');
        return;
    }

    modal.classList.add('show');
    modal.style.display = 'block';
    modal.setAttribute('aria-hidden', 'false');
    modal.setAttribute('aria-modal', 'true');
    modal.dataset.manualOpen = 'true';
    addProductModalState.isManualOpen = true;
    if (typeof window.innerWidth === 'number') {
        const scrollBarWidth = window.innerWidth - document.documentElement.clientWidth;
        if (scrollBarWidth > 0) {
            addProductModalState.scrollBarCompensation = document.body.style.paddingRight;
            document.body.style.paddingRight = scrollBarWidth + 'px';
        }
    }
    document.body.classList.add('modal-open');

    if (!addProductModalState.backdropEl) {
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.addEventListener('click', hideAddProductModal);
        addProductModalState.backdropEl = backdrop;
    }

    document.body.appendChild(addProductModalState.backdropEl);
}

function hideAddProductModal() {
    const modal = document.getElementById('addProductModal');
    if (!modal) {
        return;
    }

    if (canUseBootstrapModal()) {
        window.jQuery('#addProductModal').modal('hide');
        return;
    }

    if (!addProductModalState.isManualOpen) {
        return;
    }

    modal.classList.remove('show');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    modal.removeAttribute('aria-modal');
    delete modal.dataset.manualOpen;
    addProductModalState.isManualOpen = false;
    document.body.classList.remove('modal-open');
    if (addProductModalState.scrollBarCompensation !== '') {
        document.body.style.paddingRight = addProductModalState.scrollBarCompensation;
        addProductModalState.scrollBarCompensation = '';
    } else {
        document.body.style.paddingRight = '';
    }

    if (addProductModalState.backdropEl && addProductModalState.backdropEl.parentNode) {
        addProductModalState.backdropEl.parentNode.removeChild(addProductModalState.backdropEl);
    }
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && addProductModalState.isManualOpen) {
        hideAddProductModal();
    }
});

function addProduct() {
    showAddProductModal();
}

// Update product display without reload
function updateProductDisplay() {
    fetch(`api/livestream-api.php?action=get_products&livestream_id=${LIVESTREAM_ID}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const container = document.getElementById('products-list');
            container.innerHTML = '';
            
            // Sort products: pinned first
            const pinnedProducts = data.products.filter(p => p.is_pinned);
            const unpinnedProducts = data.products.filter(p => !p.is_pinned);
            const sortedProducts = [...pinnedProducts, ...unpinnedProducts];
            
            let index = 1;
            sortedProducts.forEach(product => {
                const productItem = document.createElement('div');
                productItem.className = `product-item d-flex align-items-center ${product.is_pinned ? 'pinned' : ''}`;
                productItem.style.position = 'relative';
                productItem.setAttribute('data-product-id', product.product_id);
                
                const productImage = product.anh_dau || 'default-product.jpg';
                
                // Xác định giá hiển thị: nếu special_price là null hoặc rỗng thì dùng giá gốc
                const displayPrice = (product.special_price && product.special_price !== null && product.special_price !== '') 
                    ? product.special_price 
                    : product.price;
                
                productItem.innerHTML = `
                    <div class="product-number">${index++}</div>
                    <img src="img/${productImage}" alt="${product.title}">
                    <div class="product-info">
                        <div class="product-title">${product.title}</div>
                        <div class="product-price">${new Intl.NumberFormat('vi-VN').format(displayPrice)} đ</div>
                        <div class="product-actions">
                            ${product.is_pinned ? 
                                `<button class="btn-unpin" onclick="unpinProduct(${product.product_id})">
                                    <i class="fas fa-thumbtack"></i> Bỏ ghim
                                </button>` :
                                `<button class="btn-pin" onclick="pinProduct(${product.product_id})">
                                    <i class="fas fa-thumbtack"></i> Ghim
                                </button>`
                            }
                        </div>
                    </div>
                `;
                
                container.appendChild(productItem);
            });
        }
    })
    .catch(error => {
        console.error('Error updating products:', error);
    });
}

// Show toast notification
function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add styles
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#28a745' : '#17a2b8'};
        color: white;
        padding: 12px 20px;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
    
    // Add animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .toast-content {
            display: flex;
            align-items: center;
            gap: 8px;
        }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(toast);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.style.animation = 'slideIn 0.3s ease reverse';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 3000);
}

document.getElementById('btnStart').onclick  = startLive;
document.getElementById('btnStop').onclick   = stopLive;
document.getElementById('btnSend').onclick   = sendChat;

// Xử lý khi user thoát trang
window.addEventListener('beforeunload', function(e) {
    // Nếu đang live thì tự động dừng
    if (localStream && broadcastWs && broadcastWs.readyState === 1) {
        // Gửi request để dừng live (không chờ response)
        navigator.sendBeacon('api/livestream-api.php', 
            'action=update_status&livestream_id=' + LIVESTREAM_ID + '&status=da_ket_thuc');
    }
});

// Hàm cập nhật số người xem (ưu tiên từ WebSocket real-time)
let currentViewersCount = 0; // Lưu số người xem từ WebSocket
function updateViewersCount(count) {
  currentViewersCount = count;
  document.getElementById('live-viewers').textContent = count;
  console.log('Streamer: Viewers count updated to', count);
}

// Hàm cập nhật lượt thích
function updateLikesCount(count) {
  document.getElementById('live-likes').textContent = count;
}

// Hàm format số tiền linh hoạt
function formatRevenue(amount) {
  if (!amount || amount === 0) return '0';
  
  const numAmount = parseFloat(amount);
  
  // Nếu >= 1 triệu, hiển thị theo triệu
  if (numAmount >= 1000000) {
    const trieu = (numAmount / 1000000).toFixed(3); // Giữ 3 chữ số thập phân
    // Loại bỏ số 0 thừa ở cuối
    const cleanTrieu = parseFloat(trieu).toString();
    return cleanTrieu.replace('.', ',') + 'tr';
  }
  
  // Nếu >= 100k, hiển thị theo k
  if (numAmount >= 100000) {
    const k = (numAmount / 1000).toFixed(0);
    return k + 'k';
  }
  
  // Nhỏ hơn 100k, hiển thị số đầy đủ với dấu phẩy ngăn cách hàng nghìn
  return new Intl.NumberFormat('vi-VN').format(numAmount);
}

// Hàm refresh thống kê từ API (chỉ cập nhật đơn hàng, doanh thu, lượt thích)
// Số người xem được cập nhật real-time qua WebSocket, không ghi đè
function refreshStats() {
  fetch(`api/livestream-api.php?action=get_realtime_stats&livestream_id=${LIVESTREAM_ID}`)
    .then(response => response.json())
    .then(data => {
      if (data.success && data.stats) {
        const stats = data.stats;
        // Chỉ cập nhật số người xem nếu chưa có từ WebSocket (fallback)
        if (currentViewersCount === 0) {
          document.getElementById('live-viewers').textContent = stats.current_viewers || 0;
          currentViewersCount = stats.current_viewers || 0;
        }
        // Cập nhật các thống kê khác
        document.getElementById('live-orders').textContent = stats.order_count || 0;
        document.getElementById('live-revenue').textContent = formatRevenue(stats.total_revenue || 0);
        document.getElementById('live-likes').textContent = stats.like_count || 0;
      }
    })
    .catch(error => {
      console.error('Error refreshing stats:', error);
    });
}

// Load thống kê ban đầu
refreshStats();

// Refresh thống kê mỗi 5 giây
setInterval(refreshStats, 5000);

initWs();
</script>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm sản phẩm vào livestream</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Đóng" onclick="hideAddProductModal()">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6>Danh sách sản phẩm của bạn</h6>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllProducts()">Chọn tất cả</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllProducts()">Bỏ chọn</button>
                            </div>
                        </div>
                        <div class="product-list" id="available-products" style="max-height: 400px; overflow-y: auto;">
                            <!-- Products will be loaded here -->
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Sản phẩm đã chọn (<span id="selected-count">0</span>)</h6>
                        <div id="selected-products-list" style="max-height: 400px; overflow-y: auto;">
                            <div class="text-center text-muted py-3">Chưa chọn sản phẩm nào</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="hideAddProductModal()">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="addProductToLivestream()" id="add-product-btn" disabled>Thêm/Cập nhật sản phẩm</button>
            </div>
        </div>
    </div>
</div>

<script>
// Lưu danh sách sản phẩm đã chọn
let selectedProducts = {}; // {productId: {product, specialPrice, stockQuantity}}

// Load available products (including already added products)
function loadProducts() {
    fetch(`api/livestream-api.php?action=get_available_products&livestream_id=${LIVESTREAM_ID}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const container = document.getElementById('available-products');
            container.innerHTML = '';
            
            if (data.products.length === 0) {
                container.innerHTML = '<div class="text-center text-muted">Chưa có sản phẩm nào</div>';
                return;
            }
            
            // Lưu tất cả sản phẩm để có thể truy cập sau
            allProductsData = data.products;
            
            data.products.forEach(product => {
                const productItem = document.createElement('div');
                productItem.className = 'product-list-item';
                productItem.setAttribute('data-product-id', product.id);
                
                // Kiểm tra xem sản phẩm đã được chọn chưa
                const isSelected = selectedProducts[product.id] !== undefined;
                
                // Hiển thị badge nếu sản phẩm đã có trong livestream
                const badgeHtml = product.is_in_livestream 
                    ? '<span class="badge badge-warning" style="position: absolute; top: 5px; right: 5px; background: #ffd700; color: #000; padding: 2px 6px; border-radius: 3px; font-size: 10px;">Đã có</span>'
                    : '';
                
                // Hiển thị giá: ưu tiên giá đặc biệt trong livestream, sau đó giá gốc
                const displayPrice = product.is_in_livestream && product.livestream_special_price 
                    ? product.livestream_special_price 
                    : product.price;
                
                productItem.innerHTML = `
                    <div style="display: flex; align-items: center; width: 100%;">
                        <input type="checkbox" class="product-checkbox" data-product-id="${product.id}" 
                               ${isSelected ? 'checked' : ''} 
                               onchange="toggleProductSelection(${product.id}, this.checked)" 
                               onclick="event.stopPropagation();" 
                               style="margin-right: 10px; width: 18px; height: 18px; cursor: pointer;">
                        <div style="position: relative; flex: 1;">
                            ${badgeHtml}
                            <img src="img/${product.anh_dau || 'default-product.jpg'}" alt="${product.title}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px; margin-right: 15px;">
                        </div>
                        <div class="product-info" style="flex: 1;">
                            <h6>${product.title}</h6>
                            <p>${new Intl.NumberFormat('vi-VN').format(displayPrice)} đ</p>
                            ${product.is_in_livestream && product.livestream_stock_quantity !== null 
                                ? `<small class="text-muted">Còn lại: ${product.livestream_stock_quantity}</small>` 
                                : ''}
                        </div>
                    </div>
                `;
                
                if (isSelected) {
                    productItem.classList.add('selected');
                }
                
                container.appendChild(productItem);
            });
        }
    })
    .catch(error => {
        console.error('Error loading products:', error);
    });
}

// Lưu tất cả sản phẩm để có thể truy cập khi toggle
let allProductsData = [];

// Toggle chọn/bỏ chọn sản phẩm
function toggleProductSelection(productId, isChecked) {
    const product = allProductsData.find(p => p.id == productId);
    if (!product) return;
    
    if (isChecked) {
        // Thêm sản phẩm vào danh sách đã chọn
        selectedProducts[productId] = {
            product: product,
            specialPrice: product.is_in_livestream ? (product.livestream_special_price || '') : '',
            stockQuantity: product.is_in_livestream ? (product.livestream_stock_quantity || '') : ''
        };
    } else {
        // Xóa sản phẩm khỏi danh sách đã chọn
        delete selectedProducts[productId];
    }
    
    // Cập nhật UI
    updateSelectedProductsList();
    updateSelectedCount();
    updateAddButton();
}

// Hiển thị danh sách sản phẩm đã chọn
function updateSelectedProductsList() {
    const container = document.getElementById('selected-products-list');
    const selectedIds = Object.keys(selectedProducts);
    
    if (selectedIds.length === 0) {
        container.innerHTML = '<div class="text-center text-muted py-3">Chưa chọn sản phẩm nào</div>';
        return;
    }
    
    container.innerHTML = '';
    
    selectedIds.forEach(productId => {
        const selectedData = selectedProducts[productId];
        const product = selectedData.product;
        const isInLivestream = product.is_in_livestream;
        
        const itemDiv = document.createElement('div');
        itemDiv.className = 'selected-product-item';
        itemDiv.style.cssText = 'border: 1px solid #ddd; border-radius: 8px; padding: 12px; margin-bottom: 10px; background: #f9f9f9;';
        
        itemDiv.innerHTML = `
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="d-flex align-items-center" style="flex: 1;">
                    <img src="img/${product.anh_dau || 'default-product.jpg'}" alt="${product.title}" 
                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px; margin-right: 10px;">
                    <div style="flex: 1;">
                        <h6 style="margin: 0; font-size: 14px;">${product.title}</h6>
                        <small class="text-muted">Giá gốc: ${new Intl.NumberFormat('vi-VN').format(product.price)} đ</small>
                        ${isInLivestream ? '<br><span class="badge badge-warning" style="font-size: 10px;">Đã có trong livestream</span>' : ''}
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="toggleProductSelection(${productId}, false)" style="margin-left: 10px;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="row">
                <div class="col-6">
                    <label style="font-size: 12px; color: #000; margin-bottom: 4px;">Giá đặc biệt</label>
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control form-control-sm product-special-price" 
                               data-product-id="${productId}" 
                               value="${selectedData.specialPrice}" 
                               placeholder="Để trống = giá gốc"
                               onchange="updateSelectedProductPrice(${productId}, this.value)">
                        ${isInLivestream && selectedData.specialPrice ? 
                            `<div class="input-group-append">
                                <button type="button" class="btn btn-sm" 
                                        style="background-color: #e0e0e0; color: #000; border: 1px solid #000; padding: 4px 8px;"
                                        onmouseover="this.style.backgroundColor='#b0b0b0'" 
                                        onmouseout="this.style.backgroundColor='#e0e0e0'"
                                        onclick="resetProductPrice(${productId})">
                                    <i class="fas fa-undo" style="font-size: 10px;"></i>
                                </button>
                            </div>` : ''}
                    </div>
                </div>
                <div class="col-6">
                    <label style="font-size: 12px; color: #000; margin-bottom: 4px;">Số lượng</label>
                    <input type="number" class="form-control form-control-sm product-stock-quantity" 
                           data-product-id="${productId}" 
                           value="${selectedData.stockQuantity}" 
                           placeholder="Nhập số lượng"
                           onchange="updateSelectedProductQuantity(${productId}, this.value)">
                </div>
            </div>
        `;
        
        container.appendChild(itemDiv);
    });
}

// Cập nhật giá của sản phẩm đã chọn
function updateSelectedProductPrice(productId, price) {
    if (selectedProducts[productId]) {
        selectedProducts[productId].specialPrice = price;
    }
}

// Cập nhật số lượng của sản phẩm đã chọn
function updateSelectedProductQuantity(productId, quantity) {
    if (selectedProducts[productId]) {
        selectedProducts[productId].stockQuantity = quantity;
    }
}

// Reset giá về giá gốc cho một sản phẩm
function resetProductPrice(productId) {
    if (selectedProducts[productId]) {
        selectedProducts[productId].specialPrice = '';
        updateSelectedProductsList();
    }
}

// Cập nhật số lượng sản phẩm đã chọn
function updateSelectedCount() {
    const count = Object.keys(selectedProducts).length;
    document.getElementById('selected-count').textContent = count;
}

// Cập nhật trạng thái nút thêm/cập nhật
function updateAddButton() {
    const btn = document.getElementById('add-product-btn');
    const count = Object.keys(selectedProducts).length;
    
    if (count > 0) {
        btn.disabled = false;
        const hasExistingProducts = Object.values(selectedProducts).some(data => data.product.is_in_livestream);
        if (hasExistingProducts) {
            btn.textContent = `Cập nhật ${count} sản phẩm`;
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-warning');
        } else {
            btn.textContent = `Thêm ${count} sản phẩm`;
            btn.classList.remove('btn-warning');
            btn.classList.add('btn-primary');
        }
    } else {
        btn.disabled = true;
        btn.textContent = 'Thêm/Cập nhật sản phẩm';
    }
}

// Chọn tất cả sản phẩm
function selectAllProducts() {
    allProductsData.forEach(product => {
        if (!selectedProducts[product.id]) {
            toggleProductSelection(product.id, true);
        }
    });
    // Cập nhật checkbox
    document.querySelectorAll('.product-checkbox').forEach(cb => {
        cb.checked = true;
    });
}

// Bỏ chọn tất cả sản phẩm
function deselectAllProducts() {
    selectedProducts = {};
    updateSelectedProductsList();
    updateSelectedCount();
    updateAddButton();
    // Cập nhật checkbox
    document.querySelectorAll('.product-checkbox').forEach(cb => {
        cb.checked = false;
    });
    // Cập nhật class selected
    document.querySelectorAll('.product-list-item').forEach(item => {
        item.classList.remove('selected');
    });
}

// Thêm/Cập nhật nhiều sản phẩm cùng lúc
function addProductToLivestream() {
    const selectedIds = Object.keys(selectedProducts);
    
    if (selectedIds.length === 0) {
        alert('Vui lòng chọn ít nhất một sản phẩm');
        return;
    }
    
    // Validate tất cả sản phẩm đã chọn
    for (const productId of selectedIds) {
        const selectedData = selectedProducts[productId];
        const stockQuantity = selectedData.stockQuantity;
        const specialPrice = selectedData.specialPrice;
        
        // Validate số lượng >= 0
        if (stockQuantity !== '' && stockQuantity !== null && (isNaN(stockQuantity) || parseInt(stockQuantity) < 0)) {
            alert(`Sản phẩm "${selectedData.product.title}": Số lượng phải là số lớn hơn hoặc bằng 0`);
            return;
        }
        
        // Validate giá > 0 nếu có nhập
        if (specialPrice !== '' && specialPrice !== null && (isNaN(specialPrice) || parseFloat(specialPrice) <= 0)) {
            alert(`Sản phẩm "${selectedData.product.title}": Giá đặc biệt phải là số lớn hơn 0`);
            return;
        }
    }
    
    // Lấy CSRF token
    let csrfToken = '';
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (csrfMeta) {
        csrfToken = csrfMeta.getAttribute('content');
    }
    
    // Chuẩn bị dữ liệu để gửi
    const productsData = selectedIds.map(productId => {
        const selectedData = selectedProducts[productId];
        return {
            product_id: productId,
            special_price: selectedData.specialPrice || '',
            stock_quantity: selectedData.stockQuantity || ''
        };
    });
    
    // Gửi request batch update
    fetch('api/livestream-api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'batch_update_products',
            livestream_id: LIVESTREAM_ID,
            products: productsData,
            csrf_token: csrfToken
        })
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                console.error('Response is not JSON:', text);
                throw new Error('Server trả về dữ liệu không hợp lệ. Vui lòng thử lại.');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Xóa danh sách đã chọn
            selectedProducts = {};
            updateSelectedProductsList();
            updateSelectedCount();
            updateAddButton();
            
            // Reload danh sách sản phẩm
            loadProducts();
            
            // Cập nhật danh sách sản phẩm trong livestream
            setTimeout(() => {
                updateProductDisplay();
            }, 300);
            
            // Đóng modal
            hideAddProductModal();
            
            // Hiển thị thông báo thành công
            if (typeof showToast === 'function') {
                showToast(data.message || `Đã cập nhật ${selectedIds.length} sản phẩm thành công`, 'success');
            } else {
                alert(data.message || `Đã cập nhật ${selectedIds.length} sản phẩm thành công`);
            }
        } else {
            alert(data.message || 'Có lỗi xảy ra');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Lỗi kết nối: ' + error.message);
    });
}
</script>

    </div>

<?php include_once __DIR__ . "/footer.php"; ?>

