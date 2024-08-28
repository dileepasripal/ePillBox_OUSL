<?php
// Check if the user is logged in and has the 'admin' role
// ... (same as in admin_dashboard.php and manage_users.php)
include '../includes/db_connect.php'; 
// Get the user ID from the query parameter
if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Delete the user from the database
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        // Redirect back to the user management page after successful deletion
        header("location: manage_users.php");
        exit;
    } else {
        echo "Error deleting user: " . $stmt->error;
    }

    $stmt->close();
} else {
    // Handle missing user ID
    echo "Invalid request.";
    exit;
}

$conn->close();
?>