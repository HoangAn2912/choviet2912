<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once("../model/mConnect.php");
require_once("../model/mDetailProduct.php");

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if ($product_id > 0) {
    try {
        $model = new mDetailProduct();
        $product = $model->getDetailById($product_id);
        
        if ($product) {
            // Lấy ảnh đầu tiên
            $images = isset($product['ds_anh']) && !empty($product['ds_anh']) 
                ? $product['ds_anh'] 
                : (isset($product['image']) ? explode(',', $product['image']) : []);
            
            $firstImage = !empty($images[0]) ? trim($images[0]) : 'default-avatar.jpg';
            
            echo json_encode([
                'success' => true,
                'product' => [
                    'id' => $product['id'],
                    'title' => $product['title'],
                    'price' => $product['price'],
                    'image' => $firstImage,
                    'formatted_price' => number_format($product['price'], 0, ',', '.') . ' đ'
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Thiếu product_id']);
}
?>

























