<?php
// Include the Composer autoloader
require_once '../vendor/autoload.php';

// Extend TCPDF with your own class
class PDF extends \TCPDF
{
    public function Header()
    {
        // Logo
        $image_file = '../tailwind/securities and exchange.png'; // Update with the actual path to your logo image
        $this->Image($image_file, 10, 10, 20, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

        // Set font for the header (optional)
        $this->SetFont('helvetica', 'B', 10);

        // Add a cell with title (optional)
    }
}

// Your existing code for database connection and query
if (isset($_GET['username'])) {
      $username = trim($_GET['username']);
} else {
    // Redirect to login or handle the case where 'username' is not in the URL parameters
    header('Location: login.php');
    exit();
}

include '../configuration/interns_config.php';

// Call the getDatabaseConfig function
$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT CONCAT(p.name, ' ', p.mname, ' ', p.lname) AS fullname, p.department FROM interns_profile p INNER JOIN interns_account a ON p.id = a.profile_id WHERE a.username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$name = $row['fullname'];
$department = $row['department'];

$stmt = $conn->prepare("SELECT morning_timein, lunch_timeout, after_lunch_timein, end_of_day_timeout, attendance_date, rendered_hours FROM attendance WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Function to generate PDF
function generatePDF($result, $name, $department)
{
    // Create an instance of the PDF class
    $pdf = new PDF();
    $pdf->setPrintHeader(true);
    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 10);

    // Add logo
    $pdf->Ln(18);

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 15, 'MONTHLY ATTENDANCE', 0, 1, 'C');

    // Output name and department
    $pdf->Cell(0, 10, 'Name: ' . $name, 0, 1, 'L');
    $pdf->Cell(0, 10, 'Department: ' . $department, 0, 1, 'L');

    $pdf->Cell(0, 10, 'Attendance Data', 0, 1, 'C');

    // Table headers
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(30, 10, 'Morning Time In', 1);
    $pdf->Cell(30, 10, 'Lunch Time Out', 1);
    $pdf->Cell(30, 10, 'After Lunch Time In', 1);
    $pdf->Cell(34, 10, 'End of the day Time Out', 1);
    $pdf->Cell(30, 10, 'Attendance Date', 1);
    $pdf->Cell(30, 10, 'Rendered Hours', 1);
    $pdf->Ln();

    // Table data
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(30, 10, $row['morning_timein'], 1);
        $pdf->Cell(30, 10, $row['lunch_timeout'], 1);
        $pdf->Cell(30, 10, $row['after_lunch_timein'], 1);
        $pdf->Cell(34, 10, $row['end_of_day_timeout'], 1);
        $pdf->Cell(30, 10, $row['attendance_date'], 1);
        $pdf->Cell(30, 10, $row['rendered_hours'], 1);
        $pdf->Ln();
    }

    // Output the PDF to the browser
    ob_clean(); // Clean the output buffer
    $pdf->Output('output.pdf', 'D');
}

// Check if the button is clicked
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_pdf'])) {
    // Call the function to generate PDF
    generatePDF($result, $name, $department);
}

$stmt->close();
$conn->close();
?>
