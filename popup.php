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

// Corrected parameter names
//$phoneNumber = $_REQUEST['phonenumber'];
$phoneNumber = ltrim($_REQUEST['phonenumber'], '0');
$applicationNo = $_REQUEST['applicationNo'];
$agent = $_GET['agent'];
$recId = $_GET['rec_id'];
$lead_id = $_GET['lead_id']; //Get Lead ID From URL

//----ORG--------
//$query = "SELECT * FROM excel_data WHERE mobile_number = '$phoneNumber' AND application_no = '$applicationNo'";
$query = "SELECT * FROM excel_data WHERE application_no = '$applicationNo'";
//$query = "SELECT * FROM excel_data WHERE RIGHT($phoneNumber, 10) = RIGHT('mobile_number', 10) AND application_no = '$applicationNo'";

$result = mysqli_query($conn, $query);

echo "$query";

//exit;

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
} else {
    // Check if the mobile number and application number were found
    if (mysqli_num_rows($result) > 0) {
        // Mobile number and application number found; fetch the data
        while ($row1 = mysqli_fetch_array($result)) {
            $typeOfApplication = $row1['type_of_application'];

            // Logic to redirect based on type_of_application
            switch ($typeOfApplication) {
                case 'Khedut Kharai Certificate':
                    header("Location: khedut_form.php?phonenumber=$phoneNumber&applicationNo=$applicationNo&agent=$agent&rec_id=$recId&lead_id=$lead_id");
                    break;
                case 'LRC-65':
                    header("Location: binkheti_form.php?phonenumber=$phoneNumber&applicationNo=$applicationNo&agent=$agent&rec_id=$recId&lead_id=$lead_id");
                    break;
                    case 'Hayatima Hakkdakhal Mutation':
                      header("Location: hayati_form.php?phonenumber=$phoneNumber&applicationNo=$applicationNo&agent=$agent&rec_id=$recId&lead_id=$lead_id");
                      break;
                      case 'Varsai Mutation':
                        header("Location: varsayi_form.php?phonenumber=$phoneNumber&applicationNo=$applicationNo&agent=$agent&rec_id=$recId&lead_id=$lead_id");
                        break;
                // Add more cases as needed

                default:
                    // Default redirection or error handling
                    header("Location: default_page.php");
                    break;
            }
        }
    } else {
        // Mobile number and application number not found; show a popup or message
        echo '<script>alert("Mobile number and application number not found in the database.");</script>';
    }
}

mysqli_close($conn);
?>
