<?php
require_once 'includes/members-data.php';
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Group Members - MMU Talent Showcase</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="assets/css/about-us.css">
    </head>

    <body>
        <?php include 'includes/header-inc.php'; ?>
        <nav class="nav">
            <ul>
                <li><a href="index.php">Home</a></li>
            </ul>
        </nav>

        <main class="group-members">
            <h1>Group Members</h1>
            <table class="members-table">
                <tbody>
                    <?php foreach ($members as $member): ?>
                        <tr>
                            <td>
                                <figure>
                                    <img src="<?php echo htmlspecialchars($member['photo']); ?>"
                                        alt="<?php echo htmlspecialchars($member['name']); ?>" class="member-photo">
                                </figure>
                            </td>
                            <td>
                                <div class="member-info">
                                    <div class="member-details">
                                        <span class="member-name"><?php echo htmlspecialchars($member['name']); ?></span>
                                        <span class="member-id">Student ID:
                                            <?php echo htmlspecialchars($member['student_id']); ?></span>
                                        <span class="member-section">Section:
                                            <?php echo htmlspecialchars($member['section']); ?></span>
                                        <a href="mailto:<?php echo htmlspecialchars($member['email']); ?>"
                                            class="member-email"><?php echo htmlspecialchars($member['email']); ?></a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
        <?php include 'includes/footer-inc.php'; ?>
    </body>

</html>