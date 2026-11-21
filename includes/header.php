<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include $_SERVER['DOCUMENT_ROOT'] . '/BYTEBANK/includes/config.php'; // Absolute path

// Ensure database connection is established
if (!isset($conn)) {
    die('Database connection not established.');
}

$current_page = basename($_SERVER['PHP_SELF']); // Current page
$is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

// Fetch username if not already set
if (isset($_SESSION['user_email']) && !isset($_SESSION['username'])) {
    $stmt = $conn->prepare("SELECT username FROM users WHERE email = ?");
    $stmt->bind_param("s", $_SESSION['user_email']);
    $stmt->execute();
    $stmt->bind_result($username);
    $stmt->fetch();
    $stmt->close();
    $_SESSION['username'] = $username ?? 'Guest';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/BYTEBANK/">
    <link rel="stylesheet" href="css/header.css">
    <title>Header</title>
</head>
<body>
<header>
    <div class="navbar">
        <!-- Logo links to index.php -->
        <a href="index.php">
            <img src="images/bytebank_logo.png" alt="ByteBank Logo">
        </a>
        <nav>
            <ul>
                <!-- Standard navigation items -->
                <li><a href="about_us.php" class="<?= $current_page === 'about_us.php' ? 'active' : '' ?>">About Us</a></li>
                <li><a href="contact.php" class="<?= $current_page === 'contact.php' ? 'active' : '' ?>">Contact</a></li>
                <li><a href="location.php" class="<?= $current_page === 'location.php' ? 'active' : '' ?>">Location</a></li>
                
                <!-- Admin Dashboard only visible to admins -->
                <?php if ($is_admin): ?>
                    <li><a href="admin_dashboard.php" class="<?= $current_page === 'admin_dashboard.php' ? 'active' : '' ?>">Admin Dashboard</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="user-actions">
            <!-- Display user actions -->
            <?php if (isset($_SESSION['user_email'])): ?>
                <p class="welcome-message">Welcome, <?= htmlspecialchars($_SESSION['username']); ?></p>
                <a href="profile.php" class="profile-btn">Profile</a>
                <a href="logout.php" class="logout-btn">Logout</a>
            <?php else: ?>
                <a href="login.php" class="login-btn">Login</a>
            <?php endif; ?>
        </div>
    </div>
</header>
</body>
</html>
