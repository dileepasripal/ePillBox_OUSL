<?php
session_start();
header('Content-Type: application/json');

if(!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    exit(json_encode(['count' => 0]));
}

require_once "../../includes/db_connect.php";

$user_id = $_SESSION['id'];
$count_sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
$stmt = $conn->prepare($count_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

echo json_encode(['count' => $count]);
?>