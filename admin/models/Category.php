<?php
class Category {
    private $conn;
    private $table = "categories";

    // Properties
    public $category_id;
    public $category_name;
    public $parent_category;
    public $description;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create category
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                (category_name, parent_category, description) 
                VALUES (:name, :parent, :description)";

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->category_name = htmlspecialchars(strip_tags($this->category_name));
        $this->description = htmlspecialchars(strip_tags($this->description));

        // Bind data
        $stmt->bindParam(":name", $this->category_name);
        $stmt->bindParam(":parent", $this->parent_category);
        $stmt->bindParam(":description", $this->description);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Read all categories
    public function read() {
        $query = "SELECT c.*, pc.category_name as parent_name 
                FROM " . $this->table . " c 
                LEFT JOIN " . $this->table . " pc ON c.parent_category = pc.category_id 
                ORDER BY c.category_name";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Read single category
    public function readOne() {
        $query = "SELECT c.*, pc.category_name as parent_name 
                FROM " . $this->table . " c 
                LEFT JOIN " . $this->table . " pc ON c.parent_category = pc.category_id 
                WHERE c.category_id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->category_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->category_name = $row['category_name'];
            $this->parent_category = $row['parent_category'];
            $this->description = $row['description'];
            return true;
        }
        return false;
    }

    // Update category
    public function update() {
        $query = "UPDATE " . $this->table . "
                SET category_name = :name,
                    parent_category = :parent,
                    description = :description
                WHERE category_id = :id";

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->category_name = htmlspecialchars(strip_tags($this->category_name));
        $this->description = htmlspecialchars(strip_tags($this->description));

        // Bind data
        $stmt->bindParam(":name", $this->category_name);
        $stmt->bindParam(":parent", $this->parent_category);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":id", $this->category_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete category
    public function delete() {
        // First check if category has products
        $query = "SELECT COUNT(*) FROM products WHERE category_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->category_id);
        $stmt->execute();
        
        if($stmt->fetchColumn() > 0) {
            return false; // Cannot delete category with products
        }

        // Then check if category has child categories
        $query = "SELECT COUNT(*) FROM " . $this->table . " WHERE parent_category = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->category_id);
        $stmt->execute();
        
        if($stmt->fetchColumn() > 0) {
            return false; // Cannot delete category with child categories
        }

        // If no products and no child categories, delete the category
        $query = "DELETE FROM " . $this->table . " WHERE category_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->category_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get parent categories for dropdown
    public function getParentCategories() {
        $query = "SELECT category_id, category_name FROM " . $this->table . " 
                WHERE category_id != :current_id 
                ORDER BY category_name";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":current_id", $this->category_id);
        $stmt->execute();

        return $stmt;
    }
} 