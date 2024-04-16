<?php
session_start();
require_once '../vendor/autoload.php';

class PDF extends TCPDF
{
    public function Footer()
    {
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

if (isset($_GET['control_number'], $_GET['given_name'], $_GET['family_name'], $_GET['primary_email'])) {
    $control_number = $_GET['control_number'];
    $given_name = urldecode($_GET['given_name']);
    $family_name = urldecode($_GET['family_name']);
    $primary_email = urldecode($_GET['primary_email']);

    // Create a new TCPDF instance
    $pdf = new PDF();
    $pdf->setPrintHeader(true);
    $pdf->SetPrintFooter(true);

    // Set header data with logo
    $logoPath = '../images/sec.png';
    $pdf->SetHeaderData($logoPath, 100, 'Control Number Slip', 'Generated on ' . date('jS F Y H:i:s'));

    // Set document information
    $pdf->SetCreator('SIP-MANAGEMENT');
    $pdf->SetAuthor('Securities and Exchange Commission');
    $pdf->SetTitle('Control Number Slip');

    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // Add a page
    $pdf->AddPage();

    // Display Name and Primary Email
    $pdf->Cell(0, 10, 'Name: ' . $given_name . ' ' . $family_name, 0, 1, 'L');
    $pdf->Cell(0, 10, 'Primary Email: ' . $primary_email, 0, 1, 'L');

    // Set font
    $pdf->SetFont('dejavusans', 'B', 14);
    $pdf->Ln(10);
    $pdf->SetFont('dejavusans', '', 12);

    // Set fill color for background
    $pdf->SetFillColor(245, 245, 245);

    // Add content
    $pdf->Cell(0, 10, 'Use this control number to see the status of your application.', 0, 1, 'C', true);
    $pdf->Cell(0, 10, 'Control Number: ' . $control_number, 0, 1, 'C');

    // Add space between header and name/primary email
    $pdf->Ln(10);

    // Generate and output PDF to the browser
    $pdf->Output('Control_Number_Slip.pdf', 'I');
} else {
    // Handle the case when parameters are not provided
    echo 'Invalid request.';
}
?>
