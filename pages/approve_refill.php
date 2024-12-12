<?php
session_start();

// Check if user is logged in and has pharmacist role
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'pharmacist') {
    header("location: login.php");
    exit;
}

require_once "../includes/db_connect.php";

// Check if prescription ID is provided
if(!isset($_GET["id"])) {
    $_SESSION['error_message'] = "No prescription specified.";
    header("location: home.php");
    exit;
}

$prescription_id = $_GET["id"];
$pharmacist_id = $_SESSION["id"];

// First verify this pharmacist has access to this prescription
$verify_sql = "SELECT p.id, p.refill_status, p.request_status 
               FROM prescriptions p
               JOIN pharmacists ph ON p.pharmacy_id = ph.pharmacy_id
               WHERE p.id = ? AND ph.user_id = ? 
               AND p.refill_status = 'refill_requested' 
               AND p.request_status = 'pending'";

$verify_stmt = $conn->prepare($verify_sql);
$verify_stmt->bind_param("ii", $prescription_id, $pharmacist_id);
$verify_stmt->execute();
$result = $verify_stmt->get_result();

if($result->num_rows === 0) {
    $_SESSION['error_message'] = "Invalid prescription or you don't have permission to approve this refill.";
    header("location: home.php");
    exit;
}

// If verification passed, update the prescription status
$update_sql = "UPDATE prescriptions 
               SET refill_status = 'refilled',
                   request_status = 'approved',
                   updated_at = CURRENT_TIMESTAMP
               WHERE id = ?";

$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("i", $prescription_id);

// Try to update the prescription
if($update_stmt->execute()) {
    // Insert a notification for the patient
    $notification_sql = "INSERT INTO notifications (user_id, type, message, created_at)
                        SELECT user_id, 'refill_approved', 
                        CONCAT('Your refill request for ', medication_name, ' has been approved.'),
                        CURRENT_TIMESTAMP
                        FROM prescriptions WHERE id = ?";
    
    $notify_stmt = $conn->prepare($notification_sql);
    $notify_stmt->bind_param("i", $prescription_id);
    $notify_stmt->execute();

    $_SESSION['success_message'] = "Refill request has been approved successfully.";
} else {
    $_SESSION['error_message'] = "Error approving refill request. Please try again.";
}

// Close all statements
$verify_stmt->close();
$update_stmt->close();
if(isset($notify_stmt)) $notify_stmt->close();
$conn->close();

// Redirect back to the dashboard
header("location: home.php");
exit;
?>