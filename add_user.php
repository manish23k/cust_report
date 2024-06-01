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
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Copy User Form</title>
</head>
<body>
  <h1>Copy User Form</h1>
  <form id="copyUserForm">
    <label for="agent_user">Agent User:</label>
    <input type="text" id="agent_user" name="agent_user" required><br>

    <label for="agent_pass">Agent Password:</label>
    <input type="password" id="agent_pass" name="agent_pass" required><br>

    <label for="agent_full_name">Agent Full Name:</label>
    <input type="text" id="agent_full_name" name="agent_full_name" required><br>

    <input type="hidden" id="source_user" name="source_user" value="copy">

    <input type="submit" value="Copy User">
  </form>

  <div id="responseMessage"></div>

  <script>
  document.getElementById('copyUserForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const agentUser = document.getElementById('agent_user').value;
    const agentPass = document.getElementById('agent_pass').value;
    const agentFullName = document.getElementById('agent_full_name').value;
    const sourceUser = document.getElementById('source_user').value;

    const apiUrl = 'http://192.168.0.201/vicidial/non_agent_api.php' +
      '?source=test' +
      '&function=copy_user' +
      '&user=admin' + // Assuming this is the user ID required by the API
      '&pass=admin' + // Assuming this is the password required by the API
      `&agent_user=${agentUser}` +
      `&agent_pass=${agentPass}` +
      `&agent_full_name=${encodeURIComponent(agentFullName)}` +
      '&source_user=copy'; // Assuming the source user is always 'copy'

    fetch(apiUrl)
      .then(response => response.text())
      .then(data => {
        if (data.includes('SUCCESS')) {
          window.alert('User has been added successfully!');
          window.location.href = '/report/list_users.php'; // Redirect upon success
        } else {
          window.alert('Error: ' + data); // Show error message in alert
        }
      })
      .catch(error => {
        console.error('Error:', error);
        window.alert('An error occurred while processing your request.');
      });
  });
</script>

  <style>
    .error {
      color: red;
    }

    .success {
      color: green;
    }
  </style>
</body>
</html>
