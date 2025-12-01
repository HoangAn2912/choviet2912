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

    .about-header {
        background: linear-gradient(135deg, #6c5ce7, #a29bfe);
        color: white;
        padding: 30px;
        margin-bottom: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(108, 92, 231, 0.3);
        text-align: center;
    }

    .about-header h1 {
        font-size: 2rem;
        font-weight: 700;
        margin: 0 0 10px 0;
        color: white;
    }

    .about-header p {
        font-size: 1.1rem;
        margin: 0;
        color: rgba(255, 255, 255, 0.9);
    }

    .about-section {
        margin-bottom: 30px;
    }

    .about-section h2 {
        color: #333;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #6c5ce7;
    }

    .about-section h3 {
        color: #555;
        font-size: 1.2rem;
        font-weight: 600;
        margin-top: 25px;
        margin-bottom: 15px;
    }

    .about-section p {
        color: #666;
        line-height: 1.8;
        margin-bottom: 15px;
    }

    .about-section ul {
        color: #666;
        line-height: 1.8;
        margin-bottom: 15px;
        padding-left: 25px;
    }

    .about-section li {
        margin-bottom: 10px;
    }

    .feature-box {
        background: #f8f9fa;
        border-left: 4px solid #6c5ce7;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .feature-box:hover {
        background: #f0f0ff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .feature-box i {
        color: #6c5ce7;
        font-size: 1.5rem;
        margin-right: 10px;
    }

    .feature-box strong {
        color: #333;
        display: block;
        margin-bottom: 10px;
        font-size: 1.1rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin: 30px 0;
    }

    .stat-card {
        background: linear-gradient(135deg, #6c5ce7, #a29bfe);
        color: white;
        padding: 25px;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(108, 92, 231, 0.2);
    }

    .stat-card .number {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .stat-card .label {
        font-size: 1rem;
        opacity: 0.9;
    }

    .mission-vision {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin: 30px 0;
    }

    .mission-card, .vision-card {
        background: #f8f9fa;
        padding: 25px;
        border-radius: 12px;
        border-top: 4px solid #6c5ce7;
    }

    .mission-card h3, .vision-card h3 {
        color: #6c5ce7;
        margin-bottom: 15px;
    }

    @media (max-width: 768px) {
        .page-background {
            padding: 0 1rem 1rem 1rem;
        }
        
        .content-wrapper {
            padding: 1.5rem;
            border-radius: 12px;
        }

        .about-header h1 {
            font-size: 1.5rem;
        }

        .stats-grid, .mission-vision {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Page Background Start -->
<div class="page-background">
    <!-- Content Wrapper Start -->
    <div class="content-wrapper">

        <div class="about-header">
            <h1><i class="fas fa-info-circle mr-2"></i>Giới Thiệu Về Chợ Việt</h1>
            <p>Nền tảng livestream bán hàng C2C hàng đầu Việt Nam</p>
        </div>

        <div class="about-section">
            <h2><i class="fas fa-building mr-2"></i>Về Chúng Tôi</h2>
            <p>
                <strong>Chợ Việt</strong> là nền tảng thương mại điện tử kết nối người bán và người mua đồ cũ trực tuyến theo mô hình <strong>C2C (Consumer to Consumer)</strong>. 
                Ra đời với sứ mệnh tạo ra một không gian mua bán minh bạch, an toàn và tiện lợi cho người Việt Nam.
            </p>
            <p>
                Với tính năng <strong>livestream bán hàng độc đáo</strong>, Chợ Việt cho phép người bán trực tiếp giới thiệu sản phẩm, tương tác với khách hàng trong thời gian thực, 
                và xử lý đơn hàng ngay trong buổi livestream. Đây là cách mới để bán đồ cũ hiệu quả và nhanh chóng.
            </p>
        </div>

        <div class="about-section">
            <h2><i class="fas fa-bullseye mr-2"></i>Sứ Mệnh & Tầm Nhìn</h2>
            
            <div class="mission-vision">
                <div class="mission-card">
                    <h3><i class="fas fa-rocket mr-2"></i>Sứ Mệnh</h3>
                    <p>
                        Tạo ra một nền tảng mua bán đồ cũ hiện đại, minh bạch và an toàn, giúp người Việt dễ dàng trao đổi hàng hóa, 
                        tận dụng tối đa giá trị của những món đồ không còn sử dụng, và xây dựng một cộng đồng giao dịch đáng tin cậy.
                    </p>
                </div>
                <div class="vision-card">
                    <h3><i class="fas fa-eye mr-2"></i>Tầm Nhìn</h3>
                    <p>
                        Trở thành nền tảng livestream bán hàng C2C hàng đầu Việt Nam, với hàng triệu người dùng tin tưởng và sử dụng, 
                        góp phần thúc đẩy nền kinh tế tuần hoàn và bảo vệ môi trường thông qua việc tái sử dụng hàng hóa.
                    </p>
                </div>
            </div>
        </div>

        <div class="about-section">
            <h2><i class="fas fa-star mr-2"></i>Tính Năng Nổi Bật</h2>
            
            <div class="feature-box">
                <strong><i class="fas fa-video"></i>Livestream Bán Hàng</strong>
                <p>
                    Tính năng độc đáo cho phép người bán phát sóng trực tiếp, giới thiệu sản phẩm chi tiết, 
                    tương tác với khách hàng qua chat real-time, và xử lý đơn hàng ngay trong livestream.
                </p>
            </div>

            <div class="feature-box">
                <strong><i class="fas fa-comments"></i>Chat Trực Tiếp</strong>
                <p>
                    Hệ thống tin nhắn nội bộ giúp người bán và người mua trao đổi, thương lượng giá cả, 
                    hỏi đáp về sản phẩm một cách nhanh chóng và tiện lợi.
                </p>
            </div>

            <div class="feature-box">
                <strong><i class="fas fa-star-half-alt"></i>Hệ Thống Đánh Giá</strong>
                <p>
                    Sau mỗi giao dịch, người dùng có thể để lại đánh giá và nhận xét, giúp xây dựng một cộng đồng 
                    minh bạch và đáng tin cậy, nơi mọi người có thể yên tâm giao dịch.
                </p>
            </div>

            <div class="feature-box">
                <strong><i class="fas fa-shield-alt"></i>Kiểm Duyệt Nội Dung</strong>
                <p>
                    Tất cả tin đăng và livestream đều được kiểm duyệt kỹ lưỡng trước khi hiển thị, 
                    đảm bảo chất lượng nội dung và tuân thủ chính sách cộng đồng.
                </p>
            </div>

            <div class="feature-box">
                <strong><i class="fas fa-wallet"></i>Ví Nội Bộ & Thanh Toán</strong>
                <p>
                    Hệ thống ví điện tử tích hợp, hỗ trợ thanh toán qua VNPay, giúp giao dịch nhanh chóng, 
                    an toàn và tiện lợi cho cả người bán và người mua.
                </p>
            </div>

            <div class="feature-box">
                <strong><i class="fas fa-th-large"></i>Phân Loại Rõ Ràng</strong>
                <p>
                    Hệ thống danh mục sản phẩm đa dạng và rõ ràng: Xe cộ, Đồ điện tử, Thời trang, 
                    Nội thất, Giải trí & Thể thao... giúp người dùng dễ dàng tìm kiếm sản phẩm mong muốn.
                </p>
            </div>
        </div>

        <div class="about-section">
            <h2><i class="fas fa-chart-line mr-2"></i>Thành Tựu & Số Liệu</h2>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="number">10K+</div>
                    <div class="label">Người Dùng</div>
                </div>
                <div class="stat-card">
                    <div class="number">50K+</div>
                    <div class="label">Sản Phẩm</div>
                </div>
                <div class="stat-card">
                    <div class="number">5K+</div>
                    <div class="label">Giao Dịch</div>
                </div>
                <div class="stat-card">
                    <div class="number">1K+</div>
                    <div class="label">Livestream</div>
                </div>
            </div>
        </div>

        <div class="about-section">
            <h2><i class="fas fa-users mr-2"></i>Đội Ngũ</h2>
            <p>
                Chợ Việt được vận hành bởi <strong>CÔNG TY TNHH CHỢ VIỆT</strong> với đội ngũ chuyên nghiệp, 
                nhiệt huyết và luôn đặt lợi ích của người dùng lên hàng đầu.
            </p>
            <p>
                <strong>Người đại diện:</strong> Nguyễn Phúc Hoàng An, Trần Thái Bảo
            </p>
            <p>
                <strong>Địa chỉ:</strong> Số 14 đường Nguyễn Văn Bảo, Phường 4, Quận Gò Vấp, Thành phố Hồ Chí Minh, Việt Nam
            </p>
        </div>

        <div class="about-section">
            <h2><i class="fas fa-handshake mr-2"></i>Cam Kết</h2>
            <ul>
                <li><strong>Minh bạch:</strong> Mọi giao dịch đều được ghi nhận và có thể truy vết</li>
                <li><strong>An toàn:</strong> Bảo vệ thông tin và quyền lợi của người dùng</li>
                <li><strong>Chất lượng:</strong> Kiểm duyệt nội dung kỹ lưỡng, đảm bảo uy tín</li>
                <li><strong>Hỗ trợ:</strong> Đội ngũ hỗ trợ 24/7, sẵn sàng giải quyết mọi vấn đề</li>
                <li><strong>Đổi mới:</strong> Liên tục cải tiến và phát triển tính năng mới</li>
            </ul>
        </div>

        <div class="about-section">
            <h2><i class="fas fa-envelope mr-2"></i>Liên Hệ</h2>
            <p>Nếu bạn có bất kỳ câu hỏi hoặc góp ý nào, vui lòng liên hệ với chúng tôi:</p>
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

