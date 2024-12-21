<?php
session_start();
require_once "../includes/db_connect.php";

if (!isset($_SESSION['id']) || !isset($_POST['id'])) {
    http_response_code(400);
    exit;
}

$notification_id = (int)$_POST['id'];
$user_id = $_SESSION['id'];

// Mark notification as read and log medication
$conn->begin_transaction();

try {
    // Update notification
    $update_sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ii", $notification_id, $user_id);
    $stmt->execute();

    // Log medication taken
    $log_sql = "INSERT INTO medication_logs (user_id, prescription_id) 
                SELECT user_id, reference_id 
                FROM notifications 
                WHERE id = ? AND type = 'medication_reminder'";
    $log_stmt = $conn->prepare($log_sql);
    $log_stmt->bind_param("i", $notification_id);
    $log_stmt->execute();

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}