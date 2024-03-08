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

    $query = "SELECT f.school_id, f.regi, f.schedule, f.form1, f.form2, f.form3, f.form4, 
    fn.school_name, fn.regi_name, fn.schedule_name, fn.form1_name, fn.form2_name, 
    fn.form3_name, fn.form4_name
    FROM files AS f 
    INNER JOIN file_names AS fn ON f.id = fn.file_id
    WHERE f.id = ?";

    $stmt = $mysqli->prepare($query);

    if ($stmt) {
        $stmt->bind_param("i", $applicant_id);

        if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result) {
                if ($result->num_rows > 0) {
                    $data = $result->fetch_assoc();

                    // Convert blob fields to base64 for JSON
                    $blobFields = ['school_id', 'regi', 'schedule', 'form1', 'form2', 'form3', 'form4'];
                    $fileNames = ['school_name', 'regi_name', 'schedule_name', 'form1_name', 'form2_name', 'form3_name', 'form4_name'];

                    $formattedData = array();
                    for ($i = 0; $i < count($blobFields); $i++) {
                        $formattedData[] = array(
                            "file_name" => $data[$fileNames[$i]],
                            "value" => base64_encode($data[$blobFields[$i]])
                        );
                    }

                    echo json_encode($formattedData);
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
