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
// // Check if the radio button "સંબંઘિ" is selected
// if (isset($_POST['relative_option']) && $_POST['relative_option'] === 'સંબંઘિ') {
//     // Fetch the value from the text box
//     $relativename = isset($_POST['relative_name']) ? $_POST['relative_name'] : null;
// } else {
//     $relativename = 'જાતે'; // Set to 'જાતે' if the radio button is not selected
// }
$r5 = isset($_POST['applicant_location']) ? $_POST['applicant_location'] : null;
$r4 = isset($_POST['applicant_taluko']) ? $_POST['applicant_taluko'] : null;
$r3 = isset($_POST['applicant_jilla']) ? $_POST['applicant_jilla'] : null;


$hayatiApplication2 = isset($_POST['hayati_application'][2]) ? $_POST['hayati_application'][2] : null;


// Check if "ના" is selected in $binkhetiApplication2
if ($hayatiApplication2 === 'ના') {
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
$hayatiApplication3 = isset($_POST['hayati_application'][3]) ? $_POST['hayati_application'][3] : null;
$hayatiApplication3_1 = isset($_POST['hayati_application']['3.1']) ? $_POST['hayati_application']['3.1'] : null;
$hayatiDifficulty4 = isset($_POST['hayati_multiple_selection_options_4']) ? $_POST['hayati_multiple_selection_options_4'] : null;
$hayatiMultipleSelection4 = isset($_POST['hayati_multiple_selection'][4]) ? $_POST['hayati_multiple_selection'][4] : [];
$hayatiApplication5 = isset($_POST['hayati_application_options_5']) ? $_POST['hayati_application_options_5'] : null;
$hayatiApplicationReason5 = isset($_POST['hayati_application_reason'][5]) ? $_POST['hayati_application_reason'][5] : [];
$hayatiApplicationResponse6 = isset($_POST['hayati_application_response']) ? $_POST['hayati_application_response'] : null;
$hayatiOfficeSelection6 = isset($_POST['hayati_office_selection'][6]) ? $_POST['hayati_office_selection'][6] : [];
$nikalMangani = isset($_POST['nikal_mangani']) ? $_POST['nikal_mangani'] : null;
$nikalManganiBy7 = isset($_POST['nikal_mangani_by'][7]) ? $_POST['nikal_mangani_by'][7] : [];
$application8 = isset($_POST['service_confirmation']) ? $_POST['service_confirmation'] : [];
$hayatisatisfied = isset($_POST['satisfied']) ? $_POST['satisfied'] : [];
$starRating = isset($_POST['starRating']) ? $_POST['starRating'] : null;
$additionalComments = isset($_POST['myInput']) ? $_POST['myInput'] : null;

// Get the current date and time
$currentDateTime = date('Y-m-d H:i:s');


 

// Print the values for verification (you can remove this in the final version)
// echo "Agent: $agent<br>";
// echo "phonenumber: $phonenumber<br>";
// echo "lead_id: $lead_id<br>";
// echo "rec_id: $rec_id<br>";
// echo "type_of_application: $type_of_application <br>";
// echo "Application No: $r2<br>";
// echo "Application Date: $r9<br>";
// echo "Disposal Date: $r10<br>";
// echo "Applicant Name: $r7<br>";
// echo "Applicant Location: $r5<br>";
// echo "Applicant Taluko: $r4<br>";
// echo "Applicant Jilla: $r3<br>";

// echo "Hayati Application 2: $hayatiApplication2<br>";
// echo "Hayati Application 3: $hayatiApplication3<br>";
// echo "Hayati Application 3.1: $hayatiApplication3_1<br>";
// echo "Hayati Difficulty4: $hayatiDifficulty4<br>";
// echo "Hayati Multiple Selection4: " . implode(', ', $hayatiMultipleSelection4) . "<br>"; // Convert array to comma-separated string
// echo "Hayati Application 5: $hayatiApplication5<br>";
// echo "Hayati Application Reason 5: " . implode(', ', $hayatiApplicationReason5) . "<br>"; // Convert array to comma-separated string
// echo "Hayati Application Response 6: $hayatiApplicationResponse6<br>";
// echo "Hayati Office Selection 6: " . implode(', ', $hayatiOfficeSelection6) . "<br>"; // Convert array to comma-separated string
// echo "Nikal Mangani: $nikalMangani<br>";
// echo "Nikal Mangani By 7: " . implode(', ', $nikalManganiBy7) . "<br>"; // Convert array to comma-separated string 
// echo "Difficulty 8: $application8<br>";
// echo "Star Rating: $starRating<br>";
// echo "Additional Comments: $additionalComments<br>";



 echo '<pre>';
 print_r($_POST);
echo '</pre>';

//exit;

// Convert array values to strings
$hayatiMultipleSelection4String = implode(', ', $hayatiMultipleSelection4);
$hayatiApplicationReason5String = implode(', ', $hayatiApplicationReason5);
$hayatiOfficeSelection6String = implode(', ', $hayatiOfficeSelection6);
$nikalManganiBy7String = implode(', ', $nikalManganiBy7);
$application8String = implode(', ', $application8);



// Insert data into the database
$sql = "INSERT INTO hayati_form_data (
    agent,
    phonenumber,
    lead_id,
    rec_id,
    type_of_application,
    application_no,
    application_date,
    disposal_date,
    applicant_name,
    hayati_relativename,
    applicant_location,
    applicant_taluko,
    applicant_jilla,
    hayati_application_2,
    hayati_application_3,
    hayati_application_3_1,
    hayati_difficulty_4,
    hayati_multiple_selection_4,
    hayati_application_5,
    hayati_application_reason_5,
    hayati_application_response_6,
    hayati_office_selection_6,
    nikal_mangani,
    nikal_mangani_by_7,
    service_confirmation,
    hayati_satisfied,
    star_rating,
    suggestion,
    entry_datetime
) VALUES (
    '$agent', '$phonenumber', '$lead_id', '$rec_id', '$type_of_application', '$r2', '$r9',
    '$r10', '$r7', '$relativename', '$r5', '$r4', '$r3', '$hayatiApplication2', '$hayatiApplication3',
    '$hayatiApplication3_1', '$hayatiDifficulty4', '$hayatiMultipleSelection4String',
    '$hayatiApplication5', '$hayatiApplicationReason5String',
    '$hayatiApplicationResponse6', '$hayatiOfficeSelection6String', '$nikalMangani',
    '$nikalManganiBy7String', '$application8', '$hayatisatisfied',
    '$starRating', '$additionalComments', '$currentDateTime'
)";

// echo "$sql";

if (mysqli_query($conn, $sql)) {
    echo "Record inserted successfully";
} else {
    echo "Error inserting record: " . mysqli_error($conn);
}




// Close the database connection
mysqli_close($conn);
?>
