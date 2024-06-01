<?php
// Assuming you have already set up Composer autoloading for PHPMailer and dompdf
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;

// Fetch the report content in XLSX format
$reportUrl = 'http://192.168.0.201/report/manish.php?agent=&phone_number=&application_no=&application_date=&disposal_date=&star_rating=&answer_filter=&search=&export=xlsx';
$xlsxContent = fetchReportContent($reportUrl);

// Fetch the report content in HTML format
$htmlReportUrl = 'http://192.168.0.201/report/manish.php?agent=&phone_number=&application_no=&application_date=&disposal_date=&star_rating=&answer_filter=&search=';
$htmlContent = fetchReportContent($htmlReportUrl);

// Function to fetch the report content from the given URL using cURL
function fetchReportContent($reportUrl) {
    $reportUrl = str_replace('&amp;', '&', $reportUrl); // Fix URL encoding

    // Use cURL to fetch content from the URL with follow redirects option
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $reportUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects

    // Ignore SSL certificate verification (use with caution)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $reportContent = curl_exec($ch);

    if ($reportContent === false) {
        echo 'Error fetching report content: ' . curl_error($ch);
        return null; // Return null or handle the error accordingly
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($httpCode !== 200) {
        echo 'HTTP error: ' . $httpCode;
        return null; // Return null or handle the error accordingly
    }

    curl_close($ch);

    return $reportContent;
}

// Create a new PHPMailer instance
$mail = new PHPMailer(true);

// Set up your email configuration
$mail->isSMTP();
$mail->SMTPDebug = 0; // Turn off debug mode in production
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'mailreport.server@gmail.com';
$mail->Password = 'lztg bzkl cmir wxye ';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

// Set email parameters
$mail->IsHTML(true);
$mail->setFrom('mailreport.server@gmail.com', 'Report Server');
$mail->addAddress('support@arrowtelecom.com', 'Recipient Name');

// Attach the XLSX report
$mail->addStringAttachment($xlsxContent, 'Binkheti_report.xlsx', 'base64', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

// Create PDF from HTML content
$dompdf = new Dompdf();
$dompdf->loadHtml($htmlContent);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Attach the PDF report
$pdfContent = $dompdf->output();
$mail->addStringAttachment($pdfContent, 'Binkheti_report.pdf', 'base64', 'application/pdf');

// Set email content
$mail->Subject = 'Daily Report';
$mail->Body = 'Please find the attached daily Binkheti_report in xlsx and pdf formats.';

// Send the email
try {
    $mail->send();
    echo 'Message sent!';
} catch (Exception $e) {
    echo 'Mailer Error: ' . $e->getMessage();
}
?>
