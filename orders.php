<?php
session_start();
include 'includes/config.php'; // Database connection

// Load PHPMailer
require __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if the admin is logged in
if (!isset($_SESSION['user_email'])) {
    header('Location: ../login.php');
    exit();
}

// Handle CRUD operations for Orders
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_order'])) {
        $customer_name = $_POST['customer_name'];
        $order_date = $_POST['order_date'];
        $total_amount = $_POST['total_amount'];
        $status = $_POST['status'];

        $stmt = $conn->prepare("INSERT INTO orders (customer_name, order_date, total_amount, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $customer_name, $order_date, $total_amount, $status);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['update_order'])) {
        $order_id = $_POST['order_id'];
        $status = $_POST['status'];

        // Fetch order details for email notification
        $stmt = $conn->prepare("SELECT customer_name, order_date, total_amount, status, customer_email FROM orders WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->bind_result($customer_name, $order_date, $total_amount, $current_status, $customer_email);
        $stmt->fetch();
        $stmt->close();

        // Update the order status
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $order_id);
        if ($stmt->execute()) {
            echo "<p style='color:green; text-align:center;'>Order updated successfully!</p>";

            // Send email notification about the status update
            $mail = new PHPMailer(true);

            try {
                // Email server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = $smtpUsername;
                $mail->Password = $smtpPassword;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;

                // Recipients
                $mail->setFrom($smtpUsername, 'ByteBank Admin');
                $mail->addAddress($customer_email); // Customer's email address

                // Email content
                $mail->isHTML(true);
                $mail->Subject = "Order Status Updated - ByteBank";
                $mail->Body = "
                    <h1>Order Status Update</h1>
                    <p>Dear <strong>{$customer_name}</strong>,</p>
                    <p>The status of your order has been updated. Please find the details below:</p>
                    <ul>
                        <li>Order ID: {$order_id}</li>
                        <li>Order Date: {$order_date}</li>
                        <li>Total Amount: ₱" . number_format($total_amount, 2) . "</li>
                        <li>Updated Status: <strong>" . ucfirst($status) . "</strong></li>
                    </ul>
                    <p>If you have any questions, please contact us.</p>
                    <p>Best regards,<br>The ByteBank Team</p>
                ";

                $mail->send();
                echo "<p style='color:green; text-align:center;'>Email notification sent to {$customer_email}.</p>";
            } catch (Exception $e) {
                echo "<p style='color:red; text-align:center;'>Email could not be sent. Mailer Error: {$mail->ErrorInfo}</p>";
            }
        } else {
            echo "<p style='color:red; text-align:center;'>Failed to update the order status.</p>";
        }
        $stmt->close();
    } elseif (isset($_POST['delete_order'])) {
        $order_id = $_POST['order_id'];

        $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch orders
$orders = $conn->query("SELECT * FROM orders");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management</title>
    <link rel="stylesheet" href="css/admin_dashboard.css">
</head>
<body>
<header>
    <div class="navbar">
        <a href="admin_dashboard.php">
            <img src="../images/bytebank_logo.png" alt="ByteBank Logo" class="logo" style="width: 50px; height: 50px;">
        </a>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="orders.php" class="active">Orders</a></li>
                <li><a href="customers.php">Customers</a></li>
            </ul>
        </nav>
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>
</header>

<main>
    <section class="dashboard-intro">
        <h1>Order Management</h1>
        <p>View and manage all orders.</p>
    </section>

    <section class="management-form">
        <form action="orders.php" method="POST">
            <input type="text" name="customer_name" placeholder="Customer Name" required>
            <input type="email" name="customer_email" placeholder="Customer Email" required>
            <input type="date" name="order_date" required>
            <input type="number" step="0.01" name="total_amount" placeholder="Total Amount" required>
            <select name="status" required>
                <option value="Pending">Pending</option>
                <option value="Completed">Completed</option>
                <option value="Cancelled">Cancelled</option>
            </select>
            <button type="submit" name="create_order">Create Order</button>
        </form>
    </section>

    <section class="management-table">
        <h2>Orders List</h2>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Order Date</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $orders->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $order['id']; ?></td>
                    <td><?php echo $order['customer_name']; ?></td>
                    <td><?php echo $order['order_date']; ?></td>
                    <td><?php echo $order['total_amount']; ?></td>
                    <td><?php echo $order['status']; ?></td>
                    <td class="table-actions">
                        <form action="orders.php" method="POST" style="display:inline;">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="status">
                                <option value="Pending" <?php if ($order['status'] === 'Pending') echo 'selected'; ?>>Pending</option>
                                <option value="Completed" <?php if ($order['status'] === 'Completed') echo 'selected'; ?>>Completed</option>
                                <option value="Cancelled" <?php if ($order['status'] === 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                            </select>
                            <button type="submit" name="update_order">Update</button>
                        </form>
                        <form action="orders.php" method="POST" style="display:inline;">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <button type="submit" name="delete_order" class="delete-btn">Delete</button>
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
