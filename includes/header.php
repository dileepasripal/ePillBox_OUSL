<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ePillbox - <?php echo $title; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">  </head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container"> 
                <a class="navbar-brand" href="home.php">ePillbox</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ml-auto">
                        <?php
                        if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
                            echo '<li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>';
                            echo '<li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>'; 
                            echo '<li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>';
                        } else {
                            echo '<li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>';
                            echo '<li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main class="flex-grow-1">