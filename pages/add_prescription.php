<?php
session_start(); // Start the session at the very beginning

// Check if the user is logged in and has the 'doctor' role 
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'doctor'){
    header("location: login.php"); // Redirect to login if not logged in or not a doctor
    exit;
}

include '../includes/db_connect.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get data from the form and perform validation
    $medication_name = $_POST["medication_name"];
    $dosage = $_POST["dosage"];
    $frequency = $_POST["frequency"];
    $start_date = $_POST["start_date"];
    $end_date = $_POST["end_date"]; // Optional
    $special_instructions = $_POST["special_instructions"]; // Optional
    $doctor_name = $_SESSION["id"]; // Assuming doctor name is the ID of the logged-in doctor
    $user_id = $_POST["patient_id"]; // Get the patient ID from the form

    // Basic input validation (you'll need to enhance this)
    $errors = array();
    if (empty($medication_name)) {
        $errors[] = "Medication name is required.";
    }
    if (empty($dosage)) {
        $errors[] = "Dosage is required.";
    }
    if (empty($frequency)) {
        $errors[] = "Frequency is required.";
    }
    if (empty($start_date)) {
        $errors[] = "Start date is required.";
    }
    if (empty($user_id)) {
        $errors[] = "Patient ID is required.";
    }

    // If there are no errors, proceed with inserting the prescription
    if (empty($errors)) {
        // Insert prescription into the database
        $sql = "INSERT INTO prescriptions (user_id, medication_name, dosage, frequency, start_date, end_date, special_instructions, doctor_name) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssssi", $user_id, $medication_name, $dosage, $frequency, $start_date, $end_date, $special_instructions, $doctor_name);

        if ($stmt->execute()) {
            echo "<p class='alert alert-success'>Prescription added successfully!</p>";
        } else {
            echo "<p class='alert alert-danger'>Error adding prescription: " . $stmt->error . "</p>";
        }

        $stmt->close();
    } else {
        // Display errors
        echo "<ul class='alert alert-danger'>";
        foreach ($errors as $error) {
            echo "<li>" . $error . "</li>";
        }
        echo "</ul>";
    }
}

// Fetch all patients for the patient selection dropdown
$sql = "SELECT id, username FROM users WHERE role = 'patient'";
$patients_result = $conn->query($sql);

if (!$patients_result) {
    die("Error fetching patients: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Prescription</title>
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
            max-width: 600px;
            margin: 30px auto;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #343a40;
            font-weight: 700;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            border-radius: 5px;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            transition: background-color 0.2s ease;
        }

        .btn-primary:hover {
            background-color: #0062cc;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include "../includes/header.php";?>
        <h2>Add Prescription</h2>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <div class="form-group">
                <label for="patient_id">Patient:</label>
                <select id="patient_id" name="patient_id" class="form-control" required>
                    <option value="">Select Patient</option>
                    <?php while ($patient = $patients_result->fetch_assoc()) { ?>
                        <option value="<?php echo $patient['id']; ?>"><?php echo $patient['username']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label for="medication_name">Medication Name:</label>
                <input type="text" id="medication_name" name="medication_name" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="dosage">Dosage:</label>
                <input type="text" id="dosage" name="dosage" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="frequency">Frequency:</label>
                <input type="text" id="frequency" name="frequency" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="start_date" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="end_date">End Date (optional):</label>
                <input type="date" id="end_date" name="end_date" class="form-control">
            </div>

            <div class="form-group">
                <label for="special_instructions">Special Instructions (optional):</label>
                <textarea id="special_instructions" name="special_instructions" class="form-control"></textarea>
            </div>

            <div class="form-group">
                <input type="submit" value="Add Prescription" class="btn btn-primary">
            </div>
        </form>

        <h3>Upload Prescription Image</h3>
        <p>This feature will be available in the future. </p>

        <?php
        $conn->close();
        include "../includes/footer.php";
        ?>
    </div>
</body>
</html>