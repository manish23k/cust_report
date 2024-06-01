<?php
session_start(); // Start the session on each page

include "config.php"; // Include the database configuration
include "header.php";

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
    // You may display an error message or set default values
    $start_time = '00:00:00'; // Set default values if the format is invalid
    $end_time = '23:59:59';
}

// Handle user filter
$userFilter = isset($_GET['user_filter']) ? $mysqli->real_escape_string($_GET['user_filter']) : '';

// Handle phone number filter
$phoneNumberFilter = isset($_GET['phone_number_filter']) ? $mysqli->real_escape_string($_GET['phone_number_filter']) : '';

// Fetch user data for the dropdown
$userOptions = '';
$userFilter = isset($_GET['user_filter']) ? $_GET['user_filter'] : '';

$sql = "SELECT DISTINCT user FROM vicidial_users WHERE user_level <= 6 AND user NOT IN ('VDAD', 'VDCL')";
$userResult = $conn->query($sql);
if ($userResult) {
    while ($row = $userResult->fetch_assoc()) {
        $selected = ($userFilter == $row['user']) ? 'selected' : '';
        $userOptions .= "<option value='{$row['user']}' $selected>{$row['user']}</option>";
    }
}

// Define the date and time range for the report
if (empty($startDate) || empty($endDate)) {
    $start_datetime = date('Y-m-d 00:00:00');
    $end_datetime = date('Y-m-d 23:59:59');
} else {
    $start_datetime = $startDate . ' ' . $start_time;
    $end_datetime = $endDate . ' ' . $end_time;
}

// Calculate the total number of records
$totalRecordsQuery = "SELECT COUNT(*) AS total FROM recording_log r
LEFT JOIN vicidial_closer_log c ON r.lead_id = c.lead_id
LEFT JOIN vicidial_log v ON r.lead_id = v.lead_id
WHERE r.start_time >= '$start_datetime' AND r.start_time <= '$end_datetime'";



// Add user filter
if (!empty($userFilter)) {
    $totalRecordsQuery .= " AND r.user = '$userFilter'";
}

// Add phone number filter with LIKE search
if (!empty($phoneNumberFilter)) {
    $totalRecordsQuery .= " AND c.phone_number LIKE '%$phoneNumberFilter%'";
}

// Execute the query
$totalRecordsResult = $conn->query($totalRecordsQuery);

// Check for query execution errors
if (!$totalRecordsResult) {
    die('Total Records Query failed: ' . $conn->error);
}

// Fetch the total number of records
$totalRecords = $totalRecordsResult->fetch_assoc()['total'];

//echo "$totalRecordsQuery";

// SQL query to fetch recording log data with filter and limit to 50 records
$sql = "SELECT 
r.recording_id, 
DATE(r.start_time) AS date, 
TIME(r.start_time) AS time, 
r.length_in_sec, 
r.lead_id, 
r.user, 
COALESCE(c.phone_number, v.phone_number) AS phone_number,
COALESCE(c.status, v.status) AS status,
r.filename,  
r.location  
FROM recording_log r
LEFT JOIN vicidial_closer_log c ON r.lead_id = c.lead_id
LEFT JOIN vicidial_log v ON r.lead_id = v.lead_id
WHERE r.start_time >= '$start_datetime' AND r.start_time <= '$end_datetime'";

// Add user filter
if (!empty($userFilter)) {
    $sql .= " AND r.user = '$userFilter'";
}

// Add phone number filter with LIKE search
if (!empty($phoneNumberFilter)) {
    $sql .= " AND c.phone_number LIKE '%$phoneNumberFilter%'";
}


// Add LIMIT and OFFSET for pagination
$sql .= " ORDER BY r.start_time DESC LIMIT $recordsPerPage OFFSET $offset"; // Limit and offset

$result = $conn->query($sql);

// echo "$sql";
// Check for query execution errors
if (!$result) {
    die('Query failed: ' . $conn->error);
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Recording Log Report</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f3f3f3;
        }

        .navbar {
            background-color: #007bff;
            padding: 1rem 0;
        }

        .navbar-brand,
        .nav-link {
            color: #ffffff !important;
            font-weight: bold;
        }

        .navbar-toggler-icon {
            background-color: #ffffff;
        }

        .navbar-nav {
            margin-left: auto;
        }

        .nav-item {
            margin-right: 15px;
        }

        .container {
            margin-top: 20px;
        }

        /* Adjusted tab colors for better visibility */
        .nav-item.active a {
            background-color: #ffffff;
            color: #007bff !important;
        }

        /* For dropdowns */
        .dropdown-menu {
            transition: visibility 0.15s, opacity 0.15s linear;
        }

        .dropdown-menu.show {
            visibility: visible;
            opacity: 1;
        }

        /* NEW ADDED FOR RIGHT-SIDE DROPDOWN */
        .dropdown-submenu {
            position: relative;
        }

        .dropdown-submenu .dropdown-menu {
            top: 0;
            left: 100%;
            margin-top: -1px;
        }

        .dropdown-submenu:hover .dropdown-menu {
            display: block;
        }

        .dropdown-submenu:hover>a:after {
            border-left-color: #fff;
        }

        .dropdown-submenu.pull-left {
            float: none;
        }

        .dropdown-submenu.pull-left .dropdown-menu {
            left: -100%;
            margin-left: 10px;
            border-radius: 6px;
        }
    </style>

        <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voicecatch Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>


<body>
 



    
    <div class="container mt-4 text-center">
        <h1 class="display-4">Recording Log Report</h1>
        <form method="get"
            action="<?= $_SERVER['PHP_SELF'] ?>" class="mb-4">
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
    <label for="start_time">From Time:</label>
    <input type="text" name="start_time" id="start_time" class="form-control" placeholder="00:00:00"
        value="<?php echo isset($_GET['start_time']) ? $_GET['start_time'] : '00:00:00'; ?>">
</div>
<div class="col-md-3 mb-3">
    <label for="end_time">To Time:</label>
    <input type="text" name="end_time" id="end_time" class="form-control" placeholder="23:59:59"
        value="<?php echo isset($_GET['end_time']) ? $_GET['end_time'] : '23:59:59'; ?>">
</div>

                <div class="col-md-3 mb-3">
                    <label for="user_filter">User Filter:</label>
                    <select name="user_filter" id="user_filter" class="form-select">
                        <option value="">Select User</option>
                        <?= $userOptions ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="phone_number_filter">Phone Number Filter:</label>
                    <input type="text" name="phone_number_filter" id="phone_number_filter"
                        value="<?= $phoneNumberFilter ?>" class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="search" class="invisible">Search Button</label>
                    <input type="submit" name="search" value="Search" class="btn btn-primary form-control">
                </div>
            </div>
        </form>
        <div class="row mt-2">
    <div class="col-md-12">
        <p style="font-weight: bold; font-size: xx-large;">Total Records: <?= $totalRecords ?></p>
    </div>
</div>


        <div class="table-responsive">
        <table class="table table-bordered table-striped mt-4">
                <thead class="thead-dark">
                    <tr>
                        <th>Recording ID</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Length (sec)</th>
                        <th>Lead ID</th>
                        <th>Phone Number</th>
                        <th>User</th>
                        <th>Status</th>
                        <th>Location</th>
                        <th>Download</th>
                    </tr>
                </thead>
                <tbody>
              <tbody>
    <?php
    // Loop through the query results and display each row with specific columns

    // Loop through the query results and include filename existence information


while ($row = $result->fetch_assoc()) {
    $recordingFileName = $row['filename'] . "-all.mp3"; // Append "-all.mp3" to the filename
    $recordingFilePath = '/var/spool/asterisk/monitorDONE/MP3/' . $recordingFileName;
    $row['file_exists'] = file_exists($recordingFilePath);

    echo "<tr>";
    echo "<td>" . $row['recording_id'] . "</td>";
    echo "<td>" . $row['date'] . "</td>";
    echo "<td>" . $row['time'] . "</td>";
    echo "<td>" . $row['length_in_sec'] . "</td>";
    echo "<td>" . $row['lead_id'] . "</td>";
    echo "<td>" . $row['phone_number'] . "</td>";
    echo "<td>" . $row['user'] . "</td>";
    echo "<td>" . $row['status'] . "</td>";
    echo "<td>";

    // Check if the recording file exists
    if ($row['file_exists']) {
        // Display the audio player if the file exists
        echo "<audio controls>";
        echo "<source src='{$row['location']}' type='audio/mpeg'>";
        echo "Your browser does not support the audio element.";
        echo "</audio>";
    } else {
        // Show a message if the file does not exist
        echo "Recording N/A";
    }

    echo "</td>"; // Moved Location to last position
    echo "<td>";

    // Check if the recording file exists
    if ($row['file_exists']) {
        // Display the download button with the same link as the audio player
        echo "<a href='{$row['location']}' download>Download</a>";
    } else {
        // Show a message if the file does not exist
        echo "Download N/A";
    }

    echo "</td>";
    echo "</tr>";
}



                    ?>
                </tbody>
            </table>
        </div>


        <div class="row mt-4">
            <div class="col-md-6">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= ($page - 1) ?>&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>&user_filter=<?= $userFilter ?>&phone_number_filter=<?= $phoneNumberFilter ?>"
                        class="btn btn-primary">Previous</a>
                <?php else: ?>
                    <button class="btn btn-primary disabled" disabled>Previous</button>
                <?php endif; ?>
            </div>
            <div class="col-md-6 text-end">
                <?php if (($page * $recordsPerPage) < $totalRecords): ?>
                    <a href="?page=<?= ($page + 1) ?>&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>&user_filter=<?= $userFilter ?>&phone_number_filter=<?= $phoneNumberFilter ?>"
                        class="btn btn-primary">Next</a>
                <?php else: ?>
                    <button class="btn btn-primary disabled" disabled>Next</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
    <script>
        document.querySelectorAll('.nav-item.dropdown').forEach(function(dropdown) {
            dropdown.addEventListener('mouseenter', function() {
                showDropdown(this);
            });

            dropdown.addEventListener('mouseleave', function() {
                hideDropdown(this);
            });

            var dropdownMenu = dropdown.querySelector('.dropdown-menu');

            dropdownMenu.addEventListener('mouseenter', function() {
                showDropdown(dropdown);
            });

            dropdownMenu.addEventListener('mouseleave', function() {
                hideDropdown(dropdown);
            });
        });

        function showDropdown(element) {
            var dropdownMenu = element.querySelector('.dropdown-menu');
            if (dropdownMenu) {
                dropdownMenu.classList.add('show');
            }
        }

        function hideDropdown(element) {
            var dropdownMenu = element.querySelector('.dropdown-menu');
            if (dropdownMenu) {
                dropdownMenu.classList.remove('show');
            }
        }
    </script>
</body>

</html>
