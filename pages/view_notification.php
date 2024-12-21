<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

require_once "../includes/db_connect.php";

$user_id = $_SESSION['id'];
$notification = null;

// Get notification ID from URL
if (isset($_GET['id'])) {
    $notification_id = (int)$_GET['id'];

    // Fetch the notification with proper security
    $sql = "SELECT n.*, 
            CASE 
                WHEN n.type = 'medication_reminder' THEN p.medication_name 
                ELSE NULL 
            END as medication_name,
            CASE 
                WHEN n.type = 'medication_reminder' THEN p.dosage
                ELSE NULL 
            END as dosage
            FROM notifications n
            LEFT JOIN prescriptions p ON n.reference_id = p.id 
            WHERE n.id = ? AND n.user_id = ?";
            
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("ii", $notification_id, $user_id);
    if (!$stmt->execute()) {
        die("Error executing statement: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Notification not found or access denied.";
        header("Location: all_notifications.php");
        exit();
    }
    
    $notification = $result->fetch_assoc();
    $stmt->close();

    // Mark notification as read
    $update_sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    if ($update_stmt) {
        $update_stmt->bind_param("ii", $notification_id, $user_id);
        $update_stmt->execute();
        $update_stmt->close();
    }
} else {
    header("Location: all_notifications.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Notification</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .notification {
            border: 1px solid #ddd;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            background-color: #fff;
        }
        .notification-header {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .notification-time {
            color: #666;
            font-size: 0.9em;
        }
        .notification-type {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85em;
            margin-right: 10px;
        }
        .type-medication_reminder {
            background-color: #cce5ff;
            color: #004085;
        }
        .type-prescription {
            background-color: #d4edda;
            color: #155724;
        }
        .type-refill {
            background-color: #fff3cd;
            color: #856404;
        }
        .type-system {
            background-color: #f8f9fa;
            color: #343a40;
        }
        .medication-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <?php include "../includes/header.php"; ?>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bell"></i> Notification Details
                </h5>
            </div>
            <div class="card-body">
                <?php if ($notification): ?>
                    <div class="notification">
                        <div class="notification-header">
                            <span class="notification-type type-<?php echo htmlspecialchars($notification['type']); ?>">
                                <?php
                                $icon = '';
                                switch ($notification['type']) {
                                    case 'medication_reminder':
                                        $icon = '<i class="fas fa-pills"></i>';
                                        break;
                                    case 'prescription':
                                        $icon = '<i class="fas fa-prescription"></i>';
                                        break;
                                    case 'refill_request':
                                    case 'refill_approved':
                                        $icon = '<i class="fas fa-sync"></i>';
                                        break;
                                    default:
                                        $icon = '<i class="fas fa-info-circle"></i>';
                                }
                                echo $icon . ' ' . ucfirst(str_replace('_', ' ', $notification['type']));
                                ?>
                            </span>
                            <span class="notification-time">
                                <i class="far fa-clock"></i>
                                <?php echo date('F d, Y h:i A', strtotime($notification['created_at'])); ?>
                            </span>
                        </div>

                        <div class="notification-content">
                            <?php echo nl2br(htmlspecialchars($notification['message'])); ?>
                        </div>

                        <?php if ($notification['type'] === 'medication_reminder' && $notification['medication_name']): ?>
                            <div class="medication-details">
                                <h6><i class="fas fa-prescription-bottle-alt"></i> Medication Details</h6>
                                <p class="mb-1"><strong>Medication:</strong> <?php echo htmlspecialchars($notification['medication_name']); ?></p>
                                <p class="mb-1"><strong>Dosage:</strong> <?php echo htmlspecialchars($notification['dosage']); ?></p>
                                <?php if (!$notification['is_read']): ?>
                                    <button class="btn btn-primary btn-sm mt-2 confirm-medication" data-id="<?php echo $notification['id']; ?>">
                                        <i class="fas fa-check"></i> Confirm Taken
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Notification not found.
                    </div>
                <?php endif; ?>

                <a href="all_notifications.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Notifications
                </a>
            </div>
        </div>
        
        <?php include "../includes/footer.php"; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.confirm-medication').click(function() {
                const button = $(this);
                const notificationId = button.data('id');
                
                $.post('../includes/confirm_medication.php', {
                    id: notificationId
                })
                .done(function(response) {
                    button.prop('disabled', true)
                          .html('<i class="fas fa-check"></i> Confirmed')
                          .removeClass('btn-primary')
                          .addClass('btn-success');
                })
                .fail(function(xhr, status, error) {
                    alert('Error confirming medication: ' + error);
                });
            });
        });
    </script>
</body>
</html>