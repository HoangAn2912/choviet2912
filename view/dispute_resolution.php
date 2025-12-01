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

    .dispute-header {
        background: linear-gradient(135deg, #e74c3c, #c0392b);
        color: white;
        padding: 30px;
        margin-bottom: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        text-align: center;
    }

    .dispute-header h1 {
        font-size: 2rem;
        font-weight: 700;
        margin: 0 0 10px 0;
        color: white;
    }

    .dispute-header p {
        font-size: 1.1rem;
        margin: 0;
        color: rgba(255, 255, 255, 0.9);
    }

    .dispute-section {
        margin-bottom: 30px;
    }

    .dispute-section h2 {
        color: #333;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e74c3c;
    }

    .dispute-section h3 {
        color: #555;
        font-size: 1.2rem;
        font-weight: 600;
        margin-top: 25px;
        margin-bottom: 15px;
    }

    .dispute-section p {
        color: #666;
        line-height: 1.8;
        margin-bottom: 15px;
    }

    .dispute-section ul, .dispute-section ol {
        color: #666;
        line-height: 1.8;
        margin-bottom: 15px;
        padding-left: 25px;
    }

    .dispute-section li {
        margin-bottom: 10px;
    }

    .step-box {
        background: #f8f9fa;
        border-left: 4px solid #e74c3c;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .step-box:hover {
        background: #fff5f5;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .step-box .step-number {
        display: inline-block;
        background: #e74c3c;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        text-align: center;
        line-height: 30px;
        font-weight: 700;
        margin-right: 10px;
    }

    .step-box strong {
        color: #333;
        display: block;
        margin-bottom: 10px;
        font-size: 1.1rem;
    }

    .info-box {
        background: #e7f3ff;
        border-left: 4px solid #007bff;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 8px;
    }

    .info-box strong {
        color: #004085;
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

    .success-box {
        background: #e8f5e9;
        border-left: 4px solid #28a745;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 8px;
    }

    .success-box strong {
        color: #155724;
        display: block;
        margin-bottom: 10px;
        font-size: 1.1rem;
    }

    .timeline {
        position: relative;
        padding-left: 30px;
        margin: 20px 0;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e74c3c;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
        padding-left: 20px;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -25px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #e74c3c;
        border: 2px solid white;
        box-shadow: 0 0 0 2px #e74c3c;
    }

    @media (max-width: 768px) {
        .page-background {
            padding: 0 1rem 1rem 1rem;
        }
        
        .content-wrapper {
            padding: 1.5rem;
            border-radius: 12px;
        }

        .dispute-header h1 {
            font-size: 1.5rem;
        }
    }
</style>

<!-- Page Background Start -->
<div class="page-background">
    <!-- Content Wrapper Start -->
    <div class="content-wrapper">

        <div class="dispute-header">
            <h1><i class="fas fa-gavel mr-2"></i>Giải Quyết Tranh Chấp</h1>
            <p>Quy trình và nguyên tắc giải quyết tranh chấp công bằng, minh bạch</p>
        </div>

        <div class="dispute-section">
            <h2><i class="fas fa-info-circle mr-2"></i>Giới Thiệu</h2>
            <p>
                Chợ Việt cam kết giải quyết mọi tranh chấp một cách công bằng, nhanh chóng và minh bạch. 
                Chúng tôi hiểu rằng đôi khi có thể xảy ra bất đồng giữa người bán và người mua, 
                và chúng tôi sẵn sàng hỗ trợ để đảm bảo quyền lợi của cả hai bên.
            </p>
        </div>

        <div class="dispute-section">
            <h2><i class="fas fa-exclamation-triangle mr-2"></i>Các Loại Tranh Chấp</h2>
            
            <div class="step-box">
                <strong><i class="fas fa-box mr-2"></i>1. Tranh Chấp Về Sản Phẩm</strong>
                <ul>
                    <li>Sản phẩm không đúng như mô tả</li>
                    <li>Sản phẩm bị hỏng, thiếu phụ kiện</li>
                    <li>Sản phẩm là hàng giả, hàng nhái</li>
                    <li>Kích thước, màu sắc không đúng</li>
                </ul>
            </div>

            <div class="step-box">
                <strong><i class="fas fa-money-bill-wave mr-2"></i>2. Tranh Chấp Về Thanh Toán</strong>
                <ul>
                    <li>Người mua đã thanh toán nhưng không nhận được hàng</li>
                    <li>Người bán không nhận được tiền sau khi giao hàng</li>
                    <li>Vấn đề về hoàn tiền, đổi trả</li>
                </ul>
            </div>

            <div class="step-box">
                <strong><i class="fas fa-truck mr-2"></i>3. Tranh Chấp Về Giao Hàng</strong>
                <ul>
                    <li>Hàng không được giao đúng thời gian</li>
                    <li>Hàng bị thất lạc, hư hỏng trong quá trình vận chuyển</li>
                    <li>Địa chỉ giao hàng không đúng</li>
                </ul>
            </div>

            <div class="step-box">
                <strong><i class="fas fa-user-times mr-2"></i>4. Tranh Chấp Về Hành Vi</strong>
                <ul>
                    <li>Lừa đảo, không giao hàng sau khi nhận tiền</li>
                    <li>Hành vi quấy rối, đe dọa</li>
                    <li>Vi phạm quy định cộng đồng</li>
                </ul>
            </div>
        </div>

        <div class="dispute-section">
            <h2><i class="fas fa-list-ol mr-2"></i>Quy Trình Giải Quyết Tranh Chấp</h2>
            
            <div class="timeline">
                <div class="timeline-item">
                    <strong>Bước 1: Thương Lượng Trực Tiếp</strong>
                    <p>
                        Trước tiên, hai bên nên cố gắng giải quyết vấn đề một cách hòa bình thông qua tin nhắn. 
                        Đa số tranh chấp có thể được giải quyết bằng cách này.
                    </p>
                </div>

                <div class="timeline-item">
                    <strong>Bước 2: Báo Cáo Lên Hệ Thống</strong>
                    <p>
                        Nếu không thể tự giải quyết, một trong hai bên có thể báo cáo lên hệ thống Chợ Việt 
                        bằng cách sử dụng nút "Báo cáo" trên trang sản phẩm hoặc trong tin nhắn.
                    </p>
                </div>

                <div class="timeline-item">
                    <strong>Bước 3: Cung Cấp Bằng Chứng</strong>
                    <p>
                        Người báo cáo cần cung cấp đầy đủ bằng chứng:
                    </p>
                    <ul>
                        <li>Màn hình cuộc trò chuyện</li>
                        <li>Hình ảnh sản phẩm (nếu có vấn đề)</li>
                        <li>Mã đơn hàng, biên lai thanh toán</li>
                        <li>Mô tả chi tiết vấn đề</li>
                    </ul>
                </div>

                <div class="timeline-item">
                    <strong>Bước 4: Xem Xét và Phản Hồi</strong>
                    <p>
                        Đội ngũ hỗ trợ sẽ xem xét trường hợp trong vòng 24-48 giờ và liên hệ với cả hai bên 
                        để thu thập thêm thông tin nếu cần.
                    </p>
                </div>

                <div class="timeline-item">
                    <strong>Bước 5: Đưa Ra Quyết Định</strong>
                    <p>
                        Dựa trên bằng chứng và quy định, chúng tôi sẽ đưa ra quyết định công bằng. 
                        Quyết định có thể bao gồm: hoàn tiền, yêu cầu đổi trả, cảnh báo, hoặc khóa tài khoản.
                    </p>
                </div>

                <div class="timeline-item">
                    <strong>Bước 6: Thực Hiện Quyết Định</strong>
                    <p>
                        Chúng tôi sẽ hỗ trợ thực hiện quyết định (hoàn tiền, điều chỉnh đơn hàng, v.v.) 
                        và theo dõi để đảm bảo vấn đề được giải quyết hoàn toàn.
                    </p>
                </div>
            </div>
        </div>

        <div class="dispute-section">
            <h2><i class="fas fa-balance-scale mr-2"></i>Nguyên Tắc Giải Quyết</h2>
            
            <div class="info-box">
                <strong><i class="fas fa-check-circle mr-2"></i>Nguyên Tắc Công Bằng:</strong>
                <ul>
                    <li>Xem xét khách quan dựa trên bằng chứng</li>
                    <li>Lắng nghe cả hai bên trước khi quyết định</li>
                    <li>Áp dụng quy định một cách nhất quán</li>
                    <li>Ưu tiên bảo vệ người dùng bị thiệt hại</li>
                </ul>
            </div>

            <div class="success-box">
                <strong><i class="fas fa-clock mr-2"></i>Nguyên Tắc Nhanh Chóng:</strong>
                <ul>
                    <li>Phản hồi trong vòng 24-48 giờ</li>
                    <li>Xử lý các trường hợp khẩn cấp ngay lập tức</li>
                    <li>Giải quyết trong vòng 7 ngày làm việc</li>
                </ul>
            </div>

            <div class="warning-box">
                <strong><i class="fas fa-eye mr-2"></i>Nguyên Tắc Minh Bạch:</strong>
                <ul>
                    <li>Thông báo rõ ràng quyết định và lý do</li>
                    <li>Cung cấp quy trình và thời gian xử lý</li>
                    <li>Cho phép khiếu nại nếu không đồng ý</li>
                </ul>
            </div>
        </div>

        <div class="dispute-section">
            <h2><i class="fas fa-file-alt mr-2"></i>Cách Báo Cáo Tranh Chấp</h2>
            
            <div class="step-box">
                <span class="step-number">1</span>
                <strong>Qua Email</strong>
                <p>
                    Gửi email đến <strong>trogiup@choviet.vn</strong> với tiêu đề "BÁO CÁO TRANH CHẤP - [Mã đơn hàng]". 
                    Đính kèm tất cả bằng chứng và mô tả chi tiết vấn đề.
                </p>
            </div>

            <div class="step-box">
                <span class="step-number">2</span>
                <strong>Qua Hotline</strong>
                <p>
                    Gọi <strong>19003003</strong> và yêu cầu chuyển đến bộ phận giải quyết tranh chấp. 
                    Nhân viên sẽ hướng dẫn bạn các bước tiếp theo.
                </p>
            </div>

            <div class="step-box">
                <span class="step-number">3</span>
                <strong>Qua Hệ Thống</strong>
                <p>
                    Sử dụng nút "Báo cáo" trên trang sản phẩm hoặc trong tin nhắn, 
                    điền form báo cáo và đính kèm bằng chứng.
                </p>
            </div>
        </div>

        <div class="dispute-section">
            <h2><i class="fas fa-shield-alt mr-2"></i>Biện Pháp Xử Lý</h2>
            
            <p>Tùy theo mức độ nghiêm trọng, chúng tôi có thể áp dụng các biện pháp sau:</p>
            
            <ul>
                <li><strong>Cảnh báo:</strong> Đối với vi phạm nhỏ, lần đầu</li>
                <li><strong>Hoàn tiền:</strong> Nếu người mua bị thiệt hại về tài chính</li>
                <li><strong>Yêu cầu đổi trả:</strong> Nếu sản phẩm không đúng như mô tả</li>
                <li><strong>Hạn chế quyền:</strong> Tạm thời hạn chế một số chức năng</li>
                <li><strong>Khóa tài khoản:</strong> Đối với vi phạm nghiêm trọng hoặc tái phạm</li>
                <li><strong>Báo cơ quan chức năng:</strong> Đối với hành vi phạm pháp</li>
            </ul>
        </div>

        <div class="dispute-section">
            <h2><i class="fas fa-question-circle mr-2"></i>Khiếu Nại Quyết Định</h2>
            
            <p>
                Nếu bạn không đồng ý với quyết định của chúng tôi, bạn có thể khiếu nại trong vòng 7 ngày 
                bằng cách gửi email với tiêu đề "KHIẾU NẠI - [Mã vụ việc]". 
                Chúng tôi sẽ xem xét lại và có thể yêu cầu thêm bằng chứng.
            </p>
            
            <div class="warning-box">
                <strong><i class="fas fa-exclamation-triangle mr-2"></i>Lưu Ý:</strong>
                <p>
                    Trong trường hợp tranh chấp phức tạp hoặc liên quan đến số tiền lớn, 
                    chúng tôi khuyến khích bạn liên hệ với cơ quan chức năng hoặc tòa án để được giải quyết theo pháp luật.
                </p>
            </div>
        </div>

        <div class="dispute-section">
            <h2><i class="fas fa-envelope mr-2"></i>Liên Hệ</h2>
            <p>Để báo cáo tranh chấp hoặc có câu hỏi, vui lòng liên hệ:</p>
            <p><i class="fa fa-envelope text-primary mr-2"></i>Email: <a href="mailto:trogiup@choviet.vn">trogiup@choviet.vn</a></p>
            <p><i class="fa fa-phone-alt text-primary mr-2"></i>Hotline: <strong>19003003</strong> (1.000đ/phút)</p>
            <p><i class="fa fa-map-marker-alt text-primary mr-2"></i>Địa chỉ: Số 14 đường Nguyễn Văn Bảo, Phường 4, Quận Gò Vấp, TP. Hồ Chí Minh</p>
        </div>

    </div>
    <!-- Content Wrapper End -->
</div>
<!-- Page Background End -->

<?php
include_once("view/footer.php");
?>

