<?php
session_start();

// Check if user is logged in and has admin role
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("location: login.php");
    exit;
}

require_once "../includes/db_connect.php";

// Fetch pharmacy statistics
$stats_sql = "SELECT 
    ph.pharmacy_id,
    ph.name,
    ph.address,
    ph.latitude,
    ph.longitude,
    ph.contact_information,
    ph.opening_hours,
    COUNT(p.id) as total_prescriptions,
    COUNT(CASE WHEN p.refill_status = 'refill_requested' THEN 1 END) as pending_refills,
    COUNT(DISTINCT p.user_id) as unique_patients,
    COUNT(DISTINCT pharm.user_id) as pharmacist_count
    FROM pharmacies ph
    LEFT JOIN prescriptions p ON ph.pharmacy_id = p.pharmacy_id
    LEFT JOIN pharmacists pharm ON ph.pharmacy_id = pharm.pharmacy_id
    GROUP BY ph.pharmacy_id";

$result = $conn->query($stats_sql);
$pharmacies = [];
while($row = $result->fetch_assoc()) {
    $pharmacies[] = $row;
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Pharmacy Statistics and Locations</h2>
        <a href="?page=manage_pharmacies" class="btn btn-primary">
            <i class="fas fa-plus"></i> Manage Pharmacies
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Map Container -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Pharmacy Locations</h5>
                </div>
                <div class="card-body">
                    <div id="pharmacyMap" style="height: 500px;"></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Statistics Summary -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Overall Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span>Total Pharmacies:</span>
                        <strong><?php echo count($pharmacies); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Total Prescriptions:</span>
                        <strong><?php 
                            echo array_sum(array_column($pharmacies, 'total_prescriptions')); 
                        ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Total Pharmacists:</span>
                        <strong><?php 
                            echo array_sum(array_column($pharmacies, 'pharmacist_count')); 
                        ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Statistics Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-table"></i> Detailed Pharmacy Statistics</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Pharmacy Name</th>
                            <th>Location</th>
                            <th>Contact</th>
                            <th>Hours</th>
                            <th>Prescriptions</th>
                            <th>Pending Refills</th>
                            <th>Unique Patients</th>
                            <th>Pharmacists</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($pharmacies as $pharmacy): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pharmacy['name']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($pharmacy['address']); ?>
                                    <button class="btn btn-sm btn-link" 
                                            onclick="centerMap(<?php echo $pharmacy['latitude']; ?>, 
                                                             <?php echo $pharmacy['longitude']; ?>)">
                                        <i class="fas fa-map-marker"></i>
                                    </button>
                                </td>
                                <td><?php echo htmlspecialchars($pharmacy['contact_information']); ?></td>
                                <td><?php echo htmlspecialchars($pharmacy['opening_hours']); ?></td>
                                <td><?php echo $pharmacy['total_prescriptions']; ?></td>
                                <td>
                                    <span class="badge badge-warning">
                                        <?php echo $pharmacy['pending_refills']; ?>
                                    </span>
                                </td>
                                <td><?php echo $pharmacy['unique_patients']; ?></td>
                                <td><?php echo $pharmacy['pharmacist_count']; ?></td>
                                <td>
                                    <a href="?page=view_pharmacy_details&id=<?php echo $pharmacy['pharmacy_id']; ?>" 
                                       class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add required styles -->
<style>
.card {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    margin-bottom: 1.5rem;
}
.table td, .table th {
    vertical-align: middle;
}
</style>

<!-- Add Leaflet CSS and JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

<script>
// Initialize map
var map = L.map('pharmacyMap');
var markers = [];

// Add OpenStreetMap tiles
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Add pharmacy markers
<?php foreach($pharmacies as $pharmacy): ?>
    var marker = L.marker([
        <?php echo $pharmacy['latitude']; ?>, 
        <?php echo $pharmacy['longitude']; ?>
    ]).addTo(map);
    
    marker.bindPopup(`
        <strong><?php echo htmlspecialchars($pharmacy['name']); ?></strong><br>
        <?php echo htmlspecialchars($pharmacy['address']); ?><br>
        <small><?php echo htmlspecialchars($pharmacy['contact_information']); ?></small>
    `);
    
    markers.push(marker);
<?php endforeach; ?>

// Fit map to show all markers
var group = new L.featureGroup(markers);
map.fitBounds(group.getBounds().pad(0.1));

function centerMap(lat, lng) {
    map.setView([lat, lng], 15);
}
</script>

<?php $conn->close(); ?>