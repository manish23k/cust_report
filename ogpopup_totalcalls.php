<?php
session_start(); // Start the session on each page

include "config.php"; // Include the database configuration

// Ensure the user is logged in, or redirect them to the login page
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit;
}

// Retrieve parameters from the URL
$category = isset($_GET['category']) ? $_GET['category'] : null;
$fromDate = isset($_GET['from_date']) ? $_GET['from_date'] : null;
$toDate = isset($_GET['to_date']) ? $_GET['to_date'] : null;
$fromTime = isset($_GET['from_time']) ? $_GET['from_time'] : null;
$toTime = isset($_GET['to_time']) ? $_GET['to_time'] : null;


echo "Category: $category<br>";
echo "From Date: $fromDate<br>";
echo "To Date: $toDate<br>";
echo "From Time: $fromTime<br>";
echo "To Time: $toTime<br>";

// // Check if from_date and to_date are provided
// $fromDate = isset($_GET['from_date']) ? $_GET['from_date'] : null;
// $toDate = isset($_GET['to_date']) ? $_GET['to_date'] : null;
// $fromTime = isset($_GET['from_time']) ? $_GET['from_time'] : null;
// $toTime = isset($_GET['to_time']) ? $_GET['to_time'] : null;

// Adjust the category if it's "Drop / Timeout Calls"
if ($category === "Drop / Timeout Calls") {
    $category = "Abandoned Calls";
}

// Construct the SQL query based on the selected category
if ($category === 'Answered Calls') {
    $sqlTotalCallsPopup = "SELECT *, DATE(call_date) AS call_date_date, TIME(call_date) AS call_date_time FROM vicidial_log WHERE status NOT IN ('DROP', 'QUEUE', 'AFTHRS', 'ERI', 'TIMEOT', 'XDROP') AND call_date BETWEEN '$fromDate $fromTime' AND '$toDate $toTime'";
} elseif ($category === 'Abandoned Calls') {
    $sqlTotalCallsPopup = "SELECT *, DATE(call_date) AS call_date_date, TIME(call_date) AS call_date_time FROM vicidial_log WHERE status IN ('DROP', 'AFTHRS', 'ERI', 'TIMEOT', 'XDROP') AND call_date BETWEEN '$fromDate $fromTime' AND '$toDate $toTime'";
} elseif ($category === 'Total Calls') {
    $sqlTotalCallsPopup = "SELECT *, DATE(call_date) AS call_date_date, TIME(call_date) AS call_date_time FROM vicidial_log WHERE call_date BETWEEN '$fromDate $fromTime' AND '$toDate $toTime'";
} else {
    // Handle invalid category
    echo "Invalid category";
    exit;
}

$resultTotalCallsPopup = $mysqli->query($sqlTotalCallsPopup);

echo "SQL Query: $sqlTotalCallsPopup";

if ($resultTotalCallsPopup) {
    // Fetch all rows and store them in a PHP variable
    $totalCallsDetails = $resultTotalCallsPopup->fetch_all(MYSQLI_ASSOC);

    // Display total calls count with bold and bigger styling
    echo "<p style='font-weight: bold; font-size: larger;'>Total Calls: {$resultTotalCallsPopup->num_rows}</p>";
    
    // Display the details in a table
    echo "<table id='dataTable' class='table'>";
    echo "<tr>
            <th>List ID</th>
            <th>Campaign ID</th>
            <th>Date</th>
            <th>Time</th>
            <th>Length (sec)</th>
            <th>Status</th>
            <th>Phone Number</th>
            <th>User</th>
            <th>Term Reason</th>
          </tr>";

    foreach ($totalCallsDetails as $totalCallsDetail) {
        echo "<tr>";
        echo "<td>{$totalCallsDetail['list_id']}</td>";
        echo "<td>{$totalCallsDetail['campaign_id']}</td>";
        echo "<td>{$totalCallsDetail['call_date_date']}</td>";
        echo "<td>{$totalCallsDetail['call_date_time']}</td>";
        echo "<td>{$totalCallsDetail['length_in_sec']}</td>";
        echo "<td>{$totalCallsDetail['status']}</td>";
        echo "<td>{$totalCallsDetail['phone_number']}</td>";
        echo "<td>{$totalCallsDetail['user']}</td>";
        echo "<td>{$totalCallsDetail['term_reason']}</td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    // Output an error message if the query fails
    echo "Error executing query: " . $mysqli->error;
}

// Close the database connection
$mysqli->close();
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
            // Generate CSV content using the PHP variable
// Generate CSV content using the PHP variable
var csvData = "List ID,Campaign ID,Date,Time,Length (sec),Status,Phone Number,User,Term Reason\n";
<?php foreach ($totalCallsDetails as $row) : ?>
    csvData += "<?php echo "{$row['list_id']},{$row['campaign_id']},{$row['call_date_date']},{$row['call_date_time']},{$row['length_in_sec']},{$row['status']},{$row['phone_number']},{$row['user']},{$row['term_reason']}"; ?>\n";
<?php endforeach; ?>


            // Create a Blob containing the CSV data
            var blob = new Blob([csvData], { type: 'text/csv' });

            // Create a link to trigger the download
            var link = document.createElement('a');

            // Customize the file name with date and time
            var currentDate = new Date();
            var formattedDate = currentDate.toISOString().slice(0, 19).replace(/[-T:]/g, "_").replace(/:/g, "");
            link.download = 'totalcalls_details_' + formattedDate + '.csv';

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

    <!-- The table will be dynamically generated above -->
</body>
</html>
