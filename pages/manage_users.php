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

// Prepare the statement outside the conditional blocks
$stmt = $conn->prepare($sql); 

if($role_filter) {
    $stmt->bind_param("sss", $search_term, $search_term, $role_filter);
} else {
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
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>

    <style>
        body{ font: 14px sans-serif; 
            text-align: center;
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa; 
        }
        .wrapper {
            background: #fff;
            border-radius: 10px; /* More rounded corners */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 80%;
            max-width: 1200px;
            margin: 30px auto;
        }
        h2 {
            text-align: center;
            margin-bottom: 30px; /* Increased margin */
            color: #343a40;
            font-weight: 700;
        }

        h3 {
            color: #343a40;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            border-radius: 5px; /* Rounded input fields */
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            transition: background-color 0.2s ease; /* Smooth transition */
        }

        .btn-primary:hover {
            background-color: #0062cc; /* Darker shade on hover */
        }

        .table {
            width: 100%;
            max-width: 100%;
            margin-top: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-collapse: separate; /* Add space between cells */
            border-spacing: 0 10px; /* Adjust spacing as needed */
        }

        .table th, .table td {
            padding: 15px;
            vertical-align: middle;
            background-color: #fff; /* White background for cells */
            border-radius: 5px; /* Rounded cell corners */
        }

        .table th {
            background-color: #f8f9fa; /* Light background for header */
            font-weight: 700;
            color: #343a40;
        }

        .table-bordered th,
        .table-bordered td {
            border: none; /* Remove default border */
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
        }

        .fa {
            margin-right: 5px;
        }
    </style>
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


<?php
// Close the statement and connection
//$stmt->close(); 
$conn->close();
include "../includes/footer.php";
?>

</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>