<?php

session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "../includes/db_connect.php";

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['id'];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

if (isset($_GET['mark_all_read'])) {
    $mark_read_sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
    $stmt = $conn->prepare($mark_read_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    header("location: all_notifications");
    exit;
}

// Fetch notifications with SQL_CALC_FOUND_ROWS
$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $limit, $offset);
$stmt->execute();
$notification_result = $stmt->get_result(); // Renamed $notifications to $notification_result

// Get total count
$count_sql = "SELECT FOUND_ROWS() as total_count";
$count_result = $conn->query($count_sql);
$total_notifications = $count_result->fetch_assoc()['total_count'];
$total_pages = ceil($total_notifications / $limit);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Notifications</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles.css"> </head>
<body>
<?php include "../includes/header.php"; ?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Notifications</h5>
            <div>
                <a href="home" class="btn btn-secondary btn-sm mr-2">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <a href="all_notifications?mark_all_read=1" class="btn btn-primary btn-sm">
                    <i class="fas fa-check-double"></i> Mark All as Read
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if ($notification_result->num_rows > 0): ?>
                <?php while ($notification = $notification_result->fetch_assoc()): ?>
                    <div class="notification-item <?php echo !$notification['is_read'] ? 'notification-unread' : ''; ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <?php
                                $type_class = 'type-message';
                                $icon_class = 'info';
                                switch ($notification['type']) {
                                    case 'prescription':
                                        $type_class = 'type-prescription';
                                        $icon_class = 'prescription-bottle-alt';
                                        break;
                                    case 'prescription_update':
                                        $type_class = 'type-prescription';
                                        $icon_class = 'edit';
                                        break;
                                    case 'prescription_deleted':
                                        $type_class = 'type-prescription';
                                        $icon_class = 'trash';
                                        break;
                                    default:
                                        $type_class = 'type-message';
                                        $icon_class = 'info';
                                }
                                ?>
                                <span class="notification-type <?php echo $type_class; ?> mb-2">
                                    <i class="fas fa-<?php echo $icon_class; ?> mr-1"></i>
                                    <?php echo ucfirst(str_replace('_', ' ', $notification['type'])); ?>
                                </span>
                                <div class="mt-2">
                                    <?php echo nl2br(htmlspecialchars($notification['message'])); ?>
                                </div>
                            </div>
                            <div class="notification-time">
                                <?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?>
                            </div>
                        </div>
                        <div class="mt-2">
                            <a href="view_notification?id=<?php echo $notification['id']; ?>"
                               class="btn btn-link btn-sm p-0">
                                View Details <i class="fas fa-chevron-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>

                <?php if ($total_pages > 1): ?>
                    <div class="card-footer">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="all_notifications?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="all_notifications?page=<?php echo $i; ?>" aria-label="Page <?php echo $i; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="all_notifications?page=<?php echo $page + 1; ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-bell-slash fa-3x mb-3"></i>
                    <p class="mb-0">No notifications found</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>