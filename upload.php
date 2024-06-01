<?php
include "config.php";
include "header.php";
require 'vendor/autoload.php'; // Include Composer autoloader


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['upload'])) {
        handleUpload();
    } elseif (isset($_POST['delete'])) {
        deleteData();
    } elseif (isset($_POST['delete_range']) && isset($_POST['list_id'])) {
            deleteDataInRange($_POST['start_date'], $_POST['start_time'], $_POST['end_date'], $_POST['end_time'], $_POST['list_id']);
        }
}

// Retrieve and display data from the database
$result = $conn->query("SELECT * FROM excel_data");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uploaded Excel Data</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h2 {
            color: #333;
        }

        form {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="file"],
        input[type="datetime-local"],
        button {
            margin-bottom: 10px;
            padding: 8px;
            font-size: 16px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <h2>Upload Data For Outgoing</h2>

    <style>
    form {
        display: flex;
        flex-direction: row;
        align-items: center;
        margin-bottom: 20px;
    }

    label {
        margin-right: 10px;
    }

    input[type="file"],
    input[type="datetime-local"],
    select,
    button {
        margin-bottom: 10px;
        padding: 8px;
        font-size: 16px;
    }

    button {
        background-color: #4CAF50;
        color: white;
        border: none;
        cursor: pointer;
    }

    button:hover {
        background-color: #45a049;
    }
</style>

<form method="post" enctype="multipart/form-data">
    <label for="excel_file">Choose Excel File(.xls or .xlsx):</label>
    <input type="file" name="excel_file" id="excel_file" accept=".xls, .xlsx" required>
    <label for="list_id">Select List For Dialing:</label>
    <select name="list_id" id="list_id">
        <?php
        // Fetch list_id and list_name from vicidial_lists excluding 998 and 999
        $listQuery = "SELECT list_id, list_name FROM vicidial_lists WHERE list_id NOT IN (998, 999)";
        $listResult = $conn->query($listQuery);

        if ($listResult->num_rows > 0) {
            while ($row = $listResult->fetch_assoc()) {
                $listId = $row['list_id'];
                $listName = $row['list_name'];
                echo "<option value='$listId'>$listId - $listName</option>";
            }
        }
        ?>
    </select>
    <button type="submit" name="upload">Upload</button>
</form>
<hr>
<!-- Delete Data  -->
<h2>Delete Data</h2>
<form method="post" onsubmit="return confirmDelete()">
    <label for="start_date">Start Date and Time:</label>
    <input type="datetime-local" name="start_date" step="1" required>
    <label for="end_date">End Date and Time:</label>
    <input type="datetime-local" name="end_date" step="1" required>

    <!-- Add the list_id input -->
    <label for="list_id">Select List For Delete Data:</label>
    <select name="list_id" id="list_id">
        <?php
        // Fetch list_id and list_name from vicidial_lists excluding 998 and 999
        $listQuery = "SELECT list_id, list_name FROM vicidial_lists WHERE list_id NOT IN (998, 999)";
        $listResult = $conn->query($listQuery);

        if ($listResult->num_rows > 0) {
            while ($row = $listResult->fetch_assoc()) {
                $listId = $row['list_id'];
                $listName = $row['list_name'];
                echo "<option value='$listId'>$listId - $listName</option>";
            }
        }
        ?>
    </select>

    <button type="submit" name="delete_range">Delete Data in Range</button>
    
</form>
<hr>

<script>
    function confirmDelete() {
        return confirm("Are you sure you want to delete the data in the specified range?");
    }
</script>








<?php
// Display data from the database
if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Application No</th><th>District</th><th>Taluka</th><th>Village</th><th>Survey No</th><th>Name of Applicant</th><th>Type of Application</th><th>Date of Application</th><th>Date of Disposal</th><th>Mobile Number</th><th>Upload DateTime</th></tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['application_no']}</td>";
        echo "<td>{$row['district']}</td>";
        echo "<td>{$row['taluka']}</td>";
        echo "<td>{$row['village']}</td>";
        echo "<td>{$row['survey_no']}</td>";
        echo "<td>{$row['name_of_applicant']}</td>";
        echo "<td>{$row['type_of_application']}</td>";
        echo "<td>{$row['date_of_application']}</td>";
        echo "<td>{$row['date_of_disposal']}</td>";
        echo "<td>{$row['mobile_number']}</td>";
        echo "<td>{$row['upload_datetime']}</td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "No data available.";
}

// Close the database connection
$conn->close();

function handleUpload()
{
    global $conn;

    $excelFile = $_FILES['excel_file']['tmp_name'];

    // Load Excel file
    $objPHPExcel = PHPExcel_IOFactory::load($excelFile);

    // Get the active sheet
    $sheet = $objPHPExcel->getActiveSheet();

    // Get the current date and time
    $currentDateTime = date('Y-m-d H:i:s');

    // Retrieve selected list_id from the form
    $selectedListId = $_POST['list_id'];

    // Loop through rows and insert data into MySQL and vicidial_list tables
    foreach ($sheet->getRowIterator(2) as $row) {
        $data = $sheet->rangeToArray('A' . $row->getRowIndex() . ':' . 'J' . $row->getRowIndex(), NULL, TRUE, FALSE);

        // Convert date values to MySQL format
        $applicationDate = convertDateFormat($data[0][7]);
        $disposalDate = convertDateFormat($data[0][8]);

        // Insert data into MySQL table with the manually set current timestamp
        $sql = "INSERT INTO excel_data (application_no, district, taluka, village, survey_no, name_of_applicant, type_of_application, date_of_application, date_of_disposal, mobile_number, upload_datetime)
                VALUES ('{$data[0][0]}', '{$data[0][1]}', '{$data[0][2]}', '{$data[0][3]}', '{$data[0][4]}', '{$data[0][5]}', '{$data[0][6]}', '$applicationDate', '$disposalDate', '{$data[0][9]}', '$currentDateTime')";

        if ($conn->query($sql) !== TRUE) {
            echo "Error inserting data into excel_data table: " . $conn->error;
        }

        // Insert data into vicidial_list table with the selected list_id
        $sqlVicidialList = "INSERT INTO vicidial_list (address1, phone_number, status, list_id)
                           VALUES ('{$data[0][0]}', '{$data[0][9]}', 'NEW', '$selectedListId')";

        if ($conn->query($sqlVicidialList) !== TRUE) {
            echo "Error inserting data into vicidial_list table: " . $conn->error;
        }
    }

    echo "Data uploaded successfully!";
}





function deleteData()
{
    global $conn;

    // Delete all data from the table
    $sql = "DELETE FROM excel_data";
    if ($conn->query($sql) !== TRUE) {
        echo "Error deleting data: " . $conn->error;
    } else {
        echo "Data deleted successfully!";
    }
}

function deleteDataInRange($startDate, $startTime, $endDate, $endTime, $listId)
{
    global $conn;

    // Convert start time to 24-hour format
    $startDateTime = new DateTime("$startDate $startTime");
    $formattedStartDateTime = $startDateTime->format('Y-m-d H:i:s');

    // Convert end time to 24-hour format
    $endDateTime = new DateTime("$endDate $endTime");
    $formattedEndDateTime = $endDateTime->format('Y-m-d H:i:s');

    // Delete data within the specified range using modify_date and list_id
    $sql = "DELETE excel_data, vicidial_list FROM excel_data
            INNER JOIN vicidial_list ON excel_data.mobile_number = vicidial_list.phone_number
            WHERE excel_data.upload_datetime BETWEEN '$formattedStartDateTime' AND '$formattedEndDateTime'
            AND vicidial_list.modify_date BETWEEN '$formattedStartDateTime' AND '$formattedEndDateTime'
            AND vicidial_list.list_id = '$listId'";

    //echo "$sql";

    if ($conn->query($sql) !== TRUE) {
        echo "Error deleting data: " . $conn->error;
    } else {
        echo "Data deleted successfully!";
    }
}


function convertDateFormat($date)
{
    // Check if the date is numeric (Excel serialized date)
    if (is_numeric($date)) {
        // Excel serialized date starts from 1900-01-01
        $excelStartDate = new DateTime('1900-01-01');
        $excelStartDateTimestamp = $excelStartDate->getTimestamp();
        
        // Convert the Excel serialized date to Unix timestamp
        $timestamp = $excelStartDateTimestamp + ($date - 2) * 24 * 3600;
        
        // Format the timestamp as 'Y-m-d'
        $formattedDate = date('Y-m-d', $timestamp);

        // Check if the formatted date is not '0000-00-00'
        if ($formattedDate !== '0000-00-00') {
            return $formattedDate;
        }
    } else {
        // Try to convert the date format "DD-MM-YYYY"
        $dateDMY = DateTime::createFromFormat('d-m-y', $date);

        if ($dateDMY !== false) {
            $formattedDate = $dateDMY->format('Y-m-d');

            // Check if the formatted date is not '0000-00-00'
            if ($formattedDate !== '0000-00-00') {
                return $formattedDate;
            }
        }
    }

    return '0000-00-00'; // Invalid date
}

?>

</body>
</html>
