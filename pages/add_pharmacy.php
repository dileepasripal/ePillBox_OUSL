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
        // Check if pharmacy name exists
        $sql = "SELECT pharmacy_id FROM pharmacies WHERE name = ?";
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("s", $param_name);
            $param_name = trim($_POST["name"]);
            if($stmt->execute()){
                $result = $stmt->get_result();
                if($result->num_rows == 1){
                    $name_err = "This pharmacy name is already taken.";
                } else{
                    $name = trim($_POST["name"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
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

    // Optional fields
    $contact_information = trim($_POST["contact_information"]);
    $opening_hours = trim($_POST["opening_hours"]);
    
    // Check input errors before inserting in database
    if(empty($name_err) && empty($address_err) && empty($latitude_err) && empty($longitude_err)){
        // Prepare an insert statement
        $sql = "INSERT INTO pharmacies (name, address, latitude, longitude, contact_information, opening_hours) VALUES (?, ?, ?, ?, ?, ?)";
         
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("ssddss", $name, $address, $latitude, $longitude, $contact_information, $opening_hours);
            
            if($stmt->execute()){
                $_SESSION['success_message'] = "Pharmacy added successfully.";
                header("location: manage_pharmacies");
                exit();
            } else{
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Add New Pharmacy</h3>
                <a href="manage_pharmacies.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="<?php echo 'add_pharmacy'; ?>" method="post" id="addPharmacyForm">
                <div class="row">
                    <div class="col-md-6">
                        <!-- Basic Information -->
                        <div class="form-group">
                            <label>Pharmacy Name*</label>
                            <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
                            <div class="invalid-feedback"><?php echo $name_err; ?></div>
                        </div>

                        <div class="form-group">
                            <label>Address*</label>
                            <textarea name="address" class="form-control <?php echo (!empty($address_err)) ? 'is-invalid' : ''; ?>" rows="3"><?php echo $address; ?></textarea>
                            <div class="invalid-feedback"><?php echo $address_err; ?></div>
                        </div>

                        <div class="form-group">
                            <label>Contact Information</label>
                            <input type="text" name="contact_information" class="form-control" value="<?php echo $contact_information; ?>" placeholder="Phone, Email, etc.">
                        </div>

                        <div class="form-group">
                            <label>Opening Hours</label>
                            <input type="text" name="opening_hours" class="form-control" value="<?php echo $opening_hours; ?>" placeholder="e.g., Mon-Fri: 9AM-6PM">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <!-- Map Location Picker -->
                        <div class="form-group">
                            <label>Location* (Click on map to set location)</label>
                            <div id="map" style="height: 400px; border-radius: 8px; margin-bottom: 10px;"></div>
                            <div class="coordinates-display small text-muted">
                                Selected coordinates: 
                                <span id="selectedLat">Not set</span>, 
                                <span id="selectedLng">Not set</span>
                            </div>
                            <?php if(!empty($latitude_err)): ?>
                                <div class="text-danger small"><?php echo $latitude_err; ?></div>
                            <?php endif; ?>
                            <input type="hidden" name="latitude" id="latitude" value="<?php echo $latitude; ?>">
                            <input type="hidden" name="longitude" id="longitude" value="<?php echo $longitude; ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Pharmacy
                    </button>
                    <button type="reset" class="btn btn-secondary" onclick="resetMap()">
                        <i class="fas fa-undo"></i> Reset Form
                    </button>
                </div>
            </form>
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

    function resetMap() {
        if (marker) {
            map.removeLayer(marker);
        }
        map.setView([7.8731, 80.7718], 8);
        document.getElementById('latitude').value = '';
        document.getElementById('longitude').value = '';
        document.getElementById('selectedLat').textContent = 'Not set';
        document.getElementById('selectedLng').textContent = 'Not set';
    }
</script>

<?php $conn->close(); ?>