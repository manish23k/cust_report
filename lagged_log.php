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

<div style="margin: 20px;">
    <?php
    // Set default date and user values or get them from a form submission
    $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : "2023-01-01";
    $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : "2023-12-31";
    $selected_user = isset($_POST['selected_user']) ? $_POST['selected_user'] : "";

    // Get a list of users from the vicidial_user table with user_level <= 7, excluding "VDAD" and "VDCL"
    $user_query = "SELECT user FROM vicidial_users WHERE user_level <= 7 AND user NOT IN ('VDAD', 'VDCL')";
    $user_result = $conn->query($user_query);
    $user_options = "";
    while ($user_row = $user_result->fetch_assoc()) {
        $user_options .= "<option value='{$user_row['user']}'" . ($selected_user == $user_row['user'] ? " selected" : "") . ">{$user_row['user']}</option>";
    }

    // SQL query with date and user filters, and limit
    $sql = "SELECT val.*, vuser.user 
            FROM vicidial_agent_log val
            LEFT JOIN vicidial_users vuser ON val.user = vuser.user
            WHERE val.sub_status='LAGGED' 
            AND val.event_time >= '$start_date' 
            AND val.event_time <= '$end_date'
            " . ($selected_user ? "AND val.user = '$selected_user'" : "") . "
            AND vuser.user_level <= 7  -- Filter out entries where user level is greater than 7
            ORDER BY val.user, val.event_time ASC 
            LIMIT 0, 100"; // Limit to 100 entries per page

    // Execute the query
    $result = $conn->query($sql);

    // Check if the query was successful
    if ($result === false) {
        die("Error executing the query: " . $conn->error);
    }
    ?>

    <form method='post' style='margin-bottom: 20px;'>
        Start Date: <input type='date' name='start_date' value='<?php echo $start_date; ?>' required>
        End Date: <input type='date' name='end_date' value='<?php echo $end_date; ?>' required>
        User: <select name='selected_user'>
            <option value=''>All Users</option>
            <?php echo $user_options; ?>
        </select>
        <input type='submit' value='Filter'>
    </form>

    <?php if ($result->num_rows > 0) : ?>
        <table border='1' style='border-collapse: collapse; width: 100%;'>
            <tr>
                <th>AGENT LOG ID</th>
                <th>USER</th>
                <th>SERVER IP</th>
                <th>EVENT TIME</th>
                <th>LEAD ID</th>
                <th>CAMPAIGN</th>
                <th>STATUS</th>
                <th>USER GROUP</th>
                <th>COMMENTS</th>
                <th>UNIQUE ID</th>
                <!-- Add more columns as needed -->
            </tr>

            <?php while ($row = $result->fetch_assoc()) : ?>
                <tr>
                    <td><?= $row['agent_log_id']; ?></td>
                    <td><?= $row['user']; ?></td>
                    <td><?= $row['server_ip']; ?></td>
                    <td><?= $row['event_time']; ?></td>
                    <td><?= $row['lead_id']; ?></td>
                    <td><?= $row['campaign_id']; ?></td> <!-- Update column name if needed -->
                    <td><?= $row['status']; ?></td>
                    <td><?= $row['user_group']; ?></td>
                    <td><?= $row['comments']; ?></td>
                    <td><?= $row['uniqueid']; ?></td> <!-- Update column name if needed -->
                    <!-- Add more columns as needed -->
                </tr>
            <?php endwhile; ?>

        </table>
    <?php else : ?>
        <p>No entries found.</p>
    <?php endif; ?>
</div>

<?php
// Close the connection
$conn->close();
?>
