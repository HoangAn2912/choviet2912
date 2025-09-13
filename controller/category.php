<?php
// xử lý danh mục con
include_once "model/mConnect.php";
$conn = (new Connect())->connect();

$idCha = isset($_GET['id_cha']) ? (int)$_GET['id_cha'] : 0;
$idCon = isset($_GET['id_con']) ? (int)$_GET['id_con'] : 0;

// Nếu có danh mục con, lọc theo id_con
if ($idCon > 0) {
            $sql = "SELECT * FROM products WHERE category_id = $idCon";
}
// Nếu chỉ có danh mục cha, lọc theo các danh mục con của nó
elseif ($idCha > 0) {
            $sql = "SELECT sp.* FROM products sp
        JOIN product_categories lsp ON sp.category_id = lsp.id
        WHERE lsp.parent_category_id = $idCha";
}
// Nếu không có gì, hiển thị tất cả sản phẩm
else {
    $sql = "SELECT * FROM products";
}

$result = mysqli_query($conn, $sql);
?>

<h2>Danh sách sản phẩm</h2>
<ul>
    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
        <li><?= htmlspecialchars($row['title']) ?> - <?= number_format($row['price']) ?> VND</li>
    <?php endwhile; ?>
</ul>
