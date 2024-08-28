<?php
include '../includes/db_connect.php'; 
// Get search term from query parameter
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Build SQL query with filtering (you'll need to refine this based on your search logic)
$sql = "SELECT * FROM pharmacies WHERE name LIKE ? OR address LIKE ?"; 
$searchTerm = '%' . $searchTerm . '%'; // Add wildcards for partial matches
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

// Fetch all pharmacies from the database
$sql = "SELECT * FROM pharmacies";
$result = $conn->query($sql);

$pharmacies = [];
while ($row = $result->fetch_assoc()) {
    $pharmacies[] = $row;
}

$conn->close();

// Return pharmacy data as JSON
header('Content-Type: application/json');
echo json_encode($pharmacies);
?>