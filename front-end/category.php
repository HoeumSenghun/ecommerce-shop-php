<?php
require_once 'config/config.php';
require_once 'config/Database.php';
require_once 'models/Category.php';
require_once 'models/Product.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize Category and Product objects
$category = new Category($db);
$product = new Product($db);

// Get category ID from URL
$category_id = isset($_GET['id']) ? $_GET['id'] : null;

// Get page number for pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 12;
$offset = ($page - 1) * $items_per_page;

// Get category details if ID is provided
$category_details = null;
$products = [];
$total_products = 0;

if ($category_id) {
    $category_details = $category->getById($category_id);
    if ($category_details) {
        // Get products for this category with pagination
        $products = $product->getAll($page, $items_per_page, $category_id);
        
        // Get total products from category details
        $total_products = $category_details['product_count'];
    }
}

// Get all categories for the sidebar
$categories = $category->getAll();

// Calculate total pages for pagination
$total_pages = ceil($total_products / $items_per_page);

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <!-- Categories Sidebar -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Categories</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach ($categories as $cat): ?>
                            <?php if (!$cat['parent_category']): ?>
                                <a href="category.php?id=<?php echo $cat['category_id']; ?>" 
                                   class="list-group-item list-group-item-action <?php echo ($category_id == $cat['category_id']) ? 'active' : ''; ?>">
                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                    <span class="badge bg-secondary float-end"><?php echo $cat['product_count']; ?></span>
                                </a>
                                <?php
                                // Display subcategories if this is the current category or parent of current category
                                if ($category_id == $cat['category_id'] || 
                                    ($category_details && $category_details['parent_category'] == $cat['category_id'])):
                                ?>
                                    <?php foreach ($categories as $subcat): ?>
                                        <?php if ($subcat['parent_category'] == $cat['category_id']): ?>
                                            <a href="category.php?id=<?php echo $subcat['category_id']; ?>" 
                                               class="list-group-item list-group-item-action ps-4 <?php echo ($category_id == $subcat['category_id']) ? 'active' : ''; ?>">
                                                <?php echo htmlspecialchars($subcat['category_name']); ?>
                                                <span class="badge bg-secondary float-end"><?php echo $subcat['product_count']; ?></span>
                                            </a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-md-9">
            <?php if ($category_details): ?>
                <h2><?php echo htmlspecialchars($category_details['category_name']); ?></h2>
                <?php if ($category_details['description']): ?>
                    <p class="lead"><?php echo htmlspecialchars($category_details['description']); ?></p>
                <?php endif; ?>

                <?php if (!empty($products)): ?>
                    <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
                        <?php foreach ($products as $product): ?>
                            <div class="col">
                                <div class="card h-100">
                                    <?php if ($product['primary_image']): ?>
                                        <img src="<?php echo htmlspecialchars($product['primary_image']); ?>" 
                                             class="card-img-top" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <a href="product.php?id=<?php echo $product['product_id']; ?>" 
                                               class="text-decoration-none">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                            </a>
                                        </h5>
                                        <p class="card-text">
                                            <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?>
                                        </p>
                                        <p class="card-text">
                                            <strong>Price: $<?php echo number_format($product['price'], 2); ?></strong>
                                        </p>
                                        <a href="product.php?id=<?php echo $product['product_id']; ?>" 
                                           class="btn btn-primary">View Details</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" 
                                           href="category.php?id=<?php echo $category_id; ?>&page=<?php echo ($page - 1); ?>">
                                            Previous
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link" 
                                           href="category.php?id=<?php echo $category_id; ?>&page=<?php echo $i; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" 
                                           href="category.php?id=<?php echo $category_id; ?>&page=<?php echo ($page + 1); ?>">
                                            Next
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        No products found in this category.
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    Please select a category from the sidebar.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
