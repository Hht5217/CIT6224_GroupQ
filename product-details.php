<?php
session_start();
require_once 'config/database.php';
include_once 'includes/product-categories.php';
include 'includes/timeout.php';

// Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = (int) $_GET['id'];

// Get product info
$sql = "SELECT p.*, u.username as seller_name 
        FROM products p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$product) {
    header("Location: products.php");
    exit();
}

// Handle product update (owner only)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_product') {
    if (!isset($_SESSION['user_id']) || $product['user_id'] != $_SESSION['user_id']) {
        header("Location: login.php");
        exit();
    }
    $title = trim($_POST['title']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    if (empty($title) || !isset($product_categories[$category]) || $price <= 0) {
        $error = "Title, valid category, and positive price are required.";
    } else {
        $stmt = $conn->prepare("UPDATE products SET title = ?, category = ?, description = ?, price = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sssddi", $title, $category, $description, $price, $product_id, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $success = "Product updated successfully.";
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        } else {
            $error = "Error updating product.";
        }
        $stmt->close();
    }
}

// Handle product deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_product') {
    if (!isset($_SESSION['user_id']) || $product['user_id'] != $_SESSION['user_id']) {
        header("Location: login.php");
        exit();
    }
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $product_id, $_SESSION['user_id']);
    if ($stmt->execute()) {
        $success = "Product deleted successfully.";
        header("Location: products.php");
        exit();
    } else {
        $error = "Error deleting product.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($product['title']); ?> - Product Details</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="assets/css/talent-details.css">
        <link rel="stylesheet" href="assets/css/products.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>

    <body>
        <?php include 'includes/header-inc.php'; ?>
        <?php include 'includes/navbar.php'; ?>

        <div class="container">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <a href="products.php" class="btn btn-secondary">Back to Products</a>

            <div class="talent-profile-container">
                <div class="profile-header">
                    <div class="profile-info">
                        <h1><?php echo htmlspecialchars($product['title']); ?></h1>
                        <p class="talent-category">
                            <i class="fas fa-star"></i>
                            <?php echo isset($product_categories[$product['category']]) ? htmlspecialchars($product_categories[$product['category']]) : 'Not specified'; ?>
                        </p>
                        <p>By: <?php echo htmlspecialchars($product['seller_name']); ?></p>
                        <a href="view-profile.php?id=<?php echo $product['user_id']; ?>" class="btn btn-primary">
                            <i class="fas fa-user"></i> View Profile
                        </a>
                        <?php if (isset($_SESSION['user_id']) && $product['user_id'] == $_SESSION['user_id']): ?>
                            <button class="btn btn-primary" onclick="showEditForm()">
                                <i class="fas fa-edit"></i> Edit Product
                            </button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="delete_product">
                                <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this product?');">
                                    <i class="fas fa-trash"></i> Delete Product
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Edit Product Modal -->
                <?php if (isset($_SESSION['user_id']) && $product['user_id'] == $_SESSION['user_id']): ?>
                    <div id="editForm" class="edit-modal" style="display: none;">
                        <div class="edit-modal-content">
                            <form id="editProductForm" method="POST">
                                <input type="hidden" name="action" value="update_product">
                                <div class="form-group">
                                    <label for="title">Title</label>
                                    <input type="text" name="title" id="title"
                                        value="<?php echo htmlspecialchars($product['title']); ?>" required maxlength="255">
                                </div>
                                <div class="form-group">
                                    <label for="category">Category</label>
                                    <select name="category" id="category" required>
                                        <?php showProductCategoryOptions($product['category']); ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea name="description" id="description"
                                        rows="5"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="price">Price ($)</label>
                                    <input type="number" name="price" id="price"
                                        value="<?php echo number_format($product['price'], 2); ?>" step="0.01" min="0.01"
                                        required>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                    <button type="button" class="btn btn-secondary" onclick="hideEditForm()">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Profile Content -->
                <div class="profile-content">
                    <!-- Image Preview Section -->
                    <div class="profile-section">
                        <h2><i class="fas fa-image"></i> Product Image</h2>
                        <div class="section-content">
                            <?php if (!empty($product['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>"
                                    alt="<?php echo htmlspecialchars($product['title']); ?>"
                                    style="max-width:100%;max-height:350px;">
                            <?php else: ?>
                                <img src="assets/images/placeholder.jpg" alt="No image available"
                                    style="max-width:100%;max-height:350px;">
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Description Section -->
                    <div class="profile-section">
                        <h2><i class="fas fa-info-circle"></i> Description</h2>
                        <div class="section-content">
                            <p><?php echo nl2br(htmlspecialchars($product['description'] ?? 'No description available.')); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Buy Product Section -->
                    <div class="profile-section">
                        <h2><i class="fas fa-shopping-cart"></i> Buy Product</h2>
                        <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                        <div class="section-content">
                            <?php if ($product['status'] !== 'active'): ?>
                                <p class="alert alert-warning">This product is currently out of stock.</p>
                            <?php elseif (!isset($_SESSION['user_id'])): ?>
                                <a href="login.php" class="btn btn-secondary">Login to Purchase</a>
                            <?php else: ?>
                                <form action="add-to-cart.php" method="post" class="add-to-cart-form">
                                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                    <input type="hidden" name="action" value="add_to_cart">
                                    <div class="form-group">
                                        <label for="quantity">Quantity</label>
                                        <input type="number" name="quantity" id="quantity" value="1" min="1"
                                            class="form-control quantity-input">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Add to Cart</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'includes/footer-inc.php'; ?>

        <script src="assets/js/validateFileSize.js"></script>
        <script>
            function showEditForm() {
                document.getElementById('editForm').style.display = 'flex';
            }
            function hideEditForm() {
                document.getElementById('editForm').style.display = 'none';
                formIsDirty = false;
            }
            let formIsDirty = false;
            const editForm = document.getElementById('editProductForm');
            if (editForm) {
                editForm.querySelectorAll('input, textarea, select').forEach(input => {
                    input.addEventListener('change', () => formIsDirty = true);
                    input.addEventListener('input', () => formIsDirty = true);
                });
                editForm.addEventListener('submit', () => formIsDirty = false);
            }
            window.addEventListener('beforeunload', (e) => {
                if (formIsDirty) {
                    e.preventDefault();
                    e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                }
            });
        </script>