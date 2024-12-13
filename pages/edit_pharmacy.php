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