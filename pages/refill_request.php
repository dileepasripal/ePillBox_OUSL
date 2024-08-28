<?php
// Check if the user is logged in and a prescription ID is provided
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php?page=view_prescriptions");
    exit;
}

// Database connection (reuse the same connection code from register.php)

// Fetch prescription details (to display on the refill request page)
$prescription_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
// ... (Fetch prescription details using $prescription_id and $user_id)

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get pharmacy ID from the form (you'll need to implement pharmacy selection later)
    $pharmacy_id = $_POST["pharmacy_id"];

    // Insert refill request into the database
    $sql = "INSERT INTO refill_requests (user_id, prescription_id, pharmacy_id) 
            VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $user_id, $prescription_id, $pharmacy_id);

    if ($stmt->execute()) {
        echo "Refill request submitted successfully!";
        // You might want to redirect to a page showing refill request status or history
    } else {
        echo "Error submitting refill request: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<h2>Refill Request</h2>

<h3>Prescription Details</h3>
<p>Medication: <?php echo $prescription_data["medication_name"]; ?></p>
</textarea><br><br>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $prescription_id; ?>">
    <label for="pharmacy_id">Select Pharmacy:</label>
    <select id="pharmacy_id" name="pharmacy_id" required>
        </select><br><br>

    <input type="submit" value="Submit Refill Request">
</form>