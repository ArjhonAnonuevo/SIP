<?php
session_start();
require_once "../configuration/application_config.php";

// Define the status colors
$statusColors = [
    'Approved' => 'bg-green-500',
    'Pending' => 'bg-orange-500',
    'Rejected' => 'bg-red-500',
    'On Process' => 'bg-yellow-500'
];

// Database configuration
$dbConfig = getDatabaseConfig();

// Create database connection
$conn = new mysqli($dbConfig['dbhost'], $dbConfig['dbuser'], $dbConfig['dbpass'], $dbConfig['dbname']);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed");
}

// Set the character set to utf8mb4
$conn->set_charset("utf8mb4");

// Retrieve control number from session
$controlNum = isset($_SESSION["control_number"]) ? $_SESSION["control_number"] : '';

// SQL query
$sql = "SELECT status.status_name, application.primary_email, application.given_name, application.middle_name, application.family_name
        FROM status
        JOIN application ON status.control_number = application.control_number   
        WHERE status.control_number = ?";

// Prepare and execute statement
$stmt = $conn->prepare($sql);

// Check for statement preparation error
if ($stmt === false) {
    die("Error preparing statement");
}

// Bind parameters and execute statement
$stmt->bind_param("s", $controlNum);

// Check for statement execution error
if ($stmt->execute() === false) {
    die("Error executing statement");
}

// Store result
$stmt->store_result();

$responseData = [];

// Process query results
if ($stmt->num_rows > 0) {
    $stmt->bind_result($status, $primaryEmail, $given_name, $middle_name, $family_name);
    $stmt->fetch();

    $colorClass = $statusColors[$status] ?? 'bg-gray-500';

    $responseData = [
        'statusResult' => $status,
        'colorClass' => $colorClass,
        'statusColorClass' => 'status-' . strtolower(str_replace(' ', '-', $status)),
        'name' => "$given_name $middle_name $family_name",
        'primaryEmail' => $primaryEmail,
        'queryResults' => [
            'status_name' => $status,
            'primary_email' => $primaryEmail,
            'given_name' => $given_name,
            'middle_name' => $middle_name,
            'family_name' => $family_name,
        ],
    ];

    // Store data in session
    $_SESSION['status_result'] = $status;
    $_SESSION['primary_email'] = $primaryEmail;
    $_SESSION['given_name'] = $given_name;
    $_SESSION['middle_name'] = $middle_name;
    $_SESSION['family_name'] = $family_name;
} else {
    // Handle no matching record
    $_SESSION['status_result'] = "No matching record found for the provided Control Number.";

    $responseData = [
        'errorMessage' => $_SESSION['status_result'],
        'statusResult' => 'No matching record',
        'colorClass' => 'bg-gray-500',
        'statusColorClass' => '',
        'name' => '',
        'queryResults' => null,
    ];
}

// Close statement and connection
$stmt->close();
$conn->close();

// Set response headers
header('Content-Type: application/json');

// Use json_last_error() to check for errors during json_encode
$response = json_encode($responseData, JSON_UNESCAPED_UNICODE);
if ($response === false || json_last_error() != JSON_ERROR_NONE) {
    $response = json_encode([
        'errorMessage' => 'Error encoding JSON',
        'statusResult' => 'Unknown',
        'colorClass' => 'bg-gray-500',
        'statusColorClass' => '',
        'name' => '',
        'queryResults' => null,
    ]);
}

// Output the response
echo $response;

// Stop script execution here to prevent any further output
exit;
?>
