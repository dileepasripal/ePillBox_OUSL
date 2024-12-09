<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include config file
require_once "../includes/db_connect.php";

// Define variables and initialize with empty values
$username = $dob = $contact = $conditions = $medications = "";
$username_err = $dob_err = $contact_err = $conditions_err = $medications_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validate username
    $username = trim($_POST["username"]);
    if(empty($username)){
        $username_err = "Please enter a username.";
    }

    // Validate date of birth
    $dob = trim($_POST["dob"]);
    if(empty($dob)){
        $dob_err = "Please enter your date of birth.";
    }

    // Validate contact information
    $contact = trim($_POST["contact"]);
    if(empty($contact)){
        $contact_err = "Please enter your contact information.";
    }

    // Validate medical conditions (optional)
    $conditions = trim($_POST["conditions"]);

    // Validate current medications (optional)
    $medications = trim($_POST["medications"]);

    // Check input errors before updating the database
    if(empty($username_err) && empty($dob_err) && empty($contact_err)){
        // Prepare an update statement
        $sql = "UPDATE users SET username = ?, dob = ?, contact = ?, conditions = ?, medications = ? WHERE id = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sssssi", $param_username, $param_dob, $param_contact, $param_conditions, $param_medications, $param_id);
            
            // Set parameters
            $param_username = $username;
            $param_dob = $dob;
            $param_contact = $contact;
            $param_conditions = $conditions;
            $param_medications = $medications;
            $param_id = $_SESSION["id"];
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Profile updated successfully. Redirect to profile page
                header("location: profile.php");
                exit();
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($conn);
} else {
    // Prepare a select statement
    $sql = "SELECT username, dob, contact, conditions, medications FROM users WHERE id = ?";
    
    if($stmt = mysqli_prepare($conn, $sql)){
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        
        // Set parameters
        $param_id = $_SESSION["id"];
        
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            // Store result
            mysqli_stmt_store_result($stmt);
            
            // Check if user exists
            if(mysqli_stmt_num_rows($stmt) == 1){                    
                // Bind result variables
                mysqli_stmt_bind_result($stmt, $username, $dob, $contact, $conditions, $medications);
                if(mysqli_stmt_fetch($stmt)){
                    // Fetch and assign values to variables
                }
            } else{
                // URL doesn't contain valid id. Redirect to error page
                header("location: error.php");
                exit();
            }
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }

        // Close statement
        mysqli_stmt_close($stmt);
    }
    
    // Close connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 360px; padding: 20px; margin: 0 auto; }
    </style>
</head>
<body>
    <?php include "../includes/header.php"; ?> 
    <div class="wrapper">
        <h2>Profile</h2>
        <p>View and update your profile information.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>
            <div class="form-group">
                <label>Date of Birth</label>
                <input type="date" name="dob" class="form-control <?php echo (!empty($dob_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $dob; ?>">
                <span class="invalid-feedback"><?php echo $dob_err; ?></span>
            </div>
            <div class="form-group">
                <label>Contact Information</label>
                <input type="text" name="contact" class="form-control <?php echo (!empty($contact_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $contact; ?>">
                <span class="invalid-feedback"><?php echo $contact_err; ?></span>
            </div>
            <div class="form-group">
                <label>Medical Conditions (Optional)</label>
                <textarea name="conditions" class="form-control"><?php echo $conditions; ?></textarea>
            </div>
            <div class="form-group">
                <label>Current Medications (Optional)</label>
                <textarea name="medications" class="form-control"><?php echo $medications; ?></textarea>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Update Profile">
            </div>
        </form>

        <p>
            <a href="reset-password.php" class="btn btn-warning">Reset Your Password</a>
        </p>
    </div>
    <?php include "../includes/footer.php"; ?>
</body>
</html>