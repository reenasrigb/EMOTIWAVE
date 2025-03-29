<?php
header('Content-Type: application/json');

include 'config.php';

$response = ['success' => false, 'message' => ''];

try {
    // Base URL for uploaded images
    $baseUrl = 'http://180.235.121.245/emotiwave/uploads/';

    // Check if the image is uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imageTmpPath = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $imageExtension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

        // Validate the image file type (JPEG, PNG, JPG)
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        if (!in_array($imageExtension, $allowedExtensions)) {
            $response['message'] = 'Invalid file type. Only JPG, JPEG, and PNG are allowed.';
            echo json_encode($response);
            exit;
        }

        // Set a unique image name and move the uploaded file
        $newImageName = uniqid() . '.' . $imageExtension;
        $imagePath = 'uploads/' . $newImageName;

        // Ensure the upload directory exists and is writable
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true); // Create the uploads directory if it doesn't exist
        }

        // Move the uploaded file to the uploads directory
        if (move_uploaded_file($imageTmpPath, $imagePath)) {
            // Full URL of the uploaded image
            $fullImageUrl = $baseUrl . $newImageName;

            // Save image URL to database (optional)
            $stmt = $conn->prepare("INSERT INTO images (image_url, uploaded_at) VALUES (?, NOW())");
            $stmt->bind_param("s", $fullImageUrl);
            $stmt->execute();
            $stmt->close();

            $response['success'] = true;
            $response['image_url'] = $fullImageUrl; // Return the full image URL
        } else {
            $response['message'] = 'Failed to save image.';
        }
    } else {
        $response['message'] = 'No image uploaded or error in upload.';
    }

    $conn->close();
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>
