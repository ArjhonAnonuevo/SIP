<?php
include 'configuration/interns_config.php';

$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if (isset($_POST['caption'], $_FILES['image']['tmp_name'])) {
    $caption = $_POST['caption'];
    $imageData = file_get_contents($_FILES['image']['tmp_name']); 

    $query = "INSERT INTO carousel (image_data, caption) VALUES (?, ?)";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('ss', $imageData, $caption); 

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
