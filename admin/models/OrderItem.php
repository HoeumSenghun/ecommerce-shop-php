<?php
class OrderItem {
    private $conn;
    private $table_name = "order_items";

    public $item_id;
    public $order_id;
    public $product_id;
    public $quantity;
    public $unit_price;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readByOrder($order_id) {
        $query = "SELECT oi.*, p.name as product_name 
                 FROM " . $this->table_name . " oi
                 LEFT JOIN products p ON p.product_id = oi.product_id
                 WHERE oi.order_id = :order_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();

        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                 (order_id, product_id, quantity, unit_price)
                 VALUES
                 (:order_id, :product_id, :quantity, :unit_price)";

        $stmt = $this->conn->prepare($query);

        // Sanitize and bind values
        $this->order_id = htmlspecialchars(strip_tags($this->order_id));
        $this->product_id = htmlspecialchars(strip_tags($this->product_id));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->unit_price = htmlspecialchars(strip_tags($this->unit_price));

        $stmt->bindParam(":order_id", $this->order_id);
        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":unit_price", $this->unit_price);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET quantity = :quantity,
                    unit_price = :unit_price
                WHERE item_id = :item_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize and bind values
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->unit_price = htmlspecialchars(strip_tags($this->unit_price));
        $this->item_id = htmlspecialchars(strip_tags($this->item_id));

        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":unit_price", $this->unit_price);
        $stmt->bindParam(":item_id", $this->item_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE item_id = :item_id";

        $stmt = $this->conn->prepare($query);

        $this->item_id = htmlspecialchars(strip_tags($this->item_id));
        $stmt->bindParam(":item_id", $this->item_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
