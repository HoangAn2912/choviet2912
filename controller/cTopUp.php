<?php
include_once("model/mTopUp.php");

class cTopUp {
    public function xuLyNopTien($userId) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_ck'])) {
        $transfer_content = trim($_POST['transfer_content']);
        $transfer_status = "Đang chờ duyệt";
        $transfer_image = '';

        // Xử lý upload ảnh
        if (isset($_FILES['transfer_image']) && $_FILES['transfer_image']['error'] == 0) {
            $ext = pathinfo($_FILES['transfer_image']['name'], PATHINFO_EXTENSION);
            $fileName = 'ck_' . time() . '_' . rand(1000,9999) . '.' . $ext;
            move_uploaded_file($_FILES['transfer_image']['tmp_name'], 'img/' . $fileName);
            $transfer_image = $fileName;
        }

        // Lưu vào DB
        $mTopUp = new mTopUp();
        $ok = $mTopUp->insertChuyenKhoan($userId, $transfer_content, $transfer_image, $transfer_status);

        if ($ok !== false) {
            header("Location: index.php?nap-tien&toast=" . urlencode("✅ Gửi yêu cầu nạp tiền thành công! Đang chờ duyệt.") . "&type=success");
        } else {
            header("Location: index.php?nap-tien&toast=" . urlencode("❌ Gửi yêu cầu thất bại!") . "&type=error");
        }
        exit;
    }
}

    public function getLichSu($userId) {
        $mTopUp = new mTopUp();
        return $mTopUp->getLichSuChuyenKhoan($userId);
    }
}
?>