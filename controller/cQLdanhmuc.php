<?php
include_once("model/mQLdanhmuc.php");

class cLoaiSanPham {
    private $model;
    
    public function __construct() {
        $this->model = new mLoaiSanPham();
    }
    
    // Parent Category Methods
    
    // Get all parent categories
    public function getAllParentCategories() {
        return $this->model->getAllParentCategories();
    }
    
    // Get parent category by ID
    public function getParentCategoryById($id) {
        return $this->model->getParentCategoryById($id);
    }
    
    // Add new parent category
    public function addParentCategory($name) {
        // Validate input
        if (empty($name)) {
            return array('success' => false, 'message' => 'Tên danh mục không được để trống');
        }
        
        $result = $this->model->addParentCategory($name);
        
        if ($result) {
            return array('success' => true, 'id' => $result);
        } else {
            return array('success' => false, 'message' => 'Không thể thêm danh mục cha');
        }
    }
    
    // Update parent category
    public function updateParentCategory($id, $name) {
        // Validate input
        if (empty($name)) {
            return array('success' => false, 'message' => 'Tên danh mục không được để trống');
        }
        
        $result = $this->model->updateParentCategory($id, $name);
        
        if ($result) {
            return array('success' => true);
        } else {
            return array('success' => false, 'message' => 'Không thể cập nhật danh mục cha');
        }
    }
    
    // Delete parent category
    public function deleteParentCategory($id) {
        // Check if there are child categories
        $childCount = $this->model->countChildCategories($id);
        
        if ($childCount > 0) {
            return array(
                'success' => false, 
                'message' => 'Không thể xóa danh mục cha này vì có ' . $childCount . ' danh mục con thuộc danh mục này'
            );
        }
        
        $result = $this->model->deleteParentCategory($id);
        
        if ($result) {
            return array('success' => true);
        } else {
            return array('success' => false, 'message' => 'Không thể xóa danh mục cha');
        }
    }
    
    // Count child categories for a parent
    public function aacountChildCategories($parentId) {
        return $this->model->countChildCategories($parentId);
    }
    
    // Child Category Methods
    
    // Get all child categories with parent info
    public function getAllChildCategories() {
        return $this->model->getAllChildCategories();
    }
    
    // Get paginated child categories with filters
    public function getPaginatedChildCategories($offset, $limit, $parentFilter = 'all', $searchTerm = '') {
        return $this->model->getPaginatedChildCategories($offset, $limit, $parentFilter, $searchTerm);
    }
    
    // Count child categories for pagination
    public function countChildCategories($parentFilter = 'all', $searchTerm = '') {
        return $this->model->countChildCategories($parentFilter, $searchTerm);
    }
    
    // Get child categories by parent ID
    public function getChildCategoriesByParentId($parentId) {
        return $this->model->getChildCategoriesByParentId($parentId);
    }
    
    // Get child category by ID
    public function getChildCategoryById($id) {
        return $this->model->getChildCategoryById($id);
    }
    
    // Add new child category
    public function addChildCategory($name, $parentId) {
        // Validate input
        if (empty($name)) {
            return array('success' => false, 'message' => 'Tên danh mục không được để trống');
        }
        
        if (empty($parentId)) {
            return array('success' => false, 'message' => 'Vui lòng chọn danh mục cha');
        }
        
        // Check if parent category exists
        $parentCategory = $this->model->getParentCategoryById($parentId);
        if (!$parentCategory) {
            return array('success' => false, 'message' => 'Danh mục cha không tồn tại');
        }
        
        $result = $this->model->addChildCategory($name, $parentId);
        
        if ($result) {
            return array('success' => true, 'id' => $result);
        } else {
            return array('success' => false, 'message' => 'Không thể thêm danh mục con');
        }
    }
    
    // Update child category
    public function updateChildCategory($id, $name, $parentId) {
        // Validate input
        if (empty($name)) {
            return array('success' => false, 'message' => 'Tên danh mục không được để trống');
        }
        
        if (empty($parentId)) {
            return array('success' => false, 'message' => 'Vui lòng chọn danh mục cha');
        }
        
        // Check if parent category exists
        $parentCategory = $this->model->getParentCategoryById($parentId);
        if (!$parentCategory) {
            return array('success' => false, 'message' => 'Danh mục cha không tồn tại');
        }
        
        $result = $this->model->updateChildCategory($id, $name, $parentId);
        
        if ($result) {
            return array('success' => true);
        } else {
            return array('success' => false, 'message' => 'Không thể cập nhật danh mục con');
        }
    }
    
    // Delete child category
    public function deleteChildCategory($id) {
        // Check if there are products using this category
        $productCount = $this->model->countProductsInCategory($id);
        
        if ($productCount > 0) {
            return array(
                'success' => false, 
                'message' => 'Không thể xóa danh mục này vì có ' . $productCount . ' sản phẩm thuộc danh mục này'
            );
        }
        
        $result = $this->model->deleteChildCategory($id);
        
        if ($result) {
            return array('success' => true);
        } else {
            return array('success' => false, 'message' => 'Không thể xóa danh mục con');
        }
    }
    
    // Statistics Methods
    
    // Get category statistics
    public function getCategoryStats() {
        return $this->model->getCategoryStats();
    }
}
?>