<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

const EMAIL_TYPE_MONTHLY = 'monthly';
const EMAIL_TYPE_WEEKLY = 'weekly';
const EMAIL_TYPE_DAILY = 'daily';
const EMAIL_TYPE_SPECIFIC_TIME = 'specific_time';


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

function getEmailTemplateNameById($templateId)
{
    global $conn;

    $templateNameStmt = $conn->prepare("SELECT template_name FROM email_config WHERE id = ?");
    $templateNameStmt->execute([$templateId]);

    $templateData = $templateNameStmt->fetch(PDO::FETCH_ASSOC);

    return isset($templateData['template_name']) ? $templateData['template_name'] : null;
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
    $mail->SMTPDebug = 2; // Change to 0 for production; 2 for verbose debug output
    $mail->Debugoutput = function ($str, $level) {
        // Log the debug output (you can adjust this based on your logging needs)
        file_put_contents('/var/log/Email_reoprt.log', "[$level] $str\n", FILE_APPEND);
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
    $mail->addAddress($reportConfig['to_email'], $reportConfig['to_name']);
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

// Function to send scheduled emails
function sendScheduledEmails()
{
    global $conn;

    try {
        $reportConfigStmt = $conn->prepare("SELECT * FROM email_report_config");
        $reportConfigStmt->execute();

        if ($reportConfigStmt === false) {
            // Handle query failure
            $errorInfo = $conn->errorInfo();
            throw new PDOException('Query failed: ' . $errorInfo[2]);
        }

        $reportConfigs = $reportConfigStmt->fetchAll(PDO::FETCH_ASSOC);

        if ($reportConfigs === false) {
            // Handle fetch failure
            $errorInfo = $conn->errorInfo();
            throw new PDOException('Fetch failed: ' . $errorInfo[2]);
        }

        foreach ($reportConfigs as $reportConfig) {
            $emailTimeType = $reportConfig['email_type'];
            $specificTime = $reportConfig['specific_time'];
            
            echo "Processing report config ID: {$reportConfig['id']}\n";
            echo "Email Time Type: $emailTimeType\n";
            echo "Specific Time: $specificTime\n";
            echo "Before switch\n";
        
            // Determine if it's time to send the email based on the email_time_type
            //$sendEmail = isTimeToSend($specificTime, $reportConfig['last_sent'], $emailTimeType);
            $sendEmail = isTimeToSend($specificTime, $reportConfig['last_sent'], $emailTimeType, time());
        
            switch ($emailTimeType) {
                case EMAIL_TYPE_DAILY:
                    echo "Inside case EMAIL_TYPE_DAILY\n";
                    break;
                case EMAIL_TYPE_WEEKLY:
                    echo "Inside case EMAIL_TYPE_WEEKLY\n";
                    break;
                case EMAIL_TYPE_MONTHLY:
                    echo "Inside case EMAIL_TYPE_MONTHLY\n";
                    break;
                case EMAIL_TYPE_SPECIFIC_TIME:
                    echo "Inside case EMAIL_TYPE_SPECIFIC_TIME\n";
                    break;
                default:
                    echo "Inside default case\n";
                    // Handle other email types if needed
                    break;
            }
        
            // Add this line to check if the script reaches this point
            echo "After switch\n";
        
            if ($sendEmail) {
                // Send the email
                $emailTemplateName = getEmailTemplateNameById($reportConfig['email_template_id']);
                sendReportByEmail($reportConfig, $emailTemplateName);
        
                // Update the last_sent timestamp
                $updateStmt = $conn->prepare("UPDATE email_report_config SET last_sent = NOW() WHERE id = ?");
                $updateStmt->execute([$reportConfig['id']]);
            }
        }

} catch (PDOException $e) {
    // Handle database connection/query errors
    echo 'Error: ' . $e->getMessage();
}
}


// Function to check if it's time to send the email for the daily type
function isDailyTimeToSend($specificTime, $lastSent)
{
    echo "isDailyTimeToSend function is called.\n";

    if (!empty($specificTime)) {
        $currentTime = time();
        $scheduledTime = strtotime(date('Y-m-d') . ' ' . $specificTime);
        $lastSentTime = strtotime($lastSent);

        echo "Current Time: " . date('Y-m-d H:i:s', $currentTime) . "\n";
        echo "Scheduled Time: " . date('Y-m-d H:i:s', $scheduledTime) . "\n";
        echo "Last Sent Time: " . date('Y-m-d H:i:s', $lastSentTime) . "\n";

        // Check if the current time is greater than or equal to the scheduled time
        // and last_sent is not today or last_sent time is earlier than scheduled time
        if (time() >= $scheduledTime && date('Y-m-d', $lastSentTime) != date('Y-m-d') && $lastSentTime < $scheduledTime) {
            echo "Type: daily, Result: true\n";
            return true;
        } else {
            echo "Type: daily, Result: false\n";
            return false;
        }
    }

    return false;
}

function isTimeToSend($specificTime, $lastSent, $type)
{
    if (!empty($specificTime)) {
        $currentTime = time();
        $scheduledTime = strtotime(date('Y-m-d') . ' ' . $specificTime);
        $lastSentTime = strtotime($lastSent);

        echo "Processing report config ID: {$reportConfig['id']}\n";
        echo "Email Time Type: $type\n";
        echo "Specific Time: $specificTime\n";
        echo "Last Sent Time: " . date('Y-m-d H:i:s', $lastSentTime) . "\n";
        echo "Before switch\n";

        switch ($type) {
            case EMAIL_TYPE_DAILY:
            case EMAIL_TYPE_WEEKLY:
            case EMAIL_TYPE_MONTHLY:
            case EMAIL_TYPE_SPECIFIC_TIME:
                // Add a check to see if last sent time is earlier than the specific time
                if ($currentTime >= $scheduledTime && $lastSentTime < $scheduledTime) {
                    $result = true;
                } else {
                    $result = false;
                }
                break;
            // Add more cases as needed

            default:
                $result = false;
        }

        echo "After switch\n";

        return $result;
    }

    return false;
}
// Call the function to send scheduled emails
sendScheduledEmails();