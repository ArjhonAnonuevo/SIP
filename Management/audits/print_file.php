<!DOCTYPE html>
<html>
<head>
    <title>Print Audits</title>
    <style>
        th {
            -webkit-print-color-adjust: exact;
            padding: 5px;
            font-family: "Helvetica", sans-serif;
            color: white; /* Added property to make text white */
        }
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }
        th {
            background-color: #2F855A;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        h1 {
            font-family: "Helvetica", sans-serif;
            font-size: 24px;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
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
$connection = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$sql = "SELECT actions, logs, audit_timestamp, role FROM audits";
$result = $connection->query($sql);

// Check if there are any rows returned
if ($result->num_rows > 0) {
    // Format the fetched data into table rows
    $tableRows = '<table>';
    $tableRows .= '<tr>';
    $tableRows .= '<th>Actions</th>';
    $tableRows .= '<th>Logs</th>';
    $tableRows .= '<th>Audit Timestamp</th>';
    $tableRows .= '<th>Role</th>';
    $tableRows .= '</tr>';
    while ($row = $result->fetch_assoc()) {
        $tableRows .= '<tr>';
        $tableRows .= '<td>' . $row['actions'] . '</td>';
        $tableRows .= '<td>' . $row['logs'] . '</td>';
        $tableRows .= '<td>' . date('F j, Y h:i A', strtotime($row['audit_timestamp'])) . '</td>';
        $tableRows .= '<td>' . $row['role'] . '</td>';
        $tableRows .= '</tr>';
    }
    $tableRows .= '</table>';

    // Send the table rows as HTML response
    header('Content-Type: text/html');
    echo $tableRows;
} else {
    $errorResponse = 'No data found.';
    header('Content-Type: text/html');
    echo $errorResponse;
}

$connection->close();
?>
