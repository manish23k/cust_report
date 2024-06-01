<?php
// email_config.php
//manish.kadiya29@gmail.com
//aaqu sfxt fhax vmle 
include "header.php";

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


// Set $editMode based on the presence of 'config_id' in the POST request
$editMode = isset($_POST['config_id']);


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

    // Use the fetched $emailConfig to send the email
    // ... (your existing email sending logic)
}




// Function to send a test email using the provided email configuration
function testEmailConfiguration($config)
{
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    // Set up the email configuration
    $mail->isSMTP();
    $mail->SMTPDebug = 1; // Turn off debug mode in production
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

// Database connection
$servername = "localhost";
$username = "cron";
$password = "1234";
$dbname = "asterisk";



try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if it's an AJAX request to get email configuration by ID
    if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['get_config'])) {
        $configId = $_GET['get_config'];
        $getConfigStmt = $conn->prepare("SELECT * FROM email_config WHERE id = ?");
        $getConfigStmt->execute([$configId]);
        $editConfig = $getConfigStmt->fetch(PDO::FETCH_ASSOC);
        header('Content-Type: application/json');
        echo json_encode($editConfig);
        exit();
 
   }

   // Check if the form is submitted for updating email configuration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_email_config'])) {
    // Get values from the form
    $configId = $_POST['config_id'];
    $templateName = $_POST['template_name'];
    $smtpHost = $_POST['host'];
    $smtpAuth = isset($_POST['smtp_auth']) ? 1 : 0;
    $smtpUsername = $_POST['username'];
    $smtpPassword = $_POST['password'];
    $smtpSecure = $_POST['smtp_secure'];
    $smtpPort = $_POST['port'];
    $fromEmail = $_POST['from_email'];
    $fromName = $_POST['from_name'];

    $updateStmt = $conn->prepare("UPDATE email_config SET template_name = ?, host = ?, smtp_auth = ?, username = ?, password = ?, smtp_secure = ?, port = ?, from_email = ?, from_name = ? WHERE id = ?");
    $updateStmt->execute([$templateName, $smtpHost, $smtpAuth, $smtpUsername, $smtpPassword, $smtpSecure, $smtpPort, $fromEmail, $fromName, $configId]);
    
    echo 'Email configuration updated successfully.';
}

 // Check if the form is submitted for saving or updating email configuration
if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['save_email_config']) || isset($_POST['update_email_config']))) {
    // Get values from the form
    $templateName = $_POST['template_name'];
    $smtpHost = $_POST['host'];
    $smtpAuth = isset($_POST['smtp_auth']) ? 1 : 0;
    $smtpUsername = $_POST['username'];
    $smtpPassword = $_POST['password'];
    $smtpSecure = $_POST['smtp_secure'];
    $smtpPort = $_POST['port'];
    $fromEmail = $_POST['from_email'];
    $fromName = $_POST['from_name'];

    if ($editMode) {
        // Update email configuration in the database
        $configId = $_POST['config_id'];
        $updateStmt = $conn->prepare("UPDATE email_config SET template_name = ?, host = ?, smtp_auth = ?, username = ?, password = ?, smtp_secure = ?, port = ?, from_email = ?, from_name = ? WHERE id = ?");
        $updateStmt->execute([$templateName, $smtpHost, $smtpAuth, $smtpUsername, $smtpPassword, $smtpSecure, $smtpPort, $fromEmail, $fromName, $configId]);
        echo 'Email configuration updated successfully.';
    } else {
        // Save email configuration to the database
        $stmt = $conn->prepare("INSERT INTO email_config (template_name, host, smtp_auth, username, password, smtp_secure, port, from_email, from_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$templateName, $smtpHost, $smtpAuth, $smtpUsername, $smtpPassword, $smtpSecure, $smtpPort, $fromEmail, $fromName]);
        echo 'Email configuration saved successfully.';
    }
}

    // Check if the form is submitted for testing email configuration
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['test_email_config'])) {
        // Fetch email configuration from the database
        $configStmt = $conn->query("SELECT * FROM email_config ORDER BY id DESC LIMIT 1");
        $config = $configStmt->fetch(PDO::FETCH_ASSOC);

        // Get values from the test email form
        $testToEmail = $_POST['test_to_email'];
        $testToName = $_POST['test_to_name'];
        $testSubject = $_POST['test_subject'];
        $testMessage = $_POST['test_message'];

        // Set additional parameters for the test email
        $config['to_email'] = $testToEmail;
        $config['to_name'] = $testToName;
        $config['subject'] = $testSubject;
        $config['message'] = $testMessage;

        // Test email configuration and display the result
        $testResult = testEmailConfiguration($config);
        echo $testResult;
    }

    // Check if the form is submitted for deleting email configuration
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_email_config'])) {
        $configId = $_POST['config_id'];
        $deleteStmt = $conn->prepare("DELETE FROM email_config WHERE id = ?");
        $deleteStmt->execute([$configId]);
        echo 'Email configuration deleted successfully.';
    }

} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}

// Check if the form is submitted for editing email configuration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_config'])) {
    $configId = $_POST['config_id'];
    $getConfigStmt = $conn->prepare("SELECT * FROM email_config WHERE id = ?");
    $getConfigStmt->execute([$configId]);
    $editConfig = $getConfigStmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Configuration</title>
        <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            /* height: 100vh; */
        }

        h2 {
            color: #333;
        }

        form {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 400px;
            margin: 20px 0;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
        }

        input,
        select,
        textarea {
            width: calc(100% - 16px);
            padding: 8px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="checkbox"] {
            width: auto;
        }

        button {
            background-color: #4caf50;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button.hidden {
            display: none;
        }

        hr {
            border: 0;
            height: 1px;
            background: #ddd;
            margin: 20px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #4caf50;
            color: #fff;
        }

        table button {
            background-color: #e74c3c;
            color: #fff;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
        }

        table button.edit {
            background-color: #3498db;
        }
    </style>
</head>

<body>
    <h2>Email Configuration</h2>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <label for="template_name">Template Name:</label>
        <input type="text" name="template_name" required
            value="<?php echo isset($editConfig['template_name']) ? $editConfig['template_name'] : ''; ?>"><br>

        <label for="host">SMTP Host:</label>
        <input type="text" name="host" required
            value="<?php echo isset($editConfig['host']) ? $editConfig['host'] : ''; ?>"><br>

        <label for="smtp_auth">SMTP Authentication:</label>
        <input type="checkbox" name="smtp_auth" <?php echo isset($editConfig['smtp_auth']) && $editConfig['smtp_auth'] ? 'checked' : ''; ?>><br>

        <label for="username">Username:</label>
        <input type="text" name="username" required
            value="<?php echo isset($editConfig['username']) ? $editConfig['username'] : ''; ?>"><br>

        <label for="password">Password:</label>
        <input type="password" name="password" required
            value="<?php echo isset($editConfig['password']) ? $editConfig['password'] : ''; ?>"><br>

        <label for="smtp_secure">SMTP Secure:</label>
        <select name="smtp_secure">
            <option value="tls" <?php echo isset($editConfig['smtp_secure']) && $editConfig['smtp_secure'] == 'tls' ? 'selected' : ''; ?>>TLS</option>
            <option value="ssl" <?php echo isset($editConfig['smtp_secure']) && $editConfig['smtp_secure'] == 'ssl' ? 'selected' : ''; ?>>SSL</option>
        </select><br>

        <label for="port">Port:</label>
        <input type="number" name="port" required
            value="<?php echo isset($editConfig['port']) ? $editConfig['port'] : ''; ?>"><br>

        <label for="from_email">From Email:</label>
        <input type="email" name="from_email" required
            value="<?php echo isset($editConfig['from_email']) ? $editConfig['from_email'] : ''; ?>"><br>

        <label for="from_name">From Name:</label>
        <input type="text" name="from_name" required
            value="<?php echo isset($editConfig['from_name']) ? $editConfig['from_name'] : ''; ?>"><br>

        <input type="hidden" name="config_id" value="<?php echo isset($editConfig['id']) ? $editConfig['id'] : ''; ?>">

        <!-- Save Configuration button -->
        <button type="submit" name="save_email_config" class="<?php echo !$editMode ? '' : 'hidden'; ?>">Save
            Configuration</button>

        <!-- Update Configuration button -->
        <button type="submit" name="update_email_config" class="<?php echo $editMode ? '' : 'hidden'; ?>">Update
            Configuration</button>

        <style>
            .hidden {
                display: none;
            }
        </style>

    </form>

    <hr>

    <!-- Test Email Configuration Form -->
    <h2>Test Email Configuration</h2>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <!-- Test Email Configuration Fields -->
        <label for="test_to_email">To Email:</label>
        <input type="email" name="test_to_email" required><br>

        <label for="test_to_name">To Name:</label>
        <input type="text" name="test_to_name" required><br>

        <label for="test_subject">Subject:</label>
        <input type="text" name="test_subject" required><br>

        <label for="test_message">Email Message:</label><br>
        <textarea name="test_message" rows="4" cols="50" required></textarea><br>

        <!-- Test Email Configuration Button -->
        <button type="submit" name="test_email_config">Test Email Configuration</button>
    </form>

    <hr>

    <!-- Email Configurations List -->
    <h2>Email Templates</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Template Name</th>
            <th>User Name</th>
            <th>Action</th>
        </tr>
        <?php
        // Fetch and display email configurations
        $emailConfigStmt = $conn->query("SELECT * FROM email_config");
        while ($config = $emailConfigStmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>{$config['id']}</td>";
            echo "<td>{$config['template_name']}</td>";
            echo "<td>{$config['username']}</td>";
            echo "<td>
            <form action='{$_SERVER["PHP_SELF"]}' method='post' style='display: inline;'>
            <input type='hidden' name='config_id' value='{$config['id']}'>
            <button type='submit' name='edit_config'>Edit</button>
        </form>
                <form action='{$_SERVER["PHP_SELF"]}' method='post' style='display: inline;'>
                    <input type='hidden' name='config_id' value='{$config['id']}'>
                    <button type='submit' name='delete_email_config'>Delete</button>
                </form>
              </td>";
            echo "</tr>";
        }
        ?>
    </table>

</body>

</html>
