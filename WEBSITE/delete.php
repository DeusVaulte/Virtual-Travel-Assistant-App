<?php
session_start();
include_once("connect_db.php");

// Get id from URL to delete that user
$id = $_GET['id'];

// Delete user row from table based on given id
$result = mysqli_query($conn, "DELETE FROM recommendation WHERE RecommendationID=$id");

// After delete redirect to Home, so that latest user list will be displayed.
header("Location:menu.php");

$conn->close();
?>

