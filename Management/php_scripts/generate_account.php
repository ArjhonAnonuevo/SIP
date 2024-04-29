<?php

include "../configuration/interns_config.php";
$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($mysqli->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $mysqli->connect_error]));
}

function getLatestUsernameFormat($mysqli)
{
    $query = "SELECT username 
FROM interns_account 
WHERE username LIKE CONCAT('SIP-', YEAR(CURRENT_DATE()), '-%') 
ORDER BY CAST(SUBSTRING_INDEX(username, '-', -1) AS UNSIGNED) DESC 
LIMIT 1
";
    $result = $mysqli->query($query);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['username'];
    } else {
        return '';
    }
}

function generateNewUsername($latestUsername)
{
    // Extract the year and number from the latest username
    preg_match('/SIP-(\d+)-(\d{1,4})/', $latestUsername, $matches);
    $year = isset($matches[1]) ? intval($matches[1]) : date('Y');
    $number = isset($matches[2]) ? intval($matches[2]) + 1 : 1;

    // Pad the number with leading zeros
    $paddedNumber = str_pad($number, 4, '0', STR_PAD_LEFT);

    // Generate the new username format
    return "SIP-$year-$paddedNumber";
}

// Get the latest username format from the database
$latestUsernameFormat = getLatestUsernameFormat($mysqli);

// Generate a new username with the format "SIP-year-number"
$newUsername = generateNewUsername($latestUsernameFormat);

// Return the new username
echo json_encode(["new_username" => $newUsername]);

?>
