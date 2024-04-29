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

$page = isset($_GET['page']) ? intval($_GET['page']) : 1; 
$perPage = 10;
$offset = ($page - 1) * $perPage;

$sql = "SELECT 
            interns_profile.name, 
            interns_profile.mname, 
            interns_profile.lname,  
            interns_profile.department, 
            interns_account.username, 
            server_status.interns_status, 
            server_status.account_expired
        FROM 
            interns_profile 
        INNER JOIN 
            interns_account ON interns_profile.id = interns_account.profile_id 
        INNER JOIN 
            server_status ON interns_account.username = server_status.username 
        WHERE 
            server_status.interns_status = 'terminated'
        LIMIT $perPage OFFSET $offset";


$result = $connection->query($sql);

$response = array();
if ($result === false) {
    die("Query execution failed: " . $connection->error);
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $response[] = $row;
    }
}

$totalRecords = $connection->query("SELECT COUNT(*) AS total FROM server_status WHERE interns_status = 'terminated'")->fetch_assoc()['total'];

$totalPages = ceil($totalRecords / $perPage);

$data = array(
    'data' => $response,
    'pagination' => array(
        'page' => $page,
        'perPage' => $perPage,
        'totalPages' => $totalPages,
        'totalRecords' => $totalRecords
    )
);

$json_response = json_encode($data);

header('Content-Type: application/json');
echo $json_response;
?>
