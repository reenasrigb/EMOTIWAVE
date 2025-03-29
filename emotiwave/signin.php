<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Include database configuration
include 'config.php'; // Ensure the correct path to your config file

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $data = json_decode(file_get_contents('php://input'), true);

    $email = $data['email'] ?? '';
    $password = $data['password'] ?? ''; // Password received from Flutter app

    // Validate input
    if (empty($email) || empty($password)) {
        $response['status'] = 'error';
        $response['message'] = 'Email and password are required.';
        echo json_encode($response);
        exit();
    }

    // Check if email exists
    $checkEmail = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $checkEmail->bind_param('s', $email);
    $checkEmail->execute();
    $checkEmail->store_result();

    if ($checkEmail->num_rows > 0) {
        $checkEmail->bind_result($userId, $storedPassword);
        $checkEmail->fetch();

        // Directly compare passwords (no hash verification)
        if ($password === $storedPassword) {
            $response['status'] = 'success';
            $response['message'] = 'Login successful';
            $response['user_id'] = $userId;
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Invalid password.';
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Email not found.';
    }

    $checkEmail->close();
    $conn->close();
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
