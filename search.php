<?php
// db.php - Database connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "hostel_finder";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//  Get latitude, longitude, and radius (default 5 km)
$lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$lng = isset($_GET['lng']) ? floatval($_GET['lng']) : null;
$radius = isset($_GET['radius']) ? floatval($_GET['radius']) : 5000; // meters

if ($lat === null || $lng === null) {
    echo json_encode(["error" => "lat and lng are required"]);
    exit;
}

//  Query hostels within radius using Haversine formula
$sql = "
    SELECT id, name, address, latitude, longitude,
           (6371000 * acos(
               cos(radians(?)) * cos(radians(latitude)) *
               cos(radians(longitude) - radians(?)) +
               sin(radians(?)) * sin(radians(latitude))
           )) AS distance
    FROM hostels
    HAVING distance < ?
    ORDER BY distance ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("dddi", $lat, $lng, $lat, $radius);
$stmt->execute();
$result = $stmt->get_result();

$hostels = [];
while ($row = $result->fetch_assoc()) {
    $hostels[] = $row;
}

echo json_encode($hostels, JSON_PRETTY_PRINT);

$conn->close();
?>