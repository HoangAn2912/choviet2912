<?php
include_once("model/mPost.php");

class cPost {
    public function dangTin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idLoaiSanPham = intval($_POST['category_id'] ?? 0);
            if ($idLoaiSanPham == 0) {
                header("Location: index.php?toast=" . urlencode("❌ Bạn chưa chọn danh mục sản phẩm!") . "&type=error");
                exit;
            }

            $tieuDe = trim($_POST['title'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $moTa = trim($_POST['description'] ?? '');
            $idNguoiDang = $_SESSION['user_id'] ?? 0;

            if ($idNguoiDang == 0) {
                header("Location: index.php?toast=" . urlencode("❌ Bạn cần đăng nhập để đăng tin!") . "&type=error");
                exit;
            }

            $anhTenList = [];

            if (isset($_FILES['image'])) {
                $total = count($_FILES['image']['name']);
                for ($i = 0; $i < $total; $i++) {
                    $tmpName = $_FILES['image']['tmp_name'][$i];
                    $fileName = $_FILES['image']['name'][$i];

                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
                        header("Location: index.php?toast=" . urlencode("❌ Chỉ cho phép tải ảnh JPG hoặc PNG!") . "&type=error");
                        exit;
                    }

                    $newFileName = uniqid() . '.' . $ext;
                    $targetDir = "img/";
                    $targetFile = $targetDir . $newFileName;

                    if (move_uploaded_file($tmpName, $targetFile)) {
                        $anhTenList[] = $newFileName;
                    }
                }
            }

            if (count($anhTenList) < 2) {
                header("Location: index.php?toast=" . urlencode("❌ Bạn phải chọn ít nhất 2 ảnh để đăng tin.") . "&type=error");
                exit;
            }

            $hinhAnh = implode(",", $anhTenList);

            $model = new mPost();
            $soLuong = $model->demSoLuongTin($idNguoiDang);

            if ($soLuong >= 3) {
                            $thongTin = $model->layThongTinNguoiDung($idNguoiDang);
            $soDu = intval($thongTin['balance'] ?? 0);

                if ($soDu < 11000) {
                    header("Location: index.php?toast=" . urlencode("❌ Tài khoản không đủ 11.000đ để đăng tin.") . "&type=error");
                    exit;
                }
            }

            $result = $model->insertSanPham($tieuDe, $price, $moTa, $hinhAnh, $idNguoiDang, $idLoaiSanPham);

            if ($result) {
                header("Location: index.php?toast=" . urlencode("🎉 Đăng tin thành công! Tin đang chờ duyệt.") . "&type=success");
                exit;
            } else {
                header("Location: index.php?toast=" . urlencode("❌ Đăng tin thất bại. Vui lòng thử lại!") . "&type=error");
                exit;
            }
        }
    }

    public function layDanhSachTinNguoiDung($userId) {
        $model = new mPost();
        return $model->layTatCaTinDangTheoNguoiDung($userId);
    }

    public function layTinDang() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Chưa đăng nhập']);
            return;
        }

        $idNguoiDung = $_SESSION['user_id'];
        $status = $_GET['status'] ?? 'Đang bán';

        $m = new mPost();
        $data = $m->getTinDangByStatus($idNguoiDung, $status);

        echo json_encode(['status' => 'success', 'data' => $data]);
    }

public function capNhatTrangThaiBan() {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Chưa đăng nhập']);
        return;
    }

    $idTin = intval($_POST['id']);
    $loai = $_POST['loai'];

    // Debug log
    file_put_contents('log.txt', "idTin: $idTin, loai: $loai\n", FILE_APPEND);

    if (!in_array($loai, ['Đã bán', 'Đã ẩn'])) {
        echo json_encode(['status' => 'error', 'message' => 'Trạng thái không hợp lệ']);
        return;
    }

    $m = new mPost();
    $ok = $m->updateTrangThaiBan($idTin, $loai);

    // Debug log
    file_put_contents('log.txt', "update result: " . ($ok ? 'success' : 'fail') . "\n", FILE_APPEND);

    if ($ok) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Cập nhật thất bại!']);
    }
}

    public function demSoLuongTheoTrangThai($userId) {
        $model = new mPost();
        return $model->demSoLuongTheoTrangThai($userId);
    }

    public function layThongTinNguoiDung($userId) {
        $model = new mPost();
        return $model->layThongTinNguoiDung($userId);
    }

    public function getBadgeColor($status) {
        switch($status) {
            case 'Đang bán': return 'success';
            case 'Đã bán': return 'secondary';
            case 'Chờ duyệt': return 'warning';
            case 'Từ chối': return 'danger';
            case 'Đã ẩn': return 'dark';
        }
    }

    public function getNoProductText($status) {
        switch($status) {
            case 'Đang bán': return 'Chưa có sản phẩm đang bán.';
            case 'Đã bán': return 'Chưa có sản phẩm đã bán.';
            case 'Chờ duyệt': return 'Chưa có sản phẩm chờ duyệt.';
            case 'Từ chối': return 'Chưa có sản phẩm bị từ chối.';
            case 'Đã ẩn': return 'Chưa có sản phẩm đã ẩn.';
            default: return 'Chưa có sản phẩm.';
        }
    }

    public function suaTin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_GET['id'] ?? 0);
            // $idLoaiSanPham = intval($_POST['category_id'] ?? 0);
            $tieuDe = trim($_POST['title'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $moTa = trim($_POST['description'] ?? '');
            $idNguoiDang = $_SESSION['user_id'] ?? 0;

            $model = new mPost();
            $tinCu = $model->laySanPhamTheoId($id);
            if (!$tinCu || $tinCu['user_id'] != $idNguoiDang) {
                header("Location: index.php?toast=" . urlencode("❌ Không tìm thấy tin!") . "&type=error");
                exit;
            }
            $idLoaiSanPham = $tinCu['category_id'];
            $anhTenList = explode(',', $tinCu['image']);
            if (isset($_FILES['image']) && $_FILES['image']['name'][0] != '') {
                $anhTenList = [];
                foreach ($_FILES['image']['tmp_name'] as $i => $tmpName) {
                    $fileName = $_FILES['image']['name'][$i];
                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    if (!in_array($ext, ['jpg', 'jpeg', 'png'])) continue;
                    $newName = uniqid() . '.' . $ext;
                    if (move_uploaded_file($tmpName, "img/$newName")) $anhTenList[] = $newName;
                }
                if (count($anhTenList) < 2) {
                    header("Location: index.php?toast=" . urlencode("❌ Vui lòng chọn ít nhất 2 ảnh hợp lệ (.jpg, .png)!") . "&type=error");
                    exit;
                }
            }

            $hinhAnh = implode(',', $anhTenList);
            $ok = $model->capNhatSanPham($id, $tieuDe, $price, $moTa, $hinhAnh, $idLoaiSanPham, $idNguoiDang);

            if ($ok) {
                header("Location: index.php?quan-ly-tin&toast=" . urlencode("✅ Đã cập nhật và chuyển về chờ duyệt!") . "&type=success");
            } else {
                header("Location: index.php?quan-ly-tin&toast=" . urlencode("❌ Cập nhật thất bại!") . "&type=error");
            }
        }
    }

    public function layTenLoaiSanPham($idLoai) {
        $model = new mPost();
        return $model->layTenLoaiSanPham($idLoai);
    }

    public function laySanPhamTheoId($id) {
        $model = new mPost();
        return $model->laySanPhamTheoId($id);
    }

    public function dayTin($idTin) {
        $idTin = intval($idTin);
        $idNguoiDung = $_SESSION['user_id'] ?? 0;
        $model = new mPost();

        $soDu = $model->laySoDuNguoiDung($idNguoiDung);
        if ($soDu < 11000) {
            header("Location: index.php?quan-ly-tin&toast=" . urlencode("⚠️ Bạn không đủ tiền để đẩy tin. Vui lòng nạp thêm.") . "&type=warning");
            return;
        }

        $ok = $model->dayTin($idTin, $idNguoiDung);
        if ($ok) {
            header("Location: index.php?quan-ly-tin&toast=" . urlencode("🚀 Đã đẩy tin thành công!") . "&type=success");
        } else {
            header("Location: index.php?quan-ly-tin&toast=" . urlencode("❌ Có lỗi xảy ra khi đẩy tin!") . "&type=error");
        }
    }
}
?>
