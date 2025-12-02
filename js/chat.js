// Tự động phát hiện WebSocket URL dựa trên môi trường
function getWebSocketURL() {
  const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
  const hostname = window.location.hostname;
  const port = window.location.port || (window.location.protocol === 'https:' ? '443' : '80');
  
  // Nếu đang chạy trên localhost (development)
  if (hostname === 'localhost' || hostname === '127.0.0.1') {
    return 'ws://localhost:3000';
  }
  
  // Nếu đang chạy trên hosting (production)
  // Sử dụng cùng domain nhưng port khác cho WebSocket
  return `${protocol}//${hostname}:3000`;
}

let socket = null;
let sendQueue = [];
let reconnectAttempts = 0;
const MAX_RECONNECT_DELAY = 10000; // ms

// Expose socket và sendQueue ra window để có thể truy cập từ các script khác
window.socket = socket;
window.sendQueue = sendQueue;

function signWebSocketPayload(userId) {
  // Nếu có biến toàn cục WS_SECRET trên server thì server sẽ bật xác thực.
  // Client chỉ tính chữ ký khi có WS_AUTH_SECRET trên window (tùy bạn set qua blade/php)
  try {
    const secret = window.WS_AUTH_SECRET;
    const hasSecret = typeof secret === 'string' && secret.length > 0;
    const ts = Math.floor(Date.now() / 1000).toString();
    if (!hasSecret) return { user_id: userId };
    // HMAC-SHA256 trên client: trình duyệt không có crypto HMAC thuần; fallback gửi plain để server tắt auth.
    // Nếu cần thật sự, nên ký từ server-side và in vào HTML (sig, ts) khi render.
    if (window.WS_AUTH_SIG && window.WS_AUTH_TS) {
      return { user_id: userId, ts: window.WS_AUTH_TS, sig: window.WS_AUTH_SIG };
    }
    return { user_id: userId, ts }; // không có sig -> server phải tắt wsSecret để chấp nhận
  } catch (e) {
    return { user_id: userId };
  }
}

function connectSocket() {
  socket = new WebSocket(getWebSocketURL());
  window.socket = socket; // Cập nhật window.socket

  socket.addEventListener('open', () => {
    reconnectAttempts = 0;
    const authPayload = signWebSocketPayload(CURRENT_USER_ID);
    socket.send(JSON.stringify({ type: 'register', ...authPayload }));
    // Flush queue
    if (sendQueue.length) {
      sendQueue.forEach(item => socket.send(JSON.stringify(item)));
      sendQueue = [];
    }
  });

  socket.addEventListener('close', () => {
    // exponential backoff
    reconnectAttempts += 1;
    const delay = Math.min(300 * reconnectAttempts, MAX_RECONNECT_DELAY);
    setTimeout(connectSocket, delay);
  });

  socket.addEventListener('message', (event) => {
    const msg = JSON.parse(event.data);
    if (msg.type === 'message') {
      renderMessage(msg, true);
      // Tín hiệu để UI có thể hiển thị badge/chấm đỏ
      if (typeof window.onNewChatMessage === 'function') {
        window.onNewChatMessage(msg);
      }
      // Nếu đang mở đúng cuộc trò chuyện, gửi mark_read để xóa unread
      if (typeof TO_USER_ID !== 'undefined' && String(msg.from) === String(TO_USER_ID)) {
        socket.send(JSON.stringify({ type: 'mark_read', from: msg.from, to: CURRENT_USER_ID }));
      }
    }
  });
}

connectSocket();

let chatBox = null;
const shownMessages = new Set();

// Theo dõi tin nhắn sản phẩm mới nhất để phục vụ nút "Viết đánh giá"
window.latestProductForReview = null;
let reviewButtonTimeout = null;
const REVIEW_DELAY_MS = 30 * 1000; // 30 giây

function renderMessage(msg, isFromSocket = false) {
  const content = msg.content || msg.noi_dung; // fallback tương thích cũ
  const timestamp = msg.timestamp || msg.created_time || "";
  const messageKey = `${msg.from}_${content}_${timestamp}`;

  if (shownMessages.has(messageKey)) return;
  shownMessages.add(messageKey);

  const isMe = msg.from == CURRENT_USER_ID;
  
  // Kiểm tra nếu là tin nhắn sản phẩm (có chứa product-card-message)
  const isProductMessage = content.includes('product-card-message');
  
  // Nếu là tin nhắn sản phẩm, hiển thị HTML trực tiếp, không wrap trong bubble
  let html;
  if (isProductMessage) {
    html = `<div class="${isMe ? 'text-right' : 'text-left'} mb-3">
      <div style="display: inline-block; max-width: 100%;">
        ${content}
      </div>
    </div>`;
  } else {
    html = `<div class="${isMe ? 'text-right' : 'text-left'} mb-2">
      <span class="${isMe ? 'bg-warning text-white chat-bubble-sent' : 'chat-bubble-received'} px-3 py-2 rounded d-inline-block">
        ${content}
      </span>
    </div>`;
  }

  if (chatBox) {
    chatBox.insertAdjacentHTML('beforeend', html);
    chatBox.scrollTop = chatBox.scrollHeight;
  }

  // Nếu là tin nhắn sản phẩm, cập nhật sản phẩm gần nhất để review
  if (isProductMessage) {
    try {
      // Lấy product_id nếu có trong payload, nếu không thì parse từ link detail
      let productId = msg.product_id || null;
      if (!productId) {
        const match = content.match(/index\.php\?detail&id=(\d+)/);
        if (match) {
          productId = parseInt(match[1], 10);
        }
      }

      if (productId) {
        // Lấy tên sản phẩm từ thẻ h6 trong card
        let productTitle = '';
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = content;
        const h6 = tempDiv.querySelector('h6');
        if (h6) {
          productTitle = h6.textContent.trim();
        }

        window.latestProductForReview = {
          productId,
          productTitle,
          timestamp: timestamp || new Date().toISOString()
        };

        scheduleReviewButtonCheck();
      }
    } catch (e) {
      console.warn('Không thể cập nhật sản phẩm để đánh giá:', e);
    }
  }
}

function scheduleReviewButtonCheck() {
  const btn = document.getElementById('btnWriteReview');
  if (!btn || !window.latestProductForReview) return;

  // Chỉ hiển thị cho người hiện tại (cả buyer/seller đều có thể đánh giá,
  // nếu cần giới hạn bên mua thì sẽ kiểm tra phía server)

  if (reviewButtonTimeout) {
    clearTimeout(reviewButtonTimeout);
    reviewButtonTimeout = null;
  }

  const ts = new Date(window.latestProductForReview.timestamp).getTime();
  if (!ts || isNaN(ts)) return;

  const diff = Date.now() - ts;
  if (diff >= REVIEW_DELAY_MS) {
    btn.style.display = 'inline-flex';
  } else {
    btn.style.display = 'none';
    reviewButtonTimeout = setTimeout(() => {
      btn.style.display = 'inline-flex';
    }, REVIEW_DELAY_MS - diff);
  }
}

// Gửi tin nhắn
function sendMessage(noiDung) {
  if (!noiDung.trim()) return;
  const payload = {
    type: 'message',
    from: CURRENT_USER_ID,
    to: TO_USER_ID,
    content: noiDung,
    product_id: ID_SAN_PHAM
  };
  if (socket && socket.readyState === WebSocket.OPEN) {
    socket.send(JSON.stringify(payload));
  } else {
    sendQueue.push(payload);
  }
}


// Load tin nhắn cũ từ file
window.addEventListener("DOMContentLoaded", () => {
  chatBox = document.getElementById('chatMessages');
  if (typeof TO_USER_ID !== 'undefined') {
    fetch(`/api/chat-file-api.php?from=${CURRENT_USER_ID}&to=${TO_USER_ID}`)
      .then(res => res.json())
      .then(messages => {
        // Chuẩn hóa và render tất cả tin nhắn
        messages.forEach(msg => {
          if (!msg.content && msg.noi_dung) {
            msg.content = msg.noi_dung;
          }
          renderMessage(msg, false);
        });

        // TÌM CARD SẢN PHẨM GẦN NHẤT DO CHÍNH MÌNH GỬI (mình là người hỏi giá)
        let latestProduct = null;
        if (Array.isArray(messages) && messages.length > 0) {
          for (let i = messages.length - 1; i >= 0; i--) {
            const m = messages[i];
            const content = m.content || m.noi_dung || '';
            if (content.includes('product-card-message') && String(m.from) === String(CURRENT_USER_ID)) {
              // Lấy product_id từ payload hoặc từ link detail
              let productId = m.product_id || null;
              if (!productId) {
                const match = content.match(/index\.php\?detail&id=(\d+)/);
                if (match) {
                  productId = parseInt(match[1], 10);
                }
              }
              if (!productId) continue;

              // Lấy tên sản phẩm từ <h6>
              let productTitle = '';
              const tempDiv = document.createElement('div');
              tempDiv.innerHTML = content;
              const h6 = tempDiv.querySelector('h6');
              if (h6) {
                productTitle = h6.textContent.trim();
              }

              latestProduct = {
                productId,
                productTitle,
                timestamp: m.timestamp || m.created_time || new Date().toISOString()
              };
              break; // dừng ở card gần nhất
            }
          }
        }

        // Nếu tìm được sản phẩm, lưu lại và BẬT NÚT LUÔN (để test cho chắc)
        if (latestProduct) {
          window.latestProductForReview = latestProduct;
          const btn = document.getElementById('btnWriteReview');
          if (btn) {
            btn.style.display = 'inline-flex';
          }
        }

        // Sau khi mở cuộc trò chuyện, đánh dấu đã đọc toàn bộ tin nhắn từ đối phương
        const markReadPayload = {
          type: 'mark_read',
          from: TO_USER_ID,
          to: CURRENT_USER_ID
        };

        if (socket && socket.readyState === WebSocket.OPEN) {
          socket.send(JSON.stringify(markReadPayload));
        } else {
          // Nếu socket chưa sẵn sàng, đẩy vào hàng đợi để gửi sau khi kết nối
          sendQueue.push(markReadPayload);
        }
      })
      .catch(err => console.error("❌ Lỗi khi đọc file JSON:", err));
  }

  // Không cần fetch unread API nữa - chỉ dùng realtime qua WebSocket
});
