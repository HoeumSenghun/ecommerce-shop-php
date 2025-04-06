<?php
require_once 'config/Database.php';
require_once 'includes/Auth.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

$auth->logout();
header("Location: login.php");
exit(); 