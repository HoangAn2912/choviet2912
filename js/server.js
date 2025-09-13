const WebSocket = require('ws');
const http = require('http');
const fs = require('fs');
const path = require('path');

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

const wss = new WebSocket.Server({ port: CONFIG.wsPort || 3000 });
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
  });

  ws.on('close', () => {
    if (ws.user_id && clients[ws.user_id]) {
      delete clients[ws.user_id];
      console.log(`🔴 User ${ws.user_id} đã ngắt kết nối`);
    }
  });
});
