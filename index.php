<?php
session_start(); // Start the session to manage login status

// Include database configuration
include 'includes/config.php'; // Ensure this file contains your database connection setup
include 'includes/header.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize user inputs
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);

    // Prepare and execute query to check email and password
    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email); // Bind the email parameter
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Fetch hashed password from the database
        $stmt->bind_result($hashed_password);
        $stmt->fetch();

        // Verify the entered password with the stored hashed password
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_email'] = $email; // Store user info in the session
            header('Location: index.php'); // Redirect to the front page
            exit();
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    } else {
        $error = 'Invalid email or password. Please try again.';
    }

    $stmt->close(); // Close the statement
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ByteBank</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
</header>

<main>
<section class="hero">
    <h1>BYTEBANK</h1>
    <p>Skin Over Skills</p>
</section>

<section class="games-offered">
    <h2>Games Offered:</h2>
    <div class="games-list">
        <!-- Valorant Game -->
        <div class="game-item">
            <a href="<?= isset($_SESSION['user_email']) ? 'games/valo.php' : 'login.php'; ?>">
                <img src="images/valo.png" alt="Valorant">
                <p>Valorant</p>
            </a>
        </div>

        <!-- Mobile Legends -->
        <div class="game-item">
            <a href="<?= isset($_SESSION['user_email']) ? 'games/mlbb.php' : 'login.php'; ?>">
                <img src="images/ml.png" alt="Mobile Legends">
                <p>Mobile Legends</p>
            </a>
        </div>

        <!-- Call of Duty: Mobile -->
        <div class="game-item">
            <a href="<?= isset($_SESSION['user_email']) ? 'games/codm.php' : 'login.php'; ?>">
                <img src="images/codm.png" alt="Call of Duty: Mobile">
                <p>Call of Duty: Mobile</p>
            </a>
        </div>

        <!-- Genshin Impact -->
        <div class="game-item">
            <a href="<?= isset($_SESSION['user_email']) ? 'games/genshin.php' : 'login.php'; ?>">
                <img src="images/genshin.png" alt="Genshin Impact">
                <p>Genshin Impact</p>
            </a>
        </div>

        <!-- League of Legends -->
        <div class="game-item">
            <a href="<?= isset($_SESSION['user_email']) ? 'games/league.php' : 'login.php'; ?>">
                <img src="images/lol.png" alt="League of Legends">
                <p>League of Legends</p>
            </a>
        </div>
    </div>
</section>
</main>

<footer>
    <?php include('includes/footer.php'); ?>
</footer>
</body>
</html>
