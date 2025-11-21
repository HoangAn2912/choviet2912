<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 40px 30px; text-align: center; }
        .header h1 { margin: 0 0 10px 0; font-size: 28px; }
        .content { padding: 40px 30px; }
        .success-box { background: #d4edda; border-left: 4px solid #28a745; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .post-title { font-size: 20px; font-weight: bold; color: #28a745; margin-bottom: 15px; }
        .btn { display: inline-block; background: #28a745; color: white !important; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
        .btn:hover { background: #218838; }
        .footer { background: #f8f9fa; padding: 30px; text-align: center; font-size: 12px; color: #666; }
        .icon { display: inline-block; width: 80px; height: 80px; background: rgba(255,255,255,0.2); border-radius: 50%; line-height: 80px; font-size: 40px; margin-bottom: 15px; }
        .tips { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">✅</div>
            <h1>Tin Đăng Đã Được Duyệt!</h1>
            <p>Chúc mừng bạn!</p>
        </div>
        <div class="content">
            <p>Xin chào <strong><?php echo htmlspecialchars($user_name); ?></strong>,</p>
            
            <p>Tin đăng của bạn đã được phê duyệt và hiện đang hiển thị trên Chợ Việt! 🎉</p>
            
            <div class="success-box">
                <div class="post-title">📌 <?php echo htmlspecialchars($post_title); ?></div>
                <p style="margin: 0; color: #155724;">
                    Tin đăng của bạn đã đáp ứng các tiêu chuẩn và đang được hiển thị công khai cho người mua.
                </p>
            </div>
            
            <center>
                <a href="<?php echo htmlspecialchars($post_url); ?>" class="btn">
                    Xem Tin Đăng →
                </a>
            </center>
            
            <div class="tips">
                <h3 style="margin-top: 0; color: #856404;">💡 Mẹo để bán hàng hiệu quả:</h3>
                <ul style="margin: 10px 0; padding-left: 20px; color: #856404;">
                    <li>Trả lời tin nhắn của khách hàng nhanh chóng</li>
                    <li>Cung cấp thông tin sản phẩm chi tiết và chính xác</li>
                    <li>Đăng ảnh rõ nét, nhiều góc độ</li>
                    <li>Cập nhật trạng thái "Đã bán" khi bán xong</li>
                    <li>Giữ uy tín bằng cách giao hàng đúng hẹn</li>
                </ul>
            </div>
            
            <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; color: #666; font-size: 14px;">
                Cảm ơn bạn đã tin tưởng và sử dụng Chợ Việt. Chúc bạn bán hàng thành công! 🚀
            </p>
        </div>
        <div class="footer">
            <p><strong>Chợ Việt</strong> - Nơi trao đổi hàng hóa</p>
            <p>&copy; <?php echo date('Y'); ?> Chợ Việt. All rights reserved.</p>
        </div>
    </div>
</body>
</html>





































