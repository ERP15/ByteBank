<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - ByteBank</title>
    <link rel="stylesheet" href="css/contact.css"> <!-- Adjust the path to your CSS file -->
</head>
<body>
<header>
    <div class="navbar">
        <a href="index.php">
            <img src="images/bytebank_logo.png" alt="ByteBank Logo" class="logo" style="width: 50px; height: 50px;">
        </a>
        <nav>
            <ul>
                <li><a href="about_us.php">About Us</a></li>
                <li><a href="contact.php" class="active">Contact</a></li>
                <li><a href="location.php">Location</a></li>
            </ul>
        </nav>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</header>

<main>
    <div class="content-container">
        <!-- Contact Introduction -->
        <section class="contact-intro">
            <h1>Contact Us</h1>
            <p>We're here to help you with any questions, concerns, or feedback you may have. Reach out to us using the details below!</p>
        </section>

        <!-- Contact Information -->
        <section class="contact-info">
            <h2>Contact Information</h2>
            <div class="info-box">
                <p><strong>Email:</strong> support@bytebank.com</p>
                <p><strong>Phone:</strong> +1 (800) 123-4567</p>
             
            </div>
        </section>

        <!-- Contact Form -->
        <section class="contact-form">
            <h2>Send Us a Message</h2>
            <form action="submit_contact.php" method="POST">
                <input type="text" name="name" placeholder="Your Name" required>
                <input type="email" name="email" placeholder="Your Email" required>
                <textarea name="message" placeholder="Your Message" rows="6" required></textarea>
                <button type="submit" class="submit-btn">Submit</button>
            </form>
        </section>
    </div>
</main>

<footer>
    <p>&copy; 2024 ByteBank. All rights reserved.</p>
</footer>
</body>
</html>
