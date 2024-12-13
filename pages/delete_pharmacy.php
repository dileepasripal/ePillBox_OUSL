<?php
session_start();

// Check if the user is logged in and has admin role
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: login.php");
    exit;
}

require_once "../includes/db_connect.php";

if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    $pharmacy_id = trim($_GET["id"]);
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // First check if there are any pharmacists assigned to this pharmacy
        $check_sql = "SELECT COUNT(*) as count FROM pharmacists WHERE pharmacy_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $pharmacy_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $pharmacist_count = $result->fetch_assoc()['count'];
        
        if($pharmacist_count > 0) {
            throw new Exception("Cannot delete pharmacy. There are still pharmacists assigned to it.");
        }
        
        // Check for active prescriptions
        $check_prescriptions_sql = "SELECT COUNT(*) as count FROM prescriptions WHERE pharmacy_id = ?";
        $check_prescriptions_stmt = $conn->prepare($check_prescriptions_sql);
        $check_prescriptions_stmt->bind_param("i", $pharmacy_id);
        $check_prescriptions_stmt->execute();
        $result = $check_prescriptions_stmt->get_result();
        $prescription_count = $result->fetch_assoc()['count'];
        
        if($prescription_count > 0) {
            throw new Exception("Cannot delete pharmacy. There are active prescriptions associated with it.");
        }
        
        // If all checks pass, delete the pharmacy
        $delete_sql = "DELETE FROM pharmacies WHERE pharmacy_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $pharmacy_id);
        
        if(!$delete_stmt->execute()) {
            throw new Exception("Error deleting pharmacy: " . $delete_stmt->error);
        }
        
        $conn->commit();
        $_SESSION['success_message'] = "Pharmacy deleted successfully.";
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = $e->getMessage();
    }
    
    // Close statements
    if(isset($check_stmt)) $check_stmt->close();
    if(isset($check_prescriptions_stmt)) $check_prescriptions_stmt->close();
    if(isset($delete_stmt)) $delete_stmt->close();
}

// Redirect back to pharmacy management
header("location: ?page=manage_pharmacies");
exit();
?>