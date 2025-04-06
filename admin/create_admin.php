<?php
require_once 'config/Database.php';

$database = new Database();
$db = $database->getConnection();

// Create super admin user
$username = 'senghun';
$password = '1205'; // Change this password after first login
$email = 'senghun@gmail.com';
$role = 'super_admin';

// Hash the password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // Check if admin already exists
    $check = $db->prepare("SELECT admin_id FROM admins WHERE username = ?");
    $check->execute([$username]);
    
    if($check->rowCount() === 0) {
        // Insert new admin
        $query = "INSERT INTO admins (username, password_hash, email, role) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$username, $password_hash, $email, $role]);
        
        echo "Super admin created successfully!<br>";
        echo "Username: " . $username . "<br>";
        echo "Password: " . $password . "<br>";
        echo "Please change the password after first login.";
    } else {
        echo "Admin user already exists!";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
} 