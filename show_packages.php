<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Packages Check</title></head><body>";
echo "<style>
body { font-family: Arial; padding: 20px; background: #f5f5f5; }
.good { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
.bad { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
table { width: 100%; border-collapse: collapse; background: white; }
th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
th { background: #667eea; color: white; }
.sql-box { background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 10px 0; }
</style>";

echo "<h1>📦 KIỂM TRA SỐ LƯỢNG GÓI LIVESTREAM</h1>";

// Connect database
require_once 'model/mConnect.php';
$conn = new Connect();
$db = $conn->connect();

// Query packages
$sql = "SELECT * FROM livestream_packages ORDER BY id ASC";
$result = $db->query($sql);

$count = $result->num_rows;

echo "<h2>Kết quả:</h2>";
if ($count >= 3) {
    echo "<div class='good'>✅ OK: Có <strong>$count gói</strong> trong database</div>";
} else {
    echo "<div class='bad'>❌ VẤN ĐỀ: Chỉ có <strong>$count gói</strong>, cần <strong>3 gói</strong>!</div>";
}

if ($count > 0) {
    echo "<h2>Danh sách gói hiện tại:</h2>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Tên gói</th><th>Giá</th><th>Số ngày</th><th>Status</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['package_name']) . "</td>";
        echo "<td><strong>" . number_format($row['price']) . "đ</strong></td>";
        echo "<td>" . $row['duration_days'] . " ngày</td>";
        echo "<td>" . ($row['status'] ? '✅ Active' : '❌ Inactive') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

if ($count < 3) {
    echo "<hr>";
    echo "<h2>🔧 CÁCH FIX:</h2>";
    echo "<ol>";
    echo "<li><strong>Mở phpMyAdmin:</strong> <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>";
    echo "<li><strong>Chọn database:</strong> <code>choviet29</code></li>";
    echo "<li><strong>Click tab \"SQL\"</strong></li>";
    echo "<li><strong>Copy & Paste đoạn SQL dưới đây:</strong></li>";
    echo "</ol>";
    
    echo "<div class='sql-box'>";
    echo "<strong>SQL để thêm đủ 3 gói:</strong><br><br>";
    echo "<textarea style='width:100%;height:300px;font-family:monospace;'>";
    echo "-- Xóa dữ liệu cũ
DELETE FROM livestream_packages;

-- Reset AUTO_INCREMENT
ALTER TABLE livestream_packages AUTO_INCREMENT = 1;

-- Thêm 3 gói
INSERT INTO livestream_packages (id, package_name, description, price, duration_days, status) VALUES
(1, 'Gói Ngày', 'Livestream trong 1 ngày. Phù hợp để test hoặc bán hàng ngắn hạn.', 190000.00, 1, 1),
(2, 'Gói Tuần', 'Livestream trong 7 ngày. Tiết kiệm hơn so với gói ngày.', 890000.00, 7, 1),
(3, 'Gói Tháng VIP', 'Livestream KHÔNG GIỚI HẠN số lần và thời lượng trong 30 ngày. Tối ưu chi phí cho doanh nghiệp.', 2990000.00, 30, 1);

-- Kiểm tra
SELECT * FROM livestream_packages ORDER BY id;";
    echo "</textarea>";
    echo "</div>";
    
    echo "<p><strong>5.</strong> Click nút <strong>\"Go\"</strong> để chạy SQL</p>";
    echo "<p><strong>6.</strong> Reload trang packages: <a href='index.php?livestream-packages'>index.php?livestream-packages</a></p>";
}

echo "<hr>";
echo "<h2>📊 Kết quả mong đợi:</h2>";
echo "<table>";
echo "<tr><th>ID</th><th>Tên gói</th><th>Giá</th><th>Số ngày</th></tr>";
echo "<tr><td>1</td><td>Gói Ngày</td><td>190,000đ</td><td>1 ngày</td></tr>";
echo "<tr><td>2</td><td>Gói Tuần</td><td>890,000đ</td><td>7 ngày</td></tr>";
echo "<tr><td>3</td><td>Gói Tháng VIP</td><td>2,990,000đ</td><td>30 ngày</td></tr>";
echo "</table>";

echo "<hr>";
echo "<p><a href='index.php?livestream-packages' style='background:#667eea;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;display:inline-block;'>→ Reload Trang Packages</a></p>";

echo "</body></html>";
?>











