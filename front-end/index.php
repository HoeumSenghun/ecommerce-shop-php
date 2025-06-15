<?php
require_once 'config/config.php';
require_once 'config/Database.php';
require_once 'models/Product.php';
require_once 'models/Category.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize models
$product = new Product($db);
$category = new Category($db);

// Get featured products (latest 8 products)
$featured_products = $product->getAll(1, 8);

// Get main categories
$main_categories = $category->getMainCategories();

// Include header
include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-4 fw-bold">Welcome to <?= SITE_NAME ?></h1>
                <p class="lead">Discover our eco-friendly Products, sourced responsibly and delivered with care.</p>
                <div class="d-flex gap-2">
                    <a href="../front-end/products.php" class="btn btn-primary btn-lg">Shop Now</a>
                    <a href="#about" class="btn btn-outline-primary btn-lg">Learn More</a>
                </div>
            </div>
            <div class="col-md-6">
                <img src="./assets/images/rk keyboard.png" alt="Eco-friendly products" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</div>

<!-- Featured Promotions -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <div class="display-6 text-success mb-3">
                            <i class="bi bi-gift"></i>
                        </div>
                        <h3>New Customer Special</h3>
                        <p class="mb-3">Get 10% off your first order when you sign up for our newsletter!</p>
                        <a href="../front-end/register.php" class="btn btn-outline-success">Sign Up Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <div class="display-6 text-primary mb-3">
                            <i class="bi bi-truck"></i>
                        </div>
                        <h3>Free Delivery</h3>
                        <p class="mb-3">Free delivery on orders over $50 within Phnom Penh!</p>
                        <a href="products.php" class="btn btn-outline-primary">Shop Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-4">Shop by Category</h2>
        <div class="row g-4">
            <?php foreach ($main_categories as $cat): ?>
            <div class="col-md-4 col-lg-3">
                <a href="category.php?id=<?= $cat['category_id'] ?>" class="text-decoration-none">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <h3 class="h5 card-title"><?= htmlspecialchars($cat['category_name']) ?></h3>
                            <p class="text-muted"><?= $cat['product_count'] ?> Products</p>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-4">Featured Products</h2>
        <div class="row g-4">
            <?php foreach ($featured_products as $product): ?>
            <div class="col-md-3">
                <div class="card h-100">
                    <?php if ($product['primary_image']): ?>
                    <img src="<?= htmlspecialchars($product['primary_image']) ?>" 
                         class="card-img-top" 
                         alt="<?= htmlspecialchars($product['name']) ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title">
                            <?= htmlspecialchars($product['name']) ?>
                        </h5>
                        <p class="card-text text-muted">
                            <?= substr(htmlspecialchars($product['description']), 0, 100) ?>...
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h5 mb-0"><?= format_price($product['price']) ?></span>
                            <a href="../front-end/product.php?id=<?= $product['product_id'] ?>" 
                               class="btn btn-outline-primary">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="../front-end/products.php" class="btn btn-primary">View All Products</a>
        </div>
    </div>
</section>

<!-- Environmental Impact -->
<section class="py-5 bg-success text-white">
    <div class="container">
        <h2 class="text-center mb-5">Our Environmental Impact</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="text-center">
                    <i class="bi bi-tree display-4"></i>
                    <h4 class="mt-3">Sustainable Packaging</h4>
                    <p>100% recyclable or biodegradable packaging for all our products</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <i class="bi bi-recycle display-4"></i>
                    <h4 class="mt-3">Zero Waste</h4>
                    <p>We're committed to reducing waste in our operations</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <i class="bi bi-globe display-4"></i>
                    <h4 class="mt-3">Local Sourcing</h4>
                    <p>Supporting local farmers and reducing transportation emissions</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Customer Testimonials -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">What Our Customers Say</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="mb-3 text-warning">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="card-text">"Great quality products and excellent service. Love that they care about the environment!"</p>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-person-circle fs-4 me-2"></i>
                            <div>
                                <h6 class="mb-0">Hin Sokheng</h6>
                                <small class="text-muted">Regular Customer</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="mb-3 text-warning">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="card-text">"The organic selection is amazing! Fast delivery and great packaging."</p>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-person-circle fs-4 me-2"></i>
                            <div>
                                <h6 class="mb-0">Y Mengsea</h6>
                                <small class="text-muted">Verified Buyer</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="mb-3 text-warning">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-half"></i>
                        </div>
                        <p class="card-text">"Fresh products and eco-friendly packaging. Highly recommend!"</p>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-person-circle fs-4 me-2"></i>
                            <div>
                                <h6 class="mb-0">Sou Phalin</h6>
                                <small class="text-muted">New Customer</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="text-center">
                    <i class="bi bi-flower2 display-4 text-success"></i>
                    <h3 class="h5 mt-3">Eco-Friendly</h3>
                    <p>All our products are selected with environmental sustainability in mind.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <i class="bi bi-credit-card display-4 text-danger"></i>
                    <h3 class="h5 mt-3">Health-Conscious</h3>
                    <p>We prioritize your health with natural and organic options.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <i class="bi bi-truck display-4 text-primary"></i>
                    <h3 class="h5 mt-3">Fast Delivery</h3>
                    <p>Quick and reliable delivery to your doorstep.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Signup -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h2 class="mb-3">Stay Updated</h2>
                <p class="mb-4">Subscribe to our newsletter for exclusive offers and eco-friendly tips!</p>
                <form class="row g-3 justify-content-center">
                    <div class="col-md-8">
                        <input type="email" class="form-control form-control-lg" placeholder="Enter your email">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-lg">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>
