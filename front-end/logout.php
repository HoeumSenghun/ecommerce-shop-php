<?php
require_once 'config/config.php';

// Destroy the session
session_destroy();

// Clear session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect to home page
header('Location: ' . SITE_URL);
exit();
