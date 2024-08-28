<?php
// Check if the user is logged in and a prescription ID is provided
if (!isset($_SESSION['id']) || !isset($_GET['id'])) {
    header("Location: view_prescriptions.php"); 
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

// Handle form submission for editing the prescription
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get updated data from the form
    // ... (retrieve data from $_POST, perform validation, etc.)

    // Update prescription data in the database
    $update_sql = "UPDATE prescriptions 
                   SET medication_name = ?, dosage = ?, frequency = ?, 
                       start_date = ?, end_date = ?, special_instructions = ?, doctor_name = ?
                   WHERE id = ? AND user_id = ?"; 
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sssssssii", $medication_name, $dosage, $frequency, $start_date, $end_date, $special_instructions, $doctor_name, $prescription_id, $user_id); 

    if ($stmt->execute()) {
        echo "Prescription updated successfully!";
        // You might want to redirect back to the view prescriptions page
        header("Location: index.php?page=view_prescriptions"); 
        exit;
    } else {
        echo "Error updating prescription: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<h2>Edit Prescription</h2>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $prescription_id; ?>"> 
    <label for="medication_name">Medication Name:</label>
    <input type="text" id="medication_name" name="medication_name" value="<?php echo $prescription_data['medication_name']; ?>" required><br><br>

    </form>