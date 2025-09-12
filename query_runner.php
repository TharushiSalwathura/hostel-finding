<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

include 'db.php';

// Get the raw POST data
$input = json_decode(file_get_contents('php://input'), true);
$query = $input['query'] ?? '';

if (empty($query)) {
    echo json_encode(['error' => 'No query provided']);
    exit;
}

// Check if it's a SELECT query (for results) or other query (for execution)
if (stripos($query, 'SELECT') === 0) {
    // It's a SELECT query, return results
    $result = $conn->query($query);
    
    if ($result === FALSE) {
        echo json_encode(['error' => $conn->error]);
    } else {
        $results = [];
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
        echo json_encode(['results' => $results]);
    }
} else {
    // It's an INSERT, UPDATE, DELETE, etc.
    if ($conn->query($query)) {
        $affectedRows = $conn->affected_rows;
        echo json_encode(['message' => "Query executed successfully. Affected rows: $affectedRows"]);
    } else {
        echo json_encode(['error' => $conn->error]);
    }
}

$conn->close();
?>