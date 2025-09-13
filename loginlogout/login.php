<?php
include_once("controller/cLoginLogout.php");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Chợ Việt</title>
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
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
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
            box-sizing: border-box;
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
        
        .hidden {
            display: none;
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }
        
        .forgot-password a {
            color: #fcb50e;
            text-decoration: none;
            font-size: 14px;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
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
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            z-index: 1000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
        }
        
        .toast.show {
            transform: translateX(0);
        }
        
        .toast.success {
            background: #28a745;
        }
        
        .toast.error {
            background: #dc3545;
        }
        
        .toast.info {
            background: #17a2b8;
        }
        
        .form-title {
            text-align: center;
            color: #fcb50e;
            margin-bottom: 30px;
            font-size: 24px;
            font-weight: bold;
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
        
        .otp-help {
            font-size: 14px;
            color: #666;
            margin-top: 10px;
            text-align: center;
    }
        
        /* Password field + toggle (align with signup) */
        .form-group input[type="password"] {
            padding-right: 40px;
        }
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-30%) translateY(6px);
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

        /* Login method toggle */
        .login-method {
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: nowrap;
        }
        .login-method label {
            margin: 0;
            font-weight: 500;
            color: #444;
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }
  </style>
</head>
<body>
    <div class="form-container">
        <!-- Form đăng nhập -->
        <div id="loginForm">
            <h2 style="text-align: center; color: #fcb50e; margin-bottom: 30px;">Đăng nhập</h2>
            
            <form id="loginFormElement">
                <div class="form-group">
                    <label>Phương thức đăng nhập</label>
                    <div class="login-method">
                        <label><input type="radio" name="loginMethod" value="email" checked> Email</label>
                        <label><input type="radio" name="loginMethod" value="username"> Tên đăng nhập</label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="loginEmail">Email hoặc Tên đăng nhập *</label>
                    <input 
                    placeholder="Ví dụ: choviet123@example.com"
                    type="text" id="loginEmail" name="email" required>
      </div>

                <div class="form-group">
                    <label for="loginPassword">Mật khẩu *</label>
                    <input 
                    placeholder="Ví dụ: Choviet123@"
                    type="password" id="loginPassword" name="password" required>
                    <span class="password-toggle" id="loginPasswordToggle" title="Hiển thị/Ẩn mật khẩu">
                        <svg width="20" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z" stroke="currentColor" stroke-width="2" fill="none"/>
                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" fill="none"/>
                            <circle cx="12" cy="12" r="1" fill="currentColor"/>
                        </svg>
                    </span>
        </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        Đăng nhập
                    </button>
                </div>
            </form>
            
            <div class="forgot-password">
                <a href="#" id="forgotPasswordLink">Quên mật khẩu?</a>
            </div>

            <div class="form-switch">
                <a href="index.php?signup">Chưa có tài khoản? Đăng ký ngay</a>
            </div>
            </div>

        <!-- Form quên mật khẩu -->
        <div id="forgotPasswordForm" class="hidden">
            <h2 class="form-title">Quên mật khẩu</h2>
            
            <form id="forgotPasswordFormElement">
                <div class="form-group">
                    <label for="forgotEmail">Email *</label>
                    <input type="email" id="forgotEmail" name="email" required>
                </div>
                
                <div class="form-group">
                    <button type="button" id="sendResetOtpBtn" class="btn btn-primary">
                        Gửi mã OTP
                    </button>
                </div>
          </form>
            
            <div class="form-switch">
                <a href="#" id="backToLoginLink">Quay lại đăng nhập</a>
        </div>
      </div>

        <!-- Form nhập OTP và mật khẩu mới -->
        <div id="resetPasswordForm" class="hidden">
            <h2 class="form-title">Đặt lại mật khẩu</h2>
            
            <form id="resetPasswordFormElement">
                <div class="form-group">
                    <label for="resetOtp">Mã OTP *</label>
                    <input type="text" id="resetOtp" name="otp" maxlength="6" placeholder="Nhập 6 chữ số" required>
                    <div class="otp-help">Mã OTP đã được gửi đến email của bạn</div>
                </div>
                
                <div class="form-group">
                    <label for="newPassword">Mật khẩu mới *</label>
                    <input type="password" id="newPassword" name="newPassword" required>
                    <span class="password-toggle" id="newPasswordToggle" title="Hiển thị/Ẩn mật khẩu">
                        <svg width="20" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z" stroke="currentColor" stroke-width="2" fill="none"/>
                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" fill="none"/>
                            <circle cx="12" cy="12" r="1" fill="currentColor"/>
                        </svg>
                    </span>
                </div>
                
                <div class="form-group">
                    <label for="confirmNewPassword">Nhập lại mật khẩu mới *</label>
                    <input type="password" id="confirmNewPassword" name="confirmNewPassword" required>
                    <span class="password-toggle" id="confirmNewPasswordToggle" title="Hiển thị/Ẩn mật khẩu">
                        <svg width="20" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z" stroke="currentColor" stroke-width="2" fill="none"/>
                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" fill="none"/>
                            <circle cx="12" cy="12" r="1" fill="currentColor"/>
                        </svg>
                    </span>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        Đặt lại mật khẩu
                    </button>
                </div>
            </form>
            
            <div class="form-switch">
                <a href="#" id="backToForgotLink">Quay lại quên mật khẩu</a>
        </div>
    </div>
  </div>

    <script>
        /**
         * Class quản lý form đăng nhập và quên mật khẩu
         */
        class LoginForm {
            constructor() {
                this.initializeElements();
                this.bindEvents();
                this.currentForm = 'login';
            }
            
            /**
             * Khởi tạo các phần tử DOM
             */
            initializeElements() {
                // Forms
                this.loginForm = document.getElementById('loginForm');
                this.forgotPasswordForm = document.getElementById('forgotPasswordForm');
                this.resetPasswordForm = document.getElementById('resetPasswordForm');
                
                // Form elements
                this.loginFormElement = document.getElementById('loginFormElement');
                this.forgotPasswordFormElement = document.getElementById('forgotPasswordFormElement');
                this.resetPasswordFormElement = document.getElementById('resetPasswordFormElement');
                
                // Inputs
                this.loginEmail = document.getElementById('loginEmail');
                this.loginMethodRadios = document.querySelectorAll('input[name="loginMethod"]');
                this.loginPassword = document.getElementById('loginPassword');
                this.forgotEmail = document.getElementById('forgotEmail');
                this.resetOtp = document.getElementById('resetOtp');
                this.newPassword = document.getElementById('newPassword');
                this.confirmNewPassword = document.getElementById('confirmNewPassword');
                // Password toggle icons
                this.loginPasswordToggle = document.getElementById('loginPasswordToggle');
                this.newPasswordToggle = document.getElementById('newPasswordToggle');
                this.confirmNewPasswordToggle = document.getElementById('confirmNewPasswordToggle');
                
                // Buttons
                this.sendResetOtpBtn = document.getElementById('sendResetOtpBtn');
                
                // Links
                this.forgotPasswordLink = document.getElementById('forgotPasswordLink');
                this.backToLoginLink = document.getElementById('backToLoginLink');
                this.backToForgotLink = document.getElementById('backToForgotLink');
            }
            
            /**
             * Gắn các sự kiện
             */
            bindEvents() {
                // Đăng nhập
                this.loginFormElement.addEventListener('submit', (e) => this.handleLogin(e));
                // Chuyển đổi phương thức đăng nhập (email <-> username)
                this.loginMethodRadios.forEach(r => r.addEventListener('change', () => this.updateLoginMethod()))
                
                // Quên mật khẩu
                this.forgotPasswordLink.addEventListener('click', (e) => this.showForgotPassword(e));
                this.backToLoginLink.addEventListener('click', (e) => this.showLogin(e));
                this.sendResetOtpBtn.addEventListener('click', () => this.handleSendResetOTP());
                
                // Đặt lại mật khẩu
                this.resetPasswordFormElement.addEventListener('submit', (e) => this.handleResetPassword(e));
                this.backToForgotLink.addEventListener('click', (e) => this.showForgotPassword(e));
                
                // Validation real-time
                this.loginEmail.addEventListener('input', () => {
                    this.validateField(this.loginEmail);
                });
                this.loginPassword.addEventListener('input', () => {
                    this.validateField(this.loginPassword);
                });
                this.forgotEmail.addEventListener('input', () => this.validateField(this.forgotEmail));
                this.resetOtp.addEventListener('input', () => this.validateField(this.resetOtp));
                this.newPassword.addEventListener('input', () => this.validateField(this.newPassword));
                this.confirmNewPassword.addEventListener('input', () => this.validateField(this.confirmNewPassword));

                // Toggle password visibility
                if (this.loginPasswordToggle) {
                    this.loginPasswordToggle.addEventListener('click', () => this.togglePasswordVisibility(this.loginPassword, this.loginPasswordToggle));
                }
                if (this.newPasswordToggle) {
                    this.newPasswordToggle.addEventListener('click', () => this.togglePasswordVisibility(this.newPassword, this.newPasswordToggle));
                }
                if (this.confirmNewPasswordToggle) {
                    this.confirmNewPasswordToggle.addEventListener('click', () => this.togglePasswordVisibility(this.confirmNewPassword, this.confirmNewPasswordToggle));
                }
            }

            /**
             * Cập nhật kiểu input/placeholder theo phương thức đăng nhập
             */
            updateLoginMethod() {
                const method = [...this.loginMethodRadios].find(r => r.checked)?.value || 'email';
                if (method === 'email') {
                    this.loginEmail.type = 'email';
                    this.loginEmail.placeholder = 'Ví dụ: choviet123@example.com';
                } else {
                    this.loginEmail.type = 'text';
                    this.loginEmail.placeholder = 'Ví dụ: choviet123';
                }
                // Reset trạng thái lỗi/thành công khi chuyển phương thức
                this.loginEmail.classList.remove('error', 'success');
            }
            
            /**
             * Xử lý đăng nhập
             */
            async handleLogin(event) {
                event.preventDefault();
                
                if (!this.validateForm(this.loginFormElement)) {
                    return;
                }
                
                try {
                    const formData = new FormData();
                    formData.append('login', '1');
                    formData.append('email', this.loginEmail.value);
                    formData.append('password', this.loginPassword.value);
                    
                    const response = await fetch('controller/cLoginLogout.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    // Kiểm tra response type
                    const contentType = response.headers.get('content-type');
                    
                    if (contentType && contentType.includes('application/json')) {
                        // Response là JSON (có lỗi)
                        const result = await response.json();
                        this.showToast(result.message, 'error');
                    } else {
                        // Response là redirect (thành công)
                        window.location.href = response.url;
                    }
                    
                } catch (error) {
                    console.error('Lỗi đăng nhập:', error);
                    this.showToast('Có lỗi xảy ra khi đăng nhập', 'error');
                }
            }
            
            /**
             * Hiển thị form quên mật khẩu
             */
            showForgotPassword(event) {
                event.preventDefault();
                this.currentForm = 'forgot';
                this.loginForm.classList.add('hidden');
                this.forgotPasswordForm.classList.remove('hidden');
                this.resetPasswordForm.classList.add('hidden');
                this.forgotEmail.focus();
            }
            
            /**
             * Hiển thị form đăng nhập
             */
            showLogin(event) {
                event.preventDefault();
                this.currentForm = 'login';
                this.loginForm.classList.remove('hidden');
                this.forgotPasswordForm.classList.add('hidden');
                this.resetPasswordForm.classList.add('hidden');
                this.loginEmail.focus();
            }
            
            /**
             * Xử lý gửi OTP để đặt lại mật khẩu
             */
            async handleSendResetOTP() {
                if (!this.validateField(this.forgotEmail)) {
                    this.showToast('Vui lòng nhập email hợp lệ', 'error');
                    return;
                }
                
                try {
                    this.sendResetOtpBtn.disabled = true;
                    this.sendResetOtpBtn.textContent = 'Đang gửi...';
                    
                    const response = await fetch('controller/cOtp.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'send_reset_otp',
                            method: 'email',
                            contact: this.forgotEmail.value
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        this.showResetPasswordForm();
                        this.showToast(result.message, 'success');
                    } else {
                        this.showToast(result.message, 'error');
                    }
                    
                } catch (error) {
                    console.error('Lỗi gửi OTP:', error);
                    this.showToast('Có lỗi xảy ra khi gửi OTP', 'error');
                } finally {
                    this.sendResetOtpBtn.disabled = false;
                    this.sendResetOtpBtn.textContent = 'Gửi mã OTP';
                }
            }
            
            /**
             * Hiển thị form đặt lại mật khẩu
             */
            showResetPasswordForm() {
                this.currentForm = 'reset';
                this.loginForm.classList.add('hidden');
                this.forgotPasswordForm.classList.add('hidden');
                this.resetPasswordForm.classList.remove('hidden');
                this.resetOtp.focus();
            }
            
            /**
             * Xử lý đặt lại mật khẩu
             */
            async handleResetPassword(event) {
                event.preventDefault();
                
                if (!this.validateForm(this.resetPasswordFormElement)) {
                    return;
                }
                
                // Kiểm tra mật khẩu mới
                if (this.newPassword.value !== this.confirmNewPassword.value) {
                    this.showToast('Mật khẩu nhập lại không khớp', 'error');
                    return;
                }
                
                try {
                    const formData = new FormData();
                    formData.append('reset_password', '1');
                    formData.append('email', this.forgotEmail.value);
                    formData.append('otp', this.resetOtp.value);
                    formData.append('new_password', this.newPassword.value);
                    
                    const response = await fetch('controller/cLoginLogout.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        this.showToast('Đặt lại mật khẩu thành công!', 'success');
                        setTimeout(() => this.showLogin(event), 2000);
                    } else {
                        this.showToast(result.message, 'error');
                    }
                    
                } catch (error) {
                    console.error('Lỗi đặt lại mật khẩu:', error);
                    this.showToast('Có lỗi xảy ra khi đặt lại mật khẩu', 'error');
                }
            }
            
            /**
             * Kiểm tra form
             */
            validateForm(form) {
                const inputs = form.querySelectorAll('input[required]');
                let isValid = true;
                
                inputs.forEach(input => {
                    if (!this.validateField(input)) {
                        isValid = false;
                    }
                });
                
                return isValid;
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
                
                // Kiểm tra email
                if (field.type === 'email' && value && !this.isValidEmail(value)) {
                    field.classList.add('error');
                    isValid = false;
                }
                
                // Kiểm tra mật khẩu
                if (field === this.newPassword && value && value.length < 6) {
                    field.classList.add('error');
                    isValid = false;
                }
                
                // Kiểm tra nhập lại mật khẩu
                if (field === this.confirmNewPassword && value && value !== this.newPassword.value) {
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
             * Bật/tắt hiển thị mật khẩu (giống trang đăng ký)
             */
            togglePasswordVisibility(passwordField, toggleButton) {
                if (!passwordField || !toggleButton) return;
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
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
            showToast(message, type = 'info') {
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                toast.textContent = message;
                
                document.body.appendChild(toast);
                
                // Hiển thị toast
                setTimeout(() => toast.classList.add('show'), 100);
                
                // Ẩn toast sau 5 giây
                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => document.body.removeChild(toast), 300);
                }, 5000);
            }
            
            /**
             * Hiển thị lỗi đăng nhập
             */
            showLoginError(message) {
                this.loginError.textContent = message;
                this.loginError.classList.remove('hidden');
                this.loginError.classList.add('show');
                
                // Ẩn error message sau 5 giây
                setTimeout(() => {
                    this.hideLoginError();
                }, 5000);
            }
            
            /**
             * Ẩn lỗi đăng nhập
             */
            hideLoginError() {
                this.loginError.classList.remove('show');
                this.loginError.classList.add('hidden');
            }
        }
        
        // Khởi tạo form khi trang đã tải xong
        document.addEventListener('DOMContentLoaded', () => {
            const lf = new LoginForm();
            lf.updateLoginMethod();
        });
    </script>
</body>
</html>
