<?php

// Include config file
require_once "../includes/db_connect.php";

// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: home.php");
    exit;
}

// Define variables and initialize with empty values
$email = $username = "";
$email_err = $username_err = $password_err = $confirm_password_err = "";
$show_password_fields = false; // Flag to show/hide password fields

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Check if email or username is empty
    if(empty(trim($_POST["email"])) && empty(trim($_POST["username"]))){
        $email_err = "Please enter your email or username.";
        $username_err = "Please enter your email or username.";
    } else {
        $email = trim($_POST["email"]);
        $username = trim($_POST["username"]);
    }
    
    // Validate credentials
    if(empty($email_err) && empty($username_err)){
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE email = ? OR username = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ss", $param_email, $param_username);
            
            // Set parameters
            $param_email = $email;
            $param_username = $username;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Store result
                mysqli_stmt_store_result($stmt);
                
                // Check if email or username exists
                if(mysqli_stmt_num_rows($stmt) == 1){
                    if (!empty($email)) {
                        // Generate a unique token
                        $token = bin2hex(random_bytes(32)); 

                        // Store the token in the database
                        $sql = "UPDATE users SET reset_token = ? WHERE email = ?";
                        
                        if($stmt = mysqli_prepare($conn, $sql)){
                            mysqli_stmt_bind_param($stmt, "ss", $token, $param_email);
                            
                            if(mysqli_stmt_execute($stmt)){
                                // Send password reset email
                                $to = $email;
                                $subject = "Password Reset Request";
                                $message = "Hi there,\n\nYou have requested to reset your password.\n\nPlease click the following link to reset your password:\n\nhttp://yourwebsite.com/reset-password.php?token=" . $token . "\n\nIf you did not request a password reset, please ignore this email.\n\nBest regards,\nYour Website";
                                $headers = "From: yourwebsite@example.com\r\n";

                                if(mail($to, $subject, $message, $headers)){
                                    // Redirect to a success page
                                    header("location: forgot-password-success.php");
                                    exit;
                                } else {
                                    echo "Oops! Something went wrong. Please try again later.";
                                }
                            } else{
                                echo "Oops! Something went wrong. Please try again later.";
                            }
                        }
                    } else {
                        // Show password fields for username-based reset
                        $show_password_fields = true; 
                    }                    
                } else{
                    // Display an error message
                    $email_err = "No account found with that email or username.";
                    $username_err = "No account found with that email or username."; 
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }

    // If username is used and password fields are shown, process password update
    if ($show_password_fields && isset($_POST["password"]) && isset($_POST["confirm_password"])) {
        // Validate password 
        if(empty(trim($_POST["password"]))){
            $password_err = "Please enter a password.";     
        } elseif(strlen(trim($_POST["password"])) < 6){
            $password_err = "Password must have atleast 6 characters.";
        } else{
            $password = trim($_POST["password"]);
        }
        
        // Validate confirm password
        if(empty(trim($_POST["confirm_password"]))){
            $confirm_password_err = "Please confirm password.";     
        } else{
            $confirm_password = trim($_POST["confirm_password"]);
            if(empty($password_err) && ($password != $confirm_password)){
                $confirm_password_err = "Password did not match.";
            }
        }

        // Check input errors before updating the database
        if(empty($password_err) && empty($confirm_password_err)){
            // Prepare an update statement
            $sql = "UPDATE users SET password = ? WHERE username = ?";
            
            if($stmt = mysqli_prepare($conn, $sql)){
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "ss", $param_password_hash, $param_username);
                
                // Set parameters
                $param_username = $username;
                $param_password_hash = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
                
                // Attempt to execute the prepared statement
                if(mysqli_stmt_execute($stmt)){
                    // Password updated successfully. Redirect to login page
                    header("location: login.php");
                    exit;
                } else{
                    echo "Oops! Something went wrong. Please try again later.";
                }

                // Close statement
                mysqli_stmt_close($stmt);
            }
        }
    }
    
    // Close connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
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
            width: 360px;
            margin: 50px auto; 
            flex-grow: 1;
        }

        .wrapper h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-weight: bold;
        }

        .form-control {
            border-radius: 3px;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            border-radius: 3px; 
            padding: 10px 20px;
            cursor: pointer;
            display: block;
            width: 100%;
        }

        .btn-primary:hover {
            background-color: #0069d9;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 12px;
        }
        </style>
</head>
<body>
    <div class="wrapper">
        <h2>Forgot Password</h2>
        <p>Please fill in your email or username to reset your password.</p>

        <?php 
        if(!empty($email_err)){
            echo '<div class="alert alert-danger">' . $email_err . '</div>';
        }        
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                <span class="invalid-feedback"><?php echo $email_err; ?></span>
            </div>  
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div> 

            <?php 
            // Show password fields only if username is used for reset
            if ($show_password_fields) { 
            ?>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                    <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                </div>
            <?php } ?>

            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
            </div>
        </form>
    </div>
</body>
</html>