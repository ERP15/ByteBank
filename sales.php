<?php
session_start();
include '../includes/config.php'; // Ensure this file contains your DB connection setup

if (!isset($_SESSION['user_email'])) {
    header('Location: ../login.php');
    exit();
}

// Initialize variables
$totalRevenue = 0;
$totalOrders = 0;

// Fetch total sales and orders from the database
$query = "SELECT SUM(total_amount) AS total_revenue, COUNT(*) AS total_orders FROM orders WHERE status = 'Completed'";
$result = $conn->query($query);

if ($result && $row = $result->fetch_assoc()) {
    $totalRevenue = $row['total_revenue'];
    $totalOrders = $row['total_orders'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Total Sales</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header>
    <div class="navbar">
        <a href="dashboard.php">
            <img src="../images/bytebank_logo.png" alt="ByteBank Logo" class="logo" style="width: 50px; height: 50px;">
        </a>
        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="product.php">Product</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="location.php">Location</a></li>
            </ul>
        </nav>
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>
</header>

<main>
<section class="hero">
    <h1>Total Sales</h1>
    <p>Here is the summary of total sales and orders processed.</p>
</section>

<section class="games-offered">
    <h2>Sales Data:</h2>
    <div class="games-list">
        <!-- Total Revenue -->
        <div class="game-item">
            <img src="../images/sales-icon.png" alt="Revenue">
            <p><strong>Total Revenue:</strong></p>
            <p>$<?php echo number_format($totalRevenue, 2); ?></p>
        </div>

        <!-- Total Orders -->
        <div class="game-item">
            <img src="../images/orders-icon.png" alt="Orders">
            <p><strong>Total Orders:</strong></p>
            <p><?php echo $totalOrders; ?></p>
        </div>
    </div>
</section>
</main>

<footer>
    <p>&copy; 2024 ByteBank Admin Dashboard. All rights reserved.</p>
</footer>
</body>
</html>
