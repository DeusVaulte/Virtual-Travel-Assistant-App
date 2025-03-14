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
    $accommodation_budget = $row['AccomodationBudget'];
    $transportation_budget = $row['TransportBudget'];
    $transport_type = $row['TransportType']; // Get transportation type

    // Prepare data for API request
    $data = array(
        "accommodation_budget" => $accommodation_budget,
        "transportation_budget" => $transportation_budget,
        "transportation_type" => $transport_type // Include transport type
    );

    // Convert data to JSON
    $json_data = json_encode($data);

    // Call Python API
    $api_url = "http://127.0.0.1:5000/suggest_destination"; // Ensure Flask server is running
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

    $response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get HTTP status code
    curl_close($ch);

    // Decode JSON response
    $decoded_response = json_decode($response, true);
    
    if ($http_status != 200 || !$decoded_response || !isset($decoded_response['suggested_destination'])) {
        $suggested_destination = "No suggestion available"; // Default message
    } else {
        $suggested_destination = $decoded_response['suggested_destination'];
    }
} else {
    echo "User not found.";
    exit();
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
        <h2> <?php echo htmlspecialchars($name); ?></h2>
    </div>
    <div class="content">
        <h1>Here are a few suggestions:</h1>
        <div class="card">
            <div class="card">
                <img src="kelowna.jpg" >
                <h3><?php echo htmlspecialchars($suggested_destination); ?></h3>
                <p>Based on your transport preference: <?php echo htmlspecialchars($transport_type); ?></p>
                <button>View</button>
            </div>
            
        </div>
    </div>
</body>
</html>
