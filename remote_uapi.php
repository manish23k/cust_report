<?php

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
// Gather form data
$remote_agent_id = isset($_POST['remote_agent_id']) ? $_POST['remote_agent_id'] : null;
$userStart = isset($_POST['user_start']) ? $_POST['user_start'] : null;
$status = isset($_POST['status']) ? $_POST['status'] : null;

    // Validate user input
    if ($remote_agent_id === null || $userStart === null || $status === null) {
        echo "Invalid input data.";
        exit;
    }

    // Prepare data for the API request
    $requestData = array(
        'remote_agent_id' => $remote_agent_id,
        'user_start' => $userStart,
        'new_status' => $status
    );

    // Send POST request to the API
    $apiUrl = 'https://demo.arrowtelecom.com/report/remote_agent.php'; // Replace with the actual URL where your api.php is hosted

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_VERBOSE, true); // Add this line for more details
    
    $apiResponse = curl_exec($ch);
    
    if ($apiResponse === false) {
        echo 'Curl error: ' . curl_error($ch);
    } else {
        $info = curl_getinfo($ch);
        echo 'HTTP Status Code: ' . $info['http_code'] . '<br>';
        var_dump($apiResponse);
    }
    
    if (is_resource($ch)) {
        curl_close($ch);
    }
// Handle API response
$apiResponse = json_decode($apiResponse, true);

if ($apiResponse === false) {
    echo 'Error making API request: ' . curl_error($ch);
} else {
    // Check if the API response contains an error
    if (isset($apiResponse['status']) && $apiResponse['status'] === 'error') {
        echo 'Error updating status: ' . $apiResponse['message'];
    } else {
        // Display success message or additional processing
        echo 'Status updated successfully!';
    }
}

if (is_resource($ch)) {
    curl_close($ch);
}
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Form</title>
</head>
<body>

<h2>Update User Status</h2>

<form method="post" action="">
    <label for="remote_agent_id">User ID:</label>
    <input type="text" name="remote_agent_id" required>

    <label for="user_start">User Start:</label>
    <input type="text" name="user_start" required>

    <label for="status">Status:</label>
    <select name="status" required>
        <option value="ACTIVE">ACTIVE</option>
        <option value="INACTIVE">INACTIVE</option>
    </select>

    <button type="submit">Update Status</button>
</form>

</body>
</html>
