<!DOCTYPE html>
<html>
<head>
    <title>ePillBox</title> 
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="../index.php">Home</a></li>
                <?php if (isset($_SESSION['id'])) { ?>
                    <li><a href="index.php?page=profile">Profile</a></li>
                    <li><a href="index.php?page=logout">Logout</a></li>
                <?php } else { ?>
                    <li><a href="register.php">Register</a></li>
                    <li><a href="login.php">Login</a></li>
                <?php } ?>
            </ul>
        </nav>
    </header>

    <main>
