<?php
session_start();
include '../configuration/interns_config.php';

$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];
$connection = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

if (!isset($_SESSION["username"])) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

if (isset($_POST["newPassword"]) && isset($_POST["confirmPassword"])) {
    $newPassword = $_POST["newPassword"];
    $confirmPassword = $_POST["confirmPassword"];

    if ($newPassword === $confirmPassword) {
        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Prepare and execute the SQL query to update the password in the database
        $username = $_SESSION["username"];
        $sql = "UPDATE interns_account SET password = ? WHERE username = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param('ss', $hashedPassword, $username);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $_SESSION["hashed_password"] = $hashedPassword;
            $response = array(
                'success' => true
            );
        } else {
            $response = array(
                'success' => false,
                'message' => 'Password update failed'
            );
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    } else {
        $response = array(
            'success' => false,
            'message' => 'Passwords do not match'
        );
    }
} else {
    $response = array(
        'success' => false,
        'message' => 'New password or confirm password not provided'
    );
}

header('Content-Type: application/json');
echo json_encode($response);
