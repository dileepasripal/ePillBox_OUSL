<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">   

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
            
            max-width: 800px; 
            margin: 50px auto; 
            flex-grow: 1; 
            width: 600px;
        }

        .wrapper h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .wrapper .btn {
            display: inline-block; 
            margin-bottom: 10px; 
            margin-right: 10px; 
        }

        .wrapper a {
            color: #000; 
        }
    </style>
</head>
<body>
    <?php
echo "<div class='wrapper'>"; // Wrapper starts here
    include "../includes/header.php"; 
    // Initialize the session
    session_start();

    // Check if the user is logged in, if not then redirect him to login  page
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
        header("location: login.php");
        exit;
    }

    

    // Check if the user is logged in and has the 'admin' role
    if (isset($_SESSION['id']) && $_SESSION['role'] == 'admin') {
        include 'admin_dashboard.php';
        echo "<p class=''>Hi, <b>". htmlspecialchars($_SESSION["username"])."</b>. Welcome to our site.";
        echo "<p>";
        echo "<a href='reset-password.php' class='btn btn-warning'>Reset Your Password</a>";
        echo "<a href='logout.php' class='btn btn-danger ml-3'>Sign Out of Your Account</a>";
        echo "</p>";
    }

    elseif (isset($_SESSION['id']) && $_SESSION['role'] == 'patient') {
        include 'patient_dashboard.php';
        echo "<p class=''>Hi, <b>". htmlspecialchars($_SESSION["username"])."</b>. Welcome to our site.";
        echo "<p>";
        echo "<a href='reset-password.php' class='btn btn-warning'>Reset Your Password</a>";
        echo "<a href='logout.php' class='btn btn-danger ml-3'>Sign Out of Your Account</a>";
        echo "</p>";
     }

    elseif (isset($_SESSION['id']) && $_SESSION['role'] == 'doctor') {
        include 'doctor_dashboard.php';
        echo "<p class=''>Hi, <b>". htmlspecialchars($_SESSION["username"])."</b>. Welcome to our site.";
        echo "<p>";
        echo "<a href='reset-password.php' class='btn btn-warning'>Reset Your Password</a>";
        echo "<a href='logout.php' class='btn btn-danger ml-3'>Sign Out of Your Account</a>";
        echo "</p>";
    }

    elseif (isset($_SESSION['id']) && $_SESSION['role'] == 'pharmacist') {
        include 'pharmacist_dashboard.php';
        echo "<p class=''>Hi, <b>". htmlspecialchars($_SESSION["username"])."</b>. Welcome to our site.";
        echo "<p>";
        echo "<a href='reset-password.php' class='btn btn-warning'>Reset Your Password</a>";
        echo "<a href='logout.php' class='btn btn-danger ml-3'>Sign Out of Your Account</a>";
        echo "</p>";
    }
    
    else {
        // Handle other roles or unknown roles (redirect to an error page or logout)
        echo "<p>Invalid role. <a href='logout.php'>Logout</a></p>";
    }

    include "../includes/footer.php"; 

    ?>
    </div>  
</body>
</html>