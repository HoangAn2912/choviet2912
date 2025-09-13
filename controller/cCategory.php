<?php
include_once __DIR__ . "/../model/mCategory.php";
include_once __DIR__ . "/../model/mConnect.php";

class cCategory {
    public function index() {
        $mCategory = new mCategory();
        $categories = $mCategory->layDanhMuc();

        $data = [];
        foreach ($categories as $row) {
            $idCha = $row['id_cha'];
            if (!isset($data[$idCha])) {
                $data[$idCha] = [
                    'ten_cha' => $row['ten_cha'],
                    'con' => []
                ];
            }
            if ($row['id_con'] != null) {
                $data[$idCha]['con'][] = [
                    'id_con' => $row['id_con'],
                    'ten_con' => $row['ten_con']
                ];
            }
        }

        return $data;
    }

    public function getProductsByCategory() {
        if (isset($_GET['id_loai'])) {
            $id_loai = $_GET['id_loai'];
            $model = new mCategory();
            $products = $model->getProductsByCategoryId($id_loai);
            header('Content-Type: application/json');
            echo json_encode($products);
        }
    }

    public function showCategoriesWithCount() {
        $mCategory = new mCategory();
        $categories = $mCategory->layDanhMucVaSoLuong();
        return $categories;
    }

    public function getProductsByCategoryId($id_loai) {
        $mCategory = new mCategory();
        return $mCategory->getProductsByCategoryId($id_loai);
    }

    public function getUserById($id) {
        $mCategory = new mCategory();
        return $mCategory->getUserById($id);
    }
}

// Xử lý API
if (isset($_GET['action'])) {
    $controller = new cCategory();
    switch ($_GET['action']) {
        case 'getProductsByCategory':
            $controller->getProductsByCategory();
            break;

        case 'getSubcategories': // Xử lý danh mục con
            if (isset($_GET['id_cha'])) {
                $idCha = intval($_GET['id_cha']);
                $conn = (new Connect())->connect();
                $stmt = $conn->prepare("SELECT id, category_name FROM product_categories WHERE parent_category_id = ?");
                $stmt->bind_param("i", $idCha);
                $stmt->execute();
                $result = $stmt->get_result();
                $subcategories = [];
                while ($row = $result->fetch_assoc()) {
                    $subcategories[] = $row;
                }
                header('Content-Type: application/json');
                echo json_encode($subcategories);
                exit;
            }
            break;
    }
}

?>
