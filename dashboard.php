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


<!DOCTYPE html>
<html>
<head>
    <title>System Summary</title>
    <style>
        .dashboard-table {
            width: 100%;
            border-collapse: collapse;
        }

        .dashboard-table th, .dashboard-table td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        .dashboard-table th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<h1>System Summary Under Construction</h1>

<table class="dashboard-table">
    <tr>
        <th>Total Stats for Today</th>
        <th>Total Calls</th>
    </tr>
    <tr>
        <td>Total Inbound Calls</td>
        <td>0</td>
    </tr>
    <tr>
        <td>Total Outbound Calls</td>
        <td>2237</td>
    </tr>
</table>

<h2>Agents Logged In</h2>
<p>6</p>

</body>
</html>