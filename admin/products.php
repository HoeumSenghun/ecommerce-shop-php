<?php
$pageTitle = 'Products';
$currentPage = 'products';

require_once 'config/Database.php';
require_once 'includes/Auth.php';
require_once 'models/Product.php';
require_once 'models/Category.php';
require_once 'models/ProductImage.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

// Check if user has permission
if(!$auth->hasPermission('super_admin') && !$auth->hasPermission('product_manager')) {
    header("Location: index.php");
    exit();
}

$product = new Product($db);
$category = new Category($db);
$productImage = new ProductImage($db);

// Process form submission
$message = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'create':
            case 'update':
                $product->name = $_POST['name'];
                $product->description = $_POST['description'];
                $product->price = $_POST['price'];
                $product->stock = $_POST['stock'];
                $product->category_id = $_POST['category_id'];
                $product->status = $_POST['status'];
                $product->dietary_tags = $_POST['dietary_tags'];
                $product->ingredients = $_POST['ingredients'];
                $product->allergen_warnings = $_POST['allergen_warnings'];
                
                if($_POST['action'] === 'create') {
                    $product->sku = $product->generateSKU($_POST['name']);
                    if($productId = $product->create()) {
                        // If image URL is provided, create product image
                        if(!empty($_POST['image_url'])) {
                            $productImage->product_id = $productId;
                            $productImage->image_url = $_POST['image_url'];
                            $productImage->is_primary = 1; // Set as primary since it's the first image
                            $productImage->create();
                        }
                        $message = "Product created successfully.";
                    } else {
                        $error = "Unable to create product.";
                    }
                } else {
                    $product->product_id = $_POST['product_id'];
                    $product->sku = $_POST['sku'];
                    if($product->update()) {
                        // If image URL is provided, create product image
                        if(!empty($_POST['image_url'])) {
                            $productImage->product_id = $_POST['product_id'];
                            $productImage->image_url = $_POST['image_url'];
                            $productImage->is_primary = 1;
                            $productImage->create();
                        }
                        $message = "Product updated successfully.";
                    } else {
                        $error = "Unable to update product.";
                    }
                }
                break;

            case 'delete':
                $product->product_id = $_POST['product_id'];
                if($product->delete()) {
                    $message = "Product deleted successfully.";
                } else {
                    $error = "Unable to delete product.";
                }
                break;
        }
    }
}

// Get search parameters
$search = $_GET['search'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Get all products
$result = $product->read($search, $categoryFilter, $statusFilter);
$products = $result->fetchAll(PDO::FETCH_ASSOC);

// Get all categories for dropdown
$categories = $category->read()->fetchAll(PDO::FETCH_ASSOC);

// Set action button for layout
$actionButton = '<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal">
                    <i class="bi bi-plus-circle"></i> Add Product
                </button>';

require_once 'includes/layout.php';
?>

<!-- Search Filters -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="category">
                    <option value="">All Categories</option>
                    <?php foreach($categories as $cat): ?>
                    <option value="<?php echo $cat['category_id']; ?>" <?php echo $categoryFilter == $cat['category_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Products List -->
<div class="card shadow mb-4">
    <?php if($message): ?>
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($products as $prod): ?>
                    <tr>
                        <td>
                            <?php if(!empty($prod['primary_image'])): ?>
                                <img src="<?php echo htmlspecialchars($prod['primary_image']); ?>" alt="Product Image" style="max-width: 50px;">
                            <?php else: ?>
                                <span class="text-muted">No image</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($prod['name']); ?></td>
                        <td><?php echo htmlspecialchars($prod['sku']); ?></td>
                        <td><?php echo htmlspecialchars($prod['category_name'] ?? 'Uncategorized'); ?></td>
                        <td>$<?php echo number_format($prod['price'], 2); ?></td>
                        <td><?php echo $prod['stock']; ?></td>
                        <td>
                            <span class="badge <?php echo $prod['status'] === 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo ucfirst($prod['status']); ?>
                            </span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary edit-product" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#productModal"
                                    data-id="<?php echo $prod['product_id']; ?>"
                                    data-name="<?php echo htmlspecialchars($prod['name']); ?>"
                                    data-description="<?php echo htmlspecialchars($prod['description']); ?>"
                                    data-price="<?php echo $prod['price']; ?>"
                                    data-stock="<?php echo $prod['stock']; ?>"
                                    data-category="<?php echo $prod['category_id']; ?>"
                                    data-sku="<?php echo htmlspecialchars($prod['sku']); ?>"
                                    data-status="<?php echo $prod['status']; ?>"
                                    data-dietary="<?php echo htmlspecialchars($prod['dietary_tags']); ?>"
                                    data-ingredients="<?php echo htmlspecialchars($prod['ingredients']); ?>"
                                    data-allergens="<?php echo htmlspecialchars($prod['allergen_warnings']); ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-info" 
                                    onclick="window.location.href='product_images.php?product_id=<?php echo $prod['product_id']; ?>'">
                                <i class="bi bi-images"></i>
                            </button>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="product_id" value="<?php echo $prod['product_id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="product_id" value="">
                <input type="hidden" name="sku" value="">
                
                <div class="modal-header">
                    <h5 class="modal-title">Add Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">Select Category</option>
                                <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>">
                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="price" class="form-label">Price</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label for="stock" class="form-label">Stock</label>
                            <input type="number" class="form-control" id="stock" name="stock" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="image_url" class="form-label">Product Image URL</label>
                        <input type="url" class="form-control" id="image_url" name="image_url" placeholder="https://example.com/image.jpg">
                        <small class="text-muted">Enter the URL of the product image</small>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="dietary_tags" class="form-label">Dietary Tags</label>
                        <input type="text" class="form-control" id="dietary_tags" name="dietary_tags" placeholder="e.g., Vegan, Gluten-Free">
                    </div>
                    <div class="mb-3">
                        <label for="ingredients" class="form-label">Ingredients</label>
                        <textarea class="form-control" id="ingredients" name="ingredients" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="allergen_warnings" class="form-label">Allergen Warnings</label>
                        <textarea class="form-control" id="allergen_warnings" name="allergen_warnings" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="available">Available</option>
                            <option value="out_of_stock">Out of Stock</option>
                            <option value="discontinued">Discontinued</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
$pageScripts = <<<EOT
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit product button clicks
    document.querySelectorAll('.edit-product').forEach(button => {
        button.addEventListener('click', function() {
            const modal = document.getElementById('productModal');
            const form = modal.querySelector('form');
            const title = modal.querySelector('.modal-title');
            
            // Set form values
            form.elements['action'].value = 'update';
            form.elements['product_id'].value = this.dataset.id;
            form.elements['name'].value = this.dataset.name;
            form.elements['description'].value = this.dataset.description;
            form.elements['price'].value = this.dataset.price;
            form.elements['stock'].value = this.dataset.stock;
            form.elements['category_id'].value = this.dataset.category;
            form.elements['sku'].value = this.dataset.sku;
            form.elements['status'].value = this.dataset.status;
            form.elements['dietary_tags'].value = this.dataset.dietary;
            form.elements['ingredients'].value = this.dataset.ingredients;
            form.elements['allergen_warnings'].value = this.dataset.allergens;
            
            // Update modal title
            title.textContent = 'Edit Product';
        });
    });

    // Reset form on modal close
    document.getElementById('productModal').addEventListener('hidden.bs.modal', function() {
        const form = this.querySelector('form');
        const title = this.querySelector('.modal-title');
        
        form.reset();
        form.elements['action'].value = 'create';
        form.elements['product_id'].value = '';
        form.elements['sku'].value = '';
        title.textContent = 'Add Product';
    });
});
</script>
EOT;

require_once 'includes/footer.php'; 
?> 