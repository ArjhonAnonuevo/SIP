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

$controlNumber = $_GET['control_number'];

$sql = "SELECT * FROM application WHERE control_number = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $controlNumber);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode($row);
} else {
    echo json_encode(array());
}

$stmt->close();
$mysqli->close();

?>
