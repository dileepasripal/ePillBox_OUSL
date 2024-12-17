<?php


// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'pharmacist') {
    header("location: login.php");
    exit;
}

require_once "../includes/db_connect.php";

// Get pharmacist's pharmacy_id
$pharmacist_id = $_SESSION["id"];
$pharmacy_sql = "SELECT pharmacy_id FROM pharmacists WHERE user_id = ?";
$pharmacy_stmt = $conn->prepare($pharmacy_sql);
$pharmacy_stmt->bind_param("i", $pharmacist_id);
$pharmacy_stmt->execute();
$pharmacy_result = $pharmacy_stmt->get_result();
$pharmacy_data = $pharmacy_result->fetch_assoc();
$pharmacy_id = $pharmacy_data['pharmacy_id'];

// Fetch prescriptions with refill requests for this pharmacy
$sql = "SELECT p.*, 
            u.username AS patient_name,
            u.contact AS patient_contact,
            d.username AS doctor_name,
            d.hospital AS doctor_hospital
            FROM prescriptions p
            JOIN users u ON p.user_id = u.id
            JOIN users du ON p.doctor_id = du.id
            JOIN doctors d ON du.id = d.user_id
            WHERE p.pharmacy_id = ? 
            AND (
                (p.request_status = 'pending' AND p.refill_status = 'refill_requested')
                OR p.refill_status = 'new'
            )
            ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pharmacy_id);
$stmt->execute();
$result = $stmt->get_result();
?>



    <div class="container-fluid">
        <div class="row">
            

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="container-fluid">
                    <div class="dashboard-header d-flex justify-content-between align-items-center mb-4">
                        <h2>Pharmacist Dashboard</h2>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-clock"></i> Pending Requests</h5>
                                    <h3 class="card-text"><?php echo $result->num_rows; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h3 class="h5 mb-0"><i class="fas fa-sync"></i> Refill Requests</h3>
                        </div>
                        <div class="card-body">
                            <?php if (isset($_SESSION['success_message'])): ?>
                                <div class="alert alert-success">
                                    <?php 
                                    echo $_SESSION['success_message']; 
                                    unset($_SESSION['success_message']);
                                    ?>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['error_message'])): ?>
                                <div class="alert alert-danger">
                                    <?php 
                                    echo $_SESSION['error_message']; 
                                    unset($_SESSION['error_message']);
                                    ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($result->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Patient</th>
                                                <th>Medication</th>
                                                <th>Doctor</th>
                                                <th>Request Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($row["patient_name"]); ?></strong><br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($row["patient_contact"]); ?></small>
                                                    </td>
                                                    <td>
                                                        <?php echo htmlspecialchars($row["medication_name"]); ?><br>
                                                        <small class="text-muted">Dosage: <?php echo htmlspecialchars($row["dosage"]); ?></small>
                                                    </td>
                                                    <td>
                                                        <?php echo htmlspecialchars($row["doctor_name"]); ?><br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($row["doctor_hospital"]); ?></small>
                                                    </td>
                                                    <td><?php echo date('M d, Y', strtotime($row["updated_at"])); ?></td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="view_prescription_details.php?id=<?php echo htmlspecialchars($row["id"]); ?>" 
                                                               class="btn btn-info btn-sm" title="View Details">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="approve_refill.php?id=<?php echo htmlspecialchars($row["id"]); ?>" 
                                                               class="btn btn-success btn-sm" title="Approve Refill"
                                                               onclick="return confirm('Are you sure you want to approve this refill request?');">
                                                                <i class="fas fa-check"></i>
                                                            </a>
                                                            <a href="reject_refill.php?id=<?php echo htmlspecialchars($row["id"]); ?>" 
                                                               class="btn btn-danger btn-sm" title="Reject Refill"
                                                               onclick="return confirm('Are you sure you want to reject this refill request?');">
                                                                <i class="fas fa-times"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> No pending refill requests at this time.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>


<?php
$stmt->close();
if(isset($pharmacy_stmt)) $pharmacy_stmt->close();
?>