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
$r5 = isset($_POST['applicant_location']) ? $_POST['applicant_location'] : null;
$r4 = isset($_POST['applicant_taluko']) ? $_POST['applicant_taluko'] : null;
$r3 = isset($_POST['applicant_jilla']) ? $_POST['applicant_jilla'] : null;


$binkhetiApplication2 = isset($_POST['binkheti_application'][2]) ? $_POST['binkheti_application'][2] : null;


// Check if "ના" is selected in $binkhetiApplication2
if ($binkhetiApplication2 === 'ના') {
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

$binkhetiApplication3 = isset($_POST['binkheti_application'][3]) ? $_POST['binkheti_application'][3] : null;
$binkhetiApplication3_1 = isset($_POST['binkheti_application']['3.1']) ? $_POST['binkheti_application']['3.1'] : null;
$binkhetiDifficulty4 = isset($_POST['binkheti_multiple_selection_options_4']) ? $_POST['binkheti_multiple_selection_options_4'] : null;
$binkhetiMultipleSelection4 = isset($_POST['binkheti_multiple_selection'][4]) ? $_POST['binkheti_multiple_selection'][4] : [];
$binkhetiApplication5 = isset($_POST['binkheti_application_options_5']) ? $_POST['binkheti_application_options_5'] : null;
$binkhetiApplicationReason5 = isset($_POST['binkheti_application_reason'][5]) ? $_POST['binkheti_application_reason'][5] : [];
$binkhetiApplicationResponse6 = isset($_POST['binkheti_application_response']) ? $_POST['binkheti_application_response'] : null;
$binkhetiOfficeSelection6 = isset($_POST['binkheti_office_selection'][6]) ? $_POST['binkheti_office_selection'][6] : [];
$nikalMangani = isset($_POST['nikal_mangani']) ? $_POST['nikal_mangani'] : null;
$nikalManganiBy7 = isset($_POST['nikal_mangani_by'][7]) ? $_POST['nikal_mangani_by'][7] : [];
$difficulty8 = isset($_POST['binkheti_difficulty_8']) ? $_POST['binkheti_difficulty_8'] : null;
$application8 = isset($_POST['binkheti_application'][8]) ? $_POST['binkheti_application'][8] : [];
$phoneVerification8_1 = isset($_POST['binkheti_application_main_8_1']) ? $_POST['binkheti_application_main_8_1'] : null;
$phoneVerificationOption8_1 = isset($_POST['binkheti_application_options_8_1']) ? $_POST['binkheti_application_options_8_1'] : [];
$binkhetisatisfied = isset($_POST['satisfied']) ? $_POST['satisfied'] : [];
$starRating = isset($_POST['starRating']) ? $_POST['starRating'] : null;
$suggestion = isset($_POST['myInput']) ? $_POST['myInput'] : null;

// Get the current date and time
$currentDateTime = date('Y-m-d H:i:s');
 

//Print the values for verification (you can remove this in the final version)
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

echo "binkheti Application 2: $binkhetiApplication2<br>";
echo "binkheti Application 3: $binkhetiApplication3<br>";
echo "binkheti Application 3.1: $binkhetiApplication3_1<br>";
echo "binkheti Difficulty4: $binkhetiDifficulty4<br>";
echo "binkheti Multiple Selection4: " . implode(', ', $binkhetiMultipleSelection4) . "<br>"; // Convert array to comma-separated string
echo "binkheti Application 5: $binkhetiApplication5<br>";
echo "binkheti Application Reason 5: " . implode(', ', $binkhetiApplicationReason5) . "<br>"; // Convert array to comma-separated string
echo "binkheti Application Response 6: $binkhetiApplicationResponse6<br>";
echo "binkheti Office Selection 6: " . implode(', ', $binkhetiOfficeSelection6) . "<br>"; // Convert array to comma-separated string
echo "Nikal Mangani: $nikalMangani<br>";
echo "Nikal Mangani By 7: " . implode(', ', $nikalManganiBy7) . "<br>"; // Convert array to comma-separated string 
echo "Difficulty 8: $difficulty8<br>";
echo "Application 8: " . implode(', ', $application8String) . "<br>"; // Convert array to comma-separated string

echo "Phone Verification: $phoneVerification8_1<br>";
echo "Phone Verification option : " . implode(', ', $phoneVerificationOption8_1) . "<br>";

echo "Star Rating: $starRating<br>";
echo "Additional Comments: $additionalComments<br>";
echo "Current Date/Time: $currentDateTime<br>";


echo '<pre>';
print_r($_POST);
echo '</pre>';

//exit;\

// Convert array values to strings
$binkhetiMultipleSelection4String = implode(', ', $binkhetiMultipleSelection4);
$binkhetiApplicationReason5String = implode(', ', $binkhetiApplicationReason5);
$binkhetiOfficeSelection6String = implode(', ', $binkhetiOfficeSelection6);
$nikalManganiBy7String = implode(', ', $nikalManganiBy7);
$application8String = implode(', ', $application8);
$phoneVerificationOption8_1String = implode(', ', $phoneVerificationOption8_1);


// Insert data into the database
$sql = "INSERT INTO binkheti_form_data (
    agent, phonenumber, lead_id, rec_id, type_of_application, application_no, application_date,
    disposal_date, applicant_name, binkheti_relativename, applicant_location, applicant_taluko, applicant_jilla,
    binkheti_application_2, binkheti_application_3, binkheti_application_3_1, binkheti_difficulty_4,
    binkheti_multiple_selection_4, binkheti_application_5, binkheti_application_reason_5,
    binkheti_application_response_6, binkheti_office_selection_6, nikal_mangani, nikal_mangani_by_7,
    binkheti_difficulty_8, binkheti_application_8, phone_verification_8_1, phone_verification_option_8_1,
    binkheti_satisfied, star_rating, suggestion, entry_datetime
) VALUES (
    '$agent', '$phonenumber', '$lead_id', '$rec_id', '$type_of_application', '$r2', '$r9',
    '$r10', '$r7', '$relativename', '$r5', '$r4', '$r3', '$binkhetiApplication2', '$binkhetiApplication3',
    '$binkhetiApplication3_1', '$binkhetiDifficulty4', '$binkhetiMultipleSelection4String',
    '$binkhetiApplication5', '$binkhetiApplicationReason5String',
    '$binkhetiApplicationResponse6', '$binkhetiOfficeSelection6String', '$nikalMangani',
    '$nikalManganiBy7String', '$difficulty8', '$application8String',
    '$phoneVerification8_1', '$phoneVerificationOption8_1String', '$binkhetisatisfied', '$starRating', '$suggestion', '$currentDateTime'
)";


 echo "$sql";
 
if (mysqli_query($conn, $sql)) {
  echo "Record inserted successfully";
} else {
    echo "Error inserting record: " . mysqli_error($conn);
}




// Close the database connection
mysqli_close($conn);
?>
