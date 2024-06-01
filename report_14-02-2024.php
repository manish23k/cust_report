<?php
include "config.php";
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Create connection
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize filter variables
$agentFilter = isset($_GET['agent']) ? $_GET['agent'] : '';
$phoneNumberFilter = isset($_GET['phone_number']) ? $_GET['phone_number'] : '';
$applicationNoFilter = isset($_GET['application_no']) ? $_GET['application_no'] : '';
$applicationDateFilter = isset($_GET['application_date']) ? $_GET['application_date'] : '';
$disposalDateFilter = isset($_GET['disposal_date']) ? $_GET['disposal_date'] : '';

// Fetch data from the binkheti_form_data table with filters
$sql = "SELECT * FROM binkheti_form_data
        WHERE (`agent` LIKE '%$agentFilter%')
          AND (`phonenumber` LIKE '%$phoneNumberFilter%')
          AND (`application_no` LIKE '%$applicationNoFilter%')
          AND (`application_date` LIKE '%$applicationDateFilter%')
          AND (`disposal_date` LIKE '%$disposalDateFilter%')";

$result = $conn->query($sql);

// Check for errors
if (!$result) {
    die("Query failed: " . $conn->error);
}


// Export data to XLSX if requested
if (isset($_GET['export']) && $_GET['export'] == 'xlsx') {
    // Check if there are any rows in the result set
    if ($result->num_rows > 0) {
        // Create a new PhpSpreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add headers to the worksheet
        $headers = [
            'ID', 'Agent', 'Phone Number', 'Record ID', 'Type of Application', 'Application No',
            'Application Date', 'Disposal Date', 'Applicant Name', 'Applicant Location', 'Applicant Taluko',
            'Applicant Jilla', 'Iora Application 2', 'Iora Application 3', 'Iora Application 3.1',
            'Iora Difficulty 4', 'Iora Multiple Selection 4', 'Iora Application 5',
            'Iora Application Reason 5', 'Iora Application Response 6', 'Iora Office Selection 6',
            'Nikal Mangani', 'Nikal Mangani By 7', 'Iora Difficulty 8', 'Iora Application 8',
            'Phone Verification 8.1', 'Phone Verification Option 8.1', 'Star Rating', 'Additional Comments'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        // Add data to the worksheet
        $rowIndex = 2;
        while ($row = $result->fetch_assoc()) {
            $col = 'A';
            foreach ($row as $cell) {
                $sheet->setCellValue($col . $rowIndex, $cell);
                $col++;
            }
            $rowIndex++;
        }

// Set headers for download with dynamic filename
$filename = 'report_output_' . date('Ymd_His') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Save the Excel file
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

        // Stop further processing
        exit;
    } else {
        echo "0 results";
    }
}
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>Data Filter</title>
</head>

<body>
    <h2>Data Filter</h2>

    <form action="" method="GET">
        <label for="agent">Agent:</label>
        <!-- Create a dropdown list for agents -->
        <select name="agent">
            <option value="">Select Agent</option>
            <?php
            // Fetch unique agents from the database
            $agentQuery = "SELECT DISTINCT agent FROM binkheti_form_data";
            //$agentQuery = "SELECT DISTINCT user FROM vicidial_users WHERE user_level <= 6 AND user NOT IN ('VDAD', 'VDCL')";
            $agentResult = $conn->query($agentQuery);

            if ($agentResult->num_rows > 0) {
                while ($agentRow = $agentResult->fetch_assoc()) {
                    $selected = ($agentRow['agent'] == $agentFilter) ? 'selected' : '';
                    echo "<option value='{$agentRow['agent']}' $selected>{$agentRow['agent']}</option>";
                }
            }
            ?>
        </select>

        <label for="phone_number">Phone Number:</label>
        <input type="text" name="phone_number">

        <label for="application_no">Application No:</label>
        <input type="text" name="application_no">

        <label for="application_date">Application Date:</label>
        <input type="date" name="application_date">

        <label for="disposal_date">Disposal Date:</label>
        <input type="date" name="disposal_date">

        <button type="submit">Apply Filters</button>
        <button type="submit" name="export" value="xlsx">Export to XLSX</button>
    </form>




<?php

// Display data in HTML table
if ($result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr>
            <th>ID</th>
            <th>Agent</th>
            <th>Phone Number</th>
            <th>Record ID</th>
            <th>Type of Application</th>
            <th>Application No</th>
            <th>Application Date</th>
            <th>Disposal Date</th>
            <th>Applicant Name</th>
            <th>Applicant Location</th>
            <th>Applicant Taluko</th>
            <th>Applicant Jilla</th>
            <th>Iora Application 2</th>
            <th>Iora Application 3</th>
            <th>Iora Application 3.1</th>
            <th>Iora Difficulty 4</th>
            <th>Iora Multiple Selection 4</th>
            <th>Iora Application 5</th>
            <th>Iora Application Reason 5</th>
            <th>Iora Application Response 6</th>
            <th>Iora Office Selection 6</th>
            <th>Nikal Mangani</th>
            <th>Nikal Mangani By 7</th>
            <th>Iora Difficulty 8</th>
            <th>Iora Application 8</th>
            <th>Phone Verification 8.1</th>
            <th>Phone Verification Option 8.1</th>
            <th>Star Rating</th>
            <th>Additional Comments</th>
        </tr>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['agent']}</td>
                <td>{$row['phonenumber']}</td>
                <td>{$row['rec_id']}</td>
                <td>{$row['type_of_application']}</td>
                <td>{$row['application_no']}</td>
                <td>{$row['application_date']}</td>
                <td>{$row['disposal_date']}</td>
                <td>{$row['applicant_name']}</td>
                <td>{$row['applicant_location']}</td>
                <td>{$row['applicant_taluko']}</td>
                <td>{$row['applicant_jilla']}</td>
                <td>{$row['iora_application_2']}</td>
                <td>{$row['iora_application_3']}</td>
                <td>{$row['iora_application_3_1']}</td>
                <td>{$row['iora_difficulty_4']}</td>
                <td>{$row['iora_multiple_selection_4']}</td>
                <td>{$row['iora_application_5']}</td>
                <td>{$row['iora_application_reason_5']}</td>
                <td>{$row['iora_application_response_6']}</td>
                <td>{$row['iora_office_selection_6']}</td>
                <td>{$row['nikal_mangani']}</td>
                <td>{$row['nikal_mangani_by_7']}</td>
                <td>{$row['iora_difficulty_8']}</td>
                <td>{$row['iora_application_8']}</td>
                <td>{$row['phone_verification_8_1']}</td>
                <td>{$row['phone_verification_option_8_1']}</td>
                <td>{$row['star_rating']}</td>
                <td>{$row['additional_comments']}</td>
            </tr>";
        }
        echo "</table>";

    }


    $conn->close();
    ?>
</body>

</html>