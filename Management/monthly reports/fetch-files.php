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
    // Convert upload date to DateTime object for formatting
    $uploadDateTime = new DateTime($uploadDate);
    $formattedDate = $uploadDateTime->format('F j, Y');

    $files[] = array(
        'file_name' => basename($filePath),
        'upload_date' => $formattedDate,
        'file_path' => $filePath
    );
}

$stmt->close();

$fileDirectory = "../monthly-uploads/";
$directoryFiles = glob($fileDirectory . '*');

foreach ($directoryFiles as $file) {
    $uploadDate = filemtime($file); 

    // Convert upload date to DateTime object for formatting
    $uploadDateTime = new DateTime('@' . $uploadDate);
    $formattedDate = $uploadDateTime->format('F j, Y');

    // Add file to the response
    $files[] = array(
        'file_name' => basename($file),
        'upload_date' => $formattedDate, 
        'file_path' => $file
    );
}

$mysqli->close();

header('Content-Type: application/json');
echo json_encode($files);
?>
