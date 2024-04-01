<?php
include 'configuration/interns_config.php';

$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

// Initialize response array
$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["image"])) {
    // Directory where uploaded files will be stored
    $uploadDir = 'image-upload/';

    // Get the file name
    $fileName = $_FILES["image"]["name"];
    
    // Get the path to store the uploaded file
    $targetFilePath = $uploadDir . $fileName;

    // Check if file already exists
    if (file_exists($targetFilePath)) {
        $response['success'] = false;
        $response['message'] = "Sorry, file already exists.";
    } else {
        // Upload file
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {

            $fileName = $mysqli->real_escape_string($fileName);
            $targetFilePath = $mysqli->real_escape_string($targetFilePath);

            $sql = "INSERT INTO images (file_name, file_path) VALUES ('$fileName', '$targetFilePath')";

            if ($mysqli->query($sql) === TRUE) {
                $response['success'] = 'success';
                $response['message'] = 'Image Inserted successfully';
            } else {
                $response['success'] = false;
                $response['message'] = "Error: " . $sql . "<br>" . $mysqli->error;
            }
        } else {
            $response['success'] = false;
            $response['message'] = "Sorry, there was an error uploading your file.";
        }
    }
} else {
    $response['success'] = false;
    $response['message'] = "Invalid request.";
}

$mysqli->close();

// Set response headers
header('Content-Type: application/json');

// Output response as JSON
echo json_encode($response);
?>
