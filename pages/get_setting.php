<?php
require_once "../includes/db_connect.php";
header('Content-Type: application/json');

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM settings WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
echo json_encode($result->fetch_assoc());