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

    .blog-header {
        background: linear-gradient(135deg, #fd79a8, #e84393);
        color: white;
        padding: 30px;
        margin-bottom: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(253, 121, 168, 0.3);
        text-align: center;
    }

    .blog-header h1 {
        font-size: 2rem;
        font-weight: 700;
        margin: 0 0 10px 0;
        color: white;
    }

    .blog-header p {
        font-size: 1.1rem;
        margin: 0;
        color: rgba(255, 255, 255, 0.9);
    }

    .blog-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    .blog-card {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .blog-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        border-color: #fd79a8;
    }

    .blog-card-image {
        width: 100%;
        height: 200px;
        background: linear-gradient(135deg, #fd79a8, #e84393);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
    }

    .blog-card-content {
        padding: 20px;
    }

    .blog-card-date {
        color: #999;
        font-size: 0.85rem;
        margin-bottom: 10px;
    }

    .blog-card-title {
        color: #333;
        font-size: 1.3rem;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .blog-card-excerpt {
        color: #666;
        line-height: 1.6;
        margin-bottom: 15px;
    }

    .blog-card-read-more {
        color: #fd79a8;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
    }

    .blog-card-read-more:hover {
        text-decoration: underline;
    }

    .blog-section {
        margin-bottom: 30px;
    }

    .blog-section h2 {
        color: #333;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #fd79a8;
    }

    .coming-soon {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }

    .coming-soon i {
        font-size: 4rem;
        color: #fd79a8;
        margin-bottom: 20px;
    }

    .coming-soon h2 {
        color: #333;
        margin-bottom: 15px;
        border: none;
    }

    .coming-soon p {
        font-size: 1.1rem;
        line-height: 1.8;
        max-width: 600px;
        margin: 0 auto;
    }

    @media (max-width: 768px) {
        .page-background {
            padding: 0 1rem 1rem 1rem;
        }
        
        .content-wrapper {
            padding: 1.5rem;
            border-radius: 12px;
        }

        .blog-header h1 {
            font-size: 1.5rem;
        }

        .blog-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Page Background Start -->
<div class="page-background">
    <!-- Content Wrapper Start -->
    <div class="content-wrapper">

        <div class="blog-header">
            <h1><i class="fas fa-blog mr-2"></i>Blog Chợ Việt</h1>
            <p>Chia sẻ kiến thức, mẹo vặt và tin tức về mua bán đồ cũ</p>
        </div>

        <div class="blog-section">
            <h2><i class="fas fa-newspaper mr-2"></i>Bài Viết Mới Nhất</h2>
            
            <div class="blog-grid">
                <div class="blog-card">
                    <div class="blog-card-image">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <div class="blog-card-content">
                        <div class="blog-card-date"><i class="far fa-calendar mr-2"></i>15/01/2025</div>
                        <div class="blog-card-title">10 Mẹo Bán Đồ Cũ Hiệu Quả Trên Chợ Việt</div>
                        <div class="blog-card-excerpt">
                            Khám phá những bí quyết giúp bạn bán đồ cũ nhanh chóng và đạt giá tốt nhất trên nền tảng Chợ Việt...
                        </div>
                        <a href="#" class="blog-card-read-more">
                            Đọc thêm <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>

                <div class="blog-card">
                    <div class="blog-card-image">
                        <i class="fas fa-video"></i>
                    </div>
                    <div class="blog-card-content">
                        <div class="blog-card-date"><i class="far fa-calendar mr-2"></i>12/01/2025</div>
                        <div class="blog-card-title">Hướng Dẫn Livestream Bán Hàng Cho Người Mới</div>
                        <div class="blog-card-excerpt">
                            Từng bước hướng dẫn chi tiết cách tạo và phát sóng livestream bán hàng hiệu quả trên Chợ Việt...
                        </div>
                        <a href="#" class="blog-card-read-more">
                            Đọc thêm <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>

                <div class="blog-card">
                    <div class="blog-card-image">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="blog-card-content">
                        <div class="blog-card-date"><i class="far fa-calendar mr-2"></i>10/01/2025</div>
                        <div class="blog-card-title">Cách Nhận Biết Lừa Đảo Khi Mua Bán Online</div>
                        <div class="blog-card-excerpt">
                            Những dấu hiệu cảnh báo và cách phòng tránh lừa đảo khi mua bán đồ cũ trên các nền tảng trực tuyến...
                        </div>
                        <a href="#" class="blog-card-read-more">
                            Đọc thêm <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>

                <div class="blog-card">
                    <div class="blog-card-image">
                        <i class="fas fa-camera"></i>
                    </div>
                    <div class="blog-card-content">
                        <div class="blog-card-date"><i class="far fa-calendar mr-2"></i>08/01/2025</div>
                        <div class="blog-card-title">Bí Quyết Chụp Ảnh Sản Phẩm Đẹp Mắt</div>
                        <div class="blog-card-excerpt">
                            Hướng dẫn chụp ảnh sản phẩm chuyên nghiệp để thu hút người mua và tăng tỷ lệ bán hàng...
                        </div>
                        <a href="#" class="blog-card-read-more">
                            Đọc thêm <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>

                <div class="blog-card">
                    <div class="blog-card-image">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="blog-card-content">
                        <div class="blog-card-date"><i class="far fa-calendar mr-2"></i>05/01/2025</div>
                        <div class="blog-card-title">Định Giá Đồ Cũ: Làm Sao Để Bán Được Giá Tốt?</div>
                        <div class="blog-card-excerpt">
                            Cách xác định giá bán hợp lý cho đồ cũ dựa trên tình trạng, thương hiệu và thị trường hiện tại...
                        </div>
                        <a href="#" class="blog-card-read-more">
                            Đọc thêm <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>

                <div class="blog-card">
                    <div class="blog-card-image">
                        <i class="fas fa-recycle"></i>
                    </div>
                    <div class="blog-card-content">
                        <div class="blog-card-date"><i class="far fa-calendar mr-2"></i>03/01/2025</div>
                        <div class="blog-card-title">Mua Bán Đồ Cũ: Góp Phần Bảo Vệ Môi Trường</div>
                        <div class="blog-card-excerpt">
                            Tầm quan trọng của việc tái sử dụng hàng hóa trong nền kinh tế tuần hoàn và bảo vệ môi trường...
                        </div>
                        <a href="#" class="blog-card-read-more">
                            Đọc thêm <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="blog-section">
            <h2><i class="fas fa-tags mr-2"></i>Chủ Đề Phổ Biến</h2>
            <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 20px;">
                <span style="background: #f8f9fa; padding: 8px 16px; border-radius: 20px; color: #666; border: 1px solid #e9ecef;">Mẹo bán hàng</span>
                <span style="background: #f8f9fa; padding: 8px 16px; border-radius: 20px; color: #666; border: 1px solid #e9ecef;">Livestream</span>
                <span style="background: #f8f9fa; padding: 8px 16px; border-radius: 20px; color: #666; border: 1px solid #e9ecef;">An toàn giao dịch</span>
                <span style="background: #f8f9fa; padding: 8px 16px; border-radius: 20px; color: #666; border: 1px solid #e9ecef;">Định giá</span>
                <span style="background: #f8f9fa; padding: 8px 16px; border-radius: 20px; color: #666; border: 1px solid #e9ecef;">Chụp ảnh</span>
                <span style="background: #f8f9fa; padding: 8px 16px; border-radius: 20px; color: #666; border: 1px solid #e9ecef;">Bảo vệ môi trường</span>
                <span style="background: #f8f9fa; padding: 8px 16px; border-radius: 20px; color: #666; border: 1px solid #e9ecef;">Kinh nghiệm</span>
            </div>
        </div>

        <div class="coming-soon">
            <i class="fas fa-rocket"></i>
            <h2>Nhiều Bài Viết Hấp Dẫn Đang Được Cập Nhật!</h2>
            <p>
                Chúng tôi đang không ngừng tạo ra những nội dung hữu ích về mua bán đồ cũ, 
                livestream bán hàng, và các mẹo vặt thú vị. Hãy quay lại thường xuyên để không bỏ lỡ những bài viết mới nhất!
            </p>
        </div>

    </div>
    <!-- Content Wrapper End -->
</div>
<!-- Page Background End -->

<?php
include_once("view/footer.php");
?>

