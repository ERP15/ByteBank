<?php
session_start();
include 'includes/config.php'; // Database connection
include 'includes/header.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';  // Include PHPMailer's autoloader

if (!isset($_SESSION['user_email'])) {
    header('Location: ../login.php');
    exit();
}

// Email sending function
function sendEmail($recipientEmail, $subject, $message) {
    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUsername;
        $mail->Password = $smtpPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;           // Enable TLS encryption
        $mail->Port       = 587;                                      // TCP port to connect to

        //Recipients
        $mail->setFrom($smtpUsername, 'ByteBank Admin');
        $mail->addAddress($recipientEmail);                            // Add recipient's email

        // Content
        $mail->isHTML(true);                                          // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Handle CRUD operations for Purchases
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_purchase'])) {
        $purchase_id = $_POST['purchase_id'];

        // Get the email of the purchaser for the email notification
        $stmt = $conn->prepare("SELECT purchase_email FROM purchases WHERE purchase_id = ?");
        $stmt->bind_param("i", $purchase_id);
        $stmt->execute();
        $stmt->bind_result($purchase_email);
        $stmt->fetch();
        $stmt->close();

        // Delete the purchase
        $stmt = $conn->prepare("DELETE FROM purchases WHERE purchase_id = ?");
        $stmt->bind_param("i", $purchase_id);
        $stmt->execute();
        $stmt->close();

        // Send email notification
        $subject = "Your Purchase Has Been Deleted";
        $message = "Dear Customer, <br>Your purchase with ID #$purchase_id has been deleted from our system.";
        sendEmail($purchase_email, $subject, $message);
    } elseif (isset($_POST['update_status'])) {
        $purchase_id = $_POST['purchase_id'];
        $new_status = $_POST['status'];

        // Get the email of the purchaser for the email notification
        $stmt = $conn->prepare("SELECT purchase_email FROM purchases WHERE purchase_id = ?");
        $stmt->bind_param("i", $purchase_id);
        $stmt->execute();
        $stmt->bind_result($purchase_email);
        $stmt->fetch();
        $stmt->close();

        // Update the purchase status
        $stmt = $conn->prepare("UPDATE purchases SET status = ? WHERE purchase_id = ?");
        $stmt->bind_param("si", $new_status, $purchase_id);
        $stmt->execute();
        $stmt->close();
// Send email notification
$subject = "Your Purchase Status Has Been Updated";
$message = "
    Dear Valued Customer, <br><br>

    We hope this email finds you well. We are writing to inform you that your purchase with ID #$purchase_id has been successfully updated. The new status of your purchase is: <strong>$new_status</strong>.<br><br>

    If you have any questions or need further assistance regarding your purchase, please do not hesitate to contact our support team. We are always here to help.<br><br>

    Thank you for choosing ByteBank. We truly appreciate your trust in us and look forward to serving you again in the future.<br><br>

    Best regards,<br>
    The ByteBank Team
";

sendEmail($purchase_email, $subject, $message);

    }
}

// Fetch purchases
$purchases = $conn->query("SELECT * FROM purchases");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Management</title>
    <link rel="stylesheet" href="css/admin_dashboard.css">
</head>
<body>
<header>
    
</header>

<main>
    <section class="dashboard-intro">
        <h1>Purchase Management</h1>
        <p>View and manage all purchases.</p>
    </section>

    <section class="management-table">
        <h2>Purchases List</h2>
        <table>
            <thead>
                <tr>
                    <th>Purchase ID</th>
                    <th>User ID</th>
                    <th>Game ID</th>
                    <th>Product ID</th>
                    <th>Purchase Amount</th>
                    <th>Purchase Date</th>
                    <th>Purchase Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($purchase = $purchases->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $purchase['purchase_id']; ?></td>
                    <td><?php echo $purchase['user_id']; ?></td>
                    <td><?php echo $purchase['game_id']; ?></td>
                    <td><?php echo $purchase['product_id']; ?></td>
                    <td><?php echo $purchase['purchase_amount']; ?></td>
                    <td><?php echo $purchase['purchase_date']; ?></td>
                    <td><?php echo $purchase['purchase_email']; ?></td>
                    <td>
                        <form action="purchase.php" method="POST" style="display:inline;">
                            <input type="hidden" name="purchase_id" value="<?php echo $purchase['purchase_id']; ?>">
                            <select name="status" class="status-select">
                                <option value="Pending" <?php if ($purchase['status'] === 'Pending') echo 'selected'; ?>>Pending</option>
                                <option value="Completed" <?php if ($purchase['status'] === 'Completed') echo 'selected'; ?>>Completed</option>
                                <option value="Cancelled" <?php if ($purchase['status'] === 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                            </select>
                            <button type="submit" name="update_status" class="update-btn">Update</button>
                        </form>
                    </td>
                    <td class="table-actions">
                        <form action="purchase.php" method="POST" style="display:inline;">
                            <input type="hidden" name="purchase_id" value="<?php echo $purchase['purchase_id']; ?>">
                            <button type="submit" name="delete_purchase" class="delete-btn">Delete</button>
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
