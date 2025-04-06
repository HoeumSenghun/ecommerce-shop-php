<?php
require_once 'config/database.php';
require_once 'models/ProductImage.php';
require_once 'models/Product.php';
require_once 'includes/auth_check.php';

$database = new Database();
$db = $database->getConnection();
$productImage = new ProductImage($db);
$product = new Product($db);

// Get product ID from URL
$product_id = $_GET['product_id'] ?? null;

if (!$product_id) {
    header("Location: products.php");
    exit();
}

// Get product details
$product->product_id = $product_id;
$product->readOne();

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'add') {
    $productImage->product_id = $_POST['product_id'];
    $productImage->image_url = $_POST['image_url'];
    $productImage->is_primary = isset($_POST['is_primary']) ? 1 : 0;
    
    if ($productImage->create()) {
        $message = "Image added successfully.";
    } else {
        $error = "Failed to add image.";
    }
} elseif ($action === 'delete') {
    $productImage->image_id = $_POST['image_id'];
    
    if ($productImage->delete()) {
        $message = "Image deleted successfully.";
    } else {
        $error = "Failed to delete image.";
    }
} elseif ($action === 'set_primary') {
    $productImage->image_id = $_POST['image_id'];
    $productImage->product_id = $_POST['product_id'];
    
    if ($productImage->setPrimary()) {
        $message = "Primary image set successfully.";
    } else {
        $error = "Failed to set primary image.";
    }
}

// Get all images for this product
$images = $productImage->getByProductId($product_id)->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Manage Product Images';
require_once 'includes/layout.php';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <h2>Manage Images for <?php echo htmlspecialchars($product->name); ?></h2>
            <a href="products.php" class="btn btn-secondary mb-3">
                <i class="bi bi-arrow-left"></i> Back to Products
            </a>
        </div>
    </div>

    <?php if(isset($message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if(isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Add Image Form -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4>Add New Image</h4>
                </div>
                <div class="card-body">
                    <form action="product_images.php?product_id=<?php echo $product_id; ?>" method="POST">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        
                        <div class="mb-3">
                            <label for="image_url" class="form-label">Image URL</label>
                            <input type="url" class="form-control" id="image_url" name="image_url" required>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_primary" name="is_primary">
                            <label class="form-check-label" for="is_primary">Set as primary image</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Add Image</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Existing Images -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Product Images</h4>
                </div>
                <div class="card-body">
                    <?php if(empty($images)): ?>
                        <p class="text-muted">No images added yet.</p>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach($images as $image): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <img src="<?php echo htmlspecialchars($image['image_url']); ?>" 
                                         class="card-img-top" 
                                         alt="Product Image"
                                         style="height: 200px; object-fit: cover;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <?php if($image['is_primary']): ?>
                                                <span class="badge bg-success">Primary Image</span>
                                            <?php else: ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="set_primary">
                                                    <input type="hidden" name="image_id" value="<?php echo $image['image_id']; ?>">
                                                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary">Set as Primary</button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this image?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="image_id" value="<?php echo $image['image_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 