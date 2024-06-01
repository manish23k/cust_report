<?php
session_start(); // Start the session on each page

include "config.php"; // Include the database configuration
include "header.php";

// Ensure the user is logged in, or redirect them to the login page
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit;
}


// Check if the user ID is provided in the URL
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Delete user from the database
    $query = "DELETE FROM vicidial_users WHERE user_id = $user_id";
    mysqli_query($conn, $query);

    // Redirect to the user list page
    header("Location: list_users.php");
    exit();
} else {
    // Redirect to the user list page if user ID is not provided
    header("Location: list_users.php");
    exit();
}
?>
