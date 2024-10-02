<?php

// Include config file
require_once "../includes/db_connect.php";

// Fetch refill requests for this pharmacist (replace with your actual logic)
$pharmacist_id = $_SESSION["id"]; // Assuming you have pharmacist IDs in your users table
$sql = "SELECT r.*, p.medication_name, u.username AS patient_name 
        FROM refill_requests r
        JOIN prescriptions p ON r.prescription_id = p.id
        JOIN users u ON r.user_id = u.id
        WHERE r.pharmacy_id = $pharmacist_id"; // Assuming pharmacy_id in refill_requests links to the pharmacist
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
                    <th>Request ID</th>
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
                        <td><?php echo htmlspecialchars($row["status"]); ?></td> 
                        <td>
                            <a href="approve_refill.php?id=<?php echo htmlspecialchars($row["id"]); ?>">Approve</a> |
                            <a href="reject_refill.php?id=<?php echo htmlspecialchars($row["id"]); ?>">Reject</a>
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

