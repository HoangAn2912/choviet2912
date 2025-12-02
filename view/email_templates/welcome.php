<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 30px; text-align: center; }
        .content { padding: 40px 30px; }
        .feature { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #667eea; }
        .btn { display: inline-block; background: #667eea; color: white !important; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 10px 5px; font-weight: bold; }
        .footer { background: #f8f9fa; padding: 30px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="font-size: 36px; margin-bottom: 10px;">Chào mừng!</div>
            <h1 style="margin: 0;">Chào Mừng Đến Với Chợ Việt!</h1>
        </div>
        <div class="content">
            <p>Xin chào <strong><?php echo htmlspecialchars($user_name); ?></strong>,</p>
            
            <p>Chúc mừng bạn đã tạo tài khoản thành công tại <strong>Chợ Việt</strong> - nền tảng mua bán trực tuyến hàng đầu Việt Nam! 🇻🇳</p>
            
            <h3>Bạn có thể làm gì trên Chợ Việt?</h3>
            
            <div class="feature">
                <h4 style="margin: 0 0 10px 0;">Mua Sắm</h4>
                <p style="margin: 0;">Tìm kiếm và mua hàng nghìn sản phẩm từ người bán uy tín</p>
            </div>
            
            <div class="feature">
                <h4 style="margin: 0 0 10px 0;">Bán Hàng</h4>
                <p style="margin: 0;">Đăng tin miễn phí, quản lý sản phẩm và đơn hàng dễ dàng</p>
            </div>
            
            <div class="feature">
                <h4 style="margin: 0 0 10px 0;">Livestream</h4>
                <p style="margin: 0;">Bán hàng trực tiếp qua livestream, tương tác realtime với khách hàng</p>
            </div>
            
            <div class="feature">
                <h4 style="margin: 0 0 10px 0;">Chat</h4>
                <p style="margin: 0;">Nhắn tin trực tiếp với người mua/bán, trao đổi thông tin nhanh chóng</p>
            </div>
            
            <center>
                <a href="<?php echo htmlspecialchars($home_url); ?>" class="btn">Khám Phá Ngay</a>
                <a href="<?php echo htmlspecialchars($profile_url); ?>" class="btn" style="background: #28a745;">Hoàn Thiện Hồ Sơ</a>
            </center>
            
            <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; color: #666;">
                <strong>Mẹo:</strong> Hoàn thiện hồ sơ của bạn để tăng uy tín và bán hàng hiệu quả hơn!
            </p>
        </div>
        <div class="footer">
            <p><strong>Chợ Việt</strong> - Nơi trao đổi hàng hóa</p>
            <p>&copy; <?php echo date('Y'); ?> Chợ Việt</p>
            <p style="margin-top: 10px;">
                Cần hỗ trợ? Liên hệ: support@choviet.com
            </p>
        </div>
    </div>
</body>
</html>






































