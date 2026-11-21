<?php
session_start(); // Ensure this is called only once in your code
include('../includes/config.php'); // Ensure this path is correct and establishes the database connection

// Check if form is submitted
if (isset($_POST['submit'])) {
    $email = isset($_POST['email']) ? trim($_POST['email']) : ''; // Prevent undefined index warning
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Validate email and password before querying
    if (!empty($email) && !empty($password)) {
        $sql = "SELECT user_id, email, password, role FROM users WHERE email = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            mysqli_stmt_bind_result($stmt, $user_id, $email, $hashed_password, $role);

            if (mysqli_stmt_num_rows($stmt) === 1) {
                mysqli_stmt_fetch($stmt);
                if (password_verify($password, $hashed_password)) {
                    // Set session variables on successful login
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['email'] = $email;
                    $_SESSION['role'] = $role;

                    header("Location: signUp.php"); // Redirect on successful login
                    exit();
                } else {
                    $_SESSION['message'] = 'Invalid password';
                }
            } else {
                $_SESSION['message'] = 'Email not registered';
            }
        } else {
            $_SESSION['message'] = 'Database query failed. Please try again later.';
        }
    } else {
        $_SESSION['message'] = 'Please fill in all fields.';
    }
    $stmt->close();
    $conn->close();
}

?>
