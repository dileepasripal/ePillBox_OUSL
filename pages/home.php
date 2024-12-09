<?php
// Initialize the session
session_start();

// Check if the user is logged in, if yes then redirect him to welcome page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

include '../includes/header.php';
// Include config file
require_once "../includes/db_connect.php"; 

// Determine the user's role and include the appropriate dashboard
$role = $_SESSION["role"];
$dashboardFile = ""; 

switch ($role) {
    case 'admin':
        $dashboardFile = "admin_dashboard.php";
        break;
    case 'patient':
        $dashboardFile = "patient_dashboard.php";
        break;
    case 'doctor':
        $dashboardFile = "doctor_dashboard.php";
        break;
    case 'pharmacist':
        $dashboardFile = "pharmacist_dashboard.php";
        break;
    default:
        // Handle invalid role (e.g., log an error and redirect to logout)
        error_log("Invalid role detected: " . $role); 
        header("location: logout.php"); 
        exit; 
}

// Include the determined dashboard file
if (!empty($dashboardFile)) {
    include $dashboardFile;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title> 
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body{ font: 14px sans-serif; text-align: center; }
    </style>
</head>
<body>

    <h1 class="my-5">Hi, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>. Welcome to ePillbox.</h1>
    <p>
        <a href="reset-password.php" class="btn btn-warning">Reset Your Password</a>
        <a href="logout.php" class="btn btn-danger ml-3">Sign Out of Your Account</a>
    </p>

    <?php include '../includes/footer.php'; ?> 
</body>
</html>