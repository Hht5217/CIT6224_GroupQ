<?php
session_start();
require_once 'config/database.php';
include 'includes/timeout.php';

// Get all FAQs from the database
$sql = "SELECT * FROM faq ORDER BY created_at DESC";
$faqs = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>FAQ - MMU Talent Showcase</title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>

    <body>
        <?php include 'includes/header-inc.php'; ?>

        <?php include 'includes/navbar.php'; ?>

        <div class="container">
            <div class="card">
                <h2>Frequently Asked Questions</h2>
                <div class="faq-list">
                    <?php while ($faq = mysqli_fetch_assoc($faqs)): ?>
                        <div class="faq-item">
                            <h3 class="faq-question"><?php echo htmlspecialchars($faq['question']); ?></h3>
                            <div class="faq-answer">
                                <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
        <?php include 'includes/footer-inc.php'; ?>
    </body>

</html>