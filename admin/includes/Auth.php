<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Auth {
    private $conn;
    private $table = "admins";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($username, $password) {
        $query = "SELECT admin_id, username, password_hash, role, email FROM " . $this->table . " WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($password, $row['password_hash'])) {
                $_SESSION['admin_id'] = $row['admin_id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['email'] = $row['email'];
                
                // Update last login
                $update = "UPDATE " . $this->table . " SET last_login = NOW() WHERE admin_id = :admin_id";
                $stmt = $this->conn->prepare($update);
                $stmt->bindParam(":admin_id", $row['admin_id']);
                $stmt->execute();
                
                return true;
            }
        }
        return false;
    }

    public function isLoggedIn() {
        return isset($_SESSION['admin_id']) && isset($_SESSION['role']);
    }

    public function logout() {
        unset($_SESSION['admin_id']);
        unset($_SESSION['username']);
        unset($_SESSION['role']);
        unset($_SESSION['email']);
        session_destroy();
        return true;
    }

    public function hasPermission($requiredRole) {
        if(!$this->isLoggedIn()) return false;
        if(!isset($_SESSION['role'])) return false;
        if($_SESSION['role'] === 'super_admin') return true;
        return $_SESSION['role'] === $requiredRole;
    }

    public function getCurrentRole() {
        return $_SESSION['role'] ?? null;
    }
} 