<?php
// Include the database configuration file
include 'config.php';

// Start session if necessary for user-specific info (optional)
session_start();

// SQL to fetch feedback data
$sql = "SELECT email, feedback_text FROM feedback";
$result = $conn->query($sql);

// Check if there are any records
if ($result->num_rows > 0) {
    echo "<h2>Feedback Entries</h2>";
    while ($row = $result->fetch_assoc()) {
        echo "<p><strong>Email:</strong> " . htmlspecialchars($row['email']) . "<br>";
        echo "<strong>Feedback:</strong> " . htmlspecialchars($row['feedback_text']) . "</p><hr>";
    }
} else {
    echo "No feedback found.";
}

// Close the database connection
$conn->close();
?>
