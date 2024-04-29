<?php
require '../configuration/interns_config.php';

// Call the getDatabaseConfig function
$config = getDatabaseConfig();

// Extract database connection parameters
$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

// Create connection
$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Start session to access session variables
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if (isset($_FILES['reportsFile']) && $_FILES['reportsFile']['error'] === UPLOAD_ERR_OK) {
    // File upload path
    $uploadDirectory = "../monthly-uploads/";
    $filename = $_FILES['reportsFile']['name'];
    $tempPath = $_FILES['reportsFile']['tmp_name'];
    $filePath = $uploadDirectory . $filename;

    // Move uploaded file to designated directory
    if (move_uploaded_file($tempPath, $filePath)) {
        $sql = "INSERT INTO reports_uploaded (file_path, username) VALUES (?, ?)";

        // Prepare and bind parameters
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ss", $filePath, $_SESSION['username']);

        // Execute the statement
        if ($stmt->execute()) {
            echo "File uploaded successfully!";
        } else {
            echo "Error uploading file: " . $mysqli->error;
        }

        // Close statement
        $stmt->close();
    } else {
        echo "Error moving file to destination directory";
    }
} else {
    echo "No file uploaded or upload error occurred";
}

// Close connection
$mysqli->close();
?>
