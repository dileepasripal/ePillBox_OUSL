<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'doctor') {
    header("location: login.php");
    exit;
}

include '../includes/db_connect.php';

$errors = [];
$success_msg = "";
$search_term = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $required_fields = ['medication_name', 'dosage', 'frequency', 'start_date', 'pharmacy_id'];
    foreach($required_fields as $field) {
        if(empty(trim($_POST[$field]))) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required.";
        }
    }

    if(!empty($_POST['start_date'])) {
        if(strtotime($_POST['start_date']) < strtotime('today')) {
            $errors[] = "Start date cannot be in the past.";
        }
        if(!empty($_POST['end_date']) && strtotime($_POST['end_date']) <= strtotime($_POST['start_date'])) {
            $errors[] = "End date must be after start date.";
        }
    }

    if (isset($_POST['search_term']) && !empty(trim($_POST['search_term']))) {
        $search_term = trim($_POST['search_term']);
        $sql = "SELECT id FROM users WHERE role = 'patient' AND username LIKE ?";
        $stmt = $conn->prepare($sql);
        $search_param = "%" . $search_term . "%";
        $stmt->bind_param("s", $search_param);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $errors[] = "No patient found with that username.";
        } else {
            $patient = $result->fetch_assoc();
            $_POST['patient_id'] = $patient['id']; // Set patient_id if found
        }
    } else if(empty(trim($_POST['patient_id']))){
        $errors[] = "Patient is required.";
    }


    if(empty($errors)) {
        $sql = "INSERT INTO prescriptions (user_id, medication_name, dosage, frequency, start_date, end_date, special_instructions, doctor_id, pharmacy_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssssis", $_POST['patient_id'], $_POST['medication_name'], $_POST['dosage'], $_POST['frequency'], $_POST['start_date'], $_POST['end_date'], $_POST['special_instructions'], $_SESSION['id'], $_POST['pharmacy_id']);

        if($stmt->execute()) {
            $notification_sql = "INSERT INTO notifications (user_id, type, message) VALUES (?, 'prescription', ?)";
            $notify_stmt = $conn->prepare($notification_sql);
            $message = "New prescription added for: " . $_POST['medication_name'];
            $notify_stmt->bind_param("is", $_POST['patient_id'], $message);
            $notify_stmt->execute();
            
            $success_msg = "Prescription added successfully!";
            $_POST = array(); // Clear form
        } else {
            $errors[] = "Error adding prescription: " . $stmt->error;
        }
    }
}

$pharmacies = $conn->query("SELECT pharmacy_id, name FROM pharmacies");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Prescription</title>
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

        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0">Add New Prescription</h2>
                </div>
                <div class="card-body">
                    <?php if(!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach($errors as $error): ?>
                                <div><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if($success_msg): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="search_term">Search Patient (Username):</label>
                                    <input type="text" name="search_term" id="search_term" class="form-control" value="<?php echo htmlspecialchars($search_term); ?>">
                                </div>
                                <input type="hidden" name="patient_id" value="<?php echo isset($_POST['patient_id']) ? $_POST['patient_id'] : ''; ?>">
                                <div class="form-group">
                                    <label>Medication Name*</label>
                                    <input type="text" name="medication_name" class="form-control" value="<?php echo htmlspecialchars($_POST['medication_name'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Dosage*</label>
                                    <input type="text" name="dosage" class="form-control" value="<?php echo htmlspecialchars($_POST['dosage'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Frequency*</label>
                                    <input type="text" name="frequency" class="form-control" value="<?php echo htmlspecialchars($_POST['frequency'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Start Date*</label>
                                    <input type="date" name="start_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Pharmacy*</label>
                            <select name="pharmacy_id" class="form-control" required>
                                <option value="">Select Pharmacy</option>
                                <?php while($pharmacy = $pharmacies->fetch_assoc()): ?>
                                    <option value="<?php echo $pharmacy['pharmacy_id']; ?>" <?php echo isset($_POST['pharmacy_id']) && $_POST['pharmacy_id'] == $pharmacy['pharmacy_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($pharmacy['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Special Instructions</label>
                            <textarea name="special_instructions" class="form-control" rows="3"><?php echo htmlspecialchars($_POST['special_instructions'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add Prescription</button>
                            <button type="reset" class="btn btn-secondary"><i class="fas fa-undo"></i> Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php include "../includes/footer.php"; ?>
    </div>
</body>
</html>