<?php

include_once("mConnect.php");

class kdbaidang {
    public function allbaidang() {
        $p = new Connect();
        $con = $p->connect();

        $sql = "SELECT * FROM products sp join users nd on sp.user_id = nd.id 
                                        join product_categories lsp on sp.category_id = lsp.id";
        $kq = mysqli_query($con, $sql);
        $i = mysqli_num_rows($kq);

        if ($i > 0) {
            while ($r = mysqli_fetch_array($kq)) {
                $id = $r['id'];
                $ten_nguoi_ban = $r['username'];
                $category_name = $r['category_name'];
                $title = $r['title'];
                $comment = $r['description'];
                $price = $r['price'];
                $hinh = $r['image'];
                $status = $r['status'];
                $status_ban = $r['sale_status'];
                $created_date = $r['created_date'];
                $updated_date = $r['updated_date'];
                $note = $r['note'];

                $dl[] = array(
                    'id' => $id,
                    'ho_ten' => $ten_nguoi_ban,
                    'category_name' => $category_name,
                    'title' => $title,
                    'comment' => $comment,
                    'price' => $price,
                    'image' => $hinh,
                    'status' => $status,
                    'status_ban' => $status_ban,
                    'created_date' => $created_date,
                    'updated_date' => $updated_date,
                    'note' => $note
                );
            }
            return $dl;
        } else {
            return null;
        }
    }
    public function onebaidang($id){
        $p = new Connect();
        $con = $p->connect();

        $sql = "SELECT * FROM products sp join users nd on sp.user_id = nd.id 
                join product_categories lsp on sp.category_id = lsp.id where sp.id = '$id'";
        $kq = mysqli_query($con, $sql);
        $i = mysqli_num_rows($kq);

        if ($i > 0) {
            while ($r = mysqli_fetch_array($kq)) {
                $id = $r['id'];
                $ten_nguoi_ban = $r['username'];
                $category_name = $r['category_name'];
                $title = $r['title'];
                $comment = $r['description'];
                $price = $r['price'];
                $hinh = $r['image'];
                $status = $r['status'];
                $status_ban = $r['sale_status'];
                $created_date = $r['created_date'];
                $updated_date = $r['updated_date'];
                $note = $r['note'];

                $dl[] = array(
                    'id' => $id,
                    'ho_ten' => $ten_nguoi_ban,
                    'category_name' => $category_name,
                    'title' => $title,
                    'comment' => $comment,
                    'price' => $price,
                    'image' => $hinh,
                    'status' => $status,
                    'status_ban' => $status_ban,
                    'created_date' => $created_date,
                    'updated_date' => $updated_date,
                    'note' => $note
                );
            }
            return $dl;
        } else {
            return null;
        }
    }
    public function duyetBai($id) {
        $p = new Connect();
        $conn = $p->connect();
        $query = "UPDATE products SET status = 'Đã duyệt', updated_date = NOW() WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }
    
    public function tuChoiBai($id, $note) {
        $p = new Connect();
        $conn = $p->connect();
        $query = "UPDATE products SET status = 'Từ chối duyệt', updated_date = NOW(), note = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $note, $id);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }
    // Get paginated and filtered posts
    function selectPaginatedPosts($offset, $limit, $status = '', $product_type = '', $search = '') {
        $p = new Connect();
        $conn = $p->connect();
        
        $whereClause = "WHERE 1=1";
        
        if (!empty($status)) {
            $whereClause .= " AND bd.status = '$status'";
        }
        
        if (!empty($product_type)) {
            $whereClause .= " AND lsp.category_name = '$product_type'";
        }
        
        if (!empty($search)) {
            $whereClause .= " AND (bd.id LIKE '%$search%' OR lsp.category_name LIKE '%$search%')";
        }
        
        $sql = "SELECT bd.*, lsp.category_name, nd.username
                FROM products bd 
                JOIN product_categories lsp ON bd.category_id = lsp.id 
                JOIN users nd ON bd.user_id = nd.id 
                $whereClause 
                ORDER BY bd.id 
                LIMIT $offset, $limit";
                
        $rs = mysqli_query($conn, $sql);
        
        $data = array();
        while ($row = mysqli_fetch_array($rs)) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    // Count total filtered posts for pagination
    function countFilteredPosts($status = '', $product_type = '', $search = '') {
        $p = new Connect();
        $conn = $p->connect();
        
        $whereClause = "WHERE 1=1";
        
        if (!empty($status)) {
            $whereClause .= " AND bd.status = '$status'";
        }
        
        if (!empty($product_type)) {
            $whereClause .= " AND lsp.category_name = '$product_type'";
        }
        
        if (!empty($search)) {
            $whereClause .= " AND (bd.id LIKE '%$search%' OR lsp.category_name LIKE '%$search%')";
        }
        
        $sql = "SELECT COUNT(*) as total 
                FROM products bd 
                JOIN product_categories lsp ON bd.category_id = lsp.id 
                JOIN users nd ON bd.user_id = nd.id 
                $whereClause";
                
        $rs = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($rs);
        
        return $row['total'];
    }
}
?>