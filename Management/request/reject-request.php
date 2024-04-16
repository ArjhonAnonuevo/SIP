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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["requestId"]) && isset($_POST["username"])) {

        $requestId = $_POST["requestId"];
        $username = $_POST["username"];
        $message = "Your request has been rejected.";

        // Generate a random numeric ID
        $notificationId = mt_rand(1000000000, 9999999999);

        // Removed 'status' field from the INSERT statement
        $sql = "INSERT INTO notifications (id, username, message) VALUES ('$notificationId', '$username', '$message')";

        if ($mysqli->query($sql) === TRUE) {
            $deleteSql = "DELETE FROM attendance_request  WHERE id = '$requestId'";

            if ($mysqli->query($deleteSql) === TRUE) {
                $response = array(
                    "status" => "success",
                    "message" => "Notification inserted and record deleted successfully",
                    "notificationId" => $notificationId
                );
            } else {
                $response = array(
                    "status" => "error",
                    "message" => "Error deleting record: " . $mysqli->error
                );
            }
        } else {
            $response = array(
                "status" => "error",
                "message" => "Error inserting notification: " . $mysqli->error
            );
        }

        echo json_encode($response);
    } else {
        $response = array(
            "status" => "error",
            "message" => "Request ID or username is missing"
        );

        echo json_encode($response);
    }
} else {
    $response = array(
        "status" => "error",
        "message" => "Invalid request method"
    );

    echo json_encode($response);
}
?>
