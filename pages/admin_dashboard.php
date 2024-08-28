<?php
// Database connection (assuming db_connect.php handles this)
include '../includes/db_connect.php'; 

// Fetch summary data
$totalUsersQuery = "SELECT COUNT(*) AS total FROM users";
$newUsersTodayQuery = "SELECT COUNT(*) AS new_today FROM users WHERE DATE(created_at) = CURDATE()";
// ... (Add more queries for recent activity as needed, e.g., latest refill requests, messages, etc.)

$totalUsersResult = $conn->query($totalUsersQuery);
$newUsersTodayResult = $conn->query($newUsersTodayQuery);
// ... (Execute other queries)

$totalUsers = $totalUsersResult->fetch_assoc()['total'];
$newUsersToday = $newUsersTodayResult->fetch_assoc()['new_today'];
// ... (Fetch data from other query results)

$conn->close(); 
?>

<h2>Admin Dashboard</h2>

<nav> 
    <ul>
        <li><a href="manage_users.php">User Management</a></li>
        <li><a href="manage_content.php">Content Management</a></li>
        <li><a href="manage_pharmacies.php">Pharmacy Management</a></li>
        <li><a href="data_analytics.php">Data Analytics</a></li>
    </ul>
</nav>

<section id="dashboard-summary">
    <h3>Summary</h3>
    <ul>
        <li>Total Users: <?php echo $totalUsers; ?></li> 
        <li>New Users (Today): <?php echo $newUsersToday; ?></li>
        </ul>
</section>