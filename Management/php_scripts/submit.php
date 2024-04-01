<?php
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
        die("Connection failed: " . $conn->connect_error);
    }

    if (!empty($username) && !empty($fname) && !empty($mname) && !empty($lname)) {
        $sql = "INSERT INTO interns (interns_username, fname, mname, lname) VALUES ('$username', '$fname', '$mname', '$lname')";

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Username and name inserted successfully');";
            echo "window.location.href = 'add.php';</script>";
            exit;
        } else {
            echo "<script>alert('Error inserting data: " . $conn->error . "');</script>";
        }
    } else {
        echo "<script>alert('Please fill in all the fields.');</script>";
    }

    $conn->close();
}
?>
