<?php
class Product {
    private $conn;
    private $table = 'products';

    // Product properties
    public $product_id;
    public $name;
    public $description;
    public $price;
    public $stock;
    public $category_id;
    public $sku;
    public $status;
    public $dietary_tags;
    public $ingredients;
    public $allergen_warnings;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($page = 1, $per_page = 12, $category_id = null) {
        $offset = ($page - 1) * $per_page;
        
        $query = "SELECT p.*, c.category_name, 
                    (SELECT image_url FROM product_images pi 
                     WHERE pi.product_id = p.product_id AND pi.is_primary = true LIMIT 1) as primary_image
                 FROM " . $this->table . " p
                 LEFT JOIN categories c ON p.category_id = c.category_id
                 WHERE 1=1";
        
        if($category_id) {
            $query .= " AND p.category_id = :category_id";
        }
        
        $query .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->conn->prepare($query);
            
            if($category_id) {
                $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
            }
            
            $stmt->bindParam(':limit', $per_page, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    public function getById($id) {
        $query = "SELECT p.*, c.category_name,
                    (SELECT GROUP_CONCAT(image_url) FROM product_images 
                     WHERE product_id = p.product_id) as images
                 FROM " . $this->table . " p
                 LEFT JOIN categories c ON p.category_id = c.category_id
                 WHERE p.product_id = :id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if($product && $product['images']) {
                $product['images'] = explode(',', $product['images']);
            }
            return $product;
        } catch(PDOException $e) {
            return null;
        }
    }

    public function search($keyword, $category_id = null) {
        $query = "SELECT p.*, c.category_name,
                    (SELECT image_url FROM product_images pi 
                     WHERE pi.product_id = p.product_id AND pi.is_primary = true LIMIT 1) as primary_image
                 FROM " . $this->table . " p
                 LEFT JOIN categories c ON p.category_id = c.category_id
                 WHERE (p.name LIKE :keyword OR p.description LIKE :keyword)";
        
        if($category_id) {
            $query .= " AND p.category_id = :category_id";
        }
        
        $query .= " ORDER BY p.created_at DESC";

        try {
            $stmt = $this->conn->prepare($query);
            
            $search_term = "%{$keyword}%";
            $stmt->bindParam(':keyword', $search_term);
            
            if($category_id) {
                $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    public function checkStock($product_id, $quantity) {
        $query = "SELECT stock FROM " . $this->table . " 
                 WHERE product_id = :product_id AND stock >= :quantity 
                 AND status = 'available'";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->bindParam(':quantity', $quantity);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function updateStock($product_id, $quantity, $operation = 'decrease') {
        $query = "UPDATE " . $this->table . " 
                 SET stock = stock " . ($operation === 'decrease' ? '-' : '+') . " :quantity
                 WHERE product_id = :product_id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->bindParam(':quantity', $quantity);
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }
}
