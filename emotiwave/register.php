<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'config.php'; // Include database configuration

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    $phone = $data['phone'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        $response['status'] = 'error';
        $response['message'] = 'All fields are required.';
        echo json_encode($response);
        exit();
    }

    // Check if the email already exists
    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkEmail->bind_param('s', $email);
    $checkEmail->execute();
    $checkEmail->store_result();

    if ($checkEmail->num_rows > 0) {
        $response['status'] = 'error';
        $response['message'] = 'Email already exists.';
        echo json_encode($response);
        $checkEmail->close();
        $conn->close();
        exit();
    }
    $checkEmail->close();

    // Insert the user data into the database
    $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $name, $email, $phone, $password);

    if ($stmt->execute()) {
        // Start the session and store user information
        session_start();
        $_SESSION['user'] = array(
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'id' => $conn->insert_id // Store the user ID
        );

        $response['status'] = 'success';
        $response['message'] = 'Registration successful.';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Error: ' . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
