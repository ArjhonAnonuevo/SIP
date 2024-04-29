 <?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

// Hash the default password "qwerty" using password_hash()
$defaultPassword = "qwerty";
$hashedDefaultPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);

// Fetch the hashed password from the session 
$hashedPassword = $_SESSION["hashed_password"]; 

// Check if the hashed password matches the hashed default password
if (password_verify($defaultPassword, $hashedPassword)) {
    // Passwords match
    $success = true;
} else {
    // Passwords do not match
    $success = false;
}

// Prepare the response data
$response = array(
    'success' => $success
);

// Send the JSON response
header('Content-Type: application/json');
echo json_encode($response);