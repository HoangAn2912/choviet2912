<?php
include_once("view/header.php");
?>

<style>
    /* Page Background - Gradient nhẹ */
    .page-background {
        background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
        min-height: calc(100vh - 180px);
        padding: 0 2rem 2rem 2rem;
    }

    /* Content wrapper - Khối trắng bên trong */
    .content-wrapper {
        background: #ffffff;
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem;
        border-radius: 16px;
        box-shadow: 0 6px 30px rgba(0, 0, 0, 0.12);
    }
    
    /* Bỏ padding của container bên trong content-wrapper */
    .content-wrapper .container,
    .content-wrapper .container-fluid {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .contact-header {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        padding: 30px;
        margin-bottom: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
        text-align: center;
    }

    .contact-header h1 {
        font-size: 2rem;
        font-weight: 700;
        margin: 0 0 10px 0;
        color: white;
    }

    .contact-header p {
        font-size: 1.1rem;
        margin: 0;
        color: rgba(255, 255, 255, 0.9);
    }

    .contact-section {
        margin-bottom: 30px;
    }

    .contact-section h2 {
        color: #333;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #007bff;
    }

    .contact-methods {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .contact-card {
        background: #f8f9fa;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 25px;
        text-align: center;
        transition: all 0.3s ease;
    }

    .contact-card:hover {
        border-color: #007bff;
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.2);
        transform: translateY(-3px);
    }

    .contact-card i {
        font-size: 2.5rem;
        color: #007bff;
        margin-bottom: 15px;
    }

    .contact-card h3 {
        color: #333;
        font-size: 1.3rem;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .contact-card p {
        color: #666;
        margin: 8px 0;
        line-height: 1.6;
    }

    .contact-card a {
        color: #007bff;
        text-decoration: none;
        font-weight: 600;
        display: inline-block;
        margin-top: 10px;
    }

    .contact-card a:hover {
        text-decoration: underline;
    }

    .contact-form {
        background: #f8f9fa;
        padding: 30px;
        border-radius: 12px;
        margin-top: 30px;
    }

    .contact-form h3 {
        color: #333;
        margin-bottom: 20px;
        font-size: 1.3rem;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        color: #333;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 120px;
    }

    .btn-submit {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-submit:hover {
        background: linear-gradient(135deg, #0056b3, #004085);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
    }

    .info-box {
        background: #e7f3ff;
        border-left: 4px solid #007bff;
        padding: 20px;
        margin-top: 20px;
        border-radius: 8px;
    }

    .info-box p {
        color: #004085;
        margin: 5px 0;
        line-height: 1.6;
    }

    .hours-box {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 20px;
        margin-top: 20px;
        border-radius: 8px;
    }

    .hours-box strong {
        color: #856404;
        display: block;
        margin-bottom: 10px;
    }

    .hours-box p {
        color: #856404;
        margin: 5px 0;
    }

    @media (max-width: 768px) {
        .page-background {
            padding: 0 1rem 1rem 1rem;
        }
        
        .content-wrapper {
            padding: 1.5rem;
            border-radius: 12px;
        }

        .contact-header h1 {
            font-size: 1.5rem;
        }

        .contact-methods {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Page Background Start -->
<div class="page-background">
    <!-- Content Wrapper Start -->
    <div class="content-wrapper">

        <div class="contact-header">
            <h1><i class="fas fa-headset mr-2"></i>Liên Hệ Hỗ Trợ</h1>
            <p>Chúng tôi luôn sẵn sàng hỗ trợ bạn mọi lúc, mọi nơi</p>
        </div>

        <div class="contact-section">
            <h2><i class="fas fa-phone-alt mr-2"></i>Thông Tin Liên Hệ</h2>
            
            <div class="contact-methods">
                <div class="contact-card">
                    <i class="fas fa-phone"></i>
                    <h3>Hotline</h3>
                    <p><strong>19003003</strong></p>
                    <p>Phí: 1.000đ/phút</p>
                    <p>Hỗ trợ 24/7</p>
                </div>

                <div class="contact-card">
                    <i class="fas fa-envelope"></i>
                    <h3>Email</h3>
                    <p><a href="mailto:trogiup@choviet.vn">trogiup@choviet.vn</a></p>
                    <p>Phản hồi trong 24h</p>
                </div>

                <div class="contact-card">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>Địa Chỉ</h3>
                    <p>Số 14 đường Nguyễn Văn Bảo</p>
                    <p>Phường 4, Quận Gò Vấp</p>
                    <p>TP. Hồ Chí Minh</p>
                </div>
            </div>
        </div>

        <div class="contact-section">
            <h2><i class="fas fa-paper-plane mr-2"></i>Gửi Yêu Cầu Hỗ Trợ</h2>
            
            <div class="contact-form">
                <h3>Điền thông tin để chúng tôi có thể hỗ trợ bạn tốt nhất</h3>
                
                <form id="supportForm" method="POST" action="mailto:trogiup@choviet.vn" enctype="text/plain">
                    <div class="form-group">
                        <label for="name">Họ và Tên *</label>
                        <input type="text" id="name" name="name" required placeholder="Nhập họ và tên của bạn">
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required placeholder="email@example.com">
                    </div>

                    <div class="form-group">
                        <label for="phone">Số Điện Thoại</label>
                        <input type="tel" id="phone" name="phone" placeholder="0123456789">
                    </div>

                    <div class="form-group">
                        <label for="subject">Chủ Đề *</label>
                        <select id="subject" name="subject" required>
                            <option value="">-- Chọn chủ đề --</option>
                            <option value="dang-tin">Vấn đề về đăng tin</option>
                            <option value="livestream">Vấn đề về livestream</option>
                            <option value="don-hang">Vấn đề về đơn hàng</option>
                            <option value="thanh-toan">Vấn đề về thanh toán</option>
                            <option value="tai-khoan">Vấn đề về tài khoản</option>
                            <option value="bao-cao">Báo cáo lừa đảo/vi phạm</option>
                            <option value="khac">Khác</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message">Nội Dung *</label>
                        <textarea id="message" name="message" required placeholder="Mô tả chi tiết vấn đề bạn gặp phải..."></textarea>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane mr-2"></i>Gửi Yêu Cầu
                    </button>
                </form>

                <div class="info-box">
                    <p><strong><i class="fas fa-info-circle mr-2"></i>Lưu ý:</strong></p>
                    <p>• Vui lòng điền đầy đủ thông tin để chúng tôi có thể liên hệ và hỗ trợ bạn nhanh chóng.</p>
                    <p>• Nếu bạn đang gặp vấn đề với đơn hàng, vui lòng cung cấp mã đơn hàng trong nội dung.</p>
                    <p>• Đối với báo cáo lừa đảo, vui lòng đính kèm bằng chứng (ảnh chụp màn hình, tin nhắn).</p>
                </div>
            </div>
        </div>

        <div class="contact-section">
            <h2><i class="fas fa-clock mr-2"></i>Thời Gian Hỗ Trợ</h2>
            
            <div class="hours-box">
                <strong><i class="fas fa-calendar-alt mr-2"></i>Giờ Làm Việc:</strong>
                <p><strong>Thứ 2 - Chủ Nhật:</strong> 8:00 - 22:00</p>
                <p><strong>Hotline 24/7:</strong> Luôn sẵn sàng hỗ trợ khẩn cấp</p>
                <p><strong>Email:</strong> Phản hồi trong vòng 24 giờ</p>
            </div>
        </div>

        <div class="contact-section">
            <h2><i class="fas fa-question-circle mr-2"></i>Các Câu Hỏi Thường Gặp</h2>
            
            <div style="margin-top: 20px;">
                <p><strong>Q: Tôi có thể gọi hotline miễn phí không?</strong></p>
                <p>A: Hotline 19003003 có phí 1.000đ/phút. Tuy nhiên, bạn có thể gửi email miễn phí và chúng tôi sẽ phản hồi trong 24 giờ.</p>
                
                <p style="margin-top: 20px;"><strong>Q: Tôi cần hỗ trợ khẩn cấp, làm thế nào?</strong></p>
                <p>A: Vui lòng gọi hotline 19003003 (24/7) hoặc gửi email với tiêu đề "KHẨN CẤP" để được ưu tiên xử lý.</p>
                
                <p style="margin-top: 20px;"><strong>Q: Tôi muốn gặp trực tiếp tại văn phòng có được không?</strong></p>
                <p>A: Có, bạn có thể đến văn phòng tại địa chỉ: Số 14 đường Nguyễn Văn Bảo, Phường 4, Quận Gò Vấp, TP. Hồ Chí Minh. Vui lòng liên hệ trước qua email để đặt lịch hẹn.</p>
            </div>
        </div>

    </div>
    <!-- Content Wrapper End -->
</div>
<!-- Page Background End -->

<script>
document.getElementById('supportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phone').value;
    const subject = document.getElementById('subject').value;
    const message = document.getElementById('message').value;
    
    const subjectText = {
        'dang-tin': 'Vấn đề về đăng tin',
        'livestream': 'Vấn đề về livestream',
        'don-hang': 'Vấn đề về đơn hàng',
        'thanh-toan': 'Vấn đề về thanh toán',
        'tai-khoan': 'Vấn đề về tài khoản',
        'bao-cao': 'Báo cáo lừa đảo/vi phạm',
        'khac': 'Khác'
    };
    
    const emailBody = `Họ và tên: ${name}\nEmail: ${email}\nSố điện thoại: ${phone}\nChủ đề: ${subjectText[subject] || subject}\n\nNội dung:\n${message}`;
    
    window.location.href = `mailto:trogiup@choviet.vn?subject=${encodeURIComponent('Yêu cầu hỗ trợ - ' + (subjectText[subject] || subject))}&body=${encodeURIComponent(emailBody)}`;
    
    alert('Đang mở ứng dụng email của bạn. Vui lòng kiểm tra và gửi email.');
});
</script>

<?php
include_once("view/footer.php");
?>

