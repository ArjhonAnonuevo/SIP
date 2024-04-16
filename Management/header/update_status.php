<?php
session_start();

require_once '../configuration/interns_config.php';

// Call the getDatabaseConfig function
$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if(isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    $updateQuery = "UPDATE server_status SET status = 'offline' WHERE username = '$username'";

    if ($conn->query($updateQuery) !== TRUE) {
        // Error updating status
        error_log("Error updating status: " . $conn->error);
    }
} else {
    error_log("Session username not set");
}

$conn->close();
?>