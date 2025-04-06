<?php
require_once 'config/config.php';
require_once 'config/Database.php';
require_once 'models/Cart.php';
require_once 'models/Product.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize cart
$cart = new Cart();

// Handle cart actions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_quantity'])) {
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];
        
        if ($cart->updateQuantity($product_id, $quantity)) {
            $message = 'Cart updated successfully!';
            $message_type = 'success';
        } else {
            $message = 'Failed to update cart.';
            $message_type = 'danger';
        }
    } elseif (isset($_POST['remove_item'])) {
        $product_id = (int)$_POST['product_id'];
        
        if ($cart->removeItem($product_id)) {
            $message = 'Item removed from cart.';
            $message_type = 'success';
        } else {
            $message = 'Failed to remove item.';
            $message_type = 'danger';
        }
    } elseif (isset($_POST['clear_cart'])) {
        $cart->clear();
        $message = 'Cart cleared successfully.';
        $message_type = 'success';
    }
}

// Get cart items
$cart_items = $cart->getItems();
$cart_total = $cart->getTotal();

// Validate stock for all items
$stock_errors = $cart->validateStock($db);

// Include header
include 'includes/header.php';
?>

<div class="container py-5">
    <h1 class="mb-4">Shopping Cart</h1>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($stock_errors)): ?>
        <div class="alert alert-warning">
            <h5>Stock Warning:</h5>
            <ul class="mb-0">
                <?php foreach ($stock_errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($cart_items): ?>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($item['image']): ?>
                                                <img src="<?= htmlspecialchars($item['image']) ?>" 
                                                     alt="<?= htmlspecialchars($item['name']) ?>" 
                                                     class="me-3"
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php endif; ?>
                                            <div>
                                                <h6 class="mb-0">
                                                    <a href="/product.php?id=<?= $item['product_id'] ?>" 
                                                       class="text-decoration-none">
                                                        <?= htmlspecialchars($item['name']) ?>
                                                    </a>
                                                </h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= format_price($item['price']) ?></td>
                                    <td>
                                        <form method="POST" action="" class="d-flex align-items-center">
                                            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                            <select name="quantity" class="form-select form-select-sm w-auto me-2" 
                                                    onchange="this.form.submit()">
                                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                                    <option value="<?= $i ?>" <?= $i === $item['quantity'] ? 'selected' : '' ?>>
                                                        <?= $i ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                            <input type="hidden" name="update_quantity" value="1">
                                        </form>
                                    </td>
                                    <td><?= format_price($item['price'] * $item['quantity']) ?></td>
                                    <td>
                                        <form method="POST" action="" class="d-inline">
                                            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                            <button type="submit" 
                                                    name="remove_item" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Are you sure you want to remove this item?')">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                <td><strong><?= format_price($cart_total) ?></strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <form method="POST" action="">
                        <button type="submit" 
                                name="clear_cart" 
                                class="btn btn-outline-danger"
                                onclick="return confirm('Are you sure you want to clear your cart?')">
                            <i class="bi bi-cart-x"></i> Clear Cart
                        </button>
                    </form>
                    <div>
                        <a href="../front-end/products.php" class="btn btn-outline-primary me-2">
                            <i class="bi bi-arrow-left"></i> Continue Shopping
                        </a>
                        <a href="../front-end/checkout.php" class="btn btn-primary <?= !empty($stock_errors) ? 'disabled' : '' ?>">
                            <i class="bi bi-credit-card"></i> Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-cart-x display-1 text-muted"></i>
            <h3 class="mt-3">Your cart is empty</h3>
            <p class="text-muted">Looks like you haven't added any items to your cart yet.</p>
            <a href="/products.php" class="btn btn-primary">
                <i class="bi bi-arrow-left"></i> Continue Shopping
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
