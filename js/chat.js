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
    // Không xử lý unread API nữa - chỉ dùng realtime qua onNewChatMessage
  });
}

connectSocket();

let chatBox = null;
const shownMessages = new Set();

function renderMessage(msg, isFromSocket = false) {
  const content = msg.content || msg.noi_dung; // fallback tương thích cũ
  const timestamp = msg.timestamp || "";
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

// Khi socket mở
// Các listener đã chuyển vào connectSocket()

// Load tin nhắn cũ từ file
window.addEventListener("DOMContentLoaded", () => {
  chatBox = document.getElementById('chatMessages');
  // Dùng đường dẫn tuyệt đối từ gốc site để tránh lệch path khi ở trong subfolder (vd: /view/...)
  if (typeof TO_USER_ID !== 'undefined') {
    fetch(`/api/chat-file-api.php?from=${CURRENT_USER_ID}&to=${TO_USER_ID}`)
      .then(res => res.json())
      .then(messages => {
        // Chuẩn hóa dữ liệu cũ sang mới trên client (nếu có noi_dung)
        messages.forEach(msg => {
          if (!msg.content && msg.noi_dung) {
            msg.content = msg.noi_dung;
          }
          renderMessage(msg, false);
        });
      })
      .catch(err => console.error("❌ Lỗi khi đọc file JSON:", err));
  }

  // Không cần fetch unread API nữa - chỉ dùng realtime qua WebSocket
});
