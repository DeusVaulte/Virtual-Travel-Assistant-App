<?php 
session_start();
include 'connect_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];


    $email = $conn->real_escape_string($email);
    $password = $conn->real_escape_string($password);

    // Fetch user from database
    $sql = "SELECT * FROM Users WHERE Email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['Password'])) { // Assuming passwords are hashed
            $_SESSION['UserID'] = $row['UserID'];
            $_SESSION['Email'] = $email;

            

            echo "Login successful! Redirecting...";
            header("refresh:2; url=menu.html"); // Redirect after login
            exit();
        } else {
            echo "Invalid email or password.";
        }
    } else {
        echo "No account found with that email.";
    }

    $conn->close();
}








?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="login">
    <div class="overlay">
        <h1>Ready to continue your Journey?</h1>
        <form action="login.php" method="POST">
            <input type="email" placeholder="Email" name="email" required>
            <input type="password" placeholder="Password" name="password">
            <button type="submit">Log in</button>
            <p>Don’t have an account yet? <a href="signup.php">Sign Up</a></p>
        </form>
    </div>
</body>
</html>
