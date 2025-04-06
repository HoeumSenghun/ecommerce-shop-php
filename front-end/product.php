<?php
require_once 'config/config.php';
require_once 'config/Database.php';
require_once 'models/Product.php';
require_once 'models/Cart.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: /products.php');
    exit();
}

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize models
$product_model = new Product($db);

// Get product details
$product = $product_model->getById($product_id);

if (!$product) {
    header('Location: /products.php');
    exit();
}

// Handle add to cart
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity > 0 && $quantity <= $product['stock']) {
        $cart = new Cart();
        $cart->addItem(
            $product['product_id'],
            $quantity,
            $product['price'],
            $product['name'],
            $product['images'][0] ?? null
        );
        $message = 'Product added to cart successfully!';
        $message_type = 'success';
    } else {
        $message = 'Invalid quantity selected.';
        $message_type = 'danger';
    }
}

// Include header
include 'includes/header.php';
?>

<div class="container py-5">
    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Product Images -->
        <div class="col-md-6 mb-4">
            <?php if (!empty($product['images'])): ?>
                <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <?php foreach ($product['images'] as $index => $image): ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                <img src="<?= htmlspecialchars($image) ?>" 
                                     class="d-block w-100" 
                                     alt="<?= htmlspecialchars($product['name']) ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($product['images']) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Product Details -->
        <div class="col-md-6">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/products.php">Products</a></li>
                    <li class="breadcrumb-item">
                        <a href="/products.php?category=<?= $product['category_id'] ?>">
                            <?= htmlspecialchars($product['category_name']) ?>
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <?= htmlspecialchars($product['name']) ?>
                    </li>
                </ol>
            </nav>

            <h1 class="mb-3"><?= htmlspecialchars($product['name']) ?></h1>
            
            <div class="mb-3">
                <h3 class="h2 mb-0 text-primary"><?= format_price($product['price']) ?></h3>
                <small class="text-muted">SKU: <?= htmlspecialchars($product['sku']) ?></small>
            </div>

            <?php if ($product['dietary_tags']): ?>
                <div class="mb-3">
                    <?php foreach (explode(',', $product['dietary_tags']) as $tag): ?>
                        <span class="badge bg-success me-1">
                            <?= htmlspecialchars(trim($tag)) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <p class="mb-4"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

            <?php if ($product['ingredients']): ?>
                <div class="mb-3">
                    <h4>Ingredients</h4>
                    <p><?= nl2br(htmlspecialchars($product['ingredients'])) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($product['allergen_warnings']): ?>
                <div class="mb-3">
                    <h4 class="text-danger">Allergen Warnings</h4>
                    <p><?= nl2br(htmlspecialchars($product['allergen_warnings'])) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($product['stock'] > 0): ?>
                <form method="POST" action="" class="mb-4">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <label for="quantity" class="form-label">Quantity:</label>
                            <select class="form-select" id="quantity" name="quantity">
                                <?php for ($i = 1; $i <= min($product['stock'], 10); $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" 
                                        name="add_to_cart" 
                                        class="btn btn-primary">
                                    <i class="bi bi-cart-plus"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                    <small class="text-muted">
                        <?= $product['stock'] ?> items in stock
                    </small>
                </form>
            <?php else: ?>
                <div class="alert alert-danger">
                    This product is currently out of stock.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
