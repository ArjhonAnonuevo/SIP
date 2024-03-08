<?php
session_start();
// Check if the username is stored in the session
if (isset($_SESSION["username"])) {
    $username = $_SESSION["username"];
} else {
    // Handle the case when the username is not available
    $username = "Unknown";
}

echo $username; 
?>
