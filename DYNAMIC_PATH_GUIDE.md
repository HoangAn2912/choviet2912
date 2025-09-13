# 🚀 Hướng dẫn sử dụng Dynamic Path System

## 📋 Tổng quan
Hệ thống đã được cập nhật để sử dụng đường dẫn động thay vì hardcode tên folder. Điều này giúp dự án có thể chạy trên bất kỳ folder nào mà không cần sửa code.

## 🔧 Các file đã được cập nhật

### 1. **Helper Functions** (`helpers/url_helper.php`)
- `getBaseUrl()`: Lấy base URL động
- `getBasePath()`: Lấy base path cho assets

### 2. **Config File** (`config/path_config.php`)
- `getCurrentFolderName()`: Lấy tên folder hiện tại
- `getDynamicBasePath()`: Lấy base path động
- `getDynamicBaseUrl()`: Lấy base URL động
- `getNodeServerConfig()`: Cấu hình cho Node.js server

### 3. **JavaScript Files**
- `js/chat.js`: Sử dụng `window.location.pathname` để lấy base path
- `js/server.js`: Sử dụng CONFIG object và environment variables

### 4. **PHP Files** (Tất cả đã được cập nhật)
- `view/profile/index.php`: Sử dụng `getBaseUrl()` và `getBasePath()`
- `view/admin.php`: Sử dụng `getBasePath()` cho tất cả assets
- `view/duyetnaptien.php`: Sử dụng `getBasePath()` cho CSS, JS, images
- `view/kdbaidang-detail.php`: Sử dụng `getBasePath()` cho CSS, images, links
- `view/kdbaidang-table.php`: Sử dụng `getBasePath()` cho tất cả đường dẫn
- `view/info-update.php`: Sử dụng `getBasePath()` cho upload, CSS, images
- `view/info-insert.php`: Sử dụng `getBasePath()` cho upload, CSS, links
- `view/info-admin.php`: Sử dụng `getBasePath()` cho upload, CSS, images
- `view/qldoanhthu.php`: Sử dụng `getBasePath()` cho links
- `controller/cLoginLogout.php`: Sử dụng `getBaseUrl()`
- `controller/vnpay/vnpay_config.php`: Sử dụng `getBaseUrl()` cho return URL

## 🎯 Cách sử dụng

### **Trong PHP:**
```php
<?php
require_once 'helpers/url_helper.php';

// Lấy base URL
$baseUrl = getBaseUrl();

// Lấy base path cho assets
$basePath = getBasePath();

// Sử dụng trong HTML
echo '<link rel="stylesheet" href="' . $basePath . '/css/style.css">';
echo '<img src="' . $basePath . '/img/logo.png">';
?>
```

### **Trong JavaScript:**
```javascript
// Tự động lấy base path từ URL hiện tại
const basePath = window.location.pathname.replace(/\/[^\/]*$/, '');

// Sử dụng trong fetch
fetch(`${basePath}/api/endpoint.php`)
```

### **Trong Node.js Server:**
```javascript
// Cấu hình trong js/server.js
const CONFIG = {
  hostname: 'localhost',
  port: 8080,
  basePath: '/choviet29' // Thay đổi theo folder
};

// Sử dụng
path: CONFIG.basePath + '/api/endpoint.php'
```

## 🔄 Cách thay đổi folder

### **Local Development:**
1. Copy toàn bộ project vào folder mới (ví dụ: `myproject`)
2. Cập nhật `js/server.js`:
   ```javascript
   const CONFIG = {
     basePath: '/myproject' // Thay đổi tên folder
   };
   ```
3. Khởi động lại Node.js server

### **Production/Hosting:**
1. Upload project lên hosting
2. Cập nhật `js/server.js` với domain và path phù hợp
3. Cấu hình web server (Apache/Nginx) nếu cần

## ⚠️ Lưu ý quan trọng

1. **Node.js Server**: Cần cập nhật `basePath` trong `js/server.js` khi thay đổi folder
2. **Web Server Port**: Đảm bảo port 8080 và 3000 không bị conflict
3. **File Permissions**: Đảm bảo thư mục `chat/` có quyền ghi
4. **HTTPS**: Hệ thống tự động detect HTTP/HTTPS

## 🧪 Test

### **Test trên folder khác:**
1. Copy project vào folder mới
2. Cập nhật CONFIG trong `js/server.js`
3. Khởi động server
4. Test chat functionality

### **Test trên hosting:**
1. Upload project
2. Cập nhật CONFIG với domain thực
3. Test tất cả chức năng

## 📞 Hỗ trợ

Nếu gặp vấn đề, kiểm tra:
- Console browser có lỗi gì không
- Node.js server có chạy không
- Đường dẫn API có đúng không
- File permissions có đúng không

