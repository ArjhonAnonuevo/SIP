<?php
include "../configuration/interns_config.php";
$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($mysqli->connect_error) {
    die(json_encode(array("error" => "Connection failed: " . $mysqli->connect_error)));
}

// Check if username parameter is set
if(isset($_GET['username'])) {
    $username = $_GET['username'];

    $sql = "SELECT file_path, created_at, filename FROM attendance_submissions WHERE username = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    $files = array();
    while($row = $result->fetch_assoc()) {
        $row['file_path'] = '../attendance-uploads/' . $row['file_path'];
        $files[] = $row;
    }

    // Prepare response data
    $response = array();
    if($files) {
        $response['success'] = true;
        $response['data'] = $files;
    } else {
        $response['success'] = false;
        $response['message'] = 'No attendance files found for the given username.';
    }

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // If username parameter is not set, send error response
    $response['success'] = false;
    $response['message'] = 'Username parameter is missing.';
    echo json_encode($response);
}
?>
