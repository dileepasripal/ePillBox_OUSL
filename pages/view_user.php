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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
                    <a href="?page=manage_users" class="btn btn-secondary">Back to Users List</a>
                    <a href="?page=edit_user&id=<?php echo $user_id; ?>" class="btn btn-primary">Edit User</a>
                </div>
            </div>
        </div>
    </div>
</div>
<br>
<?php
$stmt->close();
$conn->close();
include "../includes/footer.php";
?>
</body>
</html>

