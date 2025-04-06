<?php
require_once 'config/config.php';
require_once 'config/Database.php';
require_once 'models/Cart.php';
require_once 'models/Order.php';
require_once 'models/User.php';
require_once 'models/Product.php';

// Require login for checkout
require_login();

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize models
$cart = new Cart();
$user_model = new User($db);

// Get user details
$user = $user_model->getById($_SESSION['user_id']);

// Handle checkout process
$message = '';
$message_type = '';
$order_completed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate cart has items
    if (!$cart->hasItems()) {
        header('Location: cart.php');
        exit();
    }

    // Get cart items and total
    $cart_items = $cart->getItems();
    $cart_total = $cart->getTotal();

    // Validate stock
    $stock_errors = $cart->validateStock($db);
    if (!empty($stock_errors)) {
        $message = 'Some items in your cart are no longer available in the requested quantity.';
        $message_type = 'danger';
    } else {
        // Create order with cash payment
        $order = new Order($db);
        $order_id = $order->create($_SESSION['user_id'], $cart_items, $cart_total, 'cash', null);

        if ($order_id) {
            // Clear cart after successful order
            $cart->clear();
            $order_completed = true;

            // Set success message
            set_flash_message('success', 'Order placed successfully! Your order ID is: ' . $order_id);
            header('Location: /bun_deth_eco/front-end/order-confirmation.php?id=' . $order_id);
            exit();
        } else {
            $message = 'Failed to place order. Please try again.';
            $message_type = 'danger';
        }
    }
}

// Get current cart items
$cart_items = $cart->getItems();
$cart_total = $cart->getTotal();

// Redirect if cart is empty
if (!$cart->hasItems()) {
    header('Location: cart.php');
    exit();
}

// Include header
include 'includes/header.php';
?>

<div class="container py-5">
    <h1 class="mb-4">Checkout</h1>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Order Summary -->
        <div class="col-md-4 order-md-2 mb-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-primary">Order Summary</span>
                        <span class="badge bg-primary rounded-pill"><?= $cart->getCount() ?></span>
                    </h4>
                    <ul class="list-group mb-3">
                        <?php foreach ($cart_items as $item): ?>
                            <li class="list-group-item d-flex justify-content-between lh-sm">
                                <div>
                                    <h6 class="my-0"><?= htmlspecialchars($item['name']) ?></h6>
                                    <small class="text-muted">Quantity: <?= $item['quantity'] ?></small>
                                </div>
                                <span class="text-muted">
                                    <?= format_price($item['price'] * $item['quantity']) ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Total</strong>
                            <strong><?= format_price($cart_total) ?></strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Checkout Form -->
        <div class="col-md-8 order-md-1">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">Delivery Information</h4>
                    <form method="POST" action="checkout.php">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       value="<?= htmlspecialchars($user['name']) ?>" 
                                       disabled>
                            </div>

                            <div class="col-12">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       value="<?= htmlspecialchars($user['email']) ?>" 
                                       disabled>
                            </div>

                            <div class="col-12">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="phone" 
                                       value="<?= htmlspecialchars($user['phone']) ?>" 
                                       disabled>
                            </div>

                            <div class="col-12">
                                <label for="address" class="form-label">Delivery Address</label>
                                <textarea class="form-control" 
                                          id="address" 
                                          rows="3" 
                                          disabled><?= htmlspecialchars($user['address']) ?></textarea>
                            </div>

                            <div class="col-12">
                                <p class="small text-muted">
                                    Please ensure your contact information is up to date. 
                                    <a href="profile.php">Update profile</a>
                                </p>
                            </div>

                            <hr class="my-4">

                            <div class="col-12">
                                <h4 class="mb-3">Payment Method</h4>
                                <div class="my-3">
                                    <div class="form-check">
                                        <input id="cash" name="payment_method" value="cash" type="radio" class="form-check-input" checked>
                                        <label class="form-check-label" for="cash">Cash on Delivery</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="payment-buttons">
                            <!-- Cash payment button -->
                            <div id="cash-button">
                                <button class="w-100 btn btn-primary btn-lg" type="submit">
                                    Place Order with Cash
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
