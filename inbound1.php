<?php
session_start(); // Start the session on each page

include "config.php"; // Include the database configuration
include "header.php";

// Ensure the user is logged in, or redirect them to the login page
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit;
}

$recordsPerPage = 40; // Number of records to display per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1; // Get the current page number

// Calculate the offset to skip records on each page
$offset = ($page - 1) * $recordsPerPage;

// Fetch Inbound Group IDs and Names from the vicidial_inbound_groups table
$inboundGroupsQuery = "SELECT group_id, group_name FROM vicidial_inbound_groups";
$inboundGroupsResult = mysqli_query($conn, $inboundGroupsQuery);

// Check if fetching inbound groups was successful
if (!$inboundGroupsResult) {
    die("Failed to fetch Inbound Groups: " . mysqli_error($conn));
}

// Fetch unique "User" values from your database
$userQuery = "SELECT DISTINCT user FROM vicidial_users WHERE user_level <= 6 AND user NOT IN ('VDAD', 'VDCL')";
$userResult = mysqli_query($conn, $userQuery);

// Fetch unique "Status" values from your database
$statusQuery = "SELECT DISTINCT status FROM vicidial_closer_log";
$statusResult = mysqli_query($conn, $statusQuery);

// Initialize variables for campaign and date range
$campaign = $_GET['campaign'] ?? "";
$fromDate = $_GET['from_date'] ?? date('Y-m-d');
$toDate = $_GET['to_date'] ?? date('Y-m-d');
$fromTime = $_GET['from_time'] ?? '00:00:00';
$toTime = $_GET['to_time'] ?? '23:59:59';
$statusFilter = $_GET['status'] ?? '';
$phoneFilter = $_GET['phone_number'] ?? "";
$userFilter = $_GET['user'] ?? "";

// Check if the Search button is clicked
if (isset($_GET['search'])) {
    $campaign = $_GET['campaign'];
    $fromDate = $_GET['from_date'];
    $toDate = $_GET['to_date'];
    $fromTime = $_GET['from_time'];
    $toTime = $_GET['to_time'];
    $statusFilter = $_GET['status'];
    $phoneFilter = $_GET['phone_number'];
    $userFilter = $_GET['user'];
}


if (
    !preg_match('/^(?:2[0-3]|[01][0-9]):[0-5][0-9]:[0-5][0-9]$/', $fromTime) ||
    !preg_match('/^(?:2[0-3]|[01][0-9]):[0-5][0-9]:[0-5][0-9]$/', $toTime)
) {
    // Handle invalid time format
    // You may display an error message or set default values
}


// SQL query to select specific columns and filter calls within the specified campaign and date range
$query = "SELECT list_id, campaign_id, call_date, length_in_sec, status, phone_number, user, term_reason FROM vicidial_closer_log WHERE call_date BETWEEN '$fromDate $fromTime' AND '$toDate $toTime'";

if (!empty($campaign)) {
    $query .= " AND campaign_id = '" . mysqli_real_escape_string($conn, $campaign) . "'";
}

if (!empty($statusFilter)) {
    $statusFilter = mysqli_real_escape_string($conn, $statusFilter);
    $query .= " AND status = '$statusFilter'";
}

if (!empty($phoneFilter)) {
    $phoneFilter = mysqli_real_escape_string($conn, $phoneFilter);
    $query .= " AND phone_number LIKE '%$phoneFilter%'";
}

if (!empty($userFilter)) {
    $userFilter = mysqli_real_escape_string($conn, $userFilter);
    $query .= " AND user = '$userFilter'";
}

// Add LIMIT and OFFSET for pagination
$query .= " LIMIT $recordsPerPage OFFSET $offset";

// Print the query for debugging
//echo "Debugging Query: $query";

// Execute the query
$result = mysqli_query($conn, $query);



if (!$result) {
    die("Query failed: " . mysqli_error($conn) . "<br>Query: " . $query);
}



// Query to get the total number of records with applied filters
$totalCallsQuery = "SELECT COUNT(*) as total FROM vicidial_closer_log WHERE call_date BETWEEN '$fromDate $fromTime' AND '$toDate $toTime'";

if (!empty($campaign)) {
    $totalCallsQuery .= " AND campaign_id = '$campaign'";
}

if (!empty($statusFilter)) {
    $statusFilter = mysqli_real_escape_string($conn, $statusFilter);
    $totalCallsQuery .= " AND status = '$statusFilter'";
}

if (!empty($phoneFilter)) {
    $phoneFilter = mysqli_real_escape_string($conn, $phoneFilter);
    $totalCallsQuery .= " AND phone_number LIKE '%$phoneFilter%'";
}

if (!empty($userFilter)) {
    $userFilter = mysqli_real_escape_string($conn, $userFilter);
    $totalCallsQuery .= " AND user = '$userFilter'";
}

// Execute the query
$totalCallsResult = mysqli_query($conn, $totalCallsQuery);

if (!$totalCallsResult) {
    die("Failed to fetch total calls: " . mysqli_error($conn));
}

// Fetch the total number of calls
$totalCallsRow = mysqli_fetch_assoc($totalCallsResult);
$totalCalls = $totalCallsRow['total'];


// Get the current date and time for CSV filename
$currentDateTime = date("Ymd_His");


?>

<!DOCTYPE html>
<html>

<!-- <head>
    <!-- <title>Inbound Call Log</title> -->
    <!-- Add Bootstrap CSS link -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"> -->

    <!-- <style> -->
        <!-- /* .pagination-button {
            background-color: #007bff;
            color: white;
            border: 1px solid #007bff;
            padding: 8px 16px;
            text-decoration: none;
            margin: 0 5px;
            cursor: pointer;
        }

        .pagination-button.disabled {
            background-color: #ccc;
            border: 1px solid #ccc;
            cursor: not-allowed;
        }

        .pagination-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        } */ -->
    <!-- </style> -->
<!-- </head> -->

<!-- <body style="background-color: #f2f2f2;"> -->
<body>    
    <div class="container mt-4 text-center">
        <h1 class="display-4">Inbound Call Log</h1>


        <form method="get" action="" class="mb-4">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="campaign">Inbound Group:</label>
                    <select name="campaign" id="campaign" class="form-select">
                        <option value="">Select an Inbound Group</option>
                        <?php
                        // Populate the dropdown with Inbound Group IDs and Names
                        while ($row = mysqli_fetch_assoc($inboundGroupsResult)) {
                            $groupID = $row['group_id'];
                            $groupName = $row['group_name'];
                            $selected = ($campaign == $groupID) ? "selected" : "";
                            echo "<option value='$groupID' $selected>$groupName</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="from_date">Start Date:</label>
                    <input type="date" name="from_date" id="from_date" value="<?= $fromDate ?>" class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="to_date">End Date:</label>
                    <input type="date" name="to_date" id="to_date" value="<?= $toDate ?>" class="form-control">
                </div>

                <div class="col-md-3 mb-3">
                    <label for="from_time">From Time:</label>
                    <input type="text" name="from_time" id="from_time" class="form-control" placeholder="00:00:00"
                        value="<?php echo isset($_GET['from_time']) ? $_GET['from_time'] : '00:00:00'; ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="to_time">To Time:</label>
                    <input type="text" name="to_time" id="to_time" class="form-control" placeholder="23:59:59"
                        value="<?php echo isset($_GET['to_time']) ? $_GET['to_time'] : '23:59:59'; ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="status">Status:</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">Select Status</option>
                        <?php
                        while ($row = mysqli_fetch_assoc($statusResult)) {
                            $statusValue = $row['status'];
                            $selected = ($statusFilter == $statusValue) ? "selected" : "";
                            echo "<option value='$statusValue' $selected>$statusValue</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="phone_number">Phone Number:</label>
                    <input type="text" name="phone_number" id="phone_number" value="<?= $phoneFilter ?>"
                        class="form-control">
                </div>
                <div class="col-md-2 mb-2">
                    <label for="user">User:</label>
                    <select name="user" id="user" class="form-select">
                        <option value="">Select User</option>
                        <?php
                        while ($row = mysqli_fetch_assoc($userResult)) {
                            $userValue = $row['user'];
                            $selected = ($userFilter == $userValue) ? "selected" : "";
                            echo "<option value='$userValue' $selected>$userValue</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label for="search" class="invisible">Search Button</label>
                    <input type="submit" name="search" value="Search" class="btn btn-primary form-control">
                </div>
                <div class="col-md-2 mb-2">
                    <label for="download_csv" class="invisible">Download CSV Button</label>
                    <button type="button" onclick="downloadCSV()" class="btn btn-success form-control">Download
                        CSV</button>
                </div>
                <!-- Add color legend above Total Calls section -->
                <div class="legend mb-4">
                <p style="font-weight: bold;">DROP Calls Colors Legend:</p>
                    <p style="background-color: yellow; display: inline-block; padding: 5px;">00 seconds - 30 seconds
                    </p>
                    <p style="background-color: orange; display: inline-block; padding: 5px;">31 seconds - 60 seconds
                    </p>
                    <p style="background-color: red; display: inline-block; padding: 5px;">1 Minute and above</p>
                </div>

            </div>
        </form>
        <div>
            <!-- Display the total number of calls -->
            <?php echo "<h3>Total Calls: " . $totalCalls . "</h3>"; ?>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
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
                </thead>
                <tbody>
                    <?php
                    // Loop through the query results and display each row with specific columns
                    while ($row = mysqli_fetch_assoc($result)) {
                        $callDate = strtotime($row['call_date']);
                        $date = date("Y-m-d", $callDate);
                        $time = date("H:i:s", $callDate);

                        // Calculate length in seconds
                        $lengthInSeconds = $row['length_in_sec'];

                        // Format length as HH:MM:SS
                        $lengthFormatted = sprintf('%02d:%02d:%02d', ($lengthInSeconds / 3600), ($lengthInSeconds / 60 % 60), $lengthInSeconds % 60);

                        // Set default row color
                        $rowColor = '';

                        // Check call status and length to set row color
                        if ($row['status'] === 'DROP') {
                            if ($lengthInSeconds > 00 && $lengthInSeconds <= 30) {
                                $rowColor = 'background-color: yellow;';
                            } elseif ($lengthInSeconds > 30 && $lengthInSeconds <= 60) {
                                $rowColor = 'background-color: orange;';
                            } elseif ($lengthInSeconds > 60) {
                                $rowColor = 'background-color: red;';
                            }
                        }

                        // Echo the table row with style attribute for background color
                        echo "<tr style='$rowColor'>";
                        echo "<td>" . $row['list_id'] . "</td>";
                        echo "<td>" . $row['campaign_id'] . "</td>";
                        echo "<td>" . $date . "</td>";
                        echo "<td>" . $time . "</td>";
                        echo "<td>" . $lengthFormatted . "</td>";
                        echo "<td>" . $row['status'] . "</td>";
                        echo "<td>" . $row['phone_number'] . "</td>";
                        echo "<td>" . $row['user'] . "</td>";
                        echo "<td>" . $row['term_reason'] . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div class="row mt-4">
            <div class="col-md-6">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= ($page - 1) ?>&campaign=<?= $campaign ?>&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>&status=<?= $statusFilter ?>&phone_number=<?= $phoneFilter ?>&user=<?= $userFilter ?>"
                        class="btn btn-primary">Previous</a>
                <?php else: ?>
                    <button class="btn btn-primary disabled" disabled>Previous</button>
                <?php endif; ?>
            </div>
            <div class="col-md-6 text-end">
                <?php if (($page * $recordsPerPage) < $totalCalls): ?>
                    <a href="?page=<?= ($page + 1) ?>&campaign=<?= $campaign ?>&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>&status=<?= $statusFilter ?>&phone_number=<?= $phoneFilter ?>&user=<?= $userFilter ?>"
                        class="btn btn-primary">Next</a>
                <?php else: ?>
                    <button class="btn btn-primary disabled" disabled>Next</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function downloadCSV() {
            // Prepare CSV data
            var csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "List ID,Campaign ID,Date,Time,Length (sec),Status,Phone Number,User,Term Reason\n";

            // Add data rows
            <?php
            // Fetch data for CSV
            $csvQuery = "SELECT list_id, campaign_id, call_date, length_in_sec, status, phone_number, user, term_reason FROM vicidial_closer_log WHERE DATE(call_date) BETWEEN '$fromDate $fromTime' AND '$toDate $toTime'";

            if (!empty($campaign)) {
                $csvQuery .= " AND campaign_id = '" . mysqli_real_escape_string($conn, $campaign) . "'";
            }

            if (!empty($statusFilter)) {
                $statusFilter = mysqli_real_escape_string($conn, $statusFilter);
                $csvQuery .= " AND status LIKE '%$statusFilter%'";
            }

            if (!empty($phoneFilter)) {
                $phoneFilter = mysqli_real_escape_string($conn, $phoneFilter);
                $csvQuery .= " AND phone_number LIKE '%$phoneFilter%'";
            }

            if (!empty($userFilter)) {
                $userFilter = mysqli_real_escape_string($conn, $userFilter);
                $csvQuery .= " AND user LIKE '%$userFilter%'";
            }

            $csvResult = mysqli_query($conn, $csvQuery);

            if ($csvResult) {
                while ($row = mysqli_fetch_assoc($csvResult)) {
                    $callDate = strtotime($row['call_date']);
                    $date = date("Y-m-d", $callDate);
                    $time = date("H:i:s", $callDate);
                    $lengthInSeconds = $row['length_in_sec'];
                    $lengthFormatted = sprintf('%02d:%02d:%02d', ($lengthInSeconds / 3600), ($lengthInSeconds / 60 % 60), $lengthInSeconds % 60);

                    // Add row to CSV content
                    echo "csvContent += '" . $row['list_id'] . "," . $row['campaign_id'] . "," . $date . "," . $time . "," . $lengthFormatted . "," . $row['status'] . "," . $row['phone_number'] . "," . $row['user'] . "," . $row['term_reason'] . "\\n';\n";
                }
            }
            ?>

            // Create a data URI and trigger download
            var encodedUri = encodeURI(csvContent);
            var link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "inbound_call_log_" + <?php echo json_encode($currentDateTime); ?> + ".csv");
            document.body.appendChild(link);
            link.click();
        }
    </script>
</body>


</html>
<?php include "footer.php"; ?>