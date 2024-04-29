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

// Check if the form data is received via POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the required data (reset token and username) is present in the POST request
    if (isset($_POST["resetToken"]) && isset($_POST["username"])) {
        $resetToken = $_POST["resetToken"];
        $username = $_POST["username"];

        $sql = "SELECT reset_token FROM interns_account WHERE username = ?";
        $stmt = $mysqli->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->bind_result($dbResetToken);

            $stmt->fetch();
            $stmt->close();

            if ($dbResetToken === $resetToken) {
                echo "Token validation successful";
            } else {
                echo "Invalid token. Please try again.";
            }
        } else {
            echo "Error: Unable to prepare SQL statement.";
        }
    } else {
        echo "Missing data. Please provide both reset token and username.";
    }
} else {
    echo "Invalid request method. Please use POST method.";
}

$mysqli->close();
?>
