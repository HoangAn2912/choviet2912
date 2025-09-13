<?php
class Connect {
    public function connect() {
        // Thiết lập múi giờ PHP cho toàn ứng dụng
        if (function_exists('date_default_timezone_set')) {
            date_default_timezone_set('Asia/Ho_Chi_Minh');
        }

        $con = mysqli_connect("localhost", "admin", "123456", "choviet29");
        if (!$con) {
            echo "Lỗi kết nối cơ sở dữ liệu: " . mysqli_connect_error();
            exit();
        } else {
            mysqli_query($con, "SET NAMES 'utf8'");
            // Thiết lập múi giờ cho phiên MySQL để NOW()/TIMESTAMP đồng bộ +07:00
            @mysqli_query($con, "SET time_zone = '+07:00'");
            return $con;
        }
    }
}
?>

