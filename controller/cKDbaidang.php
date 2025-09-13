<?php
include_once("model/mKDbaidang.php");
class ckdbaidang {
    public function getallbaidang() {
        $p = new kdbaidang();
        return $p->allbaidang();
    }
    public function getonebaidang($id) {
        $p = new kdbaidang();
        return $p->onebaidang($id);
    }
    public function getAllProductTypes(){
        $p = new kdbaidang();
        $p->allloaisanpham();
    }
    public function getduyetbai($id){
        $p = new kdbaidang();
        $p->duyetBai($id);
    }
    public function gettuchoi($id, $ghichu){
        $p = new kdbaidang();
        $p->tuChoiBai($id, $ghichu);
    }
    function getPaginatedPosts($offset, $limit, $status = '', $product_type = '', $search = '') {
        $p = new kdbaidang();
        $data = $p->selectPaginatedPosts($offset, $limit, $status, $product_type, $search);
        return $data;
    }
    function countFilteredPosts($status = '', $product_type = '', $search = '') {
        $p = new kdbaidang();
        return $p->countFilteredPosts($status, $product_type, $search);
    }
}
?>