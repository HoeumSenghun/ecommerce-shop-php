<?php
init_session();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>kurizerk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Top Bar -->
    <div class="bg-success py-2 text-white">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-none d-md-flex gap-3">
                    <small><i class="bi bi-telephone"></i> (855) 097-868-4464</small>
                    <small><i class="bi bi-envelope"></i> contact@kurizerk.com</small>
                </div>
                <div class="d-flex gap-3">
                    <small><i class="bi bi-truck"></i> Free delivery for all Order of $50</small>
                    <small><i class="bi bi-clock"></i> 24/7 Support</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="sticky-top">
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
            <div class="container">
                <a class="navbar-brand fw-bold fs-4" href="./">
                    <span class="text-success">ku</span> ri <span class="text-primary">zerk</span>
                </a>

                <!-- Search Form -->
                <form class="d-none d-lg-flex mx-4 flex-grow-1">
                    <div class="input-group">
                        <input type="search" class="form-control" placeholder="Search for products..." aria-label="Search">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>

                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="./">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="category.php">Categories</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="products.php">Products</a>
                        </li>
                    </ul>

                    <ul class="navbar-nav align-items-center">
                        <li class="nav-item">
                            <a class="nav-link position-relative" href="cart.php">
                                <i class="bi bi-cart fs-5"></i>
                                <?php if(isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        <?= $_SESSION['cart_count'] ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-circle fs-5"></i>
                                    <span class="d-none d-lg-inline"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                    <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                                    <li><a class="dropdown-item" href="orders.php"><i class="bi bi-box me-2"></i>Orders</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item ms-2">
                                <a class="btn btn-outline-primary me-2" href="login.php">Login</a>
                                <a class="btn btn-primary" href="register.php">Register</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Mobile Search (shows only on mobile) -->
        <div class="bg-white d-lg-none border-bottom p-2">
            <form class="container">
                <div class="input-group">
                    <input type="search" class="form-control" placeholder="Search for products..." aria-label="Search">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </form>
        </div>
    </header>

    <div class="container my-4">
