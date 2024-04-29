<?php
include '../configuration/interns_config.php';

$config = getDatabaseConfig();
$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check if the form data has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $newPassword = $_POST['newPassword'];

    // Hash the new password (you should use a secure hashing algorithm)
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $stmt = $mysqli->prepare("UPDATE interns_account SET password = ? WHERE username = ?");
    $stmt->bind_param("ss", $hashedPassword, $username);

    if ($stmt->execute()) {
        echo "Password changed successfully!";
    } else {
        echo "Error changing password. Please try again.";
    }

    $stmt->close();
    $mysqli->close();
}
?>
