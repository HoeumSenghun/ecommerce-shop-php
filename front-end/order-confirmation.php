<?php
require_once 'config/config.php';
require_once 'config/Database.php';
require_once 'models/Order.php';

// Require login
require_login();

// Get order ID from URL
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$order_id) {
    header('Location: orders.php');
    exit();
}

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize order model
$order = new Order($db);

// Get order details
$order_details = $order->getOrderDetails($order_id, $_SESSION['user_id']);

if (!$order_details) {
    header('Location: orders.php');
    exit();
}

// Get flash message
$flash_message = get_flash_message();

// Include header
include 'includes/header.php';
?>

<div class="container py-5">
    <?php if ($flash_message): ?>
        <div class="alert alert-<?= $flash_message['type'] ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($flash_message['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="text-center mb-5">
        <i class="bi bi-check-circle-fill text-success display-1"></i>
        <h1 class="mt-3">Thank You for Your Order!</h1>
        <p class="lead">Your order has been placed successfully.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Order Details</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Order Information</h5>
                            <p class="mb-1"><strong>Order ID:</strong> #<?= $order_details['order_id'] ?></p>
                            <p class="mb-1">
                                <strong>Date:</strong> 
                                <?= date('F j, Y g:i A', strtotime($order_details['created_at'])) ?>
                            </p>
                            <p class="mb-1">
                                <strong>Status:</strong>
                                <span class="badge bg-info"><?= ucfirst($order_details['status']) ?></span>
                            </p>
                            <p class="mb-1">
                                <strong>Payment Method:</strong>
                                <?php if ($order_details['payment_method'] === 'paypal'): ?>
                                    <span class="badge bg-primary">PayPal</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Cash on Delivery</span>
                                <?php endif; ?>
                            </p>
                            <p class="mb-1">
                                <strong>Payment Status:</strong>
                                <span class="badge bg-<?= $order_details['payment_status'] === 'completed' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($order_details['payment_status']) ?>
                                </span>
                            </p>
                            <?php if ($order_details['payment_method'] === 'paypal' && $order_details['paypal_transaction_id']): ?>
                                <p class="mb-1">
                                    <strong>PayPal Transaction ID:</strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($order_details['paypal_transaction_id']) ?></small>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h5>Delivery Information</h5>
                            <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($order_details['customer_name']) ?></p>
                            <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($order_details['email']) ?></p>
                            <p class="mb-1"><strong>Phone:</strong> <?= htmlspecialchars($order_details['phone']) ?></p>
                            <p class="mb-1">
                                <strong>Address:</strong><br>
                                <?= nl2br(htmlspecialchars($order_details['address'])) ?>
                            </p>
                        </div>
                    </div>

                    <h5>Order Items</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_details['items'] as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($item['product_image']): ?>
                                                    <img src="<?= htmlspecialchars($item['product_image']) ?>" 
                                                         alt="<?= htmlspecialchars($item['name']) ?>"
                                                         class="me-2"
                                                         style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php endif; ?>
                                                <div>
                                                    <?= htmlspecialchars($item['name']) ?>
                                                    <br>
                                                    <small class="text-muted">SKU: <?= htmlspecialchars($item['sku']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= format_price($item['unit_price']) ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td class="text-end"><?= format_price($item['unit_price'] * $item['quantity']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td class="text-end"><strong><?= format_price($order_details['total']) ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-4">
                        <h5 class="alert-heading">What's Next?</h5>
                        <p class="mb-0">We will start processing your order right away. You will receive an email confirmation with your order details and updates on the delivery status.</p>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="products.php" class="btn btn-outline-primary me-2">
                    Continue Shopping
                </a>
                <a href="orders.php" class="btn btn-primary">
                    View All Orders
                </a>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
