<?php
// Check if user is logged in and prescription ID is provided
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php?page=view_prescriptions");
    exit;
}

// Database connection (reuse the same connection code from register.php)

// Fetch prescription details (to display on the log medication page)
$prescription_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
// ... (Fetch prescription details using $prescription_id and $user_id)

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // You might want to get additional information from the form (e.g., notes)
    // ...

    // Insert medication log into the database
    $sql = "INSERT INTO medication_logs (user_id, prescription_id) 
            VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $prescription_id);

    if ($stmt->execute()) {
        echo "Medication intake logged successfully!";
        // You might want to redirect back to the view prescriptions page or show a success message
    } else {
        echo "Error logging medication intake: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<h2>Log Medication Intake</h2>

<h3>Prescription Details</h3>
<p>Medication: <?php echo $prescription_data["medication_name"]; ?></p>
</textarea><br><br>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $prescription_id; ?>">
    <input type="submit" value="Log Intake">
</form>