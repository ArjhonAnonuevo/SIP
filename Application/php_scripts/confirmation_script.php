<?php
session_start();
require_once "../configuration/application_config.php";
$dbConfig = getDatabaseConfig();

$conn = new mysqli($dbConfig['dbhost'], $dbConfig['dbuser'], $dbConfig['dbpass'], $dbConfig['dbname']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the session variable for email exists
if (isset($_SESSION["primary_email"])) {
    $user_email = $_SESSION["primary_email"];

    // Prepare a SELECT statement to fetch the control number
    $sql_fetch_control_number = "SELECT given_name, family_name, control_number FROM application WHERE primary_email = ?";
    $stmt_fetch_control_number = $conn->prepare($sql_fetch_control_number);

    // Check if the statement was prepared successfully
    if ($stmt_fetch_control_number) {
        // Bind parameters and execute the statement
        $stmt_fetch_control_number->bind_param("s", $user_email);
        $stmt_fetch_control_number->execute();

        // Get the result and fetch the data
        $result_fetch_control_number = $stmt_fetch_control_number->get_result();

        if ($result_fetch_control_number->num_rows > 0) {
            $row = $result_fetch_control_number->fetch_assoc();
            $data = [
                'given_name' => $row["given_name"] ?? '',
                'family_name' => $row["family_name"] ?? '',
                'control_number' => $row["control_number"] ?? '',
                'email' => $_SESSION["primary_email"],
            ];
        } else {
            // Handle no rows found if needed
            $data = [];
        }

        $stmt_fetch_control_number->close();
    } else {
        $data = ['error' => 'Error preparing statement: ' . $conn->error];
    }
} else {
    // Handle the case where the session variable is not set
    $data = ['error' => 'Session variable not set'];
}

$conn->close();

// Output JSON data
header('Content-Type: application/json');
echo json_encode($data);
?>
