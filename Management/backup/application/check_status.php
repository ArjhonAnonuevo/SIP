<?php

include "../configuration/application_config.php";
$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($mysqli->connect_error) {
    die(json_encode(array("error" => "Connection failed: " . $mysqli->connect_error)));
}
$query = "SELECT status_name FROM status WHERE control_number = ?";

if ($stmt = $mysqli->prepare($query)) {
    // Define $controlNumber before binding it
    $controlNumber = $_GET['control_number']; 
    $stmt->bind_param("s", $controlNumber); 
    
    if ($stmt->execute()) {
        $stmt->bind_result($statusName);
        
        if ($stmt->fetch()) {
            echo json_encode(array("status_name" => $statusName));
        } else {
            echo json_encode(array("error" => "No results found"));
        }
    } else {
        echo json_encode(array("error" => "Query execution failed"));
    }
    $stmt->close();
} else {
    echo json_encode(array("error" => "Query preparation failed"));
}
$mysqli->close();

?>
