# 🎥 HƯỚNG DẪN TRIỂN KHAI HỆ THỐNG LIVESTREAM

## 📋 TỔNG QUAN

Hệ thống livestream đã được phát triển với đầy đủ tính năng:
- ✅ Ghim sản phẩm trong livestream
- ✅ Giỏ hàng realtime
- ✅ Thanh toán VNPay
- ✅ Chat realtime
- ✅ Thống kê livestream
- ✅ Quản lý cho streamer

## 🚀 CÁC BƯỚC TRIỂN KHAI

### Bước 1: Cài đặt Database

1. **Import các bảng mới:**
```sql
-- Chạy file SQL để tạo các bảng cần thiết
mysql -u root -p choviet29 < data/livestream_tables.sql
```

2. **Kiểm tra các bảng đã tạo:**
```sql
SHOW TABLES LIKE 'livestream%';
```

### Bước 2: Cài đặt Dependencies

1. **Cài đặt Node.js dependencies:**
```bash
npm install ws
```

2. **Kiểm tra package.json:**
```json
{
  "dependencies": {
    "ws": "^8.18.2"
  }
}
```

### Bước 3: Khởi động WebSocket Server

**Windows:**
```bash
# Chạy file batch
start_livestream_server.bat

# Hoặc chạy trực tiếp
node js/livestream-websocket.js
```

**Linux/Mac:**
```bash
# Cấp quyền thực thi
chmod +x start_livestream_server.sh

# Chạy script
./start_livestream_server.sh

# Hoặc chạy trực tiếp
node js/livestream-websocket.js
```

### Bước 4: Cấu hình XAMPP

1. **Khởi động XAMPP:**
   - Apache
   - MySQL

2. **Kiểm tra kết nối database:**
   - Truy cập: `http://localhost/phpmyadmin`
   - Import file `data/livestream_tables.sql`

### Bước 5: Cập nhật Routes

Đã cập nhật `index.php` để hỗ trợ:
- `index.php?livestream` - Danh sách livestream
- `index.php?livestream&id=1` - Xem livestream chi tiết
- `index.php?streamer&id=1` - Panel quản lý streamer

## 🔧 CẤU HÌNH

### 1. WebSocket Server
- **Port:** 3000 (mặc định)
- **URL:** `ws://localhost:3000`
- **Có thể thay đổi trong:** `js/livestream-websocket.js`

### 2. VNPay Integration
- **Đã tích hợp sẵn** với VNPay
- **Cấu hình trong:** `controller/vnpay/vnpay_config.php`
- **Hỗ trợ tất cả phương thức thanh toán VNPay**

### 3. Database Tables
```sql
-- Các bảng chính:
livestream_products      -- Sản phẩm trong livestream
livestream_cart_items    -- Giỏ hàng livestream
livestream_orders        -- Đơn hàng từ livestream
livestream_order_items   -- Chi tiết đơn hàng
livestream_interactions  -- Tương tác người dùng
livestream_viewers       -- Người xem livestream
livestream_messages      -- Tin nhắn chat
```

## 🎯 CÁC TÍNH NĂNG CHÍNH

### 1. **Cho Streamer (Người bán)**
- ✅ Tạo livestream
- ✅ Thêm sản phẩm vào live
- ✅ Ghim sản phẩm đang bán
- ✅ Xem thống kê realtime
- ✅ Quản lý chat
- ✅ Bắt đầu/kết thúc live

### 2. **Cho Viewer (Người xem)**
- ✅ Xem danh sách livestream
- ✅ Vào phòng live
- ✅ Chat realtime
- ✅ Xem sản phẩm được ghim
- ✅ Thêm vào giỏ hàng
- ✅ Thanh toán VNPay

### 3. **Tính năng Realtime**
- ✅ WebSocket cho chat
- ✅ Cập nhật số lượng người xem
- ✅ Thông báo sản phẩm được ghim
- ✅ Cập nhật giỏ hàng realtime

## 📱 GIAO DIỆN

### 1. **Trang Livestream Chi Tiết** (`view/livestream_detail.php`)
- Video player (placeholder)
- Chat realtime
- Giỏ hàng live
- Sản phẩm được ghim
- Thông tin streamer

### 2. **Panel Quản Lý Streamer** (`view/streamer_panel.php`)
- Quản lý sản phẩm
- Thống kê realtime
- Chat moderation
- Cài đặt livestream

### 3. **API Endpoints** (`api/livestream-api.php`)
- `get_livestreams` - Lấy danh sách livestream
- `get_livestream` - Lấy thông tin livestream
- `add_to_cart` - Thêm vào giỏ hàng
- `checkout` - Thanh toán
- `pin_product` - Ghim sản phẩm
- `send_chat_message` - Gửi tin nhắn

## 🔄 LUỒNG HOẠT ĐỘNG

### **Luồng Streamer:**
1. Tạo livestream → Thêm sản phẩm → Bắt đầu live
2. Ghim sản phẩm → Tương tác với viewers
3. Xem thống kê → Kết thúc live

### **Luồng Viewer:**
1. Xem danh sách live → Vào phòng live
2. Chat với streamer → Xem sản phẩm ghim
3. Thêm vào giỏ → Thanh toán VNPay

## 🛠️ TROUBLESHOOTING

### 1. **WebSocket không kết nối được**
```bash
# Kiểm tra port 3000 có bị chiếm không
netstat -an | findstr :3000

# Thay đổi port trong js/livestream-websocket.js
const wss = new WebSocket.Server({ port: 3001 });
```

### 2. **Database connection error**
```php
// Kiểm tra file model/mConnect.php
$con = mysqli_connect("localhost", "root", "", "choviet29");
```

### 3. **VNPay không hoạt động**
- Kiểm tra cấu hình trong `controller/vnpay/vnpay_config.php`
- Đảm bảo VNPay sandbox đang hoạt động

### 4. **Chat không realtime**
- Kiểm tra WebSocket server đang chạy
- Kiểm tra console browser có lỗi không
- Kiểm tra firewall có chặn port 3000 không

## 📊 MONITORING

### 1. **Logs WebSocket Server**
```bash
# Xem logs realtime
node js/livestream-websocket.js

# Hoặc chạy background
nohup node js/livestream-websocket.js > livestream.log 2>&1 &
```

### 2. **Database Monitoring**
```sql
-- Xem số lượng livestream
SELECT COUNT(*) FROM livestream;

-- Xem đơn hàng mới nhất
SELECT * FROM livestream_orders ORDER BY created_at DESC LIMIT 10;

-- Xem thống kê viewers
SELECT livestream_id, COUNT(*) as viewers 
FROM livestream_viewers 
GROUP BY livestream_id;
```

## 🚀 DEPLOYMENT PRODUCTION

### 1. **Cấu hình Production**
```javascript
// js/livestream-websocket.js
const CONFIG = {
    hostname: 'your-domain.com',
    port: 3000,
    basePath: '/choviet29'
};
```

### 2. **SSL/HTTPS**
```javascript
// Sử dụng WSS cho HTTPS
const wss = new WebSocket.Server({ 
    port: 3000,
    cert: fs.readFileSync('path/to/cert.pem'),
    key: fs.readFileSync('path/to/key.pem')
});
```

### 3. **PM2 Process Manager**
```bash
# Cài đặt PM2
npm install -g pm2

# Chạy WebSocket server với PM2
pm2 start js/livestream-websocket.js --name "livestream-ws"

# Auto restart
pm2 startup
pm2 save
```

## 📈 TÍNH NĂNG MỞ RỘNG

### 1. **Video Streaming**
- Tích hợp OBS Studio
- RTMP streaming
- HLS/DASH support

### 2. **Advanced Analytics**
- Heatmap viewers
- Conversion tracking
- A/B testing

### 3. **Mobile App**
- React Native
- Push notifications
- Offline support

## ✅ CHECKLIST TRIỂN KHAI

- [ ] Database đã import thành công
- [ ] WebSocket server đang chạy
- [ ] XAMPP Apache + MySQL hoạt động
- [ ] VNPay cấu hình đúng
- [ ] Test tạo livestream
- [ ] Test chat realtime
- [ ] Test thanh toán
- [ ] Test trên mobile

## 🆘 HỖ TRỢ

Nếu gặp vấn đề, hãy kiểm tra:
1. **Console browser** - F12 → Console
2. **Network tab** - Xem API calls
3. **WebSocket connection** - Kiểm tra kết nối
4. **Database logs** - Xem lỗi MySQL
5. **Server logs** - Xem logs WebSocket

---

**🎉 Chúc mừng! Hệ thống livestream đã sẵn sàng sử dụng!**




