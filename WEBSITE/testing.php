<?php
session_start();
include 'connect_db.php';

if (!isset($_SESSION['UserID'])) {
    echo "Unauthorized access! Redirecting to login...";
    header("refresh:2; url=login.php");
    exit();
}

$UserID = $_SESSION['UserID']; // Retrieve UserID from session

// Fetch user preferences from database
$query = "SELECT * FROM Users WHERE UserID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $UserID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
} else {
    echo "User preferences not found!";
    exit();
}

$stmt->close();
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Destination Recommender</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        async function getSuggestions() {
            let requestData = {
                accommodation_budget: <?php echo $user['AccomodationBudget']; ?>,
                transportation_budget: <?php echo $user['TransportBudget']; ?>,
                transportation_type: "<?php echo $user['TransportType']; ?>",
                climate: "<?php echo $user['PreferredClimate']; ?>",
                activities: "<?php echo $user['PreferredActivities']; ?>"
            };

            try {
                let response = await fetch("http://127.0.0.1:5000/suggest_destinations", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(requestData)
                });

                let data = await response.json();
                if (data.suggested_destinations) {
                    displaySuggestions(data.suggested_destinations);
                } else {
                    alert("No suggestions available.");
                }
            } catch (error) {
                console.error("Error:", error);
                alert("Error fetching suggestions.");
            }
        }

        function displaySuggestions(destinations) {
            let container = document.getElementById("suggestions");
            container.innerHTML = "";  // Clear previous results

            destinations.forEach((destination) => {
                let card = document.createElement("div");
                card.classList.add("card");

                card.innerHTML = `
                    <h3>${destination.destination}</h3>
                    <p><strong>Transport Type:</strong> ${destination.transportation_type}</p>
                    <p><strong>Climate:</strong> ${destination.climate}</p>
                    <p><strong>Activities:</strong> ${destination.activities}</p>
                    <button onclick="submitFeedback('${destination.destination}', 'yes')">Yes</button>
                    <button onclick="submitFeedback('${destination.destination}', 'no')">No</button>
                `;

                container.appendChild(card);
            });
        }

        let feedbackList = [];

        function submitFeedback(destination, feedback) {
            feedbackList.push({ destination: destination, feedback: feedback });

            // If all 5 destinations received feedback, send to backend
            if (feedbackList.length === 5) {
                sendFeedback();
            }
        }

        async function sendFeedback() {
            try {
                let response = await fetch("http://127.0.0.1:5000/submit_feedback", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        user_id: "<?php echo $_SESSION['UserID']; ?>",  // Get user_id from PHP session
                        feedback: feedbackList
                    })
                });

                let result = await response.json();
                alert(result.message || "Feedback submitted!");
                feedbackList = [];
            } catch (error) {
                console.error("Error:", error);
                alert("Error submitting feedback.");
            }
        }

        // Auto-fetch suggestions when page loads
        window.onload = getSuggestions;
    </script>
   
</head>
<body class="main" style="align-items: flex-start; background-color: #2f3440;">

    <div class="sidebar panel" style="position: fixed; left: 0; top: 0; width: 20%">
        <div class="profile">
            <img src="placeholder-profile.png" alt="Profile Image">
            <h2><?php echo $user['Name']; ?></h2>
        </div>
        <div class="button-container">
            <button onclick="window.location.href='logout.php'">Logout</button>
        </div>
    </div>
    <div class="content" style="margin-top: 20px; margin-left: 22%; padding-left: 2%;" >

        
        <h2>Suggested Destinations:</h2>

        <div class="card" id="suggestions"> </div>
        <div class="button-container">
        <button onclick="getSuggestions()">Suggest Other Destinations</button>
        </div>
        <div class="button-container">
            <button onclick="window.location.href='menu.php'">View Your Destinations</button>
        </div>
        

    </div>

    <!-- New Button to Fetch Other Destinations -->
    
</body>
</html>
