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
  border-radius: 10px;
  background: transparent !important;
}

.carousel-item {
  display: none;
  position: relative;
  height: 500px;
  align-items: center;
  justify-content: center;
  transition: transform 0.6s ease-in-out;
  background: transparent;
  width: 100%;
  overflow: hidden; /* Ẩn phần thừa */
}

.carousel-item.active {
  display: flex;
  z-index: 2; /* Ảnh active luôn ở trên */
}

.carousel-item-next,
.carousel-item-prev {
  display: flex;
  z-index: 1; /* Ảnh tiếp theo ở dưới */
}

/* Slide effect từ phải sang trái - mượt mà */
.carousel-item-next:not(.carousel-item-start),
.active.carousel-item-end {
  transform: translateX(100%);
}

.carousel-item-prev:not(.carousel-item-end),
.active.carousel-item-start {
  transform: translateX(-100%);
}

.carousel-item-next,
.carousel-item-prev,
.carousel-item.active {
  transform: translateX(0);
}

/* Đảm bảo ảnh active vẫn hiển thị trong quá trình transition */
.active.carousel-item-left,
.active.carousel-item-right {
  display: flex !important; /* Luôn hiển thị khi đang transition */
  z-index: 2; /* Vẫn ở trên để ảnh mới đè lên */
}

/* Ảnh tiếp theo đè lên ảnh cũ */
.carousel-item-next.carousel-item-left,
.carousel-item-prev.carousel-item-right {
  z-index: 3; /* Ảnh mới ở trên cùng */
}

.product-carousel-img {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  z-index: 1;
  display: block;
  /* Đảm bảo ảnh luôn có background để không thấy trắng */
  background: #f8f9fa;
}

/* Đảm bảo ảnh tiếp theo luôn sẵn sàng trước khi transition */
.carousel-item-next .product-carousel-img,
.carousel-item-prev .product-carousel-img {
  opacity: 1;
  visibility: visible;
}

/* CSS cho sản phẩm liên quan - Giống trang chủ */
.product-img-hover {
  position: relative;
  width: 100%;
  height: 150px;
  overflow: hidden;
  background-color: #f9f9f9;
  padding: 0;
  margin: 0;
  border-radius: 12px 12px 0 0;
}

.product-img-hover img {
  width: 100% !important;
  height: 100% !important;
  object-fit: cover !important;
  object-position: center !important;
  transition: transform 0.3s ease;
  margin: 0 !important;
  padding: 0 !important;
  border: none !important;
}

.product-img-hover:hover img {
  transform: scale(1.05);
}

.product-meta {
  font-size: 13px;
  color: #888;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.hover-lift {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.product-item {
  border-radius: 12px !important;
  overflow: hidden;
}

.product-item .p-3 {
  border-radius: 0 0 12px 12px;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 700;
}

/* Giá và nút nhắn tin màu xanh cho sản phẩm liên quan */
.related-product-price {
  color: #007bff !important;
  font-weight: bold;
  font-size: 16px;
}

.related-product-message-btn {
  border: 1px solid #007bff !important;
  color: #007bff !important;
  background: transparent !important;
  transition: all 0.3s ease;
  border-radius: 0.50rem !important; /* Giống Bootstrap btn-sm mặc định */
}

.related-product-message-btn:hover {
  background: #007bff !important;
  color: white !important;
  border-color: #007bff !important;
}

.related-product-message-btn i {
  color: inherit;
}

/* Nút gọi điện - Giống nút nhắn tin */
#related-product-list .product-item .btn-outline-success {
  border-radius: 0.50rem !important;
}

/* Nút điều hướng ảnh sản phẩm - Giống banner */
#product-carousel .carousel-control-prev,
#product-carousel .carousel-control-next {
  width: 38px !important;
  height: 38px !important;
  background: rgba(255, 255, 255, 0.9) !important;
  border: 2px solid rgba(255, 215, 0, 0.8) !important;
  border-radius: 50% !important;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15) !important;
  opacity: 0.85 !important;
  transition: all 0.3s ease !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  top: 50% !important;
  transform: translateY(-50%) !important;
  margin: 0 !important;
  padding: 0 !important;
}

#product-carousel .carousel-control-prev:hover,
#product-carousel .carousel-control-next:hover {
  opacity: 1 !important;
  background: rgba(255, 255, 255, 1) !important;
  border-color: #FFD333 !important;
  box-shadow: 0 4px 12px rgba(255, 215, 0, 0.4) !important;
  transform: translateY(-50%) scale(1.1) !important;
}

#product-carousel .carousel-control-prev {
  left: 15px !important;
}

#product-carousel .carousel-control-next {
  right: 15px !important;
}

#product-carousel .carousel-control-prev i,
#product-carousel .carousel-control-next i {
  color: #3D464D !important;
  font-size: 20px !important;
  transition: all 0.3s ease !important;
  margin: 0 !important;
  padding: 0 !important;
  line-height: 1 !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  width: 100% !important;
  height: 100% !important;
}

#product-carousel .carousel-control-prev:hover i,
#product-carousel .carousel-control-next:hover i {
  color: #FFD333 !important;
  transform: scale(1.1);
}

</style>
    <!-- Shop Detail Start -->
    <div class="container d-flex justify-content-center">
  <div style="max-width: 1100px; width: 100%;">
    <div class="row px-xl-10">
    <div class="col-lg-5 mb-30">
  <div id="product-carousel" class="carousel slide" data-ride="carousel" data-interval="3000" data-pause="hover">
    <div class="carousel-inner">
      <?php foreach ($product['ds_anh'] as $i => $anh): ?>
        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
          <img class="product-carousel-img" src="img/<?= htmlspecialchars($anh) ?>" alt="Ảnh <?= $i + 1 ?>">
        </div>
      <?php endforeach; ?>
    </div>
    <a class="carousel-control-prev" href="#product-carousel" role="button" data-slide="prev">
      <i class="fa fa-2x fa-angle-left text-dark"></i>
      <span class="sr-only">Previous</span>
    </a>
    <a class="carousel-control-next" href="#product-carousel" role="button" data-slide="next">
      <i class="fa fa-2x fa-angle-right text-dark"></i>
      <span class="sr-only">Next</span>
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

<!-- Sản phẩm liên quan -->
<?php if (!empty($relatedProducts)): ?>
<div class="container-fluid pt-4 pb-3">
    <div class="row px-xl-5">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="section-title position-relative text-uppercase mb-0">
                    <span class="bg-secondary pr-3">
                        <i class="fas fa-th-large text-primary mr-2"></i>Sản phẩm liên quan
                    </span>
                </h2>
            </div>
        </div>
    </div>
    <div class="row px-xl-5" id="related-product-list">
        <?php foreach ($relatedProducts as $index => $sp): ?>
            <div class="col-6 col-sm-4 col-md-3 col-lg-2 pb-3 product-item-row">
                <div class="product-item bg-white border rounded-lg h-100 shadow-sm hover-lift">
                    <div class="product-img-hover position-relative">
                        <img src="img/<?= htmlspecialchars($sp['anh_dau']) ?>" alt="" class="img-fluid w-100" style="height: 150px; object-fit: cover; width: 100% !important; min-width: 100%; max-width: 100%;">
                        <div class="position-absolute top-0 right-0 m-2">
                            <button class="btn btn-sm btn-light rounded-circle">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                        <div class="position-absolute bottom-0 left-0 m-2">
                            <span class="badge badge-success text-white">
                                <i class="fas fa-clock mr-1"></i>Mới
                            </span>
                        </div>
                    </div>
                    <div class="p-3">
                        <a class="h6 text-decoration-none text-truncate d-block mb-2 text-dark" href="index.php?detail&id=<?= $sp['id'] ?>">
                            <?= htmlspecialchars($sp['title']) ?>
                        </a>
                        <div class="product-meta mb-2 small text-muted"><?= htmlspecialchars($sp['description']) ?></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="related-product-price font-weight-bold"><?= number_format($sp['price']) ?> đ</span>
                            <div class="d-flex">
                                <button class="btn btn-sm mr-1 related-product-message-btn" onclick="window.location.href='index.php?tin-nhan&to=<?= $sp['user_id'] ?>&product_id=<?= $sp['id'] ?>'">
                                    <i class="fas fa-comment"></i>
                                </button>
                                <button class="btn btn-outline-success btn-sm" onclick="alert('Số điện thoại: <?= htmlspecialchars($sp['phone']) ?>')">
                                    <i class="fas fa-phone"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
    <!-- Shop Detail End -->

    <!-- Footer Start -->
    <?php
include_once("view/footer.php");
?>
    <!-- Footer End -->
    <script>
  document.addEventListener('DOMContentLoaded', function () {
    const carousel = $('#product-carousel');
    
    // Preload tất cả ảnh để tránh hiện tượng trắng khi chuyển
    const images = carousel.find('img');
    images.each(function() {
      const img = new Image();
      img.src = $(this).attr('src');
    });
    
    // Cấu hình carousel với autoplay mượt hơn - chuyển từ phải sang trái
    carousel.carousel({
      interval: 3000, // 3 giây - thời gian hiển thị mỗi ảnh
      ride: 'carousel',
      wrap: true,
      pause: 'hover', // Tạm dừng khi hover chuột
      keyboard: true // Cho phép điều khiển bằng phím
    });
    
    // Xử lý sự kiện khi bắt đầu chuyển slide (khi click nút)
    carousel.on('slide.bs.carousel', function (e) {
      // Đảm bảo ảnh tiếp theo đã được load và hiển thị ngay
      const $nextItem = $(e.relatedTarget);
      const $nextImg = $nextItem.find('img');
      if ($nextImg.length) {
        // Preload ảnh
        const img = new Image();
        img.src = $nextImg.attr('src');
        // Đảm bảo ảnh hiển thị ngay
        $nextImg.css({
          'opacity': '1',
          'visibility': 'visible',
          'display': 'block'
        });
      }
    });
    
    // Xử lý sau khi chuyển slide xong
    carousel.on('slid.bs.carousel', function (e) {
      // Đảm bảo ảnh active luôn hiển thị đầy đủ
      const $activeItem = $(e.target).find('.carousel-item.active');
      const $activeImg = $activeItem.find('img');
      if ($activeImg.length) {
        $activeImg.css({
          'opacity': '1',
          'visibility': 'visible',
          'display': 'block'
        });
      }
    });
    
    // Tự động bắt đầu carousel
    carousel.carousel('cycle');
  });
</script>


