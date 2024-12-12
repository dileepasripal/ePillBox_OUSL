<?php
session_start();

// Check if the user is logged in and a prescription ID is provided
if (!isset($_SESSION['id']) || !isset($_GET['id'])) {
    header("Location: patient_dashboard.php"); // Redirect to patient dashboard if no prescription ID or not logged in
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
    header("Location: patient_dashboard.php"); // Redirect to patient dashboard if prescription not found
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
    } else {
        echo "Error submitting refill request: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
            max-width: 800px;
            margin: 30px auto;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #343a40;
            font-weight: 700;
        }

        p {
            margin-bottom: 10px;
        }

        strong {
            font-weight: 700;
        }

        .btn {
            border-radius: 5px;
            padding: 10px 20px;
            transition: background-color 0.2s ease;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            color: #fff;
        }

        .btn-primary:hover {
            background-color: #0062cc;
            border-color: #0062cc;
        }
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