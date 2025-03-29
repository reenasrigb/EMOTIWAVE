<?php
header('Content-Type: application/json');

// Function to get the server's IPv4 address dynamically
function getServerIPAddress() {
    $output = [];
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Run ipconfig on Windows
        exec('ipconfig', $output);
        foreach ($output as $line) {
            // Look for IPv4 address in Wi-Fi adapter section
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
    return '127.0.0.1'; // Default to localhost if detection fails
}

// Get the server's IP address dynamically
$serverIP = getServerIPAddress();

// Include the database connection
include "config.php";

// Check if files are uploaded and mood and language are selected
if (isset($_FILES['fileUpload']) && isset($_POST['mood']) && isset($_POST['language'])) {
    $mood = $_POST['mood'];
    $language = $_POST['language']; // Get the language from POST data

    // Validate mood
    $validMoods = ['sadness', 'joy', 'love', 'anger', 'fear', 'surprise', 'neutral']; // Added 'neutral'
    if (!in_array($mood, $validMoods)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid mood selected.']);
        exit;
    }

    // Validate language
    if (empty($language)) {
        echo json_encode(['status' => 'error', 'message' => 'Language is required.']);
        exit;
    }

    // Ensure the uploads directory exists
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Ensure 'fileUpload' is an array of files
    if (!is_array($_FILES['fileUpload']['name'])) {
        $_FILES['fileUpload']['name'] = [$_FILES['fileUpload']['name']];
        $_FILES['fileUpload']['tmp_name'] = [$_FILES['fileUpload']['tmp_name']];
        $_FILES['fileUpload']['error'] = [$_FILES['fileUpload']['error']];
        $_FILES['fileUpload']['size'] = [$_FILES['fileUpload']['size']];
    }

    $uploadedSongs = [];
    foreach ($_FILES['fileUpload']['name'] as $key => $fileName) {
        if ($_FILES['fileUpload']['error'][$key] == 0) {
            $fileTmpName = $_FILES['fileUpload']['tmp_name'][$key];
            $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
            $validFileTypes = ['mp3', 'mpeg']; // Allowed file extensions

            // Check file type
            if (!in_array(strtolower($fileExt), $validFileTypes)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid file type for: ' . $fileName]);
                exit;
            }

            // Create a unique filename to avoid overwriting
            $uniqueFileName = uniqid() . '.' . $fileExt;
            $uploadPath = $uploadDir . $uniqueFileName;

            // Move the uploaded file to the server
            if (move_uploaded_file($fileTmpName, $uploadPath)) {
                // Construct the full URL for the file using the detected server IP
                $fullUrl = 'http://' . $serverIP . '/emotiwave/' . $uploadPath;

                // Prepare the SQL query to insert the new song including language
                $stmt = mysqli_prepare($conn, "INSERT INTO songs (song_name, file_path, mood, language) VALUES (?, ?, ?, ?)");

                // Check if the prepare statement is valid
                if ($stmt === false) {
                    echo json_encode(['status' => 'error', 'message' => 'Error in SQL preparation: ' . mysqli_error($conn)]);
                    exit;
                }

                // Bind the parameters to the prepared statement
                mysqli_stmt_bind_param($stmt, "ssss", $fileName, $fullUrl, $mood, $language);

                // Execute the prepared statement
                if (mysqli_stmt_execute($stmt)) {
                    $uploadedSongs[] = $fileName;
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to insert song into database. Error: ' . mysqli_stmt_error($stmt)]);
                    exit;
                }

                // Close the prepared statement
                mysqli_stmt_close($stmt);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to upload file: ' . $fileName]);
                exit;
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error with file upload: ' . $_FILES['fileUpload']['name'][$key]]);
            exit;
        }
    }

    // Return success message with the uploaded songs and language
    echo json_encode([
        'status' => 'success',
        'message' => 'Songs uploaded successfully!',
        'songs' => $uploadedSongs,
        'language' => $language
    ]);

} else {
    echo json_encode(['status' => 'error', 'message' => 'No files, mood, or language selected.']);
}

// Close the database connection
mysqli_close($conn);
?>
