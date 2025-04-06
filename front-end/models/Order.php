<?php
class Order {
    private $conn;
    private $table = 'orders';
    private $items_table = 'order_items';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($user_id, $cart_items, $total, $payment_method = 'cash', $paypal_data = null) {
        try {
            $this->conn->beginTransaction();

            // Create the order
            $order_query = "INSERT INTO " . $this->table . " 
                          (user_id, total, status, payment_status, payment_method";
            
            $values = "(:user_id, :total, 'received', :payment_status, :payment_method";
            $payment_status = $payment_method === 'paypal' ? 'completed' : 'pending';

            // Add PayPal fields if present
            if ($payment_method === 'paypal' && $paypal_data) {
                $order_query .= ", paypal_transaction_id, paypal_payer_id";
                $values .= ", :paypal_transaction_id, :paypal_payer_id";
            }

            $order_query .= ") VALUES " . $values . ")";
            
            $stmt = $this->conn->prepare($order_query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':total', $total);
            $stmt->bindParam(':payment_status', $payment_status);
            $stmt->bindParam(':payment_method', $payment_method);

            // Bind PayPal parameters if present
            if ($payment_method === 'paypal' && $paypal_data) {
                $stmt->bindParam(':paypal_transaction_id', $paypal_data['transaction_id']);
                $stmt->bindParam(':paypal_payer_id', $paypal_data['payer_id']);
            }
            $stmt->execute();

            $order_id = $this->conn->lastInsertId();

            // Create order items
            $items_query = "INSERT INTO " . $this->items_table . "
                          (order_id, product_id, quantity, unit_price)
                          VALUES (:order_id, :product_id, :quantity, :unit_price)";

            $items_stmt = $this->conn->prepare($items_query);

            foreach ($cart_items as $item) {
                $items_stmt->bindParam(':order_id', $order_id);
                $items_stmt->bindParam(':product_id', $item['product_id']);
                $items_stmt->bindParam(':quantity', $item['quantity']);
                $items_stmt->bindParam(':unit_price', $item['price']);
                $items_stmt->execute();

                // Update product stock
                $stock_query = "UPDATE products SET stock = stock - :quantity 
                              WHERE product_id = :product_id";
                $stock_stmt = $this->conn->prepare($stock_query);
                $stock_stmt->bindParam(':quantity', $item['quantity']);
                $stock_stmt->bindParam(':product_id', $item['product_id']);
                $stock_stmt->execute();
            }

            $this->conn->commit();
            return $order_id;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function getUserOrders($user_id) {
        $query = "SELECT o.*, 
                    (SELECT COUNT(*) FROM " . $this->items_table . " WHERE order_id = o.order_id) as item_count
                 FROM " . $this->table . " o
                 WHERE o.user_id = :user_id
                 ORDER BY o.created_at DESC";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getOrderDetails($order_id, $user_id = null) {
        // Get order info
        $query = "SELECT o.*, u.name as customer_name, u.email, u.phone, u.address
                 FROM " . $this->table . " o
                 JOIN users u ON o.user_id = u.user_id
                 WHERE o.order_id = :order_id";
        
        if ($user_id) {
            $query .= " AND o.user_id = :user_id";
        }

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':order_id', $order_id);
            if ($user_id) {
                $stmt->bindParam(':user_id', $user_id);
            }
            $stmt->execute();
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                return null;
            }

            // Get order items
            $items_query = "SELECT oi.*, p.name, p.sku,
                            (SELECT image_url FROM product_images 
                             WHERE product_id = p.product_id AND is_primary = true 
                             LIMIT 1) as product_image
                          FROM " . $this->items_table . " oi
                          JOIN products p ON oi.product_id = p.product_id
                          WHERE oi.order_id = :order_id";

            $items_stmt = $this->conn->prepare($items_query);
            $items_stmt->bindParam(':order_id', $order_id);
            $items_stmt->execute();
            $order['items'] = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

            return $order;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function updateStatus($order_id, $status, $user_id = null) {
        $query = "UPDATE " . $this->table . "
                SET status = :status
                WHERE order_id = :order_id";
        
        if ($user_id) {
            $query .= " AND user_id = :user_id";
        }

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':order_id', $order_id);
            if ($user_id) {
                $stmt->bindParam(':user_id', $user_id);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updatePaymentStatus($order_id, $status) {
        $query = "UPDATE " . $this->table . "
                SET payment_status = :status
                WHERE order_id = :order_id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':order_id', $order_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function cancelOrder($order_id, $user_id, $reason) {
        try {
            $this->conn->beginTransaction();

            // Update order status
            $order_query = "UPDATE " . $this->table . "
                          SET status = 'cancelled',
                              cancellation_reason = :reason
                          WHERE order_id = :order_id 
                          AND user_id = :user_id
                          AND status = 'received'";

            $stmt = $this->conn->prepare($order_query);
            $stmt->bindParam(':reason', $reason);
            $stmt->bindParam(':order_id', $order_id);
            $stmt->bindParam(':user_id', $user_id);
            
            if (!$stmt->execute()) {
                throw new PDOException("Failed to update order status");
            }

            if ($stmt->rowCount() === 0) {
                throw new PDOException("Order not found or cannot be cancelled");
            }

            // Return items to stock
            $items_query = "SELECT product_id, quantity FROM " . $this->items_table . "
                          WHERE order_id = :order_id";
            
            $items_stmt = $this->conn->prepare($items_query);
            $items_stmt->bindParam(':order_id', $order_id);
            $items_stmt->execute();
            
            $stock_query = "UPDATE products 
                          SET stock = stock + :quantity 
                          WHERE product_id = :product_id";
            
            $stock_stmt = $this->conn->prepare($stock_query);
            
            while ($item = $items_stmt->fetch(PDO::FETCH_ASSOC)) {
                $stock_stmt->bindParam(':quantity', $item['quantity']);
                $stock_stmt->bindParam(':product_id', $item['product_id']);
                $stock_stmt->execute();
            }

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
