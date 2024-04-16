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

$username = $_GET['username'];
$fileDirectory = "../monthly-uploads/";

// Get files from the database
$query = "SELECT file_path, upload_date FROM reports_uploaded WHERE username = ?";
$stmt = $mysqli->prepare($query);

if ($stmt === false) {
    $errorResponse = array('error' => 'Error preparing statement: ' . $mysqli->error);
    header('Content-Type: application/json');
    echo json_encode($errorResponse);
    $mysqli->close();
    exit();
}

$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($filePath, $uploadDate);

$files = array();

while ($stmt->fetch()) {
    $files[] = array(
        'file_name' => basename($filePath), // Get only the file name
        'upload_date' => $uploadDate,
        'file_path' => $filePath
    );
}

$stmt->close();

// Get files from the directory
$directoryFiles = scandir($_SERVER['DOCUMENT_ROOT'] . $fileDirectory);

foreach ($directoryFiles as $file) {
    if ($file !== '.' && $file !== '..') {
        // Add file to the response
        $files[] = array(
            'file_name' => $file,
            'upload_date' => null, // For files from the directory, upload date is not available
            'file_path' => $fileDirectory . $file
        );
    }
}

$mysqli->close();

header('Content-Type: application/json');
echo json_encode($files);
?>
