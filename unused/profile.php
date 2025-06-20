<?php
session_start();
require_once 'config/database.php';
include 'includes/timeout.php';
include 'includes/login-required.php';

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get user data
$sql = "SELECT u.*, p.* FROM users u LEFT JOIN profiles p ON u.id = p.user_id WHERE u.id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
}

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bio = trim($_POST['bio']);
    $talent_category = trim($_POST['talent_category']);
    $contact_details = trim($_POST['contact_details']);
    $education = trim($_POST['education']);
    $skills = trim($_POST['skills']);
    $experience = trim($_POST['experience']);
    $achievements = trim($_POST['achievements']);
    $social_media = trim($_POST['social_media']);
    $portfolio_url = trim($_POST['portfolio_url']);
    $availability = trim($_POST['availability']);
    $preferred_contact = trim($_POST['preferred_contact']);

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $allowed = array('jpg', 'jpeg', 'png', 'gif');
        $filename = $_FILES['profile_picture']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);

        if (in_array(strtolower($filetype), $allowed)) {
            $new_filename = uniqid() . '.' . $filetype;
            $upload_path = 'assets/images/profiles/' . $new_filename;

            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                $profile_picture = $upload_path;
            } else {
                $error = "Error uploading file";
            }
        } else {
            $error = "Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.";
        }
    }

    if (empty($error)) {
        // Check if profile exists
        $sql = "SELECT id FROM profiles WHERE user_id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) > 0) {
                // Update existing profile
                $sql = "UPDATE profiles SET 
                        bio = ?, 
                        talent_category = ?, 
                        contact_details = ?,
                        education = ?,
                        skills = ?,
                        experience = ?,
                        achievements = ?,
                        social_media = ?,
                        portfolio_url = ?,
                        availability = ?,
                        preferred_contact = ?";
                if (isset($profile_picture)) {
                    $sql .= ", profile_picture = ?";
                }
                $sql .= " WHERE user_id = ?";

                if ($stmt = mysqli_prepare($conn, $sql)) {
                    if (isset($profile_picture)) {
                        mysqli_stmt_bind_param(
                            $stmt,
                            "ssssssssssssi",
                            $bio,
                            $talent_category,
                            $contact_details,
                            $education,
                            $skills,
                            $experience,
                            $achievements,
                            $social_media,
                            $portfolio_url,
                            $availability,
                            $preferred_contact,
                            $profile_picture,
                            $user_id
                        );
                    } else {
                        mysqli_stmt_bind_param(
                            $stmt,
                            "sssssssssssi",
                            $bio,
                            $talent_category,
                            $contact_details,
                            $education,
                            $skills,
                            $experience,
                            $achievements,
                            $social_media,
                            $portfolio_url,
                            $availability,
                            $preferred_contact,
                            $user_id
                        );
                    }

                    if (mysqli_stmt_execute($stmt)) {
                        $success = "Profile updated successfully";
                    } else {
                        $error = "Error updating profile";
                    }
                }
            } else {
                // Create new profile
                $sql = "INSERT INTO profiles (
                    user_id, bio, talent_category, contact_details,
                    education, skills, experience, achievements,
                    social_media, portfolio_url, availability, preferred_contact";
                if (isset($profile_picture)) {
                    $sql .= ", profile_picture";
                }
                $sql .= ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?";
                if (isset($profile_picture)) {
                    $sql .= ", ?";
                }
                $sql .= ")";

                if ($stmt = mysqli_prepare($conn, $sql)) {
                    if (isset($profile_picture)) {
                        mysqli_stmt_bind_param(
                            $stmt,
                            "issssssssssss",
                            $user_id,
                            $bio,
                            $talent_category,
                            $contact_details,
                            $education,
                            $skills,
                            $experience,
                            $achievements,
                            $social_media,
                            $portfolio_url,
                            $availability,
                            $preferred_contact,
                            $profile_picture
                        );
                    } else {
                        mysqli_stmt_bind_param(
                            $stmt,
                            "isssssssssss",
                            $user_id,
                            $bio,
                            $talent_category,
                            $contact_details,
                            $education,
                            $skills,
                            $experience,
                            $achievements,
                            $social_media,
                            $portfolio_url,
                            $availability,
                            $preferred_contact
                        );
                    }

                    if (mysqli_stmt_execute($stmt)) {
                        $success = "Profile created successfully";
                    } else {
                        $error = "Error creating profile";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Profile - MMU Talent Showcase</title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>

    <body>
        <?php include 'includes/header-inc.php'; ?>
        <?php include 'includes/navbar.php'; ?>

        <div class="container">
            <div class="card">
                <h2>My Profile</h2>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
                    enctype="multipart/form-data">
                    <div class="profile-section">
                        <h3>Basic Information</h3>
                        <div class="form-group">
                            <label>Profile Picture</label>
                            <?php if (!empty($user['profile_picture'])): ?>
                                <img src="<?php echo $user['profile_picture']; ?>" alt="Profile Picture"
                                    class="profile-preview">
                            <?php endif; ?>
                            <input type="file" name="profile_picture" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Bio</label>
                            <textarea name="bio" class="form-control" rows="4"
                                placeholder="Tell us about yourself..."><?php echo isset($user['bio']) ? $user['bio'] : ''; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Talent Category</label>
                            <select name="talent_category" class="form-control">
                                <option value="">Select Category</option>
                                <option value="Music" <?php echo (isset($user['talent_category']) && $user['talent_category'] == 'Music') ? 'selected' : ''; ?>>Music</option>
                                <option value="Art" <?php echo (isset($user['talent_category']) && $user['talent_category'] == 'Art') ? 'selected' : ''; ?>>Art</option>
                                <option value="Tech" <?php echo (isset($user['talent_category']) && $user['talent_category'] == 'Tech') ? 'selected' : ''; ?>>Tech</option>
                                <option value="Writing" <?php echo (isset($user['talent_category']) && $user['talent_category'] == 'Writing') ? 'selected' : ''; ?>>Writing</option>
                            </select>
                        </div>
                    </div>

                    <div class="profile-section">
                        <h3>Education & Skills</h3>
                        <div class="form-group">
                            <label>Education</label>
                            <textarea name="education" class="form-control" rows="3"
                                placeholder="Your educational background..."><?php echo isset($user['education']) ? $user['education'] : ''; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Skills</label>
                            <textarea name="skills" class="form-control" rows="3"
                                placeholder="List your skills..."><?php echo isset($user['skills']) ? $user['skills'] : ''; ?></textarea>
                        </div>
                    </div>

                    <div class="profile-section">
                        <h3>Experience & Achievements</h3>
                        <div class="form-group">
                            <label>Experience</label>
                            <textarea name="experience" class="form-control" rows="4"
                                placeholder="Describe your experience..."><?php echo isset($user['experience']) ? $user['experience'] : ''; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Achievements</label>
                            <textarea name="achievements" class="form-control" rows="3"
                                placeholder="List your achievements..."><?php echo isset($user['achievements']) ? $user['achievements'] : ''; ?></textarea>
                        </div>
                    </div>

                    <div class="profile-section">
                        <h3>Contact & Social Media</h3>
                        <div class="form-group">
                            <label>Contact Details</label>
                            <textarea name="contact_details" class="form-control" rows="3"
                                placeholder="Your contact information..."><?php echo isset($user['contact_details']) ? $user['contact_details'] : ''; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Social Media Links</label>
                            <textarea name="social_media" class="form-control" rows="3"
                                placeholder="Your social media profiles..."><?php echo isset($user['social_media']) ? $user['social_media'] : ''; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Portfolio URL</label>
                            <input type="url" name="portfolio_url" class="form-control"
                                placeholder="Your portfolio website..."
                                value="<?php echo isset($user['portfolio_url']) ? $user['portfolio_url'] : ''; ?>">
                        </div>
                    </div>

                    <div class="profile-section">
                        <h3>Availability & Preferences</h3>
                        <div class="form-group">
                            <label>Availability</label>
                            <textarea name="availability" class="form-control" rows="2"
                                placeholder="Your availability..."><?php echo isset($user['availability']) ? $user['availability'] : ''; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Preferred Contact Method</label>
                            <select name="preferred_contact" class="form-control">
                                <option value="">Select Preferred Contact Method</option>
                                <option value="Email" <?php echo (isset($user['preferred_contact']) && $user['preferred_contact'] == 'Email') ? 'selected' : ''; ?>>Email</option>
                                <option value="Phone" <?php echo (isset($user['preferred_contact']) && $user['preferred_contact'] == 'Phone') ? 'selected' : ''; ?>>Phone</option>
                                <option value="Social Media" <?php echo (isset($user['preferred_contact']) && $user['preferred_contact'] == 'Social Media') ? 'selected' : ''; ?>>Social Media
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <input type="submit" class="btn btn-primary" value="Update Profile">
                    </div>
                </form>
            </div>
        </div>

        <?php include 'includes/footer-inc.php'; ?>
    </body>

</html>