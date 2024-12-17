<?php
session_start();

// Check if the user is logged in and has the 'doctor' role 
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'doctor') {
    header("location: login.php");
    exit;
}

require_once "../includes/db_connect.php";

// Check if prescription ID is provided
if(!isset($_GET["id"])) {
    header("location: doctor_dashboard.php");
    exit;
}

$prescription_id = $_GET["id"];
$doctor_id = $_SESSION["id"];

// Fetch prescription details
$sql = "SELECT p.*, u.username AS patient_name, ph.pharmacy_id, ph.name AS pharmacy_name 
        FROM prescriptions p
        JOIN users u ON p.user_id = u.id
        JOIN pharmacies ph ON p.pharmacy_id = ph.pharmacy_id
        WHERE p.id = ? AND p.doctor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $prescription_id, $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
    header("location: home.php");
    exit;
}

$prescription = $result->fetch_assoc();

// Fetch all pharmacies for the dropdown
$pharmacies_sql = "SELECT pharmacy_id, name FROM pharmacies";
$pharmacies_result = $conn->query($pharmacies_sql);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = array();
    
    // Validate input
    if (empty($_POST["medication_name"])) {
        $errors[] = "Medication name is required.";
    }
    if (empty($_POST["dosage"])) {
        $errors[] = "Dosage is required.";
    }
    if (empty($_POST["frequency"])) {
        $errors[] = "Frequency is required.";
    }
    if (empty($_POST["start_date"])) {
        $errors[] = "Start date is required.";
    }
    if (empty($_POST["pharmacy_id"])) {
        $errors[] = "Pharmacy selection is required.";
    }

    if (empty($errors)) {
        $update_sql = "UPDATE prescriptions SET 
            medication_name = ?,
            dosage = ?,
            frequency = ?,
            start_date = ?,
            end_date = ?,
            special_instructions = ?,
            pharmacy_id = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND doctor_id = ?";
            
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sssssssii", 
            $_POST["medication_name"],
            $_POST["dosage"],
            $_POST["frequency"],
            $_POST["start_date"],
            $_POST["end_date"],
            $_POST["special_instructions"],
            $_POST["pharmacy_id"],
            $prescription_id,
            $doctor_id
        );

        if ($stmt->execute()) {
            // Update Notification Table (New addition)
            $message = "Prescription details updated for: " . $_POST['medication_name'];
            $notify_sql = "INSERT INTO notifications (user_id, type, message) VALUES (?, 'prescription_update', ?)";
            $notify_stmt = $conn->prepare($notify_sql);
            $notify_stmt->bind_param("is", $prescription['user_id'], $message);
            $notify_stmt->execute();
            // End of New addition
      
            header("location: view_prescription_details.php?id=" . $prescription_id);
            exit;
        } else {
            $errors[] = "Error updating prescription: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Prescription</title>
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
        
        <h2 class="mb-4">Edit Prescription</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $prescription_id; ?>">
            <div class="form-group">
                <label>Patient Name</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($prescription['patient_name']); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="medication_name">Medication Name</label>
                <input type="text" class="form-control" id="medication_name" name="medication_name" 
                       value="<?php echo htmlspecialchars($prescription['medication_name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="dosage">Dosage</label>
                <input type="text" class="form-control" id="dosage" name="dosage" 
                       value="<?php echo htmlspecialchars($prescription['dosage']); ?>" required>
            </div>

            <div class="form-group">
                <label for="frequency">Frequency</label>
                <input type="text" class="form-control" id="frequency" name="frequency" 
                       value="<?php echo htmlspecialchars($prescription['frequency']); ?>" required>
            </div>

            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" 
                       value="<?php echo htmlspecialchars($prescription['start_date']); ?>" required>
            </div>

            <div class="form-group">
                <label for="end_date">End Date (Optional)</label>
                <input type="date" class="form-control" id="end_date" name="end_date" 
                       value="<?php echo htmlspecialchars($prescription['end_date']); ?>">
            </div>

            <div class="form-group">
                <label for="pharmacy_id">Pharmacy</label>
                <select class="form-control" id="pharmacy_id" name="pharmacy_id" required>
                    <option value="">Select Pharmacy</option>
                    <?php while ($pharmacy = $pharmacies_result->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($pharmacy['pharmacy_id']); ?>"
                                <?php echo $pharmacy['pharmacy_id'] == $prescription['pharmacy_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($pharmacy['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="special_instructions">Special Instructions (Optional)</label>
                <textarea class="form-control" id="special_instructions" name="special_instructions" rows="3"
                          ><?php echo htmlspecialchars($prescription['special_instructions']); ?></textarea>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <a href="view_prescription_details.php?id=<?php echo $prescription_id; ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>

        <?php include "../includes/footer.php"; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>