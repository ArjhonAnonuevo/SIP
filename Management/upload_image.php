<?php
include 'configuration/interns_config.php';

$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if (isset($_FILES['image']['tmp_name'])) {
    $file_name = $_FILES['image']['name'];
    $imageData = file_get_contents($_FILES['image']['tmp_name']); 

    $query = "INSERT INTO carousel (image_data, file_name) VALUES (?, ?)";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('ss', $imageData, $file_name); 

    $response = array();

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Image Inserted successfully.';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Error: ' . $stmt->error;
    }

    $stmt->close();
} else {
    $response['status'] = 'error';
    $response['message'] = 'Missing required data.';
}

$mysqli->close();

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
