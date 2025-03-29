<?php
require 'config.php'; // Include database connection

header('Content-Type: application/json');
$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    if (empty($email)) {
        $response["message"] = "Email is required.";
        echo json_encode($response);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);

    if ($stmt->execute()) {
        $response["success"] = true;
        $response["message"] = "Account deleted successfully.";
    } else {
        $response["message"] = "Failed to delete account.";
    }

    $stmt->close();
    $conn->close();
} else {
    $response["message"] = "Invalid request method.";
}

echo json_encode($response);
?>