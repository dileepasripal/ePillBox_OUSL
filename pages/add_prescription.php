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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .card {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 1.5rem;
        }
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }
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