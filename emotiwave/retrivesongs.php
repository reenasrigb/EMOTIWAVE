<?php
header('Content-Type: application/json');
include "config.php"; // Include the database connection

// Function to get the server's IPv4 address dynamically
function getServerIPAddress() {
    $output = [];
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Run ipconfig for Windows
        exec('ipconfig', $output);
        foreach ($output as $line) {
            if (strpos($line, 'Wireless LAN adapter Wi-Fi') !== false) {
                $wifiBlock = true;
            } elseif (isset($wifiBlock) && strpos($line, 'IPv4 Address') !== false) {
                $parts = explode(':', $line);
                return trim($parts[1]);
            }
        }
    } else {
        // For Linux/MacOS
        exec("hostname -I | awk '{print $1}'", $output);
        if (!empty($output)) {
            return trim($output[0]);
        }
    }
    return '180.235.121.245'; // Default to localhost if detection fails
}

// Detect the server's IP address
$serverIP = getServerIPAddress();

// Validate input
if (isset($_GET['mood']) && isset($_GET['language'])) {
    $mood = strtolower(trim($_GET['mood']));
    $language = strtolower(trim($_GET['language']));

    // Validate mood
    $validMoods = ['sadness', 'joy', 'love', 'anger', 'fear', 'surprise', 'neutral']; // Added 'neutral'
    if (!in_array($mood, $validMoods)) {
        echo json_encode(['status' => 'error', 'message' => "Invalid mood selected: $mood"]);
        exit;
    }

    // Validate language
    $validLanguages = ['english', 'hindi', 'all'];
    if (!in_array($language, $validLanguages)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid language selected.']);
        exit;
    }

    // Prepare the SQL query
    if ($language === 'all') {
        $query = "SELECT song_name, file_path FROM songs WHERE mood = ?";
    } else {
        $query = "SELECT song_name, file_path FROM songs WHERE mood = ? AND language = ?";
    }

    $stmt = mysqli_prepare($conn, $query);

    if ($stmt === false) {
        echo json_encode(['status' => 'error', 'message' => 'Error in SQL preparation: ' . mysqli_error($conn)]);
        exit;
    }

    // Bind parameters
    if ($language === 'all') {
        mysqli_stmt_bind_param($stmt, "s", $mood);
    } else {
        mysqli_stmt_bind_param($stmt, "ss", $mood, $language);
    }

    // Execute the query
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $song_name, $file_path);

    $songs = [];
    while (mysqli_stmt_fetch($stmt)) {
        // Adjust file URL with the dynamically detected server IP
        $fileURL = 'http://' . $serverIP . '/emotiwave/uploads/' . basename($file_path); // Ensure it's appended correctly
        $songs[] = [
            'title' => htmlspecialchars($song_name),
            'file_url' => htmlspecialchars($fileURL)
        ];
    }

    // Output the results
    if (!empty($songs)) {
        echo json_encode(['status' => 'success', 'songs' => $songs], JSON_UNESCAPED_SLASHES);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No songs found for the selected mood and language.']);
    }

    // Close the statement
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Mood or language not provided.']);
}

// Close the database connection
mysqli_close($conn);
?>
