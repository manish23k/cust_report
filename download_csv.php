<?php
// Include necessary database configuration and connection code here

// Fetch and sanitize the parameters
$download = isset($_GET['download']) ? $_GET['download'] : '';
$campaign = isset($_GET['campaign']) ? $_GET['campaign'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$phone_number = isset($_GET['phone_number']) ? $_GET['phone_number'] : '';
$user = isset($_GET['user']) ? $_GET['user'] : '';

if ($download == 1) {
    // Set the response headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="inbound_call_log.csv"');

    // Open a PHP output stream and set it to write to the browser
    $output = fopen('php://output', 'w');

    // Write the CSV header row
    fputcsv($output, array('List ID', 'Campaign ID', 'Date', 'Length (sec)', 'Status', 'Phone Number', 'User', 'Term Reason'));

    // Construct and execute the SQL query based on the parameters
    $query = "SELECT list_id, campaign_id, call_date, length_in_sec, status, phone_number, user, term_reason FROM vicidial_closer_log WHERE DATE(call_date) BETWEEN '$start_date' AND '$end_date'";

    // Add conditions based on other parameters (campaign, status, phone_number, user)
    if (!empty($campaign)) {
        $query .= " AND campaign_id = '$campaign'";
    }

    if (!empty($status)) {
        $status = trim(mysqli_real_escape_string($conn, $status));
        $query .= " AND status LIKE '%$status%'";
    }

    if (!empty($phone_number)) {
        $phone_number = trim(mysqli_real_escape_string($conn, $phone_number));
        $query .= " AND phone_number LIKE '%$phone_number%'";
    }

    if (!empty($user)) {
        $user = trim(mysqli_real_escape_string($conn, $user));
        $query .= " AND user LIKE '%$user%'";
    }

// Execute the query
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn) . "<br>Query: " . $query);
    
}


    // Write each row of data to the CSV
    while ($row = mysqli_fetch_assoc($result)) {
        // Format date and length_in_sec as needed
        $callDate = strtotime($row['call_date']);
        $date = date("Y-m-d", $callDate);
        $lengthInSeconds = $row['length_in_sec'];
        $lengthFormatted = sprintf('%02d:%02d:%02d', ($lengthInSeconds / 3600), ($lengthInSeconds / 60 % 60), $lengthInSeconds % 60);

        // Write CSV row
        fputcsv($output, array(
            $row['list_id'],
            $row['campaign_id'],
            $date,
            $lengthFormatted,
            $row['status'],
            $row['phone_number'],
            $row['user'],
            $row['term_reason']
        ));
    }

    fclose($output);
    exit();
}
?>
