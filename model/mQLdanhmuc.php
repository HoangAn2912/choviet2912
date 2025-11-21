<?php
include_once("mConnect.php");

class mLoaiSanPham {
    private $conn;
    
    public function __construct() {
        $this->conn = new Connect();
        $this->conn = $this->conn->connect();
    }
    
    // Check if column exists in a specific table
    private function columnExists($table, $column) {
        $table = $this->conn->real_escape_string($table);
        $result = $this->conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        return $result && $result->num_rows > 0;
    }
    
    // Parent Category Methods
    
    // Get all parent categories (only non-hidden by default)
    public function getAllParentCategories($includeHidden = false) {
        $query = "SELECT * FROM parent_categories";
        if (!$includeHidden && $this->columnExists('parent_categories', 'is_hidden')) {
            $query .= " WHERE (is_hidden = 0 OR is_hidden IS NULL)";
        }
        $query .= " ORDER BY parent_category_name";
        $result = $this->conn->query($query);
        $data = array();
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    // Get parent category by ID
    public function getParentCategoryById($id) {
        $id = $this->conn->real_escape_string($id);
        $query = "SELECT * FROM parent_categories WHERE parent_category_id = '$id'";
        $result = $this->conn->query($query);
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    // Add new parent category
    public function addParentCategory($name) {
        $name = $this->conn->real_escape_string($name);
        $query = "INSERT INTO parent_categories (parent_category_name) VALUES ('$name')";
        
        if ($this->conn->query($query)) {
            return $this->conn->insert_id;
        }
        
        return false;
    }
    
    // Update parent category
    public function updateParentCategory($id, $name) {
        $id = $this->conn->real_escape_string($id);
        $name = $this->conn->real_escape_string($name);
        $query = "UPDATE parent_categories SET parent_category_name = '$name' WHERE parent_category_id = '$id'";
        
        return $this->conn->query($query);
    }
    
    // Delete parent category
    public function deleteParentCategory($id) {
        $id = $this->conn->real_escape_string($id);
        
        // First check if there are child categories
        $checkQuery = "SELECT COUNT(*) as count FROM product_categories WHERE parent_category_id = '$id'";
        $result = $this->conn->query($checkQuery);
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            return false; // Cannot delete parent with children
        }
        
        $query = "DELETE FROM parent_categories WHERE parent_category_id = '$id'";
        return $this->conn->query($query);
    }
    
    // Hide parent category (set is_hidden = 1)
    public function hideParentCategory($id) {
        $id = $this->conn->real_escape_string($id);
        
        // Check if is_hidden column exists in parent_categories
        if (!$this->columnExists('parent_categories', 'is_hidden')) {
            // Create the column if it doesn't exist
            $this->conn->query("ALTER TABLE parent_categories ADD COLUMN is_hidden TINYINT(1) DEFAULT 0");
        }
        
        // First check if there are active child categories
        $checkQuery = "SELECT COUNT(*) as count FROM product_categories WHERE parent_category_id = '$id'";
        if ($this->columnExists('product_categories', 'is_hidden')) {
            $checkQuery .= " AND (is_hidden = 0 OR is_hidden IS NULL)";
        }
        $result = $this->conn->query($checkQuery);
        if ($result) {
            $row = $result->fetch_assoc();
            if ($row['count'] > 0) {
                return false; // Cannot hide parent with active children
            }
        }
        
        // Update is_hidden = 1
        $query = "UPDATE parent_categories SET is_hidden = 1 WHERE parent_category_id = '$id'";
        return $this->conn->query($query);
    }
    
    // Restore (unhide) parent category
    public function restoreParentCategory($id) {
        $id = $this->conn->real_escape_string($id);
        $query = "UPDATE parent_categories SET is_hidden = 0 WHERE parent_category_id = '$id'";
        return $this->conn->query($query);
    }
    
    // Count child categories for a parent
    public function acountChildCategories($parentId) {
        $parentId = $this->conn->real_escape_string($parentId);
        $query = "SELECT COUNT(*) as count FROM product_categories WHERE parent_category_id = '$parentId'";
        if ($this->columnExists('product_categories', 'is_hidden')) {
            $query .= " AND (is_hidden = 0 OR is_hidden IS NULL)";
        }
        $result = $this->conn->query($query);
        if ($result) {
            $row = $result->fetch_assoc();
            return $row['count'];
        }
        return 0;
    }
    
    // Child Category Methods
    
    // Get all child categories with parent info (only non-hidden by default)
    public function getAllChildCategories($includeHidden = false) {
        $query = "SELECT c.*, p.parent_category_name 
                 FROM product_categories c 
                 LEFT JOIN parent_categories p ON c.parent_category_id = p.parent_category_id";
        if (!$includeHidden && $this->columnExists('product_categories', 'is_hidden')) {
            $query .= " WHERE (c.is_hidden = 0 OR c.is_hidden IS NULL)";
        }
        $query .= " ORDER BY p.parent_category_name, c.category_name";
        $result = $this->conn->query($query);
        $data = array();
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    // Get paginated child categories with filters
    public function getPaginatedChildCategories($offset, $limit, $parentFilter = 'all', $searchTerm = '', $showHidden = false) {
        $query = "SELECT c.*, p.parent_category_name 
                 FROM product_categories c 
                 LEFT JOIN parent_categories p ON c.parent_category_id = p.parent_category_id 
                 WHERE 1=1";
        
        // Apply hidden filter (default: only show non-hidden)
        if (!$showHidden && $this->columnExists('product_categories', 'is_hidden')) {
            $query .= " AND (c.is_hidden = 0 OR c.is_hidden IS NULL)";
        }
        
        // Apply parent filter
        if ($parentFilter != 'all') {
            $query .= " AND c.parent_category_id = '" . $this->conn->real_escape_string($parentFilter) . "'";
        }
        
        // Apply search filter
        if (!empty($searchTerm)) {
            $searchTerm = $this->conn->real_escape_string($searchTerm);
            $query .= " AND (c.category_name LIKE '%$searchTerm%' OR p.parent_category_name LIKE '%$searchTerm%')";
        }
        
        $query .= " ORDER BY p.parent_category_name, c.category_name LIMIT $offset, $limit";
        
        $result = $this->conn->query($query);
        $data = array();
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    // Count child categories for pagination
    public function countChildCategories($parentFilter = 'all', $searchTerm = '', $showHidden = false) {
        $query = "SELECT COUNT(*) as total 
                FROM product_categories c 
                LEFT JOIN parent_categories p ON c.parent_category_id = p.parent_category_id 
                WHERE 1=1";
        
        // Apply hidden filter (default: only count non-hidden)
        if (!$showHidden && $this->columnExists('product_categories', 'is_hidden')) {
            $query .= " AND (c.is_hidden = 0 OR c.is_hidden IS NULL)";
        }
        
        // Apply parent filter
        if ($parentFilter != 'all') {
            $query .= " AND c.parent_category_id = '" . $this->conn->real_escape_string($parentFilter) . "'";
        }
        
        // Apply search filter
        if (!empty($searchTerm)) {
            $searchTerm = $this->conn->real_escape_string($searchTerm);
            $query .= " AND (c.category_name LIKE '%$searchTerm%' OR p.parent_category_name LIKE '%$searchTerm%')";
        }
        
        $result = $this->conn->query($query);
        if ($result) {
            $row = $result->fetch_assoc();
            return $row['total'];
        }
        
        return 0;
    }
    
    // Get child categories by parent ID
    public function getChildCategoriesByParentId($parentId) {
        $parentId = $this->conn->real_escape_string($parentId);
        $query = "SELECT * FROM product_categories WHERE parent_category_id = '$parentId' ORDER BY category_name";
        $result = $this->conn->query($query);
        $data = array();
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    // Get child category by ID
    public function getChildCategoryById($id) {
        $id = $this->conn->real_escape_string($id);
        $query = "SELECT c.*, p.parent_category_name 
                 FROM product_categories c 
                 LEFT JOIN parent_categories p ON c.parent_category_id = p.parent_category_id 
                 WHERE c.id = '$id'";
        $result = $this->conn->query($query);
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    // Add new child category
    public function addChildCategory($name, $parentId) {
        $name = $this->conn->real_escape_string($name);
        $parentId = $this->conn->real_escape_string($parentId);
        $query = "INSERT INTO product_categories (category_name, parent_category_id) VALUES ('$name', '$parentId')";
        
        if ($this->conn->query($query)) {
            return $this->conn->insert_id;
        }
        
        return false;
    }
    
    // Update child category
    public function updateChildCategory($id, $name, $parentId) {
        $id = $this->conn->real_escape_string($id);
        $name = $this->conn->real_escape_string($name);
        $parentId = $this->conn->real_escape_string($parentId);
        $query = "UPDATE product_categories SET category_name = '$name', parent_category_id = '$parentId' WHERE id = '$id'";
        
        return $this->conn->query($query);
    }
    
    // Delete child category
    public function deleteChildCategory($id) {
        $id = $this->conn->real_escape_string($id);
        
        // Check if there are products using this category
        // This would require a products table with a category_id field
        // For now, we'll assume it's safe to delete
        
        $query = "DELETE FROM product_categories WHERE id = '$id'";
        return $this->conn->query($query);
    }
    
    // Hide child category (set is_hidden = 1)
    public function hideChildCategory($id) {
        $id = $this->conn->real_escape_string($id);
        
        // Check if is_hidden column exists, create if not
        if (!$this->columnExists('product_categories', 'is_hidden')) {
            $this->conn->query("ALTER TABLE product_categories ADD COLUMN is_hidden TINYINT(1) DEFAULT 0");
        }
        
        // Update is_hidden = 1
        $query = "UPDATE product_categories SET is_hidden = 1 WHERE id = '$id'";
        return $this->conn->query($query);
    }
    
    // Restore (unhide) child category
    public function restoreChildCategory($id) {
        $id = $this->conn->real_escape_string($id);
        $query = "UPDATE product_categories SET is_hidden = 0 WHERE id = '$id'";
        return $this->conn->query($query);
    }
    
    // Count products using a category
    public function countProductsInCategory($categoryId) {
        // This would require a products table with a category_id field
        // For now, we'll return 0
        return 0;
    }
    
    // Statistics Methods
    
    // Get category statistics (only count non-hidden)
    public function getCategoryStats() {
        $parentWhere = "";
        $childWhere = "";
        
        if ($this->columnExists('parent_categories', 'is_hidden')) {
            $parentWhere = " WHERE (is_hidden = 0 OR is_hidden IS NULL)";
        }
        if ($this->columnExists('product_categories', 'is_hidden')) {
            $childWhere = " WHERE (is_hidden = 0 OR is_hidden IS NULL)";
        }
        
        $query = "SELECT 
                    (SELECT COUNT(*) FROM parent_categories$parentWhere) as total_parent_categories,
                    (SELECT COUNT(*) FROM product_categories$childWhere) as total_child_categories";
        
        $result = $this->conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return array(
            'total_parent_categories' => 0,
            'total_child_categories' => 0
        );
    }
}
?>