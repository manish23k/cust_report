<?php

ob_start(); // Start output buffering

include "config.php";
include "header.php";
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
$starRatingFilter = isset($_GET['star_rating']) ? $_GET['star_rating'] : '';
$answerFilter = isset($_GET['answer_filter']) ? $_GET['answer_filter'] : '';
$callDateFromFilter = isset($_GET['call_date_from']) ? $_GET['call_date_from'] : '';
$callDateToFilter = isset($_GET['call_date_to']) ? $_GET['call_date_to'] : '';
$wildcardFilter = isset($_GET['wildcard_filter']) ? $_GET['wildcard_filter'] : '';

// Fetch data from the hayati_form_data table with filters and converted duration
$sql = "SELECT vfd.*, rl.location AS recording_location, SEC_TO_TIME(vl.length_in_sec) AS duration, 
        DATE(vl2.call_date) AS call_date, 
        TIME(vl2.call_date) AS call_time
        FROM varsayi_form_data vfd
        INNER JOIN recording_log rl ON vfd.rec_id = rl.recording_id
        INNER JOIN vicidial_log vl ON vfd.phonenumber = vl.phone_number
        INNER JOIN vicidial_log vl2 ON vfd.lead_id = vl2.lead_id
        WHERE (`agent` LIKE '%$agentFilter%')
          AND (`phonenumber` LIKE '%$phoneNumberFilter%')
          " . (!empty($callDateFromFilter) || !empty($callDateToFilter) ? "AND DATE(vl2.call_date) BETWEEN '$callDateFromFilter' AND '$callDateToFilter'" : "") . "
          AND (`application_no` LIKE '%$applicationNoFilter%')
          AND (`application_date` LIKE '%$applicationDateFilter%')
          AND (`disposal_date` LIKE '%$disposalDateFilter%')
          AND (`star_rating` = '$starRatingFilter' OR '$starRatingFilter' = '')
          AND (
            `varsayi_application_2` LIKE '%$answerFilter%' OR `varsayi_application_2` LIKE '%$answerFilter%'
  )
  AND (
    CONCAT_WS(' ', `agent`, `phonenumber`, `application_no`, `application_date`, `disposal_date`, `applicant_name`, `varsayi_relativename`, `applicant_location`, `applicant_taluko`, `applicant_jilla`, `star_rating`, 
    `varsayi_application_2`, `varsayi_application_3`, `varsayi_application_3_1`, `varsayi_difficulty_4`, `varsayi_multiple_selection_4`, 
    `varsayi_application_5`, `varsayi_application_reason_5`, `varsayi_application_response_6`, `varsayi_office_selection_6`, 
    `varsayi_nikal_mangani`, `varsayi_nikal_mangani_by_7`, `varsayi_service_confirmation`, `suggestion`)
    LIKE '%$wildcardFilter%'
)
  GROUP BY vfd.id desc";

$result = $conn->query($sql);

// Check for errors
if (!$result) {
    die("Query failed: " . $conn->error);
}

// Fetch the total record count
$totalRecords = $result->num_rows;

// Export data to XLSX if requested
if (isset($_GET['export']) && $_GET['export'] == 'xlsx') {
    // Check if there are any rows in the result set
    if ($result->num_rows > 0) {
        // Create a new PhpSpreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add headers to the worksheet
        $headers = [
            'Sr No.',
            'Agent',
            'Phone Number',
            'Call Date',
            'Call Time',
            'Duration',
            'Record ID',
            'Type of Application',
            'Application No',
            'Application Date',
            'Disposal Date',
            'Applicant Name',
            'varsayi_relativename',
            'Applicant Location',
            'Applicant Taluko',
            'Applicant Jilla',
            'Varsayi Application 2',
            'Varsayi Application 3',
            'Varsayi Application 3.1',
            'Varsayi Difficulty 4',
            'Varsayi Multiple Selection 4',
            'Varsayi Application 5',
            'Varsayi Application Reason 5',
            'Varsayi Application Response 6',
            'Varsayi Office Selection 6',
            'Nikal Mangani',
            'Nikal Mangani By 7',
            'Service Confirmation',
            'satisfaction',
            'Star Rating',
            'Additional Comments'
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

            $sheet->setCellValue($col . $rowIndex, $row['id']); // ID
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['agent']); // Agent
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['phonenumber']); // Phone Number
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['call_date']); // Call Date
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['call_time']); // Call Time
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['duration']); // Duration
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['recording_location']); // Recording
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['type_of_application']); // Type of Application
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['application_no']); // Application No
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['application_date']); // Application Date
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['disposal_date']); // Disposal Date
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['applicant_name']);
             $col++;
            $sheet->setCellValue($col . $rowIndex, $row['varsayi_relativename']);
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['applicant_location']);
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['applicant_taluko']);
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['applicant_jilla']);
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['varsayi_application_2']);
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['varsayi_application_3']);
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['varsayi_application_3_1']);
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['varsayi_difficulty_4']);
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['varsayi_multiple_selection_4']);
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['varsayi_application_5']);
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['varsayi_application_reason_5']);
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['varsayi_application_response_6']);
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['varsayi_office_selection_6']);
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['varsayi_nikal_mangani']);
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['varsayi_nikal_mangani_by_7']);
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['varsayi_service_confirmation']);
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['varsayi_satisfied']);
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['star_rating']);
            $col++;
            $sheet->setCellValue($col . $rowIndex, $row['suggestion']);
            $col++;



            $rowIndex++;
        }

        // Set headers for download with dynamic filename
        $filename = 'varsayi_report_output_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // Clear output buffer before sending headers
        ob_clean();

        // Save the Excel file
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        // Stop further processing
        exit;
    } else {
        echo "0 results";
    }
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
     <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Data Filter Report</title>
     <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h2 {
            color: #333;
            padding: 40px;
            text-align: center;
            /* Center the text */
        }

        form {
            margin-bottom: 20px;
            padding: 20px;
            background-color: #f2f2f2;
            border-radius: 5px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        select,
        input,
        button {
            margin-bottom: 10px;
            padding: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        audio {

            margin-top: 5px;
            /* Add a margin to the audio player */
        }
    </style>
</head>

<body>
    <h2>વરસાઈ રિપોર્ટ</h2>

    <form action="" method="GET" class="row">
        <div class="col-md-2 mb-3">
            <label for="agent">Agent:</label>
            <select name="agent" class="form-control">
                <option value="">Select Agent</option>
                <?php
                // Fetch unique agents from the database
                $agentQuery = "SELECT DISTINCT user FROM vicidial_users WHERE user_level <= 6 AND user NOT IN ('VDAD', 'VDCL')";
                $agentResult = $conn->query($agentQuery);

                if ($agentResult->num_rows > 0) {
                    while ($agentRow = $agentResult->fetch_assoc()) {
                        $selected = ($agentRow['user'] == $agentFilter) ? 'selected' : '';
                        echo "<option value='{$agentRow['user']}' $selected>{$agentRow['user']}</option>";
                    }
                }
                ?>
            </select>
        </div>

        <div class="col-md-2 mb-3">
            <label for="phone_number">Phone Number:</label>
            <input type="text" name="phone_number" class="form-control">
        </div>

        <div class="col-md-2 mb-3">
    <label for="call_date_from">Call Date From:</label>
    <input type="date" name="call_date_from" class="form-control" value="<?php echo $callDateFromFilter; ?>">
</div>

<div class="col-md-2 mb-3">
    <label for="call_date_to">Call Date To:</label>
    <input type="date" name="call_date_to" class="form-control" value="<?php echo $callDateToFilter; ?>">
</div>


        <div class="col-md-2 mb-3">
            <label for="application_no">Application No:</label>
            <input type="text" name="application_no" class="form-control">
        </div>

        <div class="col-md-2 mb-3">
            <label for="application_date">Application Date:</label>
            <input type="date" name="application_date" class="form-control">
        </div>

        <div class="col-md-2 mb-3">
            <label for="disposal_date">Disposal Date:</label>
            <input type="date" name="disposal_date" class="form-control">
        </div>

        <!-- Add this code inside the form -->
        <div class="col-md-2 mb-3">
            <label for="star_rating">Star Rating:</label>
            <select name="star_rating" class="form-control">
                <option value="">Select Star</option>
                <?php
                // Options for star rating
                $starRatings = [1, 2, 3, 4, 5];

                foreach ($starRatings as $rating) {
                    $selected = ($rating == $starRatingFilter) ? 'selected' : '';
                    echo "<option value='$rating' $selected>$rating</option>";
                }
                ?>
            </select>
        </div>

        <div class="col-md-2 mb-3">
            <label for="answer_filter">Answer Filter:</label>
            <select name="answer_filter" class="form-control">
                <option value="">Select Answer</option>
                <option value="હા" <?php echo ($answerFilter === 'હા') ? 'selected' : ''; ?>>હા</option>
                <option value="ના" <?php echo ($answerFilter === 'ના') ? 'selected' : ''; ?>>ના</option>
            </select>
        </div>

        <div class="col-md-2 mb-3">
    <label for="wildcard_filter">Wildcard Filter:</label>
    <input type="text" name="wildcard_filter" class="form-control" value="<?php echo isset($_GET['wildcard_filter']) ? $_GET['wildcard_filter'] : ''; ?>">
</div>


        <div class="col-md-3 mb-3">
            <label class="invisible">Search Button</label>
            <button type="submit" name="search" class="btn btn-primary form-control">Search</button>
        </div>
        <div class="col-md-3 mb-3">
            <label class="invisible">Export to XLSX</label>
            <!-- <button type="submit" name="export" value="xlsx">Export to XLSX</button> -->
            <!-- <button type="button" onclick="downloadCSV()" class="btn btn-success form-control">Download CSV</button> -->

            <button type="submit" name="export" value="xlsx" class="btn-success form-control">Export to XLSX</button>
        </div>
    </form>

    <!-- Display total record count -->
    <h2>Total Records: <?php echo $totalRecords; ?></h2>


    <?php

    // Display data in HTML table
    if ($result->num_rows > 0) {
    // Display a single table with Bootstrap styling
    echo "<table class='table table-bordered table-striped mt-4'>
            <thead class='thead-dark'>
            <th>Sr No.</th>
            <th>Agent</th>
            <th>Phone Number</th>
            <th>Call Date</th>
            <th>Call Time</th>
            <th>Duration</th>
            <th>Recording</th>
            <th>Type of Application</th>
            <th>Application No</th>
            <th>Application Date</th>
            <th>Disposal Date</th>
            <th>Applicant Name</th>
            <th>Relative Name</th>
            <th>Applicant Location</th>
            <th>Applicant Taluko</th>
            <th>Applicant Jilla</th>
            <th>Varsayi Application 2</th>
            <th>Varsayi Application 3</th>
            <th>Varsayi Application 3.1</th>
            <th>Varsayi Difficulty 4</th>
            <th>Varsayi Multiple Selection 4</th>
            <th>Varsayi Application 5</th>
            <th>Varsayi Application Reason 5</th>
            <th>Varsayi Application Response 6</th>
            <th>Varsayi Office Selection 6</th>
            <th>Nikal Mangani</th>
            <th>Nikal Mangani By 7</th>
            <th>Service Confirmation</th>
            <th>satisfaction</th>
            <th>Star Rating</th>
            <th>Additional Comments</th>
        </tr>";
  $index = 1; // Initialize index
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                  <td>{$index}</td>
                <td>{$row['agent']}</td>
                <td>{$row['phonenumber']}</td>
                <td>{$row['call_date']}</td>
                <td>{$row['call_time']}</td>
                <td>{$row['duration']}</td>


     <td>";

            // Check if recording location is not empty
            if (!empty($row['recording_location'])) {
                // Display audio player
                echo "<audio controls>
                <source src='{$row['recording_location']}' type='audio/mpeg'>
        Your browser does not support the audio element.
      </audio>";
            }

            echo "</td>
                <td>{$row['type_of_application']}</td>
                <td>{$row['application_no']}</td>
                <td>{$row['application_date']}</td>
                <td>{$row['disposal_date']}</td>
                <td>{$row['applicant_name']}</td>
                <td>{$row['varsayi_relativename']}</td>
                <td>{$row['applicant_location']}</td>
                <td>{$row['applicant_taluko']}</td>
                <td>{$row['applicant_jilla']}</td>
                <td>{$row['varsayi_application_2']}</td>
                <td>{$row['varsayi_application_3']}</td>
                <td>{$row['varsayi_application_3_1']}</td>
                <td>{$row['varsayi_difficulty_4']}</td>
                <td>{$row['varsayi_multiple_selection_4']}</td>
                <td>{$row['varsayi_application_5']}</td>
                <td>{$row['varsayi_application_reason_5']}</td>
                <td>{$row['varsayi_application_response_6']}</td>
                <td>{$row['varsayi_office_selection_6']}</td>
                <td>{$row['varsayi_nikal_mangani']}</td>
                <td>{$row['varsayi_nikal_mangani_by_7']}</td>
                <td>{$row['varsayi_service_confirmation']}</td>
                <td>{$row['varsayi_satisfied']}</td>
                <td>{$row['star_rating']}</td>
                <td>{$row['suggestion']}</td>
            </tr>";
             $index++; // Increment index
        }
        echo "</table>";

    }

    $conn->close();
    ?>

    <!-- Add Bootstrap JS and Popper.js scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>