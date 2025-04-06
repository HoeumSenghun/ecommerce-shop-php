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

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        // Initialize database and user model
        $database = new Database();
        $db = $database->getConnection();
        $user = new User($db);

        // Attempt login
        $result = $user->login($email, $password);

        if (isset($result['error'])) {
            $error = $result['error'];
        } else {
            // Set user session
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['user_name'] = $result['name'];
            $_SESSION['user_email'] = $result['email'];

            // Redirect to intended page or home
            $redirect = isset($_SESSION['redirect_after_login']) 
                ? $_SESSION['redirect_after_login'] 
                : SITE_URL;
            unset($_SESSION['redirect_after_login']);
            
            header('Location: ' . $redirect);
            exit();
        }
    }
}

// Include header
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Login</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   value="<?= isset($email) ? htmlspecialchars($email) : '' ?>"
                                   required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <p>Don't have an account? <a href="/register.php">Register here</a></p>
                        <p><a href="/forgot-password.php">Forgot your password?</a></p>
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
