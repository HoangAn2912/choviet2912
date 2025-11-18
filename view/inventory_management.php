<?php
require_once __DIR__ . '/../helpers/Security.php';
require_once __DIR__ . '/../controller/cInventory.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: loginlogout/login.php");
    exit;
}

$controller = new cInventory();
$data = $controller->showInventoryDashboard();
$products = $data['products'];
$stats = $data['stats'];
$low_stock = $data['low_stock'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Tồn Kho</title>
    <?php echo Security::csrfMetaTag(); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .stats-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            text-align: center;
            padding: 20px;
            border-radius: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stat-box.success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .stat-box.warning {
            background: linear-gradient(135deg, #ee9ca7 0%, #ffdde1 100%);
            color: #333;
        }

        .stat-box.danger {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
        }

        .stat-box h3 {
            font-size: 2.5em;
            margin: 0;
            font-weight: bold;
        }

        .stat-box p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }

        .inventory-table {
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .inventory-table th {
            background: #667eea;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        .inventory-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .inventory-table tr:hover {
            background: #f8f9fa;
        }

        .stock-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            display: inline-block;
        }

        .stock-badge.in-stock {
            background: #d4edda;
            color: #155724;
        }

        .stock-badge.low-stock {
            background: #fff3cd;
            color: #856404;
        }

        .stock-badge.out-of-stock {
            background: #f8d7da;
            color: #721c24;
        }

        .btn-action {
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9em;
            margin: 0 3px;
            transition: all 0.3s ease;
        }

        .btn-action.btn-edit {
            background: #007bff;
            color: white;
        }

        .btn-action.btn-adjust {
            background: #28a745;
            color: white;
        }

        .btn-action.btn-history {
            background: #6c757d;
            color: white;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            animation: slideIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
        }

        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
        }

        .alert-info {
            background: #d1ecf1;
            border: 1px solid #17a2b8;
            color: #0c5460;
        }

        .product-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 4em;
            color: #ddd;
            margin-bottom: 20px;
        }
    </style>
</head>

<?php include_once("view/header.php"); ?>

<div class="container-fluid pt-5 pb-3">
    <div class="row px-xl-5">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-boxes mr-2"></i>Quản Lý Tồn Kho</h2>

            <!-- Stats Dashboard -->
            <div class="stats-grid">
                <div class="stat-box success">
                    <h3><?= $stats['in_stock'] ?? 0 ?></h3>
                    <p><i class="fas fa-check-circle"></i> Còn hàng</p>
                </div>
                <div class="stat-box warning">
                    <h3><?= $stats['low_stock'] ?? 0 ?></h3>
                    <p><i class="fas fa-exclamation-triangle"></i> Sắp hết</p>
                </div>
                <div class="stat-box danger">
                    <h3><?= $stats['out_of_stock'] ?? 0 ?></h3>
                    <p><i class="fas fa-times-circle"></i> Hết hàng</p>
                </div>
                <div class="stat-box">
                    <h3><?= number_format($stats['total_stock_quantity'] ?? 0) ?></h3>
                    <p><i class="fas fa-cubes"></i> Tổng tồn kho</p>
                </div>
            </div>

            <!-- Low Stock Alert -->
            <?php if (!empty($low_stock)): ?>
            <div class="alert alert-warning">
                <strong><i class="fas fa-exclamation-triangle"></i> Cảnh báo:</strong>
                Có <?= count($low_stock) ?> sản phẩm sắp hết hàng. Vui lòng nhập thêm!
            </div>
            <?php endif; ?>

            <!-- Products Table -->
            <?php if (!empty($products)): ?>
            <table class="inventory-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Giá</th>
                        <th>Tồn kho</th>
                        <th>Ngưỡng cảnh báo</th>
                        <th>Trạng thái</th>
                        <th>Quản lý kho</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): 
                        $stock_status = '';
                        if ($p['track_inventory'] == 0) {
                            $stock_status = '<span class="stock-badge in-stock">Không theo dõi</span>';
                        } elseif ($p['stock_quantity'] === null) {
                            $stock_status = '<span class="stock-badge in-stock">Không giới hạn</span>';
                        } elseif ($p['stock_quantity'] == 0) {
                            $stock_status = '<span class="stock-badge out-of-stock">Hết hàng</span>';
                        } elseif ($p['stock_quantity'] <= $p['low_stock_alert']) {
                            $stock_status = '<span class="stock-badge low-stock">Sắp hết</span>';
                        } else {
                            $stock_status = '<span class="stock-badge in-stock">Còn hàng</span>';
                        }
                    ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <?php if (!empty($p['first_image'])): ?>
                                <img src="img/<?= htmlspecialchars($p['first_image']) ?>" class="product-thumb" alt="">
                                <?php endif; ?>
                                <span><?= htmlspecialchars($p['title']) ?></span>
                            </div>
                        </td>
                        <td><?= number_format($p['price']) ?>đ</td>
                        <td><strong><?= $p['track_inventory'] ? ($p['stock_quantity'] ?? 'N/A') : 'N/A' ?></strong></td>
                        <td><?= $p['track_inventory'] ? $p['low_stock_alert'] : 'N/A' ?></td>
                        <td><?= $stock_status ?></td>
                        <td>
                            <?= $p['track_inventory'] ? '<i class="fas fa-check text-success"></i> Có' : '<i class="fas fa-times text-muted"></i> Không' ?>
                        </td>
                        <td>
                            <button class="btn-action btn-edit" onclick="editSettings(<?= $p['id'] ?>, <?= $p['track_inventory'] ?>, <?= $p['stock_quantity'] ?? 0 ?>, <?= $p['low_stock_alert'] ?>)">
                                <i class="fas fa-cog"></i> Cài đặt
                            </button>
                            <?php if ($p['track_inventory']): ?>
                            <button class="btn-action btn-adjust" onclick="adjustStock(<?= $p['id'] ?>, '<?= htmlspecialchars($p['title']) ?>')">
                                <i class="fas fa-plus-minus"></i> Điều chỉnh
                            </button>
                            <button class="btn-action btn-history" onclick="viewHistory(<?= $p['id'] ?>)">
                                <i class="fas fa-history"></i> Lịch sử
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h4>Chưa có sản phẩm livestream nào</h4>
                <p>Thêm sản phẩm và đánh dấu là "Sản phẩm livestream" để quản lý tồn kho.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal: Edit Settings -->
<div id="modalSettings" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modalSettings')">&times;</span>
        <h3><i class="fas fa-cog"></i> Cài Đặt Tồn Kho</h3>
        <form id="formSettings">
            <input type="hidden" name="product_id" id="settings_product_id">
            <?php echo Security::csrfInput(); ?>
            
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" name="is_livestream_product" id="is_livestream_product" value="1">
                    <label for="is_livestream_product" style="margin: 0;">Sản phẩm livestream</label>
                </div>
            </div>

            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" name="track_inventory" id="track_inventory" value="1">
                    <label for="track_inventory" style="margin: 0;">Theo dõi tồn kho</label>
                </div>
            </div>

            <div class="form-group">
                <label>Số lượng tồn kho</label>
                <input type="number" name="stock_quantity" id="stock_quantity" min="0" placeholder="0">
            </div>

            <div class="form-group">
                <label>Ngưỡng cảnh báo hết hàng</label>
                <input type="number" name="low_stock_alert" id="low_stock_alert" min="0" value="5" placeholder="5">
            </div>

            <button type="submit" class="btn-action btn-edit" style="width: 100%; padding: 12px;">
                <i class="fas fa-save"></i> Lưu cài đặt
            </button>
        </form>
    </div>
</div>

<!-- Modal: Adjust Stock -->
<div id="modalAdjust" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modalAdjust')">&times;</span>
        <h3><i class="fas fa-plus-minus"></i> Điều Chỉnh Tồn Kho</h3>
        <p id="adjust_product_name" style="color: #666; margin-bottom: 20px;"></p>
        <form id="formAdjust">
            <input type="hidden" name="product_id" id="adjust_product_id">
            <?php echo Security::csrfInput(); ?>
            
            <div class="form-group">
                <label>Loại điều chỉnh</label>
                <select name="change_type" id="change_type">
                    <option value="restock">Nhập thêm hàng</option>
                    <option value="adjustment">Điều chỉnh khác</option>
                </select>
            </div>

            <div class="form-group">
                <label>Số lượng thay đổi (âm = giảm, dương = tăng)</label>
                <input type="number" name="quantity_change" id="quantity_change" placeholder="Vd: +10 hoặc -5">
            </div>

            <div class="form-group">
                <label>Ghi chú</label>
                <textarea name="note" id="note" rows="3" placeholder="Lý do điều chỉnh..."></textarea>
            </div>

            <button type="submit" class="btn-action btn-adjust" style="width: 100%; padding: 12px;">
                <i class="fas fa-check"></i> Xác nhận điều chỉnh
            </button>
        </form>
    </div>
</div>

<!-- Modal: History -->
<div id="modalHistory" class="modal">
    <div class="modal-content" style="max-width: 900px;">
        <span class="close" onclick="closeModal('modalHistory')">&times;</span>
        <h3><i class="fas fa-history"></i> Lịch Sử Biến Động Tồn Kho</h3>
        <div id="history_content">
            <p class="text-center text-muted">Đang tải...</p>
        </div>
    </div>
</div>

<script src="js/csrf-handler.js"></script>
<script>
function editSettings(productId, trackInventory, stockQuantity, lowStockAlert) {
    document.getElementById('settings_product_id').value = productId;
    document.getElementById('is_livestream_product').checked = true; // Always true for products in this page
    document.getElementById('track_inventory').checked = trackInventory == 1;
    document.getElementById('stock_quantity').value = stockQuantity;
    document.getElementById('low_stock_alert').value = lowStockAlert;
    document.getElementById('modalSettings').style.display = 'block';
}

function adjustStock(productId, productName) {
    document.getElementById('adjust_product_id').value = productId;
    document.getElementById('adjust_product_name').textContent = 'Sản phẩm: ' + productName;
    document.getElementById('quantity_change').value = '';
    document.getElementById('note').value = '';
    document.getElementById('modalAdjust').style.display = 'block';
}

function viewHistory(productId) {
    document.getElementById('modalHistory').style.display = 'block';
    document.getElementById('history_content').innerHTML = '<p class="text-center text-muted">Đang tải...</p>';
    
    fetch('index.php?inventory-history&product_id=' + productId)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                let html = '<table class="inventory-table"><thead><tr><th>Thời gian</th><th>Loại</th><th>Thay đổi</th><th>Cũ → Mới</th><th>Người thực hiện</th><th>Ghi chú</th></tr></thead><tbody>';
                data.data.forEach(item => {
                    let typeLabel = {
                        'sale': 'Bán hàng',
                        'return': 'Trả hàng',
                        'restock': 'Nhập thêm',
                        'adjustment': 'Điều chỉnh',
                        'initial': 'Khởi tạo'
                    }[item.change_type] || item.change_type;
                    
                    html += `<tr>
                        <td>${item.created_at}</td>
                        <td>${typeLabel}</td>
                        <td style="color: ${item.quantity_change > 0 ? 'green' : 'red'}; font-weight: bold;">${item.quantity_change > 0 ? '+' : ''}${item.quantity_change}</td>
                        <td>${item.old_quantity} → ${item.new_quantity}</td>
                        <td>${item.created_by_name || 'Hệ thống'}</td>
                        <td>${item.note || '-'}</td>
                    </tr>`;
                });
                html += '</tbody></table>';
                document.getElementById('history_content').innerHTML = html;
            } else {
                document.getElementById('history_content').innerHTML = '<p class="text-center text-muted">Chưa có lịch sử biến động.</p>';
            }
        })
        .catch(err => {
            document.getElementById('history_content').innerHTML = '<p class="text-center text-danger">Lỗi tải dữ liệu.</p>';
        });
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Form handlers
document.getElementById('formSettings').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('index.php?inventory-update-settings', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            closeModal('modalSettings');
            location.reload();
        }
    })
    .catch(err => alert('Lỗi: ' + err));
});

document.getElementById('formAdjust').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('index.php?inventory-adjust-stock', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            closeModal('modalAdjust');
            location.reload();
        }
    })
    .catch(err => alert('Lỗi: ' + err));
});

// Close modal on outside click
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>

<?php include_once("view/footer.php"); ?>






























