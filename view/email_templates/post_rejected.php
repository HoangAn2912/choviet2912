<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            line-height: 1.8; 
            color: #333; 
            background-color: #f4f4f4; 
            margin: 0; 
            padding: 0; 
        }
        .container { 
            max-width: 600px; 
            margin: 20px auto; 
            background: white; 
            border-radius: 10px; 
            overflow: hidden; 
            box-shadow: 0 0 20px rgba(0,0,0,0.1); 
        }
        .header { 
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); 
            color: white; 
            padding: 40px 30px; 
            text-align: center; 
        }
        .header h1 { 
            margin: 0 0 10px 0; 
            font-size: 28px; 
            font-weight: 600;
        }
        .content { 
            padding: 40px 30px; 
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .post-title {
            font-size: 20px;
            font-weight: bold;
            color: #dc3545;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #dc3545;
            border-radius: 5px;
        }
        .rejection-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .rejection-box p {
            margin: 0;
            font-size: 15px;
            line-height: 1.8;
        }
        .reason-box {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .reason-label {
            font-weight: bold;
            color: #721c24;
            display: block;
            margin-bottom: 8px;
        }
        .reason-text {
            color: #721c24;
            font-size: 15px;
            line-height: 1.6;
        }
        .info-box {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .info-item {
            margin: 12px 0;
            font-size: 15px;
        }
        .info-label {
            font-weight: bold;
            color: #333;
            display: inline-block;
            min-width: 120px;
        }
        .info-value {
            color: #666;
        }
        .message {
            margin: 25px 0;
            font-size: 15px;
            line-height: 1.8;
        }
        .closing {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 15px;
        }
        .signature {
            margin-top: 25px;
            font-weight: bold;
            color: #dc3545;
        }
        .footer { 
            background: #f8f9fa; 
            padding: 30px; 
            text-align: center; 
            font-size: 12px; 
            color: #666; 
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Thông Báo Từ Chối Bài Viết</h1>
        </div>
        <div class="content">
            <div class="greeting">
                Kính gửi <strong><?php echo htmlspecialchars($user_name); ?></strong>,
            </div>
            
            <p class="message">
                Ban biên tập xin cảm ơn bạn đã gửi bài viết "<strong><?php echo htmlspecialchars($post_title); ?></strong>" cho chúng tôi.
            </p>
            
            <div class="rejection-box">
                <p style="margin: 0; font-size: 16px; font-weight: bold; color: #856404;">
                    Sau khi xem xét kỹ lưỡng, chúng tôi rất tiếc phải thông báo rằng bài viết của bạn chưa đáp ứng được các tiêu chí xuất bản của chúng tôi và không thể được đăng tải vào thời điểm này.
                </p>
            </div>
            
            <div class="reason-box">
                <span class="reason-label">Lý do từ chối:</span>
                <div class="reason-text">
                    <?php echo nl2br(htmlspecialchars($reason)); ?>
                </div>
            </div>
            
            <div class="info-box">
                <h3 style="margin-top: 0; color: #333; font-size: 18px;">Thông tin bài viết:</h3>
                
                <div class="info-item">
                    <span class="info-label">Ngày gửi:</span>
                    <span class="info-value"><?php echo htmlspecialchars($publish_date); ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Danh mục:</span>
                    <span class="info-value"><?php echo htmlspecialchars($category_name); ?></span>
                </div>
            </div>
            
            <p class="message">
                Chúng tôi khuyến khích bạn xem xét lại nội dung bài viết, chỉnh sửa theo gợi ý trên và gửi lại cho chúng tôi. Nếu bạn có bất kỳ thắc mắc nào, vui lòng liên hệ với chúng tôi để được hỗ trợ.
            </p>
            
            <p class="message">
                Chúng tôi rất mong nhận được sự hợp tác tiếp theo từ bạn với các bài viết chất lượng hơn.
            </p>
            
            <div class="closing">
                <p style="margin: 0;">
                    Xin chân thành cảm ơn và mong sớm hợp tác cùng bạn trong các bài viết tiếp theo!
                </p>
                
                <div class="signature">
                    Trân trọng,<br>
                    <strong>Choviet.site</strong>
                </div>
            </div>
        </div>
        <div class="footer">
            <p><strong>Chợ Việt</strong> - Nơi trao đổi hàng hóa</p>
            <p>&copy; <?php echo date('Y'); ?> Chợ Việt. All rights reserved.</p>
            <p style="margin-top: 10px; color: #999;">Email này được gửi tự động, vui lòng không reply.</p>
        </div>
    </div>
</body>
</html>
