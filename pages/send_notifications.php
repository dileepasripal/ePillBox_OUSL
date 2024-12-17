<?php
session_start();

// Check if user is logged in and has admin role
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("location: login.php");
    exit;
}

require_once "../includes/db_connect.php";

// Define variables
$recipient_ids = [];
$message = $type = "";
$errors = [];

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate recipients
    if(!isset($_POST["recipient_ids"]) || empty($_POST["recipient_ids"])) {
        $errors[] = "Please select at least one recipient.";
    } else {
        $recipient_ids = $_POST["recipient_ids"];
    }

    // Validate message
    if(empty(trim($_POST["message"]))) {
        $errors[] = "Please enter a message.";
    } else {
        $message = trim($_POST["message"]);
    }

    // Validate notification type
    if(empty($_POST["type"])) {
        $errors[] = "Please select a notification type.";
    } else {
        $type = $_POST["type"];
    }

    // If no errors, proceed with sending notifications
    if(empty($errors)) {
        $success_count = 0;
        $fail_count = 0;

        // Begin transaction
        $conn->begin_transaction();

        try {
            $sql = "INSERT INTO notifications (user_id, type, message, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);

            foreach($recipient_ids as $recipient_id) {
                $stmt->bind_param("iss", $recipient_id, $type, $message);
                if($stmt->execute()) {
                    $success_count++;
                } else {
                    $fail_count++;
                }
            }

            $conn->commit();
            $_SESSION['success_message'] = "Successfully sent notifications to $success_count recipients.";
            if($fail_count > 0) {
                $_SESSION['error_message'] = "Failed to send notifications to $fail_count recipients.";
            }
            header("location: ?page=send_notifications");
            exit();

        } catch(Exception $e) {
            $conn->rollback();
            $errors[] = "Error sending notifications: " . $e->getMessage();
        }
    }
}

// Fetch users for recipient selection
$users_sql = "SELECT u.id, u.username, u.role, u.email,
              CASE 
                  WHEN u.role = 'doctor' THEN d.specialization
                  WHEN u.role = 'patient' THEN CONCAT('Patient #', u.id)
                  WHEN u.role = 'pharmacist' THEN ph.pharmacy_name
                  ELSE ''
              END as additional_info
              FROM users u
              LEFT JOIN doctors d ON u.id = d.user_id
              LEFT JOIN pharmacists ph ON u.id = ph.user_id
              WHERE u.role != 'admin'
              ORDER BY u.role, u.username";

$users_result = $conn->query($users_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Send Notifications</h2>
    </div>

    <?php if(!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php 
                echo $_SESSION['success_message']; 
                unset($_SESSION['success_message']);
            ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Send New Notification</h5>
        </div>
        <div class="card-body">
            <form action="?page=send_notifications" method="post">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Recipients*</label>
                            <div class="recipient-list">
                                <?php if($users_result->num_rows > 0): ?>
                                    <div class="mb-2">
                                        <div class="btn-group mb-2">
                                            <button type="button" class="btn btn-secondary btn-sm" onclick="selectAllUsers()">Select All</button>
                                            <button type="button" class="btn btn-secondary btn-sm" onclick="deselectAllUsers()">Deselect All</button>
                                        </div>
                                    </div>
                                    <?php 
                                    $current_role = '';
                                    while($user = $users_result->fetch_assoc()): 
                                        if($current_role != $user['role']):
                                            if($current_role != '') echo '</div>';
                                            $current_role = $user['role'];
                                    ?>
                                            <h6 class="mt-3"><?php echo ucfirst($current_role) . 's'; ?></h6>
                                            <div class="pl-3">
                                    <?php endif; ?>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input user-checkbox" 
                                                   name="recipient_ids[]" 
                                                   value="<?php echo $user['id']; ?>" 
                                                   id="user_<?php echo $user['id']; ?>">
                                            <label class="custom-control-label" for="user_<?php echo $user['id']; ?>">
                                                <?php 
                                                    echo htmlspecialchars($user['username']);
                                                    if(!empty($user['additional_info'])) {
                                                        echo ' (' . htmlspecialchars($user['additional_info']) . ')';
                                                    }
                                                ?>
                                            </label>
                                        </div>
                                    <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">No users found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Notification Type*</label>
                            <select name="type" class="form-control" required>
                                <option value="">Select Type</option>
                                <option value="info">Information</option>
                                <option value="warning">Warning</option>
                                <option value="alert">Alert</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Message*</label>
                            <textarea name="message" class="form-control" rows="5" required><?php echo htmlspecialchars($message); ?></textarea>
                        </div>
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Send Notification
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function selectAllUsers() {
    document.querySelectorAll('.user-checkbox').forEach(checkbox => checkbox.checked = true);
}

function deselectAllUsers() {
    document.querySelectorAll('.user-checkbox').forEach(checkbox => checkbox.checked = false);
}
</script>

<?php $conn->close(); ?>

<?php include "../includes/footer.php"; ?>
    </div>
</body>
</html>