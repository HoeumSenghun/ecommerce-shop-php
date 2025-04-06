<?php
require_once 'config/config.php';
require_once 'config/Database.php';
require_once 'models/User.php';

// Require login
require_login();

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize user model
$user_model = new User($db);

// Get user details
$user = $user_model->getById($_SESSION['user_id']);

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'user_id' => $_SESSION['user_id'],
        'name' => sanitize_input($_POST['name']),
        'phone' => sanitize_input($_POST['phone']),
        'address' => sanitize_input($_POST['address'])
    ];

    // Handle password update if provided
    if (!empty($_POST['new_password'])) {
        if (empty($_POST['current_password'])) {
            $message = 'Current password is required to set a new password.';
            $message_type = 'danger';
        } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
            $message = 'New passwords do not match.';
            $message_type = 'danger';
        } else {
            // Verify current password
            $login_check = $user_model->login($user['email'], $_POST['current_password']);
            if (isset($login_check['error'])) {
                $message = 'Current password is incorrect.';
                $message_type = 'danger';
            } else {
                $data['password'] = $_POST['new_password'];
            }
        }
    }

    // Proceed with update if no password-related errors
    if (empty($message)) {
        // Handle profile image upload
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            try {
                $upload_dir = UPLOAD_DIR . 'profiles/';
                $filename = handle_file_upload($_FILES['profile_image'], $upload_dir);
                $data['profile_image'] = '/uploads/profiles/' . $filename;
            } catch (Exception $e) {
                $message = 'Profile image upload failed: ' . $e->getMessage();
                $message_type = 'danger';
            }
        }

        if (empty($message) && $user_model->update($data)) {
            $message = 'Profile updated successfully!';
            $message_type = 'success';
            
            // Refresh user data
            $user = $user_model->getById($_SESSION['user_id']);
            
            // Update session name if changed
            $_SESSION['user_name'] = $user['name'];
        } else {
            $message = 'Failed to update profile.';
            $message_type = 'danger';
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
                    <h2 class="card-title mb-4">My Profile</h2>

                    <?php if ($message): ?>
                        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="row g-3">
                            <!-- Profile Image -->
                            <div class="col-12 text-center mb-3">
                                <?php if ($user['profile_image']): ?>
                                    <img src="<?= htmlspecialchars($user['profile_image']) ?>" 
                                         alt="Profile" 
                                         class="rounded-circle mb-3"
                                         style="width: 150px; height: 150px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                                         style="width: 150px; height: 150px;">
                                        <i class="bi bi-person display-1 text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <input type="file" 
                                           class="form-control" 
                                           id="profile_image" 
                                           name="profile_image" 
                                           accept="image/*">
                                    <div class="form-text">Maximum file size: 5MB</div>
                                </div>
                            </div>

                            <!-- Basic Information -->
                            <div class="col-12">
                                <h4>Basic Information</h4>
                                <hr>
                            </div>

                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name" 
                                       value="<?= htmlspecialchars($user['name']) ?>" 
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       value="<?= htmlspecialchars($user['email']) ?>" 
                                       disabled>
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="phone" 
                                       name="phone" 
                                       value="<?= htmlspecialchars($user['phone']) ?>">
                            </div>

                            <div class="col-12">
                                <label for="address" class="form-label">Delivery Address</label>
                                <textarea class="form-control" 
                                          id="address" 
                                          name="address" 
                                          rows="3"><?= htmlspecialchars($user['address']) ?></textarea>
                            </div>

                            <!-- Change Password -->
                            <div class="col-12">
                                <h4 class="mt-4">Change Password</h4>
                                <hr>
                            </div>

                            <div class="col-md-6">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="current_password" 
                                       name="current_password">
                            </div>

                            <div class="col-md-6">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="new_password" 
                                       name="new_password">
                            </div>

                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="confirm_password" 
                                       name="confirm_password">
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    Update Profile
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
