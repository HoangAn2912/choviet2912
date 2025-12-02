<?php

include_once("view/header.php");
require_once("controller/cChat.php");
require_once("controller/cUser.php");
require_once("model/mReview.php");

$mReview = new mReview();
$cChat = new cChat();
$cUser = new cUser();

$current_user_id = $_SESSION['user_id'];
$to_user_id = isset($_GET['to']) ? intval($_GET['to']) : 0;
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$hide_review_for_product = (isset($_GET['reviewed']) && intval($_GET['reviewed']) === 1 && $product_id > 0);
$conversations = $cChat->getConversationUsers($current_user_id);
$receiver = ($to_user_id) ? $cUser->getUserById($to_user_id) : null;
?>

<script>
const CURRENT_USER_ID = <?= $current_user_id ?>;
<?php if ($to_user_id): ?>
const TO_USER_ID = <?= $to_user_id ?>;
const ID_SAN_PHAM = <?= $product_id ?>;
<?php else: ?>
const ID_SAN_PHAM = 0;
<?php endif; ?>
</script>

<style>
  .chat-user.active {
    border: 2px solid #ffc107 !important;
    background-color: #fff8e1;
  }
  .chat-bubble {
    max-width: 60%;
    word-wrap: break-word;
  }
  .chat-bubble-received {
    background-color: #f1f3f5;
    color: #212529;
    padding: 10px 15px;
    border-radius: 10px;
    display: inline-block;
    max-width: 70%;
    word-break: break-word;
    line-height: 1.4;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
  }
  .chat-bubble-sent {
    display: inline-block;
    max-width: 70%;
    word-break: break-word;
    padding: 10px 15px;
    border-radius: 10px;
    line-height: 1.4;
  }
  .btn-suggestion {
    background-color: #fff;
    color: #000;
    border: 1px solid #ffc107;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 14px;
    margin: 4px;
    transition: 0.2s;
    white-space: nowrap;
  }
  .btn-suggestion:hover {
    background-color: #ffe082;
    color: #000;
    border-color: #ffc107;
  }
  .suggestions-container {
    position: relative;
    z-index: 1;
    margin-bottom: 10px;
    padding: 8px 0;
    background-color: #fff;
  }
  .chat-user {
    border: 1px solid #dee2e6;
    background-color: #ffffff;
    transition: background-color 0.2s;
  }
  .chat-user:hover {
    background-color: #f8f9fa;
  }
  .chat-user.active {
    border: 2px solid #ffc107 !important;
    background-color: #fff8e1;
  }
  .chat-wrapper {
    margin-top: 0 !important; 
    position: relative;
    z-index: 1;
  }
  
  /* Bỏ margin-bottom của navbar trên trang chat */
  .bg-dark.mb-30 {
    margin-bottom: 0 !important;
  }

  .chat-user .unread-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    background-color: #dc3545;
    border-radius: 50%;
    margin-left: 6px;
    vertical-align: middle;
  }

  /* Khoảng cách giữa tên và tin cuối trong danh mục */
  .chat-user .js-last {
    margin-top: 3px;
  }

  /* Avatar ở header khung chat (tránh méo ảnh) */
  .chat-header-avatar {
    width: 40px;
    height: 40px;
    object-fit: cover;
  }

  /* Nút gửi tin nhắn - Bo góc */
  #formChat button.btn {
    border-radius: 8px !important;
  }

  /* Input tin nhắn - Bo góc */
  #formChat input.form-control {
    border-radius: 8px !important;
  }

</style>

<div class="container-fluid chat-wrapper" style="max-width: 1200px;">
  <div class="row border rounded shadow-sm" style="height: 84vh; overflow: hidden;">
    <!-- Danh sách người dùng -->
    <div class="col-md-4 col-lg-3 bg-light p-3 overflow-auto" style="border-right: 1px solid #dee2e6;">
    <input type="text" class="form-control mb-3" placeholder="Tìm người dùng..." id="searchUserInput">
      <ul class="list-unstyled">
        <?php foreach ($conversations as $user): ?>
        <?php
          $avatarFile = !empty($user['avatar']) ? $user['avatar'] : 'default-avatar.jpg';
        ?>
        <li class="media p-2 mb-2 rounded chat-user <?= ($user['id'] == $to_user_id ? 'active' : '') ?>" 
            data-id="<?= $user['id'] ?>"
            style="cursor: pointer;" 
            onclick="openConversation(<?= $user['id'] ?>)">
          <img src="img/<?= htmlspecialchars($avatarFile) ?>" class="mr-3 avatar-square-lg" alt="Avatar">
          <div class="media-body">
            <h6 class="mb-0 font-weight-bold d-flex align-items-center justify-content-between">
              <span class="js-username" title="<?= htmlspecialchars($user['username']) ?>"><?= htmlspecialchars($user['username']) ?></span>
              <span>
                <small class="text-muted js-time"><?= htmlspecialchars($user['created_time'] ?? '') ?></small>
                <span class="unread-dot" style="display:none"></span>
              </span>
            </h6>
            <small class="js-last text-muted d-block"><?= htmlspecialchars($user['tin_cuoi'] ?? '') ?></small>
          </div>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <!-- Khung chat -->
    <div class="col-md-8 col-lg-9 d-flex flex-column p-4 bg-white">
      <?php if ($receiver): ?>
      <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
        <a href="index.php?thongtin=<?= (int)($receiver['id'] ?? 0) ?>"
           class="d-flex align-items-center"
           style="text-decoration:none; color: inherit;">
          <img src="img/<?= htmlspecialchars($receiver['avatar'] ?? 'default-avatar.jpg') ?>" class="mr-2 avatar-square" alt="Avatar">
          <strong><?= htmlspecialchars($receiver['username']) ?></strong>
        </a>
        <!-- Nút viết đánh giá: ẩn nếu vừa đánh giá xong cho đúng người bán + sản phẩm -->
        <button type="button"
                id="btnWriteReview"
                class="btn btn-warning text-white"
                onclick="openReviewModal()"
                style="<?= $hide_review_for_product ? 'display:none;' : 'display:inline-flex;' ?> font-weight:600; border-radius:20px;">
          <i class="fas fa-star mr-1"></i>Viết đánh giá
        </button>
      </div>

      <div id="chatMessages" class="flex-grow-1 overflow-auto mb-3" style="max-height: 60vh;"></div>

      <form class="d-flex align-items-center" id="formChat" onsubmit="event.preventDefault(); sendMessage(this.content.value); this.content.value='';">
        <input name="content" type="text" class="form-control" placeholder="Nhập tin nhắn..." required>
        <button class="btn btn-warning text-white ml-2"><i class="fa fa-paper-plane"></i></button>
      </form>
      <?php else: ?>
      <div class="text-center text-muted m-auto">
        <img src="img/chat.png" alt="Chọn người" style="max-width: 400px;">
        <p class="mt-3">Chọn người để bắt đầu trò chuyện</p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Modal đánh giá người bán -->
<div class="modal fade" id="modalDanhGia" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <form action="api/review-api.php?act=themDanhGia" method="post" id="reviewForm">
        <input type="hidden" name="reviewed_user_id" value="<?= $to_user_id ?>">
        <input type="hidden" name="product_id" id="review-product-id" value="0">
        <input type="hidden" name="order_type" value="">
        <input type="hidden" name="order_id" value="">

        <div class="modal-header">
          <h5 class="modal-title">Đánh giá người bán</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <p class="mb-2">
            <strong>Sản phẩm:</strong>
            <span id="review-product-title">Chưa xác định</span>
          </p>

          <label class="mt-2 d-block">Số sao</label>
          <div class="d-flex align-items-center mb-2">
            <input type="hidden" name="rating" id="review-rating" value="5">
            <div id="review-star-container">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <i class="fas fa-star review-star-icon" data-value="<?= $i ?>" style="font-size: 1.4rem; color: #ffc107; cursor: pointer; margin-right: 4px;"></i>
              <?php endfor; ?>
            </div>
            <span id="review-rating-text" class="ml-2 small text-muted">5 sao</span>
          </div>

          <label class="mt-2">Bình luận</label>
          <textarea name="comment" class="form-control" rows="3" required></textarea>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="js/chat.js"></script>
<script>
  // Gợi ý tin nhắn
  const suggestions = [
    "Sản phẩm này còn không?",
    "Giá có thương lượng không?",
    "Cho tôi xin địa chỉ được không?",
    "Còn bạn."
  ];

  // Chỉ tạo suggestions khi có form và receiver
  document.addEventListener("DOMContentLoaded", function() {
    const form = document.querySelector("form#formChat");
    if (!form) return;
    
    const input = form.querySelector("input[name='content']");
    if (!input) return;

    const suggestContainer = document.createElement("div");
    suggestContainer.className = "suggestions-container d-flex flex-wrap gap-2";

    suggestions.forEach(msg => {
      const btn = document.createElement("button");
      btn.type = "button";
      btn.className = "btn btn-sm btn-outline-secondary btn-suggestion";
      btn.textContent = msg;
      btn.onclick = () => {
        input.value = msg;
        input.focus();
      };
      suggestContainer.appendChild(btn);
    });

    // Chèn suggestions vào trước form, trong cùng container của form
    if (form && form.parentNode) {
      form.parentNode.insertBefore(suggestContainer, form);
    }
  });
document.getElementById("searchUserInput").addEventListener("input", function () {
  const keyword = this.value.toLowerCase().trim();
  const users = document.querySelectorAll(".chat-user");

  users.forEach(user => {
    const name = user.querySelector("h6").textContent.toLowerCase();
    if (name.includes(keyword)) {
      user.style.display = "flex";
    } else {
      user.style.display = "none";
    }
  });
});
</script>
<script>
// Mở hội thoại và ẩn chấm đỏ
function openConversation(toId) {
  // Ẩn chấm đỏ ngay lập tức khi mở cuộc trò chuyện
  const dot = document.querySelector(`.chat-user[data-id="${toId}"] .unread-dot`);
  if (dot) {
    dot.style.display = 'none';
  }
  window.location.href = `index.php?tin-nhan&to=${toId}`;
}
function openReviewModal() {
  // Ưu tiên lấy sản phẩm gần nhất từ DOM để luôn đúng ngay cả khi JS global chưa set kịp
  const chatBox = document.getElementById('chatMessages');
  let latest = null;
  if (chatBox) {
    // Ưu tiên card sản phẩm do chính mình gửi (tin nhắn bên phải - text-right)
    let cards = chatBox.querySelectorAll('.text-right .product-card-message');
    // Nếu không có (trường hợp hiếm), fallback tất cả product-card
    if (!cards.length) {
      cards = chatBox.querySelectorAll('.product-card-message');
    }
    if (cards.length > 0) {
      const lastCard = cards[cards.length - 1];
      try {
        // Lấy product_id từ link chi tiết
        const link = lastCard.querySelector('a[href*="index.php?detail&id="]');
        let productId = null;
        if (link) {
          const match = link.getAttribute('href').match(/detail&id=(\d+)/);
          if (match) {
            productId = parseInt(match[1], 10);
          }
        }

        if (productId) {
          // Lấy tên sản phẩm từ thẻ h6
          const h6 = lastCard.querySelector('h6');
          const productTitle = h6 ? h6.textContent.trim() : '';
          latest = {
            productId,
            productTitle
          };
        }
      } catch (e) {
        console.warn('Không thể lấy sản phẩm từ DOM để đánh giá:', e);
      }
    }
  }

  // Fallback: nếu không lấy được từ DOM, dùng global (trong trường hợp realtime)
  if (!latest && window.latestProductForReview && window.latestProductForReview.productId) {
    latest = window.latestProductForReview;
  }

  if (!latest || !latest.productId) {
    alert('Chưa xác định được sản phẩm để đánh giá. Vui lòng gửi/nhận sản phẩm trước.');
    return;
  }

  const modalEl = document.getElementById('modalDanhGia');
  if (!modalEl) return;
  if (typeof bootstrap === "undefined") {
    console.error("Bootstrap chưa được load!");
    return;
  }

  // Gán product_id và tiêu đề sản phẩm vào modal
  const inputProductId = document.getElementById('review-product-id');
  const titleEl = document.getElementById('review-product-title');
  if (inputProductId) {
    inputProductId.value = latest.productId;
  }
  if (titleEl) {
    titleEl.textContent = latest.productTitle || ('Sản phẩm #' + latest.productId);
  }

  const modal = new bootstrap.Modal(modalEl);
  modal.show();
}

// Khởi tạo chọn sao trong modal đánh giá
document.addEventListener('DOMContentLoaded', function () {
  const modal = document.getElementById('modalDanhGia');
  const ratingInput = document.getElementById('review-rating');
  const ratingText = document.getElementById('review-rating-text');

  function initStars() {
    if (!modal || !ratingInput) return;
    const stars = modal.querySelectorAll('.review-star-icon');
    if (!stars.length) return;

    function setRating(value) {
      const v = Math.max(1, Math.min(5, value));
      ratingInput.value = v;
      stars.forEach(star => {
        const starValue = parseInt(star.getAttribute('data-value'), 10);
        star.style.color = starValue <= v ? '#ffc107' : '#e0e0e0';
      });
      if (ratingText) {
        ratingText.textContent = v + ' sao';
      }
    }

    stars.forEach(star => {
      star.addEventListener('click', function () {
        const v = parseInt(this.getAttribute('data-value'), 10);
        setRating(v);
      });
      star.addEventListener('mouseenter', function () {
        const v = parseInt(this.getAttribute('data-value'), 10);
        stars.forEach(s => {
          const starValue = parseInt(s.getAttribute('data-value'), 10);
          s.style.color = starValue <= v ? '#ffc107' : '#e0e0e0';
        });
      });
    });

    // Khôi phục màu khi rời khỏi vùng sao
    const starContainer = document.getElementById('review-star-container');
    if (starContainer) {
      starContainer.addEventListener('mouseleave', function () {
        const v = parseInt(ratingInput.value || '5', 10);
        setRating(v);
      });
    }

    // Giá trị mặc định
    setRating(parseInt(ratingInput.value || '5', 10));
  }

  initStars();
});

// Chạy sau khi load
document.addEventListener("DOMContentLoaded", () => {
  // Ẩn chấm đỏ của cuộc trò chuyện đang xem
  if (typeof TO_USER_ID !== 'undefined') {
    const currentDot = document.querySelector(`.chat-user[data-id="${TO_USER_ID}"] .unread-dot`);
    if (currentDot) {
      currentDot.style.display = 'none';
    }
  }
  
  // Tự động gửi tin nhắn sản phẩm khi mở chat từ trang chi tiết sản phẩm
  if (typeof ID_SAN_PHAM !== 'undefined' && ID_SAN_PHAM > 0 && typeof TO_USER_ID !== 'undefined') {
    // Đợi một chút để WebSocket kết nối xong
    setTimeout(() => {
      // Lấy toàn bộ lịch sử tin nhắn để kiểm tra đã từng gửi card của sản phẩm này chưa
      fetch(`/api/chat-file-api.php?from=${CURRENT_USER_ID}&to=${TO_USER_ID}`)
        .then(res => res.json())
        .then(messages => {
          // Nếu trong lịch sử CHƯA có card của đúng sản phẩm này thì mới auto gửi
          const hasThisProductCard = (messages || []).some(m => {
            const content = (m.content || m.noi_dung || '');
            return content.includes('product-card-message') &&
                   content.includes(`index.php?detail&id=${ID_SAN_PHAM}`);
          });

          if (!hasThisProductCard) {
            // Lấy thông tin sản phẩm
            fetch(`/api/get-product-info.php?product_id=${ID_SAN_PHAM}`)
              .then(res => res.json())
              .then(data => {
                if (data.success && data.product) {
                  const product = data.product;
                  
                  // Tạo HTML card sản phẩm
                  const productCard = `
                    <div class="product-card-message" style="border: 1px solid #ddd; border-radius: 8px; padding: 12px; background: #fff; max-width: 300px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                      <div style="display: flex; gap: 12px;">
                        <img src="img/${product.image}" alt="${product.title.replace(/"/g, '&quot;')}" 
                             style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px; flex-shrink: 0;">
                        <div style="flex: 1; min-width: 0;">
                          <h6 style="margin: 0 0 8px 0; font-size: 14px; font-weight: 600; color: #333; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">${product.title}</h6>
                          <p style="margin: 0 0 8px 0; color: #dc3545; font-weight: bold; font-size: 16px;">
                            ${product.formatted_price}
                          </p>
                          <a href="index.php?detail&id=${product.id}" 
                             style="display: inline-block; font-size: 12px; color: #007bff; text-decoration: none; font-weight: 500;">
                            Xem chi tiết →
                          </a>
                        </div>
                      </div>
                    </div>
                  `;
                  
                  // Hàm gửi tin nhắn sản phẩm
                  function sendProductMessage() {
                    // Kiểm tra socket từ window hoặc global scope
                    const ws = window.socket || (typeof socket !== 'undefined' ? socket : null);
                    const queue = window.sendQueue || (typeof sendQueue !== 'undefined' ? sendQueue : null);
                    
                    if (ws && ws.readyState === WebSocket.OPEN) {
                      ws.send(JSON.stringify({
                        type: 'message',
                        from: CURRENT_USER_ID,
                        to: TO_USER_ID,
                        content: productCard,
                        product_id: ID_SAN_PHAM
                      }));
                    } else if (queue && Array.isArray(queue)) {
                      // Lưu vào queue nếu WebSocket chưa sẵn sàng
                      queue.push({
                        type: 'message',
                        from: CURRENT_USER_ID,
                        to: TO_USER_ID,
                        content: productCard,
                        product_id: ID_SAN_PHAM
                      });
                    } else {
                      // Thử lại sau 1 giây
                      setTimeout(sendProductMessage, 1000);
                    }
                  }
                  
                  // Thử gửi ngay
                  sendProductMessage();
                }
              })
              .catch(err => console.error("❌ Lỗi lấy thông tin sản phẩm:", err));
          }
        })
        .catch(err => console.error("❌ Lỗi kiểm tra tin nhắn:", err));
    }, 500); // Đợi 500ms để WebSocket kết nối
  }
  
  // Khởi tạo danh mục realtime: đồng bộ tin cuối và chấm đỏ
  bootstrapConversationListRealtime();
  // Rút gọn tên và tin cuối ban đầu
  compactConversationItems();
});

</script>

<script>
function bootstrapConversationListRealtime() {
  // Khởi tạo từ dữ liệu hiện có của server trong DOM
  document.querySelectorAll('.chat-user').forEach(li => {
    const last = li.querySelector('.js-last');
    const time = li.querySelector('.js-time');
    if (last && !last.textContent.trim()) {
      // nếu rỗng, sẽ được cập nhật khi có tin nhắn
    }
  });

  // Khi nhận tin nhắn mới qua WebSocket - hiển thị chấm đỏ realtime
  window.onNewChatMessage = (msg) => {
    const item = document.querySelector(`.chat-user[data-id="${msg.from}"]`) || document.querySelector(`.chat-user[data-id="${msg.to}"]`);
    if (!item) return;
    
    // Xác định người gửi (người không phải current user)
    const isFrom = String(msg.from) !== String(CURRENT_USER_ID) ? msg.from : msg.to;
    const li = document.querySelector(`.chat-user[data-id="${isFrom}"]`);
    if (!li) return;
    
    // Cập nhật tin cuối và thời gian
    const lastEl = li.querySelector('.js-last');
    const timeEl = li.querySelector('.js-time');
    
    // Xử lý tin nhắn sản phẩm - extract tên sản phẩm từ HTML
    let displayText = msg.content || msg.noi_dung || '';
    if (displayText.includes('product-card-message')) {
      // Lấy tên sản phẩm từ HTML card
      const tempDiv = document.createElement('div');
      tempDiv.innerHTML = displayText;
      const titleElement = tempDiv.querySelector('h6');
      if (titleElement) {
        displayText = 'Sản phẩm: ' + titleElement.textContent.trim();
      } else {
        // Fallback: tìm trong HTML
        const match = displayText.match(/<h6[^>]*>([^<]+)<\/h6>/);
        if (match) {
          displayText = 'Sản phẩm: ' + match[1].trim();
        } else {
          displayText = 'Đã gửi sản phẩm';
        }
      }
    }
    
    if (lastEl) lastEl.textContent = compactText(displayText, 5);
    if (timeEl) timeEl.textContent = formatRelativeTime(msg.timestamp);
    
    // Rút gọn tên nếu cần
    const nameEl = li.querySelector('.js-username');
    if (nameEl) nameEl.textContent = clipName(nameEl.getAttribute('title') || nameEl.textContent, 15);
    
    // Hiển thị chấm đỏ realtime - chỉ nếu KHÔNG phải cuộc trò chuyện đang xem
    const isCurrentConversation = typeof TO_USER_ID !== 'undefined' && String(isFrom) === String(TO_USER_ID);
    
    if (!isCurrentConversation) {
      // Có tin nhắn mới và không phải cuộc trò chuyện đang xem -> hiện chấm đỏ
      const dot = li.querySelector('.unread-dot');
      if (dot) dot.style.display = 'inline-block';
    } else {
      // Đang xem cuộc trò chuyện này -> ẩn chấm đỏ
      const dot = li.querySelector('.unread-dot');
      if (dot) dot.style.display = 'none';
    }
    
    // Đưa item lên đầu danh sách
    const list = li.parentNode;
    list.insertBefore(li, list.firstChild);
  };

  function formatRelativeTime(ts) {
    if (!ts) return '';
    const t = new Date(ts).getTime();
    const now = Date.now();
    const diff = Math.floor((now - t) / 1000);
    if (diff < 86400) {
      const d = new Date(t);
      const hh = String(d.getHours()).padStart(2,'0');
      const min = String(d.getMinutes()).padStart(2,'0');
      return `${hh}:${min}`;
    }
    if (diff < 2*86400) return `1 ngày trước`;
    const days = Math.floor(diff/86400);
    if (days < 30) return `${days} ngày trước`;
    const months = Math.floor(days/30);
    if (months < 12) return `${months} tháng trước`;
    const years = Math.floor(days/365);
    return `${years} năm trước`;
  }
}

// RÚT GỌN UI DANH MỤC
function compactConversationItems() {
  document.querySelectorAll('.chat-user').forEach(li => {
    const nameEl = li.querySelector('.js-username');
    if (nameEl) {
      const full = nameEl.getAttribute('title') || nameEl.textContent;
      nameEl.textContent = clipName(full, 15);
    }
    const lastEl = li.querySelector('.js-last');
    if (lastEl) {
      let text = lastEl.textContent;
      // Kiểm tra nếu là HTML code của sản phẩm
      if (text.includes('product-card-message') || text.includes('<h6')) {
        // Extract tên sản phẩm từ HTML
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = text;
        const titleElement = tempDiv.querySelector('h6');
        if (titleElement) {
          text = 'Sản phẩm: ' + titleElement.textContent.trim();
        } else {
          // Fallback: regex extract
          const match = text.match(/<h6[^>]*>([^<]+)<\/h6>/);
          if (match) {
            text = 'Sản phẩm: ' + match[1].trim();
          } else {
            text = 'Đã gửi sản phẩm';
          }
        }
      }
      lastEl.textContent = compactText(text, 10);
    }
  });
}

function clipName(name, maxChars) {
  if (!name) return '';
  if (name.length <= maxChars) return name;
  return name.slice(0, maxChars-1) + '…';
}

function compactText(text, maxWords) {
  if (!text) return '';
  const words = text.trim().split(/\s+/);
  if (words.length <= maxWords) return text;
  return words.slice(0, maxWords).join(' ') + '…';
}

</script>


