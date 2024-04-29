<?php
require '../configuration/interns_config.php';

$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

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
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ss", $filePath, $_SESSION['username']);

        if ($stmt->execute()) {
            // Insert into audits table
            $action = "Upload File";
            $log = "The Intern with " . $_SESSION['username'] . " user ID uploads Monthly reports Files";
            $role = "Intern";

            $query_audits = "INSERT INTO audits (actions, logs, audit_timestamp, role) VALUES (?, ?, NOW(), ?)";
            $stmt_audits = $mysqli->prepare($query_audits);
            $stmt_audits->bind_param("sss", $action, $log, $role);

            if ($stmt_audits->execute()) {
                echo "File uploaded successfully!";
            } else {
                echo "Error inserting into audits table: " . $mysqli->error;
            }
        } else {
            echo "Error uploading file: " . $mysqli->error;
        }

        $stmt->close();
        $stmt_audits->close();
    } else {
        echo "Error moving file to destination directory";
    }
} else {
    echo "No file uploaded or upload error occurred";
}

$mysqli->close();
?>
