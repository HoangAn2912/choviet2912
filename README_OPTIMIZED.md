# Chợ Việt - Hệ thống đăng ký với OTP

## Tổng quan

Hệ thống đăng ký tài khoản với xác thực OTP qua email và SMS, được tối ưu hóa để dễ đọc, bảo trì và mở rộng.

## Cấu trúc dự án

```
project3/
├── controller/
│   ├── cLoginLogout.php      # Controller xử lý đăng nhập/đăng ký
│   └── cOtp.php             # Controller xử lý OTP
├── model/
│   ├── mConnect.php         # Kết nối cơ sở dữ liệu
│   └── mLoginLogout.php     # Model xử lý logic nghiệp vụ
├── config/
│   ├── email_config.php     # Cấu hình email SMTP
│   └── sms_config.php       # Cấu hình SMS Twilio
├── helpers/
│   └── logger.php           # Class ghi log
├── loginlogout/
│   └── signup.php           # Giao diện đăng ký
└── logs/
    └── app.log              # File log ứng dụng
```

## Tính năng chính

### 1. Đăng ký tài khoản
- Hỗ trợ đăng ký bằng email hoặc số điện thoại
- Xác thực OTP qua email (SMTP) hoặc SMS (Twilio)
- Validation real-time trên client-side
- Giao diện responsive và thân thiện người dùng

### 2. Bảo mật
- Mã hóa mật khẩu bằng MD5
- OTP có thời hạn 10 phút
- Kiểm tra trùng lặp email/số điện thoại
- Xác thực 2 lớp (OTP + thông tin cá nhân)

### 3. Ghi log
- Ghi log chi tiết mọi hoạt động
- Hỗ trợ debug và theo dõi lỗi
- Lưu trữ log theo thời gian

## Cài đặt và cấu hình

### 1. Yêu cầu hệ thống
- PHP 7.4+
- MySQL 5.7+
- Composer
- XAMPP/WAMP/LAMP

### 2. Cài đặt dependencies
```bash
composer require phpmailer/phpmailer twilio/sdk
```

### 3. Cấu hình cơ sở dữ liệu
Tạo bảng `otp_verification`:
```sql
CREATE TABLE IF NOT EXISTS otp_verification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NULL,
    so_dien_thoai VARCHAR(20) NULL,
    otp VARCHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (email),
    UNIQUE (so_dien_thoai)
);
```

### 4. Cấu hình email
Chỉnh sửa `config/email_config.php`:
```php
return [
    'host' => 'smtp.gmail.com',
    'username' => 'your-email@gmail.com',
    'password' => 'your-app-password', // Mật khẩu ứng dụng Gmail
    'port' => 587,
    'encryption' => 'tls',
    'from_email' => 'your-email@gmail.com',
    'from_name' => 'Chợ Việt'
];
```

### 5. Cấu hình SMS
Chỉnh sửa `config/sms_config.php`:
```php
return [
    'account_sid' => 'your-twilio-account-sid',
    'auth_token' => 'your-twilio-auth-token',
    'from_number' => 'your-twilio-phone-number',
];
```

## Sử dụng

### 1. Gửi OTP
```javascript
// Gửi OTP qua email
const response = await fetch('controller/cOtp.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: new URLSearchParams({
        action: 'send_otp',
        method: 'email',
        contact: 'user@example.com'
    })
});
```

### 2. Xác thực OTP
```javascript
// Xác thực OTP
const response = await fetch('controller/cOtp.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: new URLSearchParams({
        action: 'verify_otp',
        method: 'email',
        contact: 'user@example.com',
        otp: '123456'
    })
});
```

### 3. Đăng ký tài khoản
```javascript
// Gửi form đăng ký
const formData = new FormData(form);
formData.append('register', '1');

const response = await fetch('controller/cLoginLogout.php', {
    method: 'POST',
    body: formData
});
```

## Kiến trúc và thiết kế

### 1. MVC Pattern
- **Model**: Xử lý logic nghiệp vụ và tương tác cơ sở dữ liệu
- **View**: Giao diện người dùng và JavaScript
- **Controller**: Xử lý yêu cầu và điều hướng

### 2. Separation of Concerns
- Tách biệt logic xử lý OTP và đăng ký
- Mỗi class có trách nhiệm rõ ràng
- Dễ dàng mở rộng và bảo trì

### 3. Error Handling
- Xử lý lỗi ở mọi cấp độ
- Ghi log chi tiết để debug
- Thông báo lỗi thân thiện người dùng

### 4. Security
- Validation cả client-side và server-side
- Sử dụng prepared statements để tránh SQL injection
- Kiểm tra quyền truy cập

## Tùy chỉnh và mở rộng

### 1. Thêm phương thức xác thực mới
```php
// Trong cOtp.php
case 'send_otp_telegram':
    $this->handleSendOTPTelegram();
    break;
```

### 2. Thay đổi template email
```php
// Trong cOtp.php
private function createEmailContent($otp) {
    return 'Your custom email template with OTP: ' . $otp;
}
```

### 3. Thêm validation mới
```javascript
// Trong signup.php
validateField(field) {
    // Thêm logic validation tùy chỉnh
    if (field === this.customField) {
        // Custom validation logic
    }
}
```

## Troubleshooting

### 1. OTP không được gửi
- Kiểm tra cấu hình SMTP/Twilio
- Xem log trong `logs/app.log`
- Kiểm tra quyền ghi file log

### 2. Lỗi cơ sở dữ liệu
- Kiểm tra kết nối MySQL
- Xem cấu trúc bảng `nguoi_dung`
- Kiểm tra quyền truy cập database

### 3. Lỗi JavaScript
- Mở Developer Tools (F12)
- Kiểm tra Console tab
- Kiểm tra Network tab để xem AJAX requests

## Bảo trì và cập nhật

### 1. Backup dữ liệu
```bash
mysqldump -u username -p database_name > backup.sql
```

### 2. Cập nhật dependencies
```bash
composer update
```

### 3. Xóa log cũ
```bash
# Xóa log cũ hơn 30 ngày
find logs/ -name "*.log" -mtime +30 -delete
```

## Đóng góp

1. Fork dự án
2. Tạo feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit thay đổi (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Tạo Pull Request

## Giấy phép

Dự án này được phát hành dưới giấy phép MIT. Xem file `LICENSE` để biết thêm chi tiết.

## Liên hệ

- Email: support@choviet.com
- Website: https://choviet.com
- GitHub: https://github.com/choviet/project3

---

**Lưu ý**: Đây là phiên bản tối ưu hóa của hệ thống đăng ký OTP. Để sử dụng trong môi trường production, vui lòng kiểm tra và cấu hình bảo mật phù hợp.
