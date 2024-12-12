<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Check if the user has the 'admin' role, if not then redirect to home page
if ($_SESSION["role"] !== 'admin') {
    header("location: home.php");
    exit;
}

// Include config file
require_once "../includes/db_connect.php";

// Define variables and initialize with empty values
$recipient_id = $message = "";
$recipient_id_err = $message_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate recipient ID
    if (empty(trim($_POST["recipient_id"]))) {
        $recipient_id_err = "Please enter a recipient ID.";
    } else {
        $recipient_id = trim($_POST["recipient_id"]);
    }

    // Validate message
    if (empty(trim($_POST["message"]))) {
        $message_err = "Please enter a message.";
    } else {
        $message = trim($_POST["message"]);
    }

    // Check input errors before inserting in database
    if (empty($recipient_id_err) && empty($message_err)) {

        // Prepare an insert statement
        $sql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "is", $param_recipient_id, $param_message);

            // Set parameters
            $param_recipient_id = $recipient_id;
            $param_message = $message;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Redirect to send notifications page
                header("location: send_notifications.php");
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
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
    <title>Send Notifications</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
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
            width: 80%;
            max-width: 1200px; 
            margin: 50px auto; 
            flex-grow: 1; 
            width: 1000px;
        }

        .wrapper h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .wrapper .btn {
            display: inline-block; 
            margin-bottom: 10px; 
            margin-right: 10px; 
        }

        .wrapper a {
            color: #fff; 
        }

        .table {
            width: 100%;
            max-width: 100%; 
            margin-bottom: 20px;
        }

        .table th, .table td {
            padding: 10px;
            vertical-align: middle; 
        }
        .wrapper a {
            color: #000; 
        }
        </style>
</head>
<body>
    <div class="wrapper">
        <?php include "../includes/header.php"; ?>

        <h2>Send Notifications</h2>

        <h3>Send New Notification</h3>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Recipient ID</label>
                <input type="text" name="recipient_id" class="form-control <?php echo (!empty($recipient_id_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $recipient_id; ?>">
                <span class="invalid-feedback"><?php echo $recipient_id_err; ?></span>
            </div>
            <div class="form-group">
                <label>Message</label>
                <textarea name="message" class="form-control <?php echo (!empty($message_err)) ? 'is-invalid' : ''; ?>"><?php echo $message; ?></textarea>
                <span class="invalid-feedback"><?php echo $message_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Send">
                <input type="reset" class="btn btn-secondary ml-2" value="Reset">
            </div>
        </form>

        <?php include "../includes/footer.php"; ?>
    </div>
</body>
</html>