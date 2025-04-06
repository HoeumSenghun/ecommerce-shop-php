<?php
class ProductImage {
    private $conn;
    private $table = "product_images";

    // Properties
    public $image_id;
    public $product_id;
    public $image_url;
    public $is_primary;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create image
    public function create() {
        $query = "INSERT INTO " . $this->table . " (product_id, image_url, is_primary) VALUES (:product_id, :image_url, :is_primary)";
        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));

        // Bind data
        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":is_primary", $this->is_primary);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get images by product ID
    public function getByProductId($product_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE product_id = :product_id ORDER BY is_primary DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $product_id);
        $stmt->execute();
        return $stmt;
    }

    // Delete image
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE image_id = :image_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":image_id", $this->image_id);
        return $stmt->execute();
    }

    // Set primary image
    public function setPrimary() {
        // First, remove primary status from all images of this product
        $query = "UPDATE " . $this->table . " SET is_primary = 0 WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->execute();

        // Then set the selected image as primary
        $query = "UPDATE " . $this->table . " SET is_primary = 1 WHERE image_id = :image_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":image_id", $this->image_id);
        return $stmt->execute();
    }
} 