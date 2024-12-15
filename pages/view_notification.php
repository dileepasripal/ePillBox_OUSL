<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

require_once "../includes/db_connect.php";

$user_id = $_SESSION['id'];

// Get notification ID from URL
if (isset($_GET['id'])) {
    $notification_id = (int)$_GET['id'];

    // Fetch the notification
    $sql = "SELECT * FROM notifications WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error); 
    }
    $stmt->bind_param("ii", $notification_id, $user_id);
    if (!$stmt->execute()) {
        die("Error executing statement: " . $stmt->error); 
    }
    $result = $stmt->get_result();
    $notification = $result->fetch_assoc();
    $stmt->close();

    // If notification not found or doesn't belong to the user
    if (!$notification) {
        echo "<p>Notification not found.</p>"; 
        exit;
    }

    // Mark notification as read
    $update_sql = "UPDATE notifications SET is_read = 1 WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    if ($update_stmt === false) {
        die("Error preparing statement: " . $conn->error); 
    }
    $update_stmt->bind_param("i", $notification_id);
    if (!$update_stmt->execute()) {
        die("Error executing statement: " . $update_stmt->error); 
    }
    $update_stmt->close();

} else {
    echo "<p>Invalid notification ID.</p>";
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Notification</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles.css"> 
    <style>
        .notification {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .notification .time {
            font-size: smaller;
            color: #888;
            float: right; /* Align time to the right */
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include "../includes/header.php"; ?>
        <h1>Notification Details</h1>

        <div class="notification">
            <?php
            $message = htmlspecialchars($notification['message']);
            $type = htmlspecialchars($notification['type']);
            $created_at = htmlspecialchars($notification['created_at']);

            // Add icons based on notification type
            $icon = '';
            switch ($type) {
                case 'prescription':
                    $icon = '<i class="fas fa-prescription-bottle-alt"></i> ';
                    break;
                case 'prescription_update':
                    $icon = '<i class="fas fa-edit"></i> ';
                    break;
                case 'prescription_deleted':
                    $icon = '<i class="fas fa-trash"></i> ';
                    break;
                case 'refill_request':
                    $icon = '<i class="fas fa-sync"></i> ';
                    break;
                case 'refill_approved':
                    $icon = '<i class="fas fa-check-circle"></i> ';
                    break;
                case 'refill_rejected':
                    $icon = '<i class="fas fa-times-circle"></i> ';
                    break;
                // Add more cases as needed
                default:
                    $icon = '<i class="fas fa-bell"></i> '; // Default icon
            }
            echo $icon . $message;
            ?>
            <span class="time"><?php echo $created_at; ?></span>
        </div>

        <a href="all_notifications.php" class="btn btn-secondary mt-3">Back to Notifications</a>
        <?php include "../includes/footer.php"; ?>
        <?php $conn->close(); ?>
    </div>
</body>
</html>