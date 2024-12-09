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

// --- Add more queries for other summary data ---

// 1. Total Number of Prescriptions
$sql = "SELECT COUNT(*) AS total_prescriptions FROM prescriptions";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$totalPrescriptions = $row['total_prescriptions'];

// 2. Total Number of Refill Requests
$sql = "SELECT COUNT(*) AS total_refill_requests FROM refill_requests";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$totalRefillRequests = $row['total_refill_requests'];

// Close connection
$conn->close();
?>

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

        <div class="col-md-3"> 
            <div class="summary-item bg-info text-white">
                <i class="fa fa-medkit"></i> 
                <h3>Total Prescriptions</h3>
                <p><?php echo $totalPrescriptions; ?></p>
            </div>
        </div>

        <div class="col-md-3"> 
            <div class="summary-item bg-warning text-white">
                <i class="fa fa-repeat"></i> 
                <h3>Refill Requests</h3>
                <p><?php echo $totalRefillRequests; ?></p>
            </div>
        </div>
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