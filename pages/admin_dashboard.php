<?php
// Include config file
require_once "../includes/db_connect.php";

// Fetch summary data
$sql = "SELECT COUNT(*) AS total_users FROM users";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$totalUsers = $row['total_users'];

$today = date("Y-m-d");
$sql = "SELECT COUNT(*) AS new_users_today FROM users WHERE DATE(created_at) = '$today'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$newUsersToday = $row['new_users_today'];

// ... (Add more queries for other summary data as needed)

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Roboto', sans-serif;
      background-color: #f8f9fa; 
    }

    .container {
      margin-top: 30px;
      margin-bottom: 30px; 
    }

    .summary-item {
      margin-bottom: 20px;
      padding: 20px;
      border-radius: 10px; 
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      background-color: #fff;
      text-align: center;
      position: relative;
      transition: transform 0.2s ease; 
    }

    .summary-item:hover {
      transform: translateY(-5px); 
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); 
    }

    .summary-item h3 {
      margin-bottom: 10px;
      font-size: 1.8rem; 
      color: #343a40; 
    }

    .summary-item .fa {
      font-size: 4rem; 
      margin-bottom: 15px;
      color: #fff; 
    }

    .summary-item .bg-primary {
      background-color: #007bff !important; 
    }

    .summary-item .bg-success {
      background-color: #28a745 !important; 
    }

    .summary-item p {
      font-size: 2.5rem; 
      font-weight: bold;
      color: #343a40; 
    }

    .list-group-item {
      display: flex;
      align-items: center;
      padding: 15px 20px; 
      border-radius: 8px; 
      transition: all 0.2s ease; 
    }

    .list-group-item .fa {
      margin-right: 15px; 
      font-size: 1.5rem; 
      color: #007bff; 
    }

    .list-group-item:hover {
      background-color: #f8f9fa; 
      transform: translateY(-3px); 
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
    }

    h1, h2 {
      font-weight: 700; 
      color: #343a40; 
    }
    .col-md-3 {
        flex: 0 0 25%; 
        max-width: 20%;  
    }
  </style>
</head>

<body>

  <div class="container">
    <h1 class="mt-5 mb-4 text-center">Admin Dashboard</h1>

    <div class="row">
      <div class="col-md-3"> 
        <div class="summary-item bg-primary text-white">
          <i class="fa fa-users"></i>
          <h3>Total Users</h3>
          <p><?php echo $totalUsers; ?></p>
        </div>
      </div>
      <div class="col-md-3"> 
        <div class="summary-item bg-success text-white">
          <i class="fa fa-user-plus"></i>
          <h3>New Users Today</h3>
          <p><?php echo $newUsersToday; ?></p>
        </div>
      </div>
      <div class="col-md-6"></div> 
    </div>

    <h2 class="mt-5 mb-3 text-center">Admin Functions</h2>
    <div class="row">
      <div class="col-md-6">
        <div class="list-group">
          <a href="manage_users.php" class="list-group-item list-group-item-action">
            <i class="fa fa-user-md"></i> User Management
          </a>
          <a href="manage_content.php" class="list-group-item list-group-item-action">
            <i class="fa fa-file-text-o"></i> Content Management
          </a>
          <a href="manage_pharmacies.php" class="list-group-item list-group-item-action">
            <i class="fa fa-hospital-o"></i> Pharmacy Management
          </a>
        </div>
      </div>
      <div class="col-md-6">
        <div class="list-group">
          <a href="data_analytics.php" class="list-group-item list-group-item-action">
            <i class="fa fa-bar-chart"></i> Data Analytics
          </a>
          <a href="manage_messages.php" class="list-group-item list-group-item-action">
            <i class="fa fa-envelope"></i> Manage Messages
          </a>
          <a href="system_settings.php" class="list-group-item list-group-item-action">
            <i class="fa fa-cog"></i> System Settings
          </a>
          <a href="send_notifications.php" class="list-group-item list-group-item-action">
            <i class="fa fa-bell"></i> Send Notifications
          </a>
        </div>
      </div>
    </div>
  </div>

</body>
</html>