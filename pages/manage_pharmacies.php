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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style>
        .wrapper {
            padding: 20px;
        }
        #map {
            height: 400px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .coordinates-display {
            margin-top: 10px;
            font-size: 0.9em;
            color: #666;
        }
        .card {
            margin-bottom: 20px;
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }
        .table th {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container-fluid">
            <!-- Page Header -->
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
    </div>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        // Initialize map centered on Sri Lanka
        var map = L.map('map').setView([7.8731, 80.7718], 8);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        var marker;

        map.on('click', function(e) {
            if (marker) {
                map.removeLayer(marker);
            }
            marker = L.marker(e.latlng).addTo(map);
            
            document.getElementById('latitude').value = e.latlng.lat;
            document.getElementById('longitude').value = e.latlng.lng;
            document.getElementById('selectedLat').textContent = e.latlng.lat.toFixed(6);
            document.getElementById('selectedLng').textContent = e.latlng.lng.toFixed(6);
        });

        function showOnMap(lat, lng) {
            map.setView([lat, lng], 15);
            if (marker) {
                map.removeLayer(marker);
            }
            marker = L.marker([lat, lng]).addTo(map);
        }
    </script>
    <!-- Add these scripts just before </body> -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>