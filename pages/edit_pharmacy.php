<?php
session_start();

// Check if the user is logged in and has admin role
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: login.php");
    exit;
}

require_once "../includes/db_connect.php";

// Define variables and initialize with empty values
$name = $address = $latitude = $longitude = $contact_information = $opening_hours = "";
$name_err = $address_err = $latitude_err = $longitude_err = "";

// Processing form data when form is submitted
if(isset($_POST["pharmacy_id"]) && !empty($_POST["pharmacy_id"])){
    $pharmacy_id = $_POST["pharmacy_id"];
    
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
    
    // Validate coordinates
    if(empty(trim($_POST["latitude"])) || empty(trim($_POST["longitude"]))){
        $latitude_err = $longitude_err = "Please select a location on the map.";
    } else{
        $latitude = trim($_POST["latitude"]);
        $longitude = trim($_POST["longitude"]);
    }
    
    $contact_information = trim($_POST["contact_information"]);
    $opening_hours = trim($_POST["opening_hours"]);
    
    // Check input errors before updating
    if(empty($name_err) && empty($address_err) && empty($latitude_err) && empty($longitude_err)){
        $sql = "UPDATE pharmacies SET name=?, address=?, latitude=?, longitude=?, contact_information=?, opening_hours=? WHERE pharmacy_id=?";
        
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("ssddssi", $name, $address, $latitude, $longitude, $contact_information, $opening_hours, $pharmacy_id);
            
            if($stmt->execute()){
                header("location: ?page=manage_pharmacies");
                exit();
            } else{
                echo "Error updating pharmacy: " . $conn->error;
            }
            $stmt->close();
        }
    }
    
} else {
    // Check existence of pharmacy_id parameter before processing further
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
        $pharmacy_id = trim($_GET["id"]);
        
        $sql = "SELECT * FROM pharmacies WHERE pharmacy_id = ?";
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("i", $pharmacy_id);
            if($stmt->execute()){
                $result = $stmt->get_result();
                
                if($result->num_rows == 1){
                    $row = $result->fetch_assoc();
                    
                    $name = $row["name"];
                    $address = $row["address"];
                    $latitude = $row["latitude"];
                    $longitude = $row["longitude"];
                    $contact_information = $row["contact_information"];
                    $opening_hours = $row["opening_hours"];
                } else{
                    header("location: error.php");
                    exit();
                }
                
            } else{
                echo "Error retrieving pharmacy details: " . $conn->error;
            }
        }
        
        $stmt->close();
    } else{
        header("location: error.php");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Pharmacies</title>
    <title>User Management</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>

    <style>
        body{ font: 14px sans-serif; 
            text-align: center;
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa; 
        }
        .wrapper {
            background: #fff;
            border-radius: 10px; /* More rounded corners */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 80%;
            max-width: 1200px;
            margin: 30px auto;
        }
        h2 {
            text-align: center;
            margin-bottom: 30px; /* Increased margin */
            color: #343a40;
            font-weight: 700;
        }

        h3 {
            color: #343a40;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            border-radius: 5px; /* Rounded input fields */
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            transition: background-color 0.2s ease; /* Smooth transition */
        }

        .btn-primary:hover {
            background-color: #0062cc; /* Darker shade on hover */
        }

        .table {
            width: 100%;
            max-width: 100%;
            margin-top: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-collapse: separate; /* Add space between cells */
            border-spacing: 0 10px; /* Adjust spacing as needed */
        }

        .table th, .table td {
            padding: 15px;
            vertical-align: middle;
            background-color: #fff; /* White background for cells */
            border-radius: 5px; /* Rounded cell corners */
        }

        .table th {
            background-color: #f8f9fa; /* Light background for header */
            font-weight: 700;
            color: #343a40;
        }

        .table-bordered th,
        .table-bordered td {
            border: none; /* Remove default border */
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
        }

        .fa {
            margin-right: 5px;
        }
    </style>
    <style>
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }
        .large {
            font-size: 2.5rem;
            font-weight: 700;
        }
        .text-white-50 {
            color: rgba(255, 255, 255, 0.8) !important;
        }
</style>
    <style>
        .wrapper { padding: 20px; }
        .search-box { margin-bottom: 20px; }
        .role-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85em;
        }
        .role-doctor { background-color: #cce5ff; color: #004085; }
        .role-patient { background-color: #d4edda; color: #155724; }
        .role-pharmacist { background-color: #fff3cd; color: #856404; }
        .role-admin { background-color: #f8d7da; color: #721c24; }
        .action-buttons { white-space: nowrap; }
    </style>
</head>
<body>
    <div class="wrapper">
    <?php include "../includes/header.php"; ?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Pharmacy</h3>
        </div>
        <div class="card-body">
            <form action="<?php echo '?page=edit_pharmacy'; ?>" method="post">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Name*</label>
                            <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
                            <div class="invalid-feedback"><?php echo $name_err; ?></div>
                        </div>
                        <div class="form-group">
                            <label>Address*</label>
                            <textarea name="address" class="form-control <?php echo (!empty($address_err)) ? 'is-invalid' : ''; ?>"><?php echo $address; ?></textarea>
                            <div class="invalid-feedback"><?php echo $address_err; ?></div>
                        </div>
                        <div class="form-group">
                            <label>Contact Information</label>
                            <input type="text" name="contact_information" class="form-control" value="<?php echo $contact_information; ?>">
                        </div>
                        <div class="form-group">
                            <label>Opening Hours</label>
                            <input type="text" name="opening_hours" class="form-control" value="<?php echo $opening_hours; ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label>Location (Click on map to update)*</label>
                        <div id="map" style="height: 400px; margin-bottom: 20px; border-radius: 8px;"></div>
                        <div class="coordinates-display">
                            Selected location: 
                            <span id="selectedLat"><?php echo $latitude; ?></span>, 
                            <span id="selectedLng"><?php echo $longitude; ?></span>
                        </div>
                        <input type="hidden" name="latitude" id="latitude" value="<?php echo $latitude; ?>">
                        <input type="hidden" name="longitude" id="longitude" value="<?php echo $longitude; ?>">
                    </div>
                </div>
                
                <input type="hidden" name="pharmacy_id" value="<?php echo $pharmacy_id; ?>"/>
                <div class="form-group mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="?page=manage_pharmacies" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
    var map = L.map('map').setView([<?php echo $latitude; ?>, <?php echo $longitude; ?>], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    var marker = L.marker([<?php echo $latitude; ?>, <?php echo $longitude; ?>]).addTo(map);

    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        document.getElementById('latitude').value = e.latlng.lat;
        document.getElementById('longitude').value = e.latlng.lng;
        document.getElementById('selectedLat').textContent = e.latlng.lat.toFixed(6);
        document.getElementById('selectedLng').textContent = e.latlng.lng.toFixed(6);
    });
</script>

<?php include "../includes/footer.php"; ?>
    
    </div>


</body>
</html>