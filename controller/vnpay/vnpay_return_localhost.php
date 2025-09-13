<?php

// Redirect tất cả callback về file chính
if (isset($_GET['vnp_TxnRef'])) {
    // Log để debug
    error_log("[v0] Localhost callback received for: " . $_GET['vnp_TxnRef']);
    
    // Chuyển tất cả parameters sang file xử lý chính
    $query_string = http_build_query($_GET);
    header("Location: vnpay_return.php?" . $query_string);
    exit();
} else {
    // Nếu không có parameters, chuyển về trang nạp tiền
    header("Location: nap_tien.php");
    exit();
}
?>
