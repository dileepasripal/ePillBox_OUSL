<?php
session_start();

// Check if the user is logged in and has admin role
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("location: login.php");
    exit;
}

require_once "../includes/db_connect.php";

// Handle search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';

// Prepare the SQL query with search and filter
$sql = "SELECT u.id, u.username, u.email, u.role, u.created_at, u.contact, 
               CASE 
                   WHEN u.role = 'doctor' THEN d.specialization
                   WHEN u.role = 'patient' THEN p.conditions
                   WHEN u.role = 'pharmacist' THEN ph.pharmacy_name
                   ELSE NULL 
               END as additional_info
        FROM users u
        LEFT JOIN doctors d ON u.id = d.user_id
        LEFT JOIN patients p ON u.id = p.user_id
        LEFT JOIN pharmacists ph ON u.id = ph.user_id
        WHERE (u.username LIKE ? OR u.email LIKE ?)";

if($role_filter) {
    $sql .= " AND u.role = ?";
}

$sql .= " ORDER BY u.created_at DESC";

$search_term = "%$search%";
if($role_filter) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $search_term, $search_term, $role_filter);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $search_term, $search_term);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .wrapper { padding: 20px; }
        .search-box { margin-bottom: 20px; }
        .role-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85em;
        }
        .role-doctor { background-color: #cce5ff; color: #004085; }
        .role-patient { background-color: #d4edda; color: #155724; }
        .role-pharmacist { background-color: #fff3cd; color: #856404; }
        .role-admin { background-color: #f8d7da; color: #721c24; }
        .action-buttons { white-space: nowrap; }
    </style>
</head>
<body>
<div class="wrapper">
<?php include "../includes/header.php"; ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>User Management</h2>
                        <a href="add_user.php" class="btn btn-success">
                            <i class="fas fa-user-plus"></i> Add New User
                        </a>
                    </div>

                    <!-- Search and Filter Section -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="row">
                                <input type="hidden" name="page" value="manage_users">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control" 
                                               placeholder="Search by username or email"
                                               value="<?php echo htmlspecialchars($search); ?>">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="submit">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <select name="role" class="form-control" onchange="this.form.submit()">
                                        <option value="">All Roles</option>
                                        <option value="doctor" <?php echo $role_filter === 'doctor' ? 'selected' : ''; ?>>Doctors</option>
                                        <option value="patient" <?php echo $role_filter === 'patient' ? 'selected' : ''; ?>>Patients</option>
                                        <option value="pharmacist" <?php echo $role_filter === 'pharmacist' ? 'selected' : ''; ?>>Pharmacists</option>
                                        <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admins</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <a href="?page=manage_users" class="btn btn-outline-secondary w-100">
                                        <i class="fas fa-sync"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Users Table -->
                    <div class="card">
                        <div class="card-body">
                            <?php if($result->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Username</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Additional Info</th>
                                                <th>Contact</th>
                                                <th>Joined Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($row = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['email'] ?? 'N/A'); ?></td>
                                                    <td>
                                                        <span class="role-badge role-<?php echo $row['role']; ?>">
                                                            <?php echo ucfirst(htmlspecialchars($row['role'])); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['additional_info'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($row['contact'] ?? 'N/A'); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                                    <td class="action-buttons">
                                                        <a href="view_user?id=<?php echo $row['id']; ?>" 
                                                           class="btn btn-info btn-sm" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="edit_user?id=<?php echo $row['id']; ?>" 
                                                           class="btn btn-primary btn-sm" title="Edit User">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="?page=delete_user&id=<?php echo $row['id']; ?>" 
                                                           class="btn btn-danger btn-sm" title="Delete User"
                                                           onclick="return confirm('Are you sure you want to delete this user?');">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> No users found matching your criteria.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add required scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
include "../includes/footer.php";
?>