<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: white; padding: 40px 30px; text-align: center; }
        .content { padding: 40px 30px; }
        .warning-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin: 20px 0; border-radius: 5px; color: #856404; }
        .btn { display: inline-block; background: #ffc107; color: #000 !important; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; font-size: 16px; }
        .footer { background: #f8f9fa; padding: 30px; text-align: center; font-size: 12px; color: #666; }
        .security-tips { background: #e9ecef; padding: 15px; margin: 20px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="font-size: 36px; margin-bottom: 10px;">Reset Password</div>
            <h1 style="margin: 0;">Đặt Lại Mật Khẩu</h1>
        </div>
        <div class="content">
            <p>Xin chào <strong><?php echo htmlspecialchars($user_name); ?></strong>,</p>
            
            <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn trên Chợ Việt.</p>
            
            <div class="warning-box">
                <p style="margin: 0;"><strong>Lưu ý quan trọng:</strong></p>
                <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                    <li>Link đặt lại mật khẩu chỉ có hiệu lực trong <strong><?php echo htmlspecialchars($expires); ?></strong></li>
                    <li>Nếu không phải bạn yêu cầu, vui lòng bỏ qua email này</li>
                    <li>Không chia sẻ link này với bất kỳ ai</li>
                </ul>
            </div>
            
            <center>
                <a href="<?php echo htmlspecialchars($reset_url); ?>" class="btn">
                    Đặt Lại Mật Khẩu
                </a>
            </center>
            
            <p style="text-align: center; color: #666; font-size: 14px; margin-top: 15px;">
                Hoặc copy link sau vào trình duyệt:<br>
                <span style="background: #f8f9fa; padding: 10px; display: inline-block; margin-top: 10px; word-break: break-all;">
                    <?php echo htmlspecialchars($reset_url); ?>
                </span>
            </p>
            
            <div class="security-tips">
                <h4 style="margin-top: 0;">Mẹo bảo mật:</h4>
                <ul style="margin: 0; padding-left: 20px; font-size: 14px;">
                    <li>Sử dụng mật khẩu mạnh (ít nhất 8 ký tự, có chữ hoa, số)</li>
                    <li>Không sử dụng lại mật khẩu từ website khác</li>
                    <li>Không chia sẻ mật khẩu với bất kỳ ai</li>
                    <li>Thay đổi mật khẩu định kỳ</li>
                </ul>
            </div>
            
            <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; color: #666; font-size: 14px;">
                <strong>Không phải bạn yêu cầu?</strong><br>
                Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng liên hệ ngay với chúng tôi để bảo vệ tài khoản.
            </p>
        </div>
        <div class="footer">
            <p><strong>Chợ Việt</strong> - Nơi trao đổi hàng hóa</p>
            <p>&copy; <?php echo date('Y'); ?> Chợ Việt</p>
            <p style="margin-top: 10px;">
                Cần hỗ trợ? Liên hệ: security@choviet.com
            </p>
        </div>
    </div>
</body>
</html>






































