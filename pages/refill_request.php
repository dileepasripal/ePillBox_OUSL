<?php
session_start();

// Check if the user is logged in and a prescription ID is provided
if (!isset($_SESSION['id']) || !isset($_GET['id'])) {
    header("Location: home.php"); // Redirect to patient dashboard if no prescription ID or not logged in
    exit;
}

// Include config file
require_once "../includes/db_connect.php";

// Fetch prescription details (to display on the refill request page)
$prescription_id = $_GET['id'];
$user_id = $_SESSION['id'];

$sql = "SELECT p.*, u.username AS doctor_name  -- Changed doctor_id to doctor_name for clarity
            FROM prescriptions p
            JOIN users u ON p.doctor_id = u.id
            WHERE p.id = ? AND p.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $prescription_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $prescription_data = $result->fetch_assoc();
} else {
    // Handle the case where the prescription is not found or doesn't belong to the user
    header("Location: home.php"); // Redirect to patient dashboard if prescription not found
    exit;
}

// Fetch pharmacies for the dropdown
$sql = "SELECT pharmacy_id, name FROM pharmacies";
$pharmacies_result = $conn->query($sql);

if (!$pharmacies_result) {
    die("Error fetching pharmacies: " . $conn->error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get pharmacy ID from the form
    $pharmacy_id = $_POST["pharmacy_id"];

    // Update the prescription with the selected pharmacy and request status
    $sql = "UPDATE prescriptions 
                SET pharmacy_id = ?, request_status = 'pending' 
                WHERE id = ? AND user_id = ?"; 
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $pharmacy_id, $prescription_id, $user_id);

    if ($stmt->execute()) {
        // Display success message on the same page
        echo "<div class='alert alert-success'>Your refill request has been submitted successfully!</div>"; 

        // --- Add notification for the pharmacist ---

        // 1. Get the pharmacist's user ID associated with the selected pharmacy
        $pharmacist_id_sql = "SELECT user_id FROM pharmacists WHERE pharmacy_id = ?";
        $pharmacist_id_stmt = $conn->prepare($pharmacist_id_sql);
        $pharmacist_id_stmt->bind_param("i", $pharmacy_id);
        $pharmacist_id_stmt->execute();
        $pharmacist_id_result = $pharmacist_id_stmt->get_result();

        if ($pharmacist_id_result->num_rows > 0) {
            $pharmacist_id = $pharmacist_id_result->fetch_assoc()['user_id'];

            // 2. Create the notification message
            $notification_message = "Patient {$_SESSION['username']} has requested a refill for prescription #{$prescription_id}.";

            // 3. Insert the notification into the database
            $notification_sql = "INSERT INTO notifications (user_id, type, message, reference_id) 
                                 VALUES (?, 'refill_request', ?, ?)";
            $notification_stmt = $conn->prepare($notification_sql);
            $notification_stmt->bind_param("isi", $pharmacist_id, $notification_message, $prescription_id);

            if ($notification_stmt->execute()) {
                // Notification added successfully
            } else {
                error_log("Error adding notification: " . $notification_stmt->error); // Log the error for debugging
            }
        } else {
            error_log("Error fetching pharmacist ID: " . $pharmacist_id_stmt->error); // Log the error
        }
    } else {
        echo "Error submitting refill request: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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

        <h2>Refill Request</h2>

        <h3>Prescription Details</h3>
        <p><strong>Medication Name:</strong> <?php echo htmlspecialchars($prescription_data["medication_name"]); ?></p>
        <p><strong>Dosage:</strong> <?php echo htmlspecialchars($prescription_data["dosage"]); ?></p>
        <p><strong>Frequency:</strong> <?php echo htmlspecialchars($prescription_data["frequency"]); ?></p>
        <p><strong>Start Date:</strong> <?php echo htmlspecialchars($prescription_data["start_date"]); ?></p>
        <p><strong>End Date:</strong> <?php echo htmlspecialchars($prescription_data["end_date"]); ?></p>
        <p><strong>Special Instructions:</strong> <?php echo htmlspecialchars($prescription_data["special_instructions"]); ?></p>
        <p><strong>Doctor's Name:</strong> <?php echo htmlspecialchars($prescription_data["doctor_name"]); ?></p>

        <h3>Select Pharmacy for Refill</h3>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $prescription_id; ?>">
            <div class="form-group">
                <label for="pharmacy_id">Select Pharmacy:</label>
                <select id="pharmacy_id" name="pharmacy_id" class="form-control" required>
                    <option value="">Select Pharmacy</option>
                    <?php while ($pharmacy = $pharmacies_result->fetch_assoc()) { ?>
                        <option value="<?php echo $pharmacy['pharmacy_id']; ?>"><?php echo $pharmacy['name']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <input type="submit" value="Submit Refill Request" class="btn btn-primary">
            </div>
        </form>

        <?php include "../includes/footer.php"; ?>
    </div>
</body>
</html>