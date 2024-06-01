<?php

include "config.php";



// Create a database connection
$conn = mysqli_connect($dbHost, $dbUser, $dbPass);

// Check for connection errors
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Select the database
if (!mysqli_select_db($conn, $dbName)) {
    die("Database selection failed: " . mysqli_error($conn));
}

// Check if form is submitted for status toggle
if(isset($_POST['toggle_status']) && isset($_POST['remote_agent_id'])) {
    $remote_agent_id = $_POST['remote_agent_id'];
    $newStatus = ($_POST['current_status'] == 'ACTIVE') ? 'INACTIVE' : 'ACTIVE';

// Update the status in the database
$sql = "UPDATE vicidial_remote_agents SET status = '$newStatus' WHERE remote_agent_id = $remote_agent_id";
$result = $conn->query($sql);

if ($result) {
    $response = array('status' => 'success', 'message' => 'Status updated successfully');
} else {
    $response = array('status' => 'error', 'message' => 'Error updating status: ' . $conn->error);
    // Add error logging here
}

$apiResponse = curl_exec($ch);
var_dump($apiResponse); // Add this line

echo "SQL Query: " . $sql . "<br>";

}

// Fetch all users and their statuses
$sql = "SELECT remote_agent_id, user_start, status FROM vicidial_remote_agents";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Display a table with users and their statuses
    echo '<table border="1">';
    echo '<tr><th>Remote Agent ID</th><th>User Start</th><th>Status</th><th>Action</th></tr>';
    while($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $row["remote_agent_id"] . '</td>';
        echo '<td>' . $row["user_start"] . '</td>';
        echo '<td>' . $row["status"] . '</td>';

        // Display the form for status toggle
        echo '<td>';
        echo '<form method="post" action="">';
        echo '<input type="hidden" name="remote_agent_id" value="'.$row["remote_agent_id"].'">';
        echo '<input type="hidden" name="current_status" value="'.$row["status"].'">';
        echo '<input type="submit" name="toggle_status" value="Toggle Status">';
        echo '</form>';
        echo '</td>';

        echo '</tr>';
    }
    echo '</table>';
} else {
    echo "No results found.";
}


// Close the database connection
$conn->close();

?>