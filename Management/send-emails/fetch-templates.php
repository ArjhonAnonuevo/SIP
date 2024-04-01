<?php
$templateDir = '../email-templates/';

// Array to store file URLs
$templateURLs = [];

// Scan the directory for HTML files
$files = scandir($templateDir);

// Loop through the files
foreach ($files as $file) {
    // Check if the file is a regular file and has a .html extension
    if (is_file($templateDir . $file) && pathinfo($file, PATHINFO_EXTENSION) == 'html') {
        // Construct the file URL
        $fileURL = $templateDir . $file;
        // Add the file URL to the template URLs array
        $templateURLs[] = $fileURL;
    }
}
// Return the list of template URLs as JSON
echo json_encode($templateURLs);
?>
