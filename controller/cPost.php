<?php
include_once("model/mPost.php");
include_once("helpers/Security.php");
include_once("helpers/RateLimiter.php");

class cPost {
    public function dangTin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!Security::validateCSRFToken($csrfToken)) {
                header("Location: index.php?toast=" . urlencode("❌ CSRF token không hợp lệ!") . "&type=error");
                exit;
            }
            
            // Rate limiting - 5 bài đăng / 1 giờ
            RateLimiter::middleware('post_create', 5, 3600);
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
                    // Tạo file array format để validate
                    $file = [
                        'name' => $_FILES['image']['name'][$i],
                        'type' => $_FILES['image']['type'][$i],
                        'tmp_name' => $_FILES['image']['tmp_name'][$i],
                        'error' => $_FILES['image']['error'][$i],
                        'size' => $_FILES['image']['size'][$i]
                    ];
                    
                    // Validate file với Security helper
                    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                    $maxSize = 5 * 1024 * 1024; // 5MB
                    $validation = Security::validateUpload($file, $allowedTypes, $maxSize);
                    
                    if (!$validation['valid']) {
                        $errors = implode(', ', $validation['errors']);
                        header("Location: index.php?toast=" . urlencode("❌ " . $errors) . "&type=error");
                        exit;
                    }

                    // Generate safe filename
                    $newFileName = Security::generateSafeFilename($file['name'], 'product_');
                    $targetDir = "img/";
                    $targetFile = $targetDir . $newFileName;

                    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
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
    // Set header JSON và tắt output buffering
    header('Content-Type: application/json; charset=utf-8');
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Chưa đăng nhập']);
        exit;
    }

    $idTin = intval($_POST['id'] ?? 0);
    $loai = trim($_POST['loai'] ?? '');
    $note = isset($_POST['note']) ? trim($_POST['note']) : null;

    if ($idTin <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID sản phẩm không hợp lệ']);
        exit;
    }

    if (!in_array($loai, ['Đã bán', 'Đã ẩn'])) {
        echo json_encode(['status' => 'error', 'message' => 'Trạng thái không hợp lệ']);
        exit;
    }

    // Nếu là "Đã bán" hoặc "Đã ẩn" thì bắt buộc phải có note
    if (in_array($loai, ['Đã bán', 'Đã ẩn']) && (empty($note) || $note === '')) {
        $message = $loai === 'Đã bán' ? 'Vui lòng chọn lý do đã bán!' : 'Vui lòng chọn lý do ẩn sản phẩm!';
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit;
    }

    try {
    $m = new mPost();
        $ok = $m->updateTrangThaiBan($idTin, $loai, $note);

    if ($ok) {
            echo json_encode(['status' => 'success', 'message' => 'Cập nhật thành công!']);
    } else {
            echo json_encode(['status' => 'error', 'message' => 'Cập nhật thất bại! Vui lòng thử lại.']);
    }
    } catch (Exception $e) {
        error_log("Error in capNhatTrangThaiBan: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
    }
    exit;
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
            
            // Lấy danh sách ảnh cũ cần giữ lại
                $anhTenList = [];
            if (isset($_POST['images_to_keep']) && is_array($_POST['images_to_keep'])) {
                foreach ($_POST['images_to_keep'] as $img) {
                    $img = trim($img);
                    if (!empty($img)) {
                        $anhTenList[] = $img;
                    }
                }
            }
            
            // Thêm ảnh mới nếu có
            if (isset($_FILES['image']) && !empty($_FILES['image']['name'][0])) {
                foreach ($_FILES['image']['tmp_name'] as $i => $tmpName) {
                    if (empty($_FILES['image']['name'][$i])) continue;
                    
                    $fileName = $_FILES['image']['name'][$i];
                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    if (!in_array($ext, ['jpg', 'jpeg', 'png'])) continue;
                    
                    $newName = uniqid() . '.' . $ext;
                    if (move_uploaded_file($tmpName, "img/$newName")) {
                        $anhTenList[] = $newName;
                }
                }
            }
            
            // Xóa các ảnh cũ không được giữ lại
            $anhCuList = explode(',', $tinCu['image']);
            foreach ($anhCuList as $imgCu) {
                $imgCu = trim($imgCu);
                if (!empty($imgCu) && !in_array($imgCu, $anhTenList)) {
                    // Xóa file ảnh cũ
                    $filePath = "img/" . $imgCu;
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }
                }
            }
            
            // Kiểm tra số lượng ảnh
            $anhTenList = array_filter($anhTenList); // Loại bỏ phần tử rỗng
            if (count($anhTenList) < 2) {
                header("Location: index.php?quan-ly-tin&toast=" . urlencode("❌ Tổng số ảnh phải từ 2 đến 6 ảnh!") . "&type=error");
                exit;
            }
            if (count($anhTenList) > 6) {
                header("Location: index.php?quan-ly-tin&toast=" . urlencode("❌ Tổng số ảnh không được vượt quá 6 ảnh!") . "&type=error");
                    exit;
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
