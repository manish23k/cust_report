<?php
session_start(); // Start the session on each page

include "config.php"; // Include the database configuration

// Ensure the user is logged in, or redirect them to the login page
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit;
}
?>

<?php include "header.php"; ?>
<?php

$fromDate = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-d');
$toDate = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');
$fromTime = isset($_GET['from_time']) ? $_GET['from_time'] : '00:00:00';
$toTime = isset($_GET['to_time']) ? $_GET['to_time'] : '23:59:59';
$campaignFilter = isset($_GET['campaign']) ? $_GET['campaign'] : 'All';
$chartType = isset($_GET['chart_type']) ? $_GET['chart_type'] : 'pie';
$dataType = isset($_GET['data_type']) ? $_GET['data_type'] : 'all'; // Set the data type to 'call_status'



// Validate time format (HH:MM:SS)
if (
    !preg_match('/^(?:2[0-3]|[01][0-9]):[0-5][0-9]:[0-5][0-9]$/', $fromTime) ||
    !preg_match('/^(?:2[0-3]|[01][0-9]):[0-5][0-9]:[0-5][0-9]$/', $toTime)
) {
    // Handle invalid time format
    // You may display an error message or set default values
}


//$sqlTotalCalls = "SELECT COUNT(*) AS total_calls FROM vicidial_log WHERE call_date BETWEEN '$fromDate 00:00:00' AND '$toDate 23:59:59'";

//echo "$sqlTotalCalls";

//$sqlAnsweredCalls = "SELECT COUNT(*) AS answered_calls FROM vicidial_closer_log WHERE status = 'ANSWER' AND call_date BETWEEN '$fromDate 00:00:00' AND '$toDate 23:59:59'";
$sqlAnsweredCalls = "SELECT COUNT(*) AS answered_calls FROM vicidial_closer_log WHERE status NOT IN ('DROP', 'QUEUE', 'AFTHRS', 'ERI', 'TIMEOT', 'XDROP') AND call_date BETWEEN '$fromDate $fromTime' AND '$toDate $toTime'";

$sqlAbandonedCalls = "SELECT COUNT(*) AS abandoned_calls FROM vicidial_closer_log WHERE status IN ('DROP', 'AFTHRS', 'ERI', 'TIMEOT', 'XDROP') AND call_date BETWEEN '$fromDate $fromTime' AND '$toDate $toTime'";

if ($campaignFilter !== 'All') {
    //$sqlTotalCalls .= " AND campaign_id = '$campaignFilter'";
    $sqlAnsweredCalls .= " AND campaign_id = '$campaignFilter'";
    $sqlAbandonedCalls .= " AND campaign_id = '$campaignFilter'";
}

//$resultTotalCalls = $mysqli->query($sqlTotalCalls);
$resultAnsweredCalls = $mysqli->query($sqlAnsweredCalls);
$resultAbandonedCalls = $mysqli->query($sqlAbandonedCalls);

//if (!$resultTotalCalls || !$resultAnsweredCalls || !$resultAbandonedCalls) {
if (!$resultAnsweredCalls || !$resultAbandonedCalls) {
    die("Error executing database query: " . $mysqli->error);
}

//$totalCallsData = $resultTotalCalls->fetch_assoc();
$answeredCallsData = $resultAnsweredCalls->fetch_assoc();
$abandonedCallsData = $resultAbandonedCalls->fetch_assoc();

$sqlStatusBreakdown = "SELECT status, COUNT(*) AS status_count FROM vicidial_closer_log WHERE call_date BETWEEN '$fromDate $fromTime' AND '$toDate $toTime'";
if ($campaignFilter !== 'All') {
    $sqlStatusBreakdown .= " AND campaign_id = '$campaignFilter'";
}

$sqlStatusBreakdown .= " GROUP BY status";

$resultStatusBreakdown = $mysqli->query($sqlStatusBreakdown);
if (!$resultStatusBreakdown) {
    die("Error executing status breakdown query: " . $mysqli->error);
}

$statusData = array();
while ($row = $resultStatusBreakdown->fetch_assoc()) {
    $statusData[$row['status']] = $row['status_count'];
}

$sqlTotalStatusCount = "SELECT status, COUNT(*) AS total_status_count FROM vicidial_closer_log WHERE call_date BETWEEN '$fromDate $fromTime' AND '$toDate $toTime'";

if ($campaignFilter !== 'All') {
    $sqlTotalStatusCount .= " AND campaign_id = '$campaignFilter'";
}

$sqlTotalStatusCount .= " GROUP BY status";


//echo "$sqlTotalStatusCount";
$resultTotalStatusCount = $mysqli->query($sqlTotalStatusCount);
if (!$resultTotalStatusCount) {
    die("Error executing total status count query: " . $mysqli->error);
}

$totalStatusCountData = array();
while ($row = $resultTotalStatusCount->fetch_assoc()) {
    $totalStatusCountData[$row['status']] = $row['total_status_count'];
}

$sqlCampaigns = "SELECT group_id, group_name FROM vicidial_inbound_groups";
$resultCampaigns = $mysqli->query($sqlCampaigns);

$campaignOptions = "<option value='All'>All</option>";
while ($row = $resultCampaigns->fetch_assoc()) {
    $campaignId = $row['group_id'];
    $campaignName = $row['group_name'];
    $selected = ($campaignFilter == $campaignId) ? "selected" : "";
    $campaignOptions .= "<option value='$campaignId' $selected>$campaignName</option>";
}

// SQL query to calculate the user's total calls count
$sqlUserTotalCalls = "SELECT user, COUNT(*) AS user_total_calls 
                     FROM vicidial_closer_log 
                     WHERE call_date BETWEEN '$fromDate $fromTime' AND '$toDate $toTime'";




// Add a condition to exclude the 'VDCL' user
if ($campaignFilter !== 'All') {
    $sqlUserTotalCalls .= " AND campaign_id = '$campaignFilter'";
}

// Exclude the 'VDCL' user by username
$sqlUserTotalCalls .= " AND user <> 'VDCL'";

$sqlUserTotalCalls .= " GROUP BY user";

//echo "$sqlUserTotalCalls";

$resultUserTotalCalls = $mysqli->query($sqlUserTotalCalls);
if (!$resultUserTotalCalls) {
    die("Error executing user total calls query: " . $mysqli->error);
}

$userTotalCallsData = array();
while ($row = $resultUserTotalCalls->fetch_assoc()) {
    $userTotalCallsData[$row['user']] = $row['user_total_calls'];
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Check if the form was submitted
    if (isset($_GET["apply_filter"])) {
        // Update the data type to 'call_status'
        $dataType = 'call_status';
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Incoming Calls Statistics</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="container mt-5 text-center">
        <h1 class="mb-3">Incoming Calls Statistics</h1>

        <form method="get" class="mb-3">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="from_date">From:</label>
                    <input type="date" name="from_date" id="from_date" class="form-control"
                        value="<?php echo $fromDate; ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="to_date">To:</label>
                    <input type="date" name="to_date" id="to_date" class="form-control" value="<?php echo $toDate; ?>">
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
                    <label for="campaign">Inbound Group:</label>
                    <select name="campaign" id="campaign" class="form-select">
                        <?php echo $campaignOptions; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="chart_type">Chart Type:</label>
                    <select name="chart_type" id="chart_type" class="form-select">
                        <option value="pie" <?php if ($chartType === 'pie')
                            echo 'selected'; ?>>Pie Chart</option>
                        <option value="bar" <?php if ($chartType === 'bar')
                            echo 'selected'; ?>>Bar Chart</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="data_type">Data Type:</label>
                    <select name="data_type" id="data_type" class="form-select">
                        <option value="call_status" <?php if ($dataType === 'call_status')
                            echo 'selected'; ?>>Call Status
                            Breakdown</option>
                        <option value="user_calls" <?php if ($dataType === 'user_calls')
                            echo 'selected'; ?>>User Total
                            Calls</option>
                        <option value="total_calls" <?php if ($dataType === 'total_calls')
                            echo 'selected'; ?>>Total Calls
                        </option>
                        <option value="all" <?php if ($dataType === 'all')
                            echo 'selected'; ?>>All</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 text-md-end">
                    <button type="submit" class="btn btn-primary mt-2">Apply Filter</button>
                </div>
            </div>
        </form>

    </div>

    <div id="sectionsContainer">
        <?php
        // Check the selected data type and display the corresponding section
        if ($dataType === 'call_status' || $dataType === 'all') {
            echo '<h2 id="callStatusTitle" class="text-center mb-4" style="cursor: pointer; text-decoration: underline;">Call Status Breakdown</h2>';
            echo '<table class="table table-bordered" id="callStatusContent">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Status</th>';
            echo '<th>Count</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            foreach ($statusData as $status => $count) {
                echo "<tr><td>$status</td><td>$count</td></tr>";
            }
            echo '</tbody>';
            echo '</table>';
        }

        if ($dataType === 'user_calls' || $dataType === 'all') {
            echo '<h2 id="userCallsTitle"  class="text-center mb-4" style="cursor: pointer; text-decoration: underline;">User Calls Count</h2>';
            echo '<table class="table table-bordered" id="userCallsContent">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>User</th>';
            echo '<th>Total Calls</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            foreach ($userTotalCallsData as $user => $totalCalls) {
                echo "<tr><td>$user</td><td>$totalCalls</td></tr>";
            }
            echo '</tbody>';
            echo '</table>';
        }

        if ($dataType === 'total_calls' || $dataType === 'all') {
            echo '<h2 id="dataTypeTitle" class="text-center mb-4" style="cursor: pointer; text-decoration: underline;">Total Calls</h2>';
            echo '<table class="table table-bordered" id="dataTypeContent">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Data Type</th>';
            echo '<th>Total</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            echo '<tr>';
            echo '<td>Answered Calls</td>';
            echo '<td>';
            echo '<span id="answeredCalls">';
            echo $answeredCallsData['answered_calls'];
            echo '</span>';
            echo '</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td>Drop / Timeout Calls</td>';
            echo '<td>';
            echo '<span id="abandonedCalls">';
            echo $abandonedCallsData['abandoned_calls'];
            echo '</span>';
            echo '</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td>Total Calls</td>';
            echo '<td>';
            echo '<span id="userTotalCalls">';
            echo ($answeredCallsData['answered_calls'] + $abandonedCallsData['abandoned_calls']);
            echo '</span>';
            echo '</td>';
            echo '</tr>';
            echo '</tbody>';
            echo '</table>';
        }
        ?>
    </div>
    <!-- </div> -->

    <!-- Add JavaScript to toggle the visibility of the content -->
    <script>

        function toggleVisibility(contentId) {
            var content = document.getElementById(contentId);

            if (content.style.display === 'none' || content.style.display === '') {
                content.style.display = 'table';
            } else {
                content.style.display = 'none';
            }
        }

        // Add click event handlers to the titles
        document.getElementById('userCallsTitle').addEventListener('click', function () {
            toggleVisibility('userCallsContent');
        });

        document.getElementById('callStatusTitle').addEventListener('click', function () {
            toggleVisibility('callStatusContent');
        });

        document.getElementById('dataTypeTitle').addEventListener('click', function () {
            toggleVisibility('dataTypeContent');
        });

    </script>

    <!-- JavaScript to generate Plotly charts for "Call Status Breakdown" -->

    <div id="statusChart" class="mt-3"></div>
    <div id="userTotalCallsChart" class="mt-3"></div>
    <div id="allChart" class="mt-3"></div>
    <div id="totalCallsChart" class="mt-3"></div>
    <?php if ($dataType === 'call_status' || $dataType === 'all') { ?>
        <!-- JavaScript to generate Plotly charts for "Call Status Breakdown" -->
        <!-- JavaScript to generate Plotly charts for "Call Status Breakdown" -->
        <script>
            var statusCategories = <?php echo json_encode(array_keys($statusData)); ?>;
            var statusCounts = <?php echo json_encode(array_values($statusData)); ?>;
            var chartType = '<?php echo $chartType; ?>';

            // Convert statusCounts to numbers
            statusCounts = statusCounts.map(count => parseInt(count, 10));

            // Calculate percentages
            var totalStatusCount = statusCounts.reduce((a, b) => a + b, 0);
            var percentages = statusCounts.map(count => ((count / totalStatusCount) * 100).toFixed(2) + '%');

            // Create labels with both status and value
            var statusLabels = statusCategories.map((status, index) => status + ' (' + statusCounts[index] + ', ' + percentages[index] + ')');

            // Chart title and labels
            var statusTitle = "Call Status Breakdown";
            var statusXAxisLabel = "Status";
            var statusYAxisLabel = "Count";

            // Create the appropriate chart based on the selected chart type and data type
            if (chartType === 'bar') {
                var statusData = [{
                    x: statusLabels,
                    y: statusCounts,
                    text: percentages,
                    type: 'bar'
                }];
                var statusLayout = {
                    title: statusTitle,
                    xaxis: {
                        title: statusXAxisLabel
                    },
                    yaxis: {
                        title: statusYAxisLabel
                    }
                };
                Plotly.newPlot('statusChart', statusData, statusLayout);
            } else if (chartType === 'pie') {
                var statusData = [{
                    labels: statusLabels,
                    values: statusCounts,
                    text: percentages,
                    type: 'pie'
                }];
                var statusLayout = {
                    title: statusTitle
                };
                Plotly.newPlot('statusChart', statusData, statusLayout);
            }


            // Add click event handler to the "Call Status Breakdown" chart
            document.getElementById('statusChart').on('plotly_click', function (data) {
                // Check if a point on the chart was clicked
                if (data.points.length > 0) {
                    // Extract the selected status, date, and time from the clicked point
                    var selectedStatus = statusCategories[data.points[0].pointNumber];
                    var fromDate = document.getElementById('from_date').value; // Assuming you have an element with id 'from_date'
                    var toDate = document.getElementById('to_date').value;     // Assuming you have an element with id 'to_date'
                    var fromTime = document.getElementById('from_time').value; // Assuming you have an element with id 'from_time'
                    var toTime = document.getElementById('to_time').value;     // Assuming you have an element with id 'to_time'

                    // Open the popup with the selected status, date, and time using GET
                    var popupURL = 'popup_status.php?status_id=' + encodeURIComponent(selectedStatus) +
                        '&from_date=' + encodeURIComponent(fromDate) +
                        '&to_date=' + encodeURIComponent(toDate) +
                        '&from_time=' + encodeURIComponent(fromTime) +
                        '&to_time=' + encodeURIComponent(toTime);

                    window.open(popupURL, '_blank', 'width=800, height=600');
                }
            });

            //
        </script>
    <?php } ?>


    <?php if ($dataType === 'user_calls' || $dataType === 'all') { ?>
        <!-- JavaScript to generate Plotly charts for "User Total Calls" -->

        <script>
            var userCategories = <?php echo json_encode(array_keys($userTotalCallsData)); ?>;
            var userCounts = <?php echo json_encode(array_values($userTotalCallsData)); ?>;
            var chartType = '<?php echo $chartType; ?>';

            // Chart title and labels
            var userTitle = "User Total Calls";
            var userXAxisLabel = "User";
            var userYAxisLabel = "Total Calls";

            // Create labels with both user and value
            var userLabels = userCategories.map((user, index) => user + ' (' + userCounts[index] + ')');

            // Create the appropriate chart based on the selected chart type and data type
            if (chartType === 'bar') {
                var userData = [{
                    x: userLabels,
                    y: userCounts,
                    type: 'bar'
                }];
                var userLayout = {
                    title: userTitle,
                    xaxis: {
                        title: userXAxisLabel
                    },
                    yaxis: {
                        title: userYAxisLabel
                    }
                };
                Plotly.newPlot('userTotalCallsChart', userData, userLayout);
            } else if (chartType === 'pie') {
                var userData = [{
                    labels: userLabels,
                    values: userCounts,
                    type: 'pie'
                }];
                var userLayout = {
                    title: userTitle
                };
                Plotly.newPlot('userTotalCallsChart', userData, userLayout);
            }

            // Add click event handler to the "User Total Calls" chart
            document.getElementById('userTotalCallsChart').on('plotly_click', function (data) {
                // Check if a point on the chart was clicked
                if (data.points.length > 0) {
                    // Extract the selected user, fromDate, and toDate from the clicked point
                    var selectedUser = userCategories[data.points[0].pointNumber];
                    var fromDate = document.getElementById('from_date').value; // Assuming you have an element with id 'from_date'
                    var toDate = document.getElementById('to_date').value;     // Assuming you have an element with id 'to_date'
                    var fromTime = document.getElementById('from_time').value; // Assuming you have an element with id 'from_time'
                    var toTime = document.getElementById('to_time').value;     // Assuming you have an element with id 'to_time'

                    // Log the values for debugging
                    console.log("Selected User:", selectedUser);
                    console.log("From Date:", fromDate);
                    console.log("To Date:", toDate);
                    console.log("From Time:", fromTime);
                    console.log("To Time:", toTime);

                    // Open the popup with the selected user, fromDate, and toDate using GET
                    var popupURL = 'popup_user.php?user=' + encodeURIComponent(selectedUser) +
                        '&from_date=' + encodeURIComponent(fromDate) +
                        '&to_date=' + encodeURIComponent(toDate) +
                        '&from_time=' + encodeURIComponent(fromTime) +
                        '&to_time=' + encodeURIComponent(toTime);

                    window.open(popupURL, '_blank', 'width=800, height=600');
                }
            });
        </script>

    <?php } ?>

    <?php if ($dataType === 'total_calls' || $dataType === 'all') { ?>

        <!-- JavaScript to generate Plotly charts for "Total Calls" -->
        <script>
            var totalCallsCategories = ['Answered Calls', 'Drop / Timeout Calls', 'Total Calls'];
            var totalCallsCounts = [
                <?php echo $answeredCallsData['answered_calls']; ?>,
                <?php echo $abandonedCallsData['abandoned_calls']; ?>,
                <?php echo ($answeredCallsData['answered_calls'] + $abandonedCallsData['abandoned_calls']); ?>
            ];
            var chartType = '<?php echo $chartType; ?>';

            // Chart title and labels
            var totalCallsTitle = "Total Calls";
            var totalCallsXAxisLabel = "Category";
            var totalCallsYAxisLabel = "Count";

            // Create labels with both category, value, and percentage
            var totalCallsLabels = totalCallsCategories.map((category, index) => category + ' (' + totalCallsCounts[index] + ')');

            // Create the appropriate chart based on the selected chart type and data type
            if (chartType === 'bar') {
                var totalCallsData = [{
                    x: totalCallsLabels,
                    y: totalCallsCounts,
                    type: 'bar'
                }];
                var totalCallsLayout = {
                    title: totalCallsTitle,
                    xaxis: {
                        title: totalCallsXAxisLabel
                    },
                    yaxis: {
                        title: totalCallsYAxisLabel
                    }
                };
                Plotly.newPlot('totalCallsChart', totalCallsData, totalCallsLayout);
            } else if (chartType === 'pie') {
                var totalCallsData = [{
                    labels: totalCallsLabels,
                    values: totalCallsCounts,
                    type: 'pie'
                }];
                var totalCallsLayout = {
                    title: totalCallsTitle
                };
                Plotly.newPlot('totalCallsChart', totalCallsData, totalCallsLayout);
            }

            // Add click event handler to the "Total Calls" chart
            document.getElementById('totalCallsChart').on('plotly_click', function (data) {
                // Check if a point on the chart was clicked
                if (data.points.length > 0) {
                    // Extract the selected category, fromDate, and toDate from the clicked point
                    var selectedCategory = totalCallsCategories[data.points[0].pointNumber];
                    var fromDate = document.getElementById('from_date').value; // Assuming you have an element with id 'from_date'
                    var toDate = document.getElementById('to_date').value;     // Assuming you have an element with id 'to_date'
                    var fromTime = document.getElementById('from_time').value; // Assuming you have an element with id 'from_time'
                    var toTime = document.getElementById('to_time').value;     // Assuming you have an element with id 'to_time'

                    // Log the values for debugging
                    console.log("Selected Category:", selectedCategory);
                    console.log("From Date:", fromDate);
                    console.log("To Date:", toDate);
                    console.log("From Time:", fromTime);
                    console.log("To Time:", toTime);

                    // Open the popup with the selected category, fromDate, and toDate using GET
                    var popupURL = 'popup_totalcalls.php?category=' + encodeURIComponent(selectedCategory) +
                        '&from_date=' + encodeURIComponent(fromDate) +
                        '&to_date=' + encodeURIComponent(toDate) +
                        '&from_time=' + encodeURIComponent(fromTime) +
                        '&to_time=' + encodeURIComponent(toTime);

                    window.open(popupURL, '_blank', 'width=800, height=600');
                }
            });
        </script>

    <?php } ?>

    </div>
</body>

</html>
<?php include "footer.php"; ?>