<?php 
session_start();
include 'connect_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $email = $conn->real_escape_string($email);
    $password = $conn->real_escape_string($password);

    $checkEmail = "SELECT Email FROM User WHERE Email = '$email'";
    $result = $conn->query($checkEmail);
    if ($result->num_rows > 0) {
        echo "Email already exists. Try logging in.";
    } else {
        // Hash password before storing
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $sql = "INSERT INTO Users (Email, Password) 
                VALUES ('$email', '$hashedPassword')";

        if ($conn->query($sql) === TRUE) {
            echo "Signup successful! Redirecting to login...";
            header("refresh:2; url=login.php"); // Redirect after signup
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    }




    $conn->close();
}






?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="signup">
    <div class="overlay">
        <h1>Start your Journey through the world with us.</h1>
        <form action="signup.php" method="POST"> 
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Create Account</button>
            <p>Already have an account? <a href="login.html">Login</a></p>
        </form>
    </div>
</body>
</html>
