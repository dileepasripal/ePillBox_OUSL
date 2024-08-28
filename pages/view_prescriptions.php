<?php
// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: index.php?page=login"); // Redirect to login if not logged in
    exit;
}

// Database connection (reuse the same connection code from register.php)

// Fetch user's prescriptions from the database
$user_id = $_SESSION['id'];
$sql = "SELECT * FROM prescriptions WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Handle filtering and sorting (you'll need to implement this logic based on user input)
// ...

?>

<h2>View Prescriptions</h2>

<table>
    <thead>
        <tr>
            <th>Medication Name</th>
            <th>Dosage</th>
            <th>Frequency</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Actions</th> 
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row["medication_name"]; ?></td>
                <td><?php echo $row["dosage"]; ?></td>
                <td><?php echo $row["frequency"]; ?></td>
                <td><?php echo $row["start_date"]; ?></td>
                <td><?php echo $row["end_date"]; ?></td>
                <td>
    <a href="index.php?page=view_prescription_details&id=<?php echo $row["id"]; ?>">View Details</a> |
    <a href="index.php?page=edit_prescription&id=<?php echo $row["id"]; ?>">Edit</a> |
    <a href="index.php?page=delete_prescription&id=<?php echo $row["id"]; ?>" onclick="return confirm('Are you sure you want to delete this prescription?');">Delete</a> |
    <a href="index.php?page=refill_request&id=<?php echo $row["id"]; ?>">Refill</a> 
</td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<?php
$stmt->close();
$conn->close();
?>

<form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <input type="hidden" name="page" value="view_prescriptions"> 
    </form>