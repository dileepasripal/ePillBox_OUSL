<?php
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login"); // Redirect to login if not logged in
    exit;
}

// Database connection (reuse the same connection code from register.php)

// Fetch user data from the database
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user_data = $result->fetch_assoc();
} else {
    // Handle the case where the user is not found (e.g., display an error message)
    die("User not found."); 
}

$stmt->close();

// Handle form submission for profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get updated data from the form
    // ... (retrieve data from $_POST, perform validation, etc.)

    // Update user data in the database
    $update_sql = "UPDATE users SET name = ?, date_of_birth = ?, contact_information = ?, medical_conditions = ?, current_medications = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sssssi", $name, $date_of_birth, $contact_information, $medical_conditions, $current_medications, $user_id);

    if ($stmt->execute()) {
        echo "Profile updated successfully!";
        // You might want to refresh the page or update the displayed user data here
    } else {
        echo "Error updating profile: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<h2>Profile</h2>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" value="<?php echo $user_data['name']; ?>" required><br><br>

    <label for="date_of_birth">Date of Birth:</label>
    <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo $user_data['date_of_birth']; ?>" required><br><br>

    <label for="contact_information">Contact Information:</label>
    <textarea id="contact_information" name="contact_information"><?php echo $user_data['contact_information']; ?></textarea><br><br>

    <label for="medical_conditions">Medical Conditions:</label>
    <textarea id="medical_conditions" name="medical_conditions"><?php echo $user_data['medical_conditions']; ?></textarea><br><br>

    <label for="current_medications">Current Medications:</label>
    <textarea id="current_medications" name="current_medications"><?php echo $user_data['current_medications']; ?></textarea><br><br>

    <input type="submit" value="Update Profile">
</form>