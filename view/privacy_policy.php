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

    .policy-header {
        background: linear-gradient(135deg, #00b894, #00cec9);
        color: white;
        padding: 30px;
        margin-bottom: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 184, 148, 0.3);
        text-align: center;
    }

    .policy-header h1 {
        font-size: 2rem;
        font-weight: 700;
        margin: 0 0 10px 0;
        color: white;
    }

    .policy-header p {
        font-size: 1.1rem;
        margin: 0;
        color: rgba(255, 255, 255, 0.9);
    }

    .policy-section {
        margin-bottom: 30px;
    }

    .policy-section h2 {
        color: #333;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #00b894;
    }

    .policy-section h3 {
        color: #555;
        font-size: 1.2rem;
        font-weight: 600;
        margin-top: 25px;
        margin-bottom: 15px;
    }

    .policy-section p {
        color: #666;
        line-height: 1.8;
        margin-bottom: 15px;
    }

    .policy-section ul, .policy-section ol {
        color: #666;
        line-height: 1.8;
        margin-bottom: 15px;
        padding-left: 25px;
    }

    .policy-section li {
        margin-bottom: 10px;
    }

    .highlight-box {
        background: #e8f5e9;
        border-left: 4px solid #00b894;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 8px;
    }

    .highlight-box strong {
        color: #00b894;
        display: block;
        margin-bottom: 10px;
        font-size: 1.1rem;
    }

    .warning-box {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 8px;
    }

    .warning-box strong {
        color: #856404;
        display: block;
        margin-bottom: 10px;
        font-size: 1.1rem;
    }

    .update-info {
        background: #e7f3ff;
        border-left: 4px solid #007bff;
        padding: 20px;
        margin-top: 30px;
        border-radius: 8px;
    }

    .update-info p {
        color: #004085;
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

        .policy-header h1 {
            font-size: 1.5rem;
        }
    }
</style>

<!-- Page Background Start -->
<div class="page-background">
    <!-- Content Wrapper Start -->
    <div class="content-wrapper">

        <div class="policy-header">
            <h1><i class="fas fa-shield-alt mr-2"></i>Chính Sách Bảo Mật</h1>
            <p>Cam kết bảo vệ thông tin và quyền riêng tư của người dùng</p>
        </div>

        <div class="policy-section">
            <h2><i class="fas fa-info-circle mr-2"></i>Giới Thiệu</h2>
            <p>
                Chợ Việt cam kết bảo vệ quyền riêng tư và thông tin cá nhân của người dùng. 
                Chính sách bảo mật này mô tả cách chúng tôi thu thập, sử dụng, lưu trữ và bảo vệ thông tin của bạn 
                khi sử dụng nền tảng Chợ Việt.
            </p>
            <p>
                Bằng việc sử dụng dịch vụ của chúng tôi, bạn đồng ý với các điều khoản trong chính sách bảo mật này.
            </p>
        </div>

        <div class="policy-section">
            <h2><i class="fas fa-database mr-2"></i>Thông Tin Chúng Tôi Thu Thập</h2>
            
            <h3>1. Thông Tin Cá Nhân</h3>
            <ul>
                <li><strong>Thông tin đăng ký:</strong> Họ tên, email, số điện thoại, địa chỉ</li>
                <li><strong>Thông tin tài khoản:</strong> Tên đăng nhập, mật khẩu (được mã hóa), loại tài khoản</li>
                <li><strong>Thông tin thanh toán:</strong> Số tài khoản, thông tin ví điện tử (được mã hóa)</li>
                <li><strong>Thông tin giao dịch:</strong> Lịch sử mua bán, đơn hàng, đánh giá</li>
            </ul>

            <h3>2. Thông Tin Tự Động Thu Thập</h3>
            <ul>
                <li><strong>Thông tin thiết bị:</strong> Địa chỉ IP, loại trình duyệt, hệ điều hành</li>
                <li><strong>Thông tin sử dụng:</strong> Thời gian truy cập, trang đã xem, hành vi người dùng</li>
                <li><strong>Cookies:</strong> Để cải thiện trải nghiệm và phân tích dữ liệu</li>
            </ul>

            <h3>3. Thông Tin Từ Bên Thứ Ba</h3>
            <p>
                Chúng tôi có thể nhận thông tin từ các đối tác thanh toán (VNPay) và các dịch vụ tích hợp khác 
                để xử lý giao dịch và cải thiện dịch vụ.
            </p>
        </div>

        <div class="policy-section">
            <h2><i class="fas fa-cog mr-2"></i>Cách Chúng Tôi Sử Dụng Thông Tin</h2>
            
            <div class="highlight-box">
                <strong><i class="fas fa-check-circle mr-2"></i>Mục Đích Sử Dụng:</strong>
                <ul>
                    <li>Cung cấp và cải thiện dịch vụ của Chợ Việt</li>
                    <li>Xử lý giao dịch, thanh toán và đơn hàng</li>
                    <li>Gửi thông báo về tài khoản, đơn hàng, và cập nhật dịch vụ</li>
                    <li>Kiểm duyệt nội dung và đảm bảo an toàn cộng đồng</li>
                    <li>Phân tích và cải thiện trải nghiệm người dùng</li>
                    <li>Tuân thủ các yêu cầu pháp lý và giải quyết tranh chấp</li>
                </ul>
            </div>
        </div>

        <div class="policy-section">
            <h2><i class="fas fa-lock mr-2"></i>Bảo Mật Thông Tin</h2>
            
            <p>Chúng tôi áp dụng các biện pháp bảo mật tiên tiến để bảo vệ thông tin của bạn:</p>
            <ul>
                <li><strong>Mã hóa dữ liệu:</strong> Thông tin nhạy cảm được mã hóa bằng SSL/TLS</li>
                <li><strong>Bảo mật cơ sở dữ liệu:</strong> Hệ thống firewall và kiểm soát truy cập nghiêm ngặt</li>
                <li><strong>Mật khẩu:</strong> Được hash bằng thuật toán bảo mật cao, không lưu trữ dạng plain text</li>
                <li><strong>Kiểm tra bảo mật:</strong> Định kỳ kiểm tra và cập nhật hệ thống bảo mật</li>
                <li><strong>Đào tạo nhân viên:</strong> Nhân viên được đào tạo về bảo mật thông tin</li>
            </ul>

            <div class="warning-box">
                <strong><i class="fas fa-exclamation-triangle mr-2"></i>Lưu Ý:</strong>
                <p>
                    Mặc dù chúng tôi nỗ lực bảo vệ thông tin của bạn, không có phương thức truyền tải hoặc lưu trữ nào 
                    là 100% an toàn. Bạn cũng có trách nhiệm bảo vệ thông tin tài khoản của mình (mật khẩu, email).
                </p>
            </div>
        </div>

        <div class="policy-section">
            <h2><i class="fas fa-share-alt mr-2"></i>Chia Sẻ Thông Tin</h2>
            
            <p>Chúng tôi <strong>KHÔNG</strong> bán hoặc cho thuê thông tin cá nhân của bạn. Chúng tôi chỉ chia sẻ thông tin trong các trường hợp sau:</p>
            
            <h3>1. Với Người Dùng Khác</h3>
            <ul>
                <li>Tên hiển thị, đánh giá, và thông tin công khai trên trang cá nhân</li>
                <li>Thông tin liên hệ khi bạn tham gia giao dịch (để giao hàng, thanh toán)</li>
            </ul>

            <h3>2. Với Đối Tác Dịch Vụ</h3>
            <ul>
                <li><strong>VNPay:</strong> Thông tin thanh toán cần thiết để xử lý giao dịch</li>
                <li><strong>Dịch vụ hosting:</strong> Lưu trữ dữ liệu trên server an toàn</li>
                <li><strong>Dịch vụ email:</strong> Gửi thông báo và xác thực tài khoản</li>
            </ul>

            <h3>3. Yêu Cầu Pháp Lý</h3>
            <p>
                Chúng tôi có thể tiết lộ thông tin nếu được yêu cầu bởi cơ quan pháp luật, 
                hoặc để bảo vệ quyền lợi, tài sản, hoặc an toàn của Chợ Việt và người dùng.
            </p>
        </div>

        <div class="policy-section">
            <h2><i class="fas fa-user-cog mr-2"></i>Quyền Của Người Dùng</h2>
            
            <p>Bạn có các quyền sau đối với thông tin cá nhân của mình:</p>
            <ul>
                <li><strong>Truy cập:</strong> Xem và tải xuống thông tin cá nhân của bạn</li>
                <li><strong>Chỉnh sửa:</strong> Cập nhật thông tin trong trang cá nhân</li>
                <li><strong>Xóa:</strong> Yêu cầu xóa tài khoản và dữ liệu (theo quy định pháp luật)</li>
                <li><strong>Từ chối:</strong> Từ chối nhận email marketing (vẫn nhận email quan trọng về tài khoản)</li>
                <li><strong>Khiếu nại:</strong> Liên hệ với chúng tôi nếu có vấn đề về quyền riêng tư</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2><i class="fas fa-cookie mr-2"></i>Cookies và Công Nghệ Theo Dõi</h2>
            
            <p>Chúng tôi sử dụng cookies và công nghệ tương tự để:</p>
            <ul>
                <li>Ghi nhớ đăng nhập và tùy chọn của bạn</li>
                <li>Phân tích lưu lượng truy cập và cải thiện dịch vụ</li>
                <li>Cung cấp nội dung phù hợp với sở thích của bạn</li>
            </ul>
            <p>
                Bạn có thể tắt cookies trong cài đặt trình duyệt, nhưng điều này có thể ảnh hưởng đến một số chức năng của website.
            </p>
        </div>

        <div class="policy-section">
            <h2><i class="fas fa-child mr-2"></i>Bảo Vệ Trẻ Em</h2>
            <p>
                Chợ Việt không dành cho trẻ em dưới 18 tuổi. Chúng tôi không cố ý thu thập thông tin từ trẻ em. 
                Nếu phát hiện tài khoản của trẻ em, chúng tôi sẽ xóa ngay lập tức.
            </p>
        </div>

        <div class="policy-section">
            <h2><i class="fas fa-globe mr-2"></i>Thay Đổi Chính Sách</h2>
            <p>
                Chúng tôi có thể cập nhật chính sách bảo mật này theo thời gian. 
                Mọi thay đổi sẽ được thông báo trên website và có hiệu lực ngay sau khi đăng tải.
            </p>
            <p>
                Bạn nên xem lại chính sách này định kỳ để nắm được các thay đổi.
            </p>
        </div>

        <div class="policy-section">
            <h2><i class="fas fa-envelope mr-2"></i>Liên Hệ</h2>
            <p>Nếu bạn có câu hỏi về chính sách bảo mật này, vui lòng liên hệ:</p>
            <p><i class="fa fa-envelope text-primary mr-2"></i>Email: <a href="mailto:trogiup@choviet.vn">trogiup@choviet.vn</a></p>
            <p><i class="fa fa-phone-alt text-primary mr-2"></i>Hotline: <strong>19003003</strong> (1.000đ/phút)</p>
            <p><i class="fa fa-map-marker-alt text-primary mr-2"></i>Địa chỉ: Số 14 đường Nguyễn Văn Bảo, Phường 4, Quận Gò Vấp, TP. Hồ Chí Minh</p>
        </div>

        <div class="update-info">
            <p><strong>Cập nhật lần cuối:</strong> <?= date('d/m/Y') ?></p>
            <p><strong>Phiên bản:</strong> 1.0</p>
        </div>

    </div>
    <!-- Content Wrapper End -->
</div>
<!-- Page Background End -->

<?php
include_once("view/footer.php");
?>

