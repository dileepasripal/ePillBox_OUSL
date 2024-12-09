<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 80%; padding: 20px; margin: 0 auto; } 
        .table{
            width: 100%;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include "../includes/header.php"; ?> 
    <div class="wrapper">
        <h2>User Management</h2>
        <?php
        include '../includes/db_connect.php'; 

        $sql = "SELECT id, username, role FROM users"; 
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo '<table class="table table-bordered table-striped">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>ID</th>';
            echo '<th>Username</th>';
            echo '<th>Role</th>';
            echo '<th>Actions</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["id"]. "</td>";
                echo "<td>" . $row["username"]. "</td>";
                echo "<td>" . $row["role"] . "</td>";
                echo "<td>";
                echo '<a href="edit_user.php?id=' . $row["id"] . '" class="mr-2">Edit</a>'; 
                echo '<a href="delete_user.php?id=' . $row["id"] . '" onclick="return confirm(\'Are you sure you want to delete this user?\');">Delete</a>';
                echo "</td>";
                echo "</tr>";
            }
            echo '</tbody>';
            echo '</table>';
        } else {
            echo "<p>No users found.</p>";
        }
        $conn->close();
        ?>
    </div>
    <?php include "../includes/footer.php"; ?>
</body>
</html>