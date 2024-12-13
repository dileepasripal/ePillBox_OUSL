<?php
session_start();
header('Content-Type: application/json');

if(!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    exit(json_encode(['success' => false]));
}

require_once "../../includes/db_connect.php";

if(isset($_POST['id'])) {
    $sql = "UPDATE notifications SET is_read = 1 
            WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $_POST['id'], $_SESSION['id']);
    
    echo json_encode(['success' => $stmt->execute()]);
    $stmt->close();
} else {
    echo json_encode(['success' => false]);
}

$conn->close();
?>