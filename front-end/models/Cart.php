<?php
class Cart {
    private $cart_items = [];

    public function __construct() {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        $this->cart_items = &$_SESSION['cart'];
        $this->updateCartCount();
    }

    public function addItem($product_id, $quantity, $price, $name, $image = null) {
        // Check if item already exists in cart
        foreach ($this->cart_items as &$item) {
            if ($item['product_id'] == $product_id) {
                $item['quantity'] += $quantity;
                $this->updateCartCount();
                return true;
            }
        }

        // Add new item
        $this->cart_items[] = [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'price' => $price,
            'name' => $name,
            'image' => $image
        ];

        $this->updateCartCount();
        return true;
    }

    public function updateQuantity($product_id, $quantity) {
        foreach ($this->cart_items as &$item) {
            if ($item['product_id'] == $product_id) {
                if ($quantity <= 0) {
                    return $this->removeItem($product_id);
                }
                $item['quantity'] = $quantity;
                $this->updateCartCount();
                return true;
            }
        }
        return false;
    }

    public function removeItem($product_id) {
        foreach ($this->cart_items as $key => $item) {
            if ($item['product_id'] == $product_id) {
                unset($this->cart_items[$key]);
                $this->cart_items = array_values($this->cart_items); // Re-index array
                $this->updateCartCount();
                return true;
            }
        }
        return false;
    }

    public function clear() {
        $this->cart_items = [];
        $this->updateCartCount();
    }

    public function getItems() {
        return $this->cart_items;
    }

    public function getTotal() {
        $total = 0;
        foreach ($this->cart_items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    public function getCount() {
        $count = 0;
        foreach ($this->cart_items as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }

    private function updateCartCount() {
        $_SESSION['cart_count'] = $this->getCount();
    }

    public function validateStock($db) {
        $product = new Product($db);
        $errors = [];

        foreach ($this->cart_items as $item) {
            if (!$product->checkStock($item['product_id'], $item['quantity'])) {
                $errors[] = "Insufficient stock for product: " . $item['name'];
            }
        }

        return $errors;
    }

    public function hasItems() {
        return !empty($this->cart_items);
    }
}
