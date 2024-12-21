<?php
session_start();
require_once "../includes/db_connect.php";

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['id'];
$last_check = isset($_GET['last_check']) ? $_GET['last_check'] : date('Y-m-d H:i:s', strtotime('-1 minute'));

// Get new notifications
$sql = "SELECT n.*, p.medication_name, p.dosage, p.frequency 
        FROM notifications n
        LEFT JOIN prescriptions p ON n.reference_id = p.id
        WHERE n.user_id = ? 
        AND n.created_at > ?
        AND n.is_read = 0
        ORDER BY n.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $last_check);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    // Format notification based on type
    if ($row['type'] === 'medication_reminder') {
        $row['formatted_message'] = "Time to take {$row['medication_name']} - {$row['dosage']}";
        $row['icon'] = 'fas fa-pills';
    }
    $notifications[] = $row;
}

echo json_encode([
    'hasNewNotifications' => count($notifications) > 0,
    'count' => count($notifications),
    'notifications' => $notifications,
    'timestamp' => date('Y-m-d H:i:s')
]);