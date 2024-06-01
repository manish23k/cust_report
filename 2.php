<?php
include "config.php";
include "header.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch unique type_of_application values
$typeQuery = "SELECT DISTINCT type_of_application FROM (
    SELECT type_of_application FROM binkheti_form_data
    UNION
    SELECT type_of_application FROM hayati_form_data
    UNION
    SELECT type_of_application FROM khedut_form_data
    UNION
    SELECT type_of_application FROM varsayi_form_data
) AS all_types";

$typeResult = $conn->query($typeQuery);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Your Page Title</title>
    <!-- Add Bootstrap CSS link -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-4">

    <form method="post" action="">
        <div class="form-row">
            <div class="col-md-3 mb-3">
                <label for="type_filter">Filter by Type of Application:</label>
                <select class="form-control" name="type_filter" id="type_filter">
                    <option value="ALL">ALL</option>
                    <?php while ($typeRow = $typeResult->fetch_assoc()) : ?>
                        <?php $selected = (isset($_POST['type_filter']) && $_POST['type_filter'] == $typeRow['type_of_application']) ? 'selected' : ''; ?>
                        <option value="<?= $typeRow['type_of_application'] ?>" <?= $selected ?>>
                            <?= $typeRow['type_of_application'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="jilla_filter">Filter by District:</label>
                <select class="form-control" name="jilla_filter" id="jilla_filter">
                    <option value="ALL">ALL</option>
                    <?php
                    // Fetch unique district values for dropdown
                    $jillaQuery = "SELECT DISTINCT applicant_jilla FROM (
                        SELECT applicant_jilla FROM binkheti_form_data
                        UNION
                        SELECT applicant_jilla FROM hayati_form_data
                        UNION
                        SELECT applicant_jilla FROM khedut_form_data
                        UNION
                        SELECT applicant_jilla FROM varsayi_form_data
                    ) AS all_jilla";

                    $jillaResult = $conn->query($jillaQuery);
                    while ($jillaRow = $jillaResult->fetch_assoc()) {
                        $selected = (isset($_POST['jilla_filter']) && $_POST['jilla_filter'] == $jillaRow['applicant_jilla']) ? 'selected' : '';
                        echo "<option value='{$jillaRow['applicant_jilla']}' $selected>{$jillaRow['applicant_jilla']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-3 mb-3">
                <label for="filter_satisfied">Satisfied Applicant?</label>
                <select id="filter_satisfied" class="form-control" name="filter_satisfied">
                    <option value="all" <?= isset($_POST['filter_satisfied']) && $_POST['filter_satisfied'] == 'all' ? 'selected' : ''; ?>>All</option>
                    <option value="yes" <?= isset($_POST['filter_satisfied']) && $_POST['filter_satisfied'] == 'yes' ? 'selected' : ''; ?>>Yes</option>
                    <option value="no" <?= isset($_POST['filter_satisfied']) && $_POST['filter_satisfied'] == 'no' ? 'selected' : ''; ?>>No</option>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="start_date">Start Date:</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : ''; ?>">
            </div>

            <div class="col-md-3 mb-3">
                <label for="end_date">End Date:</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : ''; ?>">
            </div>

            <div class="col-md-2 mb-3">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
            <div class="col-md-3 mb-3">
                <label class="invisible">Export to XLSX</label>
                <button type="submit" id="fetchAndExportData" name="fetchAndExportData" value="xlsx" class="btn btn-success form-control">Export to XLSX</button>
            </div>
        </div>
    </form>

    <?php
    // Get the filters
    $typeOfApplicationFilter = isset($_POST['type_filter']) ? $_POST['type_filter'] : 'ALL';
    $jillaFilter = isset($_POST['jilla_filter']) ? $_POST['jilla_filter'] : 'ALL';
    $satisfiedFilter = isset($_POST['filter_satisfied']) ? $_POST['filter_satisfied'] : 'all';
    $startDate = isset($_POST['start_date']) ? $_POST['start_date'] : '';
    $endDate = isset($_POST['end_date']) ? $_POST['end_date'] : '';

    // Function to fetch and display data
    function fetchAndDisplayData($table, $columns, $typeOfApplicationFilter, $jillaFilter, $satisfiedFilter, $startDate, $endDate, $satisfiedColumn, $conn) {
        $sql = "SELECT $columns FROM $table WHERE 1=1";

        if ($typeOfApplicationFilter !== 'ALL') {
            $sql .= " AND type_of_application = '$typeOfApplicationFilter'";
        }
        if ($jillaFilter !== 'ALL') {
            $sql .= " AND applicant_jilla = '$jillaFilter'";
        }
        if ($satisfiedFilter !== 'all') {
            $sql .= " AND $satisfiedColumn = '$satisfiedFilter'";
        }
        if (!empty($startDate) && !empty($endDate)) {
            $formattedStartDate = date("Y-m-d", strtotime($startDate));
            $formattedEndDate = date("Y-m-d", strtotime($endDate));
            $sql .= " AND entry_datetime BETWEEN '$formattedStartDate' AND '$formattedEndDate'";
        }

        echo "<!-- SQL Query: $sql -->"; // Debugging line to print the SQL query

        $result = $conn->query($sql);

        if ($result === false) {
            echo "Error executing query: " . $conn->error;
        } else {
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Fetching recording location based on rec_id
                    $rec_id = $row['rec_id'];
                    $recordingQuery = "SELECT location FROM recording_log WHERE recording_id = $rec_id";
                    $recordingResult = $conn->query($recordingQuery);

                    if ($recordingResult === false) {
                        $recordingLocation = 'N/A';
                    } else {
                        if ($recordingResult->num_rows > 0) {
                            $recordingData = $recordingResult->fetch_assoc();
                            $recordingLocation = $recordingData['location'];
                        } else {
                            $recordingLocation = 'N/A';
                        }
                    }

                    echo "
                        <tr>
                            <td>{$row['id']}</td>
                            <td>{$row['type_of_application']}</td>
                            <td>{$row['application_no']}</td>
                            <td>{$row['applicant_jilla']}</td>
                            <td><audio controls><source src='$recordingLocation' type='audio/mpeg'></audio></td>
                            <td>{$row['application_3']}</td>
                            <td>{$row['application_3_1']}</td>
                            <td>{$row['multiple_selection_4']}</td>
                            <td>{$row['nikal_mangani']}</td>
                            <td>{$row['nikal_mangani_by_7']}</td>
                            <td>{$row['satisfied']}</td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='11'>No data matching the selected filters.</td></tr>";
            }
        }
    }

    // Function to fetch data and export to XLSX
    function fetchAndExportData($typeOfApplicationFilter, $jillaFilter, $satisfiedFilter, $startDate, $endDate, $conn) {
        $tables = [
            'binkheti_form_data' => 'binkheti_satisfied',
            'hayati_form_data' => 'hayati_satisfied',
            'khedut_form_data' => 'khedut_satisfied',
            'varsayi_form_data' => 'varsayi_satisfied'
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Form Data');

        // Set the header row
        $header = [
            'જી નંબર', 'Type of Application', 'Application Number', 'Applicant District', 'File',
            'Application 3', 'Application 3_1', 'Multiple Selection 4', 'Nikal Mangani',
            'Nikal Mangani by 7', 'Satisfied'
        ];
        $sheet->fromArray($header, NULL, 'A1');

        $rowNumber = 2; // Start from the second row
        foreach ($tables as $table => $satisfiedColumn) {
            $columns = 'id, type_of_application, application_no, applicant_jilla, rec_id, application_3, application_3_1, multiple_selection_4, nikal_mangani, nikal_mangani_by_7, satisfied';
            $sql = "SELECT $columns FROM $table WHERE 1=1";

            if ($typeOfApplicationFilter !== 'ALL') {
                $sql .= " AND type_of_application = '$typeOfApplicationFilter'";
            }
            if ($jillaFilter !== 'ALL') {
                $sql .= " AND applicant_jilla = '$jillaFilter'";
            }
            if ($satisfiedFilter !== 'all') {
                $sql .= " AND $satisfiedColumn = '$satisfiedFilter'";
            }
            if (!empty($startDate) && !empty($endDate)) {
                $formattedStartDate = date("Y-m-d", strtotime($startDate));
                $formattedEndDate = date("Y-m-d", strtotime($endDate));
                $sql .= " AND entry_datetime BETWEEN '$formattedStartDate' AND '$formattedEndDate'";
            }

            $result = $conn->query($sql);

            if ($result !== false && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $data = [
                        $row['id'],
                        $row['type_of_application'],
                        $row['application_no'],
                        $row['applicant_jilla'],
                        $row['rec_id'],
                        $row['application_3'],
                        $row['application_3_1'],
                        $row['multiple_selection_4'],
                        $row['nikal_mangani'],
                        $row['nikal_mangani_by_7'],
                        $row['satisfied']
                    ];
                    $sheet->fromArray($data, NULL, "A$rowNumber");
                    $rowNumber++;
                }
            }
        }

        // Output the spreadsheet to the browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="form_data.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }

    if (isset($_POST['export']) && $_POST['export'] == 'xlsx') {
        fetchAndExportData($typeOfApplicationFilter, $jillaFilter, $satisfiedFilter, $startDate, $endDate, $conn);
    }

    echo '<table class="table table-bordered">';
    echo '<thead>
                <tr>
                    <th>જી નંબર</th>
                    <th>Type of Application</th>
                    <th>Application Number</th>
                    <th>Applicant District</th>
                    <th>File</th>
                    <th>Application 3</th>
                    <th>Application 3_1</th>
                    <th>Multiple Selection 4</th>
                    <th>Nikal Mangani</th>
                    <th>Nikal Mangani by 7</th>
                    <th>Satisfied</th>
                </tr>
            </thead>
            <tbody>';

    fetchAndDisplayData('binkheti_form_data', 'id, type_of_application, application_no, applicant_jilla, rec_id, binkheti_application_3 AS application_3, binkheti_application_3_1 AS application_3_1, binkheti_multiple_selection_4 AS multiple_selection_4, nikal_mangani, nikal_mangani_by_7, binkheti_satisfied AS satisfied', $typeOfApplicationFilter, $jillaFilter, $satisfiedFilter, $startDate, $endDate, 'binkheti_satisfied', $conn);
    fetchAndDisplayData('hayati_form_data', 'id, type_of_application, application_no, applicant_jilla, rec_id, hayati_application_3 AS application_3, hayati_application_3_1 AS application_3_1, hayati_multiple_selection_4 AS multiple_selection_4, nikal_mangani, nikal_mangani_by_7, hayati_satisfied AS satisfied', $typeOfApplicationFilter, $jillaFilter, $satisfiedFilter, $startDate, $endDate, 'hayati_satisfied', $conn);
    fetchAndDisplayData('khedut_form_data', 'id, type_of_application, application_no, applicant_jilla, rec_id, khedut_application_3 AS application_3, khedut_application_3_1 AS application_3_1, khedut_multiple_selection_4 AS multiple_selection_4, khedut_nikal_mangani AS nikal_mangani, khedut_nikal_mangani_by_7 AS nikal_mangani_by_7, khedut_satisfied AS satisfied', $typeOfApplicationFilter, $jillaFilter, $satisfiedFilter, $startDate, $endDate, 'khedut_satisfied', $conn);
    fetchAndDisplayData('varsayi_form_data', 'id, type_of_application, application_no, applicant_jilla, rec_id, varsayi_application_3 AS application_3, varsayi_application_3_1 AS application_3_1, varsayi_multiple_selection_4 AS multiple_selection_4, varsayi_nikal_mangani AS nikal_mangani, varsayi_nikal_mangani_by_7 AS nikal_mangani_by_7, varsayi_satisfied AS satisfied', $typeOfApplicationFilter, $jillaFilter, $satisfiedFilter, $startDate, $endDate, 'varsayi_satisfied', $conn);

    echo '</tbody></table>';

    // Close the database connection
    $conn->close();
    ?>
</div>

<!-- Add Bootstrap and jQuery JavaScript links -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
