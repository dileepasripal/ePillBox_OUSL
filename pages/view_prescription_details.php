<?php
// Check if the user is logged in and a prescription ID is provided
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php?page=view_prescriptions"); 
    exit;
}

// Database connection (reuse the same connection code from register.php)

// Fetch prescription details from the database
$prescription_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM prescriptions WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $prescription_id, $user_id); 
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $prescription_data = $result->fetch_assoc();
} else {
    // Handle the case where the prescription is not found or doesn't belong to the user
    die("Prescription not found."); 
}

$stmt->close();
$conn->close();
?>

<h2>Prescription Details</h2>

<p><strong>Medication Name:</strong> <?php echo $prescription_data["medication_name"]; ?></p>
<p><strong>Dosage:</strong> <?php echo $prescription_data["dosage"]; ?></p>
<p><strong>Frequency:</strong> <?php echo $prescription_data["frequency"]; ?></p>
<p><strong>Start Date:</strong> <?php echo $prescription_data["start_date"]; ?></p>
<p><strong>End Date:</strong> <?php echo $prescription_data["end_date"]; ?></p>
<p><strong>Special Instructions:</strong> <?php echo $prescription_data["special_instructions"]; ?></p>
<p><strong>Doctor's Name:</strong> <?php echo $prescription_data["doctor_name"]; ?></p>

<a href="index.php?page=view_prescriptions">Back to Prescriptions</a>