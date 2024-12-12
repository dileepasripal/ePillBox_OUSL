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
    header("location: doctor_dashboard.php");
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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .wrapper {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 80%;
            max-width: 800px;
            margin: 30px auto;
        }
        .form-group label {
            font-weight: 600;
        }
        .error-message {
            color: #dc3545;
            margin-bottom: 20px;
        }
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