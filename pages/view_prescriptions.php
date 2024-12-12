<?php
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include config file
require_once "../includes/db_connect.php";

// Fetch user's prescriptions from the database
$user_id = $_SESSION['id'];
$sql = "SELECT * FROM prescriptions WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Handle filtering and sorting (you'll need to implement this logic based on user input)
// ...

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Prescriptions</title>
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

        <h2>View Prescriptions</h2>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Medication Name</th>
                    <th>Dosage</th>
                    <th>Frequency</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row["medication_name"]); ?></td>
                        <td><?php echo htmlspecialchars($row["dosage"]); ?></td>
                        <td><?php echo htmlspecialchars($row["frequency"]); ?></td>
                        <td><?php echo htmlspecialchars($row["start_date"]); ?></td>
                        <td><?php echo htmlspecialchars($row["end_date"]); ?></td>
                        <td>
                            <a href="view_prescription_details.php?id=<?php echo htmlspecialchars($row["id"]); ?>" class="btn btn-primary btn-sm mr-2"><i class="fa fa-eye"></i> View Details</a>
                            <a href="edit_prescription.php?id=<?php echo htmlspecialchars($row["id"]); ?>" class="btn btn-warning btn-sm mr-2"><i class="fa fa-pencil"></i> Edit</a>
                            <a href="delete_prescription.php?id=<?php echo htmlspecialchars($row["id"]); ?>" class="btn btn-danger btn-sm mr-2" onclick="return confirm('Are you sure you want to delete this prescription?');"><i class="fa fa-trash"></i> Delete</a>
                            <a href="refill_request.php?id=<?php echo htmlspecialchars($row["id"]); ?>" class="btn btn-success btn-sm"><i class="fa fa-repeat"></i> Refill</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <?php
        $stmt->close();
        $conn->close();
        include "../includes/footer.php";
        ?>
    </div>
</body>
</html>