<?php
require '../configuration/interns_config.php';
$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if(isset($_GET['interviewType'])){
    $interviewType = $_GET['interviewType'];

    // Prepare the SQL query
    $query = "SELECT template FROM interview_templates WHERE interview_type = ?";
    $stmt = $mysqli->prepare($query);

    // Check if the prepare() function succeeded
    if (!$stmt) {
        die("Prepare failed: " . $mysqli->error);
    }

    // Bind parameters and execute the statement
    $stmt->bind_param('s', $interviewType);
    $stmt->execute();

    // Bind result variables
    $stmt->bind_result($template);

    // Fetch the result
    $result = array();
    while ($stmt->fetch()) {
        $result[] = array(
            'template' => $template
        );
    }

    $stmt->close();

    // Output the result as JSON
    header('Content-Type: application/json');
    echo json_encode($result);
} else {
    // Handle the case where interviewType is not set in the GET request
    $response = array('error' => 'interviewType is not set in the GET request');
    header('HTTP/1.1 400 Bad Request');
    echo json_encode($response);
}

$mysqli->close();
?>
