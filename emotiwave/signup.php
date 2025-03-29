<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'config.php'; // Include database configuration

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the data from the POST request
    $data = json_decode(file_get_contents('php://input'), true);

    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    $phone = $data['phone'] ?? '';
    $password = $data['password'] ?? '';

    // Check if all fields are provided
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

    // Insert the new user into the database
    $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $name, $email, $phone, $password);

    if ($stmt->execute()) {
        // Store user info in session
        session_start();
        $_SESSION['user_id'] = $conn->insert_id; // Store user ID in session
        $_SESSION['user'] = array(
            'name' => $name,
            'email' => $email,
            'phone' => $phone
        );

        // Send a success response
        $response['status'] = 'success';
        $response['message'] = 'Registration successful.';
    } else {
        // Send an error response if insert fails
        $response['status'] = 'error';
        $response['message'] = 'Error: ' . $stmt->error;
    }

    // Close the prepared statement and database connection
    $stmt->close();
    $conn->close();
} else {
    // Handle invalid request method
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>

    