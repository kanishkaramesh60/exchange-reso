<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'];
$receiver_id = intval($_POST['receiver_id']);
$message = $_POST['message'];
$subject = $_POST['subject'];

$stmt = $conn->prepare("INSERT INTO contact_messages (sender_id, receiver_id, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("iiss", $user_id, $receiver_id, $subject, $message);
$stmt->execute();
$stmt->close();
echo "success";
?>
