<?php
require_once "../configuration/interns_config.php";
  
 $config = getDatabaseConfig();
            
            $dbhost = $config['dbhost'];
            $dbuser = $config['dbuser'];
            $dbpass = $config['dbpass'];
            $dbname = $config['dbname'];

$connection = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$query = "SELECT school, COUNT(*) as total FROM interns_profile GROUP BY school";

$result = $connection->query($query);

// Check for query errors
if (!$result) {
    die("Query failed: " . $connection->error);
}

// Loop through the returned data
$data = array();
foreach ($result as $row) {
    $data[] = $row;
}

$result->close();

$connection->close();
echo json_encode($data);
?>
