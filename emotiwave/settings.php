<?php
// Include database configuration
include 'config.php';
session_start(); // Start the session to retrieve user data

// Check if user is logged in (session is set)
if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    $name = $user['name'];
    $email = $user['email'];
    $phone = $user['phone'];

    // You can also use $conn from config.php if you need to do database queries

    // Display user details
    echo "<h1>User Settings</h1>";
    echo "<p>Name: $name</p>";
    echo "<p>Email: $email</p>";
    echo "<p>Phone: $phone</p>";
} else {
    echo "<p>You are not logged in.</p>";
}
?>
