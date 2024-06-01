

<?php


// To fetch data from all table 
// https://192.168.0.201/report/arrow_api.php?data=all&sdate=2024-05-01&edate=2024-05-14

// To fetch data from single table 
// https://192.168.0.201/report/arrow_api.php?data=varsayi

// To fetch data from varsayi_form_data between two dates: 
// https://192.168.0.201/report/arrow_api.php?data=varsayi&sdate=2024-05-01&edate=2024-05-14
// To fetch data from all tables between two dates: 
// https://192.168.0.201/report/arrow_api.php?data=all&sdate=2024-05-01&edate=2024-05-14
// To fetch data from varsayi_form_data starting from a specific date: 
// https://192.168.0.201/report/arrow_api.php?data=varsayi&sdate=2024-05-01
// To fetch data from varsayi_form_data up to a specific date:
//  https://192.168.0.201/report/arrow_api.php?data=varsayi&edate=2024-05-14
 

include "config.php";

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define API response format
$response = array();

// Check if the HTTP method is GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if the data parameter is provided in the request
    if (isset($_GET['data'])) {
        $data = $conn->real_escape_string($_GET['data']);
        $date_condition = "";

        // Check if the sdate and edate parameters are provided in the request
        if (isset($_GET['sdate']) && isset($_GET['edate'])) {
            $sdate = $conn->real_escape_string($_GET['sdate']);
            $edate = $conn->real_escape_string($_GET['edate']);
            // Validate date format (YYYY-MM-DD)
            if (preg_match("/^\d{4}-\d{2}-\d{2}$/", $sdate) && preg_match("/^\d{4}-\d{2}-\d{2}$/", $edate)) {
                $date_condition = "WHERE DATE(entry_datetime) BETWEEN '$sdate' AND '$edate'";
            } else {
                $response['message'] = "Invalid date format. Please use YYYY-MM-DD for both sdate and edate.";
                echo json_encode($response);
                exit();
            }
        } elseif (isset($_GET['sdate'])) {
            $sdate = $conn->real_escape_string($_GET['sdate']);
            if (preg_match("/^\d{4}-\d{2}-\d{2}$/", $sdate)) {
                $date_condition = "WHERE DATE(entry_datetime) >= '$sdate'";
            } else {
                $response['message'] = "Invalid start date format. Please use YYYY-MM-DD.";
                echo json_encode($response);
                exit();
            }
        } elseif (isset($_GET['edate'])) {
            $edate = $conn->real_escape_string($_GET['edate']);
            if (preg_match("/^\d{4}-\d{2}-\d{2}$/", $edate)) {
                $date_condition = "WHERE DATE(entry_datetime) <= '$edate'";
            } else {
                $response['message'] = "Invalid end date format. Please use YYYY-MM-DD.";
                echo json_encode($response);
                exit();
            }
        }

        // Determine the SQL query based on the data parameter
        if ($data == 'all') {
            // Fetch all data from all tables
            $tables = ['binkheti_form_data', 'hayati_form_data', 'khedut_form_data', 'varsayi_form_data'];
            foreach ($tables as $table) {
                $sql = "SELECT * FROM $table $date_condition";
                $result = $conn->query($sql);
                $response[$table] = ($result && $result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : array();
            }
        } else {
            // Fetch data from the specified table only
            $table_name = $data . "_form_data";
            $sql = "SELECT * FROM $table_name $date_condition";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                $response[$table_name] = $result->fetch_all(MYSQLI_ASSOC);
            } else {
                $response['message'] = "No records found for the table: $table_name";
            }
        }
    } else {
        // If no data parameter is provided, return an error message
        $response['message'] = "Please provide a data parameter";
    }

    // Close database connection
    $conn->close();

    // Set response headers
    header('Content-Type: application/json');
    
    // Send JSON encoded response
    echo json_encode($response);
} else {
    // If the request method is not GET, return an error message
    http_response_code(405);
    echo json_encode(array("message" => "Method Not Allowed"));
}
?>
