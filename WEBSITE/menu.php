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
    $name = $row['Name']; 
    $tranportType = $row['TransportType']; 
   
} else {
    echo "User not found.";
    exit();
}

$recommendedResult = $conn->query("SELECT * FROM recommendation WHERE UserId = $UserID");
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
<body class="main" style="align-items: flex-start; background-color: #2f3440;">
    
    <div class="sidebar panel" style="position: fixed; left: 0; top: 0; width: 20%">
        <div class="profile">
            <img src="placeholder-profile.png" alt="Profile Image">
            <h2><?php echo $name; ?></h2>
        </div>
        <div class="button-container">
            <button onclick="window.location.href='account-setup.php'">Update Preferences</button>
        </div>
        <div class="button-container">
            <button onclick="window.location.href='logout.php'">Logout</button>
        </div>
        
    </div>
    <div class="content" style="margin-top: 20px; margin-left: 22%; padding-left: 2%;">
        <h1 class="site-name" style="text-align:center" >Virtual Travel Assitant</h1>
        <h1 class="menu-heading">Here are a few suggestions:</h1>

        
        <div class="cards" style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-start; margin-top: 20px;">
            <?php while ($row = $recommendedResult->fetch_assoc()): ?>
            <div class="card">
                <img src="place-bg.jpg" alt="Kelowna" style="width: 300px; height: 200px;">
                <h3><?php echo $row['Destination']; ?></h3>
                <p>Based on your transport preference: <?php echo htmlspecialchars($tranportType); ?></p>
                
                <button onclick="window.location.href='delete.php?id=<?php echo $row['RecommendationID']?>'">Delete</button>
                
            </div>
            <?php endwhile; ?>
        </div>
        
    </div>
</body>
</html>