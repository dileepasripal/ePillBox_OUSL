<div class="container">
    <?php

include "../includes/header.php";
// Initialize the session
session_start();
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
// Check if the user is logged in and has the 'admin' role
elseif (isset($_SESSION['id']) && $_SESSION['role'] == 'admin') {
    include 'admin_dashboard.php';
    echo "<h1 class=''>Hi, <b>". htmlspecialchars($_SESSION["username"])."</b>. Welcome to our site.</h1>";
    echo "<p>";
    echo "    <a href='reset-password.php' class='btn btn-warning'>Reset Your Password</a>";
    echo "    <a href='logout.php' class='btn btn-danger ml-3'>Sign Out of Your Account</a>";
    echo "</p>";
}

elseif (isset($_SESSION['id']) && $_SESSION['role'] == 'patient') {
    include 'patient_dashboard.php';
    echo "<h1 class=''>Hi, <b>". htmlspecialchars($_SESSION["username"])."</b>. Welcome to our site.</h1>";
    echo "<p>";
    echo "    <a href='reset-password.php' class='btn btn-warning'>Reset Your Password</a>";
    echo "    <a href='logout.php' class='btn btn-danger ml-3'>Sign Out of Your Account</a>";
    echo "</p>";
}

elseif (isset($_SESSION['id']) && $_SESSION['role'] == 'provider') {
    include 'doctor_dashboard.php';
    echo "<h1 class=''>Hi, <b>". htmlspecialchars($_SESSION["username"])."</b>. Welcome to our site.</h1>";
    echo "<p>";
    echo "    <a href='reset-password.php' class='btn btn-warning'>Reset Your Password</a>";
    echo "    <a href='logout.php' class='btn btn-danger ml-3'>Sign Out of Your Account</a>";
    echo "</p>";
}

elseif (isset($_SESSION['id']) && $_SESSION['role'] == 'pharmacist') {
    include 'pharmacist_dashboard.php';
    echo "<h1 class=''>Hi, <b>". htmlspecialchars($_SESSION["username"])."</b>. Welcome to our site.</h1>";
    echo "<p>";
    echo "    <a href='reset-password.php' class='btn btn-warning'>Reset Your Password</a>";
    echo "    <a href='logout.php' class='btn btn-danger ml-3'>Sign Out of Your Account</a>";
    echo "</p>";
}
    
else {
        // Handle other roles or unknown roles (redirect to an error page or logout)
        echo "<p>Invalid role. <a href='logout.php'>Logout</a></p>";
    }

    include "../includes/footer.php";

?>
</div>