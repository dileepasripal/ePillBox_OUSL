<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">   

    <style>
        body {
            font: 14px sans-serif;
            background-color:   
 #f4f4f4;
            display: flex;
            flex-direction: column; 
            min-height: 100vh; 
        }

        .wrapper {
            background: #fff;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 80%;
            max-width: 1200px; 
            margin: 50px auto; 
            flex-grow: 1; 
            width: 600px;
        }

        .wrapper h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .table {
            width: 100%;
            max-width: 100%; 
            margin-bottom: 20px;
        }

        .table th, .table td {
            padding: 10px;
            vertical-align: middle; 
        }
        .wrapper a {
            color: #000; 
        }
    </style>
</head>
<body>
    <div class="wrapper">
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

        <table class="table table-bordered">  </div>
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
</body>
</html>