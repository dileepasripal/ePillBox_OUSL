<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Prescription</title>
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
            max-width: 600px;  
            margin: 50px auto; 
            flex-grow: 1; 
        }

        .wrapper h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px; 
        }

        .form-group label {
            font-weight: bold;
        }

        .form-control {
            border-radius: 3px; 
        }

        .btn-primary {
            background-color: #007bff; 
            border: none;
            border-radius: 3px;  
 
            padding: 10px 20px;
            cursor: pointer;
            display: block; 
            width: 100%; 
        }

        .btn-primary:hover {
            background-color: #0069d9; 
        }

        .invalid-feedback {
            color: #dc3545; 
            font-size: 12px;
        }
        .wrapper a {
            color: #000; 
        }
    </style>
</head>
<body>
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
    $doctor_name = $_POST["doctor_name"];
    $user_id = $_SESSION['id'];

    // Basic input validation (you'll need to enhance this)
    // ...

    // Insert prescription into the database
    $sql = "INSERT INTO prescriptions (user_id, medication_name, dosage, frequency, start_date, end_date, special_instructions, doctor_name) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssss", $user_id, $medication_name, $dosage, $frequency, $start_date, $end_date, $special_instructions, $doctor_name);

    if ($stmt->execute()) {
        echo "Prescription added successfully!";
    } else {
        echo "Error adding prescription: " . $stmt->error;
    }

    $stmt->close();
}
?>

<div class="wrapper"> 
    <?php include "../includes/header.php";?>
<h2>Add Prescription</h2>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <div class="form-group">
                <label for="medication_name">Medication Name:</label>
                <input type="text" id="medication_name" name="medication_name"   
 class="form-control" required>
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
                <label for="start_date">Start   
 Date:</label>
                <input type="date" id="start_date" name="start_date" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="end_date">End Date  
 (optional):</label>
                <input type="date" id="end_date" name="end_date" class="form-control">
            </div>

            <div class="form-group">
                <label for="special_instructions">Special Instructions (optional):</label>
                <textarea id="special_instructions" 
 name="special_instructions" class="form-control"></textarea>
            </div>

            <div class="form-group">
                <label for="doctor_name">Doctor's Name:</label>
                <input type="text" id="doctor_name" name="doctor_name" class="form-control" required>
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