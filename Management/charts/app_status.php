<?php
require_once "../configuration/application_config.php";

// Call the getDatabaseConfig function
$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$connection = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Get the year from the query string or request body
$year = isset($_GET['year']) ? $_GET['year'] : date('Y'); // Default to current year if not provided

// Initialize an array to store data per month
$dataPerMonth = array();

// Fetch data from the database for the specified year
$query = "SELECT status_name, YEAR(last_updated_date) AS year, MONTH(last_updated_date) AS month, count FROM application_status_counts WHERE YEAR(last_updated_date) = ? ORDER BY year, month";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $year);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    // Process the result and organize data per month
    while ($row = $result->fetch_assoc()) {
        $year = $row['year'];
        $month = $row['month'];
        $status = $row['status_name'];
        $count = $row['count'];

        // Create an array key for the month and year if it doesn't exist
        $key = "$year-$month";
        if (!array_key_exists($key, $dataPerMonth)) {
            $dataPerMonth[$key] = array(
                'year' => $year,
                'month' => $month,
                'statuses' => array()
            );
        }

        // Add status count to the respective month and year
        $dataPerMonth[$key]['statuses'][$status] = $count;
    }

    // Free result set
    $result->free();
} else {
    echo "Error: " . $connection->error;
}

// Close statement
$stmt->close();

// Close connection
$connection->close();

// Output the data in JSON format for use in the chart
echo json_encode(array_values($dataPerMonth));
?>
