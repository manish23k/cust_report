<?php


session_start(); // Start the session on each page

include "config.php"; // Include the database configuration
include "header.php";


// Ensure the user is logged in, or redirect them to the login page
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit;
}
?>


<?php


// Initialize date filters with default values
$start_date = date('Y-m-d'); // Default to the last 7 days
$end_date = date('Y-m-d');

// Initialize campaign filter with all campaigns
$selected_campaign = "All Campaigns";

// Initialize inbound group filter with all groups
$selected_inbound_group = "All Inbound Groups";

// Initialize the report type filter
$report_type = "outbound"; // Default to "outbound"

// Check if the user has submitted filters
if (isset($_POST['apply_filters'])) {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $selected_campaign = $_POST['campaign'];
    $selected_inbound_group = $_POST['inbound_group'];
    $report_type = $_POST['report_type'];
}

// Define the selected columns for the inbound report
$inbound_columns = "lead_id, list_id, campaign_id, call_date, length_in_sec, status, phone_code, phone_number, user, comments, processed, queue_seconds, user_group, xfercallid, term_reason, agent_only, queue_position, called_count";

// Define the selected columns for the outbound report
$outbound_columns = "lead_id, list_id, campaign_id, DATE(call_date) AS CallDate, TIME(call_date) AS CallTime, length_in_sec, status, phone_number, user, comments, user_group, term_reason, alt_dial, called_count";

// SQL query to retrieve inbound call data with the selected columns
$inbound_query = "SELECT $inbound_columns FROM vicidial_closer_log
          WHERE DATE(call_date) BETWEEN '$start_date' AND '$end_date'";
if ($selected_inbound_group !== "All Inbound Groups") {
    $inbound_query .= " AND campaign_id = '$selected_inbound_group'";
}
$inbound_query .= " ORDER BY call_date";
echo "$inbound_query";

// SQL query to retrieve outbound call data with the selected columns
$outbound_query = "SELECT $outbound_columns FROM vicidial_log
          WHERE DATE(call_date) BETWEEN '$start_date' AND '$end_date'";
if ($selected_campaign !== "All Campaigns") {
    $outbound_query .= " AND campaign_id = '$selected_campaign'";
}
$outbound_query .= " ORDER BY call_date";

// Determine which query to execute based on the report type
if ($report_type === "outbound") {
    $report_query = $outbound_query;
} else {
    $report_query = $inbound_query;
}

// Execute the report query
$report_result = $mysqli->query($report_query);

// ... (Rest of the code remains the same)

// Check for errors
if (!$report_result) {
    die("Query failed: " . mysqli_error($conn));
}

// Check for available campaigns for the dropdown list
$campaigns_query = "SELECT DISTINCT campaign_id FROM vicidial_log";
$campaigns_result = $mysqli->query($campaigns_query);

// Fetch available campaigns
$campaign_options = [];
while ($row = $campaigns_result->fetch_assoc()) {
    $campaign_options[] = $row['campaign_id'];
}

// Check for available inbound groups for the dropdown list
$inbound_groups_query = "SELECT DISTINCT campaign_id FROM vicidial_closer_log";
$inbound_groups_result = $mysqli->query($inbound_groups_query);

// Fetch available inbound groups
$inbound_group_options = [];
while ($row = $inbound_groups_result->fetch_assoc()) {
    $inbound_group_options[] = $row['campaign_id'];
}

// Create a CSV file for the report (Only if data is being displayed)
if ($report_result->num_rows > 0 && isset($_POST['download_csv'])) {
    $filename = ($report_type === "outbound") ? "outbound_call_report.csv" : "inbound_call_report.csv";
    header('Content-Type: text/csv');
    header("Content-Disposition: attachment; filename=$filename");

    $output = fopen("php://output", "w");

    // Add a header row with the selected column names
    $header_row = ($report_type === "outbound") ? explode(", ", $outbound_columns) : explode(", ", $inbound_columns);
    fputcsv($output, $header_row);

    // Fetch and output the data
    while ($row = $report_result->fetch_assoc()) {
        $data_row = array_values($row);
        fputcsv($output, $data_row);
    }

    // Close the CSV file
    fclose($output);
}

?>
<!DOCTYPE html>
<html>

<head>
    <title></title>

</head>

<body>
    <div class="container mt-4 text-center">
        <h1 class="display-4">Export Call Report</h1>
        <form method="get" action="" class="mb-4">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="start_date">From Date:</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>" class="form-control" required>
                    
                </div>    
                <div class="col-md-3 mb-3">
                    <label for="end_date">To Date:</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>" class="form-control" required>
                    </div> 
                    <div class="col-md-3 mb-3">
                    <label for="campaign">Select Campaign:</label>
                    <select name="campaign" id="campaign" class="form-select">
                        <option value="All Campaigns">All Campaigns</option>
                        <?php
                        foreach ($campaign_options as $campaign) {
                            $selected = $selected_campaign === $campaign ? "selected" : "";
                            echo "<option value='$campaign' $selected>$campaign</option>";
                        }
                        ?>
                    </select>
                    </div>
                    <div class="col-md-3 mb-3">
                    <label for="inbound_group">Select Inbound Group:</label>
                    <select name="inbound_group" id="inbound_group" class="form-select">
                        <option value="All Inbound Groups">All Inbound Groups</option>
                        <?php
                        foreach ($inbound_group_options as $group) {
                            $selected = $selected_inbound_group === $group ? "selected" : "";
                            echo "<option value='$group' $selected>$group</option>";
                        }
                        ?>
                    </select>
                    </div>
                    <div class="col-md-3 mb-3">
                    <label for="report_type">Select Report Type:</label>
                    <select name="report_type" id="report_type" class="form-select">
                        <option value="outbound" <?php echo $report_type === "outbound" ? "selected" : ""; ?>>Outbound</option>
                        <option value="inbound" <?php echo $report_type === "inbound" ? "selected" : ""; ?>>Inbound</option>
                    </select>
                    </div>
                    <div class="col-md-2 mb-2">
                    <label for="search" class="invisible">Search Button</label>
                    <input type="submit" name="apply_filters" value="Search" class="btn btn-primary form-control">
                </div>
                    <!-- <input type="submit" name="apply_filters" value="Apply Filters"> -->
        </form>

        <!-- Add a button at the top for Download CSV -->
        <!-- <form method="POST" action="">
            <input type="submit" name="download_csv" value="Download CSV" style="margin-bottom: 10px;">
        </form> -->
        <div class="col-md-2 mb-2">
                    <label for="download_csv" class="invisible">Download CSV Button</label>
                    <button type="submit" onclick="downloadCSV()" class="btn btn-success form-control">Download
                        CSV</button>
                </div>

        <h2><?php echo ucfirst($report_type); ?> Call Report</h2>

        <?php
        // Display the report data in a table
        if ($report_result->num_rows > 0) {
            echo "<form method='POST' action=''>"; // Make sure form action is empty

            if ($report_type === "outbound") {
                echo "<table class='table table-bordered table-striped mt-4'>";
                echo "<thead class='thead-dark'>";
                echo "<table border='1'>";
                echo "<tr><th>Lead ID</th><th>List ID</th><th>Campaign ID</th><th>Call Date</th><th>Call Time</th><th>Length (sec)</th><th>Status</th><th>Phone Number</th><th>User</th><th>Comments</th><th>User Group</th><th>Term Reason</th><th>Alt Dial</th><th>Called Count</th></tr>";
            } else {
                echo "<table class='table table-bordered table-striped mt-4'>";
                echo "<thead class='thead-dark'>";
                echo "<table border='1'>";
                echo "<tr><th>Lead ID</th><th>List ID</th><th>Campaign ID</th><th>Call Date</th><th>Length (sec)</th><th>Status</th><th>Phone Number</th><th>User</th><th>Comments</th><th>Processed</th><th>Queue Seconds</th><th>User Group</th><th>Xfer Call ID</th><th>Term Reason</th><th>Queue Position</th><th>Called Count</th></tr>";
            }

            while ($row = $report_result->fetch_assoc()) {
                echo "<tr>";
                if ($report_type === "outbound") {
                    // Exclude columns for outbound report
                    echo "<td>" . $row['lead_id'] . "</td>";
                    echo "<td>" . $row['list_id'] . "</td>";
                    echo "<td>" . $row['campaign_id'] . "</td>";
                    echo "<td>" . $row['CallDate'] . "</td>";
                    echo "<td>" . $row['CallTime'] . "</td>";
                    echo "<td>" . $row['length_in_sec'] . "</td>";
                    echo "<td>" . $row['status'] . "</td>";
                    echo "<td>" . $row['phone_number'] . "</td>";
                    echo "<td>" . $row['user'] . "</td>";
                    echo "<td>" . $row['comments'] . "</td>";
                    echo "<td>" . $row['user_group'] . "</td>";
                    echo "<td>" . $row['term_reason'] . "</td>";
                    echo "<td>" . $row['alt_dial'] . "</td>";
                    echo "<td>" . $row['called_count'] . "</td>";
                } else {
                    // Exclude columns for inbound report
                    echo "<td>" . $row['lead_id'] . "</td>";
                    echo "<td>" . $row['list_id'] . "</td>";
                    echo "<td>" . $row['campaign_id'] . "</td>";
                    echo "<td>" . $row['call_date'] . "</td>";
                    echo "<td>" . $row['length_in_sec'] . "</td>";
                    echo "<td>" . $row['status'] . "</td>";
                    echo "<td>" . $row['phone_number'] . "</td>";
                    echo "<td>" . $row['user'] . "</td>";
                    echo "<td>" . $row['comments'] . "</td>";
                    echo "<td>" . $row['processed'] . "</td>";
                    echo "<td>" . $row['queue_seconds'] . "</td>";
                    echo "<td>" . $row['user_group'] . "</td>";
                    echo "<td>" . $row['xfercallid'] . "</td>";
                    echo "<td>" . $row['term_reason'] . "</td>";
                    echo "<td>" . $row['queue_position'] . "</td>";
                    echo "<td>" . $row['called_count'] . "</td>";
                }
                echo "</tr>";
            }

            echo "<tr><td colspan='" . ($report_type === "outbound" ? "13" : "16") . "'><input type='submit' name='download_csv' value='Download CSV'></td></tr>";

            echo "</table>";
            echo "</form>";
        } else {
            echo "No " . $report_type . " records found.";
        }

        ?>
</body>

</html>
<?php include "footer.php"; ?>s