<?php
session_start();
//xử lý đăng xuất
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}
include_once("controller/cCategory.php");
$p = new cCategory();

include_once("controller/cProduct.php");
$c = new cProduct();

include_once("controller/cDetailProduct.php");
$controller = new cDetailProduct();
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Chợ Việt - Nơi trao đổi hàng hóa</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Free HTML Templates" name="keywords">
    <meta content="Free HTML Templates" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">  

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body>
    <?php
        if (isset($_GET['action']) && $_GET['action'] == 'capNhatTrangThai') {
            include_once("controller/cPost.php");
            $ctrl = new cPost();
            $ctrl->capNhatTrangThaiBan();
            exit;
        }else if (isset($_GET['action']) && $_GET['action'] == 'dangTin') {
            include_once "controller/cPost.php";
            $post = new cPost();
            $post->dangTin();
            exit; 
        } else if (isset($_GET['quan-ly-tin']) && isset($_GET['sua'])) {
            include_once("controller/cPost.php");
            $ctrl = new cPost();
            $tin = (new mPost())->laySanPhamTheoId($_GET['sua']);
            include_once("view/managePost.php");
            exit;        
        }else if (isset($_GET['daytin'])) {
            include_once("controller/cPost.php");
            $postCtrl = new cPost();
            $postCtrl->dayTin($_GET['daytin']);
        }else if (isset($_GET['action']) && $_GET['action'] === 'suaTin') {
            include_once("controller/cPost.php");
            $controller = new cPost();
            $controller->suaTin(); // Gọi hàm sửa tin
        } else if (isset($_GET['action']) && $_GET['action'] == 'capNhatThongTin') {
            include_once("controller/cProfile.php");
            $ctrl = new cProfile();
            $ctrl->capNhatThongTin();
        }else if (isset($_GET['tin-nhan'])) {
            include_once("view/chat.php");
            exit;
        }else if (isset($_GET['action']) && $_GET['action'] == 'danhgia') {
            include_once("view/review_form.php");
        }else if (isset($_GET['nap-tien'])) {
            include_once("view/naptien.php");
        }else if (isset($_GET['quan-ly-tin'])) {
            include_once("view/managePost.php");
        }else if (isset($_GET['search'])) {
            include_once("view/search.php");
        } else if (isset($_GET['category'])) {
            include_once("view/category.php");
        }else if(isset($_GET['shop'])){
            include_once("view/shop.html");
        } else if(isset($_GET['cart'])){
            include_once("view/cart.html");
        } else if(isset($_GET['checkout'])){
            include_once("view/checkout.html");
        } else if (isset($_GET['detail']) && isset($_GET['id'])) {
            $id = $_GET['id'];
            $controller->showDetail($id); 
        } else if(isset($_GET['contact'])){
            include_once("view/contact.html");
        } else if(isset($_GET['login'])){
            include_once("loginlogout/login.php");
        } else if(isset($_GET['signup'])){
            include_once("loginlogout/signup.php");
        } else if(isset($_GET['thongtin'])){
            include_once("view/profile/index.php");
        } else if(isset($_GET['username'])){
            // Xử lý URL thân thiện cho trang cá nhân
            include_once("model/mProfile.php");
            $profileModel = new mProfile();
            $userId = $profileModel->getUserByUsername($_GET['username']);
            if($userId) {
                $_GET['thongtin'] = $userId;
                include_once("view/profile/index.php");
            } else {
                // Nếu không tìm thấy người dùng, chuyển hướng về trang chủ
                include_once("view/index.php");
            }
        } else if(isset($_GET['livestream'])){
            include_once("view/livestream.php");
        } else {
            include_once("view/index.php");
        }
    ?>

    


</body>

</html>