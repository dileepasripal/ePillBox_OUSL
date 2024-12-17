<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id']) || !isset($_GET['id'])) {
    header("Location: login.php");
    exit;
}

// Include config file
require_once "../includes/db_connect.php";

$prescription_id = $_GET['id'];
$user_id = $_SESSION['id'];
$user_role = $_SESSION['role'];

// Helper function to safely escape potentially null values
function escape($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Different SQL queries based on user role
if ($user_role === 'doctor') {
    $sql = "SELECT p.*, 
            u.username AS patient_name,
            u.email AS patient_email,
            u.contact AS patient_contact,
            ph.name AS pharmacy_name,
            ph.address AS pharmacy_address,
            ph.contact_information AS pharmacy_contact
            FROM prescriptions p
            JOIN users u ON p.user_id = u.id
            JOIN pharmacies ph ON p.pharmacy_id = ph.pharmacy_id
            WHERE p.id = ? AND p.doctor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $prescription_id, $user_id);
} else if ($user_role === 'patient') {
    $sql = "SELECT p.*, 
            d.username AS doctor_name,
            d.specialization AS doctor_specialization,
            d.hospital AS doctor_hospital,
            ph.name AS pharmacy_name,
            ph.address AS pharmacy_address,
            ph.contact_information AS pharmacy_contact
            FROM prescriptions p
            JOIN users u ON p.doctor_id = u.id
            JOIN doctors d ON u.id = d.user_id
            JOIN pharmacies ph ON p.pharmacy_id = ph.pharmacy_id
            WHERE p.id = ? AND p.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $prescription_id, $user_id);
} else if ($user_role === 'pharmacist') {
    $sql = "SELECT p.*, 
            u.username AS patient_name,
            u.email AS patient_email,
            u.contact AS patient_contact,
            d.username AS doctor_name,
            d.specialization AS doctor_specialization,
            d.hospital AS doctor_hospital,
            ph.name AS pharmacy_name,
            ph.address AS pharmacy_address,
            ph.contact_information AS pharmacy_contact
            FROM prescriptions p
            JOIN users u ON p.user_id = u.id
            JOIN users du ON p.doctor_id = du.id
            JOIN doctors d ON du.id = d.user_id
            JOIN pharmacies ph ON p.pharmacy_id = ph.pharmacy_id
            WHERE p.id = ? AND ph.pharmacy_id = (
                SELECT pharmacy_id FROM pharmacists WHERE user_id = ?
            )";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $prescription_id, $user_id);
} else if ($user_role === 'admin') { // Admin can see all details
    $sql = "SELECT p.*, 
            u.username AS patient_name,
            u.email AS patient_email,
            u.contact AS patient_contact,
            d.username AS doctor_name,
            d.specialization AS doctor_specialization,
            d.hospital AS doctor_hospital,
            ph.name AS pharmacy_name,
            ph.address AS pharmacy_address,
            ph.contact_information AS pharmacy_contact
            FROM prescriptions p
            JOIN users u ON p.user_id = u.id
            JOIN users du ON p.doctor_id = du.id
            JOIN doctors d ON du.id = d.user_id
            JOIN pharmacies ph ON p.pharmacy_id = ph.pharmacy_id
            WHERE p.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $prescription_id);
}
 else {
    header("Location: login.php");
    exit;
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: home.php");
    exit;
}

$prescription = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prescription Details</title>
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
        
        <h2 class="mb-4">Prescription Details</h2>

        <div class="prescription-details">
            <h3 class="section-header">
                <i class="fas fa-prescription-bottle-alt"></i> Medication Details
            </h3>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Medication Name:</strong> <?php echo escape($prescription["medication_name"]); ?></p>
                    <p><strong>Dosage:</strong> <?php echo escape($prescription["dosage"]); ?></p>
                    <p><strong>Frequency:</strong> <?php echo escape($prescription["frequency"]); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Start Date:</strong> <?php echo escape($prescription["start_date"]); ?></p>
                    <p><strong>End Date:</strong> <?php echo escape($prescription["end_date"]) ?: 'Not specified'; ?></p>
                    <p><strong>Status:</strong> 
                        <span class="status-badge <?php echo 'status-' . strtolower(str_replace('_', '-', $prescription["refill_status"])); ?>">
                            <?php echo escape($prescription["refill_status"]); ?>
                        </span>
                    </p>
                    <?php if ($prescription["request_status"] !== 'no'): ?>
                        <p><strong>Request Status:</strong> 
                            <span class="badge <?php echo $prescription["request_status"] === 'approved' ? 'badge-success' : ($prescription["request_status"] === 'rejected' ? 'badge-danger' : 'badge-warning'); ?>">
                                <?php echo escape($prescription["request_status"]); ?>
                            </span>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            <?php if(!empty($prescription["special_instructions"])): ?>
                <p><strong>Special Instructions:</strong> <?php echo escape($prescription["special_instructions"]); ?></p>
            <?php endif; ?>
        </div>

        <?php if ($user_role === 'doctor' || $user_role === 'pharmacist'): ?>
        <div class="prescription-details">
            <h3 class="section-header">
                <i class="fas fa-user-circle"></i> Patient Information
            </h3>
            <p><strong>Name:</strong> <?php echo escape($prescription["patient_name"]); ?></p>
            <p><strong>Email:</strong> <?php echo escape($prescription["patient_email"]); ?></p>
            <p><strong>Contact:</strong> <?php echo escape($prescription["patient_contact"]); ?></p>
        </div>
        <?php endif; ?>

        <?php if ($user_role === 'patient' || $user_role === 'pharmacist'): ?>
        <div class="prescription-details">
            <h3 class="section-header">
                <i class="fas fa-user-md"></i> Doctor Information
            </h3>
            <p><strong>Doctor:</strong> <?php echo escape($prescription["doctor_name"]); ?></p>
            <p><strong>Specialization:</strong> <?php echo escape($prescription["doctor_specialization"]); ?></p>
            <p><strong>Hospital:</strong> <?php echo escape($prescription["doctor_hospital"]); ?></p>
        </div>
        <?php endif; ?>

        <div class="prescription-details">
            <h3 class="section-header">
                <i class="fas fa-clinic-medical"></i> Pharmacy Information
            </h3>
            <p><strong>Pharmacy Name:</strong> <?php echo escape($prescription["pharmacy_name"]); ?></p>
            <p><strong>Address:</strong> <?php echo escape($prescription["pharmacy_address"]); ?></p>
            <p><strong>Contact:</strong> <?php echo escape($prescription["pharmacy_contact"]); ?></p>
        </div>

        <div class="mt-4">
            <a href="home.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            
            <?php if ($user_role === 'doctor'): ?>
            <a href="edit_prescription.php?id=<?php echo escape($prescription_id); ?>" 
               class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit Prescription
            </a>
            <?php endif; ?>
            
            <?php if ($user_role === 'patient' && $prescription["refill_status"] === 'new'): ?>
            <a href="request_refill.php?id=<?php echo escape($prescription_id); ?>" 
               class="btn btn-primary">
                <i class="fas fa-sync"></i> Request Refill
            </a>
            <?php endif; ?>

            <?php if ($user_role === 'pharmacist' && $prescription["refill_status"] === 'refill_requested' && $prescription["request_status"] === 'pending'): ?>
            <a href="approve_refill?id=<?php echo escape($prescription_id); ?>" 
               class="btn btn-success" 
               onclick="return confirm('Are you sure you want to approve this refill request?');">
                <i class="fas fa-check"></i> Approve Refill
            </a>
            <a href="reject_refill?id=<?php echo escape($prescription_id); ?>" 
               class="btn btn-danger"
               onclick="return confirm('Are you sure you want to reject this refill request?');">
                <i class="fas fa-times"></i> Reject Refill
            </a>
            <?php endif; ?>
        </div>

        <?php include "../includes/footer.php"; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
//$stmt->close();
$conn->close();
?>