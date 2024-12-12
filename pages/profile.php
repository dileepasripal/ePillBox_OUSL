<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include config file
require_once "../includes/db_connect.php";

// Define variables and initialize with empty values
$username = $dob = $contact = $conditions = $medications = $specialization = $experience = $pharmacy_name = $license_number = "";
$username_err = $dob_err = $contact_err = $conditions_err = $medications_err = $specialization_err = $experience_err = $pharmacy_name_err = $license_number_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

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

    // Validate fields based on user role
    switch ($_SESSION["role"]) {
        case 'patient':
            // Validate medical conditions (optional)
            $conditions = trim($_POST["conditions"]);

            // Validate current medications (optional)
            $medications = trim($_POST["medications"]);
            break;
        case 'doctor':
            // Validate specialization
            $specialization = trim($_POST["specialization"]);
            if(empty($specialization)){
                $specialization_err = "Please enter your specialization.";
            }

            // Validate experience
            $experience = trim($_POST["experience"]);
            if(empty($experience)){
                $experience_err = "Please enter your experience.";
            }
            break;
        case 'pharmacist':
            // Validate pharmacy name
            $pharmacy_name = trim($_POST["pharmacy_name"]);
            if(empty($pharmacy_name)){
                $pharmacy_name_err = "Please enter your pharmacy name.";
            }

            // Validate license number
            $license_number = trim($_POST["license_number"]);
            if(empty($license_number)){
                $license_number_err = "Please enter your license number.";
            }
            break;
    }

    // Check input errors before updating the database
    if(empty($username_err) && empty($dob_err) && empty($contact_err) && 
       empty($conditions_err) && empty($medications_err) && empty($specialization_err) && 
       empty($experience_err) && empty($pharmacy_name_err) && empty($license_number_err)){

        // Prepare an update statement based on user role
        $sql = "";
        switch ($_SESSION["role"]) {
            case 'patient':
                $sql = "UPDATE users SET username = ?, dob = ?, contact = ? WHERE id = ?";
                $sql2 = "UPDATE patients SET conditions = ?, medications = ? WHERE username = ?";
                break;
            case 'doctor':
                $sql = "UPDATE users SET username = ?, dob = ?, contact = ? WHERE id = ?";
                $sql2 = "UPDATE doctors SET specialization = ?, experience = ? WHERE username = ?";
                break;
            case 'pharmacist':
                $sql = "UPDATE users SET username = ?, dob = ?, contact = ? WHERE id = ?";
                $sql2 = "UPDATE pharmacists SET pharmacy_name = ?, license_number = ? WHERE username = ?";
                break;
        }
        
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters based on user role
            switch ($_SESSION["role"]) {
                case 'patient':
                case 'doctor':
                case 'pharmacist':
                    mysqli_stmt_bind_param($stmt, "sssi", $param_username, $param_dob, $param_contact, $param_id);
                    break;
            }
            
            // Set parameters
            $param_username = $username;
            $param_dob = $dob;
            $param_contact = $contact;
            $param_id = $_SESSION["id"];
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // If doctor or pharmacist, update their specific table as well
                if ($_SESSION["role"] == 'doctor' || $_SESSION["role"] == 'pharmacist' || $_SESSION["role"] == 'patient') {
                    if($stmt2 = mysqli_prepare($conn, $sql2)){
                        // Bind parameters for the second query based on user role
                        if ($_SESSION["role"] == 'doctor') {
                            mysqli_stmt_bind_param($stmt2, "sss", $param_specialization, $param_experience, $param_username);
                            $param_specialization = $_POST["specialization"];
                            $param_experience = $_POST["experience"];
                        } else if ($_SESSION["role"] == 'pharmacist') {
                            mysqli_stmt_bind_param($stmt2, "sss", $param_pharmacy_name, $param_license_number, $param_username);
                            $param_pharmacy_name = $_POST["pharmacy_name"];
                            $param_license_number = $_POST["license_number"];
                        } else {
                            mysqli_stmt_bind_param($stmt2, "sss", $param_conditions, $param_medications, $param_username);
                            $param_conditions = $_POST["conditions"];
                            $param_medications = $_POST["medications"];
                        }

                        // Attempt to execute the second prepared statement
                        if(!mysqli_stmt_execute($stmt2)){
                            echo "Oops! Something went wrong. Please try again later.";
                        }

                        mysqli_stmt_close($stmt2);
                    }
                }

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
    // Prepare a select statement based on user role
    $sql = "";
    switch ($_SESSION["role"]) {
        case 'patient':
            $sql = "SELECT u.username, u.dob, u.contact, p.conditions, p.medications 
                    FROM users u
                    INNER JOIN patients p ON u.username = p.username 
                    WHERE u.id = ?";
            break;
        case 'doctor':
            $sql = "SELECT u.username, u.dob, u.contact, d.specialization, d.experience 
                    FROM users u
                    INNER JOIN doctors d ON u.username = d.username 
                    WHERE u.id = ?";
            break;
        case 'pharmacist':
            $sql = "SELECT u.username, u.dob, u.contact, p.pharmacy_name, p.license_number 
                    FROM users u
                    INNER JOIN pharmacists p ON u.username = p.username 
                    WHERE u.id = ?";
            break;
        case 'admin':
            $sql = "SELECT username, dob, contact FROM users WHERE id = ?";
            break;
        default:
            // Handle cases for other roles or redirect to an error page
            // header("location: error.php");
            // exit();
            echo "Invalid user role.";
    }

    if ($stmt = mysqli_prepare($conn, $sql)) {
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "i", $param_id);

        // Set parameters
        $param_id = $_SESSION["id"];

        // Attempt to execute the prepared statement
        if (mysqli_stmt_execute($stmt)) {
            // Store result
            mysqli_stmt_store_result($stmt);

            // Check if user exists
            if (mysqli_stmt_num_rows($stmt) == 1) {
                // Bind result variables based on user role
                switch ($_SESSION["role"]) {
                    case 'patient':
                        mysqli_stmt_bind_result($stmt, $username, $dob, $contact, $conditions, $medications);
                        break;
                    case 'doctor':
                        mysqli_stmt_bind_result($stmt, $username, $dob, $contact, $specialization, $experience);
                        break;
                    case 'pharmacist':
                        mysqli_stmt_bind_result($stmt, $username, $dob, $contact, $pharmacy_name, $license_number);
                        break;
                    case 'admin':
                        mysqli_stmt_bind_result($stmt, $username, $dob, $contact);
                        break;
                }

                if (mysqli_stmt_fetch($stmt)) {
                    // Fetch and assign values to variables
                    // No need to echo the user ID here
                }
            } else {
                // URL doesn't contain valid id. Redirect to error page
                 header("location: error.php");
                 exit();
                echo "User not found.";
            }
        } else {
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

            <?php if ($_SESSION["role"] == 'patient'): ?>
                <div class="form-group">
                    <label>Medical Conditions (Optional)</label>
                    <textarea name="conditions" class="form-control"><?php echo $conditions; ?></textarea>
                </div>
                <div class="form-group">
                    <label>Current Medications (Optional)</label>
                    <textarea name="medications" class="form-control"><?php echo $medications; ?></textarea>
                </div>
            <?php elseif ($_SESSION["role"] == 'doctor'): ?>
                <div class="form-group">
                    <label>Specialization</label>
                    <input type="text" name="specialization" class="form-control" value="<?php echo $specialization; ?>">
                </div>
                <div class="form-group">
                    <label>Experience</label>
                    <input type="text" name="experience" class="form-control" value="<?php echo $experience; ?>">
                </div>
            <?php elseif ($_SESSION["role"] == 'pharmacist'): ?>
                <div class="form-group">
                    <label>Pharmacy Name</label>
                    <input type="text" name="pharmacy_name" class="form-control" value="<?php echo $pharmacy_name; ?>">
                </div>
                <div class="form-group">
                    <label>License Number</label>
                    <input type="text" name="license_number" class="form-control" value="<?php echo $license_number; ?>">
                </div>
            <?php endif; ?>

            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Update Profile">
            </div>
        </form>
    </div>
    <?php include "../includes/footer.php"; ?>
</body>
</html>