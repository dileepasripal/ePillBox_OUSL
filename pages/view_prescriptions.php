<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "../includes/db_connect.php";

$user_id = $_SESSION['id'];
$user_role = $_SESSION['role'];

// Handle filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'start_date';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Initialize SQL query variable
$sql = '';

// Base SQL query depending on user role
if($user_role === 'patient') {
    $sql = "SELECT p.*, 
            doc_user.username AS doctor_name,
            ph.name AS pharmacy_name
            FROM prescriptions p
            JOIN users doc_user ON p.doctor_id = doc_user.id
            JOIN pharmacies ph ON p.pharmacy_id = ph.pharmacy_id
            WHERE p.user_id = ?";
} else if($user_role === 'doctor') {
    $sql = "SELECT p.*, 
            u.username AS patient_name,
            ph.name AS pharmacy_name
            FROM prescriptions p
            JOIN users u ON p.user_id = u.id
            JOIN pharmacies ph ON p.pharmacy_id = ph.pharmacy_id
            WHERE p.doctor_id = ?";
} else {
    // Default query for other roles or handle appropriately
    $sql = "SELECT p.*, 
            u.username AS patient_name,
            doc_user.username AS doctor_name,
            ph.name AS pharmacy_name
            FROM prescriptions p
            JOIN users u ON p.user_id = u.id
            JOIN users doc_user ON p.doctor_id = doc_user.id
            JOIN pharmacies ph ON p.pharmacy_id = ph.pharmacy_id
            WHERE 1=1";
}

// Add filters to SQL query
if($status_filter) {
    $sql .= " AND p.refill_status = ?";
}
if($date_filter) {
    $sql .= " AND DATE(p.start_date) = ?";
}

// Add sorting
$sql .= " ORDER BY p.$sort $order";

// Prepare and execute statement with appropriate binding
$stmt = $conn->prepare($sql);

// Bind parameters based on filters
if($user_role === 'patient' || $user_role === 'doctor') {
    if($status_filter && $date_filter) {
        $stmt->bind_param("iss", $user_id, $status_filter, $date_filter);
    } else if($status_filter) {
        $stmt->bind_param("is", $user_id, $status_filter);
    } else if($date_filter) {
        $stmt->bind_param("is", $user_id, $date_filter);
    } else {
        $stmt->bind_param("i", $user_id);
    }
} else {
    if($status_filter && $date_filter) {
        $stmt->bind_param("ss", $status_filter, $date_filter);
    } else if($status_filter) {
        $stmt->bind_param("s", $status_filter);
    } else if($date_filter) {
        $stmt->bind_param("s", $date_filter);
    }
}

$stmt->execute();
$result = $stmt->get_result();
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
    <!-- Filter and Sort Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row align-items-end">
                <input type="hidden" name="page" value="view_prescriptions">
                <div class="col-md-3 mb-2">
                    <label>Filter by Status</label>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="new" <?php echo $status_filter === 'new' ? 'selected' : ''; ?>>New</option>
                        <option value="refill_requested" <?php echo $status_filter === 'refill_requested' ? 'selected' : ''; ?>>Refill Requested</option>
                        <option value="refilled" <?php echo $status_filter === 'refilled' ? 'selected' : ''; ?>>Refilled</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label>Filter by Date</label>
                    <input type="date" name="date" class="form-control" value="<?php echo $date_filter; ?>" onchange="this.form.submit()">
                </div>
                <div class="col-md-3 mb-2">
                    <label>Sort by</label>
                    <select name="sort" class="form-control" onchange="this.form.submit()">
                        <option value="start_date" <?php echo $sort === 'start_date' ? 'selected' : ''; ?>>Start Date</option>
                        <option value="medication_name" <?php echo $sort === 'medication_name' ? 'selected' : ''; ?>>Medication Name</option>
                        <option value="updated_at" <?php echo $sort === 'updated_at' ? 'selected' : ''; ?>>Last Updated</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <a href="view_prescriptions" class="btn btn-secondary btn-block">Reset Filters</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Prescriptions Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="fas fa-prescription-bottle-alt"></i> 
                Prescriptions List
            </h3>
        </div>
        <div class="card-body">
            <?php if($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Medication</th>
                                <?php if($user_role === 'doctor'): ?>
                                    <th>Patient</th>
                                <?php else: ?>
                                    <th>Doctor</th>
                                <?php endif; ?>
                                <th>Pharmacy</th>
                                <th>Dosage & Frequency</th>
                                <th>Dates</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row["medication_name"]); ?></td>
                                    <?php if($user_role === 'doctor'): ?>
                                        <td><?php echo htmlspecialchars($row["patient_name"]); ?></td>
                                    <?php else: ?>
                                        <td><?php echo htmlspecialchars($row["doctor_name"]); ?></td>
                                    <?php endif; ?>
                                    <td><?php echo htmlspecialchars($row["pharmacy_name"]); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($row["dosage"]); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($row["frequency"]); ?></small>
                                    </td>
                                    <td>
                                        Start: <?php echo date('M d, Y', strtotime($row["start_date"])); ?><br>
                                        <small class="text-muted">
                                            End: <?php echo $row["end_date"] ? date('M d, Y', strtotime($row["end_date"])) : 'Not specified'; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge <?php 
                                            echo $row["refill_status"] === 'new' ? 'badge-info' : 
                                                ($row["refill_status"] === 'refill_requested' ? 'badge-warning' : 'badge-success'); 
                                        ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $row["refill_status"])); ?>
                                        </span>
                                        <?php if($row["request_status"] === 'pending'): ?>
                                            <br><span class="badge badge-secondary">Pending Approval</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="view_prescription_details?id=<?php echo $row["id"]; ?>" 
                                               class="btn btn-info btn-sm" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if($user_role === 'doctor'): ?>
                                                <a href="edit_prescription?id=<?php echo $row["id"]; ?>" 
                                                   class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if($user_role === 'patient' && $row["refill_status"] === 'new'): ?>
                                                <a href="request_refill?id=<?php echo $row["id"]; ?>" 
                                                   class="btn btn-success btn-sm" title="Request Refill"
                                                   onclick="return confirm('Are you sure you want to request a refill?');">
                                                    <i class="fas fa-sync"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No prescriptions found.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

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
.table th {
    background-color: #f8f9fa;
}
.badge {
    padding: 8px 12px;
    font-weight: 500;
}
</style>

<?php
//$stmt->close();
$conn->close();
?>
<?php
include "../includes/footer.php";
?>
</div>
</body>
</html>