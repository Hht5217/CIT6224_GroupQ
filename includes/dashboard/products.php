<?php
// session_start();
// require_once 'config/database.php';
// require_once 'includes/file-upload-delete.php';
include_once __DIR__ . '/../product-categories.php';
include 'includes/timeout.php';

// Set custom max size (5MB)
$maxSizeMB = 5;

$user_id = $_SESSION['user_id'] ?? 0;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if (!isset($conn)) {
        $error = "Database connection not available.";
        error_log("Database connection missing in products.php");
    } elseif ($user_id == 0) {
        $error = "User not logged in.";
        error_log("User ID is 0 in products.php");
    } else {
        switch ($_POST['action']) {
            case 'update_status':
                $product_id = (int) $_POST['product_id'];
                $new_status = $_POST['status'];
                $stmt = $conn->prepare("UPDATE products SET status = ? WHERE id = ? AND user_id = ?");
                if ($stmt) {
                    $stmt->bind_param("sii", $new_status, $product_id, $user_id);
                    if ($stmt->execute()) {
                        $success = "Status updated successfully.";
                    } else {
                        $error = "Error updating status: " . $stmt->error;
                        error_log("Update status error: " . $stmt->error);
                    }
                    $stmt->close();
                } else {
                    $error = "Failed to prepare update statement.";
                    error_log("Prepare update statement failed: " . $conn->error);
                }
                break;

            case 'add_product':
                $title = trim($_POST['title']);
                $description = trim($_POST['description']);
                $price = floatval($_POST['price']);
                $category = trim($_POST['category']);

                if (empty($title) || empty($description) || $price <= 0 || empty($category)) {
                    $error = "All fields are required and price must be positive.";
                    error_log("Validation failed: title='$title', description='$description', price=$price, category='$category'");
                } elseif (!array_key_exists($category, $product_categories)) {
                    $error = "Invalid category selected.";
                    error_log("Invalid category: $category");
                } else {
                    $image_url = '';
                    if (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
                        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                        $result = uploadFile($_FILES['image'], 'Uploads/products/', $allowed_types, $maxSizeMB);
                        if ($result['error']) {
                            $error = $result['error'];
                            error_log("Upload error: " . $result['error']);
                        } else {
                            $image_url = $result['filepath'];
                        }
                    } else {
                        $image_result = getDefaultProductImage($category);
                        if ($image_result['exists']) {
                            $image_url = $image_result['path'];
                        } elseif ($image_result['fallback_exists']) {
                            $image_url = $image_result['fallback_path'];
                        } else {
                            $error = "Default image for category '$category' not found at: " . $image_result['path'] .
                                ", fallback not found at: " . $image_result['fallback_path'];
                            error_log("Default image missing: " . $image_result['path'] . ", fallback missing: " . $image_result['fallback_path']);
                        }
                    }

                    if (!$error) {
                        $stmt = $conn->prepare("INSERT INTO products (user_id, title, description, price, category, image_url) VALUES (?, ?, ?, ?, ?, ?)");
                        if ($stmt) {
                            $stmt->bind_param("issdss", $user_id, $title, $description, $price, $category, $image_url);
                            if ($stmt->execute()) {
                                $success = "Product added successfully.";
                                $_SESSION['success'] = $success; // Store success message for redirect
                            } else {
                                $error = "Error adding product: " . $stmt->error;
                                error_log("Insert error: " . $stmt->error);
                                if ($image_url && file_exists($image_url) && strpos($image_url, 'Uploads/products/') === 0) {
                                    unlink($image_url);
                                }
                            }
                            $stmt->close();
                        } else {
                            $error = "Failed to prepare insert statement: " . $conn->error;
                            error_log("Prepare insert statement failed: " . $conn->error);
                        }
                    }
                }
                break;
        }
    }

    // Only redirect on success
    if ($success && !$error) {
        header("Location: user-dashboard.php?page=products");
        exit();
    }
}
?>

<div class="dashboard-card">
    <div class="card-header">
        <h2>My Products</h2>
        <button class="btn btn-primary" onclick="showAddForm()">
            <i class="fas fa-plus"></i> Add New Product
        </button>
    </div>

    <?php
    // Display session success message if set
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
        unset($_SESSION['success']);
    }
    if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <!-- Add Product Form -->
    <div id="addForm" class="add-form" style="display: none;">
        <form action="user-dashboard.php?page=products" method="post" enctype="multipart/form-data"
            onsubmit="return validateForm(this);">
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
                <input type="number" id="price" name="price" class="form-control" step="0.01" min="0.01" required>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" class="form-control" required>
                    <option value="">Select a category</option>
                    <?php showProductCategoryOptions(''); ?>
                </select>
            </div>

            <div class="form-group">
                <label for="image">Product Image (Optional)</label>
                <input type="file" id="image" name="image" class="form-control" accept="image/jpeg,image/png,image/gif"
                    data-max-size="<?php echo $maxSizeMB; ?>">
                <small class="form-text">Supported types: JPG, PNG, GIF. Max size:
                    <?php echo number_format($maxSizeMB, 2); ?>MB. If no image is uploaded, a default image based on the
                    category will be used.</small>
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
                        <p class="category">
                            <?php echo isset($product_categories[$product['category']]) ? htmlspecialchars($product_categories[$product['category']]) : 'Not specified'; ?>
                        </p>
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
                            <select name="status" class="status-select <?php echo $product['status']; ?>">
                                <option value="active" <?php echo $product['status'] == 'active' ? 'selected' : ''; ?>>Active
                                </option>
                                <option value="inactive" <?php echo $product['status'] == 'inactive' ? 'selected' : ''; ?>>
                                    Inactive
                                </option>
                                <option value="out of stock" <?php echo $product['status'] == 'out of stock' ? 'selected' : ''; ?>>Out of Stock
                                </option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">Save Change</button>
                        </form>
                        <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-secondary">View
                            Details</a>
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

    function validateForm(form) {
        const fileInput = form.querySelector('#image');
        if (fileInput.files.length > 0) {
            return validateFileSize(); // Assume validateFileSize.js returns true/false
        }
        return true; // Skip file validation if no file is selected
    }
</script>