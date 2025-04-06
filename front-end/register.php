<?php
require_once 'config/config.php';
require_once 'config/Database.php';
require_once 'models/User.php';

// If user is already logged in, redirect to home
if (is_logged_in()) {
    header('Location: ' . SITE_URL);
    exit();
}

$error = '';
$success = false;

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $name = sanitize_input($_POST['name']);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);

    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // Initialize database and user model
        $database = new Database();
        $db = $database->getConnection();
        $user = new User($db);

        // Attempt to register user
        $register_data = [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'phone' => $phone,
            'address' => $address
        ];

        if ($user->register($register_data)) {
            $success = true;
            // Auto login after registration
            $result = $user->login($email, $password);
            if (!isset($result['error'])) {
                $_SESSION['user_id'] = $result['user_id'];
                $_SESSION['user_name'] = $result['name'];
                $_SESSION['user_email'] = $result['email'];
                
                header('Location: ' . SITE_URL);
                exit();
            }
        } else {
            $error = 'Registration failed. Email might already be registered.';
        }
    }
}

// Include header
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Create an Account</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success" role="alert">
                            Registration successful! You can now log in.
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name" 
                                       value="<?= isset($name) ? htmlspecialchars($name) : '' ?>"
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?= isset($email) ? htmlspecialchars($email) : '' ?>"
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password"
                                       required>
                                <div class="form-text">Must be at least 6 characters long</div>
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Confirm Password *</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="confirm_password" 
                                       name="confirm_password"
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="phone" 
                                       name="phone"
                                       value="<?= isset($phone) ? htmlspecialchars($phone) : '' ?>">
                            </div>
                            <div class="col-12">
                                <label for="address" class="form-label">Delivery Address</label>
                                <textarea class="form-control" 
                                          id="address" 
                                          name="address" 
                                          rows="3"><?= isset($address) ? htmlspecialchars($address) : '' ?></textarea>
                            </div>
                            <div class="col-12">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Create Account</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <p>Already have an account? <a href="/login.php">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
