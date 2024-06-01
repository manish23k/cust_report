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

// Handle district filter on form submission
if (isset($_POST['filter'])) {
    $district_filter = $_POST['district_filter'];
    $type_filter = $_POST['type_filter'];

    $whereClause = "";

    // Construct the WHERE clause based on selected filters
    if (!empty($district_filter) && $district_filter != 'ALL') {
        $whereClause .= " WHERE applicant_jilla LIKE '%$district_filter%'";
    }
    if (!empty($type_filter) && $type_filter != 'ALL') {
        $whereClause .= $whereClause ? " AND type_of_application = '$type_filter'" : " WHERE type_of_application = '$type_filter'";
    }

    // Fetch district counts based on filters
    $sql_district = "SELECT district, COUNT(*) AS count FROM (
        SELECT applicant_jilla AS district FROM binkheti_form_data" . $whereClause . "
        UNION ALL
        SELECT applicant_jilla AS district FROM hayati_form_data" . $whereClause . "
        UNION ALL
        SELECT applicant_jilla AS district FROM khedut_form_data" . $whereClause . "
        UNION ALL
        SELECT applicant_jilla AS district FROM varsayi_form_data" . $whereClause . "
    ) AS all_districts GROUP BY district";
} else {
    // Fetch district counts from all tables without any filters
    $sql_district = "SELECT district, COUNT(*) AS count FROM (
        SELECT applicant_jilla AS district FROM binkheti_form_data
        UNION ALL
        SELECT applicant_jilla AS district FROM hayati_form_data
        UNION ALL
        SELECT applicant_jilla AS district FROM khedut_form_data
        UNION ALL
        SELECT applicant_jilla AS district FROM varsayi_form_data
    ) AS all_districts GROUP BY district";
}

$result_district = $conn->query($sql_district);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Application Call Summary</title>
    <!-- Add Bootstrap CSS link -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>

    <div class="container mt-4">

    <form method="post" action="">
    <div class="form-row align-items-end">
        <div class="col-md-3 mb-3">
            <label for="type_filter">Select Application Type:</label>
            <select class="form-control" name="type_filter" id="type_filter">
                <option value="ALL">ALL</option>
                <?php
                // Fetch type of applications again for dropdown
                while ($typeRow = $typeResult->fetch_assoc()) :
                    $selectedType = isset($_POST['type_filter']) && $_POST['type_filter'] == $typeRow['type_of_application'] ? 'selected' : '';
                ?>
                    <option value="<?= $typeRow['type_of_application'] ?>" <?= $selectedType ?>>
                        <?= $typeRow['type_of_application'] ?>
                    </option>
                <?php endwhile;
                $typeResult->data_seek(0); // Reset the result pointer
                ?>
            </select>
        </div>

        <div class="col-md-5 mb-3">
            <div class="row">
                <div class="col-md-6">
                    <label for="district_filter">Search by District:</label>
                    <input type="text" class="form-control" id="district_filter"  placeholder="Type District name" name="district_filter">
                </div>
                <div class="col-md-6">
                    <label for="filter_satisfied">Satisfied:</label>
                    <select id="filter_satisfied" class="form-control">
                        <option value="all">All</option>
                        <option value="yes">હા</option>
                        <option value="no">ના</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="col-md-2 mb-3">
            <button type="submit" class="btn btn-primary btn-block" name="filter">Search</button>
        </div>
        <div class="col-md-2 mb-3">
            <button type="button" onclick="exportToExcel()" class="btn btn-success btn-block">Export to XLSX</button>
        </div>
    </div>
</form>

        <div class='container mt-4'>
            <h2 style='text-align: center;'>Application Call Summary Report</h2>
        </div>

        <table class='table table-bordered table-striped mt-4'>
            <thead class='thead-dark'>
                <tr>
                    <th>ક્રમ</th>
                    <th>જિલ્લો</th>
                    <th>કુલ અરજીઓ</th>
                    <th>જવાબ થયેલ કોલ</th>
                    <th>સંતુષ્ટ અરજદાર</th>
                    <th>અસંતુષ્ટ અરજદાર</th>
                </tr>
            </thead>

            <?php
            if ($result_district->num_rows > 0) {
                $sr_no_district = 1;
                while ($row = $result_district->fetch_assoc()) {
                    echo "<tr class='data-row'><td>" . $sr_no_district . "</td><td>" . $row["district"] . "</td><td>" . $row["count"] . "</td>";

                    // Initialize district-level counts for "જવાબ થયેલ કોલ" (answered calls)
                    $district_calls_total = 0;
                    // Initialize district-level counts for "Satisfied Yes" and "Satisfied NO"
                    $satisfied_yes_count = 0;
                    $satisfied_no_count = 0;

                    // Fetch answered calls count for each district from all tables
                    $tables = array("binkheti_form_data", "hayati_form_data", "khedut_form_data", "varsayi_form_data");
                    $columns = array("binkheti", "hayati", "khedut", "varsayi");

                    foreach ($tables as $index => $table) {
                        // Construct SQL query
                        $sql_district_calls = "SELECT COUNT(*) AS count FROM $table WHERE applicant_jilla='" . $row["district"] . "' AND entry_datetime IS NOT NULL";
                        // Execute SQL query
                        $result_district_calls = $conn->query($sql_district_calls);
                        if (!$result_district_calls) {
                            echo "Error: " . $conn->error;
                            exit();
                        }
                        // Process result
                        if ($result_district_calls->num_rows > 0) {
                            $row_district_calls = $result_district_calls->fetch_assoc();
                            $district_calls_total += $row_district_calls["count"];
                        }

                        // Fetch satisfied counts for each district from all tables
                        $sql_satisfied_counts = "SELECT COUNT(*) AS count FROM $table WHERE applicant_jilla='" . $row["district"] . "' AND entry_datetime IS NOT NULL AND " . $columns[$index] . "_satisfied = 'હા'";
                        $result_satisfied_counts = $conn->query($sql_satisfied_counts);
                        if (!$result_satisfied_counts) {
                            echo "Error: " . $conn->error;
                            exit();
                        }
                        // Process result for satisfied yes counts
                        if ($result_satisfied_counts->num_rows > 0) {
                            $row_satisfied_counts = $result_satisfied_counts->fetch_assoc();
                            $satisfied_yes_count += $row_satisfied_counts["count"];
                        }

                        // Fetch unsatisfied counts for each district from all tables
                        $sql_unsatisfied_counts = "SELECT COUNT(*) AS count FROM $table WHERE applicant_jilla='" . $row["district"] . "' AND entry_datetime IS NOT NULL AND " . $columns[$index] . "_satisfied = 'ના'";
                        $result_unsatisfied_counts = $conn->query($sql_unsatisfied_counts);
                        if (!$result_unsatisfied_counts) {
                            echo "Error: " . $conn->error;
                            exit();
                        }
                        // Process result for unsatisfied no counts
                        if ($result_unsatisfied_counts->num_rows > 0) {
                            $row_unsatisfied_counts = $result_unsatisfied_counts->fetch_assoc();
                            $satisfied_no_count += $row_unsatisfied_counts["count"];
                        }
                    }

                    // Display district-level counts for "જવાબ થયેલ કોલ" (answered calls)
                    echo "<td>" . $district_calls_total . "</td>";

                    // Display district-level counts for "Satisfied Yes" and "Satisfied NO"
                    echo "<td class='satisfied-yes'>" . $satisfied_yes_count . "</td>";
                    echo "<td class='satisfied-no'>" . $satisfied_no_count . "</td>";

                    $sr_no_district++;
                }
            } else {
                echo "<tr><td colspan='6'>No data found.</td></tr>";
            }
            ?>

        </table>

    </div>

    <!-- Add Bootstrap JS and Popper.js scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
            $(document).ready(function () {
        $('#district_filter').autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: '',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        term: request.term
                    },
                    success: function (data) {
                        response(data);
                    }
                });
            },
            minLength: 2
        });
    });

        $(document).ready(function() {
            $('#filter_satisfied').change(function() {
                var filterValue = $(this).val();
                var selectedDistrict = $('#district_filter').val().trim();

                $('tr.data-row').each(function() {
                    var district = $(this).find('.district').text().trim();
                    var satisfiedYesCount = parseInt($(this).find('.satisfied-yes').text());
                    var satisfiedNoCount = parseInt($(this).find('.satisfied-no').text());

                    if (filterValue === 'all') {
                        if (selectedDistrict === '' || district === selectedDistrict) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    } else if (filterValue === 'yes') {
                        if (district === selectedDistrict && satisfiedYesCount > 0) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    } else if (filterValue === 'no') {
                        if (district === selectedDistrict && satisfiedNoCount > 0) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    }
                });
            });
        });
    </script>
    <script>
        function exportToExcel() {
            window.location = 'App_Call_Summary.xlsx';
        }
    </script>

</body>

</html>
<?php
if (isset($_POST['filter']) || isset($_GET['term'])) {
    $conn->close();
}
?>