<script>
        function exportToExcel() {
            window.location = 'App_Call_Summary.xlsx';
        }
</script>
<?php
include "config.php";
include "header.php";

require 'vendor/autoload.php'; // Adjust the path to autoload.php according to your project structure

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch unique type_of_application values for dropdown from all four tables
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
    <title>તમામ અરજીનો રીપોર્ટ</title>
    <!-- Add Bootstrap CSS link -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-4">
   <h1 class="mb-3" align="center">તમામ અરજીનો રીપોર્ટ</h1>
</br>
    <form method="post" action="">
        <div class="form-row">
            <div class="col-md-3 mb-3">
                <label for="type_filter">એપ્લિકેશનના પ્રકાર દ્વારા ફિલ્ટર કરો:</label>
                <select class="form-control" name="type_filter" id="type_filter">
                    <option value="ALL">ALL</option>
                    <?php while ($typeRow = $typeResult->fetch_assoc()) : ?>
                        <?php $selected = ($_POST['type_filter'] == $typeRow['type_of_application']) ? 'selected' : ''; ?>
                        <option value="<?= $typeRow['type_of_application'] ?>" <?= $selected ?>>
                            <?= $typeRow['type_of_application'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2 mb-3">
                <label for="star_rating_filter">સ્ટાર રેટિંગ દ્વારા ફિલ્ટર કરો:</label>
                <select class="form-control" name="star_rating_filter" id="star_rating_filter">
                    <option value="">All</option>
                    <?php for ($i = 1; $i <= 5; $i++) : ?>
                        <?php $selected = ($_POST['star_rating_filter'] == $i) ? 'selected' : ''; ?>
                        <option value="<?= $i ?>" <?= $selected ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2 mb-3">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
            <div class="col-md-3 mb-3">
                <label class="invisible">Export to XLSX</label>
                <button type="button" onclick="exportToExcel()" class="btn btn-success form-control">Export to XLSX</button>
            </div>
        </div>
    </form>

    <?php
    // Get the type of application and star rating filters
    $typeOfApplicationFilter = isset($_POST['type_filter']) ? $_POST['type_filter'] : 'ALL';
    $starRatingFilter = isset($_POST['star_rating_filter']) ? $_POST['star_rating_filter'] : '';
    $recordsPerPage = 5;
    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($currentPage - 1) * $recordsPerPage;

    // Function to fetch the total number of records
    function getTotalRecords($table, $typeOfApplicationFilter, $starRatingFilter) {
        global $conn;

        $typeFilterCondition = ($typeOfApplicationFilter != 'ALL') ? "AND type_of_application = '$typeOfApplicationFilter'" : "";
        $starRatingFilterCondition = ($starRatingFilter != '') ? "AND star_rating = '$starRatingFilter'" : "";

        $countSql = "SELECT COUNT(*) as total FROM $table WHERE 1 $typeFilterCondition $starRatingFilterCondition";
        $countResult = $conn->query($countSql);
        return $countResult->fetch_assoc()['total'];
    }

    // Function to fetch and display data based on the type of application and star rating from all four tables
    function fetchAndDisplayData($table, $columns, $typeOfApplicationFilter, $starRatingFilter, &$index, $offset, $limit) {
        global $conn;
        
        $typeFilterCondition = ($typeOfApplicationFilter != 'ALL') ? "AND type_of_application = '$typeOfApplicationFilter'" : "";
        $starRatingFilterCondition = ($starRatingFilter != '') ? "AND star_rating = '$starRatingFilter'" : "";

        // Fetch data with limit and offset
        
        if($typeOfApplicationFilter != 'ALL') {
            $sql = "SELECT $columns FROM $table  WHERE 1 $typeFilterCondition $starRatingFilterCondition LIMIT $offset, $limit";
        } else {
            $sql = "SELECT id, type_of_application, application_no, applicant_name, applicant_jilla, applicant_taluko, applicant_location, varsayi_application_3, varsayi_application_3_1, star_rating FROM varsayi_form_data union SELECT id, type_of_application, application_no, applicant_name, applicant_jilla, applicant_taluko, applicant_location, binkheti_application_3, binkheti_application_3_1, star_rating FROM binkheti_form_data union SELECT id, type_of_application, application_no, applicant_name, applicant_jilla, applicant_taluko, applicant_location, hayati_application_3, hayati_application_3_1, star_rating FROM hayati_form_data union SELECT id, type_of_application, application_no, applicant_name, applicant_jilla, applicant_taluko, applicant_location, khedut_application_3, khedut_application_3_1, star_rating FROM khedut_form_data  WHERE 1 $typeFilterCondition $starRatingFilterCondition LIMIT $offset, $limit";
        }
        
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>{$index}</td>
                    <td>{$row['type_of_application']}</td>
                    <td>{$row['application_no']}</td>
                    <td>{$row['applicant_name']}</td>
                    <td>{$row['applicant_jilla']}</td>
                    <td>{$row['applicant_taluko']}</td>
                    <td>{$row['applicant_location']}</td>";

                // Handling specific type_of_application cases
                if ($typeOfApplicationFilter === 'LRC-65') {
                    $application3Column = 'binkheti_application_3';
                    $application31Column = 'binkheti_application_3_1';
                } elseif ($typeOfApplicationFilter === 'Hayatima Hakkdakhal Mutation') {
                    $application3Column = 'hayati_application_3';
                    $application31Column = 'hayati_application_3_1';
                } elseif ($typeOfApplicationFilter === 'Khedut Kharai Certificate') {
                    $application3Column = 'khedut_application_3';
                    $application31Column = 'khedut_application_3_1';
                } elseif ($typeOfApplicationFilter === 'Varsai Mutation') {
                    $application3Column = 'varsayi_application_3';
                    $application31Column = 'varsayi_application_3_1';
                } else {
                    // Fetch all columns for ALL
                    if ($table === 'binkheti_form_data') {
                        $application3Column = 'binkheti_application_3';
                        $application31Column = 'binkheti_application_3_1';
                    } elseif ($table === 'hayati_form_data') {
                        $application3Column = 'hayati_application_3';
                        $application31Column = 'hayati_application_3_1';
                    } elseif ($table === 'khedut_form_data') {
                        $application3Column = 'khedut_application_3';
                        $application31Column = 'khedut_application_3_1';
                    } elseif ($table === 'varsayi_form_data') {
                        $application3Column = 'varsayi_application_3';
                        $application31Column = 'varsayi_application_3_1';
                    }
                }

                echo "<td>{$row[$application3Column]}</td>
                    <td>{$row[$application31Column]}</td>
                    <td>{$row['star_rating']}</td>
                </tr>";

                $index++;
            }
        }

        return $result->num_rows;
    }

    // Calculate total records
    $totalRecords = getTotalRecords('binkheti_form_data', $typeOfApplicationFilter, $starRatingFilter) +
                    getTotalRecords('hayati_form_data', $typeOfApplicationFilter, $starRatingFilter) +
                    getTotalRecords('khedut_form_data', $typeOfApplicationFilter, $starRatingFilter) +
                    getTotalRecords('varsayi_form_data', $typeOfApplicationFilter, $starRatingFilter);

    // Calculate total pages
    $totalPages = ceil($totalRecords / $recordsPerPage);
    ?>
    <h2>કુલ રેકોર્ડ્સ: <?php echo $totalRecords; ?></h2>
    <?php
    // Initialize the index counter
    $index = $offset + 1;

    // Display a single table with Bootstrap styling
    echo "<table class='table table-bordered table-striped mt-4'>
            <thead class='thead-dark'>
                <tr>
                    <th>ક્રમ</th>
                    <th>અરજી નો પ્રકાર</th>
                    <th>અરજી નંબર</th>
                    <th>નામ</th>
                    <th>જિલ્લો</th>
                    <th>તાલુકો</th>
                    <th>ગામ</th>
                    <th>અરજી જાતે કરેલી?</th>
                    <th>કોના મારફત કરેલી?</th>
                    <th>સ્ટાર</th>
                </tr>
            </thead>
            <tbody>";

    // Fetch and display data with proper offset and limit
    $remainingOffset = $offset;
    $remainingLimit = $recordsPerPage;

    $tables = [
        'binkheti_form_data' => 'id, type_of_application, application_no, applicant_name, applicant_jilla, applicant_taluko, applicant_location, binkheti_application_3, binkheti_application_3_1, star_rating',
        'hayati_form_data' => 'id, type_of_application, application_no, applicant_name, applicant_jilla, applicant_taluko, applicant_location, hayati_application_3, hayati_application_3_1, star_rating',
        'khedut_form_data' => 'id, type_of_application, application_no, applicant_name, applicant_jilla, applicant_taluko, applicant_location, khedut_application_3, khedut_application_3_1, star_rating',
        'varsayi_form_data' => 'id, type_of_application, application_no, applicant_name, applicant_jilla, applicant_taluko, applicant_location, varsayi_application_3, varsayi_application_3_1, star_rating'
    ];

    foreach ($tables as $table => $columns) {
        if ($remainingLimit > 0) {
            $recordsDisplayed = fetchAndDisplayData($table, $columns, $typeOfApplicationFilter, $starRatingFilter, $index, $remainingOffset, $remainingLimit);
            $remainingOffset = max(0, $remainingOffset - $recordsDisplayed);
            $remainingLimit -= $recordsDisplayed;
        }
    }

    echo "</tbody></table>";

    // Display pagination
    echo "<nav aria-label='Page navigation'>
            <ul class='pagination justify-content-center'>";
    for ($page = 1; $page <= $totalPages; $page++) {
        $active = ($page == $currentPage) ? 'active' : '';
        echo "<li class='page-item $active'><a class='page-link' href='?page=$page'>$page</a></li>";
    }
    echo "</ul>
          </nav>";

    $conn->close();
    ?>
</div>

<script>
        function exportToExcel() {
            window.location = 'district_summary.xlsx';
        }
    </script>
<!-- Add Bootstrap JS and Popper.js scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
