<?php
// Start the session
session_start();

// Include the TCPDF autoloader
require_once '../vendor/autoload.php';

// Extend TCPDF with your own class
class PDF extends TCPDF
{
    public function Header()
    {
        // Logo
        $image_file = '../tailwind/securities and exchange.png'; // Update with the actual path to your logo image
        $this->Image($image_file, 10, 10, 20, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font for the header
        $this->SetFont('helvetica', 'B', 10);
    }
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

if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Error executing statement: " . $stmt->error);
}

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $name = $row['fullname'];
    $department = $row['department']; 
} else {
    die("User not found.");
}

// Get the selected month
$selectedMonth = isset($_POST['selectedMonth']) ? $_POST['selectedMonth'] : date('m');

// Function to generate PDF for the selected month
function generatePDF($name, $department, $selectedMonth, $conn, $username)
{
    // Create an instance of the PDF class
    $pdf = new PDF();
    $pdf->setPrintHeader(true);
    // Add a page
    $pdf->AddPage();
    // Set font
    $pdf->SetFont('helvetica', '', 10);

    // Add logo
    $image_file = '../tailwind/securities and exchange.png'; // Update with the actual path to your logo image
    $pdf->Image($image_file, 10, 10, 20, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    $pdf->Ln(18);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 15, strtoupper('Accomplishment report'), 0, 1, 'C');
    $pdf->Cell(0, 10, 'Name: ' . str_pad($name, 20, " "));
    $pdf->SetX($pdf->GetPageWidth() - 30); // Set the x-coordinate for the next element
    $pdf->Cell(0, 10, 'Month: ' . strtoupper(date('F', mktime(0, 0, 0, $selectedMonth, 1))), 0, 1, 'R');
    $pdf->Cell(0, 10, 'Department: ' . strtoupper($department), 0, 1, 'L');

    // New query with a parameter for the selected month
    $stmt2 = $conn->prepare("SELECT * FROM acomplisment_report WHERE user_id = ? AND MONTH(date) = ?");
    if (!$stmt2) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt2->bind_param("ss", $username, $selectedMonth);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    // Check if any rows are fetched
    if (!$result2) {
        die("Error executing statement: " . $stmt2->error);
    }

    if ($result2->num_rows > 0) {
        // Create a table header
        $pdf->Ln(10);
        $pdf->SetFillColor(21, 128, 61);
        $pdf->SetTextColor(255);
        $pdf->SetDrawColor(1, 50, 32);
        $pdf->SetLineWidth(0.3);
        $pdf->SetFont('courier', 'B');
        $header = ['Date', 'Your Activity', 'Time', 'Status'];
        $w = [35, 88, 33, 35];
        foreach ($header as $col) {
            $pdf->Cell($w[key($header)], 7, $col, 1, 0, 'C', true);
            next($header);
        }
        $pdf->Ln();
        // Create a table body
        $pdf->SetFillColor(224, 235, 255);
        $pdf->SetTextColor(0);
        $pdf->SetFont('');
        $fill = false; // Initialize fill color

        // Iterate over the rows and add to the table
        while ($row2 = $result2->fetch_assoc()) {
            $fill = !$fill; // Toggle the fill color
            $pdf->Cell($w[0], 6, $row2['date'], 'LR', 0, 'L', $fill);
            $pdf->Cell($w[1], 6, $row2['type'], 'LR', 0, 'L', $fill);
            // Convert the time from the database to 12-hour format
            $databaseTime = $row2['time'];
            $convertedTime = date("h:i A", strtotime($databaseTime));
            $pdf->Cell($w[2], 6, $convertedTime, 'LR', 0, 'L', $fill);
            $pdf->Cell($w[3], 6, $row2['status'], 'LR', 0, 'L', $fill);
            $pdf->Ln();
        }
        $pdf->Cell(array_sum($w), 0, '', 'T');
    } else {
        $pdf->Cell(0, 10, 'No accomplishment records found for the user.', 0, 1, 'C');
    }

    $stmt2->close(); // Close the prepared statement

    $pdf->Ln();
    $pdf->Ln(10); // Adjust this value to change the line height
    $pdf->SetFont('courier', '', 8);
    $pdf->Line(4, $pdf->getPageHeight() - 58, 100, $pdf->getPageHeight() - 58);

    // Set the x-coordinate to add a margin-left
    $pdf->SetX(39); // set the margin-left

    $pdf->Cell(0, 10, 'Supervisor', 0, 1, 'L');

    // Output the PDF to the browser
    ob_clean();
    $pdf->Output($username.'Accomplisment Report.pdf','D');
}

// Check if the button is clicked
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['download'])) {
    // Call the function to generate PDF with the selected month
    generatePDF($name, $department, $selectedMonth, $conn, $username);
}

$stmt->close();
$conn->close();
?>
