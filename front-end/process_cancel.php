<?php
require_once 'config/config.php';
require_once 'config/Database.php';
require_once 'models/Order.php';

// Require login
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $order_id = (int)$_POST['order_id'];
    $reason = sanitize_input($_POST['reason']);
    $user_id = $_SESSION['user_id'];

    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();

    // Initialize order model
    $order = new Order($db);

    if ($order->cancelOrder($order_id, $user_id, $reason)) {
        set_flash_message('success', 'Order cancelled successfully.');
    } else {
        set_flash_message('danger', 'Failed to cancel order. The order might already be processed.');
    }
}

// Redirect back to orders page
header('Location: orders.php');
exit(); 