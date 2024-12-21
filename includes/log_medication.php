<?php
session_start();
require_once "../includes/db_connect.php";

if (!isset($_SESSION['id']) || !isset($_POST['prescription_id'])) {
    http_response_code(400);
    exit(json_encode(['error' => 'Invalid request']));
}

$user_id = $_SESSION['id'];
$prescription_id = (int)$_POST['prescription_id'];

$conn->begin_transaction();

try {
    // Verify prescription belongs to user
    $check_sql = "SELECT frequency FROM prescriptions WHERE id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $prescription_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Prescription not found or unauthorized');
    }

    // Check if max daily doses already taken
    $count_sql = "SELECT COUNT(*) as count FROM medication_logs 
                  WHERE prescription_id = ? 
                  AND DATE(taken_at) = CURDATE()
                  AND status = 'taken'";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("i", $prescription_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result()->fetch_assoc();
    
    if ($count_result['count'] >= $result->fetch_assoc()['frequency']) {
        throw new Exception('Maximum daily doses already taken');
    }

    // Log the medication
    $log_sql = "INSERT INTO medication_logs (user_id, prescription_id, taken_at, status) 
                VALUES (?, ?, NOW(), 'taken')";
    $log_stmt = $conn->prepare($log_sql);
    $log_stmt->bind_param("ii", $user_id, $prescription_id);
    $log_stmt->execute();

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>