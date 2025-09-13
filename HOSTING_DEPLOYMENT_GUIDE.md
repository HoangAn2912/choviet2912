# Hướng dẫn triển khai WebSocket Chat lên Hosting

## Vấn đề hiện tại
Chức năng nhắn tin realtime không hoạt động vì:
1. WebSocket server (`server.js`) chưa được chạy trên hosting
2. URL WebSocket cố định `localhost:3000` không hoạt động trên domain thật
3. Cấu hình server cố định cho môi trường local

## Giải pháp đã cập nhật

### 1. Cấu hình động đã được cập nhật
- ✅ `config/server_config.js`: Tự động phát hiện môi trường (local/hosting)
- ✅ `js/chat.js`: Tự động phát hiện WebSocket URL dựa trên domain hiện tại

### 2. Các loại hosting và cách triển khai

#### A. Shared Hosting (không hỗ trợ Node.js)
**Vấn đề**: Hầu hết shared hosting không cho phép chạy Node.js server.

**Giải pháp**:
1. **Chuyển sang VPS/Cloud Server** (khuyến nghị)
2. **Sử dụng dịch vụ WebSocket bên ngoài** như:
   - Pusher.com
   - Socket.io hosting
   - Firebase Realtime Database
3. **Tắt chức năng realtime** tạm thời (chỉ dùng AJAX polling)

#### B. VPS/Cloud Server (có hỗ trợ Node.js)
**Các bước triển khai**:

1. **Cài đặt Node.js trên server**:
```bash
# Ubuntu/Debian
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# CentOS/RHEL
curl -fsSL https://rpm.nodesource.com/setup_18.x | sudo bash -
sudo yum install -y nodejs
```

2. **Upload code lên server**:
```bash
# Upload toàn bộ project vào thư mục web (ví dụ: /var/www/html)
# Hoặc sử dụng Git
git clone your-repo-url
cd your-project
```

3. **Cài đặt dependencies**:
```bash
npm install
```

4. **Cấu hình environment variables**:
```bash
# Tạo file .env
export NODE_ENV=production
export HOSTING=true
export HOSTNAME=your-domain.com
export PORT=80
export WS_PORT=3000
export BASE_PATH=""
export PROJECT_ROOT="/var/www/html/your-project"
export CHAT_PATH="/var/www/html/your-project/chat"
```

5. **Chạy server**:
```bash
# Chạy trực tiếp
node js/server.js

# Hoặc sử dụng PM2 (khuyến nghị cho production)
npm install -g pm2
pm2 start js/server.js --name "websocket-chat"
pm2 startup
pm2 save
```

6. **Cấu hình firewall**:
```bash
# Mở port 3000 cho WebSocket
sudo ufw allow 3000
```

7. **Cấu hình reverse proxy (Nginx)**:
```nginx
# /etc/nginx/sites-available/your-domain
server {
    listen 80;
    server_name your-domain.com;
    
    # PHP files
    location / {
        root /var/www/html/your-project;
        index index.php index.html;
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # WebSocket proxy
    location /ws {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

#### C. Hosting có hỗ trợ Node.js (Heroku, Vercel, Railway, etc.)

1. **Heroku**:
```bash
# Tạo Procfile
echo "web: node js/server.js" > Procfile

# Deploy
git add .
git commit -m "Deploy to Heroku"
git push heroku main
```

2. **Vercel**:
```bash
# Tạo vercel.json
{
  "version": 2,
  "builds": [
    {
      "src": "js/server.js",
      "use": "@vercel/node"
    }
  ],
  "routes": [
    {
      "src": "/ws",
      "dest": "js/server.js"
    }
  ]
}
```

## Cấu hình cho từng môi trường

### Development (Local)
```bash
# Không cần cấu hình gì thêm
# Tự động sử dụng localhost:3000
```

### Production (Hosting)
```bash
# Set environment variables
export NODE_ENV=production
export HOSTING=true
export HOSTNAME=your-domain.com
export WS_PORT=3000
```

## Kiểm tra sau khi triển khai

1. **Kiểm tra WebSocket connection**:
```javascript
// Mở Developer Tools > Console
// Chạy lệnh này để test
const testSocket = new WebSocket('ws://your-domain.com:3000');
testSocket.onopen = () => console.log('✅ WebSocket connected');
testSocket.onerror = (err) => console.log('❌ WebSocket error:', err);
```

2. **Kiểm tra chat functionality**:
- Mở 2 tab browser khác nhau
- Đăng nhập 2 user khác nhau
- Thử gửi tin nhắn realtime

## Troubleshooting

### Lỗi "WebSocket connection failed"
1. Kiểm tra port 3000 có được mở không
2. Kiểm tra firewall settings
3. Kiểm tra server.js có đang chạy không

### Lỗi "Cannot find module 'ws'"
```bash
npm install ws
```

### Lỗi "Permission denied"
```bash
# Cấp quyền cho thư mục chat
chmod 755 chat/
chmod 644 chat/*.json
```

## Khuyến nghị

1. **Sử dụng PM2** để quản lý Node.js process
2. **Cấu hình SSL** cho WebSocket (wss://)
3. **Monitor logs** thường xuyên
4. **Backup database** và file chat định kỳ

## Alternative: Sử dụng dịch vụ WebSocket bên ngoài

Nếu không thể chạy Node.js trên hosting, có thể sử dụng:
- **Pusher.com** (free tier: 200k messages/day)
- **Socket.io** với hosting service
- **Firebase Realtime Database**
- **Supabase Realtime**

Cần hỗ trợ thêm về việc tích hợp các dịch vụ này không?


