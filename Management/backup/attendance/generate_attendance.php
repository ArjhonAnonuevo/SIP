<?php
// Include the Composer autoloader
require_once '../vendor/autoload.php';

// Extend TCPDF with your own class
class PDF extends \TCPDF
{
    public function Header()
    {
        // Logo
        $image_file = '../images/sec_logo.png'; 
        $this->Image($image_file, 10, 10, 20, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font for the header
        $this->SetFont('helvetica', 'B', 10);
    }
}

// Your existing code for database connection and query
if (isset($_POST['username'], $_POST['selectedMonth']) && !empty($_POST['selectedMonth'])) {
    $username = trim($_POST['username']);
    $selectedMonth = intval($_POST['selectedMonth']); // Convert to integer
    $month = date('F', mktime(0, 0, 0, $selectedMonth, 1));
} else {
    // Redirect to login or handle the case where 'username' or 'selectedMonth' is not in the POST parameters
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

// Prepare SQL query with the selected month condition
$stmt = $conn->prepare("SELECT morning_timein, lunch_timeout, after_lunch_timein, end_of_day_timeout, attendance_date, rendered_hours,overtime_hours FROM attendance WHERE username = ? AND MONTH(attendance_date) = ?");
$stmt->bind_param("si", $username, $selectedMonth);
$stmt->execute();
$result = $stmt->get_result();

// Function to generate PDF
function generatePDF($result, $name, $department, $selectedMonth)
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
    $pdf->Cell(0, 15, strtoupper('Attendance Report'), 0, 1, 'C');

    // Output name and department
    $pdf->Cell(0, 10, 'Name: ' . str_pad($name, 20, " "));
    $pdf->SetX($pdf->GetPageWidth() - 30);
    $pdf->Cell(0, 10, 'Month: ' . strtoupper(date('F', mktime(0, 0, 0, $selectedMonth, 1))), 0, 1, 'R');
    $pdf->Cell(0, 10, 'Department: ' . strtoupper($department), 0, 1, 'L');
    $pdf->Cell(0, 10, 'Attendance Data', 0, 1, 'C');
    

    $pdf->SetFont('helvetica', 'B', 8);
    // Set fill color to green and text color to white
    $pdf->SetFillColor(21, 128, 61);
    $pdf->SetTextColor(255);
    // Set border color to green
    $pdf->SetDrawColor(0, 0, 0);

    // Table headers
    $pdf->Cell(25, 10, 'Morning Time In', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Lunch Time Out', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'After Lunch Time In', 1, 0, 'C', true);
    $pdf->Cell(34, 10, 'End of the day Time Out', 1, 0, 'C', true);
    $pdf->Cell(26, 10, 'Attendance Date', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Rendered Hours', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Overtime Hours', 1, 0, 'C', true);
    $pdf->Ln();

    // Table data
    $pdf->SetFont('courier', 'B', 8);
    // Set text color to black
    $pdf->SetTextColor(0);

    if ($result->num_rows == 0) {
        // Display a message indicating no records found
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 10, 'No records found for the selected month.', 0, 1, 'C');
    } else {
        // Fetch and display table data
        while ($row = $result->fetch_assoc()) {
            // Calculate rendered hours (assuming 'rendered_hours' is in time format)
            $overtime_hours = ($row['overtime_hours'] == '00:00:00') ? '0 hours' : date("H", strtotime($row['overtime_hours'])) . ' hours';
            $rendered_hours = ($row['rendered_hours'] == '00:00:00') ? '0 hours' : date("H", strtotime($row['rendered_hours'])) . ' hours';
            // Format morning time in 12-hour format with AM/PM
            $morning_timein = ($row['morning_timein'] == '00:00:00') ? '00:00' : date("h:i A", strtotime($row['morning_timein']));
            // Format lunch time out 12-hour format with AM/PM
            $lunch_timeout = ($row['lunch_timeout'] == '00:00:00') ? '00:00' : date("h:i A", strtotime($row['lunch_timeout']));
            // Format after lunch time in 12-hour format with AM/PM
            $after_lunch_timein = ($row['after_lunch_timein'] == '00:00:00') ? '00:00' : date("h:i A", strtotime($row['after_lunch_timein']));
            // Format end of the day time out 12-hour format with AM/PM
            $end_of_day_timeout = ($row['end_of_day_timeout'] == '00:00:00') ? '00:00' : date("h:i A", strtotime($row['end_of_day_timeout']));
            // Format attendance date as "Month Day, Year"
            $formatted_attendance_date = date("F j, Y", strtotime($row['attendance_date']));
        
            // Output the formatted values in PDF cells
            $pdf->Cell(25, 10, $morning_timein, 1);
            $pdf->Cell(30, 10, $lunch_timeout, 1);
            $pdf->Cell(30, 10, $after_lunch_timein, 1);
            $pdf->Cell(34, 10, $end_of_day_timeout, 1);
            $pdf->Cell(26, 10, $formatted_attendance_date, 1);
            $pdf->Cell(25, 10, $rendered_hours, 1);
            $pdf->Cell(25, 10, $overtime_hours, 1);
            $pdf->Ln();
        
            $totalRenderedHours += $row['rendered_hours'];
        }
    }

    $pdf->SetFont('helvetica', '', 10);
    $pdf->Ln(10); 
    $pdf->Cell(0, 10, 'Total Rendered Hours: ' . $totalRenderedHours, 0, 1 , 'R');

    // Output the PDF to the browser
    ob_clean(); // Clean the output buffer
    $pdf->Output('output.pdf', 'D');
}

// Check if the button is clicked
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_pdf'])) {
    // Call the function to generate PDF
    generatePDF($result, $name, $department, $selectedMonth);
}


$stmt->close();
$conn->close();
?>
