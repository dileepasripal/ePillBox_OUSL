<?php

require_once "../includes/db_connect.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["role"] !== "patient") {
    header("location: login.php");
    exit;
}

$patient_id = $_SESSION["id"];

// Fetch active prescriptions with medication logs
$sql = "SELECT p.*, u.username AS doctor_name, ph.name AS pharmacy_name,
        (SELECT COUNT(*) FROM medication_logs ml 
         WHERE ml.prescription_id = p.id 
         AND DATE(ml.taken_at) = CURDATE() 
         AND ml.status = 'taken') as doses_taken_today,
        (SELECT COUNT(*) FROM medication_logs ml 
         WHERE ml.prescription_id = p.id 
         AND ml.status = 'missed'
         AND WEEK(ml.taken_at) = WEEK(CURDATE())) as missed_doses_this_week
        FROM prescriptions p
        JOIN users u ON p.doctor_id = u.id
        JOIN pharmacies ph ON p.pharmacy_id = ph.pharmacy_id 
        WHERE p.user_id = ? 
        AND (p.end_date IS NULL OR p.end_date >= CURDATE())
        ORDER BY p.start_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
?>


    <div class="container mt-4">


        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Today's Medications</h5>
                        <h2 class="display-4">
                            <?php
                            $today_sql = "SELECT COUNT(*) as count FROM prescriptions 
                                        WHERE user_id = ? AND (end_date IS NULL OR end_date >= CURDATE())";
                            $today_stmt = $conn->prepare($today_sql);
                            $today_stmt->bind_param("i", $patient_id);
                            $today_stmt->execute();
                            echo $today_stmt->get_result()->fetch_assoc()['count'];
                            ?>
                        </h2>
                        <p>Active Prescriptions</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Adherence Rate</h5>
                        <h2 class="display-4">
                            <?php
                            $adherence_sql = "SELECT 
                                (COUNT(CASE WHEN status = 'taken' THEN 1 END) * 100.0 / 
                                COUNT(*)) as rate
                                FROM medication_logs
                                WHERE user_id = ? 
                                AND taken_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                            $adherence_stmt = $conn->prepare($adherence_sql);
                            $adherence_stmt->bind_param("i", $patient_id);
                            $adherence_stmt->execute();
                            $adherence = $adherence_stmt->get_result()->fetch_assoc()['rate'];
                            echo number_format($adherence ?? 0, 1) . '%';
                            ?>
                        </h2>
                        <p>7-Day Adherence Rate</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-prescription-bottle-alt"></i> My Medications
                </h5>
            </div>
            <div class="card-body">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="medication-card card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="card-title"><?php echo htmlspecialchars($row["medication_name"]); ?></h5>
                                        <p class="card-text">
                                            <strong>Dosage:</strong> <?php echo htmlspecialchars($row["dosage"]); ?><br>
                                            <strong>Frequency:</strong> <?php echo htmlspecialchars($row["frequency"]); ?> times daily<br>
                                            <strong>Doctor:</strong> Dr. <?php echo htmlspecialchars($row["doctor_name"]); ?><br>
                                            <strong>Pharmacy:</strong> <?php echo htmlspecialchars($row["pharmacy_name"]); ?>
                                        </p>
                                    </div>
                                    <span class="status-badge <?php echo $row["refill_status"] === 'new' ? 'status-active' : 'status-pending'; ?>">
                                        <?php echo ucfirst($row["refill_status"]); ?>
                                    </span>
                                </div>

                                <div class="dose-tracker">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span>Today's Doses: <?php echo $row["doses_taken_today"]; ?> of <?php echo $row["frequency"]; ?> taken</span>
                                        <?php if ($row["doses_taken_today"] < $row["frequency"]): ?>
                                            <button class="btn btn-primary btn-sm confirm-dose" 
                                                    data-prescription-id="<?php echo $row["id"]; ?>">
                                                <i class="fas fa-check"></i> Confirm Dose
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: <?php echo ($row["doses_taken_today"] / $row["frequency"]) * 100; ?>%">
                                        </div>
                                    </div>
                                    <?php if ($row["missed_doses_this_week"] > 0): ?>
                                        <small class="text-danger mt-1">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <?php echo $row["missed_doses_this_week"]; ?> missed doses this week
                                        </small>
                                    <?php endif; ?>
                                </div>

                                <div class="mt-3">
                                    <a href="view_prescription_details?id=<?php echo $row["id"]; ?>" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i> Details
                                    </a>
                                    <?php if ($row["refill_status"] === 'new'): ?>
                                        <a href="refill_request?id=<?php echo $row["id"]; ?>" 
                                           class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-sync"></i> Request Refill
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No active prescriptions found.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    $('.confirm-dose').click(function() {
        const button = $(this);
        const prescriptionId = button.data('prescription-id');
        
        $.post('../includes/log_medication.php', {
            prescription_id: prescriptionId
        })
        .done(function(response) {
            location.reload();
        })
        .fail(function(xhr, status, error) {
            alert('Error logging medication: ' + error);
        });
    });
    </script>


<?php
$stmt->close();
$conn->close();
?>