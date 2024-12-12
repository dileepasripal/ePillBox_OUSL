<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("location: login.php");
    exit;
}

require_once "../includes/db_connect.php";

// Get the selected date (default to today if not specified)
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Fetch daily statistics
$stats_sql = "SELECT 
    COUNT(CASE WHEN DATE(created_at) = ? THEN 1 END) as total_prescriptions,
    COUNT(CASE WHEN DATE(created_at) = ? AND refill_status = 'new' THEN 1 END) as new_prescriptions,
    COUNT(CASE WHEN DATE(updated_at) = ? AND refill_status = 'refill_requested' THEN 1 END) as refill_requests,
    COUNT(CASE WHEN DATE(created_at) = ? THEN 1 END) as registered_users
FROM prescriptions 
CROSS JOIN (SELECT COUNT(*) as registered_users FROM users WHERE DATE(created_at) = ?) as u";

$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param("sssss", $selected_date, $selected_date, $selected_date, $selected_date, $selected_date);
$stats_stmt->execute();
$daily_stats = $stats_stmt->get_result()->fetch_assoc();

// Fetch detailed activity log for the selected date
$activity_sql = "SELECT 
    p.id,
    p.medication_name,
    p.refill_status,
    p.request_status,
    p.created_at,
    p.updated_at,
    u.username as patient_name,
    d.username as doctor_name,
    ph.name as pharmacy_name
FROM prescriptions p
JOIN users u ON p.user_id = u.id
JOIN users du ON p.doctor_id = du.id
JOIN doctors d ON du.id = d.user_id
JOIN pharmacies ph ON p.pharmacy_id = ph.pharmacy_id
WHERE DATE(p.created_at) = ? OR DATE(p.updated_at) = ?
ORDER BY p.updated_at DESC";

$activity_stmt = $conn->prepare($activity_sql);
$activity_stmt->bind_param("ss", $selected_date, $selected_date);
$activity_stmt->execute();
$activities = $activity_stmt->get_result();
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mt-4">Daily Activity Report</h1>
        <a href="?page=admin_dashboard" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <!-- Date Selection -->
    <div class="card mb-4">
        <div class="card-body">
            <form class="row align-items-center" method="GET">
                <input type="hidden" name="page" value="daily_activity">
                <div class="col-md-3">
                    <label for="date" class="form-label">Select Date</label>
                    <input type="date" class="form-control" id="date" name="date" 
                           value="<?php echo $selected_date; ?>" max="<?php echo date('Y-m-d'); ?>"
                           onchange="this.form.submit()">
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">Total Prescriptions</div>
                            <div class="h3"><?php echo $daily_stats['total_prescriptions']; ?></div>
                        </div>
                        <i class="fas fa-prescription-bottle-alt fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">New Prescriptions</div>
                            <div class="h3"><?php echo $daily_stats['new_prescriptions']; ?></div>
                        </div>
                        <i class="fas fa-file-medical fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">Refill Requests</div>
                            <div class="h3"><?php echo $daily_stats['refill_requests']; ?></div>
                        </div>
                        <i class="fas fa-sync fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">New Users</div>
                            <div class="h3"><?php echo $daily_stats['registered_users']; ?></div>
                        </div>
                        <i class="fas fa-user-plus fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Log -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-history me-1"></i>
            Detailed Activity Log for <?php echo date('F d, Y', strtotime($selected_date)); ?>
        </div>
        <div class="card-body">
            <?php if($activities->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Pharmacy</th>
                                <th>Medication</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $activities->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('H:i:s', strtotime($row['updated_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['pharmacy_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['medication_name']); ?></td>
                                    <td>
                                        <span class="badge <?php 
                                            echo $row['refill_status'] === 'new' ? 'bg-success' : 
                                                ($row['refill_status'] === 'refill_requested' ? 'bg-warning' : 'bg-info'); 
                                        ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $row['refill_status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?page=view_prescription_details&id=<?php echo $row['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No activities recorded for this date.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}
.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}
.table th {
    background-color: #f8f9fa;
}
</style>

<?php
$stats_stmt->close();
$activity_stmt->close();
$conn->close();
?>