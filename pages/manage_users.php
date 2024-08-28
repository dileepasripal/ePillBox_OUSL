<div class="container">
<?php
// Check if the user is logged in and has the 'admin' role (same as in admin_dashboard.php)
include "../includes/header.php";
include '../includes/db_connect.php'; 

// Fetch all users from the database
$sql = "SELECT id, username, role FROM users"; // Fetch only necessary columns
$result = $conn->query($sql);

// Error handling
if (!$result) {
    die("Error fetching users: " . $conn->error);
}
?>

<h2>User Management</h2>

<table class="table"> <table class="table"> 
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row["id"]); ?></td> 
                <td><?php echo htmlspecialchars($row["username"]); ?></td> 
                <td><?php echo htmlspecialchars($row["role"]); ?></td> 
                <td>
                    <a href="edit_user.php?id=<?php echo htmlspecialchars($row["id"]); ?>">Edit</a> |
                    <a href="delete_user.php?id=<?php echo htmlspecialchars($row["id"]); ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<?php
$conn->close();

include "../includes/footer.php";
?>

</div>