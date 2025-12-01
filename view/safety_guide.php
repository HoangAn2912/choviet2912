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

    .safety-header {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        padding: 30px;
        margin-bottom: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        text-align: center;
    }

    .safety-header h1 {
        font-size: 2rem;
        font-weight: 700;
        margin: 0 0 10px 0;
        color: white;
    }

    .safety-header p {
        font-size: 1.1rem;
        margin: 0;
        color: rgba(255, 255, 255, 0.9);
    }

    .safety-section {
        margin-bottom: 30px;
    }

    .safety-section h2 {
        color: #333;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #28a745;
    }

    .safety-section h3 {
        color: #555;
        font-size: 1.2rem;
        font-weight: 600;
        margin-top: 25px;
        margin-bottom: 15px;
    }

    .safety-section p {
        color: #666;
        line-height: 1.8;
        margin-bottom: 15px;
    }

    .safety-section ul {
        color: #666;
        line-height: 1.8;
        margin-bottom: 15px;
        padding-left: 25px;
    }

    .safety-section li {
        margin-bottom: 10px;
    }

    .tip-box {
        background: #e8f5e9;
        border-left: 4px solid #28a745;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 8px;
    }

    .tip-box strong {
        color: #28a745;
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

    .danger-box {
        background: #f8d7da;
        border-left: 4px solid #dc3545;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 8px;
    }

    .danger-box strong {
        color: #721c24;
        display: block;
        margin-bottom: 10px;
        font-size: 1.1rem;
    }

    .checklist-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 15px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .checklist-item:hover {
        background: #e9ecef;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .checklist-item i {
        color: #28a745;
        font-size: 1.2rem;
        margin-right: 15px;
        margin-top: 3px;
    }

    .checklist-content {
        flex: 1;
    }

    .checklist-content strong {
        color: #333;
        display: block;
        margin-bottom: 5px;
    }

    .checklist-content p {
        color: #666;
        margin: 0;
        line-height: 1.6;
    }

    @media (max-width: 768px) {
        .page-background {
            padding: 0 1rem 1rem 1rem;
        }
        
        .content-wrapper {
            padding: 1.5rem;
            border-radius: 12px;
        }

        .safety-header h1 {
            font-size: 1.5rem;
        }
    }
</style>

<!-- Page Background Start -->
<div class="page-background">
    <!-- Content Wrapper Start -->
    <div class="content-wrapper">

        <div class="safety-header">
            <h1><i class="fas fa-shield-alt mr-2"></i>An Toàn Mua Bán</h1>
            <p>Hướng dẫn giao dịch an toàn và bảo vệ quyền lợi của bạn</p>
        </div>

        <div class="safety-section">
            <h2><i class="fas fa-user-shield mr-2"></i>Trước Khi Giao Dịch</h2>
            
            <div class="checklist-item">
                <i class="fas fa-check-circle"></i>
                <div class="checklist-content">
                    <strong>Kiểm tra thông tin người bán/người mua</strong>
                    <p>Xem đánh giá, số lượng giao dịch thành công, và thời gian tham gia trên nền tảng. Tài khoản có nhiều đánh giá tích cực thường đáng tin cậy hơn.</p>
                </div>
            </div>

            <div class="checklist-item">
                <i class="fas fa-check-circle"></i>
                <div class="checklist-content">
                    <strong>Xem kỹ hình ảnh và mô tả sản phẩm</strong>
                    <p>Yêu cầu người bán cung cấp thêm hình ảnh nếu cần. Kiểm tra mô tả về tình trạng sản phẩm, phụ kiện đi kèm, và các khuyết điểm (nếu có).</p>
                </div>
            </div>

            <div class="checklist-item">
                <i class="fas fa-check-circle"></i>
                <div class="checklist-content">
                    <strong>Thương lượng giá và điều kiện giao hàng</strong>
                    <p>Trao đổi rõ ràng về giá cả, phương thức thanh toán, địa điểm giao hàng, và thời gian giao nhận. Lưu lại toàn bộ cuộc trò chuyện.</p>
                </div>
            </div>

            <div class="checklist-item">
                <i class="fas fa-check-circle"></i>
                <div class="checklist-content">
                    <strong>Yêu cầu hóa đơn hoặc giấy tờ liên quan</strong>
                    <p>Đối với sản phẩm có giá trị cao (xe cộ, điện tử), yêu cầu xem giấy tờ chứng minh quyền sở hữu, hóa đơn mua hàng, hoặc bảo hành (nếu còn).</p>
                </div>
            </div>
        </div>

        <div class="safety-section">
            <h2><i class="fas fa-handshake mr-2"></i>Trong Quá Trình Giao Dịch</h2>
            
            <div class="tip-box">
                <strong><i class="fas fa-lightbulb mr-2"></i>Mẹo An Toàn:</strong>
                <ul>
                    <li><strong>Gặp mặt trực tiếp:</strong> Ưu tiên gặp mặt tại nơi công cộng, đông người để kiểm tra sản phẩm trước khi thanh toán.</li>
                    <li><strong>Thanh toán an toàn:</strong> Sử dụng hệ thống đơn hàng của Chợ Việt để được bảo vệ. Tránh chuyển khoản trước khi nhận hàng.</li>
                    <li><strong>Kiểm tra kỹ sản phẩm:</strong> Kiểm tra tất cả chức năng, phụ kiện, và tình trạng thực tế trước khi thanh toán.</li>
                    <li><strong>Giữ lại bằng chứng:</strong> Chụp ảnh sản phẩm, lưu lại tin nhắn, và giữ hóa đơn (nếu có) để làm bằng chứng nếu có tranh chấp.</li>
                </ul>
            </div>

            <div class="warning-box">
                <strong><i class="fas fa-exclamation-triangle mr-2"></i>Cảnh Báo:</strong>
                <ul>
                    <li>Không chuyển khoản trước khi nhận hàng, đặc biệt với người bán chưa có đánh giá.</li>
                    <li>Không giao dịch bên ngoài nền tảng Chợ Việt (qua Zalo, Facebook riêng) vì không được bảo vệ.</li>
                    <li>Không cung cấp thông tin cá nhân nhạy cảm (CMND, thẻ ngân hàng) cho người khác.</li>
                    <li>Cẩn thận với các sản phẩm có giá quá rẻ so với thị trường - có thể là hàng giả, hàng nhái.</li>
                </ul>
            </div>
        </div>

        <div class="safety-section">
            <h2><i class="fas fa-exclamation-circle mr-2"></i>Nhận Biết Dấu Hiệu Lừa Đảo</h2>
            
            <div class="danger-box">
                <strong><i class="fas fa-ban mr-2"></i>Dấu Hiệu Đáng Nghi:</strong>
                <ul>
                    <li>Người bán yêu cầu chuyển khoản trước, không chịu gặp mặt hoặc giao hàng qua hệ thống.</li>
                    <li>Giá sản phẩm quá rẻ so với thị trường (ví dụ: iPhone mới 50% giá thị trường).</li>
                    <li>Tài khoản mới tạo, không có đánh giá, hoặc có nhiều đánh giá tiêu cực.</li>
                    <li>Hình ảnh sản phẩm không rõ ràng, copy từ nơi khác, hoặc không khớp với mô tả.</li>
                    <li>Yêu cầu cung cấp thông tin cá nhân, mật khẩu, hoặc mã OTP.</li>
                    <li>Liên tục thúc giục thanh toán nhanh, không cho thời gian suy nghĩ.</li>
                    <li>Yêu cầu giao dịch qua ứng dụng khác (Zalo, Telegram) thay vì trên Chợ Việt.</li>
                </ul>
            </div>
        </div>

        <div class="safety-section">
            <h2><i class="fas fa-gavel mr-2"></i>Giải Quyết Tranh Chấp</h2>
            
            <h3>Khi Có Vấn Đề Xảy Ra:</h3>
            <ol>
                <li><strong>Liên hệ trực tiếp:</strong> Trước tiên, hãy liên hệ với người bán/người mua để giải quyết một cách hòa bình.</li>
                <li><strong>Báo cáo lên hệ thống:</strong> Nếu không thể giải quyết, sử dụng tính năng "Báo cáo" trên trang sản phẩm hoặc tin nhắn.</li>
                <li><strong>Liên hệ hỗ trợ:</strong> Gửi email đến <strong>trogiup@choviet.vn</strong> hoặc gọi hotline <strong>19003003</strong> kèm theo:
                    <ul>
                        <li>Mã đơn hàng hoặc ID sản phẩm</li>
                        <li>Màn hình cuộc trò chuyện</li>
                        <li>Hình ảnh bằng chứng (nếu có)</li>
                        <li>Mô tả chi tiết vấn đề</li>
                    </ul>
                </li>
                <li><strong>Chờ xử lý:</strong> Đội ngũ hỗ trợ sẽ xem xét và phản hồi trong vòng 24-48 giờ.</li>
            </ol>

            <div class="tip-box">
                <strong><i class="fas fa-info-circle mr-2"></i>Lưu Ý:</strong>
                <p>Chợ Việt sẽ hỗ trợ giải quyết tranh chấp dựa trên bằng chứng và quy định của nền tảng. Trong trường hợp nghiêm trọng, chúng tôi có thể khóa tài khoản vi phạm và hỗ trợ bạn liên hệ cơ quan chức năng nếu cần.</p>
            </div>
        </div>

        <div class="safety-section">
            <h2><i class="fas fa-star mr-2"></i>Đánh Giá và Phản Hồi</h2>
            <p>Sau mỗi giao dịch thành công, bạn nên:</p>
            <ul>
                <li><strong>Để lại đánh giá chân thực:</strong> Giúp cộng đồng nhận biết người bán/người mua đáng tin cậy.</li>
                <li><strong>Mô tả chi tiết:</strong> Viết đánh giá cụ thể về chất lượng sản phẩm, thái độ phục vụ, và quá trình giao dịch.</li>
                <li><strong>Báo cáo hành vi xấu:</strong> Nếu gặp phải lừa đảo hoặc hành vi không đúng, hãy báo cáo ngay để bảo vệ cộng đồng.</li>
            </ul>
        </div>

        <div class="safety-section">
            <h2><i class="fas fa-lock mr-2"></i>Bảo Mật Thông Tin</h2>
            <p>Để bảo vệ tài khoản và thông tin cá nhân:</p>
            <ul>
                <li>Không chia sẻ mật khẩu với bất kỳ ai</li>
                <li>Sử dụng mật khẩu mạnh (kết hợp chữ hoa, chữ thường, số, ký tự đặc biệt)</li>
                <li>Đăng xuất sau khi sử dụng trên thiết bị công cộng</li>
                <li>Không click vào link lạ hoặc cung cấp thông tin cho người lạ</li>
                <li>Báo cáo ngay nếu phát hiện tài khoản bị xâm nhập</li>
            </ul>
        </div>

    </div>
    <!-- Content Wrapper End -->
</div>
<!-- Page Background End -->

<?php
include_once("view/footer.php");
?>

