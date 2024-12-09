<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ePillbox - <?php echo $title; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }

        .navbar {
            background-color: #007bff; 
        }

        .navbar-brand {
            font-weight: 700; 
            color: #fff !important; 
        }

        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.8) !important; 
            transition: color 0.2s ease; 
        }

        .navbar-nav .nav-link:hover {
            color: #fff !important; 
        }
        
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark"> 
            <div class="container">
                <a class="navbar-brand" href="home.php">ePillbox</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ml-auto"> 
                        <?php
                        if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
                            echo '<li class="nav-item"><a class="nav-link" href="../pages/home.php">Home</a></li>';
                            echo '<li class="nav-item"><a class="nav-link" href="../pages/profile.php">Profile</a></li>'; 
                            echo '<li class="nav-item"><a class="nav-link" href="../pages/logout.php">Logout</a></li>';
                        } else {
                            echo '<li class="nav-item"><a class="nav-link" href="../pages/register.php">Register</a></li>';
                            echo '<li class="nav-item"><a class="nav-link" href="../pages/login.php">Login</a></li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main class="flex-grow-1">