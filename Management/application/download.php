<?php
include "../configuration/application_config.php";

if (isset($_POST['id']) && isset($_POST['file']) && isset($_POST['content'])) {
    $applicant_id = $_POST['id'];
    $file_type = $_POST['file'];
    $file_content = $_POST['content'];

    // Check if the provided content is base64-encoded
    if (base64_decode($file_content, true)) {
        // Set the headers for the file download
        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf'); // Adjust content type based on your file type
        header('Content-Disposition: attachment; filename="' . $file_type . '.pdf"');

        // Clear output buffer
        ob_clean();
        flush();

        // Output the base64-encoded content directly
        echo $file_content;
        exit;
    } else {
        echo json_encode(array("error" => "Invalid base64-encoded content."));
    }
} else {
    echo json_encode(array("error" => "Invalid request. 'id', 'file', or 'content' parameter is missing."));
}
?>
