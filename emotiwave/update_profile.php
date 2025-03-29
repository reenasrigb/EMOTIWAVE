<?php
// Enable error reporting for debugging purposes
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database configuration
include 'config.php';

header('Content-Type: application/json');

// Check if the required fields are provided in the request
if (!isset($_POST['email']) || !isset($_POST['name']) || !isset($_POST['phone'])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing required fields: email, name, or phone.",
    ]);
    exit;
}

// Sanitize input data to prevent SQL injection
$email = mysqli_real_escape_string($conn, trim($_POST['email']));
$name = mysqli_real_escape_string($conn, trim($_POST['name']));
$phone = mysqli_real_escape_string($conn, trim($_POST['phone']));

// Update query to modify the user data
$sql = "UPDATE users SET name = ?, phone = ? WHERE email = ?";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare SQL statement: " . mysqli_error($conn),
    ]);
    exit;
}

// Bind parameters to the query
mysqli_stmt_bind_param($stmt, "sss", $name, $phone, $email);

// Execute the query
if (mysqli_stmt_execute($stmt)) {
    // Check if any rows were updated
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Profile updated successfully.",
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "No changes were made, or user not found.",
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Query execution failed: " . mysqli_error($conn),
    ]);
}

// Close the statement and database connection
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
