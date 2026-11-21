<?php
session_start();
include("includes/config.php");
include("includes/header.php");

if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit();
}

// Determine whose profile to display
if (isset($_GET['user_email']) && $_SESSION['user_role'] === 'admin') {
    $user_email = $_GET['user_email']; // Admin is viewing/editing another user's profile
} else {
    $user_email = $_SESSION['user_email']; // Default to the logged-in user's profile
}

// Fetch user info from the database
$query = $conn->prepare("SELECT username, email, phone, profile_picture FROM users WHERE email = ?");
$query->bind_param("s", $user_email);
$query->execute();
$query->bind_result($username, $email, $phone, $profile_picture);
$query->fetch();
$query->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_dir = "../images/";
    $new_profile_picture = $profile_picture; // Default to current profile picture

    if (!empty($_FILES["profile_picture"]["name"])) {
        // Generate a unique name for the new file
        $new_profile_picture = $target_dir . time() . '_' . basename($_FILES["profile_picture"]["name"]);

        // Ensure the target directory exists
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Create the directory if it doesn't exist
        }

        // Move the uploaded file to the target directory
        if (!move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $new_profile_picture)) {
            echo "Failed to upload profile picture.";
            $new_profile_picture = $profile_picture; // Revert to the current profile picture
        }
    }

    $new_username = htmlspecialchars(trim($_POST['username']));
    $new_email = htmlspecialchars(trim($_POST['email']));
    $new_phone = htmlspecialchars(trim($_POST['phone']));

    // Update the database
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, phone = ?, profile_picture = ? WHERE email = ?");
    $stmt->bind_param("sssss", $new_username, $new_email, $new_phone, $new_profile_picture, $user_email);

    if ($stmt->execute()) {
        if ($user_email === $_SESSION['user_email']) {
            $_SESSION['user_email'] = $new_email; // Update session email if changed
        }
        header("Location: profile.php?user_email=" . urlencode($new_email)); // Redirect to updated profile
        exit();
    } else {
        echo "Error updating profile.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - ByteBank</title>
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>
<main>
    <section class="profile-section">
        <div class="profile-container">
            <div class="profile-icon">
                <!-- Add a query string to the image URL to force reload -->
                <img src="<?= !empty($profile_picture) ? $profile_picture . '?t=' . time() : '../images/default-profile.png'; ?>" alt="Profile Picture">
            </div>
            <div class="profile-info">
                <h2><?= strtoupper($username); ?></h2>
                <p>User Email: <?= htmlspecialchars($email); ?></p>
            </div>
        </div>

        <div class="details-container">
            <h3>Details</h3>
            <p><strong>Username:</strong> <?= htmlspecialchars($username); ?></p>
            <p><strong>Email Address:</strong> <?= htmlspecialchars($email); ?></p>
            <p><strong>Phone Number:</strong> <?= htmlspecialchars($phone); ?></p>
        </div>

        <!-- Only allow editing if the logged-in user is admin or editing their own profile -->
        <?php if ($_SESSION['user_role'] === 'admin' || $user_email === $_SESSION['user_email']): ?>
            <button class="edit-btn" onclick="document.getElementById('edit-form').style.display='block'">Edit</button>
        <?php endif; ?>
    </section>

    <section id="edit-form" class="edit-form-section" style="display: none;">
        <h3>Edit Mode</h3>
        <form action="profile.php?user_email=<?= urlencode($user_email); ?>" method="POST" enctype="multipart/form-data">
            <label for="profile_picture">Profile Picture:</label>
            <input type="file" id="profile_picture" name="profile_picture">

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($username); ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email); ?>" required>

            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($phone); ?>" required>

            <button type="submit">Save Changes</button>
        </form>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
</body>
</html>
