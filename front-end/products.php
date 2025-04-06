<?php
require_once 'config/config.php';
require_once 'config/Database.php';
require_once 'models/Product.php';
require_once 'models/Category.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize models
$product_model = new Product($db);
$category_model = new Category($db);

// Get current page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // Ensure page is at least 1

// Get selected category
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;

// Get search query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get products
$products = [];
if ($search) {
    $products = $product_model->search($search, $category_id);
} else {
    $products = $product_model->getAll($page, ITEMS_PER_PAGE, $category_id);
}

// Get categories for filter
$categories = $category_model->getMainCategories();

// Include header
include 'includes/header.php';
?>

<div class="container py-4">
    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Search</h5>
                    <form action="" method="GET" class="mb-4">
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control" 
                                   name="search" 
                                   value="<?= htmlspecialchars($search) ?>" 
                                   placeholder="Search products...">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>

                    <h5 class="card-title">Categories</h5>
                    <div class="list-group">
                        <a href="/products.php" 
                           class="list-group-item list-group-item-action <?= !$category_id ? 'active' : '' ?>">
                            All Categories
                        </a>
                        <?php foreach ($categories as $cat): ?>
                        <a href="../front-end/products.php?category=<?= $cat['category_id'] ?>" 
                           class="list-group-item list-group-item-action <?= $category_id == $cat['category_id'] ? 'active' : '' ?>">
                            <?= htmlspecialchars($cat['category_name']) ?>
                            <span class="badge bg-secondary float-end">
                                <?= $cat['product_count'] ?>
                            </span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-md-9">
            <?php if ($search): ?>
                <h2 class="mb-4">Search Results for "<?= htmlspecialchars($search) ?>"</h2>
            <?php elseif ($category_id): ?>
                <?php foreach ($categories as $cat): ?>
                    <?php if ($cat['category_id'] == $category_id): ?>
                        <h2 class="mb-4"><?= htmlspecialchars($cat['category_name']) ?></h2>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <h2 class="mb-4">All Products</h2>
            <?php endif; ?>

            <?php if (empty($products)): ?>
                <div class="alert alert-info">
                    No products found.
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($products as $product): ?>
                    <div class="col-md-4">
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

                <?php if (!$search): ?>
                <!-- Pagination -->
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" 
                               href="?page=<?= $page - 1 ?><?= $category_id ? '&category=' . $category_id : '' ?>">
                                Previous
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="page-item active">
                            <span class="page-link"><?= $page ?></span>
                        </li>
                        <li class="page-item">
                            <a class="page-link" 
                               href="?page=<?= $page + 1 ?><?= $category_id ? '&category=' . $category_id : '' ?>">
                                Next
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>
