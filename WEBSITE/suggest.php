<?php
// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "vta_db";

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch accommodation and transportation budget from database
$sql = "SELECT accommodation_budget, transportation_budget FROM WHERE user_id = 1"; // Change 'user_id' dynamically
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $accommodation_budget = $row["accommodation_budget"];
    $transportation_budget = $row["transportation_budget"];

    // Prepare data for API
    $data = array(
        "accommodation_budget" => $accommodation_budget,
        "transportation_budget" => $transportation_budget
    );

    $json_data = json_encode($data);

    // Call Python API
    $api_url = "http://127.0.0.1:5000/suggest_destination"; // Ensure Flask server is running
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

    $response = curl_exec($ch);
    curl_close($ch);

    $decoded_response = json_decode($response, true);
    $suggested_destination = $decoded_response['suggested_destination'];

    // Display result in an HTML page
    echo "<h2>Suggested Destination: $suggested_destination</h2>";
} else {
    echo "No data found!";
}

$conn->close();
?>