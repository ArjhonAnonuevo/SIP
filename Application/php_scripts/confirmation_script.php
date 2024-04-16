<?php
session_start();

require_once "../configuration/application_config.php";
$dbConfig = getDatabaseConfig();

// Check if database configuration exists
if (!$dbConfig) {
    $data = ["error" => "Database configuration not found"];
    outputJson($data);
}

// Attempt to establish a database connection
$conn = new mysqli($dbConfig['dbhost'], $dbConfig['dbuser'], $dbConfig['dbpass'], $dbConfig['dbname']);

// Check for database connection error
if ($conn->connect_error) {
    $data = ["error" => "Connection failed: " . $conn->connect_error];
    outputJson($data);
}

// Check if the necessary session variables are set
if (isset($_SESSION["random_control_number"], $_SESSION["given_name"], $_SESSION["family_name"], $_SESSION["primary_email"])) {
    $controlNumber = $_SESSION["random_control_number"];
    $givenName = $_SESSION["given_name"];
    $familyName = $_SESSION["family_name"];
    $email = $_SESSION["primary_email"];

    // Prepare SQL statement to fetch data from the database
    $sql = "SELECT given_name, family_name, primary_email FROM application WHERE control_number = ?";
    $stmt = $conn->prepare($sql);

    // Check if the SQL statement is prepared successfully
    if ($stmt) {
        // Bind parameters and execute the statement
        $stmt->bind_param("s", $controlNumber);
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();

        // Fetch data
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $data = [
                "given_name" => $row["given_name"],
                "family_name" => $row["family_name"],
                "control_number" => $controlNumber,
                "email" => $row["primary_email"]
            ];
        } else {
            $data = ["error" => "No data found in the database for the provided control number"];
        }

        // Close statement
        $stmt->close();
    } else {
        $data = ["error" => "Error preparing SQL statement: " . $conn->error];
    }
} else {
    $data = ["error" => "Required session variables are not set"];
}

// Close connection
$conn->close();

// Output JSON data along with values if available
outputJson($data);

// Function to output JSON data and terminate script execution
function outputJson($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>
