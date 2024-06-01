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
  <title>Agent Time Export</title>
<style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
    }
    h1 {
      text-align: center;
    }
    form {
      max-width: 800px;
      margin: 0 auto;
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
    }
    .form-group {
      flex-basis: 30%;
      margin-bottom: 10px;
    }
    input[type="date"],
    select,
    button {
      width: 100%;
      padding: 8px;
      border-radius: 5px;
      border: 1px solid #ccc;
      box-sizing: border-box;
    }
    button {
      background-color: #007bff;
      color: #fff;
      cursor: pointer;
    }
    button:hover {
      background-color: #0056b3;
    }
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
  }
  th,
  td {
    border: 1px solid #ccc;
    padding: 8px;
    text-align: left; /* Adjusted to align text to the left within table cells */
  }
  th {
    background-color: #f2f2f2;
  }ound-color: #f2f2f2;
    }
  </style>

  <script>
    function downloadCSV() {
      // Get the table content
      const table = document.querySelector('table');
      const rows = table.querySelectorAll('tr');
      
      // Create CSV content
      let csvContent = 'data:text/csv;charset=utf-8,';
      rows.forEach(row => {
        const rowData = [];
        row.querySelectorAll('th, td').forEach(cell => {
          rowData.push(cell.innerText);
        });
        csvContent += rowData.join(',') + '\n';
      });
      
      // Create a data URI and trigger download
      const encodedUri = encodeURI(csvContent);
      const link = document.createElement('a');
      link.setAttribute('href', encodedUri);
      link.setAttribute('download', 'export.csv');
      document.body.appendChild(link);
      link.click();
    }
  </script>

  
</head>
<body>
  <h1>Agent Stats Export</h1>
  <form method="GET" action="" class="form">
   <div class="form-group">
      <label for="start_date">Select Start Date:</label>
      <input type="date" id="start_date" name="start_date" value="<?= isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d') ?>">
    
      <label for="end_date">Select End Date:</label>
      <input type="date" id="end_date" name="end_date" value="<?= isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d') ?>">
    </div>

 
    <div class="form-group">
    <label for="user">Select User:</label>
    <select id="user" name="user">
        <option value="">All Users</option>
        <?php
        

      // Fetch users from the database using the established connection from config.php
$users_query = "SELECT user FROM vicidial_users";
$users_result = $conn->query($users_query);

        if ($users_result->num_rows > 0) {
            while ($row = $users_result->fetch_assoc()) {
                $user = $row['user'];
                echo "<option value='$user'>$user</option>";
            }
        }

        $conn->close();
        ?>
    </select>
    </div>

    <div class="form-group">
    <label for="campaign">Select Campaign:</label>
    <select id="campaign" name="campaign">
        <option value="">All Campaigns</option>
        <?php
        // Connect to your database and fetch campaigns
        // Replace with your database connection details and campaign fetching logic

        // Fetch campaigns from the database
        $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $campaigns_query = "SELECT campaign_id, campaign_name FROM vicidial_campaigns";
        $campaigns_result = $conn->query($campaigns_query);

        if ($campaigns_result->num_rows > 0) {
            while ($row = $campaigns_result->fetch_assoc()) {
                $campaign_id = $row['campaign_id'];
                $campaign_name = $row['campaign_name'];
                echo "<option value='$campaign_id'>$campaign_name</option>";
            }
        }

        $conn->close();
        ?>
    </select>
</div>
  
 <div class="form-group">
    <button type="submit">Get Data</button>
      </div>

       <div class="form-group">
    <button onclick="downloadCSV()">Download CSV</button>
  </div>
     
  </form>
<?php
// Function to format time duration
function formatTime($time)
{
    $parts = explode(':', $time);
    return sprintf('%02d:%02d:%02d', $parts[0], $parts[1], $parts[2]);
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    $user = $_GET['user'];
    $campaign = $_GET['campaign'];

    // Construct the API URL with selected parameters
    $baseURL = "http://192.168.0.201/vicidial/non_agent_api.php";
    $source = "test";
    $function = "agent_stats_export";
    $time_format = "H";
    $stage = "tab";
    $arrow = "arrow";

   // Constructing the query parameters based on user input
$queryParams = http_build_query([
    'source' => $source,
    'function' => $function,
    'time_format' => $time_format,
    'stage' => $stage,
    'user' => $arrow,
    'pass' => $arrow,
    'agent_user' => $user ? $user : "''",
    'datetime_start' => $start_date . '+00:00:00',
    'datetime_end' => $end_date . '+23:59:59',
    'header' => 'YES'
]);

// Correcting the line to use "echo" instead of "Echo"
// echo "Constructed API URL: " . $baseURL . '?' . $queryParams;
$apiURL = $baseURL . '?' . $queryParams;

    // Fetching data from the API
    $response = file_get_contents($apiURL);

    // Explode response into rows and columns
    $rows = explode("\n", $response);

    // Outputting data in a table
    echo '<table border="1">';
    $headerPrinted = false;
    foreach ($rows as $row) {
        $columns = explode("\t", $row); // Assuming data is tab-separated

        // Print header row
        if (!$headerPrinted) {
            echo '<tr>';
            foreach ($columns as $column) {
                echo '<th>' . trim($column) . '</th>';
            }
            echo '</tr>';
            $headerPrinted = true;
        } else {
            // Print data rows
            echo '<tr>';
            foreach ($columns as $column) {
                echo '<td>' . trim($column) . '</td>';
            }
            echo '</tr>';
        }
    }
    echo '</table>';
}
?>
</body>
</html>
