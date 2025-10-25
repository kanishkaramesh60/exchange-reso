<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized";
    exit();
}

$user_id = $_SESSION['user_id'];
$chat_user = intval($_GET['chat_user'] ?? 0);

if ($chat_user > 0) {
    $stmt = $conn->prepare("
        UPDATE contact_messages
        SET sender_deleted = CASE WHEN sender_id = ? THEN 1 ELSE sender_deleted END,
            receiver_deleted = CASE WHEN receiver_id = ? THEN 1 ELSE receiver_deleted END
        WHERE (sender_id = ? AND receiver_id = ?) 
           OR (sender_id = ? AND receiver_id = ?)
    ");

    if (!$stmt) {
        echo "SQL Error: " . $conn->error;
        exit();
    }

    $stmt->bind_param("iiiiii", $user_id, $user_id, $user_id, $chat_user, $chat_user, $user_id);

    if ($stmt->execute()) {
        echo "Chat deleted successfully.";
    } else {
        echo "Error deleting chat.";
    }

    $stmt->close();
} else {
    echo "Invalid user.";
}

$conn->close();
?>
