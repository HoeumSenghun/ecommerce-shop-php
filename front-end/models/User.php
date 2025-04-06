<?php
class User {
    private $conn;
    private $table = 'users';

    // User properties
    public $user_id;
    public $name;
    public $email;
    public $password;
    public $phone;
    public $address;
    public $status;
    public $profile_image;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($data) {
        $query = "INSERT INTO " . $this->table . "
                (name, email, password_hash, phone, address)
                VALUES
                (:name, :email, :password, :phone, :address)";

        try {
            $stmt = $this->conn->prepare($query);
            
            // Hash password
            $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Bind data
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password', $password_hash);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':address', $data['address']);

            if($stmt->execute()) {
                return true;
            }
            return false;
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function login($email, $password) {
        $query = "SELECT user_id, name, email, password_hash, status 
                FROM " . $this->table . "
                WHERE email = :email";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if($row['status'] === 'suspended') {
                    return ['error' => 'Account is suspended'];
                }

                if(password_verify($password, $row['password_hash'])) {
                    unset($row['password_hash']);
                    return $row;
                }
            }
            return ['error' => 'Invalid credentials'];
        } catch(PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getById($id) {
        $query = "SELECT user_id, name, email, phone, address, status, profile_image, registration_date 
                FROM " . $this->table . "
                WHERE user_id = :id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return null;
        }
    }

    public function update($data) {
        $query = "UPDATE " . $this->table . "
                SET name = :name,
                    phone = :phone,
                    address = :address";

        if(isset($data['password']) && !empty($data['password'])) {
            $query .= ", password_hash = :password";
        }

        if(isset($data['profile_image'])) {
            $query .= ", profile_image = :profile_image";
        }

        $query .= " WHERE user_id = :user_id";

        try {
            $stmt = $this->conn->prepare($query);
            
            // Bind data
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':user_id', $data['user_id']);

            if(isset($data['password']) && !empty($data['password'])) {
                $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
                $stmt->bindParam(':password', $password_hash);
            }

            if(isset($data['profile_image'])) {
                $stmt->bindParam(':profile_image', $data['profile_image']);
            }

            return $stmt->execute();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
}
