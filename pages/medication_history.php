<?php
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login"); 
    exit;
}

// Database connection (reuse the same connection code from register.php)

// Fetch medication history from the database
$user_id = $_SESSION['user_id'];
$sql = "SELECT ml.*, p.medication_name 
        FROM medication_logs ml
        JOIN prescriptions p ON ml.prescription_id = p.id
        WHERE ml.user_id = ?
        ORDER BY ml.taken_at DESC"; // Order by latest intake first
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<h2>Medication History</h2>

<table>
    <thead>
        <tr>
            <th>Medication Name</th>
            <th>Taken At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row["medication_name"]; ?></td>
                <td><?php echo $row["taken_at"]; ?></td>
                <td>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<?php
$stmt->close();
$conn->close();
?>