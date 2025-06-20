<?php
// session_start();
// require_once 'config/database.php';
// require_once 'includes/file-upload-delete.php';
include 'includes/timeout.php';

// Set custom max size (5MB)
$maxSizeMB = 5;

$user_id = $_SESSION['user_id'] ?? 0;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'update_status':
            $product_id = (int) $_POST['product_id'];
            $new_status = $_POST['status'];

            $stmt = $conn->prepare("UPDATE products SET status = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("sii", $new_status, $product_id, $user_id);
            $stmt->execute();
            break;

        case 'delete_product':
            $product_id = (int) $_POST['product_id'];

            // Get product info before deletion
            $stmt = $conn->prepare("SELECT image_url FROM products WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $product_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();

            if ($product && $product['image_url'] && file_exists($product['image_url'])) {
                unlink($product['image_url']);
            }

            // Delete from database
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $product_id, $user_id);
            $stmt->execute();
            break;

        case 'add_product':
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $price = floatval($_POST['price']);
            $category = trim($_POST['category']);

            if (empty($title) || empty($description) || $price <= 0 || empty($category)) {
                $error = "All fields are required and price must be positive";
            } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] == UPLOAD_ERR_NO_FILE) {
                $error = "Please select an image to upload";
            } else {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $result = uploadFile($_FILES['image'], 'uploads/products/', $allowed_types, $maxSizeMB);
                if ($result['error']) {
                    $error = $result['error'];
                } else {
                    $stmt = $conn->prepare("INSERT INTO products (user_id, title, description, price, category, image_url) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("issdss", $user_id, $title, $description, $price, $category, $result['filepath']);
                    if ($stmt->execute()) {
                        $success = "Product added successfully";
                    } else {
                        $error = "Error adding product";
                        if (file_exists($result['filepath'])) {
                            unlink($result['filepath']);
                        }
                    }
                }
            }
            break;
    }

    // Refresh the page
    header("Location: user-dashboard.php?page=products");
    exit();
}
?>

<div class="dashboard-card">
    <div class="card-header">
        <h2>My Products</h2>
        <button class="btn btn-primary" onclick="showAddForm()">
            <i class="fas fa-plus"></i> Add New Product
        </button>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <!-- Add Product Form -->
    <div id="addForm" class="add-form" style="display: none;">
        <form action="user-dashboard.php?page=products" method="post" enctype="multipart/form-data"
            onsubmit="return validateFileSize();">
            <input type="hidden" name="action" value="add_product">

            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3" required></textarea>
            </div>

            <div class="form-group">
                <label for="price">Price ($)</label>
                <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" id="category" name="category" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="image">Product Image</label>
                <input type="file" id="image" name="image" class="form-control" accept="image/jpeg,image/png,image/gif"
                    data-max-size="<?php echo $maxSizeMB; ?>" required>
                <small class="form-text">Supported types: JPG, PNG, GIF. Max size:
                    <?php echo number_format($maxSizeMB, 2); ?>MB</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add Product</button>
                <button type="button" class="btn btn-secondary" onclick="hideAddForm()">Cancel</button>
            </div>
        </form>
    </div>

    <?php if (empty($products)): ?>
        <div class="empty-state">
            <i class="fas fa-box"></i>
            <p>No products found. Start by adding your first product!</p>
        </div>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if ($product['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>"
                                alt="<?php echo htmlspecialchars($product['title']); ?>">
                        <?php else: ?>
                            <div class="no-image">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['title']); ?></h3>
                        <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                        <p class="category"><?php echo htmlspecialchars($product['category']); ?></p>
                        <div class="product-meta">
                            <span class="status-badge <?php echo $product['status']; ?>">
                                <?php echo ucfirst($product['status']); ?>
                            </span>
                            <span class="date"><?php echo date('M d, Y', strtotime($product['created_at'])); ?></span>
                        </div>
                    </div>
                    <div class="product-actions">
                        <form method="POST" class="status-form">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <select name="status" onchange="this.form.submit()"
                                class="status-select <?php echo $product['status']; ?>">
                                <option value="active" <?php echo $product['status'] == 'active' ? 'selected' : ''; ?>>Active
                                </option>
                                <option value="inactive" <?php echo $product['status'] == 'inactive' ? 'selected' : ''; ?>>
                                    Inactive</option>
                                <option value="sold" <?php echo $product['status'] == 'sold' ? 'selected' : ''; ?>>Sold</option>
                            </select>
                        </form>
                        <button onclick="deleteProduct(<?php echo $product['id']; ?>)" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="assets/js/validateFileSize.js"></script>
<script>
    function showAddForm() {
        document.getElementById('addForm').style.display = 'block';
    }

    function hideAddForm() {
        document.getElementById('addForm').style.display = 'none';
    }

    function deleteProduct(productId) {
        if (confirm('Are you sure you want to delete this product?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="action" value="delete_product">
            <input type="hidden" name="product_id" value="${productId}">
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>