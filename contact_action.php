<?php
session_start();
include 'db.php';

// ✅ Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = intval($_POST['receiver_id']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    // ✅ Check receiver validity
    $check = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $check->bind_param("i", $receiver_id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows === 0) {
        die("Invalid receiver.");
    }
    $check->close();

    // ✅ Validate input
    if (!empty($subject) && !empty($message)) {
        $stmt = $conn->prepare("INSERT INTO contact_messages (sender_id, receiver_id, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $sender_id, $receiver_id, $subject, $message);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "✅ Message sent successfully!";
            header("Location: user_dashboard.php");
            exit();
        } else {
            die("❌ Failed to send message: " . $stmt->error);
        }
        $stmt->close();
    } else {
        die("⚠️ Please fill in all fields.");
    }
}
?>
