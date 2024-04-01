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

    $query = "SELECT schoolName, Total FROM schooldata";

    $result = $connection->query($query);

    // Check for query errors
    if (!$result) {
        die("Query failed: " . $connection->error);
    }

    // Fetch data
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $schoolName = $row['schoolName'];
        $total = $row['Total'];
        $data[] = array('schoolName' => $schoolName, 'total' => $total);
    }

    $result->close();
    $connection->close();

    echo json_encode($data);
    ?>
