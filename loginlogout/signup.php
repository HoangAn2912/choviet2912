<?php
include_once("controller/cLoginLogout.php");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Chợ Việt</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/font-awesome.css">
<style>
        body {
            background: #2c3e50;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        
        .form-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        .form-switch {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e1e5e9;
        }
        
        .form-switch a {
            color: #fcb50e;
            text-decoration: none;
            font-weight: 600;
        }
        
        .form-switch a:hover {
            text-decoration: underline;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
    display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
}

        .form-group input {
    width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
    font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #fcb50e;
        }
        
        .btn {
    width: 100%;
            padding: 15px;
    border: none;
            border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #fcb50e, #f7931e);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(252, 181, 14, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .otp-help {
            font-size: 14px;
            color: #666;
            margin-top: 10px;
            text-align: center;
        }
        
        .error {
            border-color: #dc3545 !important;
        }
        
        .success {
            border-color: #28a745 !important;
        }
        
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 20px 25px;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            z-index: 1000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            min-width: 300px;
            text-align: center;
        }
        
        .toast.show {
            transform: translateX(0);
        }
        
        .toast.success {
            background: linear-gradient(135deg, #28a745, #20c997);
            border-left: 4px solid #155724;
        }
        
        .toast.error {
            background: linear-gradient(135deg, #dc3545, #e74c3c);
            border-left: 4px solid #721c24;
        }
        
        .toast.info {
            background: linear-gradient(135deg, #17a2b8, #6f42c1);
            border-left: 4px solid #0c5460;
        }
        
        /* CSS cho tooltip lỗi */
        .field-error-tooltip {
            position: absolute;
            background: #dc3545;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            z-index: 1000;
            max-width: 300px;
            word-wrap: break-word;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Cải thiện giao diện form */
        .form-group {
            position: relative;
        }
        
        .form-group input.success {
            border-color: #28a745;
            background-color: #f8fff9;
        }
        
        .form-group input.error {
            border-color: #dc3545;
            background-color: #fff8f8;
        }
        
        /* Thêm icon cho trường hợp lệ */
        .form-group input.success::after {
            content: '✓';
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #28a745;
            font-weight: bold;
        }
        
        /* CSS cho helper text */
        .form-group small {
            color: #666;
            font-size: 12px;
            margin-top: 5px;
            display: block;
            line-height: 1.4;
        }
        
        /* CSS cho password field */
        .form-group input[type="password"] {
            padding-right: 40px;
        }
        
        /* CSS cho password toggle icon */
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%) translateY(3px);
            cursor: pointer;
            color: #666;
            font-size: 18px;
            transition: color 0.3s ease;
            z-index: 10;
        }
        
        .password-toggle:hover {
            color: #fcb50e;
        }
        
        .password-toggle.active {
            color: #fcb50e;
        }
        
        /* Đảm bảo input password có position relative */
        .form-group {
            position: relative;
        }
        
        /* Hiệu ứng focus cho input */
        .form-group input:focus {
            outline: none;
            border-color: #fcb50e;
            box-shadow: 0 0 0 3px rgba(252, 181, 14, 0.1);
        }
        
        /* Animation cho validation */
        .form-group input.error,
        .form-group input.success {
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
</style>
</head>
<body>
    <div class="form-container">
        <h2 style="text-align: center; color: #fcb50e; margin-bottom: 30px;">Đăng ký tài khoản</h2>
        
        <form id="registerForm">
            <div class="form-group">
                <label for="username">Tên đăng nhập *</label>
<input type="text" id="username" name="username" 
                       placeholder="Ví dụ: choviet123" 
                       pattern="[a-zA-Z][a-zA-Z0-9_]{2,19}" 
                       title="Tên đăng nhập phải bắt đầu bằng chữ cái, chỉ chứa chữ cái, số và dấu gạch dưới, độ dài 3-20 ký tự" 
                       required>
                <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                    Tên đăng nhập phải bắt đầu bằng chữ cái, chỉ chứa chữ cái, số và dấu gạch dưới (_), độ dài 3-20 ký tự
                </small>
            </div>
            
            <div class="form-group">
                <label for="password">Mật khẩu *</label>
                <input type="password" id="password" name="password" 
                       placeholder="Ví dụ: Choviet123@" 
                       pattern="^(?=.*[a-zA-Z])(?=.*\d)[a-zA-Z0-9!@#$%^&*()_+\-=\[\]{};\':\"\\|,.>
                <span class="password-toggle" id="passwordToggle" title="Hiển thị/Ẩn mật khẩu">
                    <svg width="20" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z" stroke="currentColor" stroke-width="2" fill="none"/>
                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" fill="none"/>
                        <circle cx="12" cy="12" r="1" fill="currentColor"/>
                    </svg>
                </span>
                <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                    Mật khẩu phải có ít nhất 8 ký tự, chứa chữ cái và số, không chứa khoảng trắng
                </small>
            </div>
            
            <div class="form-group">
                <label for="repassword">Nhập lại mật khẩu *</label>
                <input type="password" id="repassword" name="repassword" 
                       placeholder="Nhập lại mật khẩu" 
                       required>
                <span class="password-toggle" id="repasswordToggle" title="Hiển thị/Ẩn mật khẩu">
                    <svg width="20" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z" stroke="currentColor" stroke-width="2" fill="none"/>
                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" fill="none"/>
                        <circle cx="12" cy="12" r="1" fill="currentColor"/>
                    </svg>
                </span>
                <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                    Nhập lại mật khẩu để xác nhận
                </small>
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <input 
                placeholder="Ví dụ: choviet123@example.com"
                type="email" id="email" name="email" required>
					</div>
            
            <div class="form-group" id="otpGroup" style="display: none;">
                <label for="otp">Mã OTP *</label>
                <input type="text" id="otp" name="otp" maxlength="6" placeholder="Nhập 6 chữ số">
                <div class="otp-help">Mã OTP sẽ được gửi đến email của bạn</div>
			</div>
            
            <div class="form-group">
                <button type="button" id="sendOtpBtn" class="btn btn-primary">
                    Gửi mã OTP
                </button>
        </div>
            
            <div class="form-group" id="registerBtnGroup" style="display: none;">
                <button type="submit" id="registerBtn" class="btn btn-primary">
                    Đăng ký
                </button>
        </div>
        </form>
        
        <div class="form-switch" style="text-align: center; margin-top: 20px;">
            <a href="index.php?login" style="color: #fcb50e; text-decoration: none;">
                Đã có tài khoản? Đăng nhập ngay
            </a>
    </div>
</div>

    <script>
        /**
         * Class quản lý form đăng ký
         */
        class RegistrationForm {
            constructor() {
                this.initializeElements();
                this.bindEvents();
            }
            
            /**
             * Khởi tạo các phần tử DOM
             */
            initializeElements() {
                this.form = document.getElementById('registerForm');
                this.otpGroup = document.getElementById('otpGroup');
                this.sendOtpBtn = document.getElementById('sendOtpBtn');
                this.registerBtnGroup = document.getElementById('registerBtnGroup');
                this.registerBtn = document.getElementById('registerBtn');
                
                this.username = document.getElementById('username');
                this.tenDangNhap = document.getElementById('username'); // Alias for compatibility
                this.password = document.getElementById('password');
                this.repassword = document.getElementById('repassword');
                this.email = document.getElementById('email');
                this.otp = document.getElementById('otp');
                
                // Khởi tạo các toggle cho password
                this.passwordToggle = document.getElementById('passwordToggle');
                this.repasswordToggle = document.getElementById('repasswordToggle');
            }
            
            /**
             * Gắn các sự kiện
             */
            bindEvents() {
                // Gửi OTP
                this.sendOtpBtn.addEventListener('click', () => this.handleSendOTP());
                
                // Gửi form đăng ký
                this.form.addEventListener('submit', (e) => this.handleFormSubmit(e));
                
                // Validation real-time
                this.tenDangNhap.addEventListener('input', this.debounce(() => this.validateField(this.tenDangNhap), 500));
                this.password.addEventListener('input', this.debounce(() => this.validateField(this.password), 500));
                this.repassword.addEventListener('input', this.debounce(() => this.validateField(this.repassword), 300));
                this.email.addEventListener('input', () => this.validateField(this.email));
                this.otp.addEventListener('input', () => this.validateField(this.otp));
                
                // Kiểm tra tên đăng nhập real-time
                this.tenDangNhap.addEventListener('blur', () => this.checkUsernameAvailability());
                
                // Kiểm tra mật khẩu real-time
                this.password.addEventListener('blur', () => this.checkPasswordValidity());
                
                // Thêm sự kiện cho password toggle
                this.passwordToggle.addEventListener('click', () => this.togglePasswordVisibility(this.password, this.passwordToggle));
                this.repasswordToggle.addEventListener('click', () => this.togglePasswordVisibility(this.repassword, this.repasswordToggle));
            }
            
            /**
             * Debounce function để tránh gọi API quá nhiều
             */
            debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }
            
            /**
             * Kiểm tra tên đăng nhập có sẵn không
             */
            async checkUsernameAvailability() {
                const username = this.tenDangNhap.value.trim();
                
                if (!username || username.length < 3) {
                    return;
                }
                
                try {
                    const response = await fetch('controller/cLoginLogout.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            check_username: '1',
                            username: username
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success && result.available) {
                        this.tenDangNhap.classList.add('success');
                        this.tenDangNhap.classList.remove('error');
                        this.showToast('✅ ' + result.message, 'success', 3000);
                    } else {
                        this.tenDangNhap.classList.add('error');
                        this.tenDangNhap.classList.remove('success');
                        this.showToast('❌ ' + result.message, 'error', 5000);
                    }
                    
                } catch (error) {
                    console.error('Lỗi kiểm tra tên đăng nhập:', error);
                }
            }
            
            /**
             * Kiểm tra mật khẩu hợp lệ real-time
             */
            async checkPasswordValidity() {
                const password = this.password.value;
                
                if (!password || password.length < 8) {
                    return;
                }
                
                try {
                    const response = await fetch('controller/cLoginLogout.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            check_password: '1',
                            password: password
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success && result.valid) {
                        this.password.classList.add('success');
                        this.password.classList.remove('error');
                        this.showToast('✅ ' + result.message, 'success', 3000);
                    } else {
                        this.password.classList.add('error');
                        this.password.classList.remove('success');
                        this.showToast('❌ ' + result.message, 'error', 5000);
                    }
                    
                } catch (error) {
                    console.error('Lỗi kiểm tra mật khẩu:', error);
                }
            }
            
            /**
             * Xử lý gửi OTP
             */
            async handleSendOTP() {
                if (!this.validateBeforeOTP()) {
                    return;
                }
                
                try {
                    this.sendOtpBtn.disabled = true;
                    this.sendOtpBtn.textContent = 'Đang gửi...';
                    
                    const response = await fetch('controller/cOtp.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'send_otp',
                            method: 'email',
                            contact: this.email.value
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        this.showOTPInput();
                        this.showToast(result.message, 'success');
                    } else {
                        this.showToast(result.message, 'error');
                    }
                    
                } catch (error) {
                    console.error('Lỗi gửi OTP:', error);
                    this.showToast('Có lỗi xảy ra khi gửi OTP', 'error');
                } finally {
                    this.sendOtpBtn.disabled = false;
                    this.sendOtpBtn.textContent = 'Gửi lại OTP';
                }
            }
            
            /**
             * Kiểm tra dữ liệu trước khi gửi OTP
             */
            validateBeforeOTP() {
                const requiredFields = [
                    { field: this.tenDangNhap, name: 'Tên đăng nhập' },
                    { field: this.password, name: 'Mật khẩu' },
                    { field: this.repassword, name: 'Nhập lại mật khẩu' },
                    { field: this.email, name: 'Email' }
                ];
                
                // Kiểm tra các trường bắt buộc
                for (const { field, name } of requiredFields) {
                    if (!this.validateField(field)) {
                        this.showToast(`Vui lòng kiểm tra ${name}`, 'error');
                        return false;
                    }
                }
                
                // Kiểm tra mật khẩu hợp lệ
                const passwordValidation = this.validatePassword(this.password.value);
                if (!passwordValidation.valid) {
                    this.showToast('❌ ' + passwordValidation.message, 'error');
                    return false;
                }
                
                // Kiểm tra mật khẩu khớp
                if (this.password.value !== this.repassword.value) {
                    this.showToast('Mật khẩu không khớp', 'error');
                    return false;
                }
                
                return true;
            }
            
            /**
             * Hiển thị input OTP
             */
            showOTPInput() {
                this.otpGroup.style.display = 'block';
                this.registerBtnGroup.style.display = 'block';
                this.sendOtpBtn.style.display = 'none';
                this.otp.focus();
            }
            
            /**
             * Xử lý gửi form đăng ký
             */
            async handleFormSubmit(event) {
                event.preventDefault();
                
                if (!this.validateForm()) {
                    return;
                }
                
                try {
                    this.registerBtn.disabled = true;
                    this.registerBtn.textContent = 'Đang đăng ký...';
                    
                    const formData = new FormData(this.form);
                    formData.append('register', '1');
                    
                    const response = await fetch('controller/cLoginLogout.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    // Xử lý response
                    const result = await response.text();
                    console.log('Response:', result);
                    
                    try {
                        const jsonResult = JSON.parse(result);
                        if (jsonResult.success) {
                            // Hiển thị toast thành công và chuyển hướng
                            this.showToast(jsonResult.message, 'success', 3000);
                            // Chuyển hướng sang trang đăng nhập sau 3 giây
                            setTimeout(() => {
                                window.location.href = 'index.php?login';
                            }, 3000);
                        } else {
                            // Hiển thị toast lỗi
                            this.showToast(jsonResult.message, 'error');
                            this.registerBtn.disabled = false;
                            this.registerBtn.textContent = 'Đăng ký';
                        }
                    } catch (e) {
                        console.error('Lỗi parse JSON:', e);
                        // Fallback: hiển thị thông báo và chuyển hướng
                        this.showToast('Đăng ký thành công! Chuyển hướng đến trang đăng nhập...', 'success', 3000);
                        setTimeout(() => {
                            window.location.href = 'index.php?login';
                        }, 3000);
                    }
                    
                } catch (error) {
                    console.error('Lỗi đăng ký:', error);
                    this.showToast('Có lỗi xảy ra khi đăng ký', 'error');
                    this.registerBtn.disabled = false;
                    this.registerBtn.textContent = 'Đăng ký';
                }
            }
            
            /**
             * Kiểm tra form trước khi gửi
             */
            validateForm() {
                if (!this.validateField(this.otp)) {
                    this.showToast('Vui lòng nhập mã OTP', 'error');
                    return false;
                }
                
                return true;
            }
            
            /**
             * Kiểm tra một trường dữ liệu
             */
            validateField(field) {
                const value = field.value.trim();
                let isValid = true;
                
                // Xóa class lỗi cũ
                field.classList.remove('error', 'success');
                
                // Kiểm tra trường bắt buộc
                if (field.required && !value) {
                    field.classList.add('error');
                    isValid = false;
                }
                
                // Kiểm tra tên đăng nhập
                if (field === this.tenDangNhap && value) {
                    const usernameValidation = this.validateUsername(value);
                    if (!usernameValidation.valid) {
                        field.classList.add('error');
                        isValid = false;
                        // Hiển thị tooltip lỗi
                        this.showFieldError(field, usernameValidation.message);
                    } else {
                        this.hideFieldError(field);
                    }
                }
                
                // Kiểm tra mật khẩu
                if (field === this.password && value) {
                    const passwordValidation = this.validatePassword(value);
                    if (!passwordValidation.valid) {
                        field.classList.add('error');
                        isValid = false;
                        this.showFieldError(field, passwordValidation.message);
                    } else {
                        this.hideFieldError(field);
                    }
                }
                
                // Kiểm tra nhập lại mật khẩu
                if (field === this.repassword && value) {
                    if (value !== this.password.value) {
                        field.classList.add('error');
                        isValid = false;
                        this.showFieldError(field, 'Mật khẩu không khớp');
                    } else {
                        this.hideFieldError(field);
                    }
                }
                
                // Kiểm tra email
                if (field === this.email && value && !this.isValidEmail(value)) {
                    field.classList.add('error');
                    isValid = false;
                }
                
                // Thêm class thành công nếu hợp lệ
                if (isValid && value) {
                    field.classList.add('success');
                }
                
                return isValid;
            }
            
            /**
             * Kiểm tra email hợp lệ
             */
            isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }
            
            /**
             * Kiểm tra tên đăng nhập hợp lệ
             */
            validateUsername(username) {
                const errors = [];
                
                // Kiểm tra độ dài
                if (username.length < 3) {
                    errors.push('Tên đăng nhập phải có ít nhất 3 ký tự');
                }
                
                if (username.length > 20) {
                    errors.push('Tên đăng nhập không được quá 20 ký tự');
                }
                
                // Kiểm tra khoảng trắng
                if (/\s/.test(username)) {
                    errors.push('Tên đăng nhập không được chứa khoảng trắng');
                }
                
                // Kiểm tra dấu của chữ cái (chỉ cho phép chữ cái, số, dấu gạch dưới)
                if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                    errors.push('Tên đăng nhập chỉ được chứa chữ cái, số và dấu gạch dưới (_)');
                }
                
                // Kiểm tra ký tự đầu tiên
                if (!/^[a-zA-Z]/.test(username)) {
                    errors.push('Tên đăng nhập phải bắt đầu bằng chữ cái');
                }
                
                return {
                    valid: errors.length === 0,
                    errors: errors,
                    message: errors.length > 0 ? errors.join(', ') : 'Tên đăng nhập hợp lệ'
                };
            }
            
            /**
             * Kiểm tra mật khẩu hợp lệ
             */
            validatePassword(password) {
                const errors = [];
                
                // Kiểm tra độ dài
                if (password.length < 8) {
                    errors.push('Mật khẩu phải có ít nhất 8 ký tự');
                }
                
                // Kiểm tra chứa chữ cái và số
                if (!/(?=.*[a-zA-Z])(?=.*\d)/.test(password)) {
                    errors.push('Mật khẩu phải chứa ít nhất một chữ cái và một số');
                }
                
                // Kiểm tra khoảng trắng
                if (/\s/.test(password)) {
                    errors.push('Mật khẩu không được chứa khoảng trắng');
                }
                
                return {
                    valid: errors.length === 0,
                    errors: errors,
                    message: errors.length > 0 ? errors.join(', ') : 'Mật khẩu hợp lệ'
                };
            }
            
            /**
             * Hiển thị lỗi cho trường dữ liệu
             */
            showFieldError(field, message) {
                // Xóa tooltip lỗi cũ nếu có
                this.hideFieldError(field);
                
                const errorTooltip = document.createElement('div');
                errorTooltip.className = 'field-error-tooltip';
                errorTooltip.textContent = message;
                
                // Đặt vị trí tooltip
                const rect = field.getBoundingClientRect();
                errorTooltip.style.left = rect.left + 'px';
                errorTooltip.style.top = (rect.bottom + 5) + 'px';
                
                document.body.appendChild(errorTooltip);
                field.dataset.errorTooltip = 'true';
                
                // Tự động ẩn sau 5 giây
                setTimeout(() => this.hideFieldError(field), 5000);
            }
            
            /**
             * Ẩn lỗi cho trường dữ liệu
             */
            hideFieldError(field) {
                if (field.dataset.errorTooltip === 'true') {
                    const tooltip = document.querySelector('.field-error-tooltip');
                    if (tooltip) {
                        tooltip.remove();
                    }
                    delete field.dataset.errorTooltip;
                }
            }
            
            /**
             * Bật/tắt hiển thị mật khẩu
             */
            togglePasswordVisibility(passwordField, toggleButton) {
                // Chuyển đổi type của input
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    // Icon mắt đóng (mắt bị gạch chéo)
                    toggleButton.innerHTML = `
                        <svg width="20" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z" stroke="currentColor" stroke-width="2" fill="none"/>
                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" fill="none"/>
                            <circle cx="12" cy="12" r="1" fill="currentColor"/>
                            <line x1="1" y1="23" x2="23" y2="1" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    `;
                    toggleButton.classList.add('active');
                    toggleButton.title = 'Ẩn mật khẩu';
                } else {
                    passwordField.type = 'password';
                    // Icon mắt mở (mắt bình thường)
                    toggleButton.innerHTML = `
                        <svg width="20" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z" stroke="currentColor" stroke-width="2" fill="none"/>
                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" fill="none"/>
                            <circle cx="12" cy="12" r="1" fill="currentColor"/>
                        </svg>
                    `;
                    toggleButton.classList.remove('active');
                    toggleButton.title = 'Hiển thị mật khẩu';
                }
            }
            
            /**
             * Hiển thị thông báo toast
             */
            showToast(message, type = 'info', duration = 5000) {
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                
                // Hiển thị nội dung toast đơn giản, không đếm ngược, không chuyển hướng
                toast.textContent = message;
                
                document.body.appendChild(toast);
                
                // Hiển thị toast
                setTimeout(() => toast.classList.add('show'), 100);
                
                // Ẩn toast sau thời gian chỉ định
                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => {
                        if (document.body.contains(toast)) {
                            document.body.removeChild(toast);
                        }
                    }, 300);
                }, duration);
            }
        }
        
        // Khởi tạo form khi trang đã tải xong
        document.addEventListener('DOMContentLoaded', () => {
            new RegistrationForm();
        });
        
        // Hàm lấy base URL
        function getBaseUrl() {
            const currentUrl = window.location.href;
            const baseUrl = currentUrl.substring(0, currentUrl.lastIndexOf('/loginlogout/'));
            return baseUrl;
        }
    </script>
</body>
</html>