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