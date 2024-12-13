<?php
// Include config file
require_once "../includes/db_connect.php";

// Fetch summary data
$summaryQueries = [
    // Users Statistics
    "SELECT 
        COUNT(*) AS total_users,
        COUNT(CASE WHEN role = 'patient' THEN 1 END) AS total_patients,
        COUNT(CASE WHEN role = 'doctor' THEN 1 END) AS total_doctors,
        COUNT(CASE WHEN role = 'pharmacist' THEN 1 END) AS total_pharmacists
    FROM users",
    
    // Today's statistics
    "SELECT COUNT(*) AS new_users_today 
    FROM users 
    WHERE DATE(created_at) = CURDATE()",
    
    // Prescription Statistics
    "SELECT 
        COUNT(*) AS total_prescriptions,
        COUNT(CASE WHEN refill_status = 'refill_requested' AND request_status = 'pending' THEN 1 END) AS pending_refills,
        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) AS prescriptions_last_30days
    FROM prescriptions",
    
    // Pharmacy Statistics
    "SELECT COUNT(*) AS total_pharmacies 
    FROM pharmacies",

    // Replace pending refills query with active pharmacies statistics
    "SELECT 
        COUNT(DISTINCT ph.pharmacy_id) as total_pharmacies,
        COUNT(DISTINCT p.id) as total_prescriptions,
        ROUND(COUNT(DISTINCT p.id) / COUNT(DISTINCT ph.pharmacy_id), 0) as avg_prescriptions_per_pharmacy
    FROM pharmacies ph
    LEFT JOIN prescriptions p ON ph.pharmacy_id = p.pharmacy_id",

    // Add today's activity statistics
    "SELECT 
        COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as prescriptions_today,
        COUNT(CASE WHEN DATE(created_at) = CURDATE() AND refill_status = 'new' THEN 1 END) as new_prescriptions,
        COUNT(CASE WHEN DATE(updated_at) = CURDATE() THEN 1 END) as total_updates
    FROM prescriptions"
];

$stats = [];
foreach ($summaryQueries as $query) {
    $result = $conn->query($query);
    if ($result) {
        $stats = array_merge($stats, $result->fetch_assoc());
    }
}

// Recent Activity Log (last 5 activities)
$activity_sql = "SELECT p.*, u.username, u.role 
                 FROM prescriptions p
                 JOIN users u ON p.user_id = u.id
                 ORDER BY p.updated_at DESC LIMIT 5";
$recent_activity = $conn->query($activity_sql);
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Admin Dashboard</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Dashboard Overview</li>
    </ol>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Total Users</div>
                            <div class="large"><?php echo $stats['total_users']; ?></div>
                        </div>
                        <div>
                            <i class="fas fa-users fa-2x text-white-50"></i>
                        </div>
                    </div>
                    <div class="small text-white-50 mt-2">
                        Patients: <?php echo $stats['total_patients']; ?> | 
                        Doctors: <?php echo $stats['total_doctors']; ?> | 
                        Pharmacists: <?php echo $stats['total_pharmacists']; ?>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="manage_users">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Total Prescriptions</div>
                            <div class="large"><?php echo $stats['total_prescriptions']; ?></div>
                        </div>
                        <div>
                            <i class="fas fa-prescription-bottle-alt fa-2x text-white-50"></i>
                        </div>
                    </div>
                    <div class="small text-white-50 mt-2">
                        Last 30 Days: <?php echo $stats['prescriptions_last_30days']; ?>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="view_prescriptions">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
    <div class="card bg-warning text-white mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="small text-white-50">Active Pharmacies</div>
                    <div class="large"><?php echo $stats['total_pharmacies']; ?></div>
                </div>
                <div>
                    <i class="fas fa-clinic-medical fa-2x text-white-50"></i>
                </div>
            </div>
            <div class="small text-white-50 mt-2">
                Avg. Prescriptions: <?php echo $stats['avg_prescriptions_per_pharmacy']; ?> per pharmacy
            </div>
        </div>
        <div class="card-footer d-flex align-items-center justify-content-between">
            <a class="small text-white stretched-link" href="pharmacy_statistics.php">View Details</a>
            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
        </div>
    </div>
</div>

<div class="col-xl-3 col-md-6">
    <div class="card bg-info text-white mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="small text-white-50">Today's Activity</div>
                    <div class="large"><?php echo $stats['prescriptions_today']; ?></div>
                </div>
                <div>
                    <i class="fas fa-chart-line fa-2x text-white-50"></i>
                </div>
            </div>
            <div class="small text-white-50 mt-2">
                New: <?php echo $stats['new_prescriptions']; ?> | 
                Updates: <?php echo $stats['total_updates']; ?>
            </div>
        </div>
        <div class="card-footer d-flex align-items-center justify-content-between">
            <a class="small text-white stretched-link" href="data_analytics.php">View Details</a>
            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
        </div>
    </div>
</div>

    <!-- Quick Actions and Recent Activity -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-tools me-1"></i>
                    Quick Actions
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="manage_users" class="list-group-item list-group-item-action">
                            <i class="fas fa-user-md me-2"></i> Manage Users
                        </a>
                        <a href="manage_pharmacies" class="list-group-item list-group-item-action">
                            <i class="fas fa-clinic-medical me-2"></i> Manage Pharmacies
                        </a>
                        <a href="system_settings" class="list-group-item list-group-item-action">
                            <i class="fas fa-cog me-2"></i> System Settings
                        </a>
                        <a href="send_notifications" class="list-group-item list-group-item-action">
                            <i class="fas fa-bell me-2"></i> Send Notifications
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-history me-1"></i>
                    Recent Activity
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless table-hover">
                            <tbody>
                                <?php while ($activity = $recent_activity->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <i class="fas fa-file-medical text-primary"></i>
                                        </td>
                                        <td>
                                            <?php 
                                            echo htmlspecialchars($activity['username']) . 
                                                 " (" . htmlspecialchars($activity['role']) . ") - " . 
                                                 htmlspecialchars($activity['medication_name']); 
                                            ?>
                                        </td>
                                        <td class="text-muted">
                                            <?php echo date('M d, Y H:i', strtotime($activity['updated_at'])); ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
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
.large {
    font-size: 2.5rem;
    font-weight: 700;
}
.text-white-50 {
    color: rgba(255, 255, 255, 0.8) !important;
}
</style>

<?php
$conn->close();
?>