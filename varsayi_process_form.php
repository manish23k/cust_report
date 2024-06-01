<?php
include "config.php"; // Include your database configuration file

// Create a database connection
$conn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

// Check for connection errors
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Retrieve the values from the form

$agent = isset($_POST['agent']) ? $_POST['agent'] : null;
$phonenumber = isset($_POST['phonenumber']) ? $_POST['phonenumber'] : null;
$lead_id = isset($_POST['lead_id']) ? $_POST['lead_id'] : null;
$rec_id = isset($_POST['rec_id']) ? $_POST['rec_id'] : null;
$type_of_application = isset($_POST['type_of_application']) ? $_POST['type_of_application'] : null;
$r2 = isset($_POST['application_no']) ? $_POST['application_no'] : null;
$r9 = isset($_POST['application_date']) ? $_POST['application_date'] : null;
$r10 = isset($_POST['disposal_date']) ? $_POST['disposal_date'] : null;
$r7 = isset($_POST['applicant_name']) ? $_POST['applicant_name'] : null;
// Check if the radio button "સંબંઘિ" is selected
// if (isset($_POST['relative_option']) && $_POST['relative_option'] === 'સંબંઘિ') {
//     // Fetch the value from the text box
//     $relativename = isset($_POST['relative_name']) ? $_POST['relative_name'] : null;
// } else {
//     $relativename = 'જાતે'; // Set to 'જાતે' if the radio button is not selected
// }
$r5 = isset($_POST['applicant_location']) ? $_POST['applicant_location'] : null;
$r4 = isset($_POST['applicant_taluko']) ? $_POST['applicant_taluko'] : null;
$r3 = isset($_POST['applicant_jilla']) ? $_POST['applicant_jilla'] : null;

$varsayiApplication2 = isset($_POST['varsayi_application'][2]) ? $_POST['varsayi_application'][2] : null;

// Check if "ના" is selected in $binkhetiApplication2
if ($varsayiApplication2 === 'ના') {
    $relativename = ''; // Set to blank if "ના" is selected
} else {
    // Check if the radio button "સંબંઘિ" is selected
    if (isset($_POST['relative_option']) && $_POST['relative_option'] === 'સંબંઘિ') {
        // Fetch the value from the text box
        $relativename = isset($_POST['relative_name']) ? $_POST['relative_name'] : null;
    } else {
        $relativename = 'જાતે'; // Set to 'જાતે' if the radio button is not selected
    }
}
$varsayiApplication3 = isset($_POST['varsayi_application'][3]) ? $_POST['varsayi_application'][3] : null;
$varsayiApplication3_1 = isset($_POST['varsayi_application']['3.1']) ? $_POST['varsayi_application']['3.1'] : null;
$varsayiDifficulty4 = isset($_POST['varsayi_multiple_selection_options_4']) ? $_POST['varsayi_multiple_selection_options_4'] : null;
$varsayiMultipleSelection4 = isset($_POST['varsayi_multiple_selection'][4]) ? $_POST['varsayi_multiple_selection'][4] : [];
$varsayiApplication5 = isset($_POST['varsayi_application_options_5']) ? $_POST['varsayi_application_options_5'] : null;
$varsayiApplicationReason5 = isset($_POST['varsayi_application_reason'][5]) ? $_POST['varsayi_application_reason'][5] : [];
$varsayiApplicationResponse6 = isset($_POST['varsayi_application_response']) ? $_POST['varsayi_application_response'] : null;
$varsayiOfficeSelection6 = isset($_POST['varsayi_office_selection'][6]) ? $_POST['varsayi_office_selection'][6] : [];
$nikalMangani = isset($_POST['varsayi_nikal_mangani']) ? $_POST['varsayi_nikal_mangani'] : null;
$nikalManganiBy7 = isset($_POST['varsayi_nikal_mangani_by'][7]) ? $_POST['varsayi_nikal_mangani_by'][7] : [];
$serviceconfirmation = isset($_POST['varsayi_service_confirmation']) ? $_POST['varsayi_service_confirmation'] : null;
$varsayisatisfied = isset($_POST['satisfied']) ? $_POST['satisfied'] : [];
$starRating = isset($_POST['starRating']) ? $_POST['starRating'] : null;
$additionalComments = isset($_POST['myInput']) ? $_POST['myInput'] : null;

// Get the current date and time
$currentDateTime = date('Y-m-d H:i:s');


 

// Print the values for verification (you can remove this in the final version)
echo "Agent: $agent<br>";
echo "phonenumber: $phonenumber<br>";
echo "lead_id: $lead_id<br>";
echo "rec_id: $rec_id<br>";
echo "type_of_application: $type_of_application <br>";
echo "Application No: $r2<br>";
echo "Application Date: $r9<br>";
echo "Disposal Date: $r10<br>";
echo "Applicant Name: $r7<br>";
echo "Applicant Location: $r5<br>";
echo "Applicant Taluko: $r4<br>";
echo "Applicant Jilla: $r3<br>";

echo "Varsayi Application 2: $varsayiApplication2<br>";
echo "Varsayi Application 3: $varsayiApplication3<br>";
echo "Varsayi Application 3.1: $varsayiApplication3_1<br>";
echo "Varsayi Difficulty4: $varsayiDifficulty4<br>";
echo "Varsayi Multiple Selection4: " . implode(', ', $varsayiMultipleSelection4) . "<br>"; // Convert array to comma-separated string
echo "Varsayi Application 5: $varsayiApplication5<br>";
echo "Varsayi Application Reason 5: " . implode(', ', $varsayiApplicationReason5) . "<br>"; // Convert array to comma-separated string
echo "Varsayi Application Response 6: $varsayiApplicationResponse6<br>";
echo "Varsayi Office Selection 6: " . implode(', ', $varsayiOfficeSelection6) . "<br>"; // Convert array to comma-separated string
echo "Nikal Mangani: $nikalMangani<br>";
echo "Nikal Mangani By 7: " . implode(', ', $nikalManganiBy7) . "<br>"; // Convert array to comma-separated string 
echo "Service Confirmation: $serviceconfirmation<br>";


echo "Star Rating: $starRating<br>";
echo "Additional Comments: $additionalComments<br>";



echo '<pre>';
print_r($_POST);
echo '</pre>';

//exit;

// Convert array values to strings
$varsayiMultipleSelection4String = implode(', ', $varsayiMultipleSelection4);
$varsayiApplicationReason5String = implode(', ', $varsayiApplicationReason5);
$varsayiOfficeSelection6String = implode(', ', $varsayiOfficeSelection6);
$nikalManganiBy7String = implode(', ', $nikalManganiBy7);
$application8String = implode(', ', $application8);
$phoneVerificationOption8_1String = implode(', ', $phoneVerificationOption8_1);


// Insert data into the database
$sql = "INSERT INTO varsayi_form_data (
    agent,
    phonenumber,
    lead_id,
    rec_id,
    type_of_application,
    application_no,
    application_date,
    disposal_date,
    applicant_name,
    varsayi_relativename,
    applicant_location,
    applicant_taluko,
    applicant_jilla,
    varsayi_application_2,
    varsayi_application_3,
    varsayi_application_3_1,
    varsayi_difficulty_4,
    varsayi_multiple_selection_4,
    varsayi_application_5,
    varsayi_application_reason_5,
    varsayi_application_response_6,
    varsayi_office_selection_6,
    varsayi_nikal_mangani,
    varsayi_nikal_mangani_by_7,
    varsayi_service_confirmation,
    varsayi_satisfied,
    star_rating,
    suggestion,
    entry_datetime
) VALUES (
    '$agent', '$phonenumber', '$lead_id', '$rec_id', '$type_of_application', '$r2', '$r9',
    '$r10', '$r7', '$relativename', '$r5', '$r4', '$r3', '$varsayiApplication2', '$varsayiApplication3',
    '$varsayiApplication3_1', '$varsayiDifficulty4', '$varsayiMultipleSelection4String',
    '$varsayiApplication5', '$varsayiApplicationReason5String',
    '$varsayiApplicationResponse6', '$varsayiOfficeSelection6String', '$nikalMangani',
    '$nikalManganiBy7String', '$serviceconfirmation', '$varsayisatisfied',
    '$starRating', '$additionalComments', '$currentDateTime'
)";


if (mysqli_query($conn, $sql)) {
    echo "Record inserted successfully";
} else {
    echo "Error inserting record: " . mysqli_error($conn);
}




// Close the database connection
mysqli_close($conn);
?>
