const WebSocket = require('ws');
const http = require('http');
const fs = require('fs');
const path = require('path');

let livestreamClients = {};
let livestreamRooms = {};

let CONFIG = {
  hostname: process.env.HOSTNAME || 'localhost',
  port: process.env.PORT || 8080,
  basePath: process.env.BASE_PATH || ''
};

console.log("Đang chạy đúng file server.js JSON");
console.log("Thư mục làm việc hiện tại:", process.cwd());
console.log("Đã tải CONFIG:", CONFIG);

try {
  const configPath = path.join(__dirname, '../config/server_config.js');
  if (fs.existsSync(configPath)) {
    const fileConfig = require(configPath);
    CONFIG = { ...CONFIG, ...fileConfig };
    console.log('Đã load config từ file:', configPath);
  }
} catch (err) {
  console.log('Không thể load config file, sử dụng config mặc định');
}

console.log('Config hiện tại:', CONFIG);

const https = require('https');

let server;
const port = CONFIG.wsPort || 3000;

// SSL paths - lấy từ config hoặc fallback theo domain
const sslDomain = CONFIG.sslDomain || CONFIG.wsHost || 'choviet.site';
const sslKeyPath = CONFIG.sslKeyPath || `/etc/letsencrypt/live/${sslDomain}/privkey.pem`;
const sslCertPath = CONFIG.sslCertPath || `/etc/letsencrypt/live/${sslDomain}/fullchain.pem`;

if (fs.existsSync(sslKeyPath) && fs.existsSync(sslCertPath)) {
  try {
    const privateKey = fs.readFileSync(sslKeyPath, 'utf8');
    const certificate = fs.readFileSync(sslCertPath, 'utf8');
    const credentials = { key: privateKey, cert: certificate };
    server = https.createServer(credentials);
    console.log('Running in SSL mode (WSS)');
  } catch (e) {
    console.error('Error loading SSL certs:', e);
    server = http.createServer();
    console.log('Fallback to non-SSL mode (WS)');
  }
} else {
  server = http.createServer();
  console.log('Running in non-SSL mode (WS) - Certs not found');
}

server.listen(port);
const wss = new WebSocket.Server({ server });

console.log(`WebSocket server đang chạy trên port ${CONFIG.wsPort || 3000}`);
console.log(`WebSocket server sẵn sàng nhận kết nối`);
let clients = {};

wss.on('connection', function connection(ws) {
  ws.on('message', function incoming(message) {
    const data = JSON.parse(message);

    if (data.type === 'register') {
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
        console.log(`User ${data.user_id} đã kết nối`);
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

      let chatFolderPath;
      if (CONFIG.chatPath) {
        chatFolderPath = CONFIG.chatPath;
      } else {
        const currentDir = process.cwd();
        chatFolderPath = path.join(currentDir, "chat");
      }

      const filePath = path.join(chatFolderPath, fileName);

      console.log("Đường dẫn thư mục chat:", chatFolderPath);
      console.log("Đường dẫn file đầy đủ:", filePath);

      if (!fs.existsSync(chatFolderPath)) {
        fs.mkdirSync(chatFolderPath, { recursive: true });
      }

      if (!fs.existsSync(filePath)) {
        try {
          fs.writeFileSync(filePath, "[]");
          console.log("Đã tạo file mới:", filePath);

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
            console.log('Đã lưu tên file vào DB:', fileName);
          });
          req2.on('error', error => console.error("Lỗi lưu tên file:", error));
          req2.write(postFileName);
          req2.end();

        } catch (err) {
          console.error("Lỗi tạo file:", err);
        }
      }

      let messages = [];
      try {
        const fileContent = fs.readFileSync(filePath, 'utf-8');
        messages = JSON.parse(fileContent);
      } catch (err) {
        console.error("Lỗi đọc file JSON:", err);
      }

      messages.push({ from, to, content: (noi_dung || content), timestamp });

      fs.writeFile(filePath, JSON.stringify(messages, null, 2), err => {
        if (err) console.error("Lỗi ghi file JSON:", err);
        else console.log("Đã lưu tin nhắn vào file:", fileName);
      });

      const socketMessage = JSON.stringify({ type: 'message', from, to, content: (noi_dung || content), timestamp });
      if (clients[to]) clients[to].send(socketMessage);
      if (clients[from]) clients[from].send(socketMessage);

      try {
        const unreadFile = path.join(chatFolderPath, `unread_${to}.json`);
        let unread = {};
        if (fs.existsSync(unreadFile)) {
          unread = JSON.parse(fs.readFileSync(unreadFile, 'utf-8') || '{}');
        }
        unread[from] = (unread[from] || 0) + 1;
        fs.writeFileSync(unreadFile, JSON.stringify(unread, null, 2));
        if (clients[to]) {
          clients[to].send(JSON.stringify({ type: 'unread', from, to, count: unread[from] }));
        }
      } catch (e) {
        console.error('Lỗi cập nhật unread:', e);
      }

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
        console.log('Gửi API chat-api.php:', res.statusCode);
        res.on('data', chunk => console.log('Nội dung:', chunk.toString()));
      });
      req.on('error', error => console.error("Lỗi gọi API PHP:", error));
      req.write(postData);
      req.end();
    }

    if (data.type === 'mark_read') {
      const { from, to } = data;
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
        console.error('Lỗi mark_read:', e);
      }
      return;
    }

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
      console.log('Xử lý message livestream:', data.type, 'cho livestream:', data.livestream_id);
      handleLivestreamMessage(ws, data);
      return;
    }
  });

  ws.on('close', () => {
    if (ws.user_id && clients[ws.user_id]) {
      delete clients[ws.user_id];
      console.log(`User ${ws.user_id} đã ngắt kết nối`);
    }

    Object.keys(livestreamRooms).forEach(roomId => {
      if (livestreamRooms[roomId]) {
        const index = livestreamRooms[roomId].indexOf(ws);
        if (index > -1) {
          livestreamRooms[roomId].splice(index, 1);

          const newCount = livestreamRooms[roomId].length;
          broadcastToLivestream(roomId, {
            type: 'viewers_count_update',
            livestream_id: roomId,
            viewers_count: newCount
          });

          console.log(`Livestream ${roomId} viewers count updated to ${newCount}`);
        }
      }
    });

    Object.keys(livestreamClients).forEach(clientId => {
      if (livestreamClients[clientId].ws === ws) {
        delete livestreamClients[clientId];
      }
    });
  });
});

// Xử lý định tuyến các message liên quan đến livestream
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
    case 'livestream_like':
      handleLivestreamLike(ws, data);
      break;
    case 'livestream_like_broadcast':
      const { livestream_id } = data;
      if (livestream_id) {
        console.log('Đang broadcast cập nhật số lượt thích cho livestream:', livestream_id);
        fetchLikeCount(livestream_id);
      }
      break;
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
    case 'order_created':
      handleOrderCreated(ws, data);
      break;
    default:
      console.log('Loại message livestream không xác định:', data.type);
  }
}

// Xử lý khi user tham gia vào phòng livestream
function joinLivestream(ws, data) {
  const { livestream_id, user_id, user_type } = data;

  if (!livestreamRooms[livestream_id]) {
    livestreamRooms[livestream_id] = [];
  }

  const alreadyInRoom = livestreamRooms[livestream_id].includes(ws);

  if (!alreadyInRoom) {
    livestreamRooms[livestream_id].push(ws);
  }

  ws.livestream_id = livestream_id;
  ws.user_id = user_id;
  ws.user_type = user_type || 'viewer';

  const clientId = `${user_id}_${livestream_id}`;
  livestreamClients[clientId] = {
    ws: ws,
    livestream_id: livestream_id,
    user_id: user_id,
    type: user_type || 'viewer'
  };

  const currentViewersCount = livestreamRooms[livestream_id].length;
  console.log(`User ${user_id} (${user_type || 'viewer'}) đã tham gia livestream ${livestream_id}. Tổng viewers: ${currentViewersCount}`);

  ws.send(JSON.stringify({
    type: 'livestream_joined',
    livestream_id: livestream_id,
    viewers_count: currentViewersCount
  }));

  broadcastToLivestream(livestream_id, {
    type: 'viewers_count_update',
    livestream_id: livestream_id,
    viewers_count: currentViewersCount
  });

  if (!alreadyInRoom) {
    broadcastToLivestream(livestream_id, {
      type: 'viewer_joined',
      user_id: user_id,
      viewers_count: currentViewersCount
    }, ws);
  }
}

// Xử lý khi user rời khỏi phòng livestream
function leaveLivestream(ws, data) {
  const { livestream_id } = data;

  if (livestreamRooms[livestream_id]) {
    const index = livestreamRooms[livestream_id].indexOf(ws);
    if (index > -1) {
      livestreamRooms[livestream_id].splice(index, 1);
    }
  }

  const newCount = livestreamRooms[livestream_id] ? livestreamRooms[livestream_id].length : 0;
  console.log(`User đã rời livestream ${livestream_id}. Còn lại: ${newCount} viewers`);

  broadcastToLivestream(livestream_id, {
    type: 'viewers_count_update',
    livestream_id: livestream_id,
    viewers_count: newCount
  });

  broadcastToLivestream(livestream_id, {
    type: 'viewer_left',
    viewers_count: newCount
  }, ws);
}

// Xử lý tin nhắn chat trong phòng livestream
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

  broadcastToLivestream(livestream_id, chatMessage);

  console.log(`Chat trong livestream ${livestream_id}: ${username}: ${message}`);
}

// Xử lý ghim sản phẩm trong livestream để hiển thị nổi bật
function handlePinProduct(ws, data) {
  const { livestream_id, product_id, product_info } = data;

  const pinMessage = {
    type: 'product_pinned',
    livestream_id: livestream_id,
    product_id: product_id,
    product_info: product_info,
    timestamp: new Date().toISOString()
  };

  broadcastToLivestream(livestream_id, pinMessage);

  console.log(`Sản phẩm ${product_id} được ghim trong livestream ${livestream_id}`);
}

// Xử lý bỏ ghim sản phẩm trong livestream
function handleUnpinProduct(ws, data) {
  const { livestream_id } = data;

  const unpinMessage = {
    type: 'product_unpinned',
    livestream_id: livestream_id,
    timestamp: new Date().toISOString()
  };

  broadcastToLivestream(livestream_id, unpinMessage);

  console.log(`Sản phẩm đã bỏ ghim trong livestream ${livestream_id}`);
}

// Xử lý thêm sản phẩm vào giỏ hàng livestream
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

  ws.send(JSON.stringify(cartMessage));

  console.log(`User ${user_id} thêm sản phẩm ${product_id} vào giỏ hàng livestream ${livestream_id}`);
}

// Xử lý xóa sản phẩm khỏi giỏ hàng livestream
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

  ws.send(JSON.stringify(cartMessage));

  console.log(`User ${user_id} xóa sản phẩm ${product_id} khỏi giỏ hàng livestream ${livestream_id}`);
}

// Xử lý cập nhật số lượng sản phẩm trong giỏ hàng livestream
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

  ws.send(JSON.stringify(cartMessage));

  console.log(`User ${user_id} cập nhật số lượng sản phẩm ${product_id} trong giỏ hàng livestream ${livestream_id}`);
}

// Xử lý broadcast thống kê livestream đến tất cả clients
function handleLivestreamStats(ws, data) {
  const { livestream_id, stats } = data;

  const statsMessage = {
    type: 'livestream_stats',
    livestream_id: livestream_id,
    stats: stats,
    timestamp: new Date().toISOString()
  };

  broadcastToLivestream(livestream_id, statsMessage);

  console.log(`Cập nhật thống kê livestream ${livestream_id}`);
}

// Xử lý khi user thích livestream, ghi vào database và broadcast số lượt thích
function handleLivestreamLike(ws, data) {
  const { livestream_id, user_id } = data;

  if (!livestream_id || !user_id) {
    console.log('Thiếu livestream_id hoặc user_id cho like', { livestream_id, user_id });
    return;
  }

  console.log(`User ${user_id} đã thích livestream ${livestream_id}`);
  console.log(`Gọi API: http://${CONFIG.hostname}:${CONFIG.port}${CONFIG.basePath}/api/livestream-api.php`);

  const querystring = require('querystring');
  const postData = querystring.stringify({
    action: 'record_interaction',
    livestream_id: livestream_id,
    user_id: user_id,
    action_type: 'like'
  });

  const apiPath = CONFIG.basePath + '/api/livestream-api.php';
  const options = {
    hostname: CONFIG.hostname,
    port: CONFIG.port,
    path: apiPath,
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'Content-Length': Buffer.byteLength(postData)
    }
  };

  console.log(`POST Request đến: http://${options.hostname}:${options.port}${options.path}`);
  console.log(`Dữ liệu POST:`, postData);

  const req = http.request(options, (res) => {
    console.log(`Trạng thái response: ${res.statusCode} ${res.statusMessage}`);
    let responseData = '';
    res.on('data', (chunk) => {
      responseData += chunk;
    });
    res.on('end', () => {
      console.log(`Dữ liệu response:`, responseData);
      try {
        const result = JSON.parse(responseData);
        if (result.success) {
          console.log('Đã ghi nhận lượt thích thành công');
          fetchLikeCount(livestream_id);
        } else {
          console.error('Lỗi ghi nhận lượt thích:', result.message);
        }
      } catch (e) {
        console.error('Lỗi phân tích response like:', e);
        console.error('Response thô:', responseData);
      }
    });
  });

  req.on('error', (error) => {
    console.error('Lỗi gọi API like:', error);
    console.error('Chi tiết lỗi:', {
      code: error.code,
      message: error.message,
      hostname: options.hostname,
      port: options.port,
      path: options.path
    });
  });

  req.write(postData);
  req.end();
}

// Lấy số lượt thích từ API và broadcast cho tất cả clients
function fetchLikeCount(livestream_id) {
  const apiPath = CONFIG.basePath + '/api/livestream-api.php?action=get_realtime_stats&livestream_id=' + livestream_id;
  console.log(`Đang lấy số lượt thích từ: http://${CONFIG.hostname}:${CONFIG.port}${apiPath}`);

  const req = http.get({
    hostname: CONFIG.hostname,
    port: CONFIG.port,
    path: apiPath
  }, (res) => {
    console.log(`Trạng thái response số lượt thích: ${res.statusCode}`);
    let responseData = '';
    res.on('data', (chunk) => {
      responseData += chunk;
    });
    res.on('end', () => {
      console.log(`Dữ liệu response số lượt thích:`, responseData);
      try {
        const result = JSON.parse(responseData);
        if (result.success && result.stats) {
          const likeCount = result.stats.like_count || 0;

          console.log(`Số lượt thích hiện tại: ${likeCount} cho livestream ${livestream_id}`);

          broadcastToLivestream(livestream_id, {
            type: 'livestream_like_count',
            livestream_id: livestream_id,
            count: likeCount,
            timestamp: new Date().toISOString()
          });

          console.log(`Đã broadcast số lượt thích: ${likeCount} cho livestream ${livestream_id}`);
        } else {
          console.error('Lỗi lấy số lượt thích:', result.message || 'Lỗi không xác định');
          console.error('Kết quả:', result);
        }
      } catch (e) {
        console.error('Lỗi phân tích response số lượt thích:', e);
        console.error('Response thô:', responseData);
      }
    });
  });

  req.on('error', (error) => {
    console.error('Lỗi lấy số lượt thích:', error);
    console.error('Chi tiết lỗi:', {
      code: error.code,
      message: error.message,
      hostname: CONFIG.hostname,
      port: CONFIG.port,
      path: apiPath
    });
  });
}

// Xử lý khi có đơn hàng mới từ livestream, broadcast thông báo và lấy thống kê mới
function handleOrderCreated(ws, data) {
  const { livestream_id, order_id, order_code, total_amount } = data;

  if (!livestream_id) {
    console.log('Thiếu livestream_id cho order_created');
    return;
  }

  console.log(`Đơn hàng đã tạo: ${order_code || order_id} cho livestream ${livestream_id}, số tiền: ${total_amount}`);

  broadcastToLivestream(livestream_id, {
    type: 'order_created',
    livestream_id: livestream_id,
    order_id: order_id,
    order_code: order_code || '',
    total_amount: total_amount || 0,
    timestamp: new Date().toISOString()
  });

  setTimeout(() => {
    fetchLivestreamStats(livestream_id);
  }, 500);
}

// Lấy thống kê chi tiết livestream từ API và broadcast cho tất cả clients
function fetchLivestreamStats(livestream_id) {
  const apiPath = CONFIG.basePath + '/api/livestream-api.php?action=get_realtime_stats&livestream_id=' + livestream_id;
  console.log(`Đang lấy thống kê livestream từ: http://${CONFIG.hostname}:${CONFIG.port}${apiPath}`);

  const req = http.get({
    hostname: CONFIG.hostname,
    port: CONFIG.port,
    path: apiPath
  }, (res) => {
    let responseData = '';
    res.on('data', (chunk) => {
      responseData += chunk;
    });
    res.on('end', () => {
      try {
        const result = JSON.parse(responseData);
        if (result.success && result.stats) {
          const stats = result.stats;

          console.log(`Thống kê livestream:`, stats);

          broadcastToLivestream(livestream_id, {
            type: 'livestream_stats_update',
            livestream_id: livestream_id,
            stats: {
              order_count: stats.order_count || 0,
              total_revenue: stats.total_revenue || 0,
              like_count: stats.like_count || 0,
              current_viewers: stats.current_viewers || 0
            },
            timestamp: new Date().toISOString()
          });

          console.log(`Đã broadcast cập nhật thống kê cho livestream ${livestream_id}`);
        }
      } catch (e) {
        console.error('Lỗi phân tích response thống kê:', e);
      }
    });
  });

  req.on('error', (error) => {
    console.error('Lỗi lấy thống kê:', error);
  });
}

// Gửi message đến tất cả clients trong phòng livestream (có thể loại trừ 1 client)
function broadcastToLivestream(livestream_id, message, excludeWs = null) {
  if (livestreamRooms[livestream_id]) {
    let sentCount = 0;
    livestreamRooms[livestream_id].forEach(client => {
      if (client !== excludeWs && client.readyState === WebSocket.OPEN) {
        try {
          client.send(JSON.stringify(message));
          sentCount++;
        } catch (error) {
          console.error('Error sending message to client:', error);
        }
      }
    });
    if (sentCount > 0) {
      console.log(`Đã broadcast "${message.type}" đến ${sentCount} clients trong livestream ${livestream_id}`);
    }
  } else {
    console.log(`Không có clients trong phòng livestream ${livestream_id}`);
  }
}

// Chuyển tiếp WebRTC signaling (offer, answer, ICE) giữa streamer và viewers
function forwardWebRTCSignal(ws, data) {
  const { livestream_id, type } = data;
  console.log(`Đang chuyển tiếp ${type} cho livestream ${livestream_id}`);

  if (!livestream_id) {
    console.log('Không có livestream_id trong WebRTC signal');
    return;
  }

  if (livestreamRooms[livestream_id]) {
    console.log(`Tìm thấy ${livestreamRooms[livestream_id].length} clients trong phòng ${livestream_id}`);
    livestreamRooms[livestream_id].forEach((client, index) => {
      if (client !== ws && client.readyState === WebSocket.OPEN) {
        console.log(`Đang gửi ${type} đến client ${index} (readyState: ${client.readyState})`);
        try {
          client.send(JSON.stringify(data));
        } catch (error) {
          console.log(`Lỗi gửi đến client ${index}:`, error.message);
        }
      } else {
        console.log(`Client ${index} chưa sẵn sàng (readyState: ${client.readyState})`);
      }
    });
  } else {
    console.log(`Không tìm thấy phòng cho livestream ${livestream_id}`);
  }
}

// Xử lý cập nhật trạng thái livestream (bắt đầu/kết thúc) và thông báo cho viewers
function handleLivestreamStatusUpdate(ws, data) {
  const { livestream_id, status } = data;

  const viewers = Object.values(livestreamClients).filter(client =>
    client.livestream_id === livestream_id && client.type === 'viewer'
  );

  viewers.forEach(viewer => {
    const statusMessage = {
      type: status === 'dang_live' ? 'livestream_started' : 'livestream_stopped',
      livestream_id: livestream_id,
      status: status,
      timestamp: new Date().toISOString()
    };

    viewer.ws.send(JSON.stringify(statusMessage));
  });

  console.log(`Trạng thái livestream ${livestream_id} đã cập nhật thành ${status}, đã thông báo cho ${viewers.length} viewers`);
}

// Kiểm tra và gửi trạng thái hiện tại của livestream cho viewer
function handleGetLivestreamStatus(ws, data) {
  const { livestream_id } = data;

  const streamer = Object.values(livestreamClients).find(client =>
    client.livestream_id === livestream_id && client.type === 'streamer'
  );

  if (streamer) {
    const statusMessage = {
      type: 'livestream_started',
      livestream_id: livestream_id,
      status: 'dang_live',
      timestamp: new Date().toISOString()
    };

    ws.send(JSON.stringify(statusMessage));
    console.log(`Đã gửi trạng thái livestream cho viewer của livestream ${livestream_id}`);
  } else {
    console.log(`Không tìm thấy streamer đang hoạt động cho livestream ${livestream_id}`);
  }
}
