<?php
include_once "mConnect.php";

class mProduct {
    private $conn;

    public function __construct() {
        $p = new Connect();
        $this->conn = $p->connect();
    }

    public function getSanPhamMoiNhat($limit = 100) {
        $sql = "SELECT sp.*, nd.username, nd.avatar, nd.phone, nd.address
                FROM products sp 
                JOIN users nd ON sp.user_id = nd.id 
                WHERE sp.sale_status = 'Đang bán' AND sp.status = 'Đã duyệt'
                ORDER BY sp.updated_date DESC, sp.created_date DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];

        while ($row = $result->fetch_assoc()) {
            // Nếu có nhiều ảnh, tách lấy ảnh đầu tiên
            if (!empty($row['image'])) {
                $dsAnh = array_map('trim', explode(',', $row['image']));
                $row['anh_dau'] = $dsAnh[0] ?? ''; // ảnh đầu tiên
            } else {
                $row['anh_dau'] = '';
            }
            $data[] = $row;
        }
        return $data;
    }

    public function getSanPhamMoiNhatTheoViTri($provinceName, $districtName = '', $limit = 100) {
        $sql = "SELECT sp.*, nd.username, nd.avatar, nd.phone, nd.address
                FROM products sp 
                JOIN users nd ON sp.user_id = nd.id 
                WHERE sp.sale_status = 'Đang bán' AND sp.status = 'Đã duyệt'";

        $params = [];
        $types = "";
        $sql .= $this->buildLocationWhereClause($provinceName, $districtName, $types, $params);

        $sql .= " ORDER BY sp.updated_date DESC, sp.created_date DESC
                LIMIT ?";
        $params[] = $limit;
        $types .= "i";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['image'])) {
                $dsAnh = array_map('trim', explode(',', $row['image']));
                $row['anh_dau'] = $dsAnh[0] ?? '';
            } else {
                $row['anh_dau'] = '';
            }
            $data[] = $row;
        }
        return $data;
    }

    public function tinhThoiGian($created_date) {
        $now = new DateTime();
        $created = new DateTime($created_date);
        $diff = $now->diff($created);
    
        if ($diff->days == 0 && $diff->h == 0 && $diff->i < 60) return $diff->i . " phút trước";
        if ($diff->days == 0 && $diff->h < 24) return $diff->h . " giờ trước";
        if ($diff->days == 1) return "Hôm qua";
        if ($diff->days <= 6) return $diff->days . " ngày trước";
        if ($diff->days <= 30) return "Tuần trước";
        return "Tháng trước";
    }


    public function getSanPhamById($id) {
        $sql = "SELECT * FROM products WHERE id = ? AND status = 'Đã duyệt'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
    
        // xử lý chuỗi ảnh thành mảng
        if ($data && isset($data['image'])) {
            $data['ds_anh'] = array_map('trim', explode(',', $data['image']));
        }
    
        return $data;
    }
    
    public function searchProducts($keyword, $provinceName = '', $districtName = '') {
        $sql = "SELECT sp.*, nd.username, nd.avatar, nd.phone, nd.address
                FROM products sp 
                JOIN users nd ON sp.user_id = nd.id 
                WHERE sp.sale_status = 'Đang bán' AND sp.status = 'Đã duyệt' AND sp.title LIKE ?";
        $params = [];
        $types = "";
        $likeKeyword = '%' . $keyword . '%';
        $params[] = $likeKeyword;
        $types .= "s";

        $sql .= $this->buildLocationWhereClause($provinceName, $districtName, $types, $params);

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $data = [];
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['image'])) {
                $dsAnh = array_map('trim', explode(',', $row['image']));
                $row['anh_dau'] = $dsAnh[0] ?? '';
            } else {
                $row['anh_dau'] = '';
            }
            $data[] = $row;
        }
        return $data;
    }

    /**
     * Tìm kiếm nâng cao với filter
     * 
     * @param array $filters - Bộ lọc:
     *   - keyword: Từ khóa tìm kiếm
     *   - category_id: ID danh mục
     *   - min_price: Giá tối thiểu
     *   - max_price: Giá tối đa
     *   - sort: Sắp xếp (newest, price_asc, price_desc)
     * @return array
     */
    public function advancedSearch($filters = []) {
        // Build WHERE conditions
        $where = ["sp.sale_status = 'Đang bán'", "sp.status = 'Đã duyệt'"];
        $params = [];
        $types = "";

        // Keyword search
        if (!empty($filters['keyword'])) {
            $where[] = "sp.title LIKE ?";
            $params[] = '%' . $filters['keyword'] . '%';
            $types .= "s";
        }

        // Category filter
        if (!empty($filters['category_id']) && $filters['category_id'] > 0) {
            $where[] = "sp.category_id = ?";
            $params[] = intval($filters['category_id']);
            $types .= "i";
        }

        // Price range filter
        if (!empty($filters['min_price']) && $filters['min_price'] > 0) {
            $where[] = "sp.price >= ?";
            $params[] = floatval($filters['min_price']);
            $types .= "d";
        }

        if (!empty($filters['max_price']) && $filters['max_price'] > 0) {
            $where[] = "sp.price <= ?";
            $params[] = floatval($filters['max_price']);
            $types .= "d";
        }

        // Build ORDER BY
        $orderBy = "sp.updated_date DESC, sp.created_date DESC"; // Default: Mới nhất
        
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_asc':
                    $orderBy = "sp.price ASC";
                    break;
                case 'price_desc':
                    $orderBy = "sp.price DESC";
                    break;
                case 'oldest':
                    $orderBy = "sp.created_date ASC";
                    break;
                case 'newest':
                default:
                    $orderBy = "sp.updated_date DESC, sp.created_date DESC";
                    break;
            }
        }

        // Build final query
        $whereClause = implode(" AND ", $where);
        $sql = "SELECT sp.*, nd.username, nd.avatar, nd.phone, nd.address,
                       pc.category_name
                FROM products sp 
                JOIN users nd ON sp.user_id = nd.id
                LEFT JOIN product_categories pc ON sp.category_id = pc.id
                WHERE {$whereClause}
                ORDER BY {$orderBy}";

        $stmt = $this->conn->prepare($sql);

        // Bind parameters if any
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            // Process image
            if (!empty($row['image'])) {
                $dsAnh = array_map('trim', explode(',', $row['image']));
                $row['anh_dau'] = $dsAnh[0] ?? '';
            } else {
                $row['anh_dau'] = '';
            }
            $data[] = $row;
        }

        return $data;
    }

    /**
     * Lấy tất cả danh mục con (cho filter dropdown)
     */
    public function getAllCategories() {
        $sql = "SELECT pc.*, p.parent_category_name
                FROM product_categories pc
                LEFT JOIN parent_categories p ON pc.parent_category_id = p.parent_category_id
                ORDER BY p.parent_category_name, pc.category_name";
        
        $result = $this->conn->query($sql);
        $data = [];
        
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    // Lấy sản phẩm theo user_id
    public function getSanPhamByUserId($user_id) {
        $sql = "SELECT * FROM products WHERE user_id = ? AND status = 'Đã duyệt' ORDER BY created_date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];

        while ($row = $result->fetch_assoc()) {
            // Nếu có nhiều ảnh, tách lấy ảnh đầu tiên
            if (!empty($row['image'])) {
                $dsAnh = array_map('trim', explode(',', $row['image']));
                $row['anh_dau'] = $dsAnh[0] ?? ''; // ảnh đầu tiên
            } else {
                $row['anh_dau'] = '';
            }
            $data[] = $row;
        }
        return $data;
    }

    // Lấy sản phẩm của user
    public function getProductsByUserId($user_id) {
        $sql = "SELECT id, title, price, image, description, sale_status, status
                FROM products 
                WHERE user_id = ? AND status = 'Đã duyệt'
                ORDER BY created_date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];

        while ($row = $result->fetch_assoc()) {
            // Xử lý ảnh sản phẩm
            if (!empty($row['image'])) {
                $dsAnh = array_map('trim', explode(',', $row['image']));
                $row['anh_dau'] = $dsAnh[0] ?? ''; // ảnh đầu tiên
            } else {
                $row['anh_dau'] = '';
            }
            $data[] = $row;
        }
        
        return $data;
    }

    /**
     * Lấy sản phẩm hot nhất (dựa trên số lượng đơn hàng thành công)
     * @param int $limit Số lượng sản phẩm cần lấy
     * @return array
     */
    public function getHotProducts($limit = 10) {
        $sql = "SELECT sp.*, nd.username, nd.avatar, nd.phone, nd.address,
                       COALESCE(SUM(CASE WHEN lo.status != 'cancelled' THEN loi.quantity ELSE 0 END), 0) as total_sold,
                       COALESCE(COUNT(DISTINCT CASE WHEN lo.status != 'cancelled' THEN lo.id END), 0) as successful_orders
                FROM products sp 
                JOIN users nd ON sp.user_id = nd.id 
                LEFT JOIN livestream_order_items loi ON sp.id = loi.product_id
                LEFT JOIN livestream_orders lo ON loi.order_id = lo.id
                WHERE sp.sale_status = 'Đang bán' AND sp.status = 'Đã duyệt'
                GROUP BY sp.id
                ORDER BY 
                    total_sold DESC,
                    successful_orders DESC,
                    sp.updated_date DESC,
                    sp.created_date DESC
                LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];

        while ($row = $result->fetch_assoc()) {
            // Xử lý ảnh sản phẩm
            if (!empty($row['image'])) {
                $dsAnh = array_map('trim', explode(',', $row['image']));
                $row['anh_dau'] = $dsAnh[0] ?? '';
            } else {
                $row['anh_dau'] = '';
            }
            $data[] = $row;
        }
        
        return $data;
    }

    private function buildLocationWhereClause($provinceName, $districtName, &$types, &$params) {
        $clauses = [];

        if (!empty($provinceName)) {
            $provinceVariants = $this->buildAddressPatterns($provinceName, true);
            if (!empty($provinceVariants)) {
                $provinceClauses = [];
                foreach ($provinceVariants as $variant) {
                    $provinceClauses[] = "nd.address LIKE ?";
                    $params[] = '%' . $variant . '%';
                    $types .= "s";
                }
                $clauses[] = '(' . implode(' OR ', $provinceClauses) . ')';
            }
        }

        if (!empty($districtName)) {
            $districtVariants = $this->buildAddressPatterns($districtName, false);
            if (!empty($districtVariants)) {
                $districtClauses = [];
                foreach ($districtVariants as $variant) {
                    $districtClauses[] = "nd.address LIKE ?";
                    $params[] = '%' . $variant . '%';
                    $types .= "s";
                }
                $clauses[] = '(' . implode(' OR ', $districtClauses) . ')';
            }
        }

        return $clauses ? ' AND ' . implode(' AND ', $clauses) : '';
    }

    private function buildAddressPatterns($name, $isProvince = true) {
        $name = trim($name);
        if ($name === '') {
            return [];
        }

        $variants = [];
        $normalized = preg_replace('/\s+/u', ' ', $name);
        $variants[] = $normalized;

        $core = preg_replace('/^(Tỉnh|Thành phố|TP\\.?)/iu', '', $normalized);
        $core = trim($core);
        if (!empty($core) && $core !== $normalized) {
            $variants[] = $core;
        }

        if ($isProvince && stripos($normalized, 'Thành phố') === 0 && !empty($core)) {
            $variants[] = 'TP ' . $core;
            $variants[] = 'TP. ' . $core;
        }

        if (!$isProvince && !empty($core)) {
            $districtCore = preg_replace('/^(Quận|Huyện|Thị xã|Thành phố|TP\\.?)/iu', '', $normalized);
            $districtCore = trim($districtCore);
            if (!empty($districtCore) && $districtCore !== $normalized) {
                $variants[] = $districtCore;
            }
            if (stripos($normalized, 'Quận') === 0) {
                $variants[] = 'Q. ' . $districtCore;
                $variants[] = 'Q ' . $districtCore;
            }
        }

        $variants = array_values(array_unique(array_filter($variants)));
        return $variants;
    }
    
    
}
?>
