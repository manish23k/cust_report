<?php
session_start(); // Start the session on each page
include "config.php"; // Include the database configuration

// Ensure the user is logged in, or redirect them to the login page
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit;
}

$recordsPerPage = 50;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $recordsPerPage;

// Handle date range
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$start_time = isset($_GET['start_time']) ? $_GET['start_time'] : '00:00:00';
$end_time = isset($_GET['end_time']) ? $_GET['end_time'] : '23:59:59';

// Validate time format (HH:MM:SS)
if (
    !preg_match('/^(?:2[0-3]|[01][0-9]):[0-5][0-9]:[0-5][0-9]$/', $start_time) ||
    !preg_match('/^(?:2[0-3]|[01][0-9]):[0-5][0-9]:[0-5][0-9]$/', $end_time)
) {
    // Handle invalid time format
    $start_time = '00:00:00';
    $end_time = '23:59:59';
}

// Handle user filter
$userFilter = isset($_GET['user_filter']) ? $conn->real_escape_string($_GET['user_filter']) : '';

// Handle phone number filter
$phoneNumberFilter = isset($_GET['phone_number_filter']) ? $conn->real_escape_string($_GET['phone_number_filter']) : '';

// Fetch user data for the dropdown
$userOptions = '';
$sql = "SELECT DISTINCT user FROM vicidial_users WHERE user_level <= 6 AND user NOT IN ('VDAD', 'VDCL')";
$userResult = $conn->query($sql);
if ($userResult) {
    while ($row = $userResult->fetch_assoc()) {
        $selected = ($userFilter == $row['user']) ? 'selected' : '';
        $userOptions .= "<option value='{$row['user']}' $selected>{$row['user']}</option>";
    }
}

$start_datetime = $startDate . ' ' . $start_time;
$end_datetime = $endDate . ' ' . $end_time;

$sql = "SELECT 
r.recording_id, 
DATE(r.start_time) AS date, 
TIME(r.start_time) AS time, 
r.length_in_sec, 
r.lead_id, 
r.user, 
v.phone_number, 
COALESCE(c.status, v.status) AS status, 
r.filename,  
r.location  
FROM 
recording_log r
LEFT JOIN 
vicidial_closer_log c ON r.lead_id = c.lead_id
LEFT JOIN 
vicidial_log v ON r.lead_id = v.lead_id
WHERE 
r.start_time >= '2024-05-21 00:00:00' AND 
r.start_time <= '2024-05-21 23:59:59' AND 
(v.phone_number LIKE '%%' OR 
c.phone_number LIKE '%%')
ORDER BY 
r.start_time DESC 
LIMIT 
50 OFFSET 0";



// if (!empty($userFilter)) {
//     $sql .= " AND user = '$userFilter'";
// }

if (!empty($phoneNumberFilter)) {
    $sql .= " AND phone_number LIKE '%$phoneNumberFilter%'";
}

$sql .= " ORDER BY start_time DESC LIMIT $offset, $recordsPerPage";

$result = $conn->query($sql);

if (!$result) {
    // Error handling: Output the error message and SQL query for debugging
    echo "Error: " . $conn->error . "<br>";
    echo "Query: " . $sql;
    exit;
}

$totalRecordsResult = $conn->query("SELECT COUNT(*) AS total FROM recording_log 
                              WHERE start_time BETWEEN '$start_datetime' AND '$end_datetime'" . 
                              (!empty($userFilter) ? " AND user = '$userFilter'" : "") . 
                              (!empty($phoneNumberFilter) ? " AND phone_number LIKE '%$phoneNumberFilter%'" : ""));

if (!$totalRecordsResult) {
    // Error handling: Output the error message and SQL query for debugging
    echo "Error: " . $conn->error . "<br>";
    echo "Query: " . $sql;
    exit;
}

$totalRecords = $totalRecordsResult->fetch_assoc()['total'];

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    while ($row = $result->fetch_assoc()) {
        $recordingFileName = $row['filename'] . "-all.mp3";
        $recordingFilePath = '/var/spool/asterisk/monitorDONE/MP3/' . $recordingFileName;
        $row['file_exists'] = file_exists($recordingFilePath);
        ?>
        <tr>
            <td><?= $row['recording_id'] ?></td>
            <td><?= $row['date'] ?></td>
            <td><?= $row['time'] ?></td>
            <td><?= $row['length'] ?></td>
            <td><?= $row['lead_id'] ?></td>
            <td><?= $row['phone_number'] ?></td>
            <td><?= $row['user'] ?></td>
            <td><?= $row['status'] ?></td>
            <td>
                <?php if ($row['file_exists']): ?>
                    <audio controls>
                        <source src="<?= $row['location'] ?>" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                <?php else: ?>
                    Recording N/A
                <?php endif; ?>
            </td>
            <td>
                <?php if ($row['file_exists']): ?>
                    <a href="<?= $row['location'] ?>" download>Download</a>
                <?php else: ?>
                    Download N/A
                <?php endif; ?>
            </td>
        </tr>
        <?php
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Recording Log</title>
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h2>Recording Log</h2>
    <form method="GET" id="filterForm">
        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="start_date">Start Date:</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="<?= $startDate ?>">
            </div>
            <div class="col-md-3 mb-3">
                <label for="end_date">End Date:</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="<?= $endDate ?>">
            </div>
            <div class="col-md-3 mb-3">
                <label for="start_time">Start Time:</label>
                <input type="time" name="start_time" id="start_time" class="form-control" value="<?= $start_time ?>">
            </div>
            <div class="col-md-3 mb-3">
                <label for="end_time">End Time:</label>
                <input type="time" name="end_time" id="end_time" class="form-control" value="<?= $end_time ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="user_filter">User Filter:</label>
                <select name="user_filter" id="user_filter" class="form-control">
                    <option value="">All Users</option>
                    <?= $userOptions ?>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="phone_number_filter">Phone Number Filter:</label>
                <input type="text" name="phone_number_filter" id="phone_number_filter" class="form-control" value="<?= $phoneNumberFilter ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 mb-3">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Recording ID</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Length</th>
                    <th>Lead ID</th>
                    <th>Phone Number</th>
                    <th>User</th>
                    <th>Status</th>
                    <th>Playback</th>
                    <th>Download</th>
                </tr>
            </thead>
            <tbody id="recording-table-body">
                <?php
                while ($row = $result->fetch_assoc()) {
                    $recordingFileName = $row['filename'] . "-all.mp3";
                    $recordingFilePath = '/var/spool/asterisk/monitorDONE/MP3/' . $recordingFileName;
                    $row['file_exists'] = file_exists($recordingFilePath);
                ?>
                    <tr>
                        <td><?= $row['recording_id'] ?></td>
                        <td><?= $row['date'] ?></td>
                        <td><?= $row['time'] ?></td>
                        <td><?= $row['length'] ?></td>
                        <td><?= $row['lead_id'] ?></td>
                        <td><?= $row['phone_number'] ?></td>
                        <td><?= $row['user'] ?></td>
                        <td><?= $row['status'] ?></td>
                        <td>
                            <?php if ($row['file_exists']): ?>
                                <audio controls>
                                    <source src="<?= $row['location'] ?>" type="audio/mpeg">
                                    Your browser does not support the audio element.
                                </audio>
                            <?php else: ?>
                                Recording N/A
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['file_exists']): ?>
                                <a href="<?= $row['location'] ?>" download>Download</a>
                            <?php else: ?>
                                Download N/A
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <nav aria-label="Page navigation example">
        <ul class="pagination justify-content-center">
            <?php
            $totalPages = ceil($totalRecords / $recordsPerPage);
            for ($i = 1; $i <= $totalPages; $i++) {
                $active = ($i == $page) ? 'active' : '';
                echo "<li class='page-item $active'><a class='page-link' href='?page=$i'>$i</a></li>";
            }
            ?>
        </ul>
    </nav>
</div>

<script>
    $(document).ready(function() {
        function fetchRecordings() {
            $.ajax({
                url: '<?= $_SERVER['PHP_SELF'] ?>',
                type: 'GET',
                data: {
                    page: <?= $page ?>,
                    start_date: $('#start_date').val(),
                    end_date: $('#end_date').val(),
                    start_time: $('#start_time').val(),
                    end_time: $('#end_time').val(),
                    user_filter: $('#user_filter').val(),
                    phone_number_filter: $('#phone_number_filter').val()
                },
                success: function(data) {
                    $('#recording-table-body').html(data);
                }
            });
        }

        $('#phone_number_filter').on('input', function() {
            fetchRecordings();
        });

        $('.pagination a').click(function(e) {
            e.preventDefault();
            var page = $(this).attr('href').split('page=')[1];
            $.ajax({
                url: '<?= $_SERVER['PHP_SELF'] ?>',
                type: 'GET',
                data: {
                    page: page,
                    start_date: $('#start_date').val(),
                    end_date: $('#end_date').val(),
                    start_time: $('#start_time').val(),
                    end_time: $('#end_time').val(),
                    user_filter: $('#user_filter').val(),
                    phone_number_filter: $('#phone_number_filter').val()
                },
                success: function(data) {
                    $('#recording-table-body').html(data);
                }
            });
        });
    });
</script>
</body>
</html>