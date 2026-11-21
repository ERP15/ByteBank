<?php
session_start();
include("includes/config.php"); // Ensure the correct path here

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    $phone = trim($_POST["phone"]);

    if ($password !== $confirm_password) {
        $_SESSION["message"] = "Passwords do not match.";
        header("Location: signup.php");
        exit();
    }

    $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, phone) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $password_hashed, $phone);

    if ($stmt->execute()) {
        $_SESSION["message"] = "Sign up successful! Please log in.";
        header("Location: login.php");
    } else {
        $_SESSION["message"] = "Error: Could not complete registration.";
        header("Location: signUp.php");
    }

    $stmt->close();
    $conn->close();
}
?>
