<?php
header('Content-Type: application/json; charset=utf-8');

if (isset($_GET["id"]) && !empty($_GET["id"])) {
    $applicant_id = trim($_GET["id"]);

    include "../configuration/application_config.php";
    $config = getDatabaseConfig();

    $dbhost = $config['dbhost'];
    $dbuser = $config['dbuser'];
    $dbpass = $config['dbpass'];
    $dbname = $config['dbname'];

    $mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

    if ($mysqli->connect_error) {
        die(json_encode(array("error" => "Connection failed: " . $mysqli->connect_error), JSON_UNESCAPED_UNICODE));
    }
    
    $query = "SELECT a.given_name, a.middle_name, a.family_name, a.address, a.place_birth, a.birthday, a.age, a.gender, a.contact, a.landline, a.secondary_email, a.primary_email
    FROM application AS a
    WHERE a.control_number = ?";

    $stmt = $mysqli->prepare($query);

    if ($stmt) {
        $stmt->bind_param("i", $applicant_id);

        if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result) {
                if ($result->num_rows > 0) {
                    $data = $result->fetch_assoc();

                    echo json_encode($data, JSON_UNESCAPED_UNICODE);
                } else {
                    echo json_encode(array("error" => "Applicant not found."), JSON_UNESCAPED_UNICODE);
                }
            } else {
                echo json_encode(array("error" => "Error fetching result: " . $stmt->error), JSON_UNESCAPED_UNICODE);
            }

            $result->close();
        } else {
            echo json_encode(array("error" => "Error executing statement: " . $stmt->error), JSON_UNESCAPED_UNICODE);
        }

        $stmt->close();
    } else {
        echo json_encode(array("error" => "Error preparing statement: " . $mysqli->error), JSON_UNESCAPED_UNICODE);
    }

    $mysqli->close();
} else {
    echo json_encode(array("error" => "Invalid request. 'id' parameter is missing or empty."), JSON_UNESCAPED_UNICODE);
}
?>
