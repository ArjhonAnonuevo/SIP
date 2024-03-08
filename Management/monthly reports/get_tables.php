
<?php
include "db config.php";
$connection = new mysqli($db_host, $db_user, $db_pass, $db_name);
  if ($connection->connect_error) {
      die("Connection failed: " . $connection->connect_error);
  }

$selectedMonth = $_GET['month'];

// Perform a query to fetch tables for the selected month
$query = "SELECT * FROM acomplisment_report WHERE MONTH(date) = $selectedMonth";
$result = $connection->query($query);

if ($result->num_rows > 0) {
    $lastDate = "";
    $tables = [];

    while ($row = $result->fetch_assoc()) {
        $date = new DateTime($row["date"]);
        $time = new DateTime($row["time"]);
        $formattedDate = $date->format('F d, Y');

        if (array_key_exists($formattedDate, $tables)) {
            $tables[$formattedDate] .= "<tr class='hover:bg-gray-50 focus:bg-gray-300 active:bg-red-200 md:max-w-lg' tabindex='0'>";
            $tables[$formattedDate] .= "<td class='border px-4 py-2 max-w-sm overflow-y-auto text-md whitespace-nowrap overflow-ellipsis' style='font-family: Maitree, sans-serif;'>" . $row["type"] . "</td>";
            $tables[$formattedDate] .= "<td class='border px-4 py-2' style='font-family: Maitree, sans-serif;'>" . $time->format('h:i a') . "</td>";
            $tables[$formattedDate] .= "<td class='border px-4 py-2' style='font-family: Maitree, sans-serif;'>" . $row["status"] . "</td>";
            $tables[$formattedDate] .= "</tr>";
        } else {
            if ($lastDate != "") {
                echo "</tbody></table></div>";
            }

            echo "<div class='block p-4 bg-opacity-40 backdrop-blur-md rounded-md shadow-sm md:max-w-md bg-green-700 mt-4'>";
            echo "<div class='p-4 w-38 h-12 mb-4 bg-white font-bold md:flex md:max-w-sm md:justify-center text-gray-800 shadow-md rounded-lg self-start' style='font-family: \"Caudex\", serif;'>";
            echo $formattedDate;
            echo "</div>";
            echo "<table class='w-full table-auto shadow-lg bg-white border-collapse md:max-w-sm'>";
            echo "<thead class='bg-blueGray-50 text-blueGray-500'>";
            echo "    <tr>";
            echo "        <th class='px-6 py-3 border border-solid border-blueGray-100 whitespace-nowrap font-bold text-left' style='font-family: \"Caudex\", serif;'>Type</th>";
            echo "        <th class='px-6 py-3 border border-solid border-blueGray-100 whitespace-nowrap font-bold text-left' style='font-family: \"Caudex\", serif;'>Time</th>";
            echo "        <th class='px-6 py-3 border border-solid border-blueGray-100 whitespace-nowrap font-bold text-left' style='font-family: \"Caudex\", serif;'>Status</th>";
            echo "    </tr>";
            echo "</thead>";
            echo "<tbody>";
            
            echo "<tr class='hover:bg-gray-50 focus:bg-gray-300 active:bg-red-200' tabindex='0'>";
            echo "<td class='border px-4 py-2 max-w-sm overflow-y-auto text-md whitespace-nowrap overflow-ellipsis' style='font-family: Maitree, sans-serif;'>" . $row["type"] . "</td>";
            echo "<td class='border px-4 py-2' style='font-family: Maitree, sans-serif;'>" . $time->format('h:i a') . "</td>";
            echo "<td class='border px-4 py-2' style='font-family: Maitree, sans-serif;'>" . $row["status"] . "</td>";
            echo "</tr></div></div>";

            $tables[$formattedDate] = ob_get_contents();
            ob_clean();
        }

        $lastDate = $formattedDate;
    }

    if ($lastDate != "") {
        echo "</tbody></table></div>";
    }

    foreach ($tables as $table) {
        echo $table;
    }
} else {

    echo "<tbody><tr><td colspan='3' class='text-center items-center justify-center' style='font-family: Maitree, sans-serif;'>No records found.</td></tr></tbody>";
}


// Close the database connection
$connection->close();
?>