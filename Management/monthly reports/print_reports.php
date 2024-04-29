<?php
session_start();
header('Content-Type: text/html');

include '../configuration/interns_config.php';

$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];
$connection = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$monthNames = [
    "01" => "January", "02" => "February", "03" => "March", "04" => "April", 
    "05" => "May", "06" => "June", "07" => "July", "08" => "August", 
    "09" => "September", "10" => "October", "11" => "November", "12" => "December"
];


if (isset($_POST['selectedMonth'])) {
    $selectedMonth = $_POST['selectedMonth'];
    $username = $_SESSION['username'];

    $selectedMonth = $connection->real_escape_string($selectedMonth);
    $username = $connection->real_escape_string($username);

    $stmt = $connection->prepare("SELECT CONCAT(p.name, ' ', p.mname, ' ', p.lname) AS fullname, p.department FROM interns_profile p INNER JOIN interns_account a ON p.id = a.profile_id WHERE a.username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $name = $row['fullname'];
    $department = $row['department'];

    $sql = "SELECT DATE_FORMAT(date, '%M %e, %Y') AS formatted_date, type, TIME_FORMAT(time, '%h:%i %p') AS formatted_time, status FROM acomplisment_report WHERE MONTH(date) = ? AND user_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param('ss', $selectedMonth, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Start printing content
    echo '<!DOCTYPE html>
    <html>
    <head>
        <style>
        th {
            -webkit-print-color-adjust: exact;
            padding: 5px;
            font-family: "Helvetica", sans-serif;
        }
        td {
            font-family: "Courier New", Courier, monospace;
            font-size: 14px; 
            padding: 4px; 
            text-align: center;
        }
        #header{
            text-align:center;
            font-family: "Helvetica", sans-serif;
            font-size: 20px; 
        }
        #name,#department,#hours{
            font-family: "Helvetica", sans-serif;
            font-size: 15px; 
        }
        #label {
            font-family: "Helvetica", sans-serif;
            font-weight: 600;
            float: right;
        }

        #secondHeader{
            text-align:center;
            font-family: "Helvetica", sans-serif;
            font-size: 17px; 
            margin-top: 20px;
        }
        #nameContainer {
            display: flex;
            justify-content: space-between;
        }
        #monthLabel {
            font-family: "Helvetica", sans-serif;
            font-size: 15px; 
        }
        #logo {
            width: 100px;
            height: auto;
            float: left;
            margin-right: 20px;
            margin-bottom: 20px;
        }
        #header, #secondHeader {
            clear: both; 
        }
        #nameContainer {
            margin-top: 20px; 
        }
        </style>
    </head>
    <body>';

    echo "<img id='logo' src='../images/sec_logo.png' alt='Logo'>";
    echo "<h1 id='header'>Monthly Reports</h1>";
    echo "<div id='nameContainer'>";
    echo "<div>";
    echo "<h1 id='name'>Name: $name</h1>";
    echo "<h1 id='department'>Department: $department</h1>";
    echo "</div>";
    echo "<h1 id='monthLabel'>Month: $monthNames[$selectedMonth]</h1>";
    echo "</div>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'><tr>
    <th style='background-color: #2F855A; color: white; padding: 5px; text-align: center;'>Date</th>
    <th style='background-color: #2F855A; color: white; padding: 5px; text-align: center;'>Type</th>
    <th style='background-color: #2F855A; color: white; padding: 5px; text-align: center;'>Time</th>
    <th style='background-color: #2F855A; color: white; padding: 5px; text-align: center;'>Status</th>
    </tr>";
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['formatted_date']) . '</td>';
        echo '<td>' . htmlspecialchars($row['type']) . '</td>';
        echo '<td>' . htmlspecialchars($row['formatted_time']) . '</td>';
        echo '<td>' . htmlspecialchars($row['status']) . '</td>';
        echo '</tr>';
    }
    echo '</table></body></html>';

    $stmt->close();
    $connection->close();
} else {
    echo '<p>No selected month received.</p>';
}
?>
