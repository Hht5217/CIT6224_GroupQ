<?php
session_start();
require_once 'config/database.php';
include 'includes/timeout.php';
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>MMU Talent Showcase Portal</title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>

    <body>
        <?php include 'includes/header-inc.php'; ?>
        <?php include 'includes/navbar.php'; ?>

        <div class="container">
            <?php if (!empty($_SESSION['login_success'])): ?>
                <div class="alert alert-success">Login successful!</div>
                <?php unset($_SESSION['login_success']); ?>
            <?php endif; ?>
            <div class="welcome-section">
                <h2>Welcome to MMU Talent Showcase</h2>
                <p>Discover and showcase your talents with the MMU community.</p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <h3>Talent Showcase</h3>
                    <p>Share your talents with the MMU community and get discovered.</p>
                    <a href="talent-catalogue.php" class="btn btn-primary">Browse Talents</a>
                </div>

                <div class="feature-card">
                    <h3>Products & Services</h3>
                    <p>Find and purchase products or services from talented MMU students.</p>
                    <a href="products.php" class="btn btn-primary">View Products</a>
                </div>

                <div class="feature-card">
                    <h3>Resources</h3>
                    <p>Access helpful resources to develop your talents.</p>
                    <a href="resources.php" class="btn btn-primary">Explore Resources</a>
                </div>
            </div>
        </div>

        <?php include 'includes/footer-inc.php'; ?>
    </body>

</html>