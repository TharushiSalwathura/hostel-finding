<?php
include 'db.php';

// Get search parameters
$query = isset($_GET['query']) ? $_GET['query'] : '';
$lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$lng = isset($_GET['lng']) ? floatval($_GET['lng']) : null;
$radius = isset($_GET['radius']) ? floatval($_GET['radius']) : 5000; // meters

// Build base query
$sql = "SELECT *, 
        (6371000 * acos(
            cos(radians(?)) * cos(radians(latitude)) *
            cos(radians(longitude) - radians(?)) +
            sin(radians(?)) * sin(radians(latitude))
        )) AS distance 
        FROM rooms 
        WHERE is_available = TRUE";
$params = [$lat, $lng, $lat];
$types = "ddd";

// Add text search if provided
if (!empty($query)) {
    $sql .= " AND (title LIKE ? OR district LIKE ? OR facilities LIKE ?)";
    $searchTerm = "%$query%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "sss";
}

// Add distance filter if coordinates provided
if ($lat !== null && $lng !== null) {
    $sql .= " HAVING distance < ?";
    $params[] = $radius;
    $types .= "i";
    $sql .= " ORDER BY distance ASC";
} else {
    $sql .= " ORDER BY created_at DESC";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$rooms = [];
while ($row = $result->fetch_assoc()) {
    $rooms[] = $row;
}

echo json_encode($rooms);

$stmt->close();
$conn->close();
?>