<?php
include_once("controller/cProduct.php");
$p = new cProduct();
$products = [];

if (isset($_GET['keyword']) && !empty(trim($_GET['keyword']))) {
    $keyword = trim($_GET['keyword']);
    $products = $p->searchProducts($keyword);
}
?>

<head>
    <style>
        .object-fit-cover {
            object-fit: cover;
        }
    .product-img-hover {
        position: relative;
        width: 100%;
        aspect-ratio: 1 / 1;
        overflow: hidden;
        background-color: #f9f9f9;
    }

    .product-img-hover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
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

    .product-item .text-danger {
        font-size: 16px;
        font-weight: 600;
    }
    .category-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;       /* Không bị méo ảnh */
        object-position: center; /* Lấy tâm ảnh làm gốc */
        display: block;
    }
</style>

</head>

<?php include_once("view/header.php"); ?>

<div class="container-fluid pt-5 pb-3">
    <h2 class="section-title position-relative text-uppercase mx-xl-5 mb-4">
        <?php if (!empty($products)): ?>
            <span class="bg-secondary pr-3">Kết quả tìm kiếm cho: "<?= htmlspecialchars($keyword) ?>"</span>
        <?php else: ?>
            <span class="bg-secondary pr-3">Không tìm thấy sản phẩm phù hợp</span>
        <?php endif; ?>
    </h2>

    <div class="row px-xl-5" id="product-list">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $index => $sp): ?>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2 pb-3 product-item-row">
                    <div class="product-item bg-light h-100 p-2">
                        <div class="product-img-hover">
                            <img src="img/<?= htmlspecialchars($sp['anh_dau']) ?>" alt="">
                        </div>
                        <div class="text-center py-3 px-2">
                            <a class="h6 text-decoration-none text-truncate d-block mb-2" href="index.php?detail&id=<?= $sp['id'] ?>">
                                <?= htmlspecialchars($sp['title']) ?>
                            </a>
                            <div class="product-meta mb-1"><?= htmlspecialchars($sp['description']) ?></div>
                            <div class="text-danger"><?= number_format($sp['price']) ?> đ</div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
        <!-- Nút Xem thêm / Thu gọn -->
        <?php if (true): ?>
<div class="text-center mt-3">
    <button id="show-more-btn2" class="btn btn-primary px-4">Xem thêm</button>
    <button id="collapse-btn2" class="btn btn-primary px-4 d-none">Thu gọn</button>
</div>
<?php endif; ?>
</div>

<?php include_once("view/footer.php"); ?>
