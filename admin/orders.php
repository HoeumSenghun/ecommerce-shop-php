<?php
$pageTitle = 'Orders';
$currentPage = 'orders';

require_once 'config/Database.php';
require_once 'includes/Auth.php';
require_once 'models/Order.php';
require_once 'models/OrderItem.php';
require_once 'models/Product.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

// Check if user has permission
if(!$auth->hasPermission('super_admin') && !$auth->hasPermission('order_manager') && !$auth->hasPermission('product_manager')) {
    header("Location: index.php");
    exit();
}

$order = new Order($db);
$orderItem = new OrderItem($db);
$product = new Product($db);

// Process form submission
$message = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['action']) && $_POST['action'] === 'update_status') {
        $order->order_id = $_POST['order_id'];
        $order->status = $_POST['status'];
        
        if($order->updateStatus()) {
            $message = "Order status updated successfully.";
        } else {
            $error = "Failed to update order status.";
        }
    } else if(isset($_POST['action']) && $_POST['action'] === 'update_payment_status') {
        $order->order_id = $_POST['order_id'];
        $order->payment_status = $_POST['payment_status'];
        
        if($order->updatePaymentStatus()) {
            $message = "Payment status updated successfully.";
        } else {
            $error = "Failed to update payment status.";
        }
    }
}

// Create action button
$actionButton = '';

// Include layout
include_once 'includes/layout.php';

// Get order details if viewing a specific order
$orderDetails = null;
$orderItems = null;
if(isset($_GET['id'])) {
    $order->order_id = $_GET['id'];
    $orderDetails = $order->readOne();
    
    if($orderDetails) {
        $orderItems = $orderItem->readByOrder($order->order_id);
    } else {
        $error = "Order not found.";
    }
}
?>

<?php if(!empty($message)): ?>
<div class="alert alert-success"><?php echo $message; ?></div>
<?php endif; ?>

<?php if(!empty($error)): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if(isset($_GET['id']) && $orderDetails): ?>
<!-- Order Details View -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Order #<?php echo $orderDetails['order_id']; ?></h5>
        <a href="orders.php" class="btn btn-sm btn-outline-secondary">Back to Orders</a>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h6>Order Information</h6>
                <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($orderDetails['created_at'])); ?></p>
                <p><strong>Status:</strong> <span class="badge bg-<?php echo getStatusBadgeClass($orderDetails['status']); ?>"><?php echo ucfirst($orderDetails['status']); ?></span></p>
                <p><strong>Payment Status:</strong> <span class="badge bg-<?php echo getPaymentStatusBadgeClass($orderDetails['payment_status']); ?>"><?php echo ucfirst($orderDetails['payment_status']); ?></span></p>
                <p><strong>Payment Method:</strong> <?php echo ucfirst($orderDetails['payment_method']); ?></p>
                <p><strong>Total:</strong> $<?php echo number_format($orderDetails['total'], 2); ?></p>
            </div>
            <div class="col-md-6">
                <h6>Customer Information</h6>
                <p><strong>Customer ID:</strong> <?php echo $orderDetails['user_id']; ?></p>
                <!-- Add more customer details if available -->
            </div>
        </div>
        
        <h6>Order Items</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Unit Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($orderItems && $orderItems->rowCount() > 0): ?>
                        <?php while($item = $orderItems->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo $item['product_name']; ?></td>
                                <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>$<?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No items found for this order.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total:</th>
                        <th>$<?php echo number_format($orderDetails['total'], 2); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <h6>Update Order Status</h6>
                <form method="post" class="row g-3">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="order_id" value="<?php echo $orderDetails['order_id']; ?>">
                    <div class="col-md-8">
                        <select name="status" class="form-select">
                            <option value="received" <?php echo $orderDetails['status'] === 'received' ? 'selected' : ''; ?>>Received</option>
                            <option value="processing" <?php echo $orderDetails['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?php echo $orderDetails['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo $orderDetails['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo $orderDetails['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
            
            <div class="col-md-6">
                <h6>Update Payment Status</h6>
                <form method="post" class="row g-3">
                    <input type="hidden" name="action" value="update_payment_status">
                    <input type="hidden" name="order_id" value="<?php echo $orderDetails['order_id']; ?>">
                    <div class="col-md-8">
                        <select name="payment_status" class="form-select">
                            <option value="pending" <?php echo $orderDetails['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="completed" <?php echo $orderDetails['payment_status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="refunded" <?php echo $orderDetails['payment_status'] === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- Orders List View -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Payment Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $order->read();
                    if($stmt->rowCount() > 0):
                        while($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                    ?>
                    <tr>
                        <td><?php echo $row['order_id']; ?></td>
                        <td>Customer #<?php echo $row['user_id']; ?></td>
                        <td>$<?php echo number_format($row['total'], 2); ?></td>
                        <td><span class="badge bg-<?php echo getStatusBadgeClass($row['status']); ?>"><?php echo ucfirst($row['status']); ?></span></td>
                        <td><span class="badge bg-<?php echo getPaymentStatusBadgeClass($row['payment_status']); ?>"><?php echo ucfirst($row['payment_status']); ?></span></td>
                        <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                        <td>
                            <a href="orders.php?id=<?php echo $row['order_id']; ?>" class="btn btn-sm btn-info">View</a>
                        </td>
                    </tr>
                    <?php
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="7" class="text-center">No orders found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
// Helper functions for badge colors
function getStatusBadgeClass($status) {
    switch($status) {
        case 'received':
            return 'secondary';
        case 'processing':
            return 'primary';
        case 'shipped':
            return 'info';
        case 'delivered':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}

function getPaymentStatusBadgeClass($status) {
    switch($status) {
        case 'pending':
            return 'warning';
        case 'completed':
            return 'success';
        case 'refunded':
            return 'danger';
        default:
            return 'secondary';
    }
}
?>
