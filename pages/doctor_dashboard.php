<?php

// Include config file
require_once "../includes/db_connect.php";

// Fetch the doctor's ID
$doctor_id = $_SESSION["id"];

// Fetch prescriptions added by this doctor
$sql = "SELECT p.*, u.username AS patient_name 
        FROM prescriptions p
        JOIN users u ON p.user_id = u.id
        WHERE p.doctor_name = '$doctor_id'"; // Assuming you're storing the doctor's ID in the doctor_name field
$result = $conn->query($sql);

// Error handling
if (!$result) {
    die("Error fetching prescriptions: " . $conn->error);
}
?>


        <h2>Doctor Dashboard</h2>

        <ul class="list-group">
            <li class="list-group-item"><a href="add_prescription.php">Add Prescription</a></li>
            <li class="list-group-item"><a href="view_prescription.php">View Prescriptions</a></li> 
            <li class="list-group-item"><a href="get_pharmacies.php">Get Pharmacies</a></li>
        </ul>

        <h3>Prescriptions I've Added</h3>

        <?php if ($result->num_rows > 0) { ?>
        <table class="table table-bordered"> 
            <thead>
                <tr>
                    <th>Patient Name</th>
                    <th>Medication Name</th>
                    <th>Dosage</th>
                    <th>Frequency</th>
                    <th>Actions</th> 
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row["patient_name"]); ?></td>
                        <td><?php echo htmlspecialchars($row["medication_name"]); ?></td> 
                        <td><?php echo htmlspecialchars($row["dosage"]); ?></td> 
                        <td><?php echo htmlspecialchars($row["frequency"]); ?></td> 
                        <td>
                            <a href="view_prescription_details.php?id=<?php echo htmlspecialchars($row["id"]); ?>">View Details</a> | 
                            <a href="edit_prescription.php?id=<?php echo htmlspecialchars($row["id"]); ?>">Edit</a> | 
                            <a href="delete_prescription.php?id=<?php echo htmlspecialchars($row["id"]); ?>" onclick="return confirm('Are you sure you want to delete this prescription?');">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php } else { ?>
            <p>No prescriptions added yet.</p>
        <?php } ?>

        <?php
        $conn->close();
        
        ?>
