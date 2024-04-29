<!DOCTYPE html>
<html>
<head>
    <title>Print Attendance</title>
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
<body>
<?php
include '../configuration/interns_config.php';

$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$username = $_POST['username'];
$selectedMonth = $_POST['selectedMonth'];

$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$totalRenderedHours = 0;

$stmt = $conn->prepare("SELECT CONCAT(p.name, ' ', p.mname, ' ', p.lname) AS fullname, p.department FROM interns_profile p INNER JOIN interns_account a ON p.id = a.profile_id WHERE a.username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$name = $row['fullname'];
$department = $row['department'];

$stmt = $conn->prepare("SELECT morning_timein, lunch_timeout, after_lunch_timein, end_of_day_timeout, attendance_date, rendered_hours, overtime_hours FROM attendance WHERE username = ? AND MONTH(attendance_date) = ?");
$stmt->bind_param("si", $username, $selectedMonth);
$stmt->execute();
$result = $stmt->get_result();

$monthNames = [
    "01" => "January", "02" => "February", "03" => "March", "04" => "April", 
    "05" => "May", "06" => "June", "07" => "July", "08" => "August", 
    "09" => "September", "10" => "October", "11" => "November", "12" => "December"
];

$selectedMonthFormatted = sprintf("%02d", $selectedMonth);
$monthString = $monthNames[$selectedMonthFormatted] ?? 'Invalid Month';

?>
<?php
if ($result->num_rows > 0) {
    echo "<img id='logo' src='../images/sec_logo.png' alt='Logo'>";
    echo "<h1 id= 'header'>Attendance Report</h1>";
    echo "<div id='nameContainer'>";
    echo "<div>";
    echo "<h1 id = 'name'>Name: $name</h1>";
    echo "<h1 id = 'department'>Department: $department</h1>";
    echo "</div>";
    echo "<h1 id='monthLabel'>Month: $monthString</h1>";
    echo "</div>";
    echo "<h1 id = 'secondHeader'>Attendance Data</h1>";

    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>
            <tr>
                <th style='background-color: #2F855A; color: white; padding: 5px; text-align: center;'>Morning Time In</th>
                <th style='background-color: #2F855A; color: white; padding: 5px; text-align: center;'>Lunch Time Out</th>
                <th style='background-color: #2F855A; color: white; padding: 5px; text-align: center;'>After Lunch Time In</th>
                <th style='background-color: #2F855A; color: white; padding: 5px; text-align: center;'>End of Day Time Out</th>
                <th style='background-color: #2F855A; color: white; padding: 5px; text-align: center;'>Attendance Date</th>
                <th style='background-color: #2F855A; color: white; padding: 5px; text-align: center;'>Rendered Hours</th>
                <th style='background-color: #2F855A; color: white; padding: 5px; text-align: center;'>Overtime Hours</th>
            </tr>";

    while ($row = $result->fetch_assoc()) {
        $rendered_hours_seconds = strtotime($row['rendered_hours']) - strtotime('00:00:00');
        $totalRenderedHours += $rendered_hours_seconds / 3600; 
        $overtime_hours = ($row['overtime_hours'] === null || $row['overtime_hours'] == '00:00:00') ? '0 hours' : date("H", strtotime($row['overtime_hours'])) . ' hours';
        $rendered_hours = ($row['rendered_hours'] === null || $row['rendered_hours'] == '00:00:00') ? '0 hours' : substr($row['rendered_hours'], 0, 2) . ' hours';

        $morning_timein = ($row['morning_timein'] == '00:00:00') ? '00:00' : date("h:i A", strtotime($row['morning_timein']));
        $lunch_timeout = ($row['lunch_timeout'] == '00:00:00') ? '00:00' : date("h:i A", strtotime($row['lunch_timeout']));
        $after_lunch_timein = ($row['after_lunch_timein'] == '00:00:00') ? '00:00' : date("h:i A", strtotime($row['after_lunch_timein']));
        $end_of_day_timeout = ($row['end_of_day_timeout'] == '00:00:00') ? '00:00' : date("h:i A", strtotime($row['end_of_day_timeout']));
        $formatted_attendance_date = date("F j, Y", strtotime($row['attendance_date']));
        
        echo "<tr>";
        echo "<td style='text-align: center;'>" . $morning_timein . "</td>";
        echo "<td style='text-align: center;'>" . $lunch_timeout . "</td>";
        echo "<td style='text-align: center;'>" . $after_lunch_timein . "</td>";
        echo "<td style='text-align: center;'>" . $end_of_day_timeout . "</td>";
        echo "<td style='text-align: center;'>" . $formatted_attendance_date . "</td>";
        echo "<td style='text-align: center;'>" . $rendered_hours . "</td>";
        echo "<td style='text-align: center;'>" . $overtime_hours . "</td>";    
        echo "</tr>";
    }

    echo "</table>";

    echo "<p id = 'label'><strong>Total Rendered Hours:</strong> <span id = 'hours'>" . number_format($totalRenderedHours, 2) . " hours</span></p>";
} else {
    echo "No attendance records found for $name in selected month.";
}

$stmt->close();
$conn->close();
?>
</body>
</html>
