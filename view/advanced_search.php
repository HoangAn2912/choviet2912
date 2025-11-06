<?php
include_once("controller/cProduct.php");
$productController = new cProduct();

// Get all categories for filter
include_once("model/mProduct.php");
$productModel = new mProduct();
$categories = $productModel->getAllCategories();

// Get filter parameters
$filters = [
    'keyword' => trim($_GET['keyword'] ?? ''),
    'category_id' => intval($_GET['category'] ?? 0),
    'min_price' => floatval($_GET['min_price'] ?? 0),
    'max_price' => floatval($_GET['max_price'] ?? 0),
    'sort' => $_GET['sort'] ?? 'newest'
];

// Perform search
$products = $productModel->advancedSearch($filters);
$total_results = count($products);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tìm Kiếm Nâng Cao</title>
    <style>
        .filter-sidebar {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 100px;
        }

        .filter-section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .filter-section:last-child {
            border-bottom: none;
        }

        .filter-section h6 {
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 10px 15px;
        }

        .form-control:focus, .form-select:focus {
            border-color: #D19C97;
            box-shadow: 0 0 0 0.2rem rgba(209, 156, 151, 0.25);
        }

        .btn-filter {
            background: linear-gradient(135deg, #D19C97 0%, #B07570 100%);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            width: 100%;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(209, 156, 151, 0.4);
        }

        .btn-reset {
            background: #fff;
            color: #666;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 8px;
            width: 100%;
            margin-top: 10px;
        }

        .btn-reset:hover {
            background: #f8f9fa;
        }

        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .result-count {
            font-weight: 600;
            color: #333;
        }

        .sort-dropdown {
            min-width: 200px;
        }

        .product-item {
            transition: all 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
        }

        .product-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
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
            transform: scale(1.1);
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }

        .empty-state i {
            font-size: 5em;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state h4 {
            color: #666;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #999;
        }

        .active-filters {
            margin-bottom: 20px;
        }

        .filter-tag {
            display: inline-block;
            background: #D19C97;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            margin: 5px 5px 5px 0;
            font-size: 0.9em;
        }

        .filter-tag .remove {
            margin-left: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .filter-sidebar {
                margin-bottom: 20px;
                position: relative;
                top: 0;
            }
        }
    </style>
</head>

<?php include_once("view/header.php"); ?>

<div class="container-fluid pt-5 pb-3">
    <div class="row px-xl-5">
        <!-- Filter Sidebar -->
        <div class="col-lg-3">
            <div class="filter-sidebar">
                <h5 class="mb-4">🔍 Bộ Lọc Tìm Kiếm</h5>
                
                <form action="index.php" method="GET" id="filter-form">
                    <input type="hidden" name="advanced-search" value="1">
                    
                    <!-- Keyword Search -->
                    <div class="filter-section">
                        <h6>Từ khóa</h6>
                        <input type="text" 
                               class="form-control" 
                               name="keyword" 
                               placeholder="Nhập từ khóa..." 
                               value="<?= htmlspecialchars($filters['keyword']) ?>">
                    </div>

                    <!-- Category Filter -->
                    <div class="filter-section">
                        <h6>Danh mục</h6>
                        <select class="form-select" name="category">
                            <option value="0">Tất cả danh mục</option>
                            <?php 
                            $current_parent = '';
                            foreach ($categories as $cat): 
                                if ($current_parent != $cat['parent_category_name']): 
                                    if ($current_parent != '') echo '</optgroup>';
                                    $current_parent = $cat['parent_category_name'];
                                    echo '<optgroup label="' . htmlspecialchars($current_parent) . '">';
                                endif;
                            ?>
                                <option value="<?= $cat['id'] ?>" <?= $filters['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['category_name']) ?>
                                </option>
                            <?php endforeach; ?>
                            <?php if ($current_parent != '') echo '</optgroup>'; ?>
                        </select>
                    </div>

                    <!-- Price Range Filter -->
                    <div class="filter-section">
                        <h6>Khoảng giá</h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" 
                                       class="form-control" 
                                       name="min_price" 
                                       placeholder="Từ" 
                                       value="<?= $filters['min_price'] > 0 ? $filters['min_price'] : '' ?>"
                                       min="0">
                            </div>
                            <div class="col-6">
                                <input type="number" 
                                       class="form-control" 
                                       name="max_price" 
                                       placeholder="Đến" 
                                       value="<?= $filters['max_price'] > 0 ? $filters['max_price'] : '' ?>"
                                       min="0">
                            </div>
                        </div>
                        <small class="text-muted">Đơn vị: VNĐ</small>
                    </div>

                    <!-- Sort -->
                    <div class="filter-section">
                        <h6>Sắp xếp</h6>
                        <select class="form-select" name="sort">
                            <option value="newest" <?= $filters['sort'] == 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                            <option value="oldest" <?= $filters['sort'] == 'oldest' ? 'selected' : '' ?>>Cũ nhất</option>
                            <option value="price_asc" <?= $filters['sort'] == 'price_asc' ? 'selected' : '' ?>>Giá thấp → cao</option>
                            <option value="price_desc" <?= $filters['sort'] == 'price_desc' ? 'selected' : '' ?>>Giá cao → thấp</option>
                        </select>
                    </div>

                    <!-- Buttons -->
                    <button type="submit" class="btn btn-filter">
                        <i class="fa fa-search mr-2"></i>Tìm kiếm
                    </button>
                    <button type="button" class="btn btn-reset" onclick="resetFilters()">
                        <i class="fa fa-redo mr-2"></i>Xóa bộ lọc
                    </button>
                </form>
            </div>
        </div>

        <!-- Results -->
        <div class="col-lg-9">
            <!-- Active Filters -->
            <?php if (!empty($filters['keyword']) || $filters['category_id'] > 0 || $filters['min_price'] > 0 || $filters['max_price'] > 0): ?>
            <div class="active-filters">
                <strong>Bộ lọc đang áp dụng:</strong>
                <?php if (!empty($filters['keyword'])): ?>
                    <span class="filter-tag">
                        Từ khóa: "<?= htmlspecialchars($filters['keyword']) ?>"
                        <span class="remove" onclick="removeFilter('keyword')">×</span>
                    </span>
                <?php endif; ?>
                <?php if ($filters['category_id'] > 0): ?>
                    <?php 
                    $cat_name = '';
                    foreach ($categories as $cat) {
                        if ($cat['id'] == $filters['category_id']) {
                            $cat_name = $cat['category_name'];
                            break;
                        }
                    }
                    ?>
                    <span class="filter-tag">
                        Danh mục: <?= htmlspecialchars($cat_name) ?>
                        <span class="remove" onclick="removeFilter('category')">×</span>
                    </span>
                <?php endif; ?>
                <?php if ($filters['min_price'] > 0 || $filters['max_price'] > 0): ?>
                    <span class="filter-tag">
                        Giá: <?= $filters['min_price'] > 0 ? number_format($filters['min_price']) : '0' ?>đ 
                        - <?= $filters['max_price'] > 0 ? number_format($filters['max_price']) : '∞' ?>đ
                        <span class="remove" onclick="removeFilter('price')">×</span>
                    </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Result Header -->
            <div class="result-header">
                <div class="result-count">
                    <i class="fa fa-check-circle text-success"></i>
                    Tìm thấy <strong><?= $total_results ?></strong> sản phẩm
                </div>
            </div>

            <!-- Product Grid -->
            <?php if (!empty($products)): ?>
            <div class="row">
                <?php foreach ($products as $sp): ?>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2-4 pb-3">
                    <div class="product-item bg-light h-100 p-2">
                        <div class="product-img-hover">
                            <img src="img/<?= htmlspecialchars($sp['anh_dau']) ?>" alt="">
                        </div>
                        <div class="text-center py-3 px-2">
                            <a class="h6 text-decoration-none text-truncate d-block mb-2" 
                               href="index.php?detail&id=<?= $sp['id'] ?>">
                                <?= htmlspecialchars($sp['title']) ?>
                            </a>
                            <?php if (!empty($sp['category_name'])): ?>
                            <div class="text-muted small mb-1">
                                <i class="fa fa-tag"></i> <?= htmlspecialchars($sp['category_name']) ?>
                            </div>
                            <?php endif; ?>
                            <div class="text-danger font-weight-bold">
                                <?= number_format($sp['price']) ?> đ
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div style="font-size: 5em; color: #ddd; margin-bottom: 20px;">🔍</div>
                <h4>Không tìm thấy sản phẩm phù hợp</h4>
                <p>Vui lòng thử lại với bộ lọc khác hoặc từ khóa khác.</p>
                <button class="btn btn-primary mt-3" onclick="resetFilters()">
                    <i class="fa fa-redo mr-2"></i>Xóa bộ lọc và tìm lại
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function resetFilters() {
    window.location.href = 'index.php?advanced-search=1';
}

function removeFilter(filterType) {
    const url = new URL(window.location.href);
    
    switch(filterType) {
        case 'keyword':
            url.searchParams.delete('keyword');
            break;
        case 'category':
            url.searchParams.delete('category');
            break;
        case 'price':
            url.searchParams.delete('min_price');
            url.searchParams.delete('max_price');
            break;
    }
    
    window.location.href = url.toString();
}
</script>

<?php include_once("view/footer.php"); ?>











