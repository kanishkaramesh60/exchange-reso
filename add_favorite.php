<?php
session_start();
include 'db.php';
if(!isset($_SESSION['user_id'])) exit();

$user_id = $_SESSION['user_id'];
$resource_id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("INSERT IGNORE INTO favorites (user_id, resource_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $resource_id);
$stmt->execute();
$stmt->close();

header("Location: all_resources.php");
?>
