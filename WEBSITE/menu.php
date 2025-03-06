<?php 
session_start();
include 'connect_db.php';

if (!isset($_SESSION['UserID'])) {
    echo "Unauthorized access! Redirecting...";
    header("refresh:2; url=login.php");
    exit();
}


$UserID = $_SESSION['UserID']; // Get logged-in User ID

$sql = "SELECT * FROM Users WHERE UserID = $UserID";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $name = $row['Name'];  // Store Name in a PHP variable
   
} else {
    echo "User not found.";
}

$conn->close();



?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Page</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="main">
    <div class="sidebar">
        <h2> <?php echo $name ?></h2>
    </div>
    <div class="content">
        <h1>Here are a few suggestions:</h1>
        <div class="cards">
            <div class="card">
                <img src="kelowna.jpg" alt="Kelowna, Canada">
                <h3>Kelowna, Canada</h3>
                <p>Temperature, Forest, Lakeside</p>
                <button>View</button>
            </div>
            <div class="card">
                <img src="france.jpg" alt="France">
                <h3>France</h3>
                <p>Temperature, Forest, Lakeside</p>
                <button>View</button>
            </div>
        </div>
    </div>
</body>
</html>
