<?php
header('Content-Type: application/json');

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
        die(json_encode(array("error" => "Connection failed: " . $mysqli->connect_error)));
    }

    $query = "SELECT school_name, regi_name, schedule_name, form1_name, form2_name, form3_name
              FROM interns_files
              WHERE control_number= ?";

    $stmt = $mysqli->prepare($query);

    if ($stmt) {
        $stmt->bind_param("i", $applicant_id);

        if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result) {
                if ($result->num_rows > 0) {
                    $data = $result->fetch_assoc();

                    // Validate data before encoding into JSON
                    foreach ($data as $key => $value) {
                        if (empty($value)) {
                            die(json_encode(array("error" => "Invalid data retrieved from the database.")));
                        }
                    }

                    // Prepare data for JSON response
                    $urls = array(
                        "school_name" => $data["school_name"],
                        "regi_name" => $data["regi_name"],
                        "schedule_name" => $data["schedule_name"],
                        "form1_name" => $data["form1_name"],
                        "form2_name" => $data["form2_name"],
                        "form3_name" => $data["form3_name"]
                    );

                    echo json_encode($urls);
                } else {
                    echo json_encode(array("error" => "Files not found for the applicant."));
                }
            } else {
                echo json_encode(array("error" => "Error fetching result: " . $stmt->error));
            }

            $result->close();
        } else {
            echo json_encode(array("error" => "Error executing statement: " . $stmt->error));
        }

        $stmt->close();
    } else {
        echo json_encode(array("error" => "Error preparing statement: " . $mysqli->error));
    }

    $mysqli->close();
} else {
    echo json_encode(array("error" => "Invalid request. 'id' parameter is missing or empty."));
}
?>
