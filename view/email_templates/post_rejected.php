<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 40px 30px; text-align: center; }
        .content { padding: 40px 30px; }
        .error-box { background: #f8d7da; border-left: 4px solid #dc3545; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .btn { display: inline-block; background: #dc3545; color: white !important; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
        .footer { background: #f8f9fa; padding: 30px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="font-size: 32px; margin-bottom: 10px;">Thông báo</div>
            <h1 style="margin: 0;">Tin Đăng Bị Từ Chối</h1>
        </div>
        <div class="content">
            <p>Xin chào <strong><?php echo htmlspecialchars($user_name); ?></strong>,</p>
            <p>Rất tiếc, tin đăng của bạn không được phê duyệt.</p>
            
            <div class="error-box">
                <h3 style="margin-top: 0; color: #721c24;"><?php echo htmlspecialchars($post_title); ?></h3>
                <p style="margin: 0; color: #721c24;">
                    <strong>Lý do:</strong> <?php echo htmlspecialchars($reason); ?>
                </p>
            </div>
            
            <h3>🔍 Các lỗi thường gặp:</h3>
            <ul>
                <li>Ảnh không rõ nét hoặc không phải ảnh thật của sản phẩm</li>
                <li>Tiêu đề hoặc mô tả vi phạm quy định</li>
                <li>Giá không hợp lý hoặc spam</li>
                <li>Danh mục không đúng</li>
                <li>Sản phẩm cấm giao dịch</li>
            </ul>
            
            <p><strong>Bạn có thể:</strong></p>
            <ul>
                <li>Kiểm tra và chỉnh sửa tin đăng</li>
                <li>Đăng lại tin mới theo đúng quy định</li>
                <li>Liên hệ hỗ trợ nếu cần giải đáp</li>
            </ul>
            
            <center>
                <a href="<?php echo htmlspecialchars($support_url); ?>" class="btn">Liên Hệ Hỗ Trợ</a>
            </center>
        </div>
        <div class="footer">
            <p><strong>Chợ Việt</strong></p>
            <p>&copy; <?php echo date('Y'); ?> Chợ Việt</p>
        </div>
    </div>
</body>
</html>






































