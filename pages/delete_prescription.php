<?php
session_start();

// Check if the user is logged in and has the 'doctor' role 
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'doctor') {
    header("location: login.php");
    exit;
}

require_once "../includes/db_connect.php";

// Check if prescription ID is provided
if(!isset($_GET["id"])) {
    header("location: login.php");
    exit;
}

$prescription_id = $_GET["id"];
$doctor_id = $_SESSION["id"];

// First check if the prescription exists and belongs to this doctor
$check_sql = "SELECT id, medication_name FROM prescriptions WHERE id = ? AND doctor_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $prescription_id, $doctor_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if($result->num_rows === 0) {
    $_SESSION['error_message'] = "Prescription not found or you don't have permission to delete it.";
    header("location: login.php");
    exit;
}

$prescription = $result->fetch_assoc();

// If this is a GET request, show confirmation page
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Delete Prescription</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
        <style>
            .wrapper {
                background: #fff;
                border-radius: 10px;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                padding: 40px;
                width: 80%;
                max-width: 800px;
                margin: 30px auto;
            }
            .alert-warning {
                background-color: #fff3cd;
                border-color: #ffeeba;
            }
        </style>
    </head>
    <body>
        <div class="wrapper">
            <?php include "../includes/header.php"; ?>
            
            <div class="alert alert-warning">
                <h4 class="alert-heading">
                    <i class="fas fa-exclamation-triangle"></i> Confirm Deletion
                </h4>
                <p>Are you sure you want to delete the prescription for: <strong><?php echo htmlspecialchars($prescription['medication_name']); ?></strong>?</p>
                <p class="mb-0">This action cannot be undone.</p>
            </div>

            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $prescription_id; ?>">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete Prescription
                </button>
                <a href="login.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </form>

            <?php include "../includes/footer.php"; ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// If this is a POST request, process the deletion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Start transaction
    $conn->begin_transaction();

    try {
        // Delete related medication logs first
        $delete_logs_sql = "DELETE FROM medication_logs WHERE prescription_id = ?";
        $delete_logs_stmt = $conn->prepare($delete_logs_sql);
        $delete_logs_stmt->bind_param("i", $prescription_id);
        $delete_logs_stmt->execute();

        // Delete related medication reminders
        $delete_reminders_sql = "DELETE FROM medication_reminders WHERE prescription_id = ?";
        $delete_reminders_stmt = $conn->prepare($delete_reminders_sql);
        $delete_reminders_stmt->bind_param("i", $prescription_id);
        $delete_reminders_stmt->execute();

        // Finally delete the prescription
        $delete_prescription_sql = "DELETE FROM prescriptions WHERE id = ? AND doctor_id = ?";
        $delete_prescription_stmt = $conn->prepare($delete_prescription_sql);
        $delete_prescription_stmt->bind_param("ii", $prescription_id, $doctor_id);
        $delete_prescription_stmt->execute();

        // If we got here, commit the transaction
        $conn->commit();

        $_SESSION['success_message'] = "Prescription deleted successfully.";
        header("location: login.php");
        exit;

    } catch (Exception $e) {
        // Something went wrong, rollback
        $conn->rollback();
        $_SESSION['error_message'] = "Error deleting prescription: " . $e->getMessage();
        header("location: doctor_dashboard.php");
        exit;
    }
}

// Close all statements and connection
if(isset($check_stmt)) $check_stmt->close();
if(isset($delete_logs_stmt)) $delete_logs_stmt->close();
if(isset($delete_reminders_stmt)) $delete_reminders_stmt->close();
if(isset($delete_prescription_stmt)) $delete_prescription_stmt->close();
$conn->close();
?>