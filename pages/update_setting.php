<?php
require_once "../includes/db_connect.php";
header('Content-Type: application/json');

$stmt = $conn->prepare("UPDATE settings SET name = ?, value = ? WHERE id = ?");
$stmt->bind_param("ssi", $_POST['setting_name'], $_POST['setting_value'], $_POST['setting_id']);
echo json_encode(['success' => $stmt->execute()]);