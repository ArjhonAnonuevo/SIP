<?php
header('Content-Type: application/json');

if (isset($_GET["control_number"]) && !empty($_GET["control_number"])) {
    $applicant_id = trim($_GET["control_number"]);

    include "../configuration/application_config.php";
    $config = getDatabaseConfig();

    $dbhost = $config['dbhost'];
    $dbuser = $config['dbuser'];
    $dbpass = $config['dbpass'];
    $dbname = $config['dbname'];

    $mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

    if ($mysqli->connect_error) {
        error_log("Connection failed: " . $mysqli->connect_error);
        die(json_encode(array("error" => "Internal Server Error")));
    }

    $query = "SELECT school_name, regi_name, schedule_name, form1_name, form2_name, form3_name, form4_name 
              FROM interns_files 
              WHERE control_number = ?";
    
    $stmt = $mysqli->prepare($query);

    if ($stmt) {
        $stmt->bind_param("s", $applicant_id);

        if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result) {
                if ($result->num_rows > 0) {
                    $data = $result->fetch_assoc();

                    // Directory where files are stored
                    $fileDirectory = "../applicants-files/";

                    // Convert file URLs to base64 for JSON
                    $fileFields = ['school_name', 'regi_name', 'schedule_name', 'form1_name', 'form2_name', 'form3_name', 'form4_name'];
                    $formattedData = array();
                    foreach ($fileFields as $fieldName) {
                        if ($data[$fieldName] !== null) {
                            $filePath = $fileDirectory . $data[$fieldName];
                            if (file_exists($filePath)) {
                                // Read file contents and encode to base64
                                $fileContents = file_get_contents($filePath);
                                if ($fileContents !== false) {
                                    $formattedData[] = array(
                                        "file_name" => $fieldName,
                                        "value" => base64_encode($fileContents)
                                    );
                                } else {
                                    error_log("Error reading file: " . $data[$fieldName]);
                                    die(json_encode(array("error" => "Internal Server Error")));
                                }
                            } else {
                                error_log("File not found: " . $data[$fieldName]);
                                die(json_encode(array("error" => "Internal Server Error")));
                            }
                        }
                    }

                    echo json_encode($formattedData);
                } else {
                    echo json_encode(array("error" => "Files not found for the applicant."));
                }
            } else {
                error_log("Error fetching result: " . $stmt->error);
                die(json_encode(array("error" => "Internal Server Error")));
            }

            $result->close();
        } else {
            error_log("Error executing statement: " . $stmt->error);
            die(json_encode(array("error" => "Internal Server Error")));
        }

        $stmt->close();
    } else {
        error_log("Error preparing statement: " . $mysqli->error);
        die(json_encode(array("error" => "Internal Server Error")));
    }

    $mysqli->close();
} else {
    echo json_encode(array("error" => "Invalid request. 'control_number' parameter is missing or empty."));
}
?>
