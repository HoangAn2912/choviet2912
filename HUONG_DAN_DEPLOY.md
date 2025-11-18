# 🚀 HƯỚNG DẪN DEPLOY LÊN HOSTING

## 📋 CHECKLIST TRƯỚC KHI DEPLOY

### ✅ Bước 1: Chuẩn bị file config

1. Mở file `config/env_config.php`
2. **ĐỔI 1 DÒNG DUY NHẤT:**
   ```php
   // Từ:
   define('APP_ENV', 'local');
   
   // Thành:
   define('APP_ENV', 'production');
   ```

3. Cập nhật thông tin trong phần `'production'`:
   - `base_url` → Domain của bạn (vd: https://yourdomain.com)
   - `base_path` → Để trống nếu ở root, hoặc '/subfolder' nếu trong subfolder
   - `db_host` → Host database từ cPanel
   - `db_user` → Username database
   - `db_pass` → Password database  
   - `db_name` → Tên database
   - `project_root` → Đường dẫn thực tế trên hosting (vd: /home/username/public_html)
   - `chat_path`, `upload_path`, `log_path` → Cập nhật tương ứng

### ✅ Bước 2: Chuẩn bị database

1. Export database từ phpMyAdmin local (file `choviet29.sql`)
2. Login vào cPanel hosting
3. Tạo database mới và user trong MySQL Databases
4. Import file SQL vào database hosting

### ✅ Bước 3: Upload files

Upload các file/folder sau lên hosting:
- `api/`
- `config/` (⚠️ KHÔNG upload `env_config.example.php`)
- `controller/`
- `css/`
- `helpers/`
- `img/` (có thể bỏ qua ảnh test)
- `js/`
- `lib/`
- `loginlogout/`
- `logs/` (tạo folder rỗng)
- `model/`
- `scss/`
- `vendor/`
- `view/`
- `.htaccess` (quan trọng!)
- `admin.php`
- `checkout.php`
- `composer.json`
- `index.php`
- `my_orders.php`
- `show_packages.php`

### ✅ Bước 4: Thiết lập quyền folder

Đảm bảo các folder có quyền ghi (CHMOD 755 hoặc 777):
- `img/`
- `chat/`
- `logs/`

### ✅ Bước 5: Kiểm tra .htaccess

Đảm bảo file `.htaccess` có nội dung:

```apache
RewriteEngine On
RewriteBase /

# Nếu website trong subfolder, đổi thành:
# RewriteBase /subfolder/

# Admin routing
RewriteRule ^ad/([a-zA-Z0-9_-]+)$ admin.php?$1 [QSA,L]
RewriteRule ^ad/?$ admin.php [L]

# Bỏ qua file/thư mục có thật
RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule . - [L]

# URL thân thiện cho profile
RewriteRule ^([A-Za-z0-9_-]+)/?$ index.php?username=$1 [QSA,L]
```

### ✅ Bước 6: Cài đặt Composer (nếu hosting hỗ trợ)

```bash
composer install --no-dev --optimize-autoloader
```

### ✅ Bước 7: Test website

1. Truy cập domain của bạn
2. Kiểm tra:
   - ✅ Trang chủ load được
   - ✅ CSS/JS/Images hiển thị đúng
   - ✅ Đăng nhập/đăng ký hoạt động
   - ✅ Upload ảnh hoạt động
   - ✅ Chat (nếu có)
   - ✅ VNPay return URL đúng

---

## 🔄 ROLLBACK (Nếu lỗi)

Nếu website bị lỗi sau khi deploy:

1. Mở `config/env_config.php`
2. Đổi lại:
   ```php
   define('APP_ENV', 'local');
   ```
3. Hoặc xem log lỗi trong `logs/app.log`

---

## 🐞 KHẮC PHỤC LỖI THƯỜNG GẶP

### Lỗi: "Không kết nối được database"
**Nguyên nhân:** Thông tin database sai  
**Giải pháp:**
1. Kiểm tra lại `db_host`, `db_user`, `db_pass`, `db_name` trong `config/env_config.php`
2. Kiểm tra user có quyền truy cập database không (trong cPanel)

### Lỗi: "CSS/JS không load"
**Nguyên nhân:** Base path sai  
**Giải pháp:**
1. Nếu website trong subfolder, cập nhật `base_path` trong config
2. Kiểm tra `.htaccess` có `RewriteBase` đúng không

### Lỗi: "500 Internal Server Error"
**Nguyên nhân:** Có thể do PHP version hoặc quyền file  
**Giải pháp:**
1. Kiểm tra PHP version (cần >= 7.4)
2. Kiểm tra error log trong cPanel
3. Đảm bảo các folder có quyền ghi đúng

### Lỗi: "VNPay return về localhost"
**Nguyên nhân:** VNPay config chưa update  
**Giải pháp:**
1. Mở `controller/vnpay/vnpay_config.php`
2. Kiểm tra `$vnp_Returnurl` có đúng domain hosting không

---

## 📞 HỖ TRỢ

Nếu gặp lỗi khác, hãy:
1. Bật debug mode tạm thời: `define('APP_ENV', 'local');`
2. Xem log chi tiết
3. Chụp màn hình lỗi
4. Liên hệ support

---

## ✨ LỢI ÍCH CỦA HỆ THỐNG NÀY

✅ **CHỈ 1 DÒNG** để chuyển đổi môi trường  
✅ **Tự động** lấy config đúng cho mỗi môi trường  
✅ **An toàn** - không hardcode thông tin nhạy cảm  
✅ **Dễ debug** - hiển thị lỗi chi tiết ở local  
✅ **Dễ mở rộng** - thêm môi trường staging/testing dễ dàng  

---

**Chúc bạn deploy thành công! 🎉**



