<?php
session_start();

// Check if the user is logged in and a prescription ID is provided
if (!isset($_SESSION['id']) || !isset($_GET['id'])) {
    header("Location: home.php"); // Redirect to patient dashboard if no prescription ID or not logged in
    exit;
}

// Include config file
require_once "../includes/db_connect.php";

// Fetch prescription details (to display on the refill request page)
$prescription_id = $_GET['id'];
$user_id = $_SESSION['id'];

$sql = "SELECT p.*, u.username AS doctor_name  -- Changed doctor_id to doctor_name for clarity
            FROM prescriptions p
            JOIN users u ON p.doctor_id = u.id
            WHERE p.id = ? AND p.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $prescription_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $prescription_data = $result->fetch_assoc();
} else {
    // Handle the case where the prescription is not found or doesn't belong to the user
    header("Location: home.php"); // Redirect to patient dashboard if prescription not found
    exit;
}

// Fetch pharmacies for the dropdown
$sql = "SELECT pharmacy_id, name FROM pharmacies";
$pharmacies_result = $conn->query($sql);

if (!$pharmacies_result) {
    die("Error fetching pharmacies: " . $conn->error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get pharmacy ID from the form
    $pharmacy_id = $_POST["pharmacy_id"];

    // Update the prescription with the selected pharmacy and request status
    $sql = "UPDATE prescriptions 
                SET pharmacy_id = ?, request_status = 'pending' 
                WHERE id = ? AND user_id = ?"; 
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $pharmacy_id, $prescription_id, $user_id);

    if ($stmt->execute()) {
        // Display success message on the same page
        echo "<div class='alert alert-success'>Your refill request has been submitted successfully!</div>"; 

        // --- Add notification for the pharmacist ---

        // 1. Get the pharmacist's user ID associated with the selected pharmacy
        $pharmacist_id_sql = "SELECT user_id FROM pharmacists WHERE pharmacy_id = ?";
        $pharmacist_id_stmt = $conn->prepare($pharmacist_id_sql);
        $pharmacist_id_stmt->bind_param("i", $pharmacy_id);
        $pharmacist_id_stmt->execute();
        $pharmacist_id_result = $pharmacist_id_stmt->get_result();

        if ($pharmacist_id_result->num_rows > 0) {
            $pharmacist_id = $pharmacist_id_result->fetch_assoc()['user_id'];

            // 2. Create the notification message
            $notification_message = "Patient {$_SESSION['username']} has requested a refill for prescription #{$prescription_id}.";

            // 3. Insert the notification into the database
            $notification_sql = "INSERT INTO notifications (user_id, type, message, reference_id) 
                                 VALUES (?, 'refill_request', ?, ?)";
            $notification_stmt = $conn->prepare($notification_sql);
            $notification_stmt->bind_param("isi", $pharmacist_id, $notification_message, $prescription_id);

            if ($notification_stmt->execute()) {
                // Notification added successfully
            } else {
                error_log("Error adding notification: " . $notification_stmt->error); // Log the error for debugging
            }
        } else {
            error_log("Error fetching pharmacist ID: " . $pharmacist_id_stmt->error); // Log the error
        }
    } else {
        echo "Error submitting refill request: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>

    <style>
        body{ font: 14px sans-serif; 
            text-align: center;
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa; 
        }
        .wrapper {
            background: #fff;
            border-radius: 10px; /* More rounded corners */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 80%;
            max-width: 1200px;
            margin: 30px auto;
        }
        h2 {
            text-align: center;
            margin-bottom: 30px; /* Increased margin */
            color: #343a40;
            font-weight: 700;
        }

        h3 {
            color: #343a40;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            border-radius: 5px; /* Rounded input fields */
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            transition: background-color 0.2s ease; /* Smooth transition */
        }

        .btn-primary:hover {
            background-color: #0062cc; /* Darker shade on hover */
        }

        .table {
            width: 100%;
            max-width: 100%;
            margin-top: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-collapse: separate; /* Add space between cells */
            border-spacing: 0 10px; /* Adjust spacing as needed */
        }

        .table th, .table td {
            padding: 15px;
            vertical-align: middle;
            background-color: #fff; /* White background for cells */
            border-radius: 5px; /* Rounded cell corners */
        }

        .table th {
            background-color: #f8f9fa; /* Light background for header */
            font-weight: 700;
            color: #343a40;
        }

        .table-bordered th,
        .table-bordered td {
            border: none; /* Remove default border */
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
        }

        .fa {
            margin-right: 5px;
        }
    </style>
    <style>
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }
        .large {
            font-size: 2.5rem;
            font-weight: 700;
        }
        .text-white-50 {
            color: rgba(255, 255, 255, 0.8) !important;
        }
</style>
    <style>
        .wrapper { padding: 20px; }
        .search-box { margin-bottom: 20px; }
        .role-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85em;
        }
        .role-doctor { background-color: #cce5ff; color: #004085; }
        .role-patient { background-color: #d4edda; color: #155724; }
        .role-pharmacist { background-color: #fff3cd; color: #856404; }
        .role-admin { background-color: #f8d7da; color: #721c24; }
        .action-buttons { white-space: nowrap; }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include "../includes/header.php"; ?>

        <h2>Refill Request</h2>

        <h3>Prescription Details</h3>
        <p><strong>Medication Name:</strong> <?php echo htmlspecialchars($prescription_data["medication_name"]); ?></p>
        <p><strong>Dosage:</strong> <?php echo htmlspecialchars($prescription_data["dosage"]); ?></p>
        <p><strong>Frequency:</strong> <?php echo htmlspecialchars($prescription_data["frequency"]); ?></p>
        <p><strong>Start Date:</strong> <?php echo htmlspecialchars($prescription_data["start_date"]); ?></p>
        <p><strong>End Date:</strong> <?php echo htmlspecialchars($prescription_data["end_date"]); ?></p>
        <p><strong>Special Instructions:</strong> <?php echo htmlspecialchars($prescription_data["special_instructions"]); ?></p>
        <p><strong>Doctor's Name:</strong> <?php echo htmlspecialchars($prescription_data["doctor_name"]); ?></p>

        <h3>Select Pharmacy for Refill</h3>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $prescription_id; ?>">
            <div class="form-group">
                <label for="pharmacy_id">Select Pharmacy:</label>
                <select id="pharmacy_id" name="pharmacy_id" class="form-control" required>
                    <option value="">Select Pharmacy</option>
                    <?php while ($pharmacy = $pharmacies_result->fetch_assoc()) { ?>
                        <option value="<?php echo $pharmacy['pharmacy_id']; ?>"><?php echo $pharmacy['name']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <input type="submit" value="Submit Refill Request" class="btn btn-primary">
            </div>
        </form>

        <?php include "../includes/footer.php"; ?>
    </div>
</body>
</html>