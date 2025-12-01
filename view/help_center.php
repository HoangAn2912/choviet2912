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

    .help-header {
        background: linear-gradient(135deg, #ffc107, #ff8f00);
        color: #333;
        padding: 30px;
        margin-bottom: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
        text-align: center;
    }

    .help-header h1 {
        font-size: 2rem;
        font-weight: 700;
        margin: 0 0 10px 0;
        color: #333;
    }

    .help-header p {
        font-size: 1.1rem;
        margin: 0;
        color: #555;
    }

    .help-section {
        margin-bottom: 30px;
    }

    .help-section h2 {
        color: #333;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #ffc107;
    }

    .help-section h3 {
        color: #555;
        font-size: 1.2rem;
        font-weight: 600;
        margin-top: 25px;
        margin-bottom: 15px;
    }

    .help-section p {
        color: #666;
        line-height: 1.8;
        margin-bottom: 15px;
    }

    .help-section ul {
        color: #666;
        line-height: 1.8;
        margin-bottom: 15px;
        padding-left: 25px;
    }

    .help-section li {
        margin-bottom: 10px;
    }

    .faq-item {
        background: #f8f9fa;
        border-left: 4px solid #ffc107;
        padding: 20px;
        margin-bottom: 15px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .faq-item:hover {
        background: #fff8e1;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .faq-question {
        font-weight: 600;
        color: #333;
        margin-bottom: 10px;
        font-size: 1.1rem;
    }

    .faq-answer {
        color: #666;
        line-height: 1.7;
    }

    .contact-box {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        padding: 25px;
        border-radius: 12px;
        margin-top: 30px;
        text-align: center;
    }

    .contact-box h3 {
        color: #333;
        margin-bottom: 15px;
    }

    .contact-box p {
        margin: 8px 0;
        color: #666;
    }

    .contact-box a {
        color: #ffc107;
        text-decoration: none;
        font-weight: 600;
    }

    .contact-box a:hover {
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        .page-background {
            padding: 0 1rem 1rem 1rem;
        }
        
        .content-wrapper {
            padding: 1.5rem;
            border-radius: 12px;
        }

        .help-header h1 {
            font-size: 1.5rem;
        }
    }
</style>

<!-- Page Background Start -->
<div class="page-background">
    <!-- Content Wrapper Start -->
    <div class="content-wrapper">

        <div class="help-header">
            <h1><i class="fas fa-question-circle mr-2"></i>Trung Tâm Trợ Giúp</h1>
            <p>Hướng dẫn sử dụng và giải đáp thắc mắc về Chợ Việt</p>
        </div>

        <div class="help-section">
            <h2><i class="fas fa-book mr-2"></i>Hướng Dẫn Sử Dụng</h2>
            
            <h3>1. Đăng ký và Đăng nhập</h3>
            <p>Để sử dụng các tính năng của Chợ Việt, bạn cần:</p>
            <ul>
                <li>Tạo tài khoản bằng email hoặc số điện thoại</li>
                <li>Xác thực tài khoản qua email/SMS</li>
                <li>Chọn loại tài khoản: <strong>Cá nhân</strong> hoặc <strong>Doanh nghiệp</strong></li>
                <li>Hoàn thiện thông tin cá nhân trong trang cá nhân</li>
            </ul>

            <h3>2. Đăng Tin Bán Hàng</h3>
            <p>Để đăng tin bán hàng trên Chợ Việt:</p>
            <ul>
                <li>Nhấn vào nút <strong>"Đăng tin"</strong> trên thanh menu</li>
                <li>Điền đầy đủ thông tin sản phẩm: tên, mô tả, giá, danh mục</li>
                <li>Upload từ 2-6 hình ảnh sản phẩm (chất lượng tốt, rõ ràng)</li>
                <li>Chọn trạng thái sản phẩm: <strong>Đang bán</strong>, <strong>Đã bán</strong>, hoặc <strong>Ẩn sản phẩm</strong></li>
                <li>Nhấn <strong>"Đăng tin"</strong> và chờ hệ thống duyệt (thường trong 24 giờ)</li>
            </ul>

            <h3>3. Livestream Bán Hàng</h3>
            <p>Chức năng livestream chỉ dành cho tài khoản <strong>Doanh nghiệp</strong>:</p>
            <ul>
                <li>Nâng cấp tài khoản lên <strong>Doanh nghiệp</strong> (nếu chưa có)</li>
                <li>Mua gói livestream phù hợp (Thử nghiệm, Phổ biến, hoặc VIP)</li>
                <li>Tạo livestream mới và thêm sản phẩm vào giỏ hàng</li>
                <li>Bắt đầu phát sóng và tương tác với khách hàng qua chat</li>
                <li>Ghim sản phẩm nổi bật để khách hàng dễ thấy</li>
                <li>Xử lý đơn hàng trực tiếp trong livestream</li>
            </ul>

            <h3>4. Mua Hàng</h3>
            <p>Để mua hàng trên Chợ Việt:</p>
            <ul>
                <li>Tìm kiếm sản phẩm theo danh mục hoặc từ khóa</li>
                <li>Xem chi tiết sản phẩm và hình ảnh</li>
                <li>Liên hệ người bán qua tin nhắn để thương lượng</li>
                <li>Trong livestream: thêm vào giỏ hàng và đặt hàng trực tiếp</li>
                <li>Thanh toán qua ví nội bộ hoặc VNPay</li>
                <li>Nhận hàng và để lại đánh giá sau khi giao dịch thành công</li>
            </ul>

            <h3>5. Quản Lý Đơn Hàng</h3>
            <p>Người mua và người bán có thể:</p>
            <ul>
                <li>Xem danh sách đơn hàng trong trang <strong>"Đơn hàng của tôi"</strong></li>
                <li>Theo dõi trạng thái: <strong>Chờ xác nhận</strong>, <strong>Đã xác nhận</strong>, <strong>Đang giao hàng</strong>, <strong>Đã giao</strong>, <strong>Đã hủy</strong></li>
                <li>Hủy đơn hàng (nếu chưa được xác nhận)</li>
                <li>Xác nhận đã nhận hàng sau khi giao dịch thành công</li>
            </ul>
        </div>

        <div class="help-section">
            <h2><i class="fas fa-comments mr-2"></i>Câu Hỏi Thường Gặp (FAQ)</h2>
            
            <div class="faq-item">
                <div class="faq-question">Q: Tôi có thể đăng bao nhiêu tin bán hàng?</div>
                <div class="faq-answer">A: Tài khoản <strong>Cá nhân</strong> có thể đăng tối đa 20 tin miễn phí. Tài khoản <strong>Doanh nghiệp</strong> có thể đăng không giới hạn số lượng tin.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Q: Làm thế nào để livestream bán hàng?</div>
                <div class="faq-answer">A: Bạn cần có tài khoản <strong>Doanh nghiệp</strong> và mua gói livestream. Sau đó vào mục <strong>"Tạo livestream"</strong>, thêm sản phẩm và bắt đầu phát sóng.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Q: Phí livestream là bao nhiêu?</div>
                <div class="faq-answer">A: Chúng tôi có 3 gói: <strong>Gói Thử nghiệm</strong> (miễn phí 1 lần), <strong>Gói Phổ biến</strong> (50.000đ/tháng), và <strong>Gói VIP</strong> (200.000đ/tháng - không giới hạn).</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Q: Làm sao để nạp tiền vào ví?</div>
                <div class="faq-answer">A: Vào mục <strong>"Nạp tiền"</strong> trong trang cá nhân, chọn số tiền và phương thức thanh toán (VNPay). Sau khi thanh toán thành công, tiền sẽ được cộng vào ví của bạn.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Q: Tin đăng của tôi bị từ chối, tại sao?</div>
                <div class="faq-answer">A: Tin đăng có thể bị từ chối nếu: vi phạm chính sách cộng đồng, hình ảnh không rõ ràng, thông tin không đầy đủ, hoặc sản phẩm không phù hợp. Bạn sẽ nhận được thông báo lý do cụ thể.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Q: Tôi có thể chỉnh sửa tin đăng sau khi đã đăng không?</div>
                <div class="faq-answer">A: Có, bạn có thể vào <strong>"Quản lý bài viết"</strong>, chọn tin cần sửa và nhấn <strong>"Sửa tin"</strong>. Bạn có thể thay đổi thông tin, hình ảnh, giá cả, và trạng thái sản phẩm.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Q: Làm sao để liên hệ với người bán/người mua?</div>
                <div class="faq-answer">A: Bạn có thể nhấn vào nút <strong>"Chat"</strong> trên trang chi tiết sản phẩm hoặc trong livestream để gửi tin nhắn trực tiếp. Hệ thống sẽ thông báo khi có tin nhắn mới.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Q: Chợ Việt có hỗ trợ giao hàng không?</div>
                <div class="faq-answer">A: Hiện tại Chợ Việt là nền tảng kết nối người bán và người mua. Việc giao hàng và thanh toán được thỏa thuận trực tiếp giữa hai bên. Chúng tôi khuyến khích giao dịch an toàn qua hệ thống đơn hàng.</div>
            </div>
        </div>

        <div class="help-section">
            <h2><i class="fas fa-shield-alt mr-2"></i>Bảo Mật và An Toàn</h2>
            <p>Chợ Việt cam kết bảo vệ thông tin và quyền lợi của người dùng:</p>
            <ul>
                <li>Mã hóa thông tin cá nhân và giao dịch</li>
                <li>Kiểm duyệt nội dung trước khi hiển thị</li>
                <li>Hệ thống đánh giá và báo cáo người dùng</li>
                <li>Giải quyết tranh chấp nhanh chóng và công bằng</li>
                <li>Không chia sẻ thông tin với bên thứ ba</li>
            </ul>
        </div>

        <div class="contact-box">
            <h3><i class="fas fa-headset mr-2"></i>Vẫn Cần Hỗ Trợ?</h3>
            <p>Nếu bạn không tìm thấy câu trả lời, vui lòng liên hệ với chúng tôi:</p>
            <p><i class="fa fa-envelope text-primary mr-2"></i>Email: <a href="mailto:trogiup@choviet.vn">trogiup@choviet.vn</a></p>
            <p><i class="fa fa-phone-alt text-primary mr-2"></i>Hotline: <strong>19003003</strong> (1.000đ/phút)</p>
            <p><i class="fa fa-clock text-primary mr-2"></i>Thời gian hỗ trợ: 8:00 - 22:00 hàng ngày</p>
        </div>

    </div>
    <!-- Content Wrapper End -->
</div>
<!-- Page Background End -->

<?php
include_once("view/footer.php");
?>

