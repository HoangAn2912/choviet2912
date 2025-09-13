<?php

// include_once("controller/cDetailProduct.php");

// $controller = new cDetailProduct();
// $id = $_GET['id'] ?? 1; // hoặc lấy từ router
// $controller->showDetail($id);
?>
<?php
include_once("view/header.php");
?>
<style>
.carousel-inner {
  position: relative;
  width: 100%;
  overflow: hidden;
}

.carousel-item {
  display: none;
  position: relative;
  transition: transform 0.6s ease-in-out;
  height: 500px;
  align-items: center;
  justify-content: center;
}

.carousel-item.active,
.carousel-item-next,
.carousel-item-prev {
  display: flex;
}

.product-carousel-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}


</style>
    <!-- Shop Detail Start -->
    <div class="container d-flex justify-content-center">
  <div style="max-width: 1100px; width: 100%;">
    <div class="row px-xl-10">
    <div class="col-lg-5 mb-30">
  <div id="product-carousel" class="carousel slide" data-ride="carousel">
    <div class="carousel-inner bg-light">
      <?php foreach ($product['ds_anh'] as $i => $anh): ?>
        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
          <img class="product-carousel-img" src="img/<?= htmlspecialchars($anh) ?>" alt="Ảnh <?= $i + 1 ?>">
        </div>
      <?php endforeach; ?>
    </div>
    <a class="carousel-control-prev" href="#product-carousel" data-slide="prev">
      <i class="fa fa-2x fa-angle-left text-dark"></i>
    </a>
    <a class="carousel-control-next" href="#product-carousel" data-slide="next">
      <i class="fa fa-2x fa-angle-right text-dark"></i>
    </a>
  </div>
</div>
<!-- Phần thông tin bài đăng -->
<div class="col-lg-7 h-auto mb-30">
    <div class="h-100 bg-light p-30">
        <h3 class="mb-2"><?= htmlspecialchars($product['title']) ?></h3>
        <h4 class="font-weight-bold mb-3" style="color: #DC3545; font-size: 20px;">
            <?= number_format($product['price'], 0, ',', '.') ?>₫
        </h4>

        <p class="mb-2">
            <i class="fa fa-map-marker-alt mr-2" style="color: #3D464D;"></i>
            <?= htmlspecialchars($product['address']) ?>
        </p>

        <p class="mb-2">
            <i class="fa fa-clock mr-2" style="color: #3D464D;"></i>
            <?= "Cập nhật: " . ($product['thoi_gian_format'] ?? '') ?>
        </p>

        <p class="mb-4">
            <i class="fa fa-phone mr-2" style="color: #3D464D;"></i>
            <?= "Số điện thoại: " . htmlspecialchars($product['phone']) ?>
        </p>

        <div class="d-flex align-items-center mb-4">
                  <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $product['user_id']): ?>
            <button class="btn btn-warning text-white w-100" 
                onclick="window.location.href='index.php?tin-nhan&to=<?= $product['user_id'] ?>&product_id=<?= $product['id'] ?>'">
                <i class="fa fa-comment mr-2" style="color: #3D464D;"></i>Nhắn tin
            </button>
        <?php endif; ?>
        </div>

        <hr>
        <h5 class="mb-3">Người bán</h5>
        <div class="d-flex align-items-center">
            <img src="img/<?= htmlspecialchars($product['avatar']) ?>" class="rounded-circle mr-3" width="50" height="50">
            <div>
              <div>
                <strong>
                <a href="<?= htmlspecialchars($product['username']) ?>" class="text-dark" style="text-decoration: none;">
<?= htmlspecialchars($product['username']) ?>
                </a>
                </strong>
              </div>

                <div class="text-muted">Đã bán: <?= $product['quantity_da_ban'] ?> sản phẩm</div>
                <div class="text-warning">
                    <?php
                        $rating = floatval($product['rating']);
                        for ($i = 1; $i <= 5; $i++) {
                            echo $i <= round($rating) ? '<i class="fa fa-star"></i>' : '<i class="fa fa-star text-secondary"></i>';
                        }
                    ?>
                    <span style="color: #3D464D;">
                        (<?= $rating ?> sao,
                        <a href="<?= htmlspecialchars($product['username']) ?>" style="color: #3D464D; text-decoration: underline;">
                            <?= $product['so_nguoi_danh_price'] ?> người đánh giá
                        </a>)
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>



 <!--  -->
        </div>

        <!-- Mô tả thông tin chi tiết sản phẩm -->
<div class="row px-xl-10">
    <div class="col">
        <div class="bg-light p-30">
            <div class="nav nav-tabs mb-4">
                <h5 class="font-weight-bold mb-0">Mô tả thông tin chi tiết</h5>
            </div>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-pane-1">
                    <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

        </div>
        <!--  -->
    </div>
    </div>
    <!-- Shop Detail End -->

    <!-- Footer Start -->
    <?php
include_once("view/footer.php");
?>
    <!-- Footer End -->
    <script>
  document.addEventListener('DOMContentLoaded', function () {
    $('#product-carousel').carousel({
      interval: 3000
    });
  });
</script>


