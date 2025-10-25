<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

$user_id = $_SESSION['user_id'];
$chat_user = intval($_GET['chat_user'] ?? 0);

if ($chat_user <= 0) {
    echo json_encode([]);
    exit();
}

// Fetch messages between logged-in user and chat user
$stmt = $conn->prepare("
    SELECT message_id, sender_id, receiver_id, subject, message, created_at 
    FROM contact_messages
    WHERE 
    (
        (sender_id = ? AND receiver_id = ? AND sender_deleted = 0)
        OR
        (sender_id = ? AND receiver_id = ? AND receiver_deleted = 0)
    )
    ORDER BY created_at ASC
");

$stmt->bind_param("iiii", $user_id, $chat_user, $chat_user, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

$stmt->close();
echo json_encode($messages);
?>
