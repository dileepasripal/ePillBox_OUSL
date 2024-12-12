<?php

// Include config file
require_once "../includes/db_connect.php";

// Fetch prescriptions with refill requests for this pharmacist
$pharmacist_id = $_SESSION["id"]; 
echo "Pharmacist ID: " . $pharmacist_id; 

$sql = "SELECT pr.*, u.username AS patient_name 
        FROM prescriptions pr
        JOIN users u ON pr.user_id = u.id
        WHERE pr.request_status = 'pending' 
        AND pr.pharmacy_id = $pharmacist_id"; // Assuming you have pharmacy_id in prescriptions table
$result = $conn->query($sql);

// Error handling
if (!$result) {
    die("Error fetching refill requests: " . $conn->error);
}
?>

<h2>Pharmacist Dashboard</h2>

<h3>Refill Requests</h3>

<?php if ($result->num_rows > 0) { ?>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Prescription ID</th>
            <th>Patient Name</th>
            <th>Medication</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row["id"]); ?></td>
                <td><?php echo htmlspecialchars($row["patient_name"]); ?></td>
                <td><?php echo htmlspecialchars($row["medication_name"]); ?></td>
                <td><?php echo htmlspecialchars($row["request_status"]); ?></td>
                <td>
                    <a href="approve_refill.php?id=<?php echo htmlspecialchars($row["id"]); ?>" class="btn btn-success btn-sm mr-2"><i class="fa fa-check"></i> Approve</a>
                    <a href="reject_refill.php?id=<?php echo htmlspecialchars($row["id"]); ?>" class="btn btn-danger btn-sm"><i class="fa fa-times"></i> Reject</a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>
<?php } else { ?>
    <p>No refill requests at this time.</p>
<?php } ?>

<?php
$conn->close();
?>