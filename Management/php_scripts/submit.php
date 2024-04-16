<?php
$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $fname = $_POST["fname"];
    $mname = $_POST["mname"];
    $lname = $_POST["lname"];

    include '../configuration/interns_config.php';

    // Call the getDatabaseConfig function
    $config = getDatabaseConfig();

    $dbhost = $config['dbhost'];
    $dbuser = $config['dbuser'];
    $dbpass = $config['dbpass'];
    $dbname = $config['dbname'];

    $conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

    if ($conn->connect_error) {
        $response["error"] = "Connection failed: " . $conn->connect_error;
    } else {
        if (!empty($username) && !empty($fname) && !empty($mname) && !empty($lname)) {
            $sql = "INSERT INTO interns (interns_username, fname, mname, lname) VALUES ('$username', '$fname', '$mname', '$lname')";

            if ($conn->query($sql) === TRUE) {
                $response["success"] = "Username and name inserted successfully";
            } else {
                $response["error"] = "Error inserting data: " . $conn->error;
            }
        } else {
            $response["error"] = "Please fill in all the fields.";
        }

        $conn->close();
    }
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
