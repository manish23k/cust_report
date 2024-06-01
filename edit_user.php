<?php
session_start();
include "config.php";
include "header.php";

if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit;
}

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $query = "SELECT * FROM vicidial_users WHERE user_id = $user_id";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Collecting all the input fields
        $agent_user = $_POST['user'];
        $agent_pass = $_POST['pass'];
        $agent_user_level = $_POST['agent_user_level'];
        $agent_full_name = $_POST['agent_full_name'];
        $agent_user_group = $_POST['agent_user_group'];
        $phone_login = $_POST['phone_login'];
        $phone_pass = $_POST['phone_pass'];
        $hotkeys_active = $_POST['hotkeys_active'];
        $voicemail_id = $_POST['voicemail_id'];
        $email = $_POST['email'];
        $custom_one = $_POST['custom_one'];
        $custom_two = $_POST['custom_two'];
        $custom_three = $_POST['custom_three'];
        $custom_four = $_POST['custom_four'];
        $custom_five = $_POST['custom_five'];
        $active = $_POST['active'];
        $wrapup_seconds_override = $_POST['wrapup_seconds_override'];
        $campaign_rank = $_POST['campaign_rank'];
        $campaign_grade = $_POST['campaign_grade'];
        $ingroup_rank = $_POST['ingroup_rank'];
        $ingroup_grade = $_POST['ingroup_grade'];
        $camp_rg_only = $_POST['camp_rg_only'];
        $campaign_id = $_POST['campaign_id'];
        $ingrp_rg_only = $_POST['ingrp_rg_only'];
        $group_id = $_POST['group_id'];
        $reset_password = $_POST['reset_password'];

        // Formulate the API URL based on the input
        $api_url = "http://192.168.0.201/vicidial/non_agent_api.php?source=test&function=update_user";
        $api_url .= "&user=admin&pass=admin"; // Replace 6666 and 1234 with actual credentials

        // Append required fields to the API URL
        $api_url .= "&agent_user=$agent_user";

        // Append optional fields to the API URL if they are filled in the form
        if ($agent_pass !== '') {
            $api_url .= "&agent_pass=$agent_pass";
        }
        if ($agent_user_level !== '') {
            $api_url .= "&agent_user_level=$agent_user_level";
        }
        if ($agent_full_name !== '') {
            $api_url .= "&agent_full_name=$agent_full_name";
        }
        if ($agent_user_group !== '') {
            $api_url .= "&agent_user_group=$agent_user_group";
        }
        if ($phone_login !== '') {
            $api_url .= "&phone_login=$phone_login";
        }
        if ($phone_pass !== '') {
            $api_url .= "&phone_pass=$phone_pass";
        }
        if ($hotkeys_active !== '') {
            $api_url .= "&hotkeys_active=$hotkeys_active";
        }
        if ($voicemail_id !== '') {
            $api_url .= "&voicemail_id=$voicemail_id";
        }
        if ($email !== '') {
            $api_url .= "&email=$email";
        }
        if ($custom_one !== '') {
            $api_url .= "&custom_one=$custom_one";
        }
        if ($custom_two !== '') {
            $api_url .= "&custom_two=$custom_two";
        }
        if ($custom_three !== '') {
            $api_url .= "&custom_three=$custom_three";
        }
        if ($custom_four !== '') {
            $api_url .= "&custom_four=$custom_four";
        }
        if ($custom_five !== '') {
            $api_url .= "&custom_five=$custom_five";
        }
        if ($active !== '') {
            $api_url .= "&active=$active";
        }
        if ($wrapup_seconds_override !== '') {
            $api_url .= "&wrapup_seconds_override=$wrapup_seconds_override";
        }
        if ($campaign_rank !== '') {
            $api_url .= "&campaign_rank=$campaign_rank";
        }
        if ($campaign_grade !== '') {
            $api_url .= "&campaign_grade=$campaign_grade";
        }
        if ($ingroup_rank !== '') {
            $api_url .= "&ingroup_rank=$ingroup_rank";
        }
        if ($ingroup_grade !== '') {
            $api_url .= "&ingroup_grade=$ingroup_grade";
        }
        if ($camp_rg_only !== '') {
            $api_url .= "&camp_rg_only=$camp_rg_only";
        }
        if ($campaign_id !== '') {
            $api_url .= "&campaign_id=$campaign_id";
        }
        if ($ingrp_rg_only !== '') {
            $api_url .= "&ingrp_rg_only=$ingrp_rg_only";
        }
        if ($group_id !== '') {
            $api_url .= "&group_id=$group_id";
        }
        if ($reset_password !== '') {
            $api_url .= "&reset_password=$reset_password";
        }

        // Make the API call using cURL
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        // Handle API response
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            echo "API Response: " . $response;
            // Handle the API response as needed
        }

        // Redirect to the user list page
        header("Location: list_users.php");
        exit();
    }
} else {
    header("Location: list_users.php");
    exit();
}
?>

<h2>Edit User</h2>
<form method="post" action="">
    Username: <input type="text" name="user" value="<?= $user['user'] ?>" required><br>
    Password: <input type="password" name="pass" value="<?= $user['pass'] ?>" required><br>
    Agent User Level: <input type="text" name="agent_user_level" value="<?= $user['agent_user_level'] ?>"><br>
    Agent Full Name: <input type="text" name="agent_full_name" value="<?= $user['agent_full_name'] ?>"><br>
    Agent User Group: <input type="text" name="agent_user_group" value="<?= $user['agent_user_group'] ?>"><br>
    Phone Login: <input type="text" name="phone_login" value="<?= $user['phone_login'] ?>"><br>
    Phone Pass: <input type="text" name="phone_pass" value="<?= $user['phone_pass'] ?>"><br>
    Hotkeys Active: <input type="text" name="hotkeys_active" value="<?= $user['hotkeys_active'] ?>"><br>
    Voicemail ID: <input type="text" name="voicemail_id" value="<?= $user['voicemail_id'] ?>"><br>
    Email: <input type="text" name="email" value="<?= $user['email'] ?>"><br>
    Custom One: <input type="text" name="custom_one" value="<?= $user['custom_one'] ?>"><br>
    Custom Two: <input type="text" name="custom_two" value="<?= $user['custom_two'] ?>"><br>
    Custom Three: <input type="text" name="custom_three" value="<?= $user['custom_three'] ?>"><br>
    Custom Four: <input type="text" name="custom_four" value="<?= $user['custom_four'] ?>"><br>
    Custom Five: <input type="text" name="custom_five" value="<?= $user['custom_five'] ?>"><br>
    Active (Y/N): <input type="text" name="active" value="<?= $user['active'] ?>"><br>
    Wrapup Seconds Override: <input type="text" name="wrapup_seconds_override" value="<?= $user['wrapup_seconds_override'] ?>"><br>
    Campaign Rank: <input type="text" name="campaign_rank" value="<?= $user['campaign_rank'] ?>"><br>
    Campaign Grade: <input type="text" name="campaign_grade" value="<?= $user['campaign_grade'] ?>"><br>
    Ingroup Rank: <input type="text" name="ingroup_rank" value="<?= $user['ingroup_rank'] ?>"><br>
    Ingroup Grade: <input type="text" name="ingroup_grade" value="<?= $user['ingroup_grade'] ?>"><br>
    Camp RG Only: <input type="text" name="camp_rg_only" value="<?= $user['camp_rg_only'] ?>"><br>
    Campaign ID: <input type="text" name="campaign_id" value="<?= $user['campaign_id'] ?>"><br>
    Ingrp RG Only: <input type="text" name="ingrp_rg_only" value="<?= $user['ingrp_rg_only'] ?>"><br>
    Group ID: <input type="text" name="group_id" value="<?= $user['group_id'] ?>"><br>
    Reset Password: <input type="text" name="reset_password" value="<?= $user['reset_password'] ?>"><br>

    <input type="submit" value="Update User">
</form>
