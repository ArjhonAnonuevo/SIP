<?php
require_once "../configuration/interns_config.php";

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

// Retrieve the username from the session
session_start();
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

$selectedMonth = $_GET['month'];

// Perform a query to fetch tables for the selected month using prepared statement
$query = "SELECT * FROM acomplisment_report WHERE MONTH(date) = ? AND user_id = ?";
$stmt = $connection->prepare($query);

if ($stmt) {
    $stmt->bind_param("ss", $selectedMonth, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<div class='w-full overflow-x-auto justify-center'>";
        echo "<table class='w-full table-auto shadow-lg bg-white border-collapse max-w-sm'>";
        echo "<thead class='bg-green-700 text-white'>";
        echo "    <tr>";
        echo "        <th class='px-6 py-3 border border-solid border-blueGray-100 whitespace-nowrap font-bold text-left tracking-wider md:break-normal lg:break-normal xl:break-normal' style='font-family: \"Caudex\", serif;'>Date</th>";
        echo "        <th class='px-6 py-3 border border-solid border-blueGray-100 whitespace-nowrap font-bold text-left tracking-wider md:break-normal lg:break-normal xl:break-normal' style='font-family: \"Caudex\", serif;'>Type</th>";
        echo "        <th class='px-6 py-3 border border-solid border-blueGray-100 whitespace-nowrap font-bold text-left tracking-wider md:break-normal lg:break-normal xl:break-normal' style='font-family: \"Caudex\", serif;'>Time</th>";
        echo "        <th class='px-6 py-3 border border-solid border-blueGray-100 whitespace-nowrap font-bold text-left tracking-wider md:break-normal lg:break-normal xl:break-normal' style='font-family: \"Caudex\", serif;'>Status</th>";
        echo "    </tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($row = $result->fetch_assoc()) {
            $date = new DateTime($row["date"]);
            $time = new DateTime($row["time"]);
            $formattedDate = $date->format('F d, Y');

            echo "<tr class='hover:bg-gray-50 focus:bg-gray-300 active:bg-red-200' tabindex='0'>";
            echo "<td class='border px-6 py-3 max-w-sm md:break-normal lg:break-normal xl:break-normal overflow-y-auto text-md whitespace-nowrap overflow-ellipsis' style='font-family: Maitree, sans-serif;'>$formattedDate</td>";
            echo "<td class='border px-6 py-3 max-w-sm md:break-normal lg:break-normal xl:break-normal overflow-y-auto text-md whitespace-nowrap overflow-ellipsis' style='font-family: Maitree, sans-serif;'>" . $row["type"] . "</td>";
            echo "<td class='border px-6 py-3 md:break-normal lg:break-normal xl:break-normal' style='font-family: Maitree, sans-serif;'>" . $time->format('h:i a') . "</td>";
            echo "<td class='border px-6 py-3 md:break-normal lg:break-normal xl:break-normal' style='font-family: Maitree, sans-serif;'>" . $row["status"] . "</td>";
            echo "</tr>";
        }

        echo "</tbody></table></div>";
    } else {
        echo "<div class='text-center items-center justify-center' style='font-family: Maitree, sans-serif;'>No records found.</div>";
    }

    $stmt->close();
} else {
    // Handle the case where the prepared statement could not be created
    die('Error in preparing the statement: ' . $connection->error);
}

// Close the database connection
$connection->close();
?>
