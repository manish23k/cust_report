<?php
session_start(); // Start the session on each page

include "config.php"; // Include the database configuration

// Ensure the user is logged in, or redirect them to the login page
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit;
}

$user = $_GET['user'];
$fromDate = $_GET['from_date'];
$toDate = $_GET['to_date'];
$fromTime = $_GET['from_time'];
$toTime = $_GET['to_time'];
$status = $_GET['status'];

echo "User: $user<br>";
echo "Status: $status<br>";
echo "From Date: $fromDate<br>";
echo "To Date: $toDate<br>";
echo "From Time: $fromTime<br>";
echo "To Time: $toTime<br>";

// Check if a specific user is provided via GET
if (isset($_GET['user'])) {
    $user = $_GET['user'];

    // Check if from_date and to_date are provided
    $fromDate = isset($_GET['from_date']) ? $_GET['from_date'] : null;
    $toDate = isset($_GET['to_date']) ? $_GET['to_date'] : null;
    $fromTime = isset($_GET['from_time']) ? $_GET['from_time'] : null;
    $toTime = isset($_GET['to_time']) ? $_GET['to_time'] : null;

    $sqlUserDetail = "SELECT 
        list_id, 
        campaign_id, 
        DATE_FORMAT(call_date, '%Y-%m-%d') AS Date, 
        DATE_FORMAT(call_date, '%H:%i:%s') AS Time, 
        SEC_TO_TIME(length_in_sec) AS `Length (sec)`, 
        status, 
        phone_number AS `Phone Number`, 
        user, 
        term_reason AS `Term Reason`
    FROM vicidial_log 
    WHERE user = '$user' 
    AND call_date BETWEEN '$fromDate $fromTime' AND '$toDate $toTime'";

    $resultUserDetail = $mysqli->query($sqlUserDetail);

    //echo "SQL Query: $sqlUserDetail";

    if ($resultUserDetail) {
        // Fetch all rows and store them in a PHP variable
        $userDetails = $resultUserDetail->fetch_all(MYSQLI_ASSOC);
        
        // Display total calls count with bold and bigger styling
        echo "<p style='font-weight: bold; font-size: larger;'>Total Calls: {$resultUserDetail->num_rows}</p>";
    } else {
        // Output an error message if the query fails
        echo "Error executing query: " . $mysqli->error;
    }

    // Close the database connection
    $mysqli->close();
} else {
    echo "User parameter is not set.";
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
            // Generate CSV content using the PHP variable
            var csvData = "List ID,Campaign ID,Date,Time,Length (sec),Status,Phone Number,User,Term Reason\n";
            <?php foreach ($userDetails as $row) : ?>
                csvData += "<?php echo "{$row['list_id']},{$row['campaign_id']},{$row['Date']},{$row['Time']},{$row['Length (sec)']},{$row['status']},{$row['Phone Number']},{$row['user']},{$row['Term Reason']}"; ?>\n";
            <?php endforeach; ?>

            // Create a Blob containing the CSV data
            var blob = new Blob([csvData], { type: 'text/csv' });

            // Create a link to trigger the download
            var link = document.createElement('a');

            // Customize the file name with date and time
            var currentDate = new Date();
            var formattedDate = currentDate.toISOString().slice(0, 19).replace(/[-T:]/g, "_").replace(/:/g, "");
            link.download = 'user_details_' + formattedDate + '.csv';

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
        // Display rows using the PHP variable
        foreach ($userDetails as $userDetail) {
            echo "<tr>";
            echo "<td>{$userDetail['list_id']}</td>";
            echo "<td>{$userDetail['campaign_id']}</td>";
            echo "<td>{$userDetail['Date']}</td>";
            echo "<td>{$userDetail['Time']}</td>";
            echo "<td>{$userDetail['Length (sec)']}</td>";
            echo "<td>{$userDetail['status']}</td>";
            echo "<td>{$userDetail['Phone Number']}</td>";
            echo "<td>{$userDetail['user']}</td>";
            echo "<td>{$userDetail['Term Reason']}</td>";
            echo "</tr>";
        }
        ?>
    </table>
</body>
</html>
