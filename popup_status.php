<?php
session_start(); // Start the session on each page

include "config.php"; // Include the database configuration

// Ensure the user is logged in, or redirect them to the login page
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit;
}

// Check if a specific status or call ID is provided via GET
if (isset($_GET['status_id'])) {
    $statusId = $_GET['status_id'];

    // Check if from_date and to_date are provided
    $fromDate = isset($_GET['from_date']) ? $_GET['from_date'] : null;
    $toDate = isset($_GET['to_date']) ? $_GET['to_date'] : null;
    $fromTime = isset($_GET['from_time']) ? $_GET['from_time'] : null;
    $toTime = isset($_GET['to_time']) ? $_GET['to_time'] : null;


    echo "StatusId: $statusId<br>";
echo "From Date: $fromDate<br>";
echo "To Date: $toDate<br>";
echo "From Time: $fromTime<br>";
echo "To Time: $toTime<br>";

    $sqlStatusDetail = "SELECT 
        list_id, 
        campaign_id, 
        DATE_FORMAT(call_date, '%Y-%m-%d') AS Date, 
        DATE_FORMAT(call_date, '%H:%i:%s') AS Time, 
        SEC_TO_TIME(length_in_sec) AS `Length (sec)`, 
        status, 
        phone_number AS `Phone Number`, 
        user, 
        term_reason AS `Term Reason`
    FROM vicidial_closer_log 
    WHERE status = '$statusId' 
    AND call_date BETWEEN '$fromDate $fromTime' AND '$toDate $toTime'";

    $resultStatusDetail = $mysqli->query($sqlStatusDetail);

    if (!$resultStatusDetail) {
        die("Error fetching status details: " . $mysqli->error);
    }

    // Calculate total calls count
    $totalCallsCount = $resultStatusDetail->num_rows;
} else {
    // Handle the case when no status ID is provided
    die("Status ID not provided.");
}

?>
<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Add custom CSS for positioning the button */
        body {
            position: relative;
        }

        #downloadButton {
            position: absolute;
            top: 10px;
            right: 10px;
        }

        #dataTable {
            margin-top: 50px; /* Adjust as needed */
        }
    </style>
<script>
    function downloadCSV() {
        // Generate CSV content
        var csvData = "List ID,Campaign ID,Date,Time,Length (sec),Status,Phone Number,User,Term Reason\n";
        <?php while ($row = $resultStatusDetail->fetch_assoc()) : ?>
            csvData += "<?php echo "{$row['list_id']},{$row['campaign_id']},{$row['Date']},{$row['Time']},{$row['Length (sec)']},{$row['status']},{$row['Phone Number']},{$row['user']},{$row['Term Reason']}"; ?>\n";
        <?php endwhile; ?>

        // Create a Blob containing the CSV data
        var blob = new Blob([csvData], { type: 'text/csv' });

        // Create a link to trigger the download
        var link = document.createElement('a');

        // Customize the file name with the current date and time
        var currentDate = new Date();
        var formattedDate = currentDate.toISOString().slice(0, 19).replace(/[-T:]/g, "_").replace(/:/g, "");
        var fileName = 'status_details_' + formattedDate + '.csv';

        link.download = fileName;
        link.href = window.URL.createObjectURL(blob);

        // Append the link to the document and trigger the download
        document.body.appendChild(link);
        link.click();

        // Remove the link from the document
        document.body.removeChild(link);
    }
</script>


</head>
<body>
    <!-- Add Download CSV Button -->
    <button id="downloadButton" onclick="downloadCSV()" class="btn btn-primary">Download CSV</button>

    <h1>Status Detail</h1>

    <?php
    // Display total calls count with bold and bigger styling
    echo "<p style='font-weight: bold; font-size: larger;'>Total Calls: $totalCallsCount</p>";
    ?>

    <table id="dataTable" class="table">
        <tr>
            <th>List ID</th>
            <th>Campaign ID</th>
            <th>Date</th>
            <th>Time</th>
            <th>Length (sec)</th>
            <th>Status</th>
            <th>Phone Number</th>
            <th>User</th>
            <th>Term Reason</th>
        </tr>
        <?php
        // Reset the result set pointer to fetch and display all rows in a loop
        $resultStatusDetail->data_seek(0);
        while ($statusDetail = $resultStatusDetail->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$statusDetail['list_id']}</td>";
            echo "<td>{$statusDetail['campaign_id']}</td>";
            echo "<td>{$statusDetail['Date']}</td>";
            echo "<td>{$statusDetail['Time']}</td>";
            echo "<td>{$statusDetail['Length (sec)']}</td>";
            echo "<td>{$statusDetail['status']}</td>";
            echo "<td>{$statusDetail['Phone Number']}</td>";
            echo "<td>{$statusDetail['user']}</td>";
            echo "<td>{$statusDetail['Term Reason']}</td>";
            echo "</tr>";
        }
        ?>
    </table>
</body>
</html>
