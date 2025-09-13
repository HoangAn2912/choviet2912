# Hướng dẫn Debug OTP Email

## Vấn đề hiện tại
Bạn không nhận được mã OTP qua email khi đăng ký tài khoản.

## Các bước kiểm tra

### 1. Kiểm tra cấu hình email
- File: `config/email_config.php`
- Email: `choviet2912@gmail.com`
- Mật khẩu ứng dụng: `gaoj quxf nakt poba`

### 2. Kiểm tra log hệ thống
- File log: `logs/app.log`
- Từ log, hệ thống đã gửi email thành công đến nhiều địa chỉ

### 3. Các nguyên nhân có thể

#### A. Email bị chặn bởi Spam Filter
- **Giải pháp**: Kiểm tra thư mục Spam/Junk trong email của bạn
- **Lưu ý**: Email từ `choviet2912@gmail.com` có thể bị đánh dấu spam

#### B. Delay trong việc nhận email
- **Giải pháp**: Đợi 5-10 phút sau khi gửi OTP
- **Lưu ý**: Gmail có thể có delay trong việc gửi/nhận email

#### C. Email không tồn tại hoặc sai địa chỉ
- **Giải pháp**: Kiểm tra lại địa chỉ email khi đăng ký
- **Lưu ý**: Đảm bảo email được nhập chính xác

### 4. Cách test OTP

#### A. Test với email thật
1. Mở file `test_otp.php`
2. Thay đổi `$testEmail = 'your-email@gmail.com';` thành email thật của bạn
3. Chạy: `php test_otp.php`
4. Kiểm tra email (bao gồm thư mục Spam)

#### B. Test cấu hình email
1. Chạy: `php test_email.php`
2. Kiểm tra output để đảm bảo kết nối SMTP thành công

### 5. Các bước khắc phục

#### A. Kiểm tra thư mục Spam
- Mở Gmail/Outlook
- Kiểm tra thư mục Spam/Junk
- Tìm email từ `choviet2912@gmail.com`

#### B. Thêm email vào whitelist
- Trong Gmail: Settings > Filters and Blocked Addresses
- Tạo filter cho `choviet2912@gmail.com`
- Đánh dấu "Never send it to Spam"

#### C. Kiểm tra cấu hình Gmail
- Đảm bảo 2-Factor Authentication được bật
- Tạo App Password mới nếu cần
- Cập nhật mật khẩu trong `config/email_config.php`

### 6. Test thực tế

1. **Đăng ký tài khoản mới**:
   - Vào trang đăng ký
   - Nhập thông tin đầy đủ
   - Nhấn "Gửi mã OTP"
   - Kiểm tra email (bao gồm Spam)

2. **Kiểm tra log**:
   - Mở `logs/app.log`
   - Tìm dòng mới nhất với email của bạn
   - Xem có lỗi gì không

3. **Test OTP**:
   - Chạy `php test_otp.php` với email thật
   - Kiểm tra email nhận được

### 7. Liên hệ hỗ trợ

Nếu vẫn không nhận được email:
1. Kiểm tra log hệ thống
2. Test với email khác (Gmail, Outlook, Yahoo)
3. Kiểm tra cấu hình firewall/antivirus
4. Liên hệ admin để kiểm tra server

## Lưu ý quan trọng

- **Email OTP có hiệu lực 10 phút**
- **Mỗi email chỉ có thể tạo 1 OTP tại một thời điểm**
- **OTP cũ sẽ bị xóa khi tạo OTP mới**
- **Kiểm tra thư mục Spam trước khi báo lỗi**

