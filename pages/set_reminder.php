<?php
// Check if the user is logged in and a prescription ID is provided
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php?page=view_prescriptions");
    exit;
}

// Database connection (reuse the same connection code from register.php)

// Fetch prescription details (to display on the reminder page)
$prescription_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
// ... (Fetch prescription details using $prescription_id and $user_id)

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get reminder data from the form and perform validation
    $reminder_time = $_POST["reminder_time"];
    $frequency = $_POST["frequency"];
    $alert_type = $_POST["alert_type"];
    $personalized_message = $_POST["personalized_message"]; // Optional

    // Basic input validation (you'll need to enhance this)
    // ...

    // Insert reminder into the database
    $sql = "INSERT INTO medication_reminders (user_id, prescription_id, reminder_time, frequency, alert_type, personalized_message) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissss", $user_id, $prescription_id, $reminder_time, $frequency, $alert_type, $personalized_message);

    if ($stmt->execute()) {
        echo "Reminder set successfully!";
        // You might want to redirect back to the view prescriptions page or show a success message
    } else {
        echo "Error setting reminder: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<h2>Set Reminder</h2>

<h3>Prescription Details</h3>
<p>Medication: <?php echo $prescription_data["medication_name"]; ?></p>
</textarea><br><br>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $prescription_id; ?>">
    <label for="reminder_time">Reminder Time:</label>
    <input type="time" id="reminder_time" name="reminder_time" required><br><br>

    <label for="frequency">Frequency:</label>
    <select id="frequency" name="frequency" required>
        <option value="daily">Daily</option>
        <option value="weekly">Weekly</option>
        <option value="monthly">Monthly</option>
        </select><br><br>

    <label>Alert Type:</label><br>
    <input type="radio" id="browser_alert" name="alert_type" value="browser" required>
    <label for="browser_alert">Browser Notification</label><br>
    <input type="radio" id="email_alert" name="alert_type" value="email">
    <label for="email_alert">Email</label><br><br>

    <label for="personalized_message">Personalized Message (optional):</label>
    <textarea id="personalized_message" name="personalized_message"></textarea><br><br>

    <input type="submit" value="Set Reminder">
</form>