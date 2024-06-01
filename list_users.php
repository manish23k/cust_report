<?php
session_start(); // Start the session on each page

include "config.php"; // Include the database configuration
include "header.php";

// Ensure the user is logged in, or redirect them to the login page
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit;
}

// Modify the SQL query to exclude users with user_level 9
$query = "SELECT * FROM vicidial_users WHERE user_level <> 9 AND user NOT IN ('VDCL', 'VDAD')";
$result = mysqli_query($conn, $query);

//echo "$query";
echo "<a href='add_user.php'>Add user</a>";

echo "<div class=table-responsive>";
echo "<table class=table table-bordered table-striped>";
echo "    <thead class=thead-dark>";
// Display users in a table
echo "<h2>User List</h2>";
echo "<table border='1'>";
echo "<tr>";
echo "<th>User ID</th>";
echo "<th>Username</th>";
echo "<th>Full Name</th>";
echo "<th>LEVEL</th>";
echo "<th>GROUP</th>";
echo "<th>ACTIVE</th>";
echo "<th>Action</th>";
echo "</tr>";

echo "</thead>";
echo "<tbody>";
echo "</div>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>{$row['user_id']}</td>";
    echo "<td>{$row['user']}</td>";
    echo "<td>{$row['full_name']}</td>";
    echo "<td>{$row['user_level']}</td>";
    echo "<td>{$row['user_group']}</td>";
    echo "<td>{$row['active']}</td>";
    echo "<td><a href='edit_user.php?user_id={$row['user_id']}'>Edit</a> ";
    //| <a href='delete_user.php?user_id={$row['user_id']}'>Delete</a></td>
    echo "</tr>";
}

echo "</table>";

// Close the database connection
mysqli_close($conn);
?>
