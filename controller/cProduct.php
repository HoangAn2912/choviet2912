<?php
include_once "model/mProduct.php";

class cProduct {
    // controller/cProduct.php
    public function getSanPhamMoi() {
        $m = new mProduct();
        return $m->getSanPhamMoiNhat(100); // hoặc bỏ LIMIT hoàn toàn
    }

    public function getSanPhamById($id) {
        $m = new mProduct();
        return $m->getSanPhamById($id);
    }

    public function searchProducts($keyword) {
        $m = new mProduct();
        return $m->searchProducts($keyword);
    }
    

}
?>
