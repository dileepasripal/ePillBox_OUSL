<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Check if the user has the 'admin' role, if not then redirect to home page
if ($_SESSION["role"] !== 'admin') {
    header("location: home.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
        }

        .wrapper {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 80%;
            max-width: 1200px;
            margin: 30px auto;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #343a40;
            font-weight: 700;
        }

        .table {
            width: 100%;
            max-width: 100%;
            margin-top: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .table th, .table td {
            padding: 15px;
            vertical-align: middle;
            background-color: #fff;
            border-radius: 5px;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 700;
            color: #343a40;
        }

        .table-bordered th,
        .table-bordered td {
            border: none;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
        }

        .fa {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include "../includes/header.php"; ?>

        <h2>User Management</h2>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include '../includes/db_connect.php';

                $sql = "SELECT id, username, role FROM users";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row["id"] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row["username"] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row["role"] ?? ''); ?></td>
                            <td>
                                <a href="edit_user.php?id=<?php echo htmlspecialchars($row["id"] ?? ''); ?>" class="btn btn-primary btn-sm mr-2"><i class="fa fa-pencil"></i> Edit</a>
                                <a href="delete_user.php?id=<?php echo htmlspecialchars($row["id"] ?? ''); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');"><i class="fa fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                <?php
                    }
                } else {
                ?>
                    <tr>
                        <td colspan="4">No users found.</td>
                    </tr>
                <?php
                }
                $conn->close();
                ?>
            </tbody>
        </table>

        <?php include "../includes/footer.php"; ?>
    </div>
</body>
</html>