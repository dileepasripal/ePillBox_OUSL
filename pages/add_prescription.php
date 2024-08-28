<div class="container">
<?php
// Check if the user is logged in and has the 'admin' role (same as in admin_dashboard.php)
include "../includes/header.php";
include '../includes/db_connect.php'; 

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get data from the form and perform validation
    $medication_name = $_POST["medication_name"];
    $dosage = $_POST["dosage"];
    $frequency = $_POST["frequency"];
    $start_date = $_POST["start_date"];
    $end_date = $_POST["end_date"]; // Optional
    $special_instructions = $_POST["special_instructions"]; // Optional
    $doctor_name = $_POST["doctor_name"];
    $user_id = $_SESSION['user_id'];

    // Basic input validation (you'll need to enhance this)
    // ...

    // Insert prescription into the database
    $sql = "INSERT INTO prescriptions (user_id, medication_name, dosage, frequency, start_date, end_date, special_instructions, doctor_name) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssss", $user_id, $medication_name, $dosage, $frequency, $start_date, $end_date, $special_instructions, $doctor_name);

    if ($stmt->execute()) {
        echo "Prescription added successfully!";
    } else {
        echo "Error adding prescription: " . $stmt->error;
    }

    $stmt->close();
}
?>


<h2>Add Prescription</h2>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <label for="medication_name">Medication Name:</label>
    <input type="text" id="medication_name" name="medication_name" required><br><br>

    <label for="dosage">Dosage:</label>
    <input type="text" id="dosage" name="dosage" required><br><br>

    <label for="frequency">Frequency:</label>
    <input type="text" id="frequency" name="frequency" required><br><br>

    <label for="start_date">Start Date:</label>
    <input type="date" id="start_date" name="start_date" required><br><br>

    <label for="end_date">End Date (optional):</label>
    <input type="date" id="end_date" name="end_date"><br><br>

    <label for="special_instructions">Special Instructions (optional):</label>
    <textarea id="special_instructions" name="special_instructions"></textarea><br><br>

    <label for="doctor_name">Doctor's Name:</label>
    <input type="text" id="doctor_name" name="doctor_name" required><br><br>

    <input type="submit" value="Add Prescription">
</form>

<h3>Upload Prescription Image (Coming Soon)</h3>
<p>This feature will be available in the future. You'll be able to upload an image of your prescription for automatic verification using OCR technology.</p>

<?php
$conn->close();
include "../includes/footer.php";
?>