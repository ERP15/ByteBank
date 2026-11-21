<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - ByteBank</title>
    <link rel="stylesheet" href="css/signup.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

    <main>
        <section class="signup-container">
            <h1>BYTEBANK</h1>
            <p>Skin Over Skills</p>
            <div class="form-box">
                <h2>Sign Up Register</h2>
                <form action="processSignup.php" method="POST">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>

                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>

                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>

                    <label for="phone">Phone:</label>
                    <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required>

                    <button type="submit">Sign Up</button>
                </form>
                <p>Already have an account? <a href="login.php">Log-in</a></p>
            </div>
        </section>
    </main>
    <script>
    document.querySelector("form").addEventListener("submit", function (e) {
        const username = document.getElementById("username").value.trim();
        const email = document.getElementById("email").value.trim();
        const password = document.getElementById("password").value.trim();
        const confirmPassword = document.getElementById("confirm_password").value.trim();

        // Validate username (no special characters or foul words)
        const foulWords = ["badword1", "badword2", "badword3"]; // Replace with actual foul words
        if (foulWords.some((word) => username.toLowerCase().includes(word))) {
            alert("Your username contains inappropriate language.");
            e.preventDefault();
            return;
        }

        if (!/^[a-zA-Z0-9_]+$/.test(username)) {
            alert("Username can only contain letters, numbers, and underscores.");
            e.preventDefault();
            return;
        }

        // Validate email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert("Please enter a valid email address.");
            e.preventDefault();
            return;
        }

        // Validate password length
        if (password.length < 8) {
            alert("Password must be at least 8 characters long.");
            e.preventDefault();
            return;
        }

        // Check if passwords match
        if (password !== confirmPassword) {
            alert("Passwords do not match.");
            e.preventDefault();
            return;
        }
    });
</script>
<footer>
    <?php include('includes/footer.php');?>
    
</footer>
</body>
</html>
