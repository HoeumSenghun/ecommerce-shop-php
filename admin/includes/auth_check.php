<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../includes/Auth.php';

// Create database connection
$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Check if user has permission (super_admin or product_manager)
if (!$auth->hasPermission('super_admin') && !$auth->hasPermission('product_manager')) {
    header("Location: index.php");
    exit();
}
?> 