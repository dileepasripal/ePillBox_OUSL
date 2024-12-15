<?php
// Fetch notifications for logged-in users
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && isset($conn)) { // Check if $conn exists

    $user_id = $_SESSION['id'];
    try {
        // Fetch notifications
        $notifications_sql = "SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 5";
        $stmt = $conn->prepare($notifications_sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $notifications = $stmt->get_result();
        
        // Get unread count
        $count_sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param("i", $user_id);
        $count_stmt->execute();
        $unread_count = $count_stmt->get_result()->fetch_assoc()['count'];
        
        // Close statements
        //$stmt->close();
        //$count_stmt->close();
    } catch (Exception $e) {
        error_log("Error fetching notifications: " . $e->getMessage());
        $notifications = null;
        $unread_count = 0;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ePillbox - <?php echo isset($title) ? $title : ''; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }

        .navbar {
            background-color: #007bff; 
        }

        .navbar-brand {
            font-weight: 700; 
            color: #fff !important; 
        }

        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.8) !important; 
            transition: color 0.2s ease; 
        }

        .navbar-nav .nav-link:hover {
            color: #fff !important; 
        }

        /* Notification Styles */
        .notification-dropdown {
            min-width: 300px;
            padding: 0;
            max-height: 400px;
            overflow-y: auto;
        }

        .notification-item {
            padding: 10px 15px;
            border-bottom: 1px solid #dee2e6;
            white-space: normal;
        }

        .notification-item:hover {
            background-color: #f8f9fa;
        }

        .badge-notification {
            position: absolute;
            top: 0px;
            right: -5px;
            font-size: 0.75em;
            padding: 0.25em 0.4em;
        }

        .nav-link-notification {
            position: relative;
            display: inline-block;
        }

        .dropdown-header {
            background-color: #f8f9fa;
            font-weight: bold;
            padding: 10px 15px;
        }

        .notification-time {
            font-size: 0.85em;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark"> 
            <div class="container">
                <a class="navbar-brand" href="home.php">ePillbox</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ml-auto"> 
                        <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="../pages/home.php">Home</a>
                            </li>
                            
                            <!-- Notifications Dropdown -->
                            <li class="nav-item dropdown">
                                <a class="nav-link nav-link-notification dropdown-toggle" href="#" id="notificationsDropdown" 
                                   role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-bell"></i>
                                    <?php if(isset($unread_count) && $unread_count > 0): ?>
                                        <span class="badge badge-danger badge-notification"><?php echo $unread_count; ?></span>
                                    <?php endif; ?>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right notification-dropdown" aria-labelledby="notificationsDropdown">
                                    <div class="dropdown-header">Notifications</div>
                                    <?php if(isset($notifications) && $notifications->num_rows > 0): ?>
                                        <?php while($notification = $notifications->fetch_assoc()): ?>
                                            <a class="dropdown-item notification-item" href="view_notification?id=<?php echo $notification['id']; ?>" 
                                               data-id="<?php echo $notification['id']; ?>">
                                                <div class="notification-time">
                                                    <?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?>
                                                </div>
                                                <div>
                                                    <?php echo htmlspecialchars($notification['message']); ?>
                                                </div>
                                            </a>
                                        <?php endwhile; ?>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item text-center" href="all_notifications">
                                            View All Notifications
                                        </a>
                                    <?php else: ?>
                                        <div class="dropdown-item text-center">
                                            No new notifications
                                        </div>
                                        <a class="dropdown-item text-center" href="all_notifications">
                                            View All Notifications
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="../pages/profile.php">Profile</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../pages/logout.php">Logout</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="../pages/register.php">Register</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../pages/login.php">Login</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main class="flex-grow-1">

    <!-- Required JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    
    <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
    <script>
    $(document).ready(function() {
        // Mark notification as read when clicked
        $('.notification-item').click(function() {
            const notificationId = $(this).data('id');
            $.post('ajax/mark_notification_read.php', {
                id: notificationId
            });
        });

        // Check for new notifications every 30 seconds
        setInterval(function() {
            $.get('ajax/check_notifications.php', function(data) {
                if(data.count > 0) {
                    $('.badge-notification').text(data.count).show();
                } else {
                    $('.badge-notification').hide();
                }
            });
        }, 30000);
    });
    </script>
    <?php endif; ?>

    </main>
</body>
</html>