<!DOCTYPE html>
<html>
<head>
    <title>ePillBox</title> 
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"> <style>
        /* Basic Styling */
        body {
            font-family: sans-serif;
        }

        header {
            background-color: #007bff; /* Example color */
            color: #fff;
            padding: 15px 20px;
        }

        nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex; /* Use flexbox for horizontal alignment */
            justify-content: space-between; /* Distribute space between items */
            align-items: center; /* Vertically center items */
        }

        nav li {
            margin-right: 20px; /* Spacing between list items */
        }

        nav a {
            color: #fff;
            text-decoration: none;
            font-weight: bold;
        }

        nav a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <nav class="container"> 
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
        <div class="container"> 
            </div>
    </main>
</body>
</html>