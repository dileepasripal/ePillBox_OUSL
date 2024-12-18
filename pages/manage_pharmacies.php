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
        $latitude_err = "Please select a location on the map.";     
    } else{
        $latitude = trim($_POST["latitude"]);
    }

    // Validate longitude
    if(empty(trim($_POST["longitude"]))){
        $longitude_err = "Please select a location on the map.";     
    } else{
        $longitude = trim($_POST["longitude"]);
    }

    // Process optional fields
    $contact_information = trim($_POST["contact_information"]);
    $opening_hours = trim($_POST["opening_hours"]);
    
    // Check input errors before inserting in database
    if(empty($name_err) && empty($address_err) && empty($latitude_err) && empty($longitude_err)){
        // Prepare an insert statement
        $sql = "INSERT INTO pharmacies (name, address, latitude, longitude, contact_information, opening_hours) VALUES (?, ?, ?, ?, ?, ?)";
         
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("ssddss", $name, $address, $latitude, $longitude, $contact_information, $opening_hours);
            
            if($stmt->execute()){
                header("location: manage_pharmacies");
                exit();
            } else{
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch all pharmacies
$sql = "SELECT * FROM pharmacies ORDER BY name ASC";
$result = $conn->query($sql);

if (!$result) {
    die("Error fetching pharmacies: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Pharmacies</title>
    <title>User Management</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
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
        #pharmacyMap {
    height: 400px;
    width: 100%;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
    border: 1px solid #dee2e6;
}

.leaflet-popup-content {
    padding: 1rem;
}

.leaflet-popup-content h3 {
    font-weight: 600;
    margin-bottom: 0.5rem;
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
            <!-- Page Header -->
            <div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Pharmacy Locations</h5>
    </div>
    <div class="card-body">
        <div id="pharmacyMap"></div>
    </div>
</div>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-dark mb-0">Manage Pharmacies</h2>
                <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#addPharmacyForm">
                    <i class="fas fa-plus"></i> Add New Pharmacy
                </button>
            </div>

            <!-- Add Pharmacy Form -->
            <div class="collapse" id="addPharmacyForm">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Add New Pharmacy</h5>
                    </div>
                    <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
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
                                    <label>Location (Click on map to set)*</label>
                                    <div id="map"></div>
                                    <div class="coordinates-display">
                                        Selected location: 
                                        <span id="selectedLat">Not set</span>, 
                                        <span id="selectedLng">Not set</span>
                                    </div>
                                    <input type="hidden" name="latitude" id="latitude">
                                    <input type="hidden" name="longitude" id="longitude">
                                </div>
                            </div>
                            <div class="form-group mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Pharmacy
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Pharmacies List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Existing Pharmacies</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th>Contact</th>
                                    <th>Hours</th>
                                    <th>Pharmacists</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        // Get pharmacist count
                                        $pharm_sql = "SELECT COUNT(*) as count FROM pharmacists WHERE pharmacy_id = ?";
                                        $pharm_stmt = $conn->prepare($pharm_sql);
                                        $pharm_stmt->bind_param("i", $row['pharmacy_id']);
                                        $pharm_stmt->execute();
                                        $pharm_count = $pharm_stmt->get_result()->fetch_assoc()['count'];
                                        $pharm_stmt->close();
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row["name"]); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($row["address"]); ?>
                                            <button class="btn btn-sm btn-link" onclick="showOnMap(<?php echo $row['latitude']; ?>, <?php echo $row['longitude']; ?>)">
                                                <i class="fas fa-map-marker-alt"></i>
                                            </button>
                                        </td>
                                        <td><?php echo htmlspecialchars($row["contact_information"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["opening_hours"]); ?></td>
                                        <td>
                                            <span class="badge badge-info">
                                                <?php echo $pharm_count; ?> Pharmacist(s)
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="edit_pharmacy?id=<?php echo $row["pharmacy_id"]; ?>" 
                                                   class="btn btn-primary btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete_pharmacy?id=<?php echo $row["pharmacy_id"]; ?>" 
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Are you sure you want to delete this pharmacy?');"
                                                   title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php 
                                    }
                                } else {
                                ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No pharmacies found</td>
                                    </tr>
                                <?php 
                                } 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    

        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        // Initialize map centered on Sri Lanka
var map = L.map('pharmacyMap').setView([7.8731, 80.7718], 8);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Add markers for each pharmacy
<?php 
$result->data_seek(0); // Reset result pointer
while($row = $result->fetch_assoc()) { 
?>
    L.marker([<?php echo $row['latitude']; ?>, <?php echo $row['longitude']; ?>])
        .addTo(map)
        .bindPopup(`
            <div class="popup-content">
                <h3><?php echo htmlspecialchars($row["name"]); ?></h3>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($row["address"]); ?></p>
                <p><strong>Contact:</strong> <?php echo htmlspecialchars($row["contact_information"]); ?></p>
                <p><strong>Hours:</strong> <?php echo htmlspecialchars($row["opening_hours"]); ?></p>
            </div>
        `);
<?php 
} 
?>

// Fit map to show all markers
var bounds = [];
<?php 
$result->data_seek(0);
while($row = $result->fetch_assoc()) { 
?>
    bounds.push([<?php echo $row['latitude']; ?>, <?php echo $row['longitude']; ?>]);
<?php 
} 
?>
if (bounds.length > 0) {
    map.fitBounds(bounds);
}
    </script>

<?php
$conn->close();
?>
<?php include "../includes/footer.php"; ?>
    
    </div>


</body>
</html>

