<?php
// Database connection details
$servername = "localhost";
$username = "cron";
$password = "1234";
$dbname = "asterisk";



// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set the number of entries per page
$entriesPerPage = 50;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Date filter
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

    // Get the current page number from the URL, default to 1
    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    $offset = ($page - 1) * $entriesPerPage;

    // Query to fetch data from call_log table with date filter and pagination
    $sql = "SELECT * FROM call_log 
            WHERE start_time BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'
            LIMIT $offset, $entriesPerPage";

    $result = $conn->query($sql);
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vicidial Call Log Report</title>
    <style>
        .pagination {
            display: inline-block;
        }

        .pagination a {
            padding: 8px 16px;
            text-decoration: none;
            transition: background-color .3s;
            border: 1px solid #ddd;
            margin: 0 4px;
        }

        .pagination a.active {
            background-color: #4CAF50;
            color: white;
            border: 1px solid #4CAF50;
        }

        .pagination a:hover:not(.active) {
            background-color: #ddd;
        }
    </style>
</head>
<body>

<h2>Vicidial Call Log Report</h2>

<form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    Start Date: <input type="date" name="start_date" value="<?php echo $start_date; ?>">
    End Date: <input type="date" name="end_date" value="<?php echo $end_date; ?>">
    <input type="submit" value="Search">
</form>

<?php
// Display results
if (isset($result) && $result->num_rows > 0) {
    echo "<table border='1'>
            <tr>
                <th>Unique ID</th>
                <th>Channel</th>
                <th>Channel Group</th>
                <th>Type</th>
                <th>Server IP</th>
                <th>Extension</th>
                <th>Number Dialed</th>
                <th>Caller Code</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Length in Sec</th>
                <th>Length in Min</th>
            </tr>";

    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row["uniqueid"] . "</td>
                <td>" . $row["channel"] . "</td>
                <td>" . $row["channel_group"] . "</td>
                <td>" . $row["type"] . "</td>
                <td>" . $row["server_ip"] . "</td>
                <td>" . $row["extension"] . "</td>
                <td>" . $row["number_dialed"] . "</td>
                <td>" . $row["caller_code"] . "</td>
                <td>" . $row["start_time"] . "</td>
                <td>" . $row["end_time"] . "</td>
                <td>" . $row["length_in_sec"] . "</td>
                <td>" . $row["length_in_min"] . "</td>
            </tr>";
    }
    echo "</table>";

    // Pagination links
    $sqlCount = "SELECT COUNT(*) as total FROM call_log WHERE start_time BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'";
    $resultCount = $conn->query($sqlCount);
    $rowCount = $resultCount->fetch_assoc();
    $totalPages = ceil($rowCount["total"] / $entriesPerPage);

    echo "<div class='pagination'>";
    for ($i = 1; $i <= $totalPages; $i++) {
        echo "<a href='{$_SERVER["PHP_SELF"]}?start_date=$start_date&end_date=$end_date&page=$i'>$i</a> ";
    }
    echo "</div>";
} else {
    echo "No results found";
}
?>

</body>
</html>
