<?php

// Include config file
require_once "../includes/db_connect.php";

// Fetch patient's prescriptions
$patient_id = $_SESSION["id"];
$sql = "SELECT * FROM prescriptions WHERE user_id = $patient_id";
$result = $conn->query($sql);

// Error handling
if (!$result) {
    die("Error fetching prescriptions: " . $conn->error);
}
?>

        <h2>Patient Dashboard</h2>

        <h3>My Prescriptions</h3>

        <?php if ($result->num_rows > 0) { ?>
        <table class="table table-bordered"> 
            <thead>
                <tr>
                    <th>Medication Name</th>
                    <th>Dosage</th>
                    <th>Frequency</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Special Instructions</th>
                    <th>Doctor Name</th>
                    <th>Actions</th> 
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row["medication_name"]); ?></td> 
                        <td><?php echo htmlspecialchars($row["dosage"]); ?></td> 
                        <td><?php echo htmlspecialchars($row["frequency"]); ?></td> 
                        <td><?php echo htmlspecialchars($row["start_date"]); ?></td> 
                        <td><?php echo htmlspecialchars($row["end_date"]); ?></td> 
                        <td><?php echo htmlspecialchars($row["special_instructions"]); ?></td> 
                        <td><?php echo htmlspecialchars($row["doctor_name"]); ?></td> 
                        <td>
                            <a href="view_prescription_details.php?id=<?php echo htmlspecialchars($row["id"]); ?>">View Details</a> | 
                            <a href="request_refill.php?id=<?php echo htmlspecialchars($row["id"]); ?>">Request Refill</a> 
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php } else { ?>
            <p>No prescriptions found.</p>
        <?php } ?>

        <?php
        $conn->close();
        
        ?>
