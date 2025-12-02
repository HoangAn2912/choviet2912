# 📋 Hướng Dẫn Cấu Hình - Chỉ Cần 1 File

## 🎯 Tổng Quan

Hệ thống đã được cấu hình để **chỉ cần chỉnh 1 file** (`config/env_config.php`) là có thể chạy được tất cả chức năng.

## 📁 Cấu Trúc Config

```
config/
  ├── env_config.php              # ⭐ FILE CHÍNH - Chỉnh file này
  ├── env_config.example.php      # File mẫu (không chứa thông tin thật)
  ├── generate_server_config.php  # Script tạo server_config.js
  ├── email_config.php            # Tự động lấy từ env_config.php
  ├── path_config.php             # Tự động lấy từ env_config.php
  └── server_config.js            # Tự động generate từ env_config.php

helpers/
  └── config_helper.php           # Helper functions để lấy config
```

## 🚀 Cách Sử Dụng

### 1. Setup Lần Đầu (Local)

```bash
# Copy file mẫu
cp config/env_config.example.php config/env_config.php

# Mở file và điền thông tin
# - Database: db_host, db_user, db_pass, db_name
# - Email: email_username, email_password
# - VietQR: vietqr_account_number, vietqr_account_name, sieuthicode_token
```

### 2. Deploy Lên Production

**CHỈ CẦN 2 BƯỚC:**

1. **Mở `config/env_config.php`**
2. **Đổi 1 dòng:**
   ```php
   define('APP_ENV', 'production');  // Đổi từ 'local'
   ```
3. **Cập nhật thông tin trong phần `'production'`:**
   - `base_url` → Domain của bạn
   - `db_user`, `db_pass`, `db_name` → Thông tin database hosting
   - `project_root` → Đường dẫn trên server (vd: `/var/www/choviet.site`)
   - `email_*` → Thông tin email SMTP
   - `vietqr_*` → Thông tin tài khoản VietQR
   - `ws_secret` → Secret cho WebSocket (nên có)

**XONG!** Tất cả config sẽ tự động áp dụng.

### 3. Generate server_config.js (Nếu có thay đổi Node.js config)

```bash
php config/generate_server_config.php
```

Script này sẽ tự động tạo `config/server_config.js` từ config trong `env_config.php`.

## 📝 Các Config Có Sẵn

### Database
- `db_host`, `db_user`, `db_pass`, `db_name`
- `db_charset`, `db_timezone`

### URL & Paths
- `base_url`, `base_path`
- `force_https`
- `project_root`, `chat_path`, `upload_path`, `log_path`

### Node.js Server
- `node_host`, `node_port`
- `ws_host`, `ws_port`, `ws_secret`

### Email SMTP
- `email_host`, `email_username`, `email_password`
- `email_port`, `email_encryption`
- `email_from`, `email_from_name`

### VietQR Payment
- `vietqr_api_url`, `vietqr_bank_code`
- `vietqr_account_number`, `vietqr_account_name`
- `sieuthicode_api_url`, `sieuthicode_token`
- `payment_amounts`

### Debug & Performance
- `debug`, `cache_enabled`, `log_queries`
- `development_mode`

## 🔧 Các File Tự Động Lấy Config

Các file sau **KHÔNG CẦN CHỈNH**, chúng tự động lấy từ `env_config.php`:

- ✅ `config/email_config.php` → Lấy từ `getEmailConfig()`
- ✅ `controller/VietQR_payment/config/config.php` → Lấy từ `getVietQRConfig()`
- ✅ `config/path_config.php` → Lấy từ `getConfig()`
- ✅ `config/server_config.js` → Generate từ `getNodeServerConfig()`

## 💡 Helper Functions

```php
// Lấy config theo key
$dbHost = getConfig('db_host', 'localhost');

// Lấy toàn bộ config
$allConfig = getAllConfig();

// Lấy config email
$emailConfig = getEmailConfig();

// Lấy config VietQR
$vietqrConfig = getVietQRConfig();

// Lấy config Node.js
$nodeConfig = getNodeServerConfig();
```

## ⚠️ Lưu Ý

1. **KHÔNG commit `config/env_config.php` lên Git** (chứa thông tin nhạy cảm)
2. **Chỉ commit `config/env_config.example.php`** (file mẫu)
3. **Sau khi deploy**, chạy `php config/generate_server_config.php` để cập nhật Node.js config
4. **Kiểm tra quyền file** trên server: `chmod 644 config/env_config.php`

## 🐛 Xử Lý Sự Cố

### Lỗi: "Config không tìm thấy"
- Kiểm tra file `config/env_config.php` có tồn tại
- Kiểm tra `APP_ENV` đã được define chưa

### Lỗi: "Môi trường không tồn tại"
- Kiểm tra trong `env_config.php` có key `'local'`, `'production'` hay `'staging'`
- Đảm bảo `APP_ENV` khớp với key trong config

### Node.js server không chạy
- Chạy `php config/generate_server_config.php` để tạo lại `server_config.js`
- Kiểm tra `ws_port` và `ws_secret` trong config

## 📚 Tài Liệu Tham Khảo

- `README_CONFIG.md` - Tài liệu chi tiết về hệ thống config
- `HUONG_DAN_DEPLOY.md` - Hướng dẫn deploy lên server
- `HUONG_DAN_UPDATE.md` - Hướng dẫn cập nhật code

---

**✨ Giờ bạn chỉ cần chỉnh 1 file `env_config.php` là xong!**

