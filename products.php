<?php
session_start();
require_once 'config/database.php';
include_once 'includes/product-categories.php';
include 'includes/timeout.php';

// Get search parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';

// Build query
$sql = "SELECT p.*, u.username as seller_name 
        FROM products p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.status IN ('active', 'out of stock')";

$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (p.title LIKE ? OR p.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if (!empty($category)) {
    $sql .= " AND p.category = ?";
    $params[] = $category;
    $types .= "s";
}

if (!empty($status)) {
    $sql .= " AND p.status = ?";
    $params[] = $status === 'in_stock' ? 'active' : 'out of stock';
    $types .= "s";
}

// Add sorting
switch ($sort) {
    case 'price_low':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'oldest':
        $sql .= " ORDER BY p.created_at ASC";
        break;
    default: // newest
        $sql .= " ORDER BY p.created_at DESC";
}

// Prepare and execute query
if ($stmt = mysqli_prepare($conn, $sql)) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Products - MMU Talent Showcase</title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>

    <body>
        <?php include 'includes/header-inc.php'; ?>
        <?php include 'includes/navbar.php'; ?>

        <div class="container">
            <div class="products-header">
                <h2>Products</h2>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="search-form">
                    <div class="search-filters">
                        <input type="text" name="search" placeholder="Search products..."
                            value="<?php echo htmlspecialchars($search); ?>" class="form-control">

                        <select name="category" class="form-control">
                            <option value="">All Categories</option>
                            <?php showProductCategoryOptions($category); ?>
                        </select>

                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="in_stock" <?php echo $status === 'in_stock' ? 'selected' : ''; ?>>In Stock
                            </option>
                            <option value="out_of_stock" <?php echo $status === 'out_of_stock' ? 'selected' : ''; ?>>Out
                                of Stock</option>
                        </select>

                        <select name="sort" class="form-control">
                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First
                            </option>
                            <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First
                            </option>
                            <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to
                                High</option>
                            <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High
                                to Low</option>
                        </select>

                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </form>
            </div>
            <?php if (isset($_SESSION['warning'])): ?>
                <div class="alert alert-warning">
                    <?php
                    echo $_SESSION['warning'];
                    unset($_SESSION['warning']);
                    ?>
                </div>
            <?php elseif (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="products-grid">
                <?php if (empty($products)): ?>
                    <div class="no-products">
                        <p>No products found</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php if (!empty($product['image_url'])): ?>
                                    <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['title']; ?>">
                                <?php else: ?>
                                    <img src="assets/images/placeholder.jpg" alt="No image available">
                                <?php endif; ?>
                            </div>
                            <div class="product-details">
                                <h3><?php echo $product['title']; ?></h3>
                                <p class="seller">Seller: <?php echo $product['seller_name']; ?></p>
                                <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                                <p class="category">
                                    <?php echo isset($product_categories[$product['category']]) ? htmlspecialchars($product_categories[$product['category']]) : 'Not specified'; ?>
                                </p>
                                <p class="description"><?php echo substr($product['description'], 0, 100) . '...'; ?></p>

                                <?php if (isset($_SESSION['user_id']) && $product['status'] === 'active'): ?>
                                    <form action="add-to-cart.php" method="post" class="add-to-cart-form">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <input type="number" name="quantity" value="1" min="1" class="form-control quantity-input">
                                        <button type="submit" class="btn btn-primary">Add to Cart</button>
                                    </form>
                                <?php elseif (isset($_SESSION['user_id']) && $product['status'] !== 'active'): ?>
                                    <p class="out-of-stock">Out of Stock</p>
                                <?php elseif (!isset($_SESSION['user_id'])): ?>
                                    <a href="login.php" class="btn btn-secondary">Login to Purchase</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php include 'includes/footer-inc.php'; ?>
    </body>

</html>