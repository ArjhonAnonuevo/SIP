<?php
require_once "../configuration/interns_config.php";

// Call the getDatabaseConfig function
$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$connection = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$sql = "SELECT status AS label, count AS data FROM intern_status_counts";

$result = $connection->query($sql);

if ($result) {
    $data = $result->fetch_all(MYSQLI_ASSOC);
    
    $json_data = json_encode($data);
    
    echo $json_data;
} else {
    echo "Error: " . $sql . "<br>" . $connection->error;
}

$connection->close();
?>
