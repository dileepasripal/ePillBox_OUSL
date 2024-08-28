<?php
// Check if the user is logged in and has the 'admin' role
// ... (same as in admin_dashboard.php and manage_users.php)
include '../includes/db_connect.php'; 
// Get the user ID from the query parameter
if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Fetch user data for editing (change 'email' to 'username')
    $sql = "SELECT id, username, role FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc(); 

        $username = $row["username"]; // Change 'email' to 'username'
        $role = $row["role"];
    } else {
        // Handle invalid user ID
        echo "User not found.";
        exit;
    }

    $stmt->close();
} else {
    // Handle missing user ID
    echo "Invalid request.";
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input 
    $newUsername = htmlspecialchars($_POST["username"]); // Change 'email' to 'username'
    $newRole = htmlspecialchars($_POST["role"]);

    // Update user data in the database (change 'email' to 'username')
    $sql = "UPDATE users SET username = ?, role = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $newUsername, $newRole, $userId);

    if ($stmt->execute()) {
        header("location: manage_users.php");
        exit;
    } else {
        echo "Error updating user: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<h2>Edit User</h2>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $userId; ?>" method="post">
    <div class="form-group">
        <label>Username:</label>
        <input type="text" name="username" class="form-control" value="<?php echo $username; ?>" required> 
    </div>
    <div class="form-group">
        <label>Role:</label>
        <select name="role" class="form-control" required>
            <option value="patient" <?php if ($role == 'patient') echo 'selected'; ?>>Patient</option>
            <option value="provider" <?php if ($role == 'provider') echo 'selected'; ?>>Provider</option>
            <option value="pharmacist" <?php if ($role == 'pharmacist') echo 'selected'; ?>>Pharmacist</option>
            <option value="admin" <?php if ($role == 'admin') echo 'selected'; ?>>Admin</option>
        </select>
    </div>
    <div class="form-group">
        <input type="submit" class="btn btn-primary" value="Save Changes">
        <a href="manage_users.php" class="btn btn-secondary ml-2">Cancel</a>
    </div>
</form>