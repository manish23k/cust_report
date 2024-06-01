<?php
include "header.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selectedReport = $_POST["report"];

    if (file_exists($selectedReport)) {
        include $selectedReport;
        exit(); // Make sure to exit after including the report to prevent further output
    } else {
        echo "Selected report does not exist.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Home</title>
</head>
<body>
    <h1>Select a Report</h1>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <label for="report">Choose a report:</label>
        <select name="report" id="report">
            <option value="binkheti_report.php">Binkheti Report</option>
            <option value="hayati_report.php">Hayati Report</option>
            <option value="varsayi_report.php">Varsayi Report</option>
            <option value="khedut_report.php">Khedut Report</option>
            <!-- Add more options as needed -->
        </select>
        <br>
        <input type="submit" value="View Report">
    </form>
</body>
</html>
