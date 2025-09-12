<?php
// Include database connection
include __DIR__ . '/db.php';  // safer include

// Form Data
$first_name       = $_POST['first_name'];
$last_name        = $_POST['last_name'];
$mobile           = $_POST['mobile'];
$email            = $_POST['email'];
$password         = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$room_type        = $_POST['room_type'];
$district         = $_POST['district'];
$price            = $_POST['price'];
$location         = $_POST['location'];
$facilities       = $_POST['facilities'];


// Password validation
if ($password !== $confirm_password) {
    die("Passwords do not match!");
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Handle photo upload
$target_dir = "uploads/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true); // create folder if not exists
}
$photo_name = time() . "_" . basename($_FILES["photo"]["name"]);
$target_file = $target_dir . $photo_name;

if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO rooms 
        (first_name, last_name, mobile, email, password, room_type, district, price, photo, location, facilities) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssisss", 
        $first_name, $last_name, $mobile, $email, $hashed_password, 
        $room_type, $district, $price, $photo_name, $location, $facilities
    );

    // ✅ Redirect happens here after successful registration
    if ($stmt->execute()) {
        header("Location: staynest.html");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();

} else {
    echo "Error uploading file.";
}

$conn->close();
?>


