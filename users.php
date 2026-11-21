<?php
session_start();
include 'includes/config.php'; // Include database connection

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_email']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php'); // Redirect if not logged in or not an admin
    exit();
}

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Handle Role Update or Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_role'])) {
        $user_id = $_POST['user_id'];
        $new_role = $_POST['new_role'];

        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("si", $new_role, $user_id);

        if ($stmt->execute()) {
            header("Location: users.php"); // Refresh the page after updating the role
            exit();
        } else {
            die("Error updating role: " . $stmt->error);
        }
        $stmt->close();
    }

    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];

        // Debugging: Ensure user_id is passed
        if (empty($user_id)) {
            die("Error: user_id is missing.");
        }

        // Delete the user from the database
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            header("Location: users.php"); // Refresh the page after deleting the user
            exit();
        } else {
            die("Error deleting user: " . $stmt->error);
        }
        $stmt->close();
    }
}

// Fetch all users
$users = $conn->query("SELECT user_id, username, email, role, phone, profile_picture, created_at FROM users");
if (!$users) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management</title>
    <link rel="stylesheet" href="css/admin_dashboard.css">
</head>
<body>
<header>
    <div class="navbar">
        <a href="admin_dashboard.php">
            <img src="images/bytebank_logo.png" alt="ByteBank Logo" class="logo" style="width: 50px; height: 50px;">
        </a>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="users.php" class="active">Users</a></li>
            </ul>
        </nav>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</header>

<main>
    <section class="dashboard-intro">
        <h1>Users Management</h1>
        <p>View and manage user accounts.</p>
    </section>

    <section class="management-table">
        <h2>All Users</h2>
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Phone</th>
                    <th>Profile Picture</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['user_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td>
                        <form action="users.php" method="POST" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                            <select name="new_role" class="role-select">
                                <option value="user" <?php echo $row['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                <option value="admin" <?php echo $row['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="moderator" <?php echo $row['role'] === 'moderator' ? 'selected' : ''; ?>>Moderator</option>
                            </select>
                            <button type="submit" name="update_role" class="update-btn">Update Role</button>
                        </form>
                    </td>
                    <td><?php echo htmlspecialchars($row['phone'] ?: 'N/A'); ?></td>
                    <td>
                        <?php if ($row['profile_picture']): ?>
                            <img src="uploads/<?php echo $row['profile_picture']; ?>" alt="Profile" class="profile-pic">
                        <?php else: ?>
                            <span>No Picture</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></td>
                    <td class="table-actions">
                        <a href="profile.php?user_email=<?php echo urlencode($row['email']); ?>" class="edit-btn">Edit Profile</a>
                        <form action="users.php" method="POST" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                            <button type="submit" name="delete_user" class="delete-btn">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>
</main>

<footer>
    <p>&copy; 2024 ByteBank. All rights reserved.</p>
</footer>
</body>
</html>
