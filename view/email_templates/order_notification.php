<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 30px; text-align: center; }
        .header h1 { margin: 0 0 10px 0; font-size: 28px; }
        .header p { margin: 0; opacity: 0.9; }
        .content { padding: 40px 30px; }
        .order-box { background: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .order-code { font-size: 24px; font-weight: bold; color: #667eea; margin-bottom: 10px; }
        .total-amount { font-size: 28px; font-weight: bold; color: #28a745; margin: 15px 0; }
        .customer-info { background: white; border: 1px solid #dee2e6; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .customer-info h3 { margin-top: 0; color: #667eea; font-size: 16px; }
        .customer-info p { margin: 5px 0; }
        .items-list { margin: 20px 0; }
        .item { background: white; border: 1px solid #dee2e6; padding: 15px; margin-bottom: 10px; border-radius: 5px; }
        .item-name { font-weight: bold; color: #333; }
        .item-details { color: #666; font-size: 14px; margin-top: 5px; }
        .btn { display: inline-block; background: #667eea; color: white !important; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
        .btn:hover { background: #5568d3; }
        .footer { background: #f8f9fa; padding: 30px; text-align: center; font-size: 12px; color: #666; }
        .icon { display: inline-block; width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 50%; line-height: 60px; font-size: 30px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">Chợ Việt</div>
            <h1>Đơn Hàng Mới!</h1>
            <p>Bạn có một đơn hàng mới cần xử lý</p>
        </div>
        <div class="content">
            <p>Xin chào <strong><?php echo htmlspecialchars($seller_name); ?></strong>,</p>
            
            <p>Chúc mừng! Bạn vừa nhận được một đơn hàng mới trên Chợ Việt.</p>
            
            <div class="order-box">
                <div class="order-code">Mã đơn hàng: #<?php echo htmlspecialchars($order_code); ?></div>
                <div class="total-amount">Tổng: <?php echo htmlspecialchars($total_amount); ?> đ</div>
            </div>
            
            <div class="customer-info">
                <h3>Thông Tin Khách Hàng</h3>
                <p><strong>Tên:</strong> <?php echo htmlspecialchars($customer_name); ?></p>
                <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($customer_phone); ?></p>
            </div>
            
            <?php if (!empty($items)): ?>
            <h3>Sản Phẩm Trong Đơn</h3>
            <div class="items-list">
                <?php foreach ($items as $item): ?>
                <div class="item">
                    <div class="item-name"><?php echo htmlspecialchars($item['title'] ?? $item['product_title'] ?? 'Sản phẩm'); ?></div>
                    <div class="item-details">
                        Số lượng: <?php echo $item['quantity']; ?> x <?php echo number_format($item['price']); ?> đ
                        = <strong><?php echo number_format($item['quantity'] * $item['price']); ?> đ</strong>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <center>
                <a href="<?php echo htmlspecialchars($order_url); ?>" class="btn">
                    Xem Chi Tiết Đơn Hàng →
                </a>
            </center>
            
            <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; color: #666; font-size: 14px;">
                <strong>Lưu ý:</strong> Vui lòng xác nhận đơn hàng và chuẩn bị hàng trong vòng 24 giờ.
                Khách hàng đang chờ đợi sản phẩm từ bạn!
            </p>
        </div>
        <div class="footer">
            <p><strong>Chợ Việt</strong> - Nơi trao đổi hàng hóa</p>
            <p>&copy; <?php echo date('Y'); ?> Chợ Việt. All rights reserved.</p>
            <p style="margin-top: 10px;">
                Email này được gửi tự động, vui lòng không reply.<br>
                Nếu cần hỗ trợ, vui lòng liên hệ: support@choviet.com
            </p>
        </div>
    </div>
</body>
</html>






































