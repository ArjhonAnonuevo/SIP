<?php
include '../configuration/interns_config.php';
  $config = getDatabaseConfig();
            
            $dbhost = $config['dbhost'];
            $dbuser = $config['dbuser'];
            $dbpass = $config['dbpass'];
            $dbname = $config['dbname'];

$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT ip.name, ip.mname, ip.lname, ip.department, ia.username
FROM interns_profile ip
JOIN interns_account ia ON ia.profile_id = ip.id";
$result = $conn->query($sql);

$data = [];

if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'name' => $row['name'] . " " . $row['mname'] . " " . $row['lname'],
            'department' => $row['department'],
            'username' => $row['username']
        ];
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($data);
?>
