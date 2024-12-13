<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("location: login.php");
    exit;
}

require_once "../includes/db_connect.php";

if(!isset($_GET["id"])) {
    header("location: home.php");
    exit;
}

$user_id = $_GET["id"];

// Begin transaction
$conn->begin_transaction();

try {
    // Delete role-specific data first
    $tables = ['doctors', 'patients', 'pharmacists'];
    foreach($tables as $table) {
        $sql = "DELETE FROM $table WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }

    // Delete prescriptions
    $sql = "DELETE FROM prescriptions WHERE user_id = ? OR doctor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();

    // Finally delete the user
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $conn->commit();
    $_SESSION["success_message"] = "User deleted successfully.";

} catch(Exception $e) {
    $conn->rollback();
    $_SESSION["error_message"] = "Error deleting user: " . $e->getMessage();
}

header("location: ?page=manage_users");
exit;
?>