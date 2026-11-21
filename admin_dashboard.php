<?php
include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/admin_dashboard.css">
</head>
<body>
<header>
   
</header>

<main>
    <!-- Dashboard Introduction -->
    <section class="dashboard-intro">
        <h1>Admin Dashboard</h1>
        <p>Manage ByteBank efficiently with the following tools:</p>
    </section>

    <!-- Management Sections -->
    <section class="management-sections">
        <h2>Management Sections:</h2>
        <div class="management-grid">
            <!-- Users Management -->
            <a href="users.php" class="management-card">
       
                <h3>Users Management</h3>
                <p>Manage user accounts and roles.</p>
            </a>

            <!-- Products Management -->
            <a href="products.php" class="management-card">
         
                <h3>Products Management</h3>
                <p>Manage product inventory.</p>
            </a>

        
            <!-- Purchase Management -->
            <a href="purchase.php" class="management-card">
               
                <h3>Purchase Management</h3>
                <p>Analyze sales and performance data.</p>
            </a>
        </div>
    </section>
</main>

<footer>
    <p>&copy; 2024 ByteBank. All rights reserved.</p>
</footer>
</body>
</html>
