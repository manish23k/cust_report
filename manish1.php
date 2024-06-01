<?php
// Assuming you have already set up Composer autoloading for PHPMailer and dompdf
//include "header.php";
require 'vendor/autoload.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;


error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection details
$servername = "localhost";
$username = "cron";
$password = "1234";
$dbname = "asterisk";


// Initialize $conn to null
$conn = null;

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    die();
}

// Function to fetch the report content from the given URL using cURL
function fetchReportContent($reportUrl)
{
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


// Database connection
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    die();
}

// Function to send a test email using the provided email configuration
function testEmailConfiguration($config)
{
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    // Set up the email configuration
    $mail->isSMTP();
    $mail->SMTPDebug = 3; // Enable verbose debug output
    $mail->Debugoutput = function ($str, $level) {
        echo "[$level] $str\n";
    };

    $mail->Host = $config['host'];
    $mail->SMTPAuth = $config['smtp_auth'];
    $mail->Username = $config['username'];
    $mail->Password = $config['password'];
    $mail->SMTPSecure = $config['smtp_secure'];
    $mail->Port = $config['port'];

    // Set email parameters
    $mail->IsHTML(true);
    $mail->setFrom($config['from_email'], $config['from_name']);
    $mail->addAddress($config['to_email'], $config['to_name']); // Use the configured "To Email" and "To Name"
    $mail->Subject = isset($config['subject']) ? $config['subject'] : 'Test Email Configuration';
    $mail->Body = isset($config['message']) ? $config['message'] : 'This is a test email to check the email configuration.';

    // Send the test email
    try {
        $mail->send();
        return 'Test email sent successfully!';
    } catch (Exception $e) {
        return 'Test email could not be sent. Mailer Error: ' . $e->getMessage();
    }
}

// Function to send a report by email
function sendReportByEmail($reportConfig, $emailTemplateName)
{
    global $conn; // Make sure $conn is available in this function

    // Fetch email configuration based on the template name
    $emailConfigStmt = $conn->prepare("SELECT * FROM email_config WHERE template_name = ?");
    $emailConfigStmt->execute([$emailTemplateName]);
    $emailConfig = $emailConfigStmt->fetch(PDO::FETCH_ASSOC);

    if (!$emailConfig) {
        echo 'Error: Email template not found';
        return;
    }

    // Fetch the report content
    $reportContent = fetchReportContent($reportConfig['report_link']);

    if (!$reportContent) {
        echo 'Error fetching report content';
        return;
    }

    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    // Set up the email configuration
    $mail->isSMTP();
    $mail->SMTPDebug = 2; // Enable verbose debug output
    $mail->Debugoutput = function ($str, $level) {
        echo "[$level] $str\n";
    };

    $mail->Host = $emailConfig['host'];
    $mail->SMTPAuth = $emailConfig['smtp_auth'];
    $mail->Username = $emailConfig['username'];
    $mail->Password = $emailConfig['password'];
    $mail->SMTPSecure = $emailConfig['smtp_secure'];
    $mail->Port = $emailConfig['port'];

    // Set email parameters
    $mail->IsHTML(true);
    $mail->setFrom($emailConfig['from_email'], $emailConfig['from_name']);
    $mail->addAddress($reportConfig['to_email']);
    $mail->Subject = $reportConfig['subject'];
    $mail->Body = $reportConfig['message'];

    // Add the report content as an attachment
    $mail->addStringAttachment($reportContent, 'report.xlsx', 'base64', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    // Send the email
    try {
        $mail->send();
        echo 'Email sent successfully!';
    } catch (Exception $e) {
        echo 'Email could not be sent. Mailer Error: ' . $e->getMessage();
    }
}



// Check if the form is submitted for updating or saving report configuration
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_report_config'])) {
        // Update report configuration in the database
        $reportId = $_POST['report_id'];
        $reportName = $_POST['report_name'];
        $reportLink = $_POST['report_link'];
        //$emailTime = $_POST['email_type'];
        $emailTimeType = isset($_POST['email_type']) ? $_POST['email_type'] : '';
        $specificTime = isset($_POST['specific_time']) ? $_POST['specific_time'] : '';
        $emailTemplateId = $_POST['email_template_id'];
        $reportType = $_POST['report_type'];
        $toEmail = $_POST['to_email'];
        $subject = $_POST['subject'];
        $message = $_POST['message'];

        $updateStmt = $conn->prepare("UPDATE email_report_config SET report_name=?, report_link=?, email_type=?, specific_time=?, email_template_id=?, report_type=?, to_email=?, subject=?, message=? WHERE id=?");


        if ($updateStmt->execute([$reportName, $reportLink, $emailTimeType, $specificTime, $emailTemplateId, $reportType, $toEmail, $subject, $message, $reportId])) {
            
            echo 'Report configuration updated successfully.';
        } else {
            echo 'Error updating report configuration: ' . implode(' ', $updateStmt->errorInfo());
        }
        //echo 'Report configuration updated successfully.';
    } elseif (isset($_POST['save_report_config'])) {
        // Save report configuration to the database
        $reportName = $_POST['report_name'];
        $reportLink = $_POST['report_link'];
        //$emailTime = $_POST['email_type'];
        $emailTimeType = isset($_POST['email_type']) ? $_POST['email_type'] : '';
        $specificTime = isset($_POST['specific_time']) ? $_POST['specific_time'] : '';
        $emailTemplateId = $_POST['email_template_id'];
        $reportType = $_POST['report_type'];
        $toEmail = $_POST['to_email'];
        $subject = $_POST['subject'];
        $message = $_POST['message'];

        $insertStmt = $conn->prepare("INSERT INTO email_report_config (report_name, report_link, email_type, specific_time, email_template_id, report_type, to_email, subject, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if ($insertStmt->execute([$reportName, $reportLink, $emailTimeType, $specificTime, $emailTemplateId, $reportType, $toEmail, $subject, $message])) {
            echo 'Report configuration saved successfully.';
        } else {
            echo 'Error saving report configuration: ' . implode(' ', $insertStmt->errorInfo());
        }
    } elseif (isset($_POST['edit_report_config'])) {
        // Fetch the report configuration for editing
        $reportId = $_POST['report_id'];
        $editStmt = $conn->prepare("SELECT * FROM email_report_config WHERE id = ?");
        $editStmt->execute([$reportId]);
        $editReport = $editStmt->fetch(PDO::FETCH_ASSOC);
    } elseif (isset($_POST['delete_report_config'])) {
        // Delete report configuration from the database
        $reportId = $_POST['report_id'];
        $deleteStmt = $conn->prepare("DELETE FROM email_report_config WHERE id = ?");
        $deleteStmt->execute([$reportId]);
        echo 'Report configuration deleted successfully.';
    }
}

// Fetch all report configurations from the database
$reportConfigStmt = $conn->query("SELECT * FROM email_report_config");
$reportConfigs = $reportConfigStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all email templates from the database
$emailTemplateStmt = $conn->query("SELECT * FROM email_config");
$emailTemplates = $emailTemplateStmt->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Configuration</title>
    <style>
        .hidden {
            display: none;
        }
    </style>
</head>

<body>
    <h2>Report Configuration</h2>

    <!-- Form for Updating and Saving Report Configuration -->
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
    <?php
    $baseURL = 'http://192.168.0.201/report/';
$reportName = isset($_GET['report_name']) ? $_GET['report_name'] : 'default_report';
$reportLink = $baseURL . $reportName . '.php?agent=' . urlencode($_GET['agent']) . '&phone_number=' . urlencode($_GET['phone_number']) . '&application_no=' . urlencode($_GET['application_no']) . '&export=xlsx';
// Add other filters as needed
?>

        <!-- <label for="report_name">Report Name:</label>
        <input type="text" name="report_name" required value="<?php echo isset($editReport['report_name']) ? $editReport['report_name'] : ''; ?>"><br> -->
        <label for="report_name">Report Name:</label>
    <!-- Add a dropdown for selecting Report Name -->
    <select name="report_name">
        <option value="manish1" <?php echo isset($_GET['report_name']) && $_GET['report_name'] == 'manish1' ? 'selected' : ''; ?>>Manish 1</option>
        <!-- Add other report names as needed -->
    </select><br>
        <!-- <label for="report_link">Report Link:</label>
        <input type="text" name="report_link" required value="<?php echo isset($editReport['report_link']) ? $editReport['report_link'] : ''; ?>"><br> -->
        <label for="report_link">Report Link:</label>
    <!-- Add filter input fields -->
    <input type="text" name="agent" placeholder="Agent" value="<?php echo isset($_GET['agent']) ? $_GET['agent'] : ''; ?>">
    <input type="text" name="phone_number" placeholder="Phone Number" value="<?php echo isset($_GET['phone_number']) ? $_GET['phone_number'] : ''; ?>">
    <input type="text" name="application_no" placeholder="Application No" value="<?php echo isset($_GET['application_no']) ? $_GET['application_no'] : ''; ?>">
    <!-- Add other filter fields as needed -->
        <label for="email_type">Email Type/Time:</label>
        <select name="email_type">
            <option value="monthly" <?php echo isset($editReport['email_type']) && $editReport['email_type'] == 'monthly' ? 'selected' : ''; ?>>Monthly</option>
            <option value="weekly" <?php echo isset($editReport['email_type']) && $editReport['email_type'] == 'weekly' ? 'selected' : ''; ?>>Weekly</option>
            <option value="daily" <?php echo isset($editReport['email_type']) && $editReport['email_type'] == 'daily' ? 'selected' : ''; ?>>Daily</option>
            <option value="specific_time" <?php echo isset($editReport['email_type']) && $editReport['email_type'] == 'specific_time' ? 'selected' : ''; ?>>Specific Time</option>
        </select>

        <!-- Add a field for specific_time if selected -->
        <input type="time" name="specific_time" value="<?php echo isset($editReport['specific_time']) ? $editReport['specific_time'] : ''; ?>"><br>


        <label for="email_template_id">Email Template:</label>
        <select name="email_template_id">
            <?php
            foreach ($emailTemplates as $template) {
                $selected = isset($editReport['email_template_id']) && $editReport['email_template_id'] == $template['id'] ? 'selected' : '';
                echo "<option value='{$template['id']}' $selected>{$template['template_name']}</option>";
            }
            ?>
        </select><br>

        <label for="report_type">Report Type:</label>
        <select name="report_type">
            <option value="XLSX" <?php echo isset($editReport['report_type']) && $editReport['report_type'] == 'XLSX' ? 'selected' : ''; ?>>XLSX</option>
            <option value="PDF" <?php echo isset($editReport['report_type']) && $editReport['report_type'] == 'PDF' ? 'selected' : ''; ?>>PDF</option>
            <option value="CSV" <?php echo isset($editReport['report_type']) && $editReport['report_type'] == 'CSV' ? 'selected' : ''; ?>>CSV</option>
        </select><br>

        <!-- Add fields for To Email, Subject, and Message -->
        <label for="to_email">To Email:</label>
        <input type="text" name="to_email" required value="<?php echo isset($editReport['to_email']) ? $editReport['to_email'] : ''; ?>"><br>

        <label for="subject">Subject:</label>
        <input type="text" name="subject" required value="<?php echo isset($editReport['subject']) ? $editReport['subject'] : ''; ?>"><br>

        <label for="message">Message:</label>
        <textarea name="message" required><?php echo isset($editReport['message']) ? $editReport['message'] : ''; ?></textarea><br>

        <!-- Add hidden field for Report ID -->
        <input type="hidden" name="report_id" value="<?php echo isset($editReport['id']) ? $editReport['id'] : ''; ?>">

        <!-- Save Configuration button -->
        <button type="submit" name="save_report_config" class="<?php echo isset($editReport['id']) ? 'hidden' : ''; ?>">Save Configuration</button>

        <!-- Update Configuration button -->
        <button type="submit" name="update_report_config" class="<?php echo isset($editReport['id']) ? '' : 'hidden'; ?>">Update Configuration</button>
    </form>

    <hr>

    <!-- Report Configurations List -->
    <h2>Report Configurations</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Report Name</th>
            <th>Report Link</th>
            <th>Email Type</th>
            <th>Email Time</th>
            <th>Email Template</th>
            <th>Report Type</th>
            <th>To Email</th>
            <th>Subject</th>
            <th>Message</th>
            <th>Actions</th>
        </tr>
        <?php
        foreach ($reportConfigs as $report) {
            echo "<tr>";
            echo "<td>{$report['id']}</td>";
            echo "<td>{$report['report_name']}</td>";
            echo "<td>{$report['report_link']}</td>";
            echo "<td>{$report['email_type']}</td>";
            echo "<td>{$report['specific_time']}</td>";
            echo "<td>{$report['email_template_id']}</td>";
            echo "<td>{$report['report_type']}</td>";
            echo "<td>{$report['to_email']}</td>";
            echo "<td>{$report['subject']}</td>";
            echo "<td>{$report['message']}</td>";

            echo "<td>
        <form action='{$_SERVER['PHP_SELF']}' method='post' style='display:inline;'>
          <input type='hidden' name='report_id' value='{$report['id']}'>
          <button type='submit' name='edit_report_config'>Edit</button>
        </form>
        <form action='{$_SERVER['PHP_SELF']}' method='post' style='display:inline;'>
          <input type='hidden' name='report_id' value='{$report['id']}'>
          <button type='submit' name='delete_report_config'>Delete</button>
        </form>
        <form action='{$_SERVER['PHP_SELF']}' method='post' style='display:inline;'>
          <input type='hidden' name='report_id' value='{$report['id']}'>
          <button type='submit' name='send_test_email'>Send Email</button>
        </form>
    </td>";
            echo "</tr>";
        }


        // Handle "Send Email" button submission
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_test_email'])) {
            $reportId = $_POST['report_id'];

            // Fetch the report configuration
            $reportStmt = $conn->prepare("SELECT * FROM email_report_config WHERE id = ?");
            $reportStmt->execute([$reportId]);
            $reportConfig = $reportStmt->fetch(PDO::FETCH_ASSOC);

            // Fetch the corresponding email configuration based on the template name
            $emailTemplateName = getEmailTemplateNameById($reportConfig['email_template_id']);
            echo "Email Template Name: $emailTemplateName"; // Add this line for debugging
            sendReportByEmail($reportConfig, $emailTemplateName);

            // You can add a success message or redirect to the same page after sending the email
            echo 'Test123 email sent successfully!';
        }


        function getEmailTemplateNameById($templateId)
        {
            global $conn;
        
            $templateNameStmt = $conn->prepare("SELECT template_name FROM email_config WHERE id = ?");
            $templateNameStmt->execute([$templateId]);
            
            $templateData = $templateNameStmt->fetch(PDO::FETCH_ASSOC);
        
            return isset($templateData['template_name']) ? $templateData['template_name'] : null;
        }
        ?>


    </table>
</body>

</html>