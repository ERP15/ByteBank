<?php
session_start();
include("../includes/config.php");
include("../includes/header.php");



         // Send confirmation email using PHPMailer
         require __DIR__ . '/../vendor/autoload.php';
         use PHPMailer\PHPMailer\PHPMailer;
         use PHPMailer\PHPMailer\Exception;
         
         
if (!isset($_SESSION['user_email'])) {
    header('Location: ../user/login.php');
    exit();
}

// Function to filter bad words
function filter_bad_words($text) {
    $bad_words = [
        'fuck', 'tangina', 'gago', 'putangina', 'bitch', 'motherfucker', 'tarantado', 'nigga', 'tite', 'asshole', 'pakyu', 'shit',
        'damn', 'puta', 'shet', 'tarantado', 'unggoy', 'pakshet', 'douche', 'dick', 'cock', 'pussy', 'fucker', 'cunt', 'puta',
        'boob', 'boobs', 'ballsack', 'whore', 'slut', 'penis', 'vagina', 'bastos', 'kantot', 'ulol', 'burat', 'bobo', 'inutil',
        'hayop', 'idiot', 'imbecile', 'moron', 'lowlife', 'peenoise', 'bakla', 'lame', 'siraulo', 'kupal', 'suck', 'dumbass',
        'fuckface', 'shithead', 'arsehole', 'butthole', 'asswipe', 'cum', 'scumbag', 'twat', 'smeghead', 'pisser', 'crap',
        'jerk', 'freak', 'noob', 'loser', 'luser', 'lola mo', 'loko', 'panget', 'retard', 'spaz', 'stupid', 'ugok', 'kiki',
        'pokpok', 'pakyu', 'tanga', 'fart', 'kabayo', 'unggoy', 'hudas', 'lecheng', 'kikay', 'tangek', 'abnoy', 'hinayupak',
        'buisit', 'shitface', 'fuckwit', 'kurimaw', 'loko', 'salot', 'tarugo', 'lechon', 'lecheng ina', 'lintik', 'unggoy'
    ];
    $pattern = "/\b(" . implode('|', array_map('preg_quote', $bad_words)) . ")\b/i";
    $replacement = str_repeat('*', 3); // Mask with asterisks
    return preg_replace($pattern, $replacement, $text);
}

// Handle purchase and review submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $game_id = 5; 
    $user_email = $_SESSION['user_email'];

    // Verify the user's email
    if (empty($user_email)) {
        echo "<p style='color:red; text-align:center;'>Error: User not logged in. Please log in to proceed.</p>";
        exit();
    }

    // Fetch the user_id from the database
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $stmt->bind_result($user_id);
    $stmt->fetch();
    $stmt->close();

    if (!$user_id) {
        echo "<p style='color:red; text-align:center;'>Error: User not found!</p>";
        exit();
    }

    // Handle purchase submission
    if (isset($_POST['product_id'])) {
        $product_id = intval($_POST['product_id']);

        // Fetch product price and name
        $stmt = $conn->prepare("SELECT product_price, product_name FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->bind_result($product_price, $product_name);
        $stmt->fetch();
        $stmt->close();

        if (empty($product_price) || empty($product_name)) {
            echo "<p style='color:red; text-align:center;'>Error: Product details not found.</p>";
            exit();
        }

        // Insert the purchase into the database with default status 'pending'
        $status = 'pending'; // Default order status
        $stmt = $conn->prepare("INSERT INTO purchases (user_id, game_id, product_id, purchase_amount, purchase_email, purchase_date, status) 
                                VALUES (?, ?, ?, ?, ?, NOW(), ?)");
        $stmt->bind_param("iiidss", $user_id, $game_id, $product_id, $product_price, $user_email, $status);

        if ($stmt->execute()) {
            echo "<p style='color:green; text-align:center;'>Purchase successful! Thank you for your purchase.</p>";

            // Fetch the newly created purchase details
            $purchase_id = $stmt->insert_id;
            $purchase_date = date('Y-m-d H:i:s'); // Current timestamp

            // Send confirmation email using PHPMailer
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
                $mail->addAddress($user_email); // User's email address

                // Email content
                $mail->isHTML(true);
                $mail->Subject = "Purchase Confirmation - Thank You for Your Order!";
                $mail->Body = "
                    <h1>Thank You for Your Purchase!</h1>
                    <p>Dear <strong>{$user_email}</strong>,</p>
                    <p>Your purchase for <strong>{$product_name}</strong> has been successful. Please find the details below:</p>
                    <ul>
                        <li>Product: {$product_name}</li>
                        <li>Amount: ₱" . number_format($product_price, 2) . "</li>
                        <li>Status: " . ucfirst($status) . "</li>
                        <li>Purchase Date: {$purchase_date}</li>
                    </ul>
                    <p>If you have any questions or need support, please contact us.</p>
                    <p>Best regards,<br>The ByteBank Team</p>
                ";

                $mail->send();
                echo "<p style='color:green; text-align:center;'>A confirmation email has been sent to your email address.</p>";
            } catch (Exception $e) {
                echo "<p style='color:red; text-align:center;'>Email could not be sent. Mailer Error: {$mail->ErrorInfo}</p>";
            }
        } else {
            echo "<p style='color:red; text-align:center;'>Error: " . $stmt->error . "</p>";
        }

        $stmt->close();
    }
}

// Handle review submission
if (isset($_POST['submit_review'])) {
    $review_text = filter_bad_words(trim($_POST['review_text']));
    $rating = intval($_POST['rating']);

    if (!empty($review_text) && $rating >= 1 && $rating <= 5) {
        $stmt = $conn->prepare("INSERT INTO reviews (user_id, game_id, review_text, rating, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("iisi", $user_id, $game_id, $review_text, $rating);

        if ($stmt->execute()) {
            echo "<p style='color:green; text-align:center;'>Review submitted successfully!</p>";
        } else {
            echo "<p style='color:red; text-align:center;'>Error: " . $stmt->error . "</p>";
        }

        $stmt->close();
    } else {
        echo "<p style='color:red; text-align:center;'>Please fill out all fields and provide a valid rating.</p>";
    }
}

// Handle review update
if (isset($_POST['update_review'])) {
    $review_id = intval($_POST['review_id']);
    $updated_text = filter_bad_words(trim($_POST['updated_review_text']));
    $updated_rating = intval($_POST['updated_rating']);

    if (!empty($updated_text) && $updated_rating >= 1 && $updated_rating <= 5) {
        $stmt = $conn->prepare("UPDATE reviews SET review_text = ?, rating = ?, created_at = NOW() WHERE review_id = ? AND user_id = ?");
        $stmt->bind_param("siii", $updated_text, $updated_rating, $review_id, $user_id);

        if ($stmt->execute()) {
            echo "<p style='color:green; text-align:center;'>Review updated successfully!</p>";
        } else {
            echo "<p style='color:red; text-align:center;'>SQL Error: " . $stmt->error . "</p>";
        }

        $stmt->close();
    } else {
        echo "<p style='color:red; text-align:center;'>Invalid review text or rating.</p>";
    }
}

// Handle review delete
if (isset($_POST['delete_review'])) {
    $review_id = intval($_POST['review_id']);

    // Ensure the review belongs to the logged-in user
    $stmt = $conn->prepare("DELETE FROM reviews WHERE review_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $review_id, $user_id);

    if ($stmt->execute()) {
        echo "<p class='success'>Review deleted successfully!</p>";
    } else {
        echo "<p class='error'>SQL Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

// Display reviews with Delete Button
$stmt = $conn->prepare("SELECT review_id, review_text, rating FROM reviews WHERE user_id = ? AND game_id = ?");
$stmt->bind_param("ii", $user_id, $game_id);
$stmt->execute();
$stmt->bind_result($review_id, $review_text, $rating);

if ($stmt->num_rows > 0) {
    while ($stmt->fetch()) {
        // Display the review text and rating
        echo "<div class='review'>";
        echo "<p><strong>Rating:</strong> $rating</p>";
        echo "<p><strong>Review:</strong> $review_text</p>";

        // Display the Delete button
        echo "<form method='POST' style='display:inline;'>
                <input type='hidden' name='review_id' value='$review_id'>
                <button type='submit' name='delete_review' class='delete-btn'>Delete</button>
              </form>";
        echo "</div>";
    }
} 

$stmt->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Call of Duty Mobile - ByteBank</title>
    <link rel="stylesheet" href="css/codm.css"> <!-- Path to the updated CSS -->
</head>
<body>
<main>
    <section class="game-section">
        <div class="content-container">
            <!-- Game Banner and Details -->
            <div class="left-block">
                <div class="game-header">
                    <img src="images/codm_banner.jpg" alt="Call of Duty Mobile" class="game-banner">
                </div>
                <div class="game-details">
                    <h1>Buy Garena Shells for Call of Duty Mobile CP</h1>
                    <p>
                        Reminder: You can convert your Garena Shells voucher to Call of Duty Mobile CP 
                        <a href="#">here</a>. Make sure to log in using your in-game account.
                    </p>
                    <p>
                        Buy Garena Shells for Call of Duty Mobile CP in seconds! Just select the recharge amount, complete the payment, and the Garena Shells voucher will immediately appear on the confirmation page and will be sent via email or SMS/Text Message (if purchased using Smart/Sun/TNT or Globe/TM).
                    </p>
                    <p>
                        Garena Shells voucher codes sold have at least 7 days validity from the date of purchase. Learn how to redeem Garena Shells voucher codes 
                        <a href="#">here</a>.
                    </p>
                    <p>Download & play Call of Duty Mobile today!</p>
                </div>
            </div>

            <!-- Purchase Form -->
            <!-- Purchase Form -->
<div class="right-block">
    <form method="POST" class="purchase-form">
    <h2>1. Enter UID</h2>
        <input type="hidden" name="game_id" value="5"> <!-- CODM game_id -->
        <!-- UID Input Section -->
        <div class="form-step">
            <div class="uid-server">
                <input type="text" name="uid" placeholder="Enter UID" required>
            </div>
            <small style="color: yellow;">To find your UID, log in to your account in the application. Check on the profile button at the top left corner.</small>
        </div>

        <!-- Select Voucher Section -->
        <div class="form-step">
            <h2>2. Select Voucher</h2>
            <small style="color: red;">251 items bought in the last hour</small>
            <div class="recharge-options">
                <?php
                $stmt = $conn->prepare("SELECT product_id, product_name, product_price FROM products WHERE game_id = ?");
                $stmt->bind_param("i", $game_id);
                $game_id = 5;
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    echo "<label class='option'>
                            <input type='radio' name='product_id' value='" . $row['product_id'] . "' required>
                            <div class='option-content'>
                                <span>" . $row['product_name'] . "</span>
                                <span>₱" . number_format($row['product_price'], 2) . "</span>
                            </div>
                        </label>";
                }

                $stmt->close();
                ?>
            </div>
        </div>

        <!-- Buy Section -->
        <div class="form-step">
            <h2>3. Buy!</h2>
            <p>Purchase receipt will be sent to your email: <strong><?= htmlspecialchars($_SESSION['user_email']); ?></strong></p>
            <label>
                <input type="checkbox"> Yes, I would like to receive news and promotions via SMS.
            </label>
            <button type="submit" class="buy-now">Buy Now</button>
        </div>
    </form>
</div>

        </div>

        <div class="content-container">
        <?php
// Check if the user has purchased the product
$purchased = false; // Default to false
$stmt = $conn->prepare("SELECT COUNT(*) FROM purchases WHERE user_id = ? AND game_id = ?");
$stmt->bind_param("ii", $_SESSION['user_id'], $game_id);
$stmt->execute();
$stmt->bind_result($purchase_count);
$stmt->fetch();
$stmt->close();

if ($purchase_count > 0) {
    $purchased = true; // User has purchased the product
}
?>

<div class="content-container">
    <!-- Left Block: Customer Reviews -->
    <div class="left-block">
        <div class="product-reviews">
            <h2>Customer Reviews</h2>
            <div class="review-list">
                <?php
                // Fetch and display all reviews for the product
                $stmt = $conn->prepare("SELECT r.review_id, r.user_id, r.review_text, r.rating, r.created_at, u.email 
                                        FROM reviews r
                                        JOIN users u ON r.user_id = u.user_id
                                        WHERE r.game_id = ?
                                        ORDER BY r.created_at DESC");
                $stmt->bind_param("i", $game_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="review">';
                        echo '<p><strong>' . htmlspecialchars($row['email']) . '</strong> - Rating: ' . $row['rating'] . '/5</p>';
                        echo '<p>"' . htmlspecialchars($row['review_text']) . '"</p>';
                        echo '<p><small>Posted on ' . $row['created_at'] . '</small></p>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No reviews yet. Be the first to leave a review!</p>';
                }
                $stmt->close();
                ?>
            </div>
        </div>
    </div>

    <!-- Right Block -->
<div class="right-block">
    <!-- Your Reviews Block -->
    <div class="user-reviews">
        <h2>Your Reviews</h2>
        <div class="review-list">
            <?php
            // Fetch and display reviews by the logged-in user
            $stmt = $conn->prepare("SELECT review_id, review_text, rating, created_at 
                                    FROM reviews 
                                    WHERE game_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $game_id, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="review">';
                    echo '<p><strong>Rating:</strong> ' . $row['rating'] . '/5</p>';
                    echo '<p><strong>"' . htmlspecialchars($row['review_text']) . '"</strong></p>';
                    echo '<p><small>Posted on ' . $row['created_at'] . '</small></p>';

                    // Update form
                    echo '<form method="POST" style="margin-bottom: 10px;">
                            <input type="hidden" name="review_id" value="' . $row['review_id'] . '">
                            <textarea name="updated_review_text" required>' . htmlspecialchars($row['review_text']) . '</textarea>
                            <input type="number" name="updated_rating" value="' . $row['rating'] . '" min="1" max="5" required>
                            <button type="submit" name="update_review">Update</button>
                          </form>';

                    // Delete form
                    echo '<form method="POST">
                            <input type="hidden" name="review_id" value="' . $row['review_id'] . '">
                            <button type="submit" name="delete_review" class="delete-btn">Delete</button>
                          </form>';
                    echo '</div>';
                }
            } else {
                echo '<p>You have not submitted any reviews yet.</p>';
            }
            $stmt->close();
            ?>
        </div>
    </div>
</div>


        <!-- Leave a Review Block (Only Visible After Purchase) -->
        <?php if ($purchased): ?>
        <div class="review-block">
            <h2>Leave a Review</h2>
            <form method="POST">
                <input type="hidden" name="game_id" value="<?= $game_id; ?>"> <!-- Hidden Game ID -->
                <div class="rating-section">
                    <label for="rating">Rate the Product:</label>
                    <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <input type="radio" name="rating" id="star-<?= $i ?>" value="<?= $i ?>" required>
                            <label for="star-<?= $i ?>" class="star">&#9733;</label>
                        <?php endfor; ?>
                    </div>
                </div>
                <textarea name="review_text" placeholder="Write your review here..." required></textarea>
                <button type="submit" name="submit_review">Submit Review</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

    </section>
</main>

<?php include '../includes/footer.php'; ?>
</body>
</html>
