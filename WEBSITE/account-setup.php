<?php
session_start();
include 'connect_db.php';

if (!isset($_SESSION['UserID'])) {
    echo "Unauthorized access! Redirecting to login...";
    header("refresh:2; url=login.php");
    exit();
}
$UserID = $_SESSION['UserID']; // Retrieve UserID from session


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $TransportBudget = floatval($_POST['TransportBudget']);
    $AccomodationBudget = floatval($_POST['AccomodationBudget']);
    $PreferredClimate = $_POST['PreferredClimate'];
    $PreferredActivities = $_POST['PreferredActivities'];
    $TravelHistory = $_POST['TravelHistory'];

    

    

    // Insert new user
         $sql = "UPDATE Users 
            SET Name = '$name', 
                TransportBudget = $TransportBudget, 
                AccomodationBudget = $AccomodationBudget, 
                PreferredClimate = '$PreferredClimate', 
                PreferredActivities = '$PreferredActivities', 
                TravelHistory = '$TravelHistory' 
            WHERE UserID = $UserID";

        if ($conn->query($sql) === TRUE) {
            echo "Signup successful! Redirecting to login...";
            header("refresh:2; url=menu.php"); // Redirect after input of pref
            exit();
        } else {
            echo "Error: " . $conn->error;
        }




    $conn->close();
}










?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Setup</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="account-setup">
    <div class="overlay">
        <h1>Let’s get you all set.</h1>
        <form action="account-setup.php" method="POST">
            <input type="text" placeholder="What should we call you?" name="name">
            <input type="text" placeholder="What’s your transport budget?" name="TransportBudget">
            <input type="text" placeholder="What’s your Accomodation budget?" name="AccomodationBudget">
            <input type="text" placeholder="What is your preferred climate?" name="PreferredClimate">
            <input type="text" placeholder="What do you like to do in your travels?" name="PreferredActivities">
            <input type="text" placeholder="Give us a few countries you have already visited" name="TravelHistory">
            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>
