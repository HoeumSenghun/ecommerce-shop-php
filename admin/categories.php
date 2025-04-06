<?php
$pageTitle = 'Categories';
$currentPage = 'categories';

require_once 'config/Database.php';
require_once 'includes/Auth.php';
require_once 'models/Category.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

// Check if user has permission
if(!$auth->hasPermission('super_admin') && !$auth->hasPermission('product_manager')) {
    header("Location: index.php");
    exit();
}

$category = new Category($db);

// Process form submission
$message = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'create':
            case 'update':
                $category->category_name = $_POST['category_name'];
                $category->parent_category = !empty($_POST['parent_category']) ? $_POST['parent_category'] : null;
                $category->description = $_POST['description'];
                
                if($_POST['action'] === 'create') {
                    if($category->create()) {
                        $message = "Category created successfully.";
                    } else {
                        $error = "Unable to create category.";
                    }
                } else {
                    $category->category_id = $_POST['category_id'];
                    if($category->update()) {
                        $message = "Category updated successfully.";
                    } else {
                        $error = "Unable to update category.";
                    }
                }
                break;

            case 'delete':
                $category->category_id = $_POST['category_id'];
                if($category->delete()) {
                    $message = "Category deleted successfully.";
                } else {
                    $error = "Unable to delete category. Make sure it has no products or subcategories.";
                }
                break;
        }
    }
}

// Get all categories
$result = $category->read();
$categories = $result->fetchAll(PDO::FETCH_ASSOC);

// Set action button for layout
$actionButton = '<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                    <i class="bi bi-plus-circle"></i> Add Category
                </button>';

require_once 'includes/layout.php';
?>

<!-- Categories List -->
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
                        <th>Category Name</th>
                        <th>Parent Category</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($categories as $cat): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cat['category_name']); ?></td>
                        <td><?php echo htmlspecialchars($cat['parent_name'] ?? 'None'); ?></td>
                        <td><?php echo htmlspecialchars($cat['description']); ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary edit-category" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#categoryModal"
                                    data-id="<?php echo $cat['category_id']; ?>"
                                    data-name="<?php echo htmlspecialchars($cat['category_name']); ?>"
                                    data-parent="<?php echo $cat['parent_category']; ?>"
                                    data-description="<?php echo htmlspecialchars($cat['description']); ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="category_id" value="<?php echo $cat['category_id']; ?>">
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

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="category_id" value="">
                
                <div class="modal-header">
                    <h5 class="modal-title">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="category_name" name="category_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="parent_category" class="form-label">Parent Category</label>
                        <select class="form-select" id="parent_category" name="parent_category">
                            <option value="">None</option>
                            <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>">
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
$pageScripts = <<<EOT
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit category button clicks
    document.querySelectorAll('.edit-category').forEach(button => {
        button.addEventListener('click', function() {
            const modal = document.getElementById('categoryModal');
            const form = modal.querySelector('form');
            const title = modal.querySelector('.modal-title');
            
            // Set form values
            form.elements['action'].value = 'update';
            form.elements['category_id'].value = this.dataset.id;
            form.elements['category_name'].value = this.dataset.name;
            form.elements['parent_category'].value = this.dataset.parent;
            form.elements['description'].value = this.dataset.description;
            
            // Update modal title
            title.textContent = 'Edit Category';
        });
    });

    // Reset form on modal close
    document.getElementById('categoryModal').addEventListener('hidden.bs.modal', function() {
        const form = this.querySelector('form');
        const title = this.querySelector('.modal-title');
        
        form.reset();
        form.elements['action'].value = 'create';
        form.elements['category_id'].value = '';
        title.textContent = 'Add Category';
    });
});
</script>
EOT;

require_once 'includes/footer.php'; 
?> 