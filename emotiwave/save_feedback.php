<?php
// Include the database configuration file
include 'config.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session if necessary for user-specific info (optional)
session_start();

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get email and feedback from the POST request
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $feedback = isset($_POST['feedback']) ? trim($_POST['feedback']) : '';

    // Validate feedback and email
    if (!empty($email) && !empty($feedback)) {
        // Prepare and bind the SQL statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO feedback (email, feedback_text) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param("ss", $email, $feedback);

            // Execute the statement and check if successful
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Feedback saved successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'SQL execution error: ' . $stmt->error]);
            }

            // Close the statement
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'SQL preparation error: ' . $conn->error]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Email or feedback cannot be empty.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

// Close the database connection
$conn->close();
?>
