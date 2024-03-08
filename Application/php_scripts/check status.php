<?php

session_start(); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $control_num = $_POST['control_num'];
    require_once "../configuration/application_config.php";
    $dbConfig = getDatabaseConfig();
    
    $conn = new mysqli($dbConfig['dbhost'], $dbConfig['dbuser'], $dbConfig['dbpass'], $dbConfig['dbname']);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Query to select data based on the control number
    $sql = "SELECT control_number FROM application WHERE control_number = '$control_num'";

    $result = $conn->query($sql);

    // Check if any rows are returned
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Store the control_number in a session
            $_SESSION['control_number'] = $row['control_number'];
            
            // Redirect to status.html
            header("Location: ../application forms/status.html");
            exit(); // Make sure to exit after header redirection
        }
    } else {
        echo "No results found for the given control number.";
    }

    // Close the database connection
    $conn->close();
}
?>
