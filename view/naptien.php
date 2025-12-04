<?php
if (!isset($_SESSION['role'])) {
    echo "<script>
        alert('Bạn không đủ thẩm quyền truy cập!');
        
    </script>";
    header("refresh: 0; url='?login'");
    exit;
}
?>
<<<<<<< HEAD

=======
>>>>>>> 65997a0 (up len web)
<?php include_once("view/header.php"); ?>   
<?php
    include_once("controller/VietQR_payment/index.php");
?>
<?php include_once("view/footer.php"); ?>
