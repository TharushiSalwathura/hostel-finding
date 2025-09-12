<?php
include 'db.php';

$room_type = isset($_GET['type']) ? $_GET['type'] : '';
$district = isset($_GET['district']) ? $_GET['district'] : '';

// Build query based on filters
$sql = "SELECT * FROM rooms WHERE is_available = TRUE";
$params = [];
$types = "";

if (!empty($room_type)) {
    $sql .= " AND room_type = ?";
    $params[] = $room_type;
    $types .= "s";
}

if (!empty($district)) {
    $sql .= " AND district = ?";
    $params[] = $district;
    $types .= "s";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

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