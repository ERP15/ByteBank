<?php
session_start(); // Start the session for login management

// Include database configuration
include 'includes/config.php'; // Ensure this file contains your database connection setup

$error = ''; // Initialize error message variable

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize user inputs
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);

    // Validate database connection
    if (!$conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }

    // Prepare and execute query to fetch user information
    $stmt = $conn->prepare("SELECT user_id, password, role FROM users WHERE email = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $email); // Bind the email parameter
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Fetch user details
        $stmt->bind_result($user_id, $hashed_password, $role);
        $stmt->fetch();

        // Verify the entered password with the stored hashed password
        if (password_verify($password, $hashed_password)) {
            // Regenerate session ID for security
            session_regenerate_id(true);

            // Store user info in the session
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = $role;

            // Redirect based on the role
            if ($role === 'admin') {
                header('Location: admin_dashboard.php'); // Redirect to admin dashboard
            } else {
                header('Location: index.php'); // Redirect to user homepage
            }
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
    <link rel="stylesheet" href="css/login.css"> <!-- Ensure the correct path to login.css -->
</head>
<body>
    <?php include 'includes/header.php'; ?> <!-- Correct header inclusion -->

    <main>
        <section class="login-container">
            <h1>BYTEBANK</h1>
            <p>Skin Over Skills</p>
            <div class="form-box">
                <h2>Login</h2>
                <?php if ($error): ?>
                    <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
                <form action="login.php" method="POST">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>

                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>

                    <button type="submit">Log In</button>
                </form>
                <p>Don't have an account? <a href="signUp.php">Sign Up</a></p>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?> <!-- Correct footer inclusion -->
</body>
</html>
