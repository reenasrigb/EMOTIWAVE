<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
include 'config.php'; // Make sure 'config.php' contains a valid $conn connection

header('Content-Type: application/json');

// Check if 'email' is provided in the request
if (!isset($_GET['email']) || empty($_GET['email'])) {
    echo json_encode([
        "success" => false,
        "message" => "Email is required.",
    ]);
    exit;
}

// Sanitize the email input to avoid SQL injection
$email = mysqli_real_escape_string($conn, trim($_GET['email']));

// Query to fetch the user details
$sql = "SELECT name, email, phone FROM users WHERE email = ?";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare SQL statement: " . mysqli_error($conn),
    ]);
    exit;
}

// Bind the email parameter to the query
mysqli_stmt_bind_param($stmt, "s", $email);

// Execute the query
if (!mysqli_stmt_execute($stmt)) {
    echo json_encode([
        "success" => false,
        "message" => "Query execution failed: " . mysqli_error($conn),
    ]);
    exit;
}

$result = mysqli_stmt_get_result($stmt);

// Fetch the user details if available
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    echo json_encode([
        "success" => true,
        "data" => [
            "name" => $row['name'],
            "email" => $row['email'],
            "phone" => $row['phone'], // Ensure 'phone' column exists in the database
        ],
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "User not found.",
    ]);
}

// Close the statement and connection
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
