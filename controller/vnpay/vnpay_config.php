<?php
require_once '../../helpers/url_helper.php';

// Cấu hình VNPay
$vnp_TmnCode    = "AFC6ZM4W"; // Mã website
$vnp_HashSecret = "599LP8UPX1QUYKEJ1CYS7R2MEUF73A3Q"; // Chuỗi bí mật
$vnp_Url        = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
// Fix for duplicate controller issue - use direct URL construction
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$vnp_Returnurl = $protocol . '://' . $host . '/controller/vnpay/vnpay_return.php';

// Cấu hình khác
$vnp_apiUrl = "http://sandbox.vnpayment.vn/merchant_webapi/merchant.html";
$apiUrl = "https://sandbox.vnpayment.vn/merchant_webapi/api/transaction";

// Múi giờ
date_default_timezone_set('Asia/Ho_Chi_Minh');

function getReturnUrl() {
    // Fix for duplicate controller issue - use direct URL construction
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . '://' . $host . '/controller/vnpay/vnpay_return.php';
}
?>
