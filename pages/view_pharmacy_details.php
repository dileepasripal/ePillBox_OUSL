<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
   header("location: login.php");
   exit;
}

require_once "../includes/db_connect.php";

if(!isset($_GET["id"])) {
   header("location: home.php");
   exit; 
}

$pharmacy_id = $_GET["id"];

$sql = "SELECT p.*,
       COUNT(DISTINCT ps.id) as total_prescriptions,
       COUNT(DISTINCT ph.user_id) as total_pharmacists
       FROM pharmacies p
       LEFT JOIN prescriptions ps ON p.pharmacy_id = ps.pharmacy_id
       LEFT JOIN pharmacists ph ON p.pharmacy_id = ph.pharmacy_id
       WHERE p.pharmacy_id = ?
       GROUP BY p.pharmacy_id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pharmacy_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
   header("location: home.php");
   exit;
}

$pharmacy = $result->fetch_assoc();

// Get pharmacists
$pharmacists_sql = "SELECT u.username, u.email, u.contact
                   FROM pharmacists ph 
                   JOIN users u ON ph.user_id = u.id
                   WHERE ph.pharmacy_id = ?";
$pharmacists_stmt = $conn->prepare($pharmacists_sql);
$pharmacists_stmt->bind_param("i", $pharmacy_id);
$pharmacists_stmt->execute();
$pharmacists = $pharmacists_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
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
   <div class="row mb-4">
       <div class="col-12">
           <div class="d-flex justify-content-between align-items-center">
               <h2>Pharmacy Details</h2>
               <a href="manage_pharmacies" class="btn btn-secondary">
                   <i class="fas fa-arrow-left"></i> Back to List
               </a>
           </div>
       </div>
   </div>

   <div class="row">
       <div class="col-md-6">
           <!-- Basic Info Card -->
           <div class="card mb-4">
               <div class="card-header">
                   <h5 class="mb-0"><i class="fas fa-info-circle"></i> Basic Information</h5>
               </div>
               <div class="card-body">
                   <table class="table">
                       <tr>
                           <th>Name:</th>
                           <td><?php echo htmlspecialchars($pharmacy["name"]); ?></td>
                       </tr>
                       <tr>
                           <th>Address:</th>
                           <td><?php echo htmlspecialchars($pharmacy["address"]); ?></td>
                       </tr>
                       <tr>
                           <th>Contact:</th>
                           <td><?php echo htmlspecialchars($pharmacy["contact_information"]); ?></td>
                       </tr>
                       <tr>
                           <th>Hours:</th>
                           <td><?php echo htmlspecialchars($pharmacy["opening_hours"]); ?></td>
                       </tr>
                   </table>
               </div>
           </div>

           <!-- Statistics Card -->
           <div class="card mb-4">
               <div class="card-header">
                   <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Statistics</h5>
               </div>
               <div class="card-body">
                   <div class="row text-center">
                       <div class="col-6">
                           <h3><?php echo $pharmacy["total_prescriptions"]; ?></h3>
                           <p class="text-muted">Total Prescriptions</p>
                       </div>
                       <div class="col-6">
                           <h3><?php echo $pharmacy["total_pharmacists"]; ?></h3>
                           <p class="text-muted">Pharmacists</p>
                       </div>
                   </div>
               </div>
           </div>
       </div>

       <div class="col-md-6">
           <!-- Location Card -->
           <div class="card mb-4">
               <div class="card-header">
                   <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Location</h5>
               </div>
               <div class="card-body">
                   <div id="map" style="height: 300px;"></div>
               </div>
           </div>

           <!-- Pharmacists Card -->
           <div class="card">
               <div class="card-header">
                   <h5 class="mb-0"><i class="fas fa-users"></i> Pharmacists</h5>
               </div>
               <div class="card-body">
                   <?php if($pharmacists->num_rows > 0): ?>
                       <div class="table-responsive">
                           <table class="table">
                               <thead>
                                   <tr>
                                       <th>Name</th>
                                       <th>Contact</th>
                                       <th>Email</th>
                                   </tr>
                               </thead>
                               <tbody>
                                   <?php while($pharmacist = $pharmacists->fetch_assoc()): ?>
                                       <tr>
                                           <td><?php echo htmlspecialchars($pharmacist["username"]); ?></td>
                                           <td><?php echo htmlspecialchars($pharmacist["contact"]); ?></td>
                                           <td><?php echo htmlspecialchars($pharmacist["email"]); ?></td>
                                       </tr>
                                   <?php endwhile; ?>
                               </tbody>
                           </table>
                       </div>
                   <?php else: ?>
                       <p class="text-muted mb-0">No pharmacists assigned yet.</p>
                   <?php endif; ?>
               </div>
           </div>
       </div>
   </div>
</div>

<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
var map = L.map('map').setView([<?php echo $pharmacy["latitude"]; ?>, <?php echo $pharmacy["longitude"]; ?>], 15);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
   attribution: '© OpenStreetMap contributors'
}).addTo(map);

L.marker([<?php echo $pharmacy["latitude"]; ?>, <?php echo $pharmacy["longitude"]; ?>])
   .addTo(map)
   .bindPopup("<?php echo htmlspecialchars($pharmacy["name"]); ?>");
</script>

<?php
//$stmt->close();
$pharmacists_stmt->close();
$conn->close();
?>

<?php
include "../includes/footer.php";
?>
</div>
</body>
</html>