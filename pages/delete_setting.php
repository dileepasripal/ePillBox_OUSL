<?php
require_once "../includes/db_connect.php";
header('Content-Type: application/json');

$stmt = $conn->prepare("DELETE FROM settings WHERE id = ?");
$stmt->bind_param("i", $_POST['id']);
echo json_encode(['success' => $stmt->execute()]);