# Hướng dẫn cấu hình và sử dụng chức năng xác thực OTP

## Giới thiệu

Chức năng xác thực OTP cho phép người dùng đăng ký tài khoản bằng email hoặc số điện thoại, với xác thực thông qua mã OTP. Hệ thống hỗ trợ gửi OTP qua email (sử dụng PHPMailer) và SMS (sử dụng Twilio).

## Cài đặt

### 1. Cài đặt Composer

- Chạy file `install_composer.bat` để tải và cài đặt Composer
- Hoặc tải Composer từ [getcomposer.org](https://getcomposer.org/download/) và cài đặt thủ công

### 2. Cài đặt các thư viện cần thiết

```bash
composer require phpmailer/phpmailer
composer require twilio/sdk
```

Hoặc chạy file `install_packages.bat` để cài đặt tự động.

## Cấu hình

### 1. Cấu hình gửi email

Mở file `config/email_config.php` và cập nhật các thông tin sau:

```php
return [
    'host' => 'smtp.gmail.com', // Hoặc SMTP server khác
    'username' => 'your_email@gmail.com', // Email của bạn
    'password' => 'your_app_password', // Mật khẩu ứng dụng
    'port' => 587,
    'encryption' => 'tls',
    'from_email' => 'your_email@gmail.com', // Email của bạn
    'from_name' => 'Chợ Việt'
];
```

**Lưu ý về mật khẩu ứng dụng Gmail:**
1. Bật xác thực 2 bước cho tài khoản Google của bạn
2. Truy cập [https://myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords)
3. Tạo mật khẩu ứng dụng mới cho "Mail" và "Khác (Tên tùy chỉnh)"
4. Sử dụng mật khẩu được tạo ra trong cấu hình

### 2. Cấu hình gửi SMS với Twilio

Mở file `config/sms_config.php` và cập nhật các thông tin sau:

```php
return [
    'account_sid' => 'your_account_sid', // SID từ trang Twilio
    'auth_token' => 'your_auth_token', // Token từ trang Twilio
    'from_number' => '+1234567890' // Số điện thoại Twilio của bạn
];
```

**Cách lấy thông tin Twilio:**
1. Đăng ký tài khoản tại [Twilio.com](https://www.twilio.com/)
2. Sau khi đăng nhập, vào Dashboard để lấy Account SID và Auth Token
3. Mua một số điện thoại Twilio hoặc sử dụng số thử nghiệm
4. Đối với tài khoản thử nghiệm, bạn cần xác thực số điện thoại nhận SMS trước

## Xử lý lỗi

### Kiểm tra logs

Hệ thống ghi log chi tiết vào thư mục `logs/app.log`. Kiểm tra file này để tìm nguyên nhân lỗi.

### Lỗi thường gặp khi gửi email

1. **Lỗi xác thực SMTP**: Kiểm tra lại username và password
2. **Lỗi kết nối SMTP**: Kiểm tra cấu hình host, port và encryption
3. **Email bị chặn**: Kiểm tra spam folder và cấu hình bảo mật của email

### Lỗi thường gặp khi gửi SMS

1. **Lỗi xác thực Twilio**: Kiểm tra lại Account SID và Auth Token
2. **Số điện thoại không hợp lệ**: Đảm bảo số điện thoại có định dạng quốc tế (+84xxx)
3. **Hạn chế tài khoản thử nghiệm**: Với tài khoản thử nghiệm, chỉ gửi được SMS đến số điện thoại đã xác thực

## Mở rộng

### Thay đổi nhà cung cấp SMS

Nếu muốn sử dụng nhà cung cấp SMS khác (như Viettel SMS, SpeedSMS, ...), bạn cần:

1. Cài đặt thư viện tương ứng (nếu có)
2. Chỉnh sửa hàm `sendOTPBySMS()` trong file `controller/cOtp.php`
3. Cập nhật cấu hình trong `config/sms_config.php`

### Tùy chỉnh nội dung OTP

Để thay đổi nội dung email hoặc SMS OTP:

1. Chỉnh sửa template HTML trong hàm `sendOTPByEmail()` tại file `controller/cOtp.php`
2. Chỉnh sửa nội dung SMS trong hàm `sendOTPBySMS()` tại file `controller/cOtp.php`

## Kiểm thử

### Kiểm thử gửi email

1. Đăng ký bằng email thật để nhận OTP
2. Kiểm tra logs để xem quá trình gửi email
3. Kiểm tra hộp thư đến và thư mục spam

### Kiểm thử gửi SMS

1. Đăng ký bằng số điện thoại đã xác thực với Twilio
2. Kiểm tra logs để xem quá trình gửi SMS
3. Kiểm tra tin nhắn trên điện thoại

## Hỗ trợ

Nếu gặp vấn đề trong quá trình cài đặt hoặc sử dụng, vui lòng liên hệ với đội phát triển để được hỗ trợ.

