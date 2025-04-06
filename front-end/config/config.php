<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Site settings
define('SITE_NAME', 'Jo Jo Ba Shop');
define('SITE_URL', 'http://localhost/php-project/front-end');

// Upload settings
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// Pagination
define('ITEMS_PER_PAGE', 12);

// Order status messages
define('ORDER_STATUS_MESSAGES', [
    'received' => 'Order received, pending processing',
    'processing' => 'Order is being processed',
    'shipped' => 'Order has been shipped',
    'delivered' => 'Order has been delivered',
    'cancelled' => 'Order has been cancelled'
]);

// Payment status messages
define('PAYMENT_STATUS_MESSAGES', [
    'pending' => 'Payment pending',
    'completed' => 'Payment completed',
    'refunded' => 'Payment refunded'
]);

// Initialize session if not already started
function init_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Function to format price
function format_price($price) {
    return number_format($price, 2, '.', ',') . ' USD';
}

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to generate random string
function generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $string;
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to require login
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . SITE_URL . '/login.php');
        exit();
    }
}

// Function to show flash message
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Function to get flash message
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

// Function to handle file upload
function handle_file_upload($file, $destination_dir) {
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception('File is too large. Maximum size is ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB');
    }

    // Check file type
    if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) {
        throw new Exception('Invalid file type. Allowed types: ' . implode(', ', ALLOWED_IMAGE_TYPES));
    }

    // Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = generate_random_string() . '.' . $file_extension;
    $filepath = $destination_dir . $filename;

    // Create directory if it doesn't exist
    if (!is_dir($destination_dir)) {
        mkdir($destination_dir, 0777, true);
    }

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to move uploaded file');
    }

    return $filename;
}

// Initialize session
init_session();
