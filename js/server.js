const WebSocket = require('ws');
const http = require('http');
const fs = require('fs');
const path = require('path');

// Livestream WebSocket variables
let livestreamClients = {};
let livestreamRooms = {};

// Cấu hình động - có thể thay đổi theo môi trường
let CONFIG = {
  hostname: process.env.HOSTNAME || 'localhost',
  port: process.env.PORT || 8080,
  basePath: process.env.BASE_PATH || '/choviet29' // Có thể thay đổi qua environment variable
};

console.log("🟡 Đang chạy đúng file server.js JSON");
console.log("🔍 Current working directory:", process.cwd());
console.log("🔍 CONFIG loaded:", CONFIG);

// Thử load config từ file nếu có
try {
  const configPath = path.join(__dirname, '../config/server_config.js');
  if (fs.existsSync(configPath)) {
    const fileConfig = require(configPath);
    CONFIG = { ...CONFIG, ...fileConfig };
    console.log('📁 Đã load config từ file:', configPath);
  }
} catch (err) {
  console.log('⚠️ Không thể load config file, sử dụng config mặc định');
}

console.log('🔧 Config hiện tại:', CONFIG);

// Tạo WebSocket server trên port riêng
const wss = new WebSocket.Server({ port: CONFIG.wsPort || 3000 });

console.log(`🚀 WebSocket server đang chạy trên port ${CONFIG.wsPort || 3000}`);
console.log(`🔌 WebSocket server sẵn sàng nhận kết nối`);
let clients = {};

wss.on('connection', function connection(ws) {
  ws.on('message', function incoming(message) {
    const data = JSON.parse(message);

    if (data.type === 'register') {
      // Xác thực đơn giản bằng HMAC nếu có secret, payload: {user_id, ts, sig}
      // sig = HMAC_SHA256(user_id + ":" + ts, secret)
      try {
        const hasSecret = !!CONFIG.wsSecret;
        if (hasSecret) {
          const crypto = require('crypto');
          const userId = String(data.user_id || '');
          const ts = String(data.ts || '');
          const sig = String(data.sig || '');
          if (!userId || !ts || !sig) {
            ws.close(4001, 'missing auth fields');
            return;
          }
          // chống replay: lệch thời gian tối đa 5 phút
          const now = Math.floor(Date.now() / 1000);
          const delta = Math.abs(now - parseInt(ts, 10));
          if (delta > 300) {
            ws.close(4002, 'timestamp expired');
            return;
          }
          const base = userId + ':' + ts;
          const expected = crypto
            .createHmac('sha256', CONFIG.wsSecret)
            .update(base)
            .digest('hex');
          if (expected !== sig) {
            ws.close(4003, 'invalid signature');
            return;
          }
        }
        clients[data.user_id] = ws;
        ws.user_id = data.user_id;
        console.log(`🟢 User ${data.user_id} đã kết nối`);
      } catch (e) {
        console.error('Auth error:', e);
        ws.close(4000, 'auth error');
      }
      return;
    }

    if (data.type === 'message') {
      const { from, to, content, noi_dung, product_id } = data;
      const timestamp = new Date().toISOString();

      const ids = [from, to].sort((a, b) => a - b);
      const fileName = `chat_${ids[0]}_${ids[1]}.json`;

      // ✅ Sửa lỗi: Đảm bảo đường dẫn luôn đúng với thư mục choviet29
      // Sử dụng cấu hình từ file config nếu có, nếu không thì dùng đường dẫn tương đối
      let chatFolderPath;
      if (CONFIG.chatPath) {
        chatFolderPath = CONFIG.chatPath;
      } else {
        // Sử dụng process.cwd() để lấy thư mục hiện tại thay vì __dirname
        const currentDir = process.cwd();
        chatFolderPath = path.join(currentDir, "chat");
      }
      
      const filePath = path.join(chatFolderPath, fileName);
      
      console.log("🔍 Chat folder path:", chatFolderPath);
      console.log("🔍 Full file path:", filePath);

      // ✅ Tạo thư mục chat nếu chưa có
      if (!fs.existsSync(chatFolderPath)) {
        fs.mkdirSync(chatFolderPath, { recursive: true });
      }

      // ✅ Nếu file chưa tồn tại thì tạo file trống và lưu DB
      if (!fs.existsSync(filePath)) {
        try {
          fs.writeFileSync(filePath, "[]");
          console.log("📁 Đã tạo file mới:", filePath);

          const postFileName = JSON.stringify({ from, to, file_name: fileName });
          const req2 = http.request({
            hostname: CONFIG.hostname,
            port: CONFIG.port,
            path: CONFIG.basePath + '/api/chat-save-filename.php',
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Content-Length': Buffer.byteLength(postFileName)
            }
          }, res => {
            console.log('📁 Đã lưu tên file vào DB:', fileName);
          });
          req2.on('error', error => console.error("❌ Lỗi lưu tên file:", error));
          req2.write(postFileName);
          req2.end();

        } catch (err) {
          console.error("❌ Lỗi tạo file:", err);
        }
      }

      // ✅ Đọc và cập nhật file JSON
      let messages = [];
      try {
        const fileContent = fs.readFileSync(filePath, 'utf-8');
        messages = JSON.parse(fileContent);
      } catch (err) {
        console.error("❌ Lỗi đọc file JSON:", err);
      }

      // Lưu field chuẩn 'content' (giữ tương thích khi nhận noi_dung từ client cũ)
      messages.push({ from, to, content: (noi_dung || content), timestamp });

      fs.writeFile(filePath, JSON.stringify(messages, null, 2), err => {
        if (err) console.error("❌ Lỗi ghi file JSON:", err);
        else console.log("✅ Đã lưu tin nhắn vào file:", fileName);
      });

      // ✅ Gửi tin nhắn về 2 phía
      // Phát về client với field chuẩn 'content'
      const socketMessage = JSON.stringify({ type: 'message', from, to, content: (noi_dung || content), timestamp });
      if (clients[to]) clients[to].send(socketMessage);
      if (clients[from]) clients[from].send(socketMessage);

      // ✅ Cập nhật chưa đọc cho người nhận
      try {
        const unreadFile = path.join(chatFolderPath, `unread_${to}.json`);
        let unread = {};
        if (fs.existsSync(unreadFile)) {
          unread = JSON.parse(fs.readFileSync(unreadFile, 'utf-8') || '{}');
        }
        unread[from] = (unread[from] || 0) + 1;
        fs.writeFileSync(unreadFile, JSON.stringify(unread, null, 2));
        // Thông báo realtime
        if (clients[to]) {
          clients[to].send(JSON.stringify({ type: 'unread', from, to, count: unread[from] }));
        }
      } catch (e) {
        console.error('❌ Lỗi cập nhật unread:', e);
      }

      // ✅ Gọi API lưu vào DB nếu cần (gửi cả noi_dung và content để tương thích API)
      const postData = JSON.stringify({ from, to, noi_dung: (noi_dung || content), content: (content || noi_dung), product_id: product_id || null });
      const req = http.request({
        hostname: CONFIG.hostname,
        port: CONFIG.port,
        path: CONFIG.basePath + '/api/chat-api.php',
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Content-Length': Buffer.byteLength(postData)
        }
      }, res => {
        console.log('📩 Gửi API chat-api.php:', res.statusCode);
        res.on('data', chunk => console.log('📦 Nội dung:', chunk.toString()));
      });
      req.on('error', error => console.error("❌ Lỗi gọi API PHP:", error));
      req.write(postData);
      req.end();
    }

    // ✅ Đánh dấu đã đọc một hội thoại: { type: 'mark_read', from, to }
    if (data.type === 'mark_read') {
      const { from, to } = data; // from: đối tác, to: user hiện tại
      try {
        let chatFolderPath;
        if (CONFIG.chatPath) {
          chatFolderPath = CONFIG.chatPath;
        } else {
          const currentDir = process.cwd();
          chatFolderPath = path.join(currentDir, "chat");
        }
        const unreadFile = path.join(chatFolderPath, `unread_${to}.json`);
        let unread = {};
        if (fs.existsSync(unreadFile)) {
          unread = JSON.parse(fs.readFileSync(unreadFile, 'utf-8') || '{}');
        }
        if (unread[from]) delete unread[from];
        fs.writeFileSync(unreadFile, JSON.stringify(unread, null, 2));
        if (clients[to]) {
          clients[to].send(JSON.stringify({ type: 'unread_summary', to, unread }));
        }
      } catch (e) {
        console.error('❌ Lỗi mark_read:', e);
      }
      return;
    }
    
    // Xử lý livestream messages
    if (data.type && (data.type.startsWith('join_livestream') || 
                     data.type.startsWith('leave_livestream') || 
                     data.type.startsWith('livestream_') || 
                     data.type.startsWith('pin_') || 
                     data.type.startsWith('unpin_') || 
                     data.type.startsWith('add_to_cart') || 
                     data.type.startsWith('remove_from_cart') || 
                     data.type.startsWith('update_cart_') || 
                     data.type.startsWith('livestream_stats') ||
                     data.type.startsWith('webrtc_') ||
                     data.type.startsWith('request_') ||
                     data.type.startsWith('get_'))) {
      console.log('🎯 Processing livestream message:', data.type, 'for livestream:', data.livestream_id);
      handleLivestreamMessage(ws, data);
      return;
    }
  });

  ws.on('close', () => {
    if (ws.user_id && clients[ws.user_id]) {
      delete clients[ws.user_id];
      console.log(`🔴 User ${ws.user_id} đã ngắt kết nối`);
    }
    
    // Xóa client khỏi livestream rooms
    Object.keys(livestreamRooms).forEach(roomId => {
      if (livestreamRooms[roomId]) {
        const index = livestreamRooms[roomId].indexOf(ws);
        if (index > -1) {
          livestreamRooms[roomId].splice(index, 1);
        }
      }
    });
  });
});

// =============================================
// LIVESTREAM WEBSOCKET HANDLERS
// =============================================

function handleLivestreamMessage(ws, data) {
  switch (data.type) {
    case 'join_livestream':
      joinLivestream(ws, data);
      break;
    case 'leave_livestream':
      leaveLivestream(ws, data);
      break;
    case 'livestream_chat':
      handleLivestreamChat(ws, data);
      break;
    case 'pin_product':
      handlePinProduct(ws, data);
      break;
    case 'unpin_product':
      handleUnpinProduct(ws, data);
      break;
    case 'add_to_cart':
      handleAddToCart(ws, data);
      break;
    case 'remove_from_cart':
      handleRemoveFromCart(ws, data);
      break;
    case 'update_cart_quantity':
      handleUpdateCartQuantity(ws, data);
      break;
    case 'livestream_stats':
      handleLivestreamStats(ws, data);
      break;
    // WebRTC signaling bridge
    case 'webrtc_offer':
    case 'webrtc_answer':
    case 'webrtc_ice':
    case 'request_offer':
      forwardWebRTCSignal(ws, data);
      break;
    case 'livestream_status_update':
      handleLivestreamStatusUpdate(ws, data);
      break;
    case 'get_livestream_status':
      handleGetLivestreamStatus(ws, data);
      break;
    default:
      console.log('❓ Unknown livestream message type:', data.type);
  }
}

function joinLivestream(ws, data) {
  const { livestream_id, user_id, user_type } = data;
  
  if (!livestreamRooms[livestream_id]) {
    livestreamRooms[livestream_id] = [];
  }
  
  if (!livestreamRooms[livestream_id].includes(ws)) {
    livestreamRooms[livestream_id].push(ws);
  }
  
  ws.livestream_id = livestream_id;
  ws.user_id = user_id;
  ws.user_type = user_type || 'viewer';
  
  // Lưu vào livestreamClients
  const clientId = `${user_id}_${livestream_id}`;
  livestreamClients[clientId] = {
    ws: ws,
    livestream_id: livestream_id,
    user_id: user_id,
    type: user_type || 'viewer'
  };
  
  console.log(`🎥 User ${user_id} đã tham gia livestream ${livestream_id}`);
  
  // Gửi thông tin phòng cho client
  ws.send(JSON.stringify({
    type: 'livestream_joined',
    livestream_id: livestream_id,
    viewers_count: livestreamRooms[livestream_id].length
  }));
  
  // Thông báo cho các client khác
  broadcastToLivestream(livestream_id, {
    type: 'viewer_joined',
    user_id: user_id,
    viewers_count: livestreamRooms[livestream_id].length
  }, ws);
}

function leaveLivestream(ws, data) {
  const { livestream_id } = data;
  
  if (livestreamRooms[livestream_id]) {
    const index = livestreamRooms[livestream_id].indexOf(ws);
    if (index > -1) {
      livestreamRooms[livestream_id].splice(index, 1);
    }
  }
  
  console.log(`🎥 User đã rời livestream ${livestream_id}`);
  
  // Thông báo cho các client khác
  broadcastToLivestream(livestream_id, {
    type: 'viewer_left',
    viewers_count: livestreamRooms[livestream_id] ? livestreamRooms[livestream_id].length : 0
  }, ws);
}

function handleLivestreamChat(ws, data) {
  const { livestream_id, user_id, message, username } = data;
  
  const chatMessage = {
    type: 'livestream_chat',
    livestream_id: livestream_id,
    user_id: user_id,
    username: username,
    message: message,
    timestamp: new Date().toISOString()
  };
  
  // Broadcast tin nhắn đến tất cả client trong livestream
  broadcastToLivestream(livestream_id, chatMessage);
  
  console.log(`💬 Chat trong livestream ${livestream_id}: ${username}: ${message}`);
}

function handlePinProduct(ws, data) {
  const { livestream_id, product_id, product_info } = data;
  
  const pinMessage = {
    type: 'product_pinned',
    livestream_id: livestream_id,
    product_id: product_id,
    product_info: product_info,
    timestamp: new Date().toISOString()
  };
  
  // Broadcast sản phẩm được ghim đến tất cả client
  broadcastToLivestream(livestream_id, pinMessage);
  
  console.log(`📌 Sản phẩm ${product_id} được ghim trong livestream ${livestream_id}`);
}

function handleUnpinProduct(ws, data) {
  const { livestream_id } = data;
  
  const unpinMessage = {
    type: 'product_unpinned',
    livestream_id: livestream_id,
    timestamp: new Date().toISOString()
  };
  
  // Broadcast sản phẩm bỏ ghim đến tất cả client
  broadcastToLivestream(livestream_id, unpinMessage);
  
  console.log(`📌 Sản phẩm đã bỏ ghim trong livestream ${livestream_id}`);
}

function handleAddToCart(ws, data) {
  const { livestream_id, user_id, product_id, quantity, price } = data;
  
  const cartMessage = {
    type: 'cart_updated',
    livestream_id: livestream_id,
    user_id: user_id,
    product_id: product_id,
    quantity: quantity,
    price: price,
    action: 'add',
    timestamp: new Date().toISOString()
  };
  
  // Chỉ gửi cho user cụ thể
  ws.send(JSON.stringify(cartMessage));
  
  console.log(`🛒 User ${user_id} thêm sản phẩm ${product_id} vào giỏ hàng livestream ${livestream_id}`);
}

function handleRemoveFromCart(ws, data) {
  const { livestream_id, user_id, product_id } = data;
  
  const cartMessage = {
    type: 'cart_updated',
    livestream_id: livestream_id,
    user_id: user_id,
    product_id: product_id,
    action: 'remove',
    timestamp: new Date().toISOString()
  };
  
  // Chỉ gửi cho user cụ thể
  ws.send(JSON.stringify(cartMessage));
  
  console.log(`🛒 User ${user_id} xóa sản phẩm ${product_id} khỏi giỏ hàng livestream ${livestream_id}`);
}

function handleUpdateCartQuantity(ws, data) {
  const { livestream_id, user_id, product_id, quantity } = data;
  
  const cartMessage = {
    type: 'cart_updated',
    livestream_id: livestream_id,
    user_id: user_id,
    product_id: product_id,
    quantity: quantity,
    action: 'update',
    timestamp: new Date().toISOString()
  };
  
  // Chỉ gửi cho user cụ thể
  ws.send(JSON.stringify(cartMessage));
  
  console.log(`🛒 User ${user_id} cập nhật số lượng sản phẩm ${product_id} trong giỏ hàng livestream ${livestream_id}`);
}

function handleLivestreamStats(ws, data) {
  const { livestream_id, stats } = data;
  
  const statsMessage = {
    type: 'livestream_stats',
    livestream_id: livestream_id,
    stats: stats,
    timestamp: new Date().toISOString()
  };
  
  // Broadcast thống kê đến tất cả client
  broadcastToLivestream(livestream_id, statsMessage);
  
  console.log(`📊 Cập nhật thống kê livestream ${livestream_id}`);
}

function broadcastToLivestream(livestream_id, message, excludeWs = null) {
  if (livestreamRooms[livestream_id]) {
    livestreamRooms[livestream_id].forEach(client => {
      if (client !== excludeWs && client.readyState === WebSocket.OPEN) {
        client.send(JSON.stringify(message));
      }
    });
  }
}

// Forward WebRTC signaling messages between streamer and viewers in the same room
function forwardWebRTCSignal(ws, data) {
  const { livestream_id, type } = data;
  console.log(`🔄 Forwarding ${type} for livestream ${livestream_id}`);
  
  if (!livestream_id) {
    console.log('❌ No livestream_id in WebRTC signal');
    return;
  }

  // Relay to everyone else in the same room
  if (livestreamRooms[livestream_id]) {
    console.log(`📡 Found ${livestreamRooms[livestream_id].length} clients in room ${livestream_id}`);
    livestreamRooms[livestream_id].forEach((client, index) => {
      if (client !== ws && client.readyState === WebSocket.OPEN) {
        console.log(`📤 Sending ${type} to client ${index} (readyState: ${client.readyState})`);
        try {
          client.send(JSON.stringify(data));
        } catch (error) {
          console.log(`❌ Error sending to client ${index}:`, error.message);
        }
      } else {
        console.log(`❌ Client ${index} not ready (readyState: ${client.readyState})`);
      }
    });
  } else {
    console.log(`❌ No room found for livestream ${livestream_id}`);
  }
}

function handleLivestreamStatusUpdate(ws, data) {
  const { livestream_id, status } = data;
  
  // Tìm tất cả viewers của livestream này
  const viewers = Object.values(livestreamClients).filter(client => 
    client.livestream_id === livestream_id && client.type === 'viewer'
  );
  
  // Gửi thông báo status update cho tất cả viewers
  viewers.forEach(viewer => {
    const statusMessage = {
      type: status === 'dang_live' ? 'livestream_started' : 'livestream_stopped',
      livestream_id: livestream_id,
      status: status,
      timestamp: new Date().toISOString()
    };
    
    viewer.ws.send(JSON.stringify(statusMessage));
  });
  
  console.log(`📺 Livestream ${livestream_id} status updated to ${status}, notified ${viewers.length} viewers`);
}

function handleGetLivestreamStatus(ws, data) {
  const { livestream_id } = data;
  
  // Kiểm tra xem livestream có đang live không
  const streamer = Object.values(livestreamClients).find(client => 
    client.livestream_id === livestream_id && client.type === 'streamer'
  );
  
  if (streamer) {
    // Gửi thông báo livestream đã bắt đầu
    const statusMessage = {
      type: 'livestream_started',
      livestream_id: livestream_id,
      status: 'dang_live',
      timestamp: new Date().toISOString()
    };
    
    ws.send(JSON.stringify(statusMessage));
    console.log(`📺 Sent livestream status to viewer for livestream ${livestream_id}`);
  } else {
    console.log(`📺 No active streamer found for livestream ${livestream_id}`);
  }
}
