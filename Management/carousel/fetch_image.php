<?php
include '../configuration/interns_config.php';

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

// Query to fetch image file names from the database
$sql = "SELECT file_name FROM images";
$result = $mysqli->query($sql);

// Array to store image URLs
$imageUrls = array();

if ($result->num_rows > 0) {
    // Fetch image file names and construct full URLs
    while($row = $result->fetch_assoc()) {
        $fileName = $row["file_name"];
        $imageUrl = 'image-upload/' . $fileName; // Adjust path as needed
        $imageUrls[] = $imageUrl;
    }
}

$mysqli->close();

// Output image URLs as JSON
header('Content-Type: application/json');
echo json_encode($imageUrls);
?>
