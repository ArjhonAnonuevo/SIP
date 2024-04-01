<?php
session_start();
include '../configuration/interns_config.php';

// Call the getDatabaseConfig function
$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$connection = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

if (isset($_GET['username'])) {
    $username = $_GET['username'];

    // Prepare SQL query to calculate total rendered hours for the specified username
    $stmt = $connection->prepare("SELECT SUM(TIME_TO_SEC(rendered_hours)) AS total_rendered_seconds FROM attendance WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch the total rendered hours in seconds
        $row = $result->fetch_assoc();
        $totalRenderedSeconds = $row['total_rendered_seconds'];

        // Convert total seconds to hours
        $totalRenderedHours = floor($totalRenderedSeconds / 3600); // 3600 seconds = 1 hour

        // Prepare JSON response
        $response = array('total_rendered_hours' => $totalRenderedHours);
    } else {
        // Handle case where no data found for the provided username
        $response = array('total_rendered_hours' => 0);
    }

    // Output JSON response
    header('Content-Type: application/json');
    echo json_encode($response);

    // Close the prepared statement and database connection
    $stmt->close();
    $connection->close();
} else {
    // Handle case where username is not provided
    $response = array('error' => 'Username not provided');
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
