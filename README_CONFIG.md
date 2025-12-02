# Hệ Thống Quản Lý Môi Trường

## Giới Thiệu

Dự án này sử dụng hệ thống quản lý môi trường tự động, cho phép **chỉ cần đổi 1 dòng** để chuyển đổi giữa môi trường Local và Production.

## Cấu Trúc File

```
config/
  ├── env_config.php          # File cấu hình chính (KHÔNG commit lên Git)
  └── env_config.example.php  # File mẫu

helpers/
  └── url_helper.php          # Helper tự động lấy config

model/
  └── mConnect.php            # Database connection tự động
```

## Cách Sử Dụng

### **1. Setup Local (Lần đầu)**

```bash
# Copy file mẫu
cp config/env_config.example.php config/env_config.php

# Mở env_config.php và cập nhật thông tin database local
```

### **2. Chạy Test**

Truy cập: `http://localhost:8080/test_config.php`

Kiểm tra xem tất cả đều OK

### **3. Deploy Lên Hosting**

**CHỈ CẦN 3 BƯỚC:**

1. Mở `config/env_config.php`
2. Đổi 1 dòng:
   ```php
   define('APP_ENV', 'production');  // Đổi từ 'local'
   ```
3. Cập nhật thông tin trong phần `'production'` (database, URL, paths)

**XONG!**

## Các Môi Trường Hỗ Trợ

- **`local`** - Môi trường phát triển (XAMPP)
- **`production`** - Môi trường hosting thật
- **`staging`** - Môi trường test (tùy chọn)

## Các Hàm Helper Có Sẵn

```php
// Lấy giá trị config
config('db_host');          // localhost
config('base_url');         // http://localhost:8080

// Kiểm tra môi trường
isLocal();                  // true/false
isProduction();             // true/false
getCurrentEnvironment();    // 'local' hoặc 'production'

// URL helpers
getBaseUrl();               // http://localhost:8080
getBasePath();              // '' hoặc '/subfolder'

// Debug (chỉ hiện ở local)
debug($data, 'Label');      // Hiển thị debug info
showEnvironmentInfo();      // Hiển thị bảng thông tin môi trường
```

## Ưu Điểm

- **CHỈ 1 DÒNG** để chuyển môi trường  
- **Tự động** lấy config đúng  
- **An toàn** - config không bị commit lên Git  
- **Dễ debug** - hiển thị lỗi chi tiết ở local  
- **Tối ưu** - cache config, không đọc file nhiều lần  

## Tài Liệu

- **[HUONG_DAN_DEPLOY.md](HUONG_DAN_DEPLOY.md)** - Hướng dẫn deploy chi tiết
- **test_config.php** - File test cấu hình (XÓA sau khi deploy)

## Lưu Ý Bảo Mật

1. **KHÔNG** commit file `config/env_config.php` lên Git
2. File `.gitignore` đã được thiết lập tự động
3. **XÓA** file `test_config.php` sau khi deploy production
4. Đảm bảo `APP_ENV='production'` TẮT debug mode

## Khắc Phục Lỗi

### Lỗi kết nối database?
→ Kiểm tra thông tin trong `config/env_config.php`

### CSS/JS không load?
→ Kiểm tra `base_url` và `base_path` trong config

### Muốn xem thông tin môi trường?
```php
// Thêm vào đầu file
showEnvironmentInfo();
```

---

**Made with love for easy deployment**









