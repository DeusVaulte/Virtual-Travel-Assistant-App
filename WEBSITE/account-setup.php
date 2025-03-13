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
    $TransportType = $_POST['TransportType'];

    

    

    // Create an array to store non-empty fields
    $updates = [];

    // Only add fields that are not empty
    if (!empty($name)) {
        $name = $conn->real_escape_string($name);
        $updates[] = "Name = '$name'";
    }
    if (!empty($TransportBudget)) {
        $TransportBudget = floatval($TransportBudget);
        $updates[] = "TransportBudget = $TransportBudget";
    }
    if (!empty($AccomodationBudget)) {
        $AccomodationBudget = floatval($AccomodationBudget);
        $updates[] = "AccomodationBudget = $AccomodationBudget";
    }
    if (!empty($PreferredClimate)) {
        $PreferredClimate = $conn->real_escape_string($PreferredClimate);
        $updates[] = "PreferredClimate = '$PreferredClimate'";
    }
    if (!empty($PreferredActivities)) {
        $PreferredActivities = $conn->real_escape_string($PreferredActivities);
        $updates[] = "PreferredActivities = '$PreferredActivities'";
    }
    if (!empty($TravelHistory)) {
        $TravelHistory = $conn->real_escape_string($TravelHistory);
        $updates[] = "TravelHistory = '$TravelHistory'";
    }
    if (!empty($TransportType)) {
        $TransportType = $conn->real_escape_string($TransportType);
        $updates[] = "TransportType = '$TransportType'";
    }

    // Only execute the query if there are changes
    if (!empty($updates)) {
        $sql = "UPDATE Users SET " . implode(", ", $updates) . " WHERE UserID = $UserID";

        if ($conn->query($sql) === TRUE) {
            echo "Profile updated successfully! Redirecting...";
            header("refresh:2; url=testing.php"); // Redirect after update
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "No changes made (all fields were empty).";
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
    <div class="blue-box">
        <div class="header-container">
            <h1 class="site-name">Site Name</h1>
            <h1 class="setup-heading">Let’s get you all set.</h1>
        </div>
    <div class="overlay">
        <form action="account-setup.php" method="POST">
            <input type="text" placeholder="What should we call you?" name="name">
            <input type="text" placeholder="What’s your transport budget?" name="TransportBudget">
            <input type="text" placeholder="What’s your transport Type?" name="TransportType">
            <input type="text" placeholder="What’s your Accomodation budget?" name="AccomodationBudget">
            <input type="text" placeholder="What is your preferred climate?" name="PreferredClimate">
            <input type="text" placeholder="What do you like to do in your travels?" name="PreferredActivities">
            <input type="text" placeholder="Give us a few countries you have already visited" name="TravelHistory">
            <button type="submit">Submit</button>
        </form>
    </div>
    </div>
</body>
</html>
