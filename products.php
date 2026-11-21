<?php
session_start();
include 'includes/config.php'; // Database connection
include 'includes/header.php';
if (!isset($_SESSION['user_email'])) {
    header('Location: ../login.php');
    exit();
}

// Handle CRUD operations for Products
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        // Add a new product
        $game_id = $_POST['game_id'];
        $product_name = $_POST['product_name'];
        $product_price = $_POST['product_price'];
        $product_bonus = $_POST['product_bonus'];
        $is_popular = isset($_POST['is_popular']) ? 1 : 0;
        $rewards = $_POST['rewards'];

        // Insert into the main products table
        $stmt = $conn->prepare("INSERT INTO products (game_id, product_name, product_price, product_bonus, is_popular, rewards) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isdsid", $game_id, $product_name, $product_price, $product_bonus, $is_popular, $rewards);
        if ($stmt->execute()) {
            echo "<p style='color:white;'>Product added successfully.</p>";
        } else {
            echo "<p style='color:red;'>Error adding product: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } elseif (isset($_POST['update_product'])) {
        // Update an existing product
        $product_id = $_POST['product_id'];
        $game_id = $_POST['game_id'];
        $product_name = $_POST['product_name'];
        $product_price = $_POST['product_price'];
        $product_bonus = $_POST['product_bonus'];
        $is_popular = isset($_POST['is_popular']) ? 1 : 0;
        $rewards = $_POST['rewards'];

        // Update in the main products table
        $stmt = $conn->prepare("UPDATE products SET game_id=?, product_name=?, product_price=?, product_bonus=?, is_popular=?, rewards=? WHERE product_id=?");
        $stmt->bind_param("isdsidi", $game_id, $product_name, $product_price, $product_bonus, $is_popular, $rewards, $product_id);
        if ($stmt->execute()) {
            echo "<p style='color:green;'>Product updated successfully.</p>";
        } else {
            echo "<p style='color:red;'>Error updating product: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } elseif (isset($_POST['delete_product'])) {
        // Delete a product
        $product_id = $_POST['product_id'];

        // Delete from the main products table
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id=?");
        $stmt->bind_param("i", $product_id);
        if ($stmt->execute()) {
            echo "<p style='color:green;'>Product deleted successfully.</p>";
        } else {
            echo "<p style='color:red;'>Error deleting product: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}

// Fetch products
$products = $conn->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <link rel="stylesheet" href="css/admin_dashboard.css">
</head>
<body>
<header>
   
</header>

<main>
<section class="dashboard-intro">
    <h1>Product Management</h1>
    <p>Manage your product inventory here.</p>
</section>

<section class="management-form">
    <form action="products.php" method="POST">
        <input type="text" name="product_name" placeholder="Product Name" required>
        <input type="number" name="game_id" placeholder="Game ID" required>
        <input type="number" step="0.01" name="product_price" placeholder="Product Price" required>
        <input type="text" name="product_bonus" placeholder="Product Bonus">
        <label>
            <input type="checkbox" name="is_popular"> Is Popular
        </label>
        <input type="number" step="0.01" name="rewards" placeholder="Rewards">
        <button type="submit" name="add_product">Add Product</button>
    </form>
</section>

<section class="management-table">
    <h2>Products List</h2>
    <form action="products.php" method="POST">
        <table>
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Game ID</th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Bonus</th>
                    <th>Is Popular</th>
                    <th>Rewards</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = $products->fetch_assoc()): ?>
                <tr>
                    <td>
                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                        <?php echo $product['product_id']; ?>
                    </td>
                    <td><input type="number" name="game_id" value="<?php echo $product['game_id']; ?>" required></td>
                    <td><input type="text" name="product_name" value="<?php echo $product['product_name']; ?>" required></td>
                    <td><input type="number" step="0.01" name="product_price" value="<?php echo $product['product_price']; ?>" required></td>
                    <td><input type="text" name="product_bonus" value="<?php echo $product['product_bonus']; ?>"></td>
                    <td>
                        <input type="checkbox" name="is_popular" <?php echo $product['is_popular'] ? 'checked' : ''; ?>>
                    </td>
                    <td><input type="number" step="0.01" name="rewards" value="<?php echo $product['rewards']; ?>"></td>
                    <td>
                        <button class="save-btn" type="submit" name="update_product">Update</button>
                        <form action="products.php" method="POST" style="display: inline;">
                            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                            <button class="delete-btn" type="submit" name="delete_product">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </form>
</section>
</main>

<footer>
    <p>&copy; 2024 ByteBank. All rights reserved.</p>
</footer>
</body>
</html>

