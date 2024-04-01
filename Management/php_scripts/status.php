<?php

require_once '../configuration/interns_config.php';

// Call the getDatabaseConfig function
$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch names, usernames, and status from server_status table
$query = "SELECT interns_profile.name, interns_profile.mname, interns_profile.lname,  interns_profile.department, interns_account.username, server_status.id, server_status.status, server_status.interns_status 
          FROM interns_profile 
          INNER JOIN interns_account ON interns_profile.id = interns_account.profile_id 
          INNER JOIN server_status ON interns_account.username = server_status.username";

$result = $conn->query($query);

$response = array();

if ($result === false) {
    die("Query execution failed: " . $conn->error);
}

if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        $mname_initial = mb_substr($row["mname"], 0, 1, "UTF-8");
        $mname_initial = mb_convert_case($mname_initial, MB_CASE_UPPER, "UTF-8");

        $lname = $row["lname"];

        $name = $row["name"] . " " . $mname_initial . ". " . $lname;

        $response[] = array(
            "name" => $name,
            "id" => $row["id"], 
            "department" => $row["department"],
            "username" => $row["username"],
            "status" => $row["status"],
            "interns_status" => $row["interns_status"]
        );
    }
} else {
    $response["message"] = "0 results";
}

$conn->close();

echo json_encode($response, JSON_UNESCAPED_UNICODE); 
?>

