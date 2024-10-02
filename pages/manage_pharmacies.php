<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Check if the user has the 'admin' role, if not then redirect to home page
if($_SESSION["role"] !== 'admin'){
    header("location: home.php");
    exit;
}

// Include config file
require_once "../includes/db_connect.php";

// Define variables and initialize with empty values
$name = $address = $latitude = $longitude = $contact_information = $opening_hours = "";
$name_err = $address_err = $latitude_err = $longitude_err = $contact_information_err = $opening_hours_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validate name
    if(empty(trim($_POST["name"]))){
        $name_err = "Please enter a pharmacy name.";
    } else{
        $name = trim($_POST["name"]);
    }

    // Validate address
    if(empty(trim($_POST["address"]))){
        $address_err = "Please enter an address.";     
    } else{
        $address = trim($_POST["address"]);
    }

    // Validate latitude
    if(empty(trim($_POST["latitude"]))){
        $latitude_err = "Please enter a latitude.";     
    } else{
        $latitude = trim($_POST["latitude"]);
    }

    // Validate longitude
    if(empty(trim($_POST["longitude"]))){
        $longitude_err = "Please enter a longitude.";     
    } else{
        $longitude = trim($_POST["longitude"]);
    }

    // Validate contact information (optional)
    $contact_information = trim($_POST["contact_information"]);

    // Validate opening hours (optional)
    $opening_hours = trim($_POST["opening_hours"]);
    
    // Check input errors before inserting in database
    if(empty($name_err) && empty($address_err) && empty($latitude_err) && empty($longitude_err)){
        
        // Prepare an insert statement
        $sql = "INSERT INTO pharmacies (name, address, latitude, longitude, contact_information, opening_hours) VALUES (?, ?, ?, ?, ?, ?)";
         
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ssssss", $param_name, $param_address, $param_latitude, $param_longitude, $param_contact_information, $param_opening_hours);
            
            // Set parameters
            $param_name = $name;
            $param_address = $address;
            $param_latitude = $latitude;
            $param_longitude = $longitude;
            $param_contact_information = $contact_information;
            $param_opening_hours = $opening_hours;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Redirect to pharmacy management page
                header("location: manage_pharmacies.php");
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }

    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pharmacy Management</title>
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
            width: 80%;
            max-width: 1200px; 
            margin: 50px auto; 
            flex-grow: 1; 
            width: 800px;
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

        <h2>Pharmacy Management</h2>

        <h3>Add New Pharmacy</h3>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
                <span class="invalid-feedback"><?php echo $name_err; ?></span>
            </div>    
            <div class="form-group">
                <label>Address</label>
                <textarea name="address" class="form-control <?php echo (!empty($address_err)) ? 'is-invalid' : ''; ?>"><?php echo $address; ?></textarea>
                <span class="invalid-feedback"><?php echo $address_err; ?></span>
            </div>
            <div class="form-group">
                <label>Latitude</label>
                <input type="text" name="latitude" class="form-control <?php echo (!empty($latitude_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $latitude; ?>">
                <span class="invalid-feedback"><?php echo $latitude_err; ?></span>
            </div>
            <div class="form-group">
                <label>Longitude</label>
                <input type="text" name="longitude" class="form-control <?php echo (!empty($longitude_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $longitude; ?>">
                <span class="invalid-feedback"><?php echo $longitude_err; ?></span>
            </div>
            <div class="form-group">
                <label>Contact Information (Optional)</label>
                <input type="text" name="contact_information" class="form-control" value="<?php echo $contact_information; ?>">
            </div>
            <div class="form-group">
                <label>Opening Hours (Optional)</label>
                <input type="text" name="opening_hours" class="form-control" value="<?php echo $opening_hours; ?>">
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Add">
                <input type="reset" class="btn btn-secondary ml-2" value="Reset">
            </div>
        </form>

        <h3>Existing Pharmacies</h3>

        <?php
        // Fetch all pharmacies from the database
        $sql = "SELECT id, name, address, latitude, longitude, contact_information, opening_hours FROM pharmacies"; 
        $result = $conn->query($sql);

        // Error handling
        if (!$result) {
            die("Error fetching pharmacies: " . $conn->error);
        }
        ?>

        <table class="table table-bordered"> 
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>Contact Information</th>
                    <th>Opening Hours</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row["id"]); ?></td> 
                        <td><?php echo htmlspecialchars($row["name"]); ?></td> 
                        <td><?php echo htmlspecialchars($row["address"]); ?></td> 
                        <td><?php echo htmlspecialchars($row["latitude"]); ?></td> 
                        <td><?php echo htmlspecialchars($row["longitude"]); ?></td> 
                        <td><?php echo htmlspecialchars($row["contact_information"]); ?></td> 
                        <td><?php echo htmlspecialchars($row["opening_hours"]); ?></td> 
                        <td>
                            <a href="edit_pharmacy.php?id=<?php echo htmlspecialchars($row["id"]); ?>">Edit</a> |
                            <a href="delete_pharmacy.php?id=<?php echo htmlspecialchars($row["id"]); ?>" onclick="return confirm('Are you sure you want to delete this pharmacy?');">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <?php
        // Close connection
    mysqli_close($conn);

        include "../includes/footer.php";
        ?>
    </div>
</body>
</html>