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
                                           <td><?php echo htmlspecialchars($pharmacist["username"] ?? ''); ?></td> 
                                           <td><?php echo htmlspecialchars($pharmacist["contact"] ?? ''); ?></td> 
                                           <td><?php echo htmlspecialchars($pharmacist["email"] ?? ''); ?></td> 
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