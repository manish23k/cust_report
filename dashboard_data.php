<?php
function getDashboardData($db) {
    $dashboardData = [];

    $queries = [
        "Live Calls" => "SELECT COUNT(vicidial_auto_calls.`status`) FROM vicidial_auto_calls",
            "Calls in IVR OK" => "SELECT COUNT(vicidial_auto_calls.`status`) FROM vicidial_auto_calls WHERE vicidial_auto_calls.`status` = 'LIVE'",
            "Calls Waiting OK" => "SELECT COUNT(vicidial_auto_calls.`status`) FROM vicidial_auto_calls WHERE vicidial_auto_calls.`status` = 'IVR'",
            "Calls Ringing" => "SELECT COUNT(vicidial_auto_calls.call_type) FROM vicidial_auto_calls WHERE vicidial_auto_calls.stage = 'START'",
            "Agents on Call OK" => "SELECT COUNT(vicidial_live_agents.`user`) FROM vicidial_live_agents WHERE vicidial_live_agents.`status` = 'INCALL'",
            "Agents Available OK" => "SELECT COUNT(vicidial_live_agents.`user`) FROM vicidial_live_agents WHERE vicidial_live_agents.`status` in ('READY', 'CLOSER')",
            "Agents on Pause OK" => "SELECT COUNT(vicidial_live_agents.`user`) FROM vicidial_live_agents WHERE vicidial_live_agents.`status` = 'PAUSED'",
            "Inbound Total Calls" => "SELECT COUNT(vicidial_list.`status`) FROM vicidial_list WHERE vicidial_list.entry_date >= '" . date("Y-m-d") . "'",
            "Inbound Answered Calls" => "SELECT COUNT(vicidial_list.`status`) FROM vicidial_list WHERE vicidial_list.entry_date >= '" . date("Y-m-d") . "' AND vicidial_list.`status` NOT LIKE 'DROP' AND vicidial_list.`status` NOT LIKE 'TIMEOT' AND vicidial_list.`status` NOT LIKE 'INBND'",
            "Inbound Drop Calls" => "SELECT COUNT(vicidial_closer_log.`status`) FROM vicidial_closer_log WHERE vicidial_closer_log.entry_date >= '" . date("Y-m-d") . "' AND (vicidial_closer_log.`status` = 'DROP' OR vicidial_closer_log.`status` = 'TIMEOT' OR vicidial_closer_log.`status` = 'INBND')",
            "Outbound Total Calls OK" => "SELECT COUNT(vicidial_log.uniqueid) AS total_calls FROM vicidial_log WHERE `call_date` > '" . date("Y-m-d") . "' AND vicidial_log.`status` NOT LIKE 'CANCEL' AND vicidial_log.`status` NOT LIKE 'DOCCOM' AND vicidial_log.`status` NOT LIKE 'CALLBK' AND vicidial_log.`status` NOT LIKE 'WSD' AND vicidial_log.`status` NOT LIKE 'DCMX' AND vicidial_log.`status` NOT LIKE 'ADC'",
            "Outbound Answered Calls" => "SELECT COUNT(vicidial_log.`status`) FROM vicidial_log WHERE vicidial_log.call_date >= '" . date("Y-m-d") . "' AND `user` <> 'VDAD'",
            "Outbound Drop Calls Today" => "SELECT COUNT(vicidial_log.uniqueid) AS total_calls FROM vicidial_log WHERE `call_date` >= '" . date("Y-m-d") . "' AND `status` = 'DROP'",
    ];

    foreach ($queries as $title => $query) {
        $stmt = $db->prepare($query);
        
        if ($stmt) {
            $stmt->execute();
            $stmt->bind_result($count);
            
            if ($stmt->fetch()) {
                $dashboardData[$title] = $count;
            }

            $stmt->close();
        } else {
            // Handle query preparation failure
            // Example: log the error or redirect to an error page
        }
    }

    return $dashboardData;
}
?>
