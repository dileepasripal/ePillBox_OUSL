<?php
session_start(); 

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Check if the user has the 'admin' role, if not then redirect to home page
if($_SESSION["role"] !== 'admin'){
    header("location: home.php");
    exit;
}

// Include config file
require_once "../includes/db_connect.php"; 

// Fetch all conversations
$sql = "SELECT 
            m.id AS message_id, 
            s.username AS sender, 
            r.username AS recipient, 
            m.subject, 
            m.body, 
            m.created_at
        FROM messages m
        JOIN users s ON m.sender_id = s.id
        JOIN users r ON m.recipient_id = r.id
        ORDER BY m.created_at DESC"; 
$result = $conn->query($sql);

if (!$result) {
    die("Error fetching messages: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Messages</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
    <style>
        body {
            font: 14px sans-serif;
            background-color: #f4f4f4;
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
            width: 1000px;
        }

        .wrapper h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .wrapper .btn {
            display: inline-block; 
            margin-bottom: 10px; 
            margin-right: 10px; 
        }

        .wrapper a {
            color: #fff; 
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
        <?php include "../includes/header.php"; ?>

        <h2>Manage Messages</h2>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Sender</th>
                    <th>Recipient</th>
                    <th>Subject</th>
                    <th>Body</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row["message_id"]); ?></td>
                        <td><?php echo htmlspecialchars($row["sender"]); ?></td>
                        <td><?php echo htmlspecialchars($row["recipient"]); ?></td>
                        <td><?php echo htmlspecialchars($row["subject"]); ?></td>
                        <td><?php echo htmlspecialchars($row["body"]); ?></td>
                        <td><?php echo htmlspecialchars($row["created_at"]); ?></td>
                        <td>
                            <a href="view_message.php?id=<?php echo htmlspecialchars($row["message_id"]); ?>" class="btn btn-primary btn-sm mr-2"><i class="fa fa-eye"></i> View</a>
                            <a href="delete_message.php?id=<?php echo htmlspecialchars($row["message_id"]); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this message?');"><i class="fa fa-trash"></i> Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <?php include "../includes/footer.php"; ?>
    </div>
</body>
</html>