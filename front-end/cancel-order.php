<?php
require_once 'config/config.php';
require_once 'config/Database.php';
require_once 'models/Order.php';

// Require login
require_login();

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize order model
$order = new Order($db);

// Get order ID from URL
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// Get order details
$order_details = $order->getOrderDetails($order_id, $user_id);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $reason = sanitize_input($_POST['reason']);

    if ($order->cancelOrder($order_id, $user_id, $reason)) {
        set_flash_message('success', 'Order cancelled successfully.');
        header('Location: orders.php');
        exit();
    } else {
        $error_message = 'Failed to cancel order. The order might already be processed.';
    }
}

// Include header
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Cancel Order #<?= htmlspecialchars($order_id) ?></h4>
                </div>
                <div class="card-body">
                    <?php if (!$order_details): ?>
                        <div class="alert alert-danger">
                            Order not found or you don't have permission to cancel it.
                        </div>
                        <div class="text-center mt-3">
                            <a href="orders.php" class="btn btn-primary">Back to Orders</a>
                        </div>
                    <?php elseif ($order_details['status'] !== 'received'): ?>
                        <div class="alert alert-warning">
                            This order cannot be cancelled because it is already <?= strtolower($order_details['status']) ?>.
                        </div>
                        <div class="text-center mt-3">
                            <a href="orders.php" class="btn btn-primary">Back to Orders</a>
                        </div>
                    <?php else: ?>
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($error_message) ?>
                            </div>
                        <?php endif; ?>

                        <div class="order-summary mb-4">
                            <h5>Order Summary</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Date:</strong> <?= date('M j, Y g:i A', strtotime($order_details['created_at'])) ?></p>
                                    <p><strong>Status:</strong> <span class="badge bg-info"><?= ucfirst($order_details['status']) ?></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Total:</strong> <?= format_price($order_details['total']) ?></p>
                                    <p><strong>Payment Status:</strong> 
                                        <span class="badge bg-<?= $order_details['payment_status'] === 'completed' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($order_details['payment_status']) ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <form action="cancel-order.php?id=<?= $order_id ?>" method="POST" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label for="reason" class="form-label">Reason for Cancellation</label>
                                <textarea class="form-control" id="reason" name="reason" rows="4" required></textarea>
                                <div class="invalid-feedback">
                                    Please provide a reason for cancellation.
                                </div>
                                <small class="text-muted">
                                    Please explain why you want to cancel this order. This helps us improve our service.
                                </small>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
                                <button type="submit" name="cancel_order" class="btn btn-danger">
                                    Confirm Cancellation
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 