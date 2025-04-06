<?php
require_once 'Auth.php';
if(!isset($auth)) {
    require_once 'config/Database.php';
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);
}

if(!$auth->isLoggedIn()) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin Panel'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }
        .sidebar .nav-link {
            color: #333;
            padding: .5rem 1rem;
        }
        .sidebar .nav-link.active {
            color: #0d6efd;
        }
        .sidebar .nav-link:hover {
            color: #0d6efd;
        }
        .main-content {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5>Admin Panel</h5>
                        <small class="text-muted"><?php echo $_SESSION['username']; ?></small>
                    </div>
                    <ul class="nav flex-column">
                        <hr>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>" href="index.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <?php if($auth->hasPermission('super_admin') || $auth->hasPermission('product_manager')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'products' ? 'active' : ''; ?>" href="products.php">
                                <i class="bi bi-box"></i> Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'categories' ? 'active' : ''; ?>" href="categories.php">
                                <i class="bi bi-grid"></i> Categories
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if($auth->hasPermission('super_admin') || $auth->hasPermission('order_manager') || $auth->hasPermission('product_manager')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'orders' ? 'active' : ''; ?>" href="orders.php">
                                <i class="bi bi-cart"></i> Orders
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if($auth->hasPermission('super_admin')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'users' ? 'active' : ''; ?>" href="404.php">
                                <i class="bi bi-people"></i> Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'admins' ? 'active' : ''; ?>" href="404.php">
                                <i class="bi bi-person-badge"></i> Admins
                            </a>
                        </li>
                        
                        <?php endif; ?>
                        
                        <li class="nav-item">
                        <br>
                        <hr>
                            <a class="nav-link" href="logout.php">

                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <?php if(isset($pageTitle)): ?>
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2"><?php echo $pageTitle; ?></h1>
                        <?php if(isset($actionButton)): ?>
                            <?php echo $actionButton; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
