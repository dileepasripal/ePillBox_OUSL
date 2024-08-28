<?php
// ... (Database connection)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reminder_id = $_POST["reminder_id"];

    // Update reminder status in the database
    $update_sql = "UPDATE medication_reminders SET status = 'snoozed' WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $reminder_id);

    if ($stmt->execute()) {
        echo "Reminder snoozed successfully!";
    } else {
        echo "Error snoozing reminder: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>