<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "../includes/db_connect.php";

$user_id = $_SESSION['id'];
$user_role = $_SESSION['role'];

// Handle filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'start_date';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Initialize SQL query variable
$sql = '';

// Base SQL query depending on user role
if($user_role === 'patient') {
    $sql = "SELECT p.*, 
            doc_user.username AS doctor_name,
            ph.name AS pharmacy_name
            FROM prescriptions p
            JOIN users doc_user ON p.doctor_id = doc_user.id
            JOIN pharmacies ph ON p.pharmacy_id = ph.pharmacy_id
            WHERE p.user_id = ?";
} else if($user_role === 'doctor') {
    $sql = "SELECT p.*, 
            u.username AS patient_name,
            ph.name AS pharmacy_name
            FROM prescriptions p
            JOIN users u ON p.user_id = u.id
            JOIN pharmacies ph ON p.pharmacy_id = ph.pharmacy_id
            WHERE p.doctor_id = ?";
} else {
    // Default query for other roles or handle appropriately
    $sql = "SELECT p.*, 
            u.username AS patient_name,
            doc_user.username AS doctor_name,
            ph.name AS pharmacy_name
            FROM prescriptions p
            JOIN users u ON p.user_id = u.id
            JOIN users doc_user ON p.doctor_id = doc_user.id
            JOIN pharmacies ph ON p.pharmacy_id = ph.pharmacy_id
            WHERE 1=1";
}

// Add filters to SQL query
if($status_filter) {
    $sql .= " AND p.refill_status = ?";
}
if($date_filter) {
    $sql .= " AND DATE(p.start_date) = ?";
}

// Add sorting
$sql .= " ORDER BY p.$sort $order";

// Prepare and execute statement with appropriate binding
$stmt = $conn->prepare($sql);

// Bind parameters based on filters
if($user_role === 'patient' || $user_role === 'doctor') {
    if($status_filter && $date_filter) {
        $stmt->bind_param("iss", $user_id, $status_filter, $date_filter);
    } else if($status_filter) {
        $stmt->bind_param("is", $user_id, $status_filter);
    } else if($date_filter) {
        $stmt->bind_param("is", $user_id, $date_filter);
    } else {
        $stmt->bind_param("i", $user_id);
    }
} else {
    if($status_filter && $date_filter) {
        $stmt->bind_param("ss", $status_filter, $date_filter);
    } else if($status_filter) {
        $stmt->bind_param("s", $status_filter);
    } else if($date_filter) {
        $stmt->bind_param("s", $date_filter);
    }
}

$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container-fluid">
    <!-- Filter and Sort Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row align-items-end">
                <input type="hidden" name="page" value="view_prescriptions">
                <div class="col-md-3 mb-2">
                    <label>Filter by Status</label>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="new" <?php echo $status_filter === 'new' ? 'selected' : ''; ?>>New</option>
                        <option value="refill_requested" <?php echo $status_filter === 'refill_requested' ? 'selected' : ''; ?>>Refill Requested</option>
                        <option value="refilled" <?php echo $status_filter === 'refilled' ? 'selected' : ''; ?>>Refilled</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label>Filter by Date</label>
                    <input type="date" name="date" class="form-control" value="<?php echo $date_filter; ?>" onchange="this.form.submit()">
                </div>
                <div class="col-md-3 mb-2">
                    <label>Sort by</label>
                    <select name="sort" class="form-control" onchange="this.form.submit()">
                        <option value="start_date" <?php echo $sort === 'start_date' ? 'selected' : ''; ?>>Start Date</option>
                        <option value="medication_name" <?php echo $sort === 'medication_name' ? 'selected' : ''; ?>>Medication Name</option>
                        <option value="updated_at" <?php echo $sort === 'updated_at' ? 'selected' : ''; ?>>Last Updated</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <a href="?page=view_prescriptions" class="btn btn-secondary btn-block">Reset Filters</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Prescriptions Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="fas fa-prescription-bottle-alt"></i> 
                Prescriptions List
            </h3>
        </div>
        <div class="card-body">
            <?php if($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Medication</th>
                                <?php if($user_role === 'doctor'): ?>
                                    <th>Patient</th>
                                <?php else: ?>
                                    <th>Doctor</th>
                                <?php endif; ?>
                                <th>Pharmacy</th>
                                <th>Dosage & Frequency</th>
                                <th>Dates</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row["medication_name"]); ?></td>
                                    <?php if($user_role === 'doctor'): ?>
                                        <td><?php echo htmlspecialchars($row["patient_name"]); ?></td>
                                    <?php else: ?>
                                        <td><?php echo htmlspecialchars($row["doctor_name"]); ?></td>
                                    <?php endif; ?>
                                    <td><?php echo htmlspecialchars($row["pharmacy_name"]); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($row["dosage"]); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($row["frequency"]); ?></small>
                                    </td>
                                    <td>
                                        Start: <?php echo date('M d, Y', strtotime($row["start_date"])); ?><br>
                                        <small class="text-muted">
                                            End: <?php echo $row["end_date"] ? date('M d, Y', strtotime($row["end_date"])) : 'Not specified'; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge <?php 
                                            echo $row["refill_status"] === 'new' ? 'badge-info' : 
                                                ($row["refill_status"] === 'refill_requested' ? 'badge-warning' : 'badge-success'); 
                                        ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $row["refill_status"])); ?>
                                        </span>
                                        <?php if($row["request_status"] === 'pending'): ?>
                                            <br><span class="badge badge-secondary">Pending Approval</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="?page=view_prescription_details&id=<?php echo $row["id"]; ?>" 
                                               class="btn btn-info btn-sm" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if($user_role === 'doctor'): ?>
                                                <a href="?page=edit_prescription&id=<?php echo $row["id"]; ?>" 
                                                   class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if($user_role === 'patient' && $row["refill_status"] === 'new'): ?>
                                                <a href="?page=request_refill&id=<?php echo $row["id"]; ?>" 
                                                   class="btn btn-success btn-sm" title="Request Refill"
                                                   onclick="return confirm('Are you sure you want to request a refill?');">
                                                    <i class="fas fa-sync"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No prescriptions found.
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
.badge {
    padding: 8px 12px;
    font-weight: 500;
}
</style>

<?php
$stmt->close();
$conn->close();
?>