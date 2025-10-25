<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized";
    exit();
}

$user_id = $_SESSION['user_id'];
$message_id = intval($_POST['message_id'] ?? 0);

if ($message_id > 0) {
    // Mark the message as deleted for the current user
    $stmt = $conn->prepare("
        UPDATE contact_messages 
        SET sender_deleted = CASE WHEN sender_id = ? THEN 1 ELSE sender_deleted END,
            receiver_deleted = CASE WHEN receiver_id = ? THEN 1 ELSE receiver_deleted END
        WHERE message_id = ?
    ");

    if (!$stmt) {
        echo "SQL Error: " . $conn->error;
        exit();
    }

    $stmt->bind_param("iii", $user_id, $user_id, $message_id);

    if ($stmt->execute()) {
        echo "Message deleted successfully.";
    } else {
        echo "Error deleting message.";
    }

    $stmt->close();
} else {
    echo "Invalid message.";
}

$conn->close();
?>
