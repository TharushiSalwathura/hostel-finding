<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

include 'db.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'stats':
        getStats();
        break;
    case 'test_connection':
        testConnection();
        break;
    case 'export':
        exportDatabase();
        break;
    case 'clear_data':
        clearData();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function getStats() {
    global $conn;
    
    $stats = [
        'total_rooms' => 0,
        'total_users' => 0,
        'available_rooms' => 0,
        'recent_rooms' => []
    ];
    
    // Get total rooms
    $result = $conn->query("SELECT COUNT(*) as count FROM rooms");
    if ($result) {
        $row = $result->fetch_assoc();
        $stats['total_rooms'] = $row['count'];
    }
    
    // Get total users
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        $stats['total_users'] = $row['count'];
    }
    
    // Get available rooms
    $result = $conn->query("SELECT COUNT(*) as count FROM rooms WHERE is_available = TRUE");
    if ($result) {
        $row = $result->fetch_assoc();
        $stats['available_rooms'] = $row['count'];
    }
    
    // Get recent rooms
    $result = $conn->query("SELECT title, room_type, price FROM rooms ORDER BY created_at DESC LIMIT 5");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $stats['recent_rooms'][] = $row;
        }
    }
    
    echo json_encode($stats);
}

function testConnection() {
    global $conn;
    
    if ($conn->connect_error) {
        echo json_encode(['message' => 'Connection failed: ' . $conn->connect_error]);
    } else {
        echo json_encode(['message' => 'Connection successful!']);
    }
}

function exportDatabase() {
    global $conn;
    
    // Set headers for download
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="staynest_backup.sql"');
    
    // Get all tables
    $tables = [];
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }
    
    // Generate SQL dump
    $output = "";
    foreach ($tables as $table) {
        // Add DROP TABLE statement
        $output .= "DROP TABLE IF EXISTS `$table`;\n";
        
        // Add CREATE TABLE statement
        $result = $conn->query("SHOW CREATE TABLE `$table`");
        $row = $result->fetch_row();
        $output .= $row[1] . ";\n\n";
        
        // Add INSERT statements
        $result = $conn->query("SELECT * FROM `$table`");
        while ($row = $result->fetch_assoc()) {
            $output .= "INSERT INTO `$table` VALUES(";
            $values = [];
            foreach ($row as $value) {
                $values[] = is_null($value) ? 'NULL' : "'" . $conn->real_escape_string($value) . "'";
            }
            $output .= implode(', ', $values) . ");\n";
        }
        $output .= "\n";
    }
    
    echo $output;
}

function clearData() {
    global $conn;
    
    // Disable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    
    // Get all tables
    $tables = [];
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }
    
    // Truncate all tables
    foreach ($tables as $table) {
        $conn->query("TRUNCATE TABLE `$table`");
    }
    
    // Enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    
    echo json_encode(['message' => 'All data has been cleared']);
}
?>