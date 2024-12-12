<?php
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
$setting_name = $setting_value = "";
$setting_name_err = $setting_value_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate setting name
    if (empty(trim($_POST["setting_name"]))) {
        $setting_name_err = "Please enter a setting name.";
    } else {
        $setting_name = trim($_POST["setting_name"]);
    }

    // Validate setting value
    if (empty(trim($_POST["setting_value"]))) {
        $setting_value_err = "Please enter a setting value.";
    } else {
        $setting_value = trim($_POST["setting_value"]);
    }

    // Check input errors before inserting in database
    if (empty($setting_name_err) && empty($setting_value_err)) {

        // Prepare an insert statement
        $sql = "INSERT INTO settings (name, value) VALUES (?, ?)";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ss", $param_setting_name, $param_setting_value);

            // Set parameters
            $param_setting_name = $setting_name;
            $param_setting_value = $setting_value;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Redirect to system settings page
                header("location: system_settings.php");
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
    <title>System Settings</title>
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

        <h2>System Settings</h2>

        <h3>Add New Setting</h3>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Setting Name</label>
                <input type="text" name="setting_name" class="form-control <?php echo (!empty($setting_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $setting_name; ?>">
                <span class="invalid-feedback"><?php echo $setting_name_err; ?></span>
            </div>
            <div class="form-group">
                <label>Setting Value</label>
                <input type="text" name="setting_value" class="form-control <?php echo (!empty($setting_value_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $setting_value; ?>">
                <span class="invalid-feedback"><?php echo $setting_value_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Add">
                <input type="reset" class="btn btn-secondary ml-2" value="Reset">
            </div>
        </form>

        <h3>Existing Settings</h3>

        <?php
        // Fetch all settings from the database
        $sql = "SELECT id, name, value FROM settings";
        $result = $conn->query($sql);

        // Error handling
        if (!$result) {
            die("Error fetching settings: " . $conn->error);
        }
        ?>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Value</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row["id"]); ?></td>
                        <td><?php echo htmlspecialchars($row["name"]); ?></td>
                        <td><?php echo htmlspecialchars($row["value"]); ?></td>
                        <td>
                            <a href="edit_setting.php?id=<?php echo htmlspecialchars($row["id"]); ?>" class="btn btn-primary btn-sm mr-2"><i class="fa fa-pencil"></i> Edit</a>
                            <a href="delete_setting.php?id=<?php echo htmlspecialchars($row["id"]); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this setting?');"><i class="fa fa-trash"></i> Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <?php
        $conn->close();
        include "../includes/footer.php";
        ?>
    </div>
</body>
</html>