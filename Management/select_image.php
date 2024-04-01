<?php
include 'configuration/interns_config.php';

$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Query to fetch image file names and file paths from the database
$sql = "SELECT file_name, file_path FROM images";
$result = $mysqli->query($sql);

// Array to store image data (file names and paths)
$imageData = array();

if ($result->num_rows > 0) {
    // Fetch image file names and paths
    while($row = $result->fetch_assoc()) {
        $fileName = $row["file_name"];
        $filePath = $row["file_path"];
        // Construct full URL using file path and name
        $imageUrl = $filePath . '/' . $fileName; 
        // Add image data to the array
        $imageData[] = array(
            'fileName' => $fileName,
            'filePath' => $filePath,
            'imageUrl' => $imageUrl
        );
    }
}

$mysqli->close();

// Output image data as JSON
header('Content-Type: application/json');
echo json_encode($imageData);
?>
