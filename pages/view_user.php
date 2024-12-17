<?php
session_start();

// Check if the user is logged in and has admin role
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("location: login.php");
    exit;
}

require_once "../includes/db_connect.php";

if(!isset($_GET["id"])) {
    header("location: home.php");
    exit;
}

$user_id = $_GET["id"];

// Fetch user details including role-specific information
$sql = "SELECT u.*, 
        CASE 
            WHEN u.role = 'doctor' THEN d.specialization
            WHEN u.role = 'patient' THEN p.conditions
            WHEN u.role = 'pharmacist' THEN ph.pharmacy_name
            ELSE NULL 
        END as additional_info,
        CASE 
            WHEN u.role = 'doctor' THEN d.hospital
            WHEN u.role = 'patient' THEN p.emergency_contact_1
            WHEN u.role = 'pharmacist' THEN ph.license_number
            ELSE NULL 
        END as secondary_info
        FROM users u
        LEFT JOIN doctors d ON u.id = d.user_id
        LEFT JOIN patients p ON u.id = p.user_id
        LEFT JOIN pharmacists ph ON u.id = ph.user_id
        WHERE u.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
    header("location: home.php");
    exit;
}

$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management</title>
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
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">User Details</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Basic Information</h4>
                            <table class="table">
                                <tr>
                                    <th>Username:</th>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Role:</th>
                                    <td><span class="badge bg-info"><?php echo ucfirst(htmlspecialchars($user['role'])); ?></span></td>
                                </tr>
                                <tr>
                                    <th>Contact:</th>
                                    <td><?php echo htmlspecialchars($user['contact']); ?></td>
                                </tr>
                                <tr>
                                    <th>Date of Birth:</th>
                                    <td><?php echo htmlspecialchars($user['dob']); ?></td>
                                </tr>
                                <tr>
                                    <th>Joined Date:</th>
                                    <td><?php echo date('F d, Y', strtotime($user['created_at'])); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h4>Role-Specific Information</h4>
                            <?php if($user['role'] === 'doctor'): ?>
                                <table class="table">
                                    <tr>
                                        <th>Specialization:</th>
                                        <td><?php echo htmlspecialchars($user['additional_info']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Hospital:</th>
                                        <td><?php echo htmlspecialchars($user['secondary_info']); ?></td>
                                    </tr>
                                </table>
                            <?php elseif($user['role'] === 'patient'): ?>
                                <table class="table">
                                    <tr>
                                        <th>Conditions:</th>
                                        <td><?php echo htmlspecialchars($user['additional_info']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Emergency Contact:</th>
                                        <td><?php echo htmlspecialchars($user['secondary_info']); ?></td>
                                    </tr>
                                </table>
                            <?php elseif($user['role'] === 'pharmacist'): ?>
                                <table class="table">
                                    <tr>
                                        <th>Pharmacy:</th>
                                        <td><?php echo htmlspecialchars($user['additional_info']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>License Number:</th>
                                        <td><?php echo htmlspecialchars($user['secondary_info']); ?></td>
                                    </tr>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="manage_users" class="btn btn-secondary">Back to Users List</a>
                    <a href="edit_user?id=<?php echo $user_id; ?>" class="btn btn-primary">Edit User</a>
                </div>
            </div>
        </div>
    </div>
</div>
<br>
<?php
//$stmt->close();
$conn->close();
include "../includes/footer.php";
?>
</body>
</html>

