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

// Get user's orders
$orders = $order->getUserOrders($_SESSION['user_id']);

// Handle order cancellation
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $order_id = (int)$_POST['order_id'];
    $reason = sanitize_input($_POST['reason']);

    if ($order->cancelOrder($order_id, $_SESSION['user_id'], $reason)) {
        $message = 'Order cancelled successfully.';
        $message_type = 'success';
        // Refresh orders list
        $orders = $order->getUserOrders($_SESSION['user_id']);
    } else {
        $message = 'Failed to cancel order. Order might already be processed.';
        $message_type = 'danger';
    }
}

// Include header
include 'includes/header.php';
?>

<div class="container py-5">
    <h1 class="mb-4">My Orders</h1>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div class="text-center py-5">
            <i class="bi bi-bag-x display-1 text-muted"></i>
            <h3 class="mt-3">No Orders Yet</h3>
            <p class="text-muted">You haven't placed any orders yet.</p>
            <a href="/products.php" class="btn btn-primary">Start Shopping</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Payment</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>#<?= $order['order_id'] ?></td>
                                            <td>
                                                <?= date('M j, Y g:i A', strtotime($order['created_at'])) ?>
                                            </td>
                                            <td><?= $order['item_count'] ?> items</td>
                                            <td><?= format_price($order['total']) ?></td>
                                            <td>
                                                <?php
                                                $status_class = match($order['status']) {
                                                    'received' => 'info',
                                                    'processing' => 'primary',
                                                    'shipped' => 'success',
                                                    'delivered' => 'success',
                                                    'cancelled' => 'danger',
                                                    default => 'secondary'
                                                };
                                                ?>
                                                <span class="badge bg-<?= $status_class ?>">
                                                    <?= ucfirst($order['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $order['payment_status'] === 'completed' ? 'success' : 'warning' ?>">
                                                    <?= ucfirst($order['payment_status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="order-confirmation.php?id=<?= $order['order_id'] ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        View Details
                                                    </a>
                                                    
                                                    <?php if ($order['status'] === 'received'): ?>
                                                        <a href="cancel-order.php?id=<?= $order['order_id'] ?>" 
                                                           class="btn btn-sm btn-outline-danger">
                                                            Cancel
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
