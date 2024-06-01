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
$query = "SELECT * FROM excel_data WHERE mobile_number = '" . $_REQUEST['phonenumber'] . "'";
$result = mysqli_query($conn, $query); // Pass $connection as the first parameter

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

while ($row1 = mysqli_fetch_array($result)) {
    $r1 = $row1['ID'];
    $r2 = $row1['application_no'];
    $r3 = $row1['district'];
    $r4 = $row1['taluka'];
    $r5 = $row1['village'];
    $r6 = $row1['survey_no'];
    $r7 = $row1['name_of_applicant'];
    $r8 = $row1['type_of_application'];
    $r9 = $row1['date_of_application'];
    $r10 = $row1['date_of_disposal'];
    $r11 = $row1['mobile_number'];
    //$r2 = $row1['name_of_applicant'];

    //URL FOR FETCH DATA
    //http://192.168.0.201/guj/binkheti_form.php?phonenumber=9227231501&agent=manish


    // ...

    // echo "$r2";
    // exit();
}

// Close the database connection
mysqli_close($conn);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>iORA ફીડબેક ફોર્મ</title>

    <!-- Include Bootstrap from a CDN -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Include the chosen Bootstrap theme from a CDN -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootswatch/4.5.2/cosmo/bootstrap.min.css">

<style>
        body {
            background-color: #f8f9fa;
            color: #495057;
        }

        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }

        h1,
        h2 {
            color: #007bff;
        }

        label {
            font-weight: bold;
        }

        input[type="text"].form-control {
            border-radius: 5px;
        }

        .form-check-label {
            padding-left: 20px;
        }

        button.btn-primary {
            background-color: #007bff;
            border: none;
        }

        button.btn-primary:hover {
            background-color: #0056b3;
        }

              .form-group input.hidden {
        display: none;
    }

    .form-group label, .form-group span {
        font-weight: bold;
    }

    </style>


<script>
    function toggleStarRating() {
        var satisfaction = document.querySelector('input[name="satisfaction"]:checked');

        if (satisfaction) {
            var satisfactionValue = satisfaction.value;
            var starContainer = document.getElementById('starContainer');
            var textareaContainer = document.getElementById('textareaContainer');

            if (satisfactionValue === 'yes') {
                starContainer.style.display = 'block';
                textareaContainer.style.display = 'none';
            } else if (satisfactionValue === 'no') {
                starContainer.style.display = 'none';
                textareaContainer.style.display = 'block';
            }
        }
    }
      </script>

            <script>
    function confirmSubmit() {
        // Display a confirmation dialog
        var confirmation = confirm("Are you sure you want to submit the form?");
        
        // Return true if the user clicked OK, false otherwise
        return confirmation;
    }
</script>

<script>
        function toggleFurtherQuestions() {
            var selectedOption = document.querySelector('input[name="hayati_application[2]"]:checked');

            if (selectedOption) {
                var optionValue = selectedOption.value;
                var furtherQuestionsContainer = document.getElementById('furtherQuestionsContainer');
                var submitButton = document.getElementById('submitButton');

                if (optionValue === 'હા') {
                    furtherQuestionsContainer.style.display = 'block';
                    submitButton.style.display = 'none';
                } else if (optionValue === 'ના') {
                    furtherQuestionsContainer.style.display = 'none';
                    submitButton.style.display = 'block';
                }
            }
        }
    </script>


</head>

<body>
   <div class="container">
   

    <b>
        <h1 class="text-center mt-4">iORA ના માધ્યમથી હયાતીમાં હકક દાખલ સર્વિસનો લાભ મેળવેલ હોય</h1>
        <h2 class="text-center">તેવા અરજદારશ્રીઓના પ્રતિભાવ મેળવવા માટેનું ફોર્મ.</h2>
    </b>
 


        <form class="mt-4" action="hayati_process_form.php" method="post" onsubmit="return confirmSubmit()">
        <?php
// Retrieve the "agent" value from the URL query parameters
$agent = $_GET['agent'];
$phonenumber = $_GET['phonenumber'];
$recId = $_GET['rec_id']; // Update variable name to match the URL parameter
$lead_id = $_GET['lead_id']; //Get Lead ID From URL 
?>

           
<!-- Display the agent name -->
<!-- <div class="form-group" id="agentDiv">
    <label for="agent">Call Agent:</label>
    <input type="text" name="agent" id="agent" value="<?php echo $agent; ?>" readonly class="hidden">
    <span><?php echo $agent; ?></span>
</div>

<div class="form-group" id="recIdDiv">
    <label for="rec_id">Rec ID:</label>
    <input type="text" name="rec_id" id="rec_id" value="<?php echo $recId; ?>" readonly class="hidden">
    <span><?php echo $recId; ?></span>
</div>

<div class="form-group" id="phonenumber">
    <label for="rec_id">Phone Number:</label>
    <input type="text" name="phonenumber" id="phonenumber" value="<?php echo $phonenumber; ?>" readonly class="hidden">
    <span><?php echo $phonenumber; ?></span>
</div>

<div class="form-group">
    <label for="type_of_application">Type of Application</label>
    <input type="text" name="type_of_application" id="type_of_application" value="<?php echo $r8; ?>" readonly class="hidden">
    <span><?php echo $r8; ?></span>
</div>

<div class="form-group">
    <label for="application_no">Application No.</label>
    <input type="text" name="application_no" id="application_no" value="<?php echo $r2; ?>" readonly class="hidden">
    <span><?php echo $r2; ?></span>
</div>

<div class="form-group">
    <label for="application_date">Application Date</label>
    <input type="text" name="application_date" id="application_date" value="<?php echo $r9; ?>" readonly class="hidden">
    <span><?php echo $r9; ?></span>

    <label for="disposal_date">Disposal Date</label>
    <input type="text" name="disposal_date" id="disposal_date" value="<?php echo $r10; ?>" readonly class="hidden">
    <span><?php echo $r10; ?></span>
</div> -->
<div class="row">
    <div class="col-md-3 form-group" id="agentDiv">
        <label for="agent">Call Agent:</label>
        <input type="text" name="agent" id="agent" value="<?php echo $agent; ?>" readonly class="hidden form-control">
        <span><?php echo $agent; ?></span>
    </div>

    <div class="col-md-3 form-group" id="recIdDiv">
        <label for="rec_id">Rec ID:</label>
        <input type="text" name="rec_id" id="rec_id" value="<?php echo $recId; ?>" readonly class="hidden form-control">
        <span><?php echo $recId; ?></span>
    </div>

    <div class="col-md-3 form-group" id="phonenumber">
        <label for="rec_id">Phone Number:</label>
        <input type="text" name="phonenumber" id="phonenumber" value="<?php echo $phonenumber; ?>" readonly class="hidden form-control">
        <span><?php echo $phonenumber; ?></span>
    </div>

    <div class="col-md-3 form-group" id="lead_id">
        <label for="rec_id">Lead ID:</label>
        <input type="text" name="lead_id" id="lead_id" value="<?php echo $lead_id; ?>" readonly class="hidden form-control">
        <span><?php echo $lead_id; ?></span>
    </div>

    <div class="col-md-3 form-group">
        <label for="type_of_application">Type of Application:</label>
        <input type="text" name="type_of_application" id="type_of_application" value="<?php echo $r8; ?>" readonly style="width: 400px;" >
         
    </div>
</div>

<div class="row">
    <div class="col-md-3 form-group">
        <label for="application_no">Application No.</label>
        <input type="text" name="application_no" id="application_no" value="<?php echo $r2; ?>" readonly class="hidden form-control">
        <span><?php echo $r2; ?></span>
    </div>

    <div class="col-md-3 form-group">
        <label for="application_date">Application Date</label>
        <input type="text" name="application_date" id="application_date" value="<?php echo $r9; ?>" readonly class="hidden form-control">
        <span><?php echo $r9; ?></span>
    </div>

    <div class="col-md-3 form-group">
        <label for="disposal_date">Disposal Date</label>
        <input type="text" name="disposal_date" id="disposal_date" value="<?php echo $r10; ?>" readonly class="hidden form-control">
        <span><?php echo $r10; ?></span>
    </div>
</div>


 

<div class="form-group">
    <label for="applicant_name">૧. આપનું નામ</label>
    <input type="text" name="applicant_name" id="applicant_name" value="<?php echo $r7; ?>" readonly style="width: 250px;">
    <label>છે ?</label> 
    <br>

    <div class="form-check">
        <input type="checkbox" class="form-check-input" name="relative_option" value="સંબંઘિ" id="relative_sambandh" >
        <label class="form-check-label" for="relative_sambandh">ના</label>
    </div>
 <div id="relativeNameContainer" style="display: none;">
        <label for="relative_name">આપનું નામ</label>
        <input type="text" name="relative_name" id="relative_name" style="width: 250px;">
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var relativeSambandhRadio = document.getElementById('relative_sambandh');
        var relativeNameContainer = document.getElementById('relativeNameContainer');

        relativeSambandhRadio.addEventListener('change', function () {
            if (relativeSambandhRadio.checked) {
                relativeNameContainer.style.display = 'block';
            } else {
                relativeNameContainer.style.display = 'none';
            }
        });
    });
</script>


<div class="form-group">
    <label for="applicant_location">૧.૧ આપ ગામ:</label>
    <!-- <input type="text" name="applicant_location" id="applicant_location" value="<?php echo $r5; ?>" readonly class="hidden"> -->
    <input type="text" name="applicant_location" id="applicant_location" value="<?php echo $r5; ?>" readonly style="width: 250px;">
    <!-- <span><?php echo $r5; ?></span> -->
<!-- </div>

<div class="form-group"> -->
    <label for="applicant_taluko">તાલુકો:</label>
    <!-- <input type="text" name="applicant_taluko" id="applicant_taluko" value="<?php echo $r4; ?>" readonly class="hidden"> -->
    <input type="text" name="applicant_taluko" id="applicant_taluko" value="<?php echo $r4; ?>" readonly style="width: 250px;">
    <!-- <span><?php echo $r4; ?></span> -->
<!-- </div>

<div class="form-group"> -->
    <label for="applicant_jilla">જિલ્લા:</label>
    <!-- <input type="text" name="applicant_jilla" id="applicant_jilla" value="<?php echo $r3; ?>" readonly class="hidden"> -->
    <input type="text" name="applicant_jilla" id="applicant_jilla" value="<?php echo $r3; ?>" readonly style="width: 250px;">
    <!-- <span><?php echo $r3; ?></span> -->
    <label>ના વતની છો?</label>
</div>


 <!-- Question 2 -->
<div class="form-group">
    <label for="hayati_application_2">૨. આપે તાજેતરમાં iORA મારફતે હયાતીમાં હકક દાખલઅંગેની અરજી કરેલી?</label>
    <div class="form-check">
        <input type="radio" class="form-check-input" name="hayati_application[2]" value="હા" onchange="toggleFurtherQuestions()">
        <label class="form-check-label">હા</label>
    </div>
    <div class="form-check">
        <input type="radio" class="form-check-input" name="hayati_application[2]" value="ના" onchange="toggleFurtherQuestions()">
        <label class="form-check-label">ના</label>
    </div>
</div>


<div id="furtherQuestionsContainer" style="display: none;">


  <!-- Question 3 -->
<div class="form-group">
    <label for="hayati_application_3">૩. અરજી આપે જાતે કરેલી કે કોઈના મારફતે કરાવેલી હતી?</label>
    <div class="form-check">
        <input type="radio" class="form-check-input" name="hayati_application[3]" value="જાતે" id="hayati_application_3_yes">
        <label class="form-check-label" for="hayati_application_3_yes">જાતે</label>
    </div>
    <div class="form-check">
        <input type="radio" class="form-check-input" name="hayati_application[3]" value="અન્ય મારફત" id="hayati_application_3_no">
        <label class="form-check-label" for="hayati_application_3_no">અન્ય મારફત</label>
    </div>
</div>
 
    <!-- Question 3.1 -->
        <div class="form-group" id="iora_application_options_3_1" style="display: none;">

<div class="form-group">
    <label for="hayati_application_3_1">૩.૧ જો કોઈના મારફતે કરાવેલ હોય તો, કોના મારફતે કરાવેલ?</label>
    <div class="form-check">
        <input type="radio" class="form-check-input" name="hayati_application[3.1]" value="સંબંઘિ" id="hayati_application_3_1_1">
        <label class="form-check-label" for="hayati_application_3_1_1">સંબંઘિ</label>
    </div>
    <div class="form-check">
        <input type="radio" class="form-check-input" name="hayati_application[3.1]" value="વકીલ" id="hayati_application_3_1_2">
        <label class="form-check-label" for="hayati_application_3_1_2">વકીલ</label>
    </div>
</div>
</div>


    <script>
        // Add JavaScript code to show/hide the multiple selection options based on the radio button value
        document.getElementById('hayati_application_3_no').addEventListener('change', function () {
            document.getElementById('iora_application_options_3_1').style.display = this.checked ? 'block' : 'none';
        });

        document.getElementById('hayati_application_3_yes').addEventListener('change', function () {
            document.getElementById('iora_application_options_3_1').style.display = 'none';
        });
    </script>


             <!-- Question 4 -->
<div class="form-group">
    <label for="hayati_application_4">૪. iORA ઉપર અરજી કરતાં સમયે કોઈ મુશ્કેલી પડી હતી ? </label>
    <div class="form-check">
        <input type="radio" class="form-check-input" name="hayati_multiple_selection_options_4" value="હા" id="hayati_difficulty_yes_4">
        <label class="form-check-label" for="hayati_difficulty_yes_4">હા</label>
    </div>
    <div class="form-check">
        <input type="radio" class="form-check-input" name="hayati_multiple_selection_options_4" value="ના" id="hayati_difficulty_no_4">
        <label class="form-check-label" for="hayati_difficulty_no_4">ના</label>
    </div>

    <div id="hayati_multiple_selection_options_4" style="display: none;">
         <label> જો હા તો ?</label>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="hayati_multiple_selection[4][]" value="૧. અરજી ફોર્મ વધારે લાંબું જણાયેલ" id="hayati_multiselect1_4">
            <label class="form-check-label" for="hayati_multiselect1_4">૧. અરજી ફોર્મ વધારે લાંબું જણાયેલ</label>
        </div>

        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="hayati_multiple_selection[4][]" value="૨. અરજીની ભાષા સમજવામાં મુશ્કેલી" id="hayati_multiselect2_4">
            <label class="form-check-label" for="hayati_multiselect2_4">૨. અરજીની ભાષા સમજવામાં મુશ્કેલી</label>
        </div>

        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="hayati_multiple_selection[4][]" value="૩. ડોક્યુમેન્ટ અપલોડ કરતી વખતે મુશ્કેલી" id="hayati_multiselect3_4">
            <label class="form-check-label" for="hayati_multiselect3_4">૩. ડોક્યુમેન્ટ અપલોડ કરતી વખતે મુશ્કેલી</label>
        </div>

        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="hayati_multiple_selection[4][]" value="૪. અરજી ફી ઓનલાઈન ભરતી વખતે મુશ્કેલી" id="hayati_multiselect4_4">
            <label class="form-check-label" for="hayati_multiselect4_4">૪. અરજી ફી ઓનલાઈન ભરતી વખતે મુશ્કેલી</label>
        </div>

        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="hayati_multiple_selection[4][]" value="૫. અન્ય" id="hayati_multiselect5_4">
            <label class="form-check-label" for="hayati_multiselect5_4">૫. અન્ય</label>
        </div>
    </div>
</div>

<script>
    // Add JavaScript code to show/hide the multiple selection options based on the radio button value
    document.getElementById('hayati_difficulty_yes_4').addEventListener('change', function () {
        document.getElementById('hayati_multiple_selection_options_4').style.display = this.checked ? 'block' : 'none';
    });

    document.getElementById('hayati_difficulty_no_4').addEventListener('change', function () {
        document.getElementById('hayati_multiple_selection_options_4').style.display = 'none';
    });
</script>

<!-- Question 5 -->
<div class="form-group">
    <label for="hayati_application_5">૫. અરજી નિકાલ માટે આપે કોઈને રૂબરૂ મળવા જવું પડ્યું હતું ?</label>

    <div class="form-check">
        <input type="radio" class="form-check-input" name="hayati_application_options_5" value="હા" id="hayati_application_yes_5">
        <label class="form-check-label" for="hayati_application_yes_5">હા</label>
    </div>

    <div class="form-check">
        <input type="radio" class="form-check-input" name="hayati_application_options_5" value="ના" id="hayati_application_no_5">
        <label class="form-check-label" for="hayati_application_no_5">ના</label>
    </div>

    <div id="hayati_application_options_5" style="display: none;">
         <label> જો હા તો ?</label>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="hayati_application_reason[5][]" value="૧. અરજીના સાધનિક કાગળો રજુ કરવા માટે કે"
                id="hayati_application_reason1">
            <label class="form-check-label" for="hayati_application_reason1">૧. અરજીના સાધનિક કાગળો રજુ કરવા માટે કે</label>
        </div>

        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="hayati_application_reason[5][]" value="૨. અન્ય કારણથી ?"
                id="hayati_application_reason2">
            <label class="form-check-label" for="hayati_application_reason2">૨. અન્ય કારણથી ?</label>
        </div>
    </div>
</div>

<script>
    // Add JavaScript code to show/hide the options based on the radio button value
    document.getElementById('hayati_application_yes_5').addEventListener('change', function () {
        document.getElementById('hayati_application_options_5').style.display = this.checked ? 'block' : 'none';
    });

    document.getElementById('hayati_application_no_5').addEventListener('change', function () {
        document.getElementById('hayati_application_options_5').style.display = 'none';
    });
</script>


            <!-- Question 6 -->
<div class="form-group">
    <label for="hayati_application">૬. અરજીના નિકાલ માટે કલેક્ટર ઑફિસ કે અન્ય કોઈ ઑફિસમાંથી આપની ઉપર કોઈનો ફોન આવેલ ? </label>

    <div class="form-check">
        <input type="radio" class="form-check-input" name="hayati_application_response" value="હા" id="hayati_application_yes_6">
        <label class="form-check-label" for="hayati_application_yes_6">હા</label>
    </div>

    <div class="form-check">
        <input type="radio" class="form-check-input" name="hayati_application_response" value="ના" id="hayati_application_no_6">
        <label class="form-check-label" for="hayati_application_no_6">ના</label>
    </div>

    <div id="hayati_office_options_6" style="display: none;">
         <label> જો હા તો ?</label>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="hayati_office_selection[6][]" value="૧. કલેકટર કચેરી" id="hayati_office1">
            <label class="form-check-label" for="hayati_office1">૧. કલેકટર કચેરી</label>
        </div>

        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="hayati_office_selection[6][]" value="૨. પ્રાંત કચેરી" id="hayati_office2">
            <label class="form-check-label" for="hayati_office2">૨. પ્રાંત કચેરી</label>
        </div>

        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="hayati_office_selection[6][]" value="૩. મામલતદાર કચેરી" id="hayati_office3">
            <label class="form-check-label" for="hayati_office3">૩. મામલતદાર કચેરી</label>
        </div>
    </div>
</div>

<script>
    // Add JavaScript code to show/hide the office options based on the radio button value
    document.getElementById('hayati_application_yes_6').addEventListener('change', function () {
        document.getElementById('hayati_office_options_6').style.display = this.checked ? 'block' : 'none';
    });

    document.getElementById('hayati_application_no_6').addEventListener('change', function () {
        document.getElementById('hayati_office_options_6').style.display = 'none';
    });
</script>

            
        <!-- Question 7 -->
<div class="form-group">
    <label for="hayati_application">૭. અરજી નિકાલ માટે કોઈ બિનજરૂરી માંગણી કરવામાં આવેલ? </label>

    <div class="form-check">
        <input type="radio" class="form-check-input" name="nikal_mangani" value="હા" id="nikal_mangani_yes">
        <label class="form-check-label" for="nikal_mangani_yes">હા</label>
    </div>

    <div class="form-check">
        <input type="radio" class="form-check-input" name="nikal_mangani" value="ના" id="nikal_mangani_no">
        <label class="form-check-label" for="nikal_mangani_no">ના</label>
    </div>

    <div id="nikal_mangani_options_7" style="display: none;">
        <label> જો હા તો કોના દ્વારા?</label>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="nikal_mangani_by[7][]" value="૧. કલેકટર કચેરીના સ્ટાફ દ્વારા" id="nikal_mangani_option1">
            <label class="form-check-label" for="nikal_mangani_option1">૧. કલેકટર કચેરીના સ્ટાફ દ્વારા</label>
        </div>

        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="nikal_mangani_by[7][]" value="૨. પ્રાંત કચેરીના સ્ટાફ દ્વારા" id="nikal_mangani_option2">
            <label class="form-check-label" for="nikal_mangani_option2">૨. પ્રાંત કચેરીના સ્ટાફ દ્વારા</label>
        </div>

        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="nikal_mangani_by[7][]" value="૩. મામલતદાર કચેરીના સ્ટાફ દ્વારા" id="nikal_mangani_option3">
            <label class="form-check-label" for="nikal_mangani_option3">૩. મામલતદાર કચેરીના સ્ટાફ દ્વારા</label>
        </div>

        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="nikal_mangani_by[7][]" value="૪. અન્ય દ્વારા" id="nikal_mangani_option4">
            <label class="form-check-label" for="nikal_mangani_option4">૪. અન્ય દ્વારા</label>
        </div>
    </div>
</div>

<script>
    // Add JavaScript code to show/hide the multiple selection options based on the radio button value
    document.getElementById('nikal_mangani_yes').addEventListener('change', function () {
        document.getElementById('nikal_mangani_options_7').style.display = this.checked ? 'block' : 'none';
    });

    document.getElementById('nikal_mangani_no').addEventListener('change', function () {
        document.getElementById('nikal_mangani_options_7').style.display = 'none';
    });
</script>

<!-- Question 8 -->
<div class="form-group">
    <label for="hayati_service">૮. આ સેવા અન્વયે નોંઘ પ્રમાણીત થયેલ છે તેની જાણ આ૫ને મેસેજથી થયેલ હતી?</label>

    <div class="form-check">
        <input type="radio" class="form-check-input" name="service_confirmation" value="હા" id="service_confirmation_yes">
        <label class="form-check-label" for="service_confirmation_yes">હા</label>
    </div>

    <div class="form-check">
        <input type="radio" class="form-check-input" name="service_confirmation" value="ના" id="service_confirmation_no">
        <label class="form-check-label" for="service_confirmation_no">ના</label>
    </div>
</div>


 
<!-- Question 9 -->
<div class="form-group">
    <label for="satisfaction">૯. આપે આ સેવાનો લાભ મેળવેલ છે આ સમગ્ર પ્રક્રિયાથી આપ સંતુષ્ટ છો? </label>


    <div class="form-check">
        <input type="radio" class="form-check-input" name="satisfied" value="હા" id="satisfied_yes">
        <label class="form-check-label" for="satisfied_yes">હા</label>
    </div>

    <div class="form-check">
        <input type="radio" class="form-check-input" name="satisfied" value="ના" id="satisfied_no">
        <label class="form-check-label" for="satisfied_no">ના</label>
    </div>



        <label>આ પ્રક્રીયા ને આપ કેટલા સ્ટાર આ૫શો?</label>
    <div class="form-group">
        
        <div class="form-check">
            <input type="radio" class="form-check-input" name="starRating" value="1">
            <label class="form-check-label">૧ સ્ટાર</label>
        </div>
        <div class="form-check">
            <input type="radio" class="form-check-input" name="starRating" value="2">
            <label class="form-check-label">૨ સ્ટાર</label>
        </div>
        <div class="form-check">
            <input type="radio" class="form-check-input" name="starRating" value="3">
            <label class="form-check-label">૩ સ્ટાર</label>
        </div>
        <div class="form-check">
            <input type="radio" class="form-check-input" name="starRating" value="4">
            <label class="form-check-label">૪ સ્ટાર</label>
        </div>
        <div class="form-check">
            <input type="radio" class="form-check-input" name="starRating" value="5">
            <label class="form-check-label">૫ સ્ટાર</label>
        </div>
    </div>
</div>

<div class="form-group">
    <label>સેવાને વઘુ સારી બનાવવા સૂચન.</label>
    <div class="form-text">
        <textarea name="myInput" placeholder="Enter text here" style="width: 500px; resize: both;  font-weight: normal;"></textarea>
    </div>
</div>
</div>

</div>
    </div>
    

            

           <button type="submit" class="btn btn-primary" style="display: block; margin: 0 auto;">સબમિટ</button>
    
        </form>
    </div>

    <!-- Include Bootstrap JavaScript (at the end of the body for better performance) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>