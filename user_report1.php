<?php
session_start(); // Start the session on each page

include "config.php"; // Include the database configuration

include "header.php";

$startDate = $_GET['start_date'] ?? date('Y-m-d');
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$fromTime = $_GET['from_time'] ?? '00:00:00';
$toTime = $_GET['to_time'] ?? '23:59:59';
$user = $_GET['user'] ?? "";



// Validate time format (HH:MM:SS)
if (
    !preg_match('/^(?:2[0-3]|[01][0-9]):[0-5][0-9]:[0-5][0-9]$/', $fromTime) ||
    !preg_match('/^(?:2[0-3]|[01][0-9]):[0-5][0-9]:[0-5][0-9]$/', $toTime)
) {
    // Handle invalid time format
    // You may display an error message or set default values
}


?>

<!DOCTYPE html>
<html>

<head>
    <title>Vicidial Calls Report</title>
</head>

<body style="background-color: #f2f2f2;">
    <div class="container mt-4 text-center">
        <h1 class="display-4">User Report</h1>
        <form method="get" action="" class="mb-4">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="start_date">Start Date:</label>
                    <input type="date" name="start_date" id="start_date" value="<?= $startDate ?>" class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="end_date">End Date:</label>
                    <input type="date" name="end_date" id="end_date" value="<?= $endDate ?>" class="form-control">
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
                    <label for="user">User:</label>
                    <select name="user" id="user" class="form-select">
                        <option value="">Select User</option>
                        <?php
                        $userQuery = "SELECT DISTINCT user FROM vicidial_users WHERE user_level <= 6 AND user NOT IN ('VDAD', 'VDCL')";
                        $userResult = mysqli_query($conn, $userQuery);

                        if ($userResult) {
                            while ($row = mysqli_fetch_assoc($userResult)) {
                                $userValue = $row['user'];
                                $selected = ($user == $userValue) ? "selected" : "";
                                echo "<option value='$userValue' $selected>$userValue</option>";
                            }
                        } else {
                            // Handle the error, if any
                            echo "<option disabled selected>Error fetching users</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-2 mb-2">
                    <label for="search" class="invisible">Search Button</label>
                    <input type="submit" name="search" value="Search" class="btn btn-primary form-control">
                </div>
            </div>
        </form>

        <?php
        // Function to convert duration in HH:MM:SS format to seconds
        function durationToSeconds($duration)
        {
            list($hours, $minutes, $seconds) = explode(':', $duration);
            return $hours * 3600 + $minutes * 60 + $seconds;
        }

        // Function to sum durations in "HH:MM:SS" format
        function sumDurations($duration1, $duration2)
        {
            // Convert durations to seconds
            $seconds1 = durationToSeconds($duration1);
            $seconds2 = durationToSeconds($duration2);

            // Sum durations in seconds
            $totalSeconds = $seconds1 + $seconds2;

            // Convert total duration to "HH:MM:SS" format
            $totalHours = floor($totalSeconds / 3600);
            $totalMinutes = floor(($totalSeconds % 3600) / 60);
            $totalSeconds = $totalSeconds % 60;

            return sprintf("%02d:%02d:%02d", $totalHours, $totalMinutes, $totalSeconds);
        }


        // Function to convert seconds into HH:MM:SS format
        function sec_convert($seconds, $format = 'H:i:s')
        {
            $dt = new DateTime("@$seconds");
            return $dt->format($format);
        }




        if (isset($_GET['search'])) {
            // Get user input
            $startDate = $_GET['start_date'];
            $endDate = $_GET['end_date'];
            $fromTime = $_GET['from_time'];
            $toTime = $_GET['to_time'];
            $user = $_GET['user'];



            // Execute the outgoing status count query
            $outgoingStatusCountQuery = "SELECT status, COUNT(*) as count, SUM(length_in_sec) as total_duration FROM vicidial_log WHERE user = '$user' AND call_date BETWEEN '$startDate $fromTime' AND '$endDate $toTime' GROUP BY status ORDER BY status";
            $outgoingStatusCountResult = mysqli_query($conn, $outgoingStatusCountQuery);

            // Execute the incoming status count query
            $incomingStatusCountQuery = "SELECT status, COUNT(*) as count, SUM(length_in_sec - queue_seconds) as total_duration FROM vicidial_closer_log WHERE user = '$user' AND call_date BETWEEN '$startDate $fromTime' AND '$endDate $toTime' GROUP BY status ORDER BY status";
            $incomingStatusCountResult = mysqli_query($conn, $incomingStatusCountQuery);

            //echo "Debugging Query: $incomingStatusCountQuery";
            ?>

            <!-- Display Combined Outgoing and Incoming status counts -->
            <div class="mt-4">
                <h3>Agent Talk Time and Status</h3>

                <!-- Combined Status Count Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>Status</th>
                                <th>Count</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $outgoingData = [];
                            $incomingData = [];
                            $totalDurationInSeconds = 0;

                            while ($row = mysqli_fetch_assoc($outgoingStatusCountResult)) {
                                $status = $row['status'];
                                if ($status !== '0' || durationToSeconds($row['total_duration']) > 0) {
                                    // Exclude the "0 00:00:00" entry
                                    $outgoingData[$status] = [
                                        'count' => $row['count'],
                                        'total_duration' => $row['total_duration']
                                    ];
                                    // Add outgoing total duration to the overall total duration
                                    $totalDurationInSeconds += durationToSeconds($row['total_duration']);
                                }
                            }

                            while ($row = mysqli_fetch_assoc($incomingStatusCountResult)) {
                                $status = $row['status'];
                                if ($status !== '0' || durationToSeconds($row['total_duration']) > 0) {
                                    // Exclude the "0 00:00:00" entry
                                    $incomingData[$status] = [
                                        'count' => $row['count'],
                                        'total_duration' => $row['total_duration']
                                    ];
                                    // Add incoming total duration to the overall total duration
                                    $totalDurationInSeconds += durationToSeconds($row['total_duration']);
                                }
                            }

                            // Combine data based on status
                            $allStatus = array_unique(array_merge(array_keys($outgoingData), array_keys($incomingData)));

                            // Function to format duration in HH:MM:SS format
                            function formatDuration($hours, $minutes, $seconds)
                            {
                                return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                            }
                            // Calculate total count
                            $totalCount = 0;
                            foreach ($allStatus as $status) {
                                $outgoingCount = isset($outgoingData[$status]) ? $outgoingData[$status]['count'] : 0;
                                $incomingCount = isset($incomingData[$status]) ? $incomingData[$status]['count'] : 0;
                                $totalCount += $outgoingCount + $incomingCount;
                            }

                            // Calculate total duration
                            $totalDurationInSeconds = 0;

                            // Print rows for each status
                            foreach ($allStatus as $status) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($status) . "</td>";

                                // Combined Count
                                $outgoingCount = isset($outgoingData[$status]) ? $outgoingData[$status]['count'] : 0;
                                $incomingCount = isset($incomingData[$status]) ? $incomingData[$status]['count'] : 0;
                                $statusCount = $outgoingCount + $incomingCount;
                                echo "<td>" . htmlspecialchars($statusCount) . "</td>";

                                // Combined Duration
                                $outgoingDuration = isset($outgoingData[$status]) ? $outgoingData[$status]['total_duration'] : '00:00:00';
                                $incomingDuration = isset($incomingData[$status]) ? $incomingData[$status]['total_duration'] : '00:00:00';

                                // Convert individual durations to seconds and accumulate
                                $totalDurationInSeconds += durationToSeconds($outgoingDuration) + durationToSeconds($incomingDuration);

                                // Display individual durations in HH:MM:SS format
                                list($hoursIncoming, $minutesIncoming, $secondsIncoming) = explode(':', $incomingDuration);
                                echo "<td>" . htmlspecialchars(formatDuration($hoursIncoming, $minutesIncoming, $secondsIncoming)) . "</td>";
                                // Display individual durations in HH:MM:SS format
                                list($hoursOutgoing, $minutesOutgoing, $secondsOutgoing) = explode(':', $outgoingDuration);
                                echo "<td>" . htmlspecialchars(formatDuration($hoursOutgoing, $minutesOutgoing, $secondsOutgoing)) . "</td>";

                                echo "</tr>";
                            }

                            // Debug: Output the value of $totalDurationInSeconds
                            echo "Total Duration in Seconds: " . $totalDurationInSeconds;

                            // Print TOTAL CALLS row at the bottom
                            echo "<tr>";
                            echo "<td>TOTAL CALLS</td>";
                            echo "<td>" . htmlspecialchars($totalCount) . "</td>";

                            // Convert total duration to "HH:MM:SS" format
                            $totalDurationFormatted = sec_convert($totalDurationInSeconds);
                            echo "<td>" . htmlspecialchars($totalDurationFormatted) . "</td>";
                            echo "</tr>";
        }
        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>



    <?php
    // Execute the user log query
    $userLogQuery = "SELECT event, event_epoch, event_date, campaign_id, user_group, session_id, server_ip, extension, computer_ip, phone_login, phone_ip, IF(event='LOGOUT' OR event='TIMEOUTLOGOUT', 1, 0) AS LOGpriority, webserver, login_url FROM vicidial_user_log WHERE user= '$user' AND event_date >= '$startDate $fromTime' AND event_date <= '$endDate $toTime' ORDER BY event_date, LOGpriority ASC";
    $userLogResult = mysqli_query($conn, $userLogQuery);
    //echo "Debugging Query: $userLogQuery";
    
    // Display the result in an HTML table
    if ($userLogResult) {
        echo '<div class="mt-4">';
        echo '<h3>Agent Login and Logout Time:</h3>';
        echo '<div class="table-responsive">';
        echo '<table class="table table-bordered table-striped">';
        echo '<thead class="thead-dark">';
        echo '<tr>';
        echo '<th>Event</th>';
        echo '<th>Date</th>';
        echo '<th>Campaign</th>';
        echo '<th>Group</th>';
        echo '<th>Session</th>';
        // echo '<th>Hours:MM:SS</th>';
        echo '<th>Server</th>';
        echo '<th>Phone</th>';
        echo '<th>Computer</th>';
        echo '<th>Phone IP</th>';
        echo '<th>Login</th>';
        echo '<th>Phone IP</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        while ($logRow = mysqli_fetch_assoc($userLogResult)) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($logRow['event']) . '</td>';
            echo '<td>' . htmlspecialchars($logRow['event_date']) . '</td>';
            echo '<td>' . htmlspecialchars($logRow['campaign_id']) . '</td>';
            echo '<td>' . htmlspecialchars($logRow['user_group']) . '</td>';
            echo '<td>' . htmlspecialchars($logRow['session_id']) . '</td>';
            // echo '<td>' . htmlspecialchars(sec_convert($logRow['event_epoch'])) . '</td>';
            echo '<td>' . htmlspecialchars($logRow['server_ip']) . '</td>';
            echo '<td>' . htmlspecialchars($logRow['extension']) . '</td>';
            echo '<td>' . htmlspecialchars($logRow['computer_ip']) . '</td>';
            echo '<td>' . htmlspecialchars($logRow['phone_ip']) . '</td>';
            echo '<td>' . htmlspecialchars($logRow['login_url']) . '</td>';
            echo '<td>' . htmlspecialchars($logRow['webserver']) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
    } else {
        // Handle the error, if any
        echo "Error executing the query: " . mysqli_error($conn);
    }

    ?>
    

    <?php
// Execute the Closer In-Group Selection logs query
$closerLogQuery = "SELECT user, campaign_id, event_date, blended, closer_campaigns, manager_change FROM vicidial_user_closer_log WHERE user = '$user' AND event_date >= '$startDate $fromTime' AND event_date <= '$endDate $toTime' ORDER BY event_date DESC LIMIT 1000";
$closerLogResult = mysqli_query($conn, $closerLogQuery);
echo "Debugging Query: $closerLogQuery";

// Display the result in an HTML table
if ($closerLogResult) {
    echo '<div class="mt-4">';
    echo '<h3>Closer In-Group Selection Logs</h3>';
    echo '<div class="table-responsive">';
    echo '<table class="table table-bordered table-striped">';
    echo '<thead class="thead-dark">';
    echo '<tr>';
    echo '<th>User</th>';
    echo '<th>Date/Time</th>';
    echo '<th>Campaign</th>';
    echo '<th>Blended</th>';
    echo '<th>Groups</th>';
    echo '<th>Manager</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    while ($logRow = mysqli_fetch_assoc($closerLogResult)) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($logRow['user']) . '</td>';
        echo '<td>' . htmlspecialchars($logRow['event_date']) . '</td>';
        echo '<td>' . htmlspecialchars($logRow['campaign_id']) . '</td>';
        echo '<td>' . htmlspecialchars($logRow['blended']) . '</td>';
        echo '<td>' . htmlspecialchars($logRow['closer_campaigns']) . '</td>';
        echo '<td>' . htmlspecialchars($logRow['manager_change']) . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    echo '</div>';
} else {
    // Handle the error, if any
    echo "Error executing the query: " . mysqli_error($conn);
}
?>


<?php
// Outgoing Calls Log Query
$outgoingLogQuery = "SELECT uniqueid, call_date, length_in_sec, status, phone_number, campaign_id, user_group, list_id, lead_id, term_reason FROM vicidial_log WHERE user = '$user' AND call_date >= '$startDate $fromTime' AND call_date <= '$endDate $toTime' ORDER BY call_date DESC LIMIT 10000";
$outgoingLogResult = mysqli_query($conn, $outgoingLogQuery);
//echo "Debugging Query: $outgoingLogQuery";

// Display the result in an HTML table
if ($outgoingLogResult) {
    echo '<div class="mt-4">';
    echo '<h3>Outgoing Calls Log</h3>';
    echo '<div class="table-responsive">';
    echo '<table class="table table-bordered table-striped">';
    echo '<thead class="thead-dark">';
    echo '<tr>';
    echo '<th>#</th>';
    echo '<th>Date/Time</th>';
    echo '<th>Length</th>';
    echo '<th>Status</th>';
    echo '<th>Phone</th>';
    echo '<th>Campaign</th>';
    echo '<th>Group</th>';
    echo '<th>List</th>';
    echo '<th>Lead</th>';
    echo '<th>Hangup Reason</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    $rowNumber = 1;
    while ($logRow = mysqli_fetch_assoc($outgoingLogResult)) {
        echo '<tr>';
        echo '<td>' . $rowNumber . '</td>';
        echo '<td>' . htmlspecialchars($logRow['call_date']) . '</td>';
        echo '<td>' . htmlspecialchars($logRow['length_in_sec']) . '</td>';
        echo '<td>' . htmlspecialchars($logRow['status']) . '</td>';
        echo '<td>' . htmlspecialchars($logRow['phone_number']) . '</td>';
        echo '<td>' . htmlspecialchars($logRow['campaign_id']) . '</td>';
        echo '<td>' . htmlspecialchars($logRow['user_group']) . '</td>';
        echo '<td>' . htmlspecialchars($logRow['list_id']) . '</td>';
        echo '<td>' . htmlspecialchars($logRow['lead_id']) . '</td>';
        echo '<td>' . htmlspecialchars($logRow['term_reason']) . '</td>';
        echo '</tr>';
        $rowNumber++;
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    echo '</div>';
} else {
    // Handle the error, if any
    echo "Error executing the query: " . mysqli_error($conn);
}
?>


<?php
// Inbound Closer Calls Log Query
$inboundCloserLogQuery = "SELECT call_date, length_in_sec, status, phone_number, campaign_id, queue_seconds, user_group, list_id, lead_id, term_reason, closecallid, (length_in_sec - queue_seconds) AS agent_seconds FROM vicidial_closer_log WHERE user = '$user' AND call_date >= '$startDate $fromTime' AND call_date <= '$endDate $toTime' ORDER BY call_date DESC LIMIT 10000";
$inboundCloserLogResult = mysqli_query($conn, $inboundCloserLogQuery);
//echo "Debugging Query: $inboundCloserLogQuery";

// Display the result in an HTML table
if ($inboundCloserLogResult) {
    echo '<div class="mt-4">';
    echo '<h3>Inbound Closer Calls Log</h3>';
    echo '<div class="table-responsive">';
    echo '<table class="table table-bordered table-striped">';
    echo '<thead class="thead-dark">';
    echo '<tr>';
    echo '<th>#</th>';
    echo '<th>Date/Time</th>';
    echo '<th>Length</th>';
    echo '<th>Status</th>';
    echo '<th>Phone</th>';
    echo '<th>Campaign</th>';
    echo '<th>In-Group</th>';
    echo '<th>Wait (s)</th>';
    echo '<th>Agent (s)</th>';
    echo '<th>List</th>';
    echo '<th>Lead</th>';
    echo '<th>Hangup Reason</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    $rowNumber = 1;
    while ($logRow = mysqli_fetch_assoc($inboundCloserLogResult)) {
        echo '<tr>';
        echo '<td>' . $rowNumber . '</td>';
        echo '<td>' . htmlspecialchars($logRow['call_date']) . '</td>';
        echo '<td>' . htmlspecialchars($logRow['length_in_sec']) . '</td>';
        echo '<td>' . htmlspecialchars($logRow['status']) . '</td>';
        echo '<td>' . htmlspecialchars($logRow['phone_number']) . '</td>';
        echo '<td>' . htmlspecialchars($logRow['campaign_id']) . '</td>';
        echo '<td>' . htmlspecialchars($logRow['user_group']) . '</td>';
        echo '<td>' . htmlspecialchars($logRow['queue_seconds']) . '</td>';
        echo '<td>' . htmlspecialchars($logRow['agent_seconds']) . '</td>';
        echo '<td>' . htmlspecialchars($logRow['list_id']) . '</td>';
        echo '<td>' . htmlspecialchars($logRow['lead_id']) . '</td>';
        echo '<td>' . htmlspecialchars($logRow['term_reason']) . '</td>';
        echo '</tr>';
        $rowNumber++;
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    echo '</div>';
} else {
    // Handle the error, if any
    echo "Error executing the query: " . mysqli_error($conn);
}
?>















</body>

</html>