<?php
class Order {
    private $conn;
    private $table_name = "orders";

    public $order_id;
    public $user_id;
    public $total;
    public $status;
    public $payment_status;
    public $cancellation_reason;
    public $created_at;
    public $payment_method;
    public $paypal_transaction_id;
    public $paypal_payer_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE order_id = :order_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $this->order_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            return $row;
        }
        
        return false;
    }

    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . "
                SET status = :status
                WHERE order_id = :order_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize and bind values
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->order_id = htmlspecialchars(strip_tags($this->order_id));

        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":order_id", $this->order_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    public function updatePaymentStatus() {
        $query = "UPDATE " . $this->table_name . "
                SET payment_status = :payment_status
                WHERE order_id = :order_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize and bind values
        $this->payment_status = htmlspecialchars(strip_tags($this->payment_status));
        $this->order_id = htmlspecialchars(strip_tags($this->order_id));

        $stmt->bindParam(":payment_status", $this->payment_status);
        $stmt->bindParam(":order_id", $this->order_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
