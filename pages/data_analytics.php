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

// Sample data for the chart (replace with actual data from your database)
$data = array(
    array('Month', 'Sign-ups'),
    array('Jan', 100),
    array('Feb', 150),
    array('Mar', 200),
    array('Apr', 250),
    array('May', 300)
    // ... more data
);

// Convert data to JSON format for the Google Chart
$data_json = json_encode($data);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Data Analytics</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = google.visualization.arrayToDataTable(<?php echo $data_json; ?>);

            var options = {
                title: 'User Sign-ups Over Time',
                curveType: 'function',
                legend: { position: 'bottom' }
            };

            var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

            chart.draw(data, options);
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

        <div id="curve_chart" style="width: 900px; height: 500px"></div>

        <?php
        $conn->close();
        include "../includes/footer.php";
        ?>
    </div>
</body>
</html>