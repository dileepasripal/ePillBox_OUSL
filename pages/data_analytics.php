<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Check if the user has the 'admin' role, if not then redirect to home page
if($_SESSION["role"] !== 'admin'){
    header("location: home.php");
    exit;
}

// Include config file
require_once "../includes/db_connect.php";

// --- Fetch actual data from your database ---

// Example 1: User Sign-ups Over Time (Monthly)
$monthlySignups = [];
$sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS count FROM users GROUP BY month ORDER BY month";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $monthlySignups[] = array($row['month'], (int)$row['count']);
    }
} else {
    echo "Error fetching monthly sign-ups: " . $conn->error;
}

// Example 2: Prescriptions by Month
$prescriptionsByMonth = [];
$sql = "SELECT DATE_FORMAT(start_date, '%Y-%m') AS month, COUNT(*) AS count FROM prescriptions GROUP BY month ORDER BY month";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $prescriptionsByMonth[] = array($row['month'], (int)$row['count']);
    }
} else {
    echo "Error fetching prescriptions by month: " . $conn->error;
}

// Close connection
$conn->close();

// Prepare data for Google Charts
$signupsData = array_merge(array(array('Month', 'Sign-ups')), $monthlySignups);
$prescriptionsData = array_merge(array(array('Month', 'Prescriptions')), $prescriptionsByMonth);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Data Analytics</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawCharts);

        function drawCharts() {
            // Draw sign-ups chart
            var signupsData = google.visualization.arrayToDataTable(<?php echo json_encode($signupsData); ?>);
            var signupsOptions = {
                title: 'User Sign-ups Over Time',
                curveType: 'function',
                legend: { position: 'bottom' }
            };
            var signupsChart = new google.visualization.LineChart(document.getElementById('signups_chart'));
            signupsChart.draw(signupsData, signupsOptions);

            // Draw prescriptions chart
            var prescriptionsData = google.visualization.arrayToDataTable(<?php echo json_encode($prescriptionsData); ?>);
            var prescriptionsOptions = {
                title: 'Prescriptions by Month',
                curveType: 'function',
                legend: { position: 'bottom' }
            };
            var prescriptionsChart = new google.visualization.LineChart(document.getElementById('prescriptions_chart'));
            prescriptionsChart.draw(prescriptionsData, prescriptionsOptions);

            // ... (Add more chart drawing functions as needed) ...
        }
    </script>
    <style>
        body {
            font: 14px sans-serif;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column; 
            min-height: 100vh; 
        }

        .wrapper {
            background: #fff;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 80%;
            max-width: 1200px; 
            margin: 50px auto; 
            flex-grow: 1; 
            width: 1000px;
        }

        .wrapper h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .wrapper .btn {
            display: inline-block; 
            margin-bottom: 10px; 
            margin-right: 10px; 
        }

        .wrapper a {
            color: #fff; 
        }

        .table {
            width: 100%;
            max-width: 100%; 
            margin-bottom: 20px;
        }

        .table th, .table td {
            padding: 10px;
            vertical-align: middle; 
        }
        .wrapper a {
            color: #000; 
        }
        </style>
</head>
<body>
    <div class="wrapper">
        <?php include "../includes/header.php"; ?>

        <h2>Data Analytics</h2>

        <div id="signups_chart" style="width: 900px; height: 500px"></div>

        <div id="prescriptions_chart" style="width: 900px; height: 500px"></div>

        <?php include "../includes/footer.php"; ?>
    </div>
</body>
</html>