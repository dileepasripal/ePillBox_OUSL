<?php
// Check if the user is logged in and a prescription ID is provided
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php?page=view_prescriptions"); 
    exit;
}

// Database connection (reuse the same connection code from register.php)

// Delete the prescription from the database
$prescription_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$sql = "DELETE FROM prescriptions WHERE id = ? AND user_id = ?"; 
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $prescription_id, $user_id); 

if ($stmt->execute()) {
    echo "Prescription deleted successfully!";
} else {
    echo "Error deleting prescription: " . $stmt->error;
}

$stmt->close();
$conn->close();

// Redirect back to the view prescriptions page
header("Location: index.php?page=view_prescriptions"); 
exit;
?>