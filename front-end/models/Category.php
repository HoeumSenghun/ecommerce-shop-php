<?php
class Category {
    private $conn;
    private $table = 'categories';

    // Category properties
    public $category_id;
    public $category_name;
    public $parent_category;
    public $description;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT c.*, 
                    (SELECT COUNT(*) FROM products WHERE category_id = c.category_id) as product_count,
                    pc.category_name as parent_name
                 FROM " . $this->table . " c
                 LEFT JOIN " . $this->table . " pc ON c.parent_category = pc.category_id
                 ORDER BY c.parent_category IS NULL DESC, c.category_name ASC";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    public function getById($id) {
        $query = "SELECT c.*, 
                    pc.category_name as parent_name,
                    (SELECT COUNT(*) FROM products WHERE category_id = c.category_id) as product_count
                 FROM " . $this->table . " c
                 LEFT JOIN " . $this->table . " pc ON c.parent_category = pc.category_id
                 WHERE c.category_id = :id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return null;
        }
    }

    public function getMainCategories() {
        $query = "SELECT c.*, 
                    (SELECT COUNT(*) FROM products WHERE category_id = c.category_id) as product_count
                 FROM " . $this->table . " c
                 WHERE c.parent_category IS NULL
                 ORDER BY c.category_name ASC";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    public function getSubCategories($parent_id) {
        $query = "SELECT c.*, 
                    (SELECT COUNT(*) FROM products WHERE category_id = c.category_id) as product_count
                 FROM " . $this->table . " c
                 WHERE c.parent_category = :parent_id
                 ORDER BY c.category_name ASC";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':parent_id', $parent_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . "
                (category_name, parent_category, description)
                VALUES
                (:category_name, :parent_category, :description)";

        try {
            $stmt = $this->conn->prepare($query);
            
            // Handle null parent_category
            $parent_category = !empty($data['parent_category']) ? $data['parent_category'] : null;
            
            // Bind data
            $stmt->bindParam(':category_name', $data['category_name']);
            $stmt->bindParam(':parent_category', $parent_category);
            $stmt->bindParam(':description', $data['description']);

            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }

    public function update($data) {
        $query = "UPDATE " . $this->table . "
                SET category_name = :category_name,
                    parent_category = :parent_category,
                    description = :description
                WHERE category_id = :category_id";

        try {
            $stmt = $this->conn->prepare($query);
            
            // Handle null parent_category
            $parent_category = !empty($data['parent_category']) ? $data['parent_category'] : null;
            
            // Bind data
            $stmt->bindParam(':category_name', $data['category_name']);
            $stmt->bindParam(':parent_category', $parent_category);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':category_id', $data['category_id']);

            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }

    public function delete($id) {
        // First, update any products in this category to have no category
        $update_products = "UPDATE products SET category_id = NULL WHERE category_id = :category_id";
        
        // Then, update any child categories to have no parent
        $update_children = "UPDATE " . $this->table . " SET parent_category = NULL WHERE parent_category = :category_id";
        
        // Finally, delete the category
        $delete_category = "DELETE FROM " . $this->table . " WHERE category_id = :category_id";

        try {
            $this->conn->beginTransaction();

            // Update products
            $stmt = $this->conn->prepare($update_products);
            $stmt->bindParam(':category_id', $id);
            $stmt->execute();

            // Update child categories
            $stmt = $this->conn->prepare($update_children);
            $stmt->bindParam(':category_id', $id);
            $stmt->execute();

            // Delete category
            $stmt = $this->conn->prepare($delete_category);
            $stmt->bindParam(':category_id', $id);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch(PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
