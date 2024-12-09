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

// ... (Add more queries for other summary data)

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    .summary-item {
      margin-bottom: 15px;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Admin Dashboard</h1>
    <div class="row">
      <div class="col-md-4">
        <div class="summary-item">
          <h3>Total Users</h3>
          <p><?php echo $totalUsers; ?></p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="summary-item">
          <h3>New Users Today</h3>
          <p><?php echo $newUsersToday; ?></p>
        </div>
      </div>
      </div>
      <h2>Admin Functions</h2>
      <ul>
        <li><a href="manage_users.php">User Management</a></li>
        <li><a href="manage_content.php">Content Management</a></li>
        <li><a href="manage_pharmacies.php">Pharmacy Management</a></li>
        <li><a href="data_analytics.php">Data Analytics</a></li>
      </ul>
  </div>
</body>
</html>