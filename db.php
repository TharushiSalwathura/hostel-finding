<?php
// Database connection settings
$host = "localhost";   // Database host
$user = "root";        // Database username (default in XAMPP)
$pass = "";            // Database password (leave empty in XAMPP by default)
$db   = "hostel_finder"; // Your database name

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
