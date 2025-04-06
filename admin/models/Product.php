<?php
class Product {
    private $conn;
    private $table = "products";

    // Properties
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

    // Create product
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                (name, description, price, stock, category_id, sku, status, dietary_tags, ingredients, allergen_warnings) 
                VALUES 
                (:name, :description, :price, :stock, :category_id, :sku, :status, :dietary_tags, :ingredients, :allergen_warnings)";

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->sku = htmlspecialchars(strip_tags($this->sku));
        $this->dietary_tags = htmlspecialchars(strip_tags($this->dietary_tags));
        $this->ingredients = htmlspecialchars(strip_tags($this->ingredients));
        $this->allergen_warnings = htmlspecialchars(strip_tags($this->allergen_warnings));

        // Bind data
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":stock", $this->stock);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":sku", $this->sku);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":dietary_tags", $this->dietary_tags);
        $stmt->bindParam(":ingredients", $this->ingredients);
        $stmt->bindParam(":allergen_warnings", $this->allergen_warnings);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Read all products
    public function read($search = '', $category = '', $status = '') {
        $query = "SELECT p.*, c.category_name, 
                (SELECT image_url FROM product_images pi WHERE pi.product_id = p.product_id AND pi.is_primary = 1 LIMIT 1) as primary_image 
                FROM " . $this->table . " p 
                LEFT JOIN categories c ON p.category_id = c.category_id 
                WHERE 1=1";

        if(!empty($search)) {
            $query .= " AND (p.name LIKE :search OR p.description LIKE :search OR p.sku LIKE :search)";
        }
        if(!empty($category)) {
            $query .= " AND p.category_id = :category";
        }
        if(!empty($status)) {
            $query .= " AND p.status = :status";
        }

        $query .= " ORDER BY p.name";

        $stmt = $this->conn->prepare($query);

        if(!empty($search)) {
            $searchTerm = "%{$search}%";
            $stmt->bindParam(":search", $searchTerm);
        }
        if(!empty($category)) {
            $stmt->bindParam(":category", $category);
        }
        if(!empty($status)) {
            $stmt->bindParam(":status", $status);
        }

        $stmt->execute();
        return $stmt;
    }

    // Read single product
    public function readOne() {
        $query = "SELECT p.*, c.category_name 
                FROM " . $this->table . " p 
                LEFT JOIN categories c ON p.category_id = c.category_id 
                WHERE p.product_id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->product_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->price = $row['price'];
            $this->stock = $row['stock'];
            $this->category_id = $row['category_id'];
            $this->sku = $row['sku'];
            $this->status = $row['status'];
            $this->dietary_tags = $row['dietary_tags'];
            $this->ingredients = $row['ingredients'];
            $this->allergen_warnings = $row['allergen_warnings'];
            return true;
        }
        return false;
    }

    // Update product
    public function update() {
        $query = "UPDATE " . $this->table . "
                SET name = :name,
                    description = :description,
                    price = :price,
                    stock = :stock,
                    category_id = :category_id,
                    sku = :sku,
                    status = :status,
                    dietary_tags = :dietary_tags,
                    ingredients = :ingredients,
                    allergen_warnings = :allergen_warnings
                WHERE product_id = :id";

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->sku = htmlspecialchars(strip_tags($this->sku));
        $this->dietary_tags = htmlspecialchars(strip_tags($this->dietary_tags));
        $this->ingredients = htmlspecialchars(strip_tags($this->ingredients));
        $this->allergen_warnings = htmlspecialchars(strip_tags($this->allergen_warnings));

        // Bind data
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":stock", $this->stock);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":sku", $this->sku);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":dietary_tags", $this->dietary_tags);
        $stmt->bindParam(":ingredients", $this->ingredients);
        $stmt->bindParam(":allergen_warnings", $this->allergen_warnings);
        $stmt->bindParam(":id", $this->product_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete product
    public function delete() {
        // First delete product images
        $query = "DELETE FROM product_images WHERE product_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->product_id);
        $stmt->execute();

        // Then delete the product
        $query = "DELETE FROM " . $this->table . " WHERE product_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->product_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Check if SKU exists
    public function skuExists($sku, $exclude_id = null) {
        $query = "SELECT COUNT(*) FROM " . $this->table . " WHERE sku = :sku";
        if($exclude_id) {
            $query .= " AND product_id != :id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":sku", $sku);
        if($exclude_id) {
            $stmt->bindParam(":id", $exclude_id);
        }
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    // Generate unique SKU
    public function generateSKU($name) {
        $base = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $name));
        $base = substr($base, 0, 5);
        $sku = $base . rand(1000, 9999);
        
        while($this->skuExists($sku)) {
            $sku = $base . rand(1000, 9999);
        }
        
        return $sku;
    }
} 