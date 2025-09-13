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
  }
  .btn-suggestion:hover {
    background-color: #ffe082;
    color: #000;
    border-color: #ffc107;
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
    margin-top: -30px; 
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


</style>

<div class="container-fluid chat-wrapper" style="max-width: 1200px;">
  <div class="row border rounded shadow-sm" style="height: 84vh; overflow: hidden;">
    <!-- Danh sách người dùng -->
    <div class="col-md-4 col-lg-3 bg-light p-3 overflow-auto" style="border-right: 1px solid #dee2e6;">
    <input type="text" class="form-control mb-3" placeholder="Tìm người dùng..." id="searchUserInput">
      <ul class="list-unstyled">
        <?php foreach ($conversations as $user): ?>
        <li class="media p-2 mb-2 rounded chat-user <?= ($user['id'] == $to_user_id ? 'active' : '') ?>" 
            data-id="<?= $user['id'] ?>"
            style="cursor: pointer;" 
            onclick="openConversation(<?= $user['id'] ?>)">
          <img src="img/<?= htmlspecialchars($user['avatar']) ?>" class="mr-3 rounded-circle" width="50" height="50">
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
        <div class="d-flex align-items-center">
        <img src="img/<?= htmlspecialchars($receiver['avatar']) ?>" class="rounded-circle mr-2 chat-header-avatar" width="40" height="40">
      <strong><?= htmlspecialchars($receiver['username']) ?></strong>
    </div>

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

<!-- Modal đánh giá -->
<div class="modal fade" id="modalDanhGia" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
  <form action="api/review-api.php?act=themDanhGia" method="post">
      <input type="hidden" name="reviewer_id" value="">
<input type="hidden" name="reviewed_user_id" value="">
<input type="hidden" name="product_id" value="">

      <div class="modal-header">
        <h5 class="modal-title">Đánh giá người bán</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <label>Số sao</label>
        <select name="rating" class="form-control" required>
          <?php for ($i = 5; $i >= 1; $i--): ?>
            <option value="<?= $i ?>"><?= $i ?> sao</option>
          <?php endfor; ?>
        </select>

        <label class="mt-2">Bình luận</label>
        <textarea name="comment" class="form-control" required></textarea>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
      </div>
    </form>
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

  const form = document.querySelector("form.d-flex");
  const input = form.querySelector("input");
  const suggestContainer = document.createElement("div");
  suggestContainer.className = "d-flex flex-wrap gap-2 mt-2";

  suggestions.forEach(msg => {
    const btn = document.createElement("button");
    btn.type = "button";
    btn.className = "btn btn-sm btn-outline-secondary btn-suggestion mr-2 mb-2";
    btn.textContent = msg;
    btn.onclick = () => {
      input.value = msg;
      input.focus();
    };
    suggestContainer.appendChild(btn);
  });

  if (form && form.parentNode) {
    form.parentNode.insertBefore(suggestContainer, form);
  }
</script>

<script>
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
// Mở hội thoại và đánh dấu đã nhấn (đọc) bằng localStorage
function openConversation(toId) {
  try {
    const key = `openedConv:${CURRENT_USER_ID}`;
    const opened = JSON.parse(localStorage.getItem(key) || '{}');
    opened[String(toId)] = true;
    localStorage.setItem(key, JSON.stringify(opened));
  } catch (e) {}
  window.location.href = `index.php?tin-nhan&to=${toId}`;
}
// Hàm gọi API lấy tin đầu và thêm nút "Viết đánh giá"
async function checkFirstMessageAndShowButton(from, to, selector) {
  try {
    // Kiểm tra đã đánh giá chưa
    const checkRes = await fetch(`api/check-reviewed.php?from=${from}&to=${to}&product_id=${ID_SAN_PHAM}`);
    if (!checkRes.ok) return;
    const checkData = await checkRes.json();
    if (checkData.reviewed) return; // Đã đánh giá thì không hiển thị nút
console.log('API check-reviewed:', checkData);
    // Lấy tin nhắn đầu tiên
    const res = await fetch(`api/chat-first-message.php?from=${from}&to=${to}`);
    if (!res.ok) return;
    const msg = await res.json();

    const firstTime = new Date(msg.thoi_pricen).getTime();
    const now = Date.now();
    const isSender = msg.sender_id == from;
    const timePassed = (now - firstTime) > 3600000; // hơn 1 giờ

    if (isSender && timePassed) {
      const html = `<a href="index.php?action=danhprice&from=${msg.sender_id}&to=${msg.receiver_id}&product_id=${msg.product_id}" 
  class="btn btn-sm btn-outline-warning mt-1">Viết đánh giá</a>`;
      const el = document.querySelector(selector);
      if (el && !el.querySelector('.btn-outline-warning')) {
        el.insertAdjacentHTML("beforeend", html);
      }
    }
  } catch (err) {
    console.error("❌ Lỗi API chat-first-message hoặc check-reviewed:", err);
  }
}

// Hàm hiển thị modal và gán giá trị
function openReviewModal(idNguoiDanhGia, idNguoiDuocDanhGia, idSanPham) {
  const modalEl = document.getElementById('modalDanhGia');
  if (!modalEl) {
    console.error("Không tìm thấy modal DOM");
    return;
  }

  // Gán dữ liệu vào form
  modalEl.querySelector('input[name="reviewer_id"]').value = idNguoiDanhGia;
modalEl.querySelector('input[name="reviewed_user_id"]').value = idNguoiDuocDanhGia;
  modalEl.querySelector('input[name="product_id"]').value = idSanPham;

  // Delay để đảm bảo bootstrap đã load
  setTimeout(() => {
    if (typeof bootstrap === "undefined") {
      console.error("Bootstrap chưa được load!");
      return;
    }
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
  }, 50); // delay nhẹ để đảm bảo script bootstrap được load xong
}


// Chạy sau khi load
document.addEventListener("DOMContentLoaded", () => {
  const fromId = CURRENT_USER_ID;
  document.querySelectorAll(".chat-user").forEach(userEl => {
    const toId = userEl.getAttribute("data-id");
    const selector = `.chat-user[data-id="${toId}"] .media-body`;
    checkFirstMessageAndShowButton(fromId, toId, selector);
  });
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

  // Khi tải số unread ban đầu
  window.onUnreadBootstrap = (unreadMap) => {
    Object.keys(unreadMap || {}).forEach(fromId => {
      if (!hasOpenedConversation(fromId)) {
        const el = document.querySelector(`.chat-user[data-id="${fromId}"] .unread-dot`);
        if (el) el.style.display = 'inline-block';
      }
    });
  };
  // Nếu client đã fetch unread trước khi gắn handler
  if (window.__UNREAD_BOOT) {
    window.onUnreadBootstrap(window.__UNREAD_BOOT);
    window.__UNREAD_BOOT = null;
  }
  // Đọc từ localStorage để vẫn hiển thị chấm đỏ ngay cả trước khi API trả về
  try {
    const cached = localStorage.getItem(`unread:${CURRENT_USER_ID}`);
    if (cached) {
      const map = JSON.parse(cached);
      window.onUnreadBootstrap(map);
    }
  } catch (e) {}

  // Re-apply một lần nữa sau 800ms để chống trường hợp component khác ghi đè
  setTimeout(() => {
    try {
      const cached = localStorage.getItem(`unread:${CURRENT_USER_ID}`);
      if (cached) {
        const map = JSON.parse(cached);
        window.onUnreadBootstrap(map);
      }
    } catch (e) {}
  }, 800);

  // Khi server báo unread thay đổi
  window.onUnreadChanged = (msg) => {
    if (msg.type === 'unread') {
      if (!hasOpenedConversation(msg.from)) {
        const el = document.querySelector(`.chat-user[data-id="${msg.from}"] .unread-dot`);
        if (el) el.style.display = 'inline-block';
      }
    }
    if (msg.type === 'unread_summary') {
      // reset toàn bộ
      document.querySelectorAll('.chat-user .unread-dot').forEach(dot => dot.style.display = 'none');
      const unread = msg.unread || {};
      Object.keys(unread).forEach(fromId => {
        if (!hasOpenedConversation(fromId)) {
          const el = document.querySelector(`.chat-user[data-id="${fromId}"] .unread-dot`);
          if (el) el.style.display = 'inline-block';
        }
      });
    }
  };

  // Khi nhận tin nhắn mới, cập nhật tin cuối + thời gian và đưa hội thoại lên đầu
  window.onNewChatMessage = (msg) => {
    const item = document.querySelector(`.chat-user[data-id="${msg.from}"]`) || document.querySelector(`.chat-user[data-id="${msg.to}"]`);
    if (!item) return;
    const isFrom = String(msg.from) !== String(CURRENT_USER_ID) ? msg.from : msg.to;
    const li = document.querySelector(`.chat-user[data-id="${isFrom}"]`);
    if (!li) return;
    const lastEl = li.querySelector('.js-last');
    const timeEl = li.querySelector('.js-time');
    if (lastEl) lastEl.textContent = compactText(msg.content || msg.noi_dung || '', 5);
    if (timeEl) timeEl.textContent = formatRelativeTime(msg.timestamp);
    // rút gọn tên nếu cần
    const nameEl = li.querySelector('.js-username');
    if (nameEl) nameEl.textContent = clipName(nameEl.getAttribute('title') || nameEl.textContent, 15);
    // nếu không phải cuộc trò chuyện đang mở, bật chấm đỏ
    if (typeof TO_USER_ID === 'undefined' || String(isFrom) !== String(TO_USER_ID)) {
      const dot = li.querySelector('.unread-dot');
      if (dot) dot.style.display = 'inline-block';
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
      lastEl.textContent = compactText(lastEl.textContent, 10);
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

// Utility: kiểm tra người dùng đã từng mở hội thoại với fromId chưa
function hasOpenedConversation(fromId) {
  try {
    const key = `openedConv:${CURRENT_USER_ID}`;
    const opened = JSON.parse(localStorage.getItem(key) || '{}');
    return !!opened[String(fromId)];
  } catch (e) {
    return false;
  }
}
</script>


